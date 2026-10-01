<?php

namespace App\Actions\AuthorProfiles;

use App\Actions\Profiles\SaveProfile;
use App\Models\AuthorProfile;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class CreateAuthorProfile
{
    public function __construct(private SaveProfile $saveProfile) {}

    /** @param array{display_name: string, handle: string, bio?: string|null} $attributes */
    public function handle(User $user, array $attributes, ?UploadedFile $avatar = null): AuthorProfile
    {
        $profile = $user->authorProfile()->make();
        $this->saveProfile->handle($profile, $attributes, $avatar);

        return $profile;
    }
}
