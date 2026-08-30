<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $display_name
 * @property string $handle
 * @property string|null $bio
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['display_name', 'handle', 'bio'])]
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

    public function getRouteKeyName(): string
    {
        return 'handle';
    }
}
