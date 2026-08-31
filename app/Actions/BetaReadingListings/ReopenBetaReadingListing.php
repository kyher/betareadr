<?php

namespace App\Actions\BetaReadingListings;

use App\Models\BetaReadingListing;
use App\Models\Book;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReopenBetaReadingListing
{
    public function handle(BetaReadingListing $listing): void
    {
        DB::transaction(function () use ($listing): void {
            $book = Book::query()->lockForUpdate()->findOrFail($listing->book_id);

            if ($book->betaReadingListings()
                ->whereNull('closed_at')
                ->whereKeyNot($listing->id)
                ->exists()) {
                throw ValidationException::withMessages([
                    'listing' => __('Close the current Listing before reopening another one.'),
                ]);
            }

            $listing->update(['closed_at' => null]);
        });
    }
}
