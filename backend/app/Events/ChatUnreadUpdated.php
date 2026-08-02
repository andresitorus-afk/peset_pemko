<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class ChatUnreadUpdated implements ShouldBroadcastNow
{
    use SerializesModels;

    public function __construct(public string $sessionId)
    {
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('chat.staff');
    }

    public function broadcastWith(): array
    {
        return ['session_id' => $this->sessionId];
    }
}
