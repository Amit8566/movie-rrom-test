<?php

namespace App\Events;

use App\Models\ChatMessage;
use App\Models\Room;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Room $room, public ChatMessage $message)
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
            'id' => $this->message->id,
            'sender_name' => $this->message->sender_name,
            'content' => $this->message->content,
            'is_system_message' => $this->message->is_system_message,
            'created_at' => $this->message->created_at->toISOString(),
        ];
    }
}
