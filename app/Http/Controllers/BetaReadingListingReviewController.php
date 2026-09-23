<?php

namespace App\Http\Controllers;

use App\Actions\BetaReadingListings\ReviewInterest;
use App\Enums\BetaReadingListingAuthorDecision;
use App\Enums\BetaReadingListingReaderDecisionType;
use App\Models\BetaReadingListing;
use App\Models\BetaReadingListingReaderDecision;
use App\Models\BetaReadingMatch;
use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class BetaReadingListingReviewController extends Controller
{
    public function index(Book $book, BetaReadingListing $listing): Response
    {
        Gate::authorize('manage', [$listing, $book]);

        $interested = $listing->readerDecisions()
            ->where('reader_decision', BetaReadingListingReaderDecisionType::Interested)
            ->whereNull('author_decision')
            ->with('betaReaderProfile')
            ->oldest()
            ->oldest('id')
            ->get()
            ->map(fn (BetaReadingListingReaderDecision $interest): array => $this->readerData($interest));

        $approved = BetaReadingMatch::query()
            ->whereNull('ended_at')
            ->whereHas('readerDecision', fn ($query) => $query->where('beta_reading_listing_id', $listing->id))
            ->with('readerDecision.betaReaderProfile')
            ->latest()
            ->latest('id')
            ->get()
            ->map(fn (BetaReadingMatch $match): array => $this->readerData($match->readerDecision, $match->id));

        return Inertia::render('beta-reading-listings/Review', [
            'book' => $book->only(['id', 'title', 'language', 'genre', 'content_warnings']),
            'listing' => $listing->only(['id', 'closed_at']),
            'interested' => $interested,
            'approved' => $approved,
        ]);
    }

    public function approve(
        Book $book,
        BetaReadingListing $listing,
        BetaReadingListingReaderDecision $interest,
        ReviewInterest $reviewInterest,
    ): RedirectResponse {
        Gate::authorize('reviewInterest', [$listing, $book, $interest]);

        if ($reviewInterest->handle($interest, BetaReadingListingAuthorDecision::Approved)) {
            Inertia::flash('toast', ['type' => 'success', 'message' => __('Beta reader approved.')]);
        }

        return back();
    }

    public function decline(
        Book $book,
        BetaReadingListing $listing,
        BetaReadingListingReaderDecision $interest,
        ReviewInterest $reviewInterest,
    ): RedirectResponse {
        Gate::authorize('reviewInterest', [$listing, $book, $interest]);
        $reviewInterest->handle($interest, BetaReadingListingAuthorDecision::Declined);

        return back();
    }

    /** @return array{id: int, match_id: int|null, created_at: string|null, profile: array<string, mixed>} */
    private function readerData(BetaReadingListingReaderDecision $interest, ?int $matchId = null): array
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
