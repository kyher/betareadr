<?php

namespace App\Models;

use App\Enums\ContentLanguage;
use App\Enums\Genre;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $author_profile_id
 * @property string $title
 * @property string|null $description
 * @property ContentLanguage $language
 * @property Genre $genre
 * @property list<string> $content_warnings
 * @property string|null $cover_image_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['title', 'description', 'language', 'genre', 'content_warnings', 'cover_image_path'])]
class Book extends Model
{
    protected $attributes = ['content_warnings' => '[]'];

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

    public function hasActiveMatch(): bool
    {
        return $this->betaReadingListings()
            ->whereHas('readerDecisions.match', fn ($match) => $match->whereNull('ended_at'))
            ->exists();
    }

    public function coverImageUrl(): ?string
    {
        return $this->cover_image_path === null ? null : Storage::disk('r2')->url($this->cover_image_path);
    }

    protected function casts(): array
    {
        return ['language' => ContentLanguage::class, 'genre' => Genre::class, 'content_warnings' => 'array'];
    }
}
