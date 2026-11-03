<?php

namespace Tests\Feature;

use App\Events\MessageDeleted;
use App\Events\MessageSent;
use App\Events\ParticipantMuted;
use App\Events\PlaybackUpdated;
use App\Events\SessionEnded;
use App\Models\ChatMessage;
use App\Models\Participant;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class MovieRoomFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** 1. Room Creation: Host creates a room with name, video URL, host name */
    public function test_host_can_create_movie_room_and_gets_unique_link()
    {
        $response = $this->post(route('rooms.store'), [
            'name' => 'Avengers Watch Party',
            'video_url' => 'https://example.com/videos/avengers.mp4',
            'host_name' => 'Tony Stark',
        ]);

        $this->assertDatabaseHas('rooms', [
            'name' => 'Avengers Watch Party',
            'video_url' => 'https://example.com/videos/avengers.mp4',
            'host_name' => 'Tony Stark',
            'status' => 'waiting',
        ]);

        $room = Room::first();
        $this->assertNotEmpty($room->uuid);
        $this->assertNotEmpty($room->host_token);

        // Host is automatically joined as participant and redirected
        $this->assertDatabaseHas('participants', [
            'room_id' => $room->id,
            'display_name' => 'Tony Stark',
        ]);

        $response->assertRedirect(route('rooms.show', $room->uuid));
    }

    /** 2. Room Access: Viewer opens room link and is prompted to join */
    public function test_viewer_prompted_for_display_name_and_can_join()
    {
        $room = Room::create([
            'name' => 'Sci-Fi Night',
            'video_url' => 'https://example.com/scifi.mp4',
            'host_token' => 'host-secret-token',
            'host_name' => 'Neo',
            'status' => 'waiting',
            'chat_enabled' => true,
        ]);

        // Accessing without joining shows the join view
        $viewResponse = $this->get(route('rooms.show', $room->uuid));
        $viewResponse->assertStatus(200);
        $viewResponse->assertViewIs('rooms.join');

        // Submit display name to join
        $joinResponse = $this->post(route('rooms.join', $room->uuid), [
            'display_name' => 'Trinity',
        ]);

        $joinResponse->assertRedirect(route('rooms.show', $room->uuid));

        $this->assertDatabaseHas('participants', [
            'room_id' => $room->id,
            'display_name' => 'Trinity',
        ]);
    }

    /** 3. Playback Control Authorization: Host can control playback, non-host is forbidden */
    public function test_only_host_can_control_playback()
    {
        Event::fake([PlaybackUpdated::class]);

        $room = Room::create([
            'name' => 'Action Fest',
            'video_url' => 'https://example.com/action.mp4',
            'host_token' => 'secret-host-key',
            'host_name' => 'HostUser',
            'status' => 'waiting',
        ]);

        // Attempt as unauthenticated/guest participant (no host token in session)
        $unauthorized = $this->postJson(route('playback.update', $room->uuid), [
            'action' => 'play',
            'current_time' => 12.5,
        ]);
        $unauthorized->assertStatus(403);

        // Attempt as authorized host with matching session token
        $authorized = $this->withSession(["host_token_{$room->uuid}" => 'secret-host-key'])
            ->postJson(route('playback.update', $room->uuid), [
                'action' => 'play',
                'current_time' => 12.5,
            ]);

        $authorized->assertStatus(200);
        $authorized->assertJson([
            'success' => true,
            'status' => 'playing',
            'current_time' => 12.5,
        ]);

        $this->assertEquals('playing', $room->fresh()->status);
        $this->assertEquals(12.5, $room->fresh()->current_time);

        Event::assertDispatched(PlaybackUpdated::class);
    }

    /** 4. Session Management: Ending the session disables access and broadcasts event */
    public function test_host_can_end_session()
    {
        Event::fake([SessionEnded::class]);

        $room = Room::create([
            'name' => 'Comedy Night',
            'video_url' => 'https://example.com/comedy.mp4',
            'host_token' => 'host-token-123',
            'host_name' => 'Chaplin',
            'status' => 'playing',
        ]);

        $response = $this->withSession(["host_token_{$room->uuid}" => 'host-token-123'])
            ->postJson(route('playback.end', $room->uuid));

        $response->assertStatus(200);
        $this->assertEquals('ended', $room->fresh()->status);
        $this->assertFalse($room->fresh()->chat_enabled);

        Event::assertDispatched(SessionEnded::class);

        // Accessing ended room displays the ended view
        $showResponse = $this->get(route('rooms.show', $room->uuid));
        $showResponse->assertStatus(200);
        $showResponse->assertViewIs('rooms.ended');
    }

    /** 5. Live Chat: Participant can send message with sanitization and validation */
    public function test_participant_can_send_sanitized_chat_message()
    {
        Event::fake([MessageSent::class]);

        $room = Room::create([
            'name' => 'Live Discussion Room',
            'video_url' => 'https://example.com/movie.mp4',
            'host_token' => 'token-abc',
            'host_name' => 'Host',
            'status' => 'playing',
            'chat_enabled' => true,
        ]);

        $participant = Participant::create([
            'room_id' => $room->id,
            'session_id' => 'session-xyz',
            'display_name' => 'Alice',
        ]);

        // Attempt with malicious XSS content
        $response = $this->withSession(["participant_{$room->uuid}" => $participant->id])
            ->postJson(route('chat.store', $room->uuid), [
                'content' => '<script>alert("hack")</script>Hello Movie Fans!',
            ]);

        $response->assertStatus(200);

        // Verify message was sanitized
        $this->assertDatabaseHas('chat_messages', [
            'room_id' => $room->id,
            'participant_id' => $participant->id,
            'sender_name' => 'Alice',
            'content' => 'Hello Movie Fans!', // stripped script tag
        ]);

        Event::assertDispatched(MessageSent::class);
    }

    /** 6. Host Moderation: Delete message, mute participant, toggle chat */
    public function test_host_moderation_controls()
    {
        Event::fake([MessageDeleted::class, ParticipantMuted::class]);

        $room = Room::create([
            'name' => 'Moderated Room',
            'video_url' => 'https://example.com/movie.mp4',
            'host_token' => 'mod-token',
            'host_name' => 'ModHost',
            'status' => 'playing',
            'chat_enabled' => true,
        ]);

        $troublemaker = Participant::create([
            'room_id' => $room->id,
            'session_id' => 'trouble-session',
            'display_name' => 'Troll',
            'is_muted' => false,
        ]);

        $badMessage = ChatMessage::create([
            'room_id' => $room->id,
            'participant_id' => $troublemaker->id,
            'sender_name' => 'Troll',
            'content' => 'Spam content',
        ]);

        // 1. Host mutes participant
        $muteResponse = $this->withSession(["host_token_{$room->uuid}" => 'mod-token'])
            ->postJson(route('chat.mute', ['uuid' => $room->uuid, 'participantId' => $troublemaker->id]));

        $muteResponse->assertStatus(200);
        $this->assertTrue($troublemaker->fresh()->is_muted);
        Event::assertDispatched(ParticipantMuted::class);

        // Muted participant cannot send messages anymore
        $spamResponse = $this->withSession(["participant_{$room->uuid}" => $troublemaker->id])
            ->postJson(route('chat.store', $room->uuid), [
                'content' => 'More spam',
            ]);
        $spamResponse->assertStatus(403);

        // 2. Host deletes message
        $deleteResponse = $this->withSession(["host_token_{$room->uuid}" => 'mod-token'])
            ->deleteJson(route('chat.destroy', ['uuid' => $room->uuid, 'id' => $badMessage->id]));

        $deleteResponse->assertStatus(200);
        $this->assertTrue($badMessage->fresh()->is_deleted);
        Event::assertDispatched(MessageDeleted::class);

        // 3. Host toggles chat off
        $toggleResponse = $this->withSession(["host_token_{$room->uuid}" => 'mod-token'])
            ->postJson(route('chat.toggle', $room->uuid));

        $toggleResponse->assertStatus(200);
        $this->assertFalse($room->fresh()->chat_enabled);
    }
}
