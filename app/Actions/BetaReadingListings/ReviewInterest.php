<?php

namespace App\Actions\BetaReadingListings;

use App\Enums\BetaReadingListingAuthorDecision;
use App\Enums\BetaReadingListingReaderDecisionType;
use App\Models\BetaReadingListingReaderDecision;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use LogicException;

class ReviewInterest
{
    public function handle(
        BetaReadingListingReaderDecision $interest,
        BetaReadingListingAuthorDecision $decision,
    ): bool {
        return DB::transaction(function () use ($interest, $decision): bool {
            // Serialize approval with User deletion before creating a new Match.
            $participantIds = [
                $interest->betaReaderProfile->user_id,
                $interest->betaReadingListing->book->authorProfile->user_id,
            ];
            $participants = User::query()->whereKey($participantIds)->orderBy('id')->lockForUpdate()->get();
            abort_unless($participants->count() === count(array_unique($participantIds)), 409);

            $interest = BetaReadingListingReaderDecision::query()->lockForUpdate()->findOrFail($interest->id);

            if ($interest->reader_decision !== BetaReadingListingReaderDecisionType::Interested) {
                throw new LogicException('Only Interest can be reviewed.');
            }

            if ($interest->author_decision !== null) {
                return false;
            }

            $interest->update(['author_decision' => $decision]);

            if ($decision === BetaReadingListingAuthorDecision::Approved) {
                $interest->match()->create();
            }

            return true;
        });
    }
}
