<?php

namespace App\Http\Controllers;

use App\Events\PlaybackUpdated;
use App\Events\SessionEnded;
use App\Http\Requests\PlaybackActionRequest;
use App\Models\Room;
use App\Services\PlaybackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlaybackController extends Controller
{
    public function __construct(
        private PlaybackService $playbackService
    ) {}

    /**
     * Update room playback state and broadcast to all participants.
     */
    public function update(PlaybackActionRequest $request, string $uuid): JsonResponse
    {
        $room = Room::where('uuid', $uuid)->firstOrFail();

        if (!$room->isActive()) {
            return response()->json(['error' => 'This session has ended.'], 400);
        }

        $action = $request->validated('action');
        $currentTime = (float) $request->validated('current_time');

        $this->playbackService->updatePlayback($room, $action, $currentTime);
        $room->refresh();

        // Broadcast to all viewers in real time
        broadcast(new PlaybackUpdated($room, $action, $currentTime))->toOthers();

        return response()->json([
            'success' => true,
            'status' => $room->status,
            'current_time' => $room->current_time,
            'action' => $action,
        ]);
    }

    /**
     * End the movie room session entirely.
     */
    public function end(Request $request, string $uuid): JsonResponse
    {
        $room = Room::where('uuid', $uuid)->firstOrFail();

        $this->playbackService->endSession($room);
        $room->refresh();

        // Broadcast session end to immediately notify participants
        broadcast(new SessionEnded($room));

        return response()->json([
            'success' => true,
            'message' => 'Session has been ended successfully.',
        ]);
    }
}
