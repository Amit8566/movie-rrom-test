<?php

namespace App\Services;

use App\Models\Room;

class PlaybackService
{
    /** Update playback state based on the given action. */
    public function updatePlayback(Room $room, string $action, float $currentTime): void
    {
        $statusMap = [
            'play' => 'playing',
            'pause' => 'paused',
            'stop' => 'waiting',
        ];

        $status = $statusMap[$action] ?? $room->status;
        $time = ($action === 'stop') ? 0 : $currentTime;

        $room->update([
            'status' => $status,
            'current_time' => $time,
        ]);
    }

    /** End the room session entirely. */
    public function endSession(Room $room): void
    {
        $room->update([
            'status' => 'ended',
            'chat_enabled' => false,
        ]);
    }
}
