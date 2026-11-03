# Movie Room — Synchronized Virtual Cinema & Live Chat

A modern web application built with **Laravel 12**, **Laravel Reverb (WebSockets)**, **Laravel Echo**, and **Alpine.js** that allows a host to create and manage a virtual movie room where participants can join, chat, and watch videos in real-time synchronization.

---

## Features Implemented

### 1. Room Creation (Host Functionality)
- Host provides a **Room Name**, **Display Name**, and a **Direct Video URL** (`.mp4`, `.webm`).
- System automatically generates a unique UUID-based shareable link (e.g. `/rooms/{uuid}`).
- Instant 1-click **"Copy Room Link"** button to share with viewers.

### 2. Room Access (Participant Functionality)
- Anyone with the link can join the room.
- Prompted to enter their **Display Name** before entering.
- Viewers see the video player, participant list, and live chat panel.

### 3. Playback Control & Host Exclusivity
- **Host Exclusive Controls**: Play, Pause, Stop, Seek, and End Session.
- **Participant Restriction**: Viewers have video controls disabled and pointer interaction blocked.
- Server-side middleware (`VerifyRoomHost` / `room.host`) enforces authorization on all playback endpoints (returning HTTP 403 on unauthorized attempts).

### 4. Real-Time Synchronization
- Powered by **Laravel Reverb** WebSockets and **Presence Channels** (`room.{uuid}`).
- Host actions (`play`, `pause`, `stop`, seek) instantly update all viewers without any page refresh.
- New joiners receive the current playback position and state upon entry.

### 5. Moderated Live Chat
- **Real-Time Chat Panel** alongside video player.
- Messages display sender's name and local timestamp.
- **Host Moderation**:
  - Remove/delete inappropriate messages instantly from all viewers.
  - Mute a specific participant (blocks further message submission without removing them).
  - Toggle chat entirely for the room.
- **Safety & Validation**:
  - Strict validation (non-empty, max 500 characters).
  - XSS sanitization (scripts and HTML tags stripped and escaped).
  - Rate limiting (1 message per second to prevent spamming).

### 6. Session Lifecycle
- Host can conclude the session at any time with **"End Session"**.
- Viewers are immediately notified via WebSocket.
- Access to the video room and chat is locked.

---

## Tech Stack

| Layer | Technology |
|---|---|
| **Backend Framework** | Laravel 12.x (PHP 8.2+) |
| **Database** | SQLite (Default, zero-config) / MySQL compatible |
| **WebSockets Server** | Laravel Reverb (First-party, high performance) |
| **Broadcasting Client**| Laravel Echo + Pusher JS |
| **Frontend UI** | Blade + Alpine.js + Tailwind CSS |
| **Video Player** | Native HTML5 `<video>` |

---

## Architecture Overview

```
app/
├── Events/
│   ├── PlaybackUpdated.php       # Broadcasts play/pause/stop/seek
│   ├── SessionEnded.php          # Broadcasts session termination
│   ├── MessageSent.php           # Broadcasts real-time chat messages
│   ├── MessageDeleted.php        # Broadcasts host message deletion
│   ├── ParticipantMuted.php      # Broadcasts mute/unmute state
│   └── ChatToggled.php           # Broadcasts chat disable/enable
├── Http/
│   ├── Controllers/
│   │   ├── RoomController.php     # Room creation, show, participant join
│   │   ├── PlaybackController.php # Host playback state & end session
│   │   └── ChatController.php     # Message sending & moderation
│   ├── Middleware/
│   │   └── VerifyRoomHost.php     # Server-side host token verification
│   └── Requests/
│       ├── CreateRoomRequest.php
│       ├── JoinRoomRequest.php
│       ├── PlaybackActionRequest.php
│       └── SendMessageRequest.php
├── Models/
│   ├── Room.php
│   ├── Participant.php            # Implements Authenticatable for presence auth
│   └── ChatMessage.php
└── Services/
    ├── RoomService.php
    ├── PlaybackService.php
    └── ChatService.php
```

---

## How to Run Locally

### 1. Requirements
- PHP 8.2 or higher
- Composer

### 2. Setup Database & Migrations
The database is already configured to use SQLite in `.env`. Run:
```bash
php artisan migrate
```

### 3. Start Laravel Reverb (WebSocket Server)
In a separate terminal window, run:
```bash
php artisan reverb:start
```
*Reverb will listen on port `8080` for WebSocket connections.*

### 4. Start the Application Server
In your main terminal, run:
```bash
php artisan serve
```
Open your browser at: **[http://localhost:8000](http://localhost:8000)**

---

## Running Automated Tests

Run the test suite:
```bash
php artisan test --filter=MovieRoomFeatureTest
```

All 6 core feature tests verify room creation, participant joining, host authorization, playback sync, chat sanitization, and moderation controls.
