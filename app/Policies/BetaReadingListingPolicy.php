<?php

namespace App\Policies;

use App\Enums\BetaReadingListingReaderDecisionType;
use App\Models\BetaReadingListing;
use App\Models\BetaReadingListingReaderDecision;
use App\Models\Book;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BetaReadingListingPolicy
{
    public function manage(User $user, BetaReadingListing $listing, Book $book): Response
    {
        if ($listing->book_id !== $book->id) {
            return Response::denyAsNotFound();
        }

        return $this->ownerResponse($user, $listing);
    }

    public function reviewInterest(
        User $user,
        BetaReadingListing $listing,
        Book $book,
        BetaReadingListingReaderDecision $interest,
    ): Response {
        if ($listing->book_id !== $book->id
            || $interest->beta_reading_listing_id !== $listing->id
            || $interest->reader_decision !== BetaReadingListingReaderDecisionType::Interested) {
            return Response::denyAsNotFound();
        }

        return $this->ownerResponse($user, $listing);
    }

    private function ownerResponse(User $user, BetaReadingListing $listing): Response
    {
        return $listing->book->authorProfile->user_id === $user->id
            ? Response::allow()
            : Response::deny();
    }
}
