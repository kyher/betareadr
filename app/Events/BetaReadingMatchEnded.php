<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class BetaReadingMatchEnded implements ShouldBroadcastNow
{
    public function __construct(public int $matchId) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('matches.'.$this->matchId)];
    }

    /** @return array{match_id: int} */
    public function broadcastWith(): array
    {
        return ['match_id' => $this->matchId];
    }
}
