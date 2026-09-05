<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $beta_reading_listing_decision_id
 * @property Carbon|null $ended_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property BetaReadingListingDecision $decision
 */
#[Fillable(['ended_at'])]
class BetaReadingMatch extends Model
{
    /** @return BelongsTo<BetaReadingListingDecision, $this> */
    public function decision(): BelongsTo
    {
        return $this->belongsTo(BetaReadingListingDecision::class, 'beta_reading_listing_decision_id');
    }

    protected function casts(): array
    {
        return ['ended_at' => 'datetime'];
    }
}
