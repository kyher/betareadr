<?php

namespace App\Http\Controllers;

use App\Actions\BetaReadingListings\CreateBetaReadingListing;
use App\Actions\BetaReadingListings\ReopenBetaReadingListing;
use App\Actions\BetaReadingListings\UpdateBetaReadingListing;
use App\Enums\BetaReadingListingReaderDecisionType;
use App\Http\Requests\StoreBetaReadingListingRequest;
use App\Http\Requests\UpdateBetaReadingListingRequest;
use App\Models\BetaReadingListing;
use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class BetaReadingListingController extends Controller
{
    public function index(Book $book): Response
    {
        $this->authorizeBook($book);

        return Inertia::render('beta-reading-listings/Index', [
            'book' => $book->only(['id', 'title']),
            'hasOpenListing' => $book->betaReadingListings()->whereNull('closed_at')->exists(),
            'listings' => $book->betaReadingListings()
                ->withCount([
                    'readerDecisions as interested_count' => fn ($query) => $query
                        ->where('reader_decision', BetaReadingListingReaderDecisionType::Interested)
                        ->whereNull('author_decision'),
                    'readerDecisions as approved_count' => fn ($query) => $query
                        ->whereHas('match', fn ($match) => $match->whereNull('ended_at')),
                ])
                ->orderByRaw('closed_at IS NULL DESC')
                ->latest('created_at')
                ->latest('id')
                ->paginate(12)
                ->through(fn (BetaReadingListing $listing): array => $this->listingData($listing)),
        ]);
    }

    public function create(Book $book): Response|RedirectResponse
    {
        $this->authorizeBook($book);

        if ($book->betaReadingListings()->whereNull('closed_at')->exists()) {
            return to_route('books.listings.index', $book)
                ->withErrors(['listing' => __('Close the current Listing before creating another one.')]);
        }

        return Inertia::render('beta-reading-listings/Create', [
            'book' => $book->only(['id', 'title']),
        ]);
    }

    public function store(
        StoreBetaReadingListingRequest $request,
        Book $book,
        CreateBetaReadingListing $createListing,
    ): RedirectResponse {
        $createListing->handle($book, $request->betaReadingListingAttributes());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Listing created.')]);

        return to_route('books.listings.index', $book);
    }

    public function edit(Book $book, BetaReadingListing $listing): Response
    {
        $this->authorizeListing($book, $listing);

        return Inertia::render('beta-reading-listings/Edit', [
            'book' => $book->only(['id', 'title']),
            'listing' => $this->listingData($listing),
        ]);
    }

    public function update(
        UpdateBetaReadingListingRequest $request,
        Book $book,
        BetaReadingListing $listing,
        UpdateBetaReadingListing $updateListing,
    ): RedirectResponse {
        $updateListing->handle($listing, $request->betaReadingListingAttributes());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Listing updated.')]);

        return to_route('books.listings.index', $book);
    }

    public function close(Book $book, BetaReadingListing $listing): RedirectResponse
    {
        $this->authorizeListing($book, $listing);
        $listing->update(['closed_at' => $listing->closed_at ?? now()]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Listing closed.')]);

        return to_route('books.listings.index', $book);
    }

    public function reopen(
        Book $book,
        BetaReadingListing $listing,
        ReopenBetaReadingListing $reopenListing,
    ): RedirectResponse {
        $this->authorizeListing($book, $listing);
        $reopenListing->handle($listing);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Listing reopened.')]);

        return to_route('books.listings.index', $book);
    }

    private function authorizeBook(Book $book): void
    {
        abort_unless($book->authorProfile->user_id === request()->user()->id, 403);
    }

    private function authorizeListing(Book $book, BetaReadingListing $listing): void
    {
        Gate::authorize('manage', [$listing, $book]);
    }

    /** @return array{id: int, reading_brief: string, word_count: int, closed_at: string|null, created_at: string|null, interested_count: int, approved_count: int} */
    private function listingData(BetaReadingListing $listing): array
    {
        return [
            'id' => $listing->id,
            'reading_brief' => $listing->reading_brief,
            'word_count' => $listing->word_count,
            'closed_at' => $listing->closed_at?->toISOString(),
            'created_at' => $listing->created_at?->toISOString(),
            'interested_count' => (int) $listing->getAttribute('interested_count'),
            'approved_count' => (int) $listing->getAttribute('approved_count'),
        ];
    }
}
