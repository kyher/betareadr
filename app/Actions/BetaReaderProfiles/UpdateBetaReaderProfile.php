<?php

namespace App\Actions\BetaReaderProfiles;

use App\Models\BetaReaderProfile;

class UpdateBetaReaderProfile
{
    /** @param array{display_name: string, bio?: string|null, reading_languages: list<string>} $attributes */
    public function handle(BetaReaderProfile $betaReaderProfile, array $attributes): BetaReaderProfile
    {
        $betaReaderProfile->update($attributes);

        return $betaReaderProfile;
    }
}
