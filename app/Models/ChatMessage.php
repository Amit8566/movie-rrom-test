<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    protected $fillable = [
        'room_id',
        'participant_id',
        'sender_name',
        'content',
        'is_system_message',
        'is_deleted',
    ];

    protected $casts = [
        'is_system_message' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_deleted', false);
    }
}
