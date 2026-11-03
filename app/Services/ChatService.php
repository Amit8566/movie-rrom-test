<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\Participant;
use App\Models\Room;
use Illuminate\Support\Collection;

class ChatService
{
    /** Send a chat message from a participant. */
    public function sendMessage(Room $room, Participant $participant, string $content): ChatMessage
    {
        // Strip script blocks including inner code to prevent XSS
        $withoutScripts = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $content);
        $sanitized = trim(e(strip_tags($withoutScripts)));

        return $room->chatMessages()->create([
            'participant_id' => $participant->id,
            'sender_name' => $participant->display_name,
            'content' => $sanitized,
            'is_system_message' => false,
            'is_deleted' => false,
        ]);
    }

    /** Send a system-generated message to the room. */
    public function sendSystemMessage(Room $room, string $content): ChatMessage
    {
        return $room->chatMessages()->create([
            'participant_id' => null,
            'sender_name' => 'System',
            'content' => $content,
            'is_system_message' => true,
            'is_deleted' => false,
        ]);
    }

    /** Soft-delete a message by flagging it. */
    public function deleteMessage(ChatMessage $message): void
    {
        $message->update(['is_deleted' => true]);
    }

    /** Toggle mute status on a participant. */
    public function muteParticipant(Participant $participant, bool $mute = true): void
    {
        $participant->update(['is_muted' => $mute]);
    }

    /** Get the most recent visible messages for a room. */
    public function getRecentMessages(Room $room, int $limit = 50): Collection
    {
        return $room->chatMessages()
            ->visible()
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();
    }
}
