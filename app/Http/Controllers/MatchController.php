<?php

namespace App\Http\Controllers;

use App\Actions\BetaReadingListings\EndBetaReadingMatch;
use App\Models\BetaReadingMatch;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MatchController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user(User::AUTH_GUARD);

        $matches = BetaReadingMatch::query()
            ->whereHas('readerDecision', fn ($query) => $query
                ->whereHas('betaReaderProfile', fn ($query) => $query->where('user_id', $user->id))
                ->orWhereHas('betaReadingListing.book.authorProfile', fn ($query) => $query->where('user_id', $user->id)))
            ->with([
                'readerDecision.betaReaderProfile',
                'readerDecision.betaReadingListing.book.authorProfile',
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
                    'profile' => $profile->only(['display_name', 'handle']),
                    'book' => $book->only(['id', 'title']),
                    'listing' => $listing->only(['id', 'reading_brief']),
                    'created_at' => $match->created_at?->toISOString(),
                    'ended_at' => $match->ended_at?->toISOString(),
                ];
            });

        return Inertia::render('matches/Index', ['matches' => $matches]);
    }

    public function end(BetaReadingMatch $match, EndBetaReadingMatch $endMatch): RedirectResponse
    {
        Gate::authorize('end', $match);

        if ($endMatch->handle($match)) {
            Inertia::flash('toast', ['type' => 'success', 'message' => __('Match ended.')]);
        }

        return back();
    }
}
