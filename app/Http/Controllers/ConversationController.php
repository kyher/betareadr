<?php

namespace App\Http\Controllers;

use App\Actions\Conversations\MarkConversationRead;
use App\Actions\Conversations\SendConversationMessage;
use App\Models\BetaReadingMatch;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ConversationController extends Controller
{
    public function show(BetaReadingMatch $match, Request $request): Response
    {
        Gate::authorize('view', $match);
        $match->load('readerDecision.betaReaderProfile', 'readerDecision.betaReadingListing.book.authorProfile');
        $user = $request->user(User::AUTH_GUARD);
        $isAuthor = $match->readerDecision->betaReadingListing->book->authorProfile->user_id === $user->id;
        $profile = $isAuthor ? $match->readerDecision->betaReaderProfile : $match->readerDecision->betaReadingListing->book->authorProfile;

        return Inertia::render('matches/Conversation', [
            'match' => [
                'id' => $match->id,
                'ended_at' => $match->ended_at?->toISOString(),
                'ended_by' => $match->ended_by_user_id === null ? null : ($match->ended_by_user_id === $match->readerDecision->betaReadingListing->book->authorProfile->user_id ? 'author' : 'beta_reader'),
                'profile' => $profile->only(['display_name']) + ['avatar_url' => $profile->avatarUrl()],
                'book' => $match->readerDecision->betaReadingListing->book->only(['title', 'language', 'genre', 'content_warnings']),
            ],
            'userId' => $user->id,
        ]);
    }

    public function index(BetaReadingMatch $match, Request $request): JsonResponse
    {
        Gate::authorize('view', $match);
        $input = $request->validate(['cursor' => ['nullable', 'integer', 'min:1']]);

        $messages = $match->messages()
            ->when(isset($input['cursor']), fn ($query) => $query->where('id', '<', $input['cursor']))
            ->orderByDesc('id')->limit(51)->get(['id', 'sender_id', 'body', 'created_at']);

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

    public function read(BetaReadingMatch $match, Request $request, MarkConversationRead $markRead): JsonResponse
    {
        Gate::authorize('view', $match);
        $input = $request->validate(['message_id' => ['required', 'integer', 'min:1']]);
        $user = $request->user(User::AUTH_GUARD);
        $markRead->handle($match, $user, $input['message_id']);

        return response()->json(['unreadMessageCount' => $user->unreadMessageCount()]);
    }
}
