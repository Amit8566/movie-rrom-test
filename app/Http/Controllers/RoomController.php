<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRoomRequest;
use App\Http\Requests\JoinRoomRequest;
use App\Models\Room;
use App\Services\ChatService;
use App\Services\RoomService;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function __construct(
        private RoomService $roomService,
        private ChatService $chatService,
    ) {}

    /**
     * Landing page.
     */
    public function index()
    {
        return view('rooms.index');
    }

    /**
     * Show the room creation form.
     */
    public function create()
    {
        return view('rooms.create');
    }

    /**
     * Store a newly created room and redirect the host into it.
     */
    public function store(CreateRoomRequest $request)
    {
        $data = $this->roomService->createRoom(
            name: $request->validated('name'),
            videoUrl: $request->validated('video_url'),
            hostName: $request->validated('host_name'),
        );

        $room = $data['room'];
        $hostToken = $data['host_token'];

        // Store host token in session so we can identify them later
        session()->put("host_token_{$room->uuid}", $hostToken);

        // Auto-join the host as a participant
        $participant = $this->roomService->joinRoom(
            room: $room,
            displayName: $request->validated('host_name'),
            sessionId: session()->getId(),
        );

        // Remember participant id in session
        session()->put("participant_{$room->uuid}", $participant->id);

        return redirect()->route('rooms.show', $room->uuid);
    }

    /**
     * Display the room. Redirects to join form if user hasn't joined yet.
     */
    public function show(string $uuid)
    {
        $room = Room::where('uuid', $uuid)->firstOrFail();

        // If room has ended, show the ended page
        if ($room->status === 'ended') {
            return view('rooms.ended', compact('room'));
        }

        // Check if this user has joined the room
        $participantId = session()->get("participant_{$room->uuid}");

        if (!$participantId) {
            return view('rooms.join', compact('room'));
        }

        $participant = $room->participants()->find($participantId);

        if (!$participant || $participant->left_at !== null) {
            session()->forget("participant_{$room->uuid}");
            return view('rooms.join', compact('room'));
        }

        // Determine if current user is the host
        $hostToken = session()->get("host_token_{$room->uuid}", '');
        $isHost = $room->isHost($hostToken);

        // Fetch chat messages and active participants
        $messages = $this->chatService->getRecentMessages($room);
        $participants = $this->roomService->getRoomParticipants($room);

        return view('rooms.show', compact(
            'room', 'participant', 'isHost', 'messages', 'participants'
        ));
    }

    /**
     * Process join request — validate display name and add participant.
     */
    public function join(JoinRoomRequest $request, string $uuid)
    {
        $room = Room::where('uuid', $uuid)->firstOrFail();

        if ($room->status === 'ended') {
            return redirect()->route('rooms.show', $uuid);
        }

        $participant = $this->roomService->joinRoom(
            room: $room,
            displayName: $request->validated('display_name'),
            sessionId: session()->getId(),
        );

        session()->put("participant_{$room->uuid}", $participant->id);

        // Broadcast a system message about the new participant
        $this->chatService->sendSystemMessage(
            $room,
            $participant->display_name . ' joined the room'
        );

        return redirect()->route('rooms.show', $uuid);
    }
}

