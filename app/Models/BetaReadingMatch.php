<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $beta_reading_listing_decision_id
 * @property int|null $remaining_user_id
 * @property string|null $remaining_role
 * @property string|null $retained_book_title
 * @property Carbon|null $ended_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $author_read_message_id
 * @property int $reader_read_message_id
 * @property int|null $ended_by_user_id
 * @property BetaReadingListingReaderDecision $readerDecision
 */
#[Fillable(['ended_at', 'ended_by_user_id'])]
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

    /** @return HasOne<ConversationMessage, $this> */
    public function latestMessage(): HasOne
    {
        return $this->hasOne(ConversationMessage::class)->latestOfMany();
    }

    protected function casts(): array
    {
        return ['ended_at' => 'datetime'];
    }
}
