<?php

namespace App\Actions\BetaReaderProfiles;

use App\Actions\Profiles\SaveProfile;
use App\Models\BetaReaderProfile;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class CreateBetaReaderProfile
{
    public function __construct(private SaveProfile $saveProfile) {}

    /** @param array{display_name: string, handle: string, bio?: string|null, reading_languages: list<string>, preferred_genres: list<string>} $attributes */
    public function handle(User $user, array $attributes, ?UploadedFile $avatar = null): BetaReaderProfile
    {
        $profile = $user->betaReaderProfile()->make();
        $this->saveProfile->handle($profile, $attributes, $avatar);

        return $profile;
    }
}
