<?php

namespace App\Actions\BetaReaderProfiles;

use App\Actions\Profiles\SaveProfile;
use App\Models\BetaReaderProfile;
use Illuminate\Http\UploadedFile;

class UpdateBetaReaderProfile
{
    public function __construct(private SaveProfile $saveProfile) {}

    /** @param array{display_name: string, bio?: string|null, reading_languages: list<string>, preferred_genres: list<string>} $attributes */
    public function handle(BetaReaderProfile $betaReaderProfile, array $attributes, ?UploadedFile $avatar = null, bool $removeAvatar = false): BetaReaderProfile
    {
        $this->saveProfile->handle($betaReaderProfile, $attributes, $avatar, $removeAvatar);

        return $betaReaderProfile;
    }
}
