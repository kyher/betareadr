<?php

namespace App\Actions\BetaReadingListings;

use App\Models\BetaReadingListing;
use App\Models\Book;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateBetaReadingListing
{
    /** @param array{reading_brief: string, word_count: int} $attributes */
    public function handle(Book $book, array $attributes): BetaReadingListing
    {
        return DB::transaction(function () use ($book, $attributes): BetaReadingListing {
            $lockedBook = Book::query()->lockForUpdate()->findOrFail($book->id);

            if ($lockedBook->betaReadingListings()->whereNull('closed_at')->exists()) {
                throw ValidationException::withMessages([
                    'listing' => __('Close the current Listing before creating another one.'),
                ]);
            }

            return $lockedBook->betaReadingListings()->create($attributes);
        });
    }
}
