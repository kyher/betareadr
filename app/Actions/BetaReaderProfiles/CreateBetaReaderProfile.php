<?php

namespace App\Actions\BetaReaderProfiles;

use App\Models\BetaReaderProfile;
use App\Models\User;

class CreateBetaReaderProfile
{
    /** @param array{display_name: string, handle: string, bio?: string|null, reading_languages: list<string>, preferred_genres: list<string>} $attributes */
    public function handle(User $user, array $attributes): BetaReaderProfile
    {
        return $user->betaReaderProfile()->create($attributes);
    }
}
