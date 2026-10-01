<?php

namespace App\Actions\Profiles;

use App\Models\AuthorProfile;
use App\Models\BetaReaderProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class SaveProfile
{
    /** @param array<string, mixed> $attributes */
    public function handle(AuthorProfile|BetaReaderProfile $profile, array $attributes, ?UploadedFile $avatar = null, bool $removeAvatar = false): void
    {
        $newPath = $avatar?->store('profile-avatars', 'r2');

        if ($newPath === false) {
            throw new RuntimeException('Could not store profile avatar.');
        }

        try {
            $oldPath = DB::transaction(function () use ($profile, $attributes, $newPath, $removeAvatar): ?string {
                if ($profile->exists) {
                    $lockedProfile = $profile->newQuery()->lockForUpdate()->findOrFail($profile->id);
                    $profile->setRawAttributes($lockedProfile->getAttributes(), true);
                }

                $oldPath = $profile->avatar_path;
                $avatarPath = $newPath ?? ($removeAvatar ? null : $oldPath);

                $profile->fill([...$attributes, 'avatar_path' => $avatarPath])->save();

                return $oldPath !== $avatarPath ? $oldPath : null;
            });
        } catch (Throwable $exception) {
            if ($newPath !== null) {
                Storage::disk('r2')->delete($newPath);
            }

            throw $exception;
        }

        if ($oldPath !== null) {
            Storage::disk('r2')->delete($oldPath);
        }
    }
}
