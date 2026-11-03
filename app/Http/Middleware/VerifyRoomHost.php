<?php

namespace App\Http\Middleware;

use App\Models\Room;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyRoomHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $uuid = $request->route('uuid');

        $room = Room::where('uuid', $uuid)->first();

        if (!$room) {
            return response()->json(['error' => 'Room not found.'], 404);
        }

        $token = session("host_token_{$uuid}");

        if (!$token || !$room->isHost($token)) {
            return response()->json(['error' => 'Unauthorized. Host access required.'], 403);
        }

        return $next($request);
    }
}
