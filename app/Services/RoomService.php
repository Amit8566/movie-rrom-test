<?php

namespace App\Services;

use App\Models\Participant;
use App\Models\Room;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class RoomService
{
    /** Create a new room and return it along with the host token. */
    public function createRoom(string $name, string $videoUrl, string $hostName): array
    {
        $token = Str::random(64);

        $room = Room::create([
            'name' => $name,
            'video_url' => $videoUrl,
            'host_name' => $hostName,
            'host_token' => $token,
            'status' => 'waiting',
            'current_time' => 0,
            'chat_enabled' => true,
        ]);

        return ['room' => $room, 'host_token' => $token];
    }

    /** Join a room, or rejoin if the session already exists. */
    public function joinRoom(Room $room, string $displayName, string $sessionId): Participant
    {
        $participant = $room->participants()
            ->where('session_id', $sessionId)
            ->first();

        if ($participant) {
            $participant->update([
                'display_name' => $displayName,
                'left_at' => null,
            ]);

            return $participant;
        }

        return $room->participants()->create([
            'session_id' => $sessionId,
            'display_name' => $displayName,
            'is_muted' => false,
            'joined_at' => now(),
        ]);
    }

    /** Mark a participant as having left the room. */
    public function leaveRoom(Room $room, string $sessionId): void
    {
        $room->participants()
            ->where('session_id', $sessionId)
            ->whereNull('left_at')
            ->update(['left_at' => now()]);
    }

    /** Get all currently active participants in a room. */
    public function getRoomParticipants(Room $room): Collection
    {
        return $room->participants()
            ->whereNull('left_at')
            ->get();
    }
}
