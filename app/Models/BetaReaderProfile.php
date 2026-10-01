<?php

namespace App\Models;

use App\Enums\ContentLanguage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $user_id
 * @property string $display_name
 * @property string $handle
 * @property string|null $avatar_path
 * @property string|null $bio
 * @property list<string> $reading_languages
 * @property list<string> $preferred_genres
 * @property list<string> $content_boundaries
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['avatar_path', 'display_name', 'handle', 'bio', 'reading_languages', 'preferred_genres', 'content_boundaries'])]
class BetaReaderProfile extends Model
{
    public const ANY_LANGUAGE = '*';

    protected $attributes = ['content_boundaries' => '[]', 'preferred_genres' => '[]'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<BetaReadingListingReaderDecision, $this> */
    public function readerDecisions(): HasMany
    {
        return $this->hasMany(BetaReadingListingReaderDecision::class);
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar_path === null ? null : Storage::disk('r2')->url($this->avatar_path);
    }

    public function getRouteKeyName(): string
    {
        return 'handle';
    }

    public function acceptsLanguage(ContentLanguage $language): bool
    {
        return in_array(self::ANY_LANGUAGE, $this->reading_languages, true)
            || in_array($language->value, $this->reading_languages, true);
    }

    protected function casts(): array
    {
        return ['reading_languages' => 'array', 'preferred_genres' => 'array', 'content_boundaries' => 'array'];
    }
}
