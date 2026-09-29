<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $book_id
 * @property string $reading_brief
 * @property int $word_count
 * @property Carbon|null $closed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['reading_brief', 'word_count', 'closed_at'])]
class BetaReadingListing extends Model
{
    /** @return BelongsTo<Book, $this> */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /** @return HasMany<BetaReadingListingReaderDecision, $this> */
    public function readerDecisions(): HasMany
    {
        return $this->hasMany(BetaReadingListingReaderDecision::class);
    }

    public function hasActiveMatch(): bool
    {
        return $this->readerDecisions()->whereHas('match', fn ($match) => $match->whereNull('ended_at'))->exists();
    }

    protected function casts(): array
    {
        return [
            'word_count' => 'integer',
            'closed_at' => 'datetime',
        ];
    }
}
