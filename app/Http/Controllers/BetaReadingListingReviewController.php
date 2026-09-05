<?php

namespace App\Http\Controllers;

use App\Models\BetaReadingListing;
use App\Models\BetaReadingListingDecision;
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

        $approved = $listing->decisions()
            ->whereHas('match')
            ->with(['betaReaderProfile', 'match'])
            ->get()
            ->sortByDesc(fn (BetaReadingListingDecision $interest) => $interest->match->created_at)
            ->values()
            ->map(fn (BetaReadingListingDecision $interest): array => $this->readerData($interest));

        return Inertia::render('beta-reading-listings/Review', [
            'book' => $book->only(['id', 'title']),
            'listing' => $listing->only(['id', 'closed_at']),
            'interested' => $interested,
            'approved' => $approved,
        ]);
    }

    public function approve(Book $book, BetaReadingListing $listing, BetaReadingListingDecision $interest): RedirectResponse
    {
        $this->decide($book, $listing, $interest, BetaReadingListingDecision::APPROVED);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Beta reader approved.')]);

        return back();
    }

    public function decline(Book $book, BetaReadingListing $listing, BetaReadingListingDecision $interest): RedirectResponse
    {
        $this->decide($book, $listing, $interest, BetaReadingListingDecision::DECLINED);

        return back();
    }

    public function end(Book $book, BetaReadingListing $listing, BetaReadingListingDecision $interest): RedirectResponse
    {
        $this->authorizeInterest($book, $listing, $interest);
        $interest->match()->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Match ended.')]);

        return back();
    }

    private function decide(Book $book, BetaReadingListing $listing, BetaReadingListingDecision $interest, string $decision): void
    {
        $this->authorizeInterest($book, $listing, $interest);

        DB::transaction(function () use ($interest, $decision): void {
            $interest = BetaReadingListingDecision::query()->lockForUpdate()->findOrFail($interest->id);

            if ($interest->author_decision !== null) {
                return;
            }

            $interest->update(['author_decision' => $decision]);

            if ($decision === BetaReadingListingDecision::APPROVED) {
                $interest->match()->create();
            }
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

    /** @return array{id: int, created_at: string|null, profile: array<string, mixed>} */
    private function readerData(BetaReadingListingDecision $interest): array
    {
        return [
            'id' => $interest->id,
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
