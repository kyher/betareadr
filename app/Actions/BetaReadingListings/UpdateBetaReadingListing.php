<?php

namespace App\Actions\BetaReadingListings;

use App\Models\BetaReadingListing;

class UpdateBetaReadingListing
{
    /** @param array{reading_brief: string, word_count: int} $attributes */
    public function handle(BetaReadingListing $listing, array $attributes): void
    {
        $listing->update($attributes);
    }
}
