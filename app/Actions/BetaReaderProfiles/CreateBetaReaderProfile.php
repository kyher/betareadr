<?php

namespace App\Actions\BetaReaderProfiles;

use App\Models\BetaReaderProfile;
use App\Models\User;

class CreateBetaReaderProfile
{
    /** @param array{display_name: string, handle: string, bio?: string|null} $attributes */
    public function handle(User $user, array $attributes): BetaReaderProfile
    {
        return $user->betaReaderProfile()->create($attributes);
    }
}
