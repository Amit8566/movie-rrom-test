<?php

use App\Models\Room;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('room.{uuid}', function ($user, $uuid) {
    $room = Room::where('uuid', $uuid)->first();

    if (!$room || !$room->isActive()) {
        return false;
    }

    if ($user && $user->room_id === $room->id) {
        $hostToken = session("host_token_{$uuid}", '');
        $isHost = $room->isHost($hostToken);

        return [
            'id' => $user->id,
            'name' => $user->display_name,
            'is_host' => $isHost,
            'is_muted' => (bool) $user->is_muted,
        ];
    }

    return false;
});
