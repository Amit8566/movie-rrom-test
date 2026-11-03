<?php

namespace App\Http\Controllers;

use App\Events\ChatToggled;
use App\Events\MessageDeleted;
use App\Events\MessageSent;
use App\Events\ParticipantMuted;
use App\Http\Requests\SendMessageRequest;
use App\Models\ChatMessage;
use App\Models\Participant;
use App\Models\Room;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class ChatController extends Controller
{
    public function __construct(
        private ChatService $chatService
    ) {}

    /**
     * Send a new message to the room chat.
     */
    public function store(SendMessageRequest $request, string $uuid): JsonResponse
    {
        $room = Room::where('uuid', $uuid)->firstOrFail();

        if (!$room->isActive()) {
            return response()->json(['error' => 'This room session has ended.'], 403);
        }

        if (!$room->chat_enabled) {
            return response()->json(['error' => 'Chat is currently disabled for this room.'], 403);
        }

        $participantId = session("participant_{$room->uuid}");
        $participant = $room->participants()->find($participantId);

        if (!$participant) {
            return response()->json(['error' => 'You must join the room before sending messages.'], 403);
        }

        if ($participant->is_muted) {
            return response()->json(['error' => 'You are muted and cannot send messages.'], 403);
        }

        // Basic rate limiting: 1 message per second per participant to prevent spam
        $rateLimitKey = "chat-rate:{$participant->id}";
        if (RateLimiter::tooManyAttempts($rateLimitKey, 1)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'error' => "Please wait {$seconds} second before sending another message."
            ], 429);
        }
        RateLimiter::hit($rateLimitKey, 1);

        $message = $this->chatService->sendMessage(
            room: $room,
            participant: $participant,
            content: $request->validated('content')
        );

        // Broadcast to all participants in the room
        broadcast(new MessageSent($room, $message));

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'sender_name' => $message->sender_name,
                'content' => $message->content,
                'is_system_message' => false,
                'created_at' => $message->created_at->toISOString(),
            ],
        ]);
    }

    /**
     * Host moderation: Delete a message from the room chat.
     */
    public function destroy(Request $request, string $uuid, int $id): JsonResponse
    {
        $room = Room::where('uuid', $uuid)->firstOrFail();
        $message = $room->chatMessages()->findOrFail($id);

        $this->chatService->deleteMessage($message);

        // Broadcast removal event to all connected users
        broadcast(new MessageDeleted($room, $id));

        return response()->json([
            'success' => true,
            'message_id' => $id,
        ]);
    }

    /**
     * Host moderation: Mute or unmute a participant.
     */
    public function mute(Request $request, string $uuid, int $participantId): JsonResponse
    {
        $room = Room::where('uuid', $uuid)->firstOrFail();
        $participant = $room->participants()->findOrFail($participantId);

        // Toggle mute status
        $newMuteStatus = !$participant->is_muted;
        $this->chatService->muteParticipant($participant, $newMuteStatus);

        // Broadcast mute state change
        broadcast(new ParticipantMuted($room, $participantId, $newMuteStatus));

        return response()->json([
            'success' => true,
            'participant_id' => $participantId,
            'is_muted' => $newMuteStatus,
        ]);
    }

    /**
     * Host moderation: Toggle room-wide chat enabled/disabled.
     */
    public function toggleChat(Request $request, string $uuid): JsonResponse
    {
        $room = Room::where('uuid', $uuid)->firstOrFail();

        $room->update([
            'chat_enabled' => !$room->chat_enabled,
        ]);
        $room->refresh();

        // Broadcast chat toggle status
        broadcast(new ChatToggled($room, $room->chat_enabled));

        return response()->json([
            'success' => true,
            'chat_enabled' => $room->chat_enabled,
        ]);
    }
}

