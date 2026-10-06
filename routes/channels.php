<?php

use App\Models\BetaReadingMatch;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('users.{userId}', fn (User $user, int $userId): bool => $user->id === $userId);

Broadcast::channel('matches.{matchId}', function (User $user, int $matchId): bool {
    $match = BetaReadingMatch::find($matchId);

    return $match !== null && $user->can('view', $match);
});
