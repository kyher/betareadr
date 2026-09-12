<?php

namespace App\Policies;

use App\Models\BetaReadingMatch;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BetaReadingMatchPolicy
{
    public function end(User $user, BetaReadingMatch $match): Response
    {
        $decision = $match->readerDecision;

        return $decision->betaReaderProfile->user_id === $user->id
            || $decision->betaReadingListing->book->authorProfile->user_id === $user->id
            ? Response::allow()
            : Response::deny();
    }
}
