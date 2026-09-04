<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $author_profile_id
 * @property string $title
 * @property string|null $description
 * @property string $language
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['title', 'description', 'language'])]
class Book extends Model
{
    /** @return BelongsTo<AuthorProfile, $this> */
    public function authorProfile(): BelongsTo
    {
        return $this->belongsTo(AuthorProfile::class);
    }

    /** @return HasMany<BetaReadingListing, $this> */
    public function betaReadingListings(): HasMany
    {
        return $this->hasMany(BetaReadingListing::class);
    }
}
