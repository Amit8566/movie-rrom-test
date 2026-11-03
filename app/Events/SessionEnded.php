<?php

namespace App\Events;

use App\Models\Room;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionEnded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Room $room)
    {
        //
    }

    public function broadcastOn(): array
    {
        return [new PresenceChannel('room.' . $this->room->uuid)];
    }

    public function broadcastWith(): array
    {
        return [
            'message' => 'The host has ended this session.',
        ];
    }
}
