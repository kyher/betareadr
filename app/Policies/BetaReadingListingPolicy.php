<?php

namespace App\Policies;

use App\Models\BetaReadingListing;
use App\Models\User;

class BetaReadingListingPolicy
{
    public function manage(User $user, BetaReadingListing $listing): bool
    {
        return $listing->book->authorProfile->user_id === $user->id;
    }
}
