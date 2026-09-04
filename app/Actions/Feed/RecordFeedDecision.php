<?php

namespace App\Actions\Feed;

use App\Models\BetaReaderProfile;
use App\Models\BetaReadingListing;
use App\Models\BetaReadingListingDecision;
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
        string $decision,
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

            $existing = $profile->decisions()
                ->where('beta_reading_listing_id', $listing->id)
                ->first();

            if ($existing !== null) {
                return self::EXISTING;
            }

            if ($decision === BetaReadingListingDecision::INTERESTED
                && ! $profile->acceptsLanguage($listing->book->language)) {
                return self::INELIGIBLE;
            }

            $profile->decisions()->create([
                'beta_reading_listing_id' => $listing->id,
                'decision' => $decision,
            ]);

            return self::CREATED;
        });
    }
}
