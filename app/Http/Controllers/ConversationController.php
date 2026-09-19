<?php

namespace App\Http\Controllers;

use App\Actions\Conversations\SendConversationMessage;
use App\Models\BetaReadingMatch;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ConversationController extends Controller
{
    public function show(BetaReadingMatch $match, Request $request): Response
    {
        return DB::transaction(function () use ($match, $request): Response {
            $match = BetaReadingMatch::query()->lockForUpdate()->findOrFail($match->id);
            Gate::authorize('view', $match);
            $this->markRead($match, $request->user(User::AUTH_GUARD)->id);
            $match->load('readerDecision.betaReaderProfile', 'readerDecision.betaReadingListing.book.authorProfile');
            $user = $request->user(User::AUTH_GUARD);
            $isAuthor = $match->readerDecision->betaReadingListing->book->authorProfile->user_id === $user->id;
            $profile = $isAuthor ? $match->readerDecision->betaReaderProfile : $match->readerDecision->betaReadingListing->book->authorProfile;

            return Inertia::render('matches/Conversation', [
                'match' => [
                    'id' => $match->id,
                    'ended_at' => $match->ended_at?->toISOString(),
                    'ended_by' => $match->ended_by_user_id === null ? null : ($match->ended_by_user_id === $match->readerDecision->betaReadingListing->book->authorProfile->user_id ? 'author' : 'beta_reader'),
                    'profile' => $profile->only(['display_name']),
                    'book' => $match->readerDecision->betaReadingListing->book->only(['title', 'content_warnings']),
                ],
                'userId' => $user->id,
            ]);
        });
    }

    public function index(BetaReadingMatch $match, Request $request): JsonResponse
    {
        Gate::authorize('view', $match);
        $input = $request->validate(['cursor' => ['nullable', 'integer', 'min:1']]);
        $userId = $request->user(User::AUTH_GUARD)->id;

        // Lock the same Match row used by send and end so read progress has a defined cutoff.
        $messages = DB::transaction(function () use ($match, $userId, $input) {
            $locked = BetaReadingMatch::query()->lockForUpdate()->findOrFail($match->id);
            if (! isset($input['cursor'])) {
                $this->markRead($locked, $userId);
            }

            return $locked->messages()
                ->when(isset($input['cursor']), fn ($query) => $query->where('id', '<', $input['cursor']))
                ->orderByDesc('id')->limit(51)->get(['id', 'sender_id', 'body', 'created_at']);
        });

        $hasMore = $messages->count() > 50;
        $page = $messages->take(50)->values();

        return response()->json([
            'messages' => $page,
            'next_cursor' => $hasMore ? $page->last()?->id : null,
        ]);
    }

    public function store(BetaReadingMatch $match, Request $request, SendConversationMessage $sendMessage): JsonResponse
    {
        Gate::authorize('view', $match);
        $input = $request->validate(['body' => ['required', 'string', 'max:5000', function (string $attribute, mixed $value, $fail): void {
            if (trim($value) === '') {
                $fail(__('The message must not be empty.'));
            }
        }]]);

        $message = $sendMessage->handle($match, $request->user(User::AUTH_GUARD), $input['body']);

        return response()->json(['message' => $message->only(['id', 'sender_id', 'body', 'created_at'])], 201);
    }

    private function markRead(BetaReadingMatch $match, int $userId): void
    {
        $isAuthor = $match->readerDecision->betaReadingListing->book->authorProfile->user_id === $userId;
        $readColumn = $isAuthor ? 'author_read_message_id' : 'reader_read_message_id';
        $latestReceivedId = $match->messages()->where('sender_id', '!=', $userId)->max('id');
        if ($latestReceivedId !== null && $latestReceivedId > $match->$readColumn) {
            $match->forceFill([$readColumn => $latestReceivedId])->save();
        }
    }
}
