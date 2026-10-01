<?php

namespace App\Models;

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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['avatar_path', 'display_name', 'handle', 'bio'])]
class AuthorProfile extends Model
{
    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<Book, $this> */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar_path === null ? null : Storage::disk('r2')->url($this->avatar_path);
    }

    public function getRouteKeyName(): string
    {
        return 'handle';
    }
}
