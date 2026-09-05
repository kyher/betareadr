<?php

namespace App\Actions\Feed;

use App\Enums\BetaReadingListingReaderDecisionType;
use App\Models\BetaReaderProfile;
use App\Models\BetaReadingListing;
use Illuminate\Support\Facades\DB;

class RecordFeedDecision
{
    public const CLOSED = 'closed';

    public const CREATED = 'created';

    public const EXISTING = 'existing';

    public const INELIGIBLE = 'ineligible';

    public function handle(
        BetaReaderProfile $profile,
        BetaReadingListing $listing,
        BetaReadingListingReaderDecisionType $decision,
    ): string {
        return DB::transaction(function () use ($profile, $listing, $decision): string {
            $profile = BetaReaderProfile::query()->lockForUpdate()->findOrFail($profile->id);
            $listing = BetaReadingListing::query()
                ->with('book.authorProfile')
                ->lockForUpdate()
                ->findOrFail($listing->id);

            if ($listing->closed_at !== null) {
                return self::CLOSED;
            }

            abort_if($listing->book->authorProfile->user_id === $profile->user_id, 403);

            $existing = $profile->readerDecisions()
                ->where('beta_reading_listing_id', $listing->id)
                ->first();

            if ($existing !== null) {
                return self::EXISTING;
            }

            if ($decision === BetaReadingListingReaderDecisionType::Interested
                && ! $profile->acceptsLanguage($listing->book->language)) {
                return self::INELIGIBLE;
            }

            $profile->readerDecisions()->create([
                'beta_reading_listing_id' => $listing->id,
                'reader_decision' => $decision,
            ]);

            return self::CREATED;
        });
    }
}
