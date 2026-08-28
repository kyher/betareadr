<?php

namespace App\Actions\AuthorProfiles;

use App\Models\AuthorProfile;
use App\Models\User;

class CreateAuthorProfile
{
    /** @param array{display_name: string, handle: string, bio?: string|null} $attributes */
    public function handle(User $user, array $attributes): AuthorProfile
    {
        return $user->authorProfile()->create($attributes);
    }
}
