<?php

namespace App\Actions\BetaReadingListings;

use App\Models\BetaReadingListing;

class DeleteBetaReadingListing
{
    public function handle(BetaReadingListing $listing): bool
    {
        if ($listing->hasActiveMatch()) {
            return false;
        }

        $listing->delete();

        return true;
    }
}
