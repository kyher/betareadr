<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $beta_reader_profile_id
 * @property int $beta_reading_listing_id
 * @property string $decision
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['beta_reading_listing_id', 'decision'])]
class BetaReadingListingDecision extends Model
{
    public const INTERESTED = 'interested';

    public const PASSED = 'passed';

    /** @return BelongsTo<BetaReaderProfile, $this> */
    public function betaReaderProfile(): BelongsTo
    {
        return $this->belongsTo(BetaReaderProfile::class);
    }

    /** @return BelongsTo<BetaReadingListing, $this> */
    public function betaReadingListing(): BelongsTo
    {
        return $this->belongsTo(BetaReadingListing::class);
    }
}
