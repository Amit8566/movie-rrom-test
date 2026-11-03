<?php

namespace App\Events;

use App\Models\Room;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ParticipantMuted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $participantId;
    public bool $isMuted;

    public function __construct(public Room $room, int $participantId, bool $isMuted)
    {
        $this->participantId = $participantId;
        $this->isMuted = $isMuted;
    }

    public function broadcastOn(): array
    {
        return [new PresenceChannel('room.' . $this->room->uuid)];
    }

    public function broadcastWith(): array
    {
        return [
            'participant_id' => $this->participantId,
            'is_muted' => $this->isMuted,
        ];
    }
}
