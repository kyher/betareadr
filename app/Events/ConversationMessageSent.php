<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ConversationMessageSent implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    public function __construct(public int $matchId, public int $messageId, public int $recipientUserId) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('matches.'.$this->matchId),
            new PrivateChannel('users.'.$this->recipientUserId),
        ];
    }

    /** @return array{match_id: int, message_id: int} */
    public function broadcastWith(): array
    {
        return ['match_id' => $this->matchId, 'message_id' => $this->messageId];
    }
}
