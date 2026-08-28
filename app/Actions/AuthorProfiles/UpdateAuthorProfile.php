<?php

namespace App\Actions\AuthorProfiles;

use App\Models\AuthorProfile;

class UpdateAuthorProfile
{
    /** @param array{display_name: string, bio?: string|null} $attributes */
    public function handle(AuthorProfile $authorProfile, array $attributes): AuthorProfile
    {
        $authorProfile->update($attributes);

        return $authorProfile;
    }
}
