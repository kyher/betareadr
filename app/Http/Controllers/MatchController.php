<?php

namespace App\Http\Controllers;

use App\Actions\BetaReadingListings\EndBetaReadingMatch;
use App\Models\BetaReadingMatch;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MatchController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user(User::AUTH_GUARD);

        return DB::transaction(function () use ($user): Response {
            $matches = BetaReadingMatch::query()
                ->whereHas('readerDecision', fn ($query) => $query
                    ->whereHas('betaReaderProfile', fn ($query) => $query->where('user_id', $user->id))
                    ->orWhereHas('betaReadingListing.book.authorProfile', fn ($query) => $query->where('user_id', $user->id)))
                ->lockForUpdate()
                ->with([
                    'readerDecision.betaReaderProfile',
                    'readerDecision.betaReadingListing.book.authorProfile',
                    'latestMessage',
                ])
                ->latest()
                ->latest('id')
                ->get()
                ->map(function (BetaReadingMatch $match) use ($user): array {
                    $decision = $match->readerDecision;
                    $listing = $decision->betaReadingListing;
                    $book = $listing->book;
                    $isAuthor = $book->authorProfile->user_id === $user->id;
                    $profile = $isAuthor ? $decision->betaReaderProfile : $book->authorProfile;

                    return [
                        'id' => $match->id,
                        'unread_count' => $match->messages()->where('sender_id', '!=', $user->id)->where('id', '>', $isAuthor ? $match->author_read_message_id : $match->reader_read_message_id)->count(),
                        'role' => $isAuthor ? 'author' : 'beta_reader',
                        'profile' => $profile->only(['display_name', 'handle']) + ['avatar_url' => $profile->avatarUrl()],
                        'book' => $book->only(['id', 'title', 'language', 'genre', 'content_warnings']),
                        'listing' => $listing->only(['id', 'reading_brief']),
                        'created_at' => $match->created_at?->toISOString(),
                        'ended_at' => $match->ended_at?->toISOString(),
                        'ended_by' => $match->ended_by_user_id === null ? null : ($match->ended_by_user_id === $book->authorProfile->user_id ? 'author' : 'beta_reader'),
                        'latest_message' => $match->latestMessage?->only(['body', 'created_at']),
                    ];
                });

            return Inertia::render('matches/Index', ['matches' => $matches]);
        });
    }

    public function end(BetaReadingMatch $match, EndBetaReadingMatch $endMatch): RedirectResponse
    {
        Gate::authorize('end', $match);

        if ($endMatch->handle($match, request()->user(User::AUTH_GUARD))) {
            Inertia::flash('toast', ['type' => 'success', 'message' => __('Match ended.')]);
        }

        return back();
    }
}
