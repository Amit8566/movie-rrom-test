<?php

namespace App\Events;

use App\Models\Room;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlaybackUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $action;
    public float $currentTime;
    public string $status;

    public function __construct(public Room $room, string $action, float $currentTime)
    {
        $this->action = $action;
        $this->currentTime = $currentTime;
        $this->status = $room->status;
    }

    public function broadcastOn(): array
    {
        return [new PresenceChannel('room.' . $this->room->uuid)];
    }

    public function broadcastAs(): string
    {
        return 'PlaybackUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'current_time' => $this->currentTime,
            'status' => $this->status,
        ];
    }
}
