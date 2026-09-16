<?php

namespace App\Actions\BetaReadingListings;

use App\Events\BetaReadingMatchEnded;
use App\Models\BetaReadingMatch;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class EndBetaReadingMatch
{
    public function handle(BetaReadingMatch $match, User $ender): bool
    {
        $ended = DB::transaction(function () use ($match, $ender): bool {
            $match = BetaReadingMatch::query()->lockForUpdate()->findOrFail($match->id);

            if ($match->ended_at !== null) {
                return false;
            }

            $match->update(['ended_at' => now(), 'ended_by_user_id' => $ender->id]);

            return true;
        });

        if ($ended) {
            DB::afterCommit(function () use ($match): void {
                try {
                    event(new BetaReadingMatchEnded($match->id));
                } catch (Throwable $exception) {
                    Log::warning('Match end notification failed', ['match_id' => $match->id, 'exception' => $exception]);
                }
            });
        }

        return $ended;
    }
}
