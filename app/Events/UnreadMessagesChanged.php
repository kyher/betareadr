<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class UnreadMessagesChanged implements ShouldBroadcastNow
{
    public function __construct(public int $userId) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('users.'.$this->userId)];
    }

    /** @return array{} */
    public function broadcastWith(): array
    {
        return [];
    }
}
