<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $beta_reading_listing_decision_id
 * @property Carbon|null $ended_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $author_read_message_id
 * @property int $reader_read_message_id
 * @property BetaReadingListingReaderDecision $readerDecision
 */
#[Fillable(['ended_at'])]
class BetaReadingMatch extends Model
{
    /** @return BelongsTo<BetaReadingListingReaderDecision, $this> */
    public function readerDecision(): BelongsTo
    {
        return $this->belongsTo(BetaReadingListingReaderDecision::class, 'beta_reading_listing_decision_id');
    }

    /** @return HasMany<ConversationMessage, $this> */
    public function messages(): HasMany
    {
        return $this->hasMany(ConversationMessage::class);
    }

    protected function casts(): array
    {
        return ['ended_at' => 'datetime'];
    }
}
