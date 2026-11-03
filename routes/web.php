<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\PlaybackController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

// Public room views & participant join
Route::get('/', [RoomController::class, 'index'])->name('rooms.index');
Route::get('/rooms/create', [RoomController::class, 'create'])->name('rooms.create');
Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
Route::get('/rooms/{uuid}', [RoomController::class, 'show'])->name('rooms.show');
Route::post('/rooms/{uuid}/join', [RoomController::class, 'join'])->name('rooms.join');

// Participant chat message submission
Route::post('/rooms/{uuid}/messages', [ChatController::class, 'store'])->name('chat.store');

// Host-protected actions: playback control & chat moderation
Route::middleware(['room.host'])->group(function () {
    Route::post('/rooms/{uuid}/playback', [PlaybackController::class, 'update'])->name('playback.update');
    Route::post('/rooms/{uuid}/end', [PlaybackController::class, 'end'])->name('playback.end');

    Route::delete('/rooms/{uuid}/messages/{id}', [ChatController::class, 'destroy'])->name('chat.destroy');
    Route::post('/rooms/{uuid}/mute/{participantId}', [ChatController::class, 'mute'])->name('chat.mute');
    Route::post('/rooms/{uuid}/chat-toggle', [ChatController::class, 'toggleChat'])->name('chat.toggle');
});
