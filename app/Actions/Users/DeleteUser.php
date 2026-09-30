<?php

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;

class DeleteUser
{
    public function handle(User $user): void
    {
        $coverImagePaths = DB::transaction(function () use ($user): array {
            $user = User::query()->lockForUpdate()->findOrFail($user->id);
            $coverImagePaths = $user->authorProfile?->books()
                ->whereNotNull('cover_image_path')
                ->pluck('cover_image_path')
                ->all() ?? [];

            Password::broker('users')->deleteToken($user);
            DB::connection(config('session.connection'))
                ->table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
            $user->delete();

            return $coverImagePaths;
        });

        if ($coverImagePaths !== []) {
            Storage::disk('r2')->delete($coverImagePaths);
        }
    }
}
