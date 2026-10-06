<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $locale
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'locale', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const string AUTH_GUARD = 'web';

    public function unreadMessageCount(): int
    {
        return DB::table('conversation_messages as messages')
            ->join('beta_reading_matches as matches', 'matches.id', '=', 'messages.beta_reading_match_id')
            ->join('beta_reading_listing_decisions as decisions', 'decisions.id', '=', 'matches.beta_reading_listing_decision_id')
            ->join('beta_reading_listings as listings', 'listings.id', '=', 'decisions.beta_reading_listing_id')
            ->join('books', 'books.id', '=', 'listings.book_id')
            ->join('author_profiles as authors', 'authors.id', '=', 'books.author_profile_id')
            ->join('beta_reader_profiles as readers', 'readers.id', '=', 'decisions.beta_reader_profile_id')
            ->where('messages.sender_id', '!=', $this->id)
            ->where(fn ($query) => $query
                ->where(fn ($query) => $query->where('authors.user_id', $this->id)->whereColumn('messages.id', '>', 'matches.author_read_message_id'))
                ->orWhere(fn ($query) => $query->where('readers.user_id', $this->id)->whereColumn('messages.id', '>', 'matches.reader_read_message_id')))
            ->count();
    }

    /** @return HasOne<AuthorProfile, $this> */
    public function authorProfile(): HasOne
    {
        return $this->hasOne(AuthorProfile::class);
    }

    /** @return HasOne<BetaReaderProfile, $this> */
    public function betaReaderProfile(): HasOne
    {
        return $this->hasOne(BetaReaderProfile::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
