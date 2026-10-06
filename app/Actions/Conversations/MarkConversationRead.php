<?php

namespace App\Actions\Conversations;

use App\Events\UnreadMessagesChanged;
use App\Models\BetaReadingMatch;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Throwable;

class MarkConversationRead
{
    public function handle(BetaReadingMatch $match, User $user, int $messageId): void
    {
        $changed = DB::transaction(function () use ($match, $user, $messageId): bool {
            $locked = BetaReadingMatch::query()->lockForUpdate()->findOrFail($match->id);
            Gate::forUser($user)->authorize('view', $locked);
            abort_unless($locked->messages()->whereKey($messageId)->exists(), 422);
            $isAuthor = $locked->readerDecision->betaReadingListing->book->authorProfile->user_id === $user->id;
            $readColumn = $isAuthor ? 'author_read_message_id' : 'reader_read_message_id';
            $latestReceivedId = $locked->messages()->where('sender_id', '!=', $user->id)->where('id', '<=', $messageId)->max('id');
            if ($latestReceivedId === null || $latestReceivedId <= $locked->$readColumn) {
                return false;
            }

            $locked->forceFill([$readColumn => $latestReceivedId])->save();

            return true;
        });

        if ($changed) {
            DB::afterCommit(function () use ($user): void {
                try {
                    event(new UnreadMessagesChanged($user->id));
                } catch (Throwable $exception) {
                    Log::warning('Unread message notification failed', ['user_id' => $user->id, 'exception' => $exception]);
                }
            });
        }
    }
}
