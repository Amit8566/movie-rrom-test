<?php

namespace App\Events;

use App\Models\Room;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatToggled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public bool $chatEnabled;

    public function __construct(public Room $room, bool $chatEnabled)
    {
        $this->chatEnabled = $chatEnabled;
    }

    public function broadcastOn(): array
    {
        return [new PresenceChannel('room.' . $this->room->uuid)];
    }

    public function broadcastWith(): array
    {
        return [
            'chat_enabled' => $this->chatEnabled,
        ];
    }
}
