<?php

namespace App\Models;

use App\Enums\BetaReadingListingAuthorDecision;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $beta_reader_profile_id
 * @property int $beta_reading_listing_id
 * @property string $decision
 * @property BetaReadingListingAuthorDecision|null $author_decision
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property BetaReaderProfile $betaReaderProfile
 * @property BetaReadingMatch|null $match
 */
#[Fillable(['beta_reading_listing_id', 'decision', 'author_decision'])]
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

    /** @return HasOne<BetaReadingMatch, $this> */
    public function match(): HasOne
    {
        return $this->hasOne(BetaReadingMatch::class);
    }

    protected function casts(): array
    {
        return ['author_decision' => BetaReadingListingAuthorDecision::class];
    }
}
