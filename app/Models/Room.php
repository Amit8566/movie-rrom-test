<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Room extends Model
{
    protected $fillable = [
        'name',
        'video_url',
        'host_token',
        'host_name',
        'status',
        'current_time',
        'chat_enabled',
    ];

    protected $casts = [
        'chat_enabled' => 'boolean',
        'current_time' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Room $room) {
            $room->uuid = (string) Str::uuid();
        });
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    /** Check if the given token belongs to the host. */
    public function isHost(string $token): bool
    {
        return hash_equals($this->host_token, $token);
    }

    /** Whether this room session is still active. */
    public function isActive(): bool
    {
        return $this->status !== 'ended';
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'ended');
    }
}
