<?php

namespace App\Actions\BetaReadingListings;

use App\Models\BetaReadingMatch;
use Illuminate\Support\Facades\DB;

class EndBetaReadingMatch
{
    public function handle(BetaReadingMatch $match): bool
    {
        return DB::transaction(function () use ($match): bool {
            $match = BetaReadingMatch::query()->lockForUpdate()->findOrFail($match->id);

            if ($match->ended_at !== null) {
                return false;
            }

            $match->update(['ended_at' => now()]);

            return true;
        });
    }
}
