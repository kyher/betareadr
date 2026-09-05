<?php

namespace App\Http\Controllers;

use App\Models\BetaReadingListing;
use App\Models\BetaReadingListingDecision;
use App\Models\BetaReadingMatch;
use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class BetaReadingListingReviewController extends Controller
{
    public function index(Book $book, BetaReadingListing $listing): Response
    {
        $this->authorizeListing($book, $listing);

        $interested = $listing->decisions()
            ->where('decision', BetaReadingListingDecision::INTERESTED)
            ->whereNull('author_decision')
            ->with('betaReaderProfile')
            ->oldest()
            ->get()
            ->map(fn (BetaReadingListingDecision $interest): array => $this->readerData($interest));

        $approved = BetaReadingMatch::query()
            ->whereNull('ended_at')
            ->whereHas('decision', fn ($query) => $query->where('beta_reading_listing_id', $listing->id))
            ->with('decision.betaReaderProfile')
            ->latest()
            ->get()
            ->map(fn (BetaReadingMatch $match): array => $this->readerData($match->decision, $match->id));

        return Inertia::render('beta-reading-listings/Review', [
            'book' => $book->only(['id', 'title']),
            'listing' => $listing->only(['id', 'closed_at']),
            'interested' => $interested,
            'approved' => $approved,
        ]);
    }

    public function approve(Book $book, BetaReadingListing $listing, BetaReadingListingDecision $interest): RedirectResponse
    {
        if ($this->decide($book, $listing, $interest, BetaReadingListingDecision::APPROVED)) {
            Inertia::flash('toast', ['type' => 'success', 'message' => __('Beta reader approved.')]);
        }

        return back();
    }

    public function decline(Book $book, BetaReadingListing $listing, BetaReadingListingDecision $interest): RedirectResponse
    {
        $this->decide($book, $listing, $interest, BetaReadingListingDecision::DECLINED);

        return back();
    }

    public function end(Book $book, BetaReadingListing $listing, BetaReadingMatch $match): RedirectResponse
    {
        $this->authorizeListing($book, $listing);
        $match->load('decision');
        abort_unless($match->decision->beta_reading_listing_id === $listing->id, 404);

        $ended = DB::transaction(function () use ($match): bool {
            $match = BetaReadingMatch::query()->lockForUpdate()->findOrFail($match->id);

            if ($match->ended_at !== null) {
                return false;
            }

            $match->update(['ended_at' => now()]);

            return true;
        });

        if ($ended) {
            Inertia::flash('toast', ['type' => 'success', 'message' => __('Match ended.')]);
        }

        return back();
    }

    private function decide(Book $book, BetaReadingListing $listing, BetaReadingListingDecision $interest, string $decision): bool
    {
        $this->authorizeInterest($book, $listing, $interest);

        return DB::transaction(function () use ($interest, $decision): bool {
            $interest = BetaReadingListingDecision::query()->lockForUpdate()->findOrFail($interest->id);

            if ($interest->author_decision !== null) {
                return false;
            }

            $interest->update(['author_decision' => $decision]);

            if ($decision === BetaReadingListingDecision::APPROVED) {
                $interest->match()->create();
            }

            return true;
        });
    }

    private function authorizeInterest(Book $book, BetaReadingListing $listing, BetaReadingListingDecision $interest): void
    {
        $this->authorizeListing($book, $listing);
        abort_unless(
            $interest->beta_reading_listing_id === $listing->id
            && $interest->decision === BetaReadingListingDecision::INTERESTED,
            404,
        );
    }

    private function authorizeListing(Book $book, BetaReadingListing $listing): void
    {
        abort_unless($book->authorProfile->user_id === request()->user()->id, 403);
        abort_unless($listing->book_id === $book->id, 404);
    }

    /** @return array{id: int, match_id: int|null, created_at: string|null, profile: array<string, mixed>} */
    private function readerData(BetaReadingListingDecision $interest, ?int $matchId = null): array
    {
        return [
            'id' => $interest->id,
            'match_id' => $matchId,
            'created_at' => $interest->created_at?->toISOString(),
            'profile' => $interest->betaReaderProfile->only([
                'display_name',
                'handle',
                'bio',
                'reading_languages',
            ]),
        ];
    }
}
