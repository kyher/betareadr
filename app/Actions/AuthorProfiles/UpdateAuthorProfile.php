<?php

namespace App\Actions\AuthorProfiles;

use App\Actions\Profiles\SaveProfile;
use App\Models\AuthorProfile;
use Illuminate\Http\UploadedFile;

class UpdateAuthorProfile
{
    public function __construct(private SaveProfile $saveProfile) {}

    /** @param array{display_name: string, bio?: string|null} $attributes */
    public function handle(AuthorProfile $authorProfile, array $attributes, ?UploadedFile $avatar = null, bool $removeAvatar = false): AuthorProfile
    {
        $this->saveProfile->handle($authorProfile, $attributes, $avatar, $removeAvatar);

        return $authorProfile;
    }
}
