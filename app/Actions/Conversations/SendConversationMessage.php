<?php

namespace App\Actions\Conversations;

use App\Events\ConversationMessageSent;
use App\Models\BetaReadingMatch;
use App\Models\ConversationMessage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendConversationMessage
{
    public function handle(BetaReadingMatch $match, User $sender, string $body): ConversationMessage
    {
        $message = DB::transaction(function () use ($match, $sender, $body): ConversationMessage {
            $locked = BetaReadingMatch::query()->lockForUpdate()->findOrFail($match->id);
            abort_if($locked->ended_at !== null, 409, 'This Match has ended.');

            return $locked->messages()->create([
                'sender_id' => $sender->id,
                'body' => $body,
            ]);
        });

        try {
            $decision = $match->readerDecision;
            $authorId = $decision->betaReadingListing->book->authorProfile->user_id;
            $readerId = $decision->betaReaderProfile->user_id;
            event(new ConversationMessageSent($match->id, $message->id, $sender->id === $authorId ? $readerId : $authorId));
        } catch (Throwable $exception) {
            Log::warning('Conversation notification failed', ['match_id' => $match->id, 'message_id' => $message->id, 'exception' => $exception]);
        }

        return $message;
    }
}
