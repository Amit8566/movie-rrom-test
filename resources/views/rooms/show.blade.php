@extends('layouts.app')

@section('content')
<div
    class="flex-1 flex flex-col max-w-7xl w-full mx-auto p-4 sm:p-6 lg:p-8"
    x-data="roomApp()"
    x-init="initRoom()"
    x-cloak
>
    <!-- Top Header: Room Title, Status Badge, Share Link -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-slate-800">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-white tracking-tight">{{ $room->name }}</h1>
                <span
                    class="px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider"
                    :class="{
                        'bg-amber-500/10 text-amber-400 border border-amber-500/20': roomStatus === 'waiting',
                        'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20': roomStatus === 'playing',
                        'bg-blue-500/10 text-blue-400 border border-blue-500/20': roomStatus === 'paused',
                        'bg-rose-500/10 text-rose-400 border border-rose-500/20': roomStatus === 'ended',
                    }"
                    x-text="roomStatus"
                >
                    {{ $room->status }}
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">
                Hosted by <span class="text-rose-400 font-semibold">{{ $room->host_name }}</span>
                &bull; You are <span class="text-slate-200 font-semibold">{{ $participant->display_name }}</span>
                @if($isHost)
                    <span class="ml-1 px-1.5 py-0.5 rounded bg-rose-500/20 text-rose-300 text-[10px] font-bold">HOST</span>
                @else
                    <span class="ml-1 px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 text-[10px]">VIEWER</span>
                @endif
            </p>
        </div>

        <!-- Shareable Link Copy Box -->
        <div class="flex items-center gap-2 bg-slate-900 border border-slate-800 rounded-xl px-3 py-1.5 text-xs">
            <span class="text-slate-500">Room Link:</span>
            <input
                type="text"
                readonly
                :value="shareUrl"
                class="bg-transparent text-slate-300 font-mono text-xs focus:outline-none w-48 sm:w-64 truncate"
            >
            <button
                type="button"
                @click="copyShareLink()"
                class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg transition font-medium text-xs flex items-center gap-1"
            >
                <span x-text="copied ? 'Copied!' : 'Copy'">Copy</span>
            </button>
        </div>
    </div>

    <!-- Main Grid: Video Player (Left 2/3) & Participants/Chat (Right 1/3) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6 flex-1 items-start">

        <!-- Left Column: Video Area & Host Controls -->
        <div class="lg:col-span-2 flex flex-col gap-4">
            <!-- Video Player Box -->
            <div class="relative bg-black rounded-2xl overflow-hidden shadow-2xl border border-slate-800 aspect-video flex items-center justify-center">
                <video
                    id="movieVideo"
                    src="{{ $room->video_url }}"
                    playsinline
                    preload="auto"
                    class="w-full h-full object-contain"
                    :class="{ 'pointer-events-none': !isHost }"
                    @timeupdate="onVideoTimeUpdate()"
                    @play="onLocalPlay()"
                    @pause="onLocalPause()"
                ></video>

                <!-- Viewer overlay notice -->
                @if(!$isHost)
                    <div class="absolute top-3 left-3 bg-slate-950/70 backdrop-blur px-2.5 py-1 rounded-md text-[11px] text-slate-300 flex items-center gap-1.5 border border-slate-800/80">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Synced with Host</span>
                    </div>
                @endif

                <!-- Ended Overlay -->
                <div
                    x-show="roomStatus === 'ended'"
                    class="absolute inset-0 bg-slate-950/90 backdrop-blur-sm flex flex-col items-center justify-center text-center p-6"
                >
                    <h2 class="text-2xl font-bold text-white mb-2">Session Ended</h2>
                    <p class="text-sm text-slate-400 mb-4">The host has ended this movie room session.</p>
                    <a href="{{ route('rooms.index') }}" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-lg text-sm">
                        Return Home
                    </a>
                </div>
            </div>

            <!-- Host Exclusive Playback Controls -->
            @if($isHost)
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-wrap items-center justify-between gap-3 shadow-lg">
                    <div class="flex items-center gap-2">
                        <!-- Play Button -->
                        <button
                            type="button"
                            @click="triggerPlayback('play')"
                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-xs rounded-xl transition flex items-center gap-1.5 shadow-sm shadow-emerald-900/50"
                        >
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                            </svg>
                            <span>Play</span>
                        </button>

                        <!-- Pause Button -->
                        <button
                            type="button"
                            @click="triggerPlayback('pause')"
                            class="px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white font-medium text-xs rounded-xl transition flex items-center gap-1.5 shadow-sm shadow-amber-900/50"
                        >
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <span>Pause</span>
                        </button>

                        <!-- Stop Button -->
                        <button
                            type="button"
                            @click="triggerPlayback('stop')"
                            class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white font-medium text-xs rounded-xl transition flex items-center gap-1.5"
                        >
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd" />
                            </svg>
                            <span>Stop</span>
                        </button>
                    </div>

                    <!-- Moderate Controls: Toggle Chat & End Session -->
                    <div class="flex items-center gap-2">
                        <!-- Toggle Chat -->
                        <button
                            type="button"
                            @click="toggleChat()"
                            class="px-3 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 font-medium text-xs rounded-xl transition flex items-center gap-1.5"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <span x-text="chatEnabled ? 'Disable Chat' : 'Enable Chat'"></span>
                        </button>

                        <!-- End Session Button -->
                        <button
                            type="button"
                            @click="endSession()"
                            class="px-3.5 py-2 bg-rose-700 hover:bg-rose-600 text-white font-medium text-xs rounded-xl transition flex items-center gap-1.5 shadow-sm shadow-rose-950"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span>End Session</span>
                        </button>
                    </div>
                </div>
            @else
                <!-- Participant Guidance Banner -->
                <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-3.5 flex items-center justify-between text-xs text-slate-400">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Playback is controlled exclusively by <strong class="text-slate-200">{{ $room->host_name }}</strong>.</span>
                    </div>
                    <span class="text-slate-500">Participant View</span>
                </div>
            @endif
        </div>

        <!-- Right Column: Sidebar (Participants list & Live Chat panel) -->
        <div class="flex flex-col gap-4 h-[650px]">

            <!-- Participant List Card (Collapsible/compact) -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-3.5 shadow-lg flex flex-col max-h-44">
                <div class="flex items-center justify-between pb-2 border-b border-slate-800 text-xs">
                    <div class="flex items-center gap-2 font-semibold text-slate-200">
                        <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span>Participants</span>
                        <span class="px-1.5 py-0.2 rounded-full bg-slate-800 text-[10px] text-slate-400 font-mono" x-text="participants.length"></span>
                    </div>
                    <span class="text-[10px] text-slate-500">Live</span>
                </div>

                <div class="overflow-y-auto space-y-1.5 mt-2 pr-1 text-xs">
                    <template x-for="p in participants" :key="p.id">
                        <div class="flex items-center justify-between py-1 px-2 rounded-lg bg-slate-950/40 hover:bg-slate-950/80 transition">
                            <div class="flex items-center gap-2 truncate">
                                <span class="w-2 h-2 rounded-full" :class="p.is_muted ? 'bg-amber-500' : 'bg-emerald-400'"></span>
                                <span class="font-medium text-slate-200 truncate" x-text="p.name || p.display_name"></span>
                                <span x-show="p.is_host" class="text-[10px] text-amber-400 font-bold tracking-wider">[HOST]</span>
                                <span x-show="p.id === currentParticipantId" class="text-[10px] text-slate-400">(You)</span>
                                <span x-show="p.is_muted" class="text-[10px] text-amber-400 italic">Muted</span>
                            </div>

                            @if($isHost)
                                <div class="flex items-center gap-1" x-show="!p.is_host && p.id !== currentParticipantId">
                                    <button
                                        type="button"
                                        @click="muteParticipant(p.id)"
                                        class="px-2 py-0.5 rounded text-[10px] font-medium transition"
                                        :class="p.is_muted ? 'bg-amber-500/20 text-amber-300 hover:bg-amber-500/30' : 'bg-slate-800 text-slate-400 hover:bg-slate-700'"
                                        x-text="p.is_muted ? 'Unmute' : 'Mute'"
                                    ></button>
                                </div>
                            @endif
                        </div>
                    </template>
                </div>
            </div>

            <!-- Real-Time Chat Panel -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl flex-1 flex flex-col overflow-hidden shadow-lg">
                <!-- Chat Header -->
                <div class="px-4 py-3 border-b border-slate-800 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2 font-semibold text-slate-200">
                        <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                        <span>Room Chat</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <span x-show="!chatEnabled" class="text-[10px] text-rose-400 font-semibold bg-rose-500/10 px-2 py-0.5 rounded border border-rose-500/20">Chat Disabled</span>
                        <span x-show="isMuted" class="text-[10px] text-amber-400 font-semibold bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20">You are muted</span>
                    </div>
                </div>

                <!-- Messages Scroll Area -->
                <div id="chatMessages" class="flex-1 p-4 overflow-y-auto space-y-3 text-xs">
                    <template x-for="msg in messages" :key="msg.id">
                        <div>
                            <!-- System message -->
                            <template x-if="msg.is_system_message">
                                <div class="text-center py-1">
                                    <span class="inline-block px-3 py-1 rounded-full bg-slate-950/60 text-[11px] text-slate-400 italic border border-slate-800/60" x-text="msg.content"></span>
                                </div>
                            </template>

                            <!-- User message -->
                            <template x-if="!msg.is_system_message">
                                <div class="group flex flex-col p-2.5 rounded-xl bg-slate-950/50 hover:bg-slate-950/80 border border-slate-800/40 transition">
                                    <div class="flex items-center justify-between mb-1">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-semibold text-slate-200" x-text="msg.sender_name"></span>
                                            <span class="text-[10px] text-slate-500" x-text="formatTime(msg.created_at)"></span>
                                        </div>

                                        @if($isHost)
                                            <!-- Host delete message button -->
                                            <button
                                                type="button"
                                                @click="deleteMessage(msg.id)"
                                                title="Delete this message"
                                                class="opacity-0 group-hover:opacity-100 text-slate-500 hover:text-rose-400 transition text-[10px]"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                    <p class="text-slate-300 break-words leading-relaxed" x-text="msg.content"></p>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- Chat Input Box -->
                <div class="p-3 border-t border-slate-800 bg-slate-950/60">
                    <template x-if="!chatEnabled">
                        <div class="text-center py-2 text-xs text-slate-500 italic">
                            Chat has been disabled by the host.
                        </div>
                    </template>

                    <template x-if="chatEnabled && isMuted">
                        <div class="text-center py-2 text-xs text-amber-400 italic">
                            You have been muted by the host and cannot send messages.
                        </div>
                    </template>

                    <template x-if="chatEnabled && !isMuted">
                        <form @submit.prevent="sendMessage()" class="flex items-center gap-2">
                            <input
                                type="text"
                                x-model="newMessage"
                                placeholder="Type a message..."
                                maxlength="500"
                                required
                                class="flex-1 px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 text-xs transition"
                            >
                            <button
                                type="submit"
                                :disabled="sending || !newMessage.trim()"
                                class="px-3.5 py-2 bg-rose-600 hover:bg-rose-500 disabled:opacity-50 text-white font-medium text-xs rounded-xl transition flex items-center justify-center shadow-sm shadow-rose-900/50"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                            </button>
                        </form>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Pusher & Echo CDN -->
<script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0-rc2/dist/web/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

<script>
function roomApp() {
    return {
        roomUuid: '{{ $room->uuid }}',
        roomStatus: '{{ $room->status }}',
        isHost: @json($isHost),
        currentParticipantId: {{ $participant->id }},
        isMuted: @json($participant->is_muted),
        chatEnabled: @json($room->chat_enabled),
        shareUrl: window.location.href,
        copied: false,
        videoEl: null,
        messages: @json($messages),
        participants: @json($participants),
        newMessage: '',
        sending: false,
        isRemoteUpdate: false,

        initRoom() {
            this.videoEl = document.getElementById('movieVideo');

            // Scroll chat to bottom initially
            this.$nextTick(() => this.scrollChatToBottom());

            // Initialize WebSockets via Laravel Echo + Reverb
            this.setupEcho();

            // Initial sync: set currentTime and play state from room
            const initialTime = {{ $room->current_time }};
            if (this.videoEl) {
                this.videoEl.currentTime = initialTime;
                if (this.roomStatus === 'playing') {
                    this.videoEl.play().catch(() => {});
                }
            }
        },

        setupEcho() {
            try {
                window.Pusher = Pusher;
                window.Echo = new Echo({
                    broadcaster: 'reverb',
                    key: '{{ config("broadcasting.connections.reverb.key", "movie-room-key-local") }}',
                    wsHost: '{{ config("broadcasting.connections.reverb.options.host", "127.0.0.1") }}',
                    wsPort: {{ config("broadcasting.connections.reverb.options.port", 8080) }},
                    wssPort: {{ config("broadcasting.connections.reverb.options.port", 8080) }},
                    forceTLS: false,
                    enabledTransports: ['ws', 'wss'],
                    authEndpoint: '/broadcasting/auth',
                    auth: {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        }
                    }
                });

                // Join the Presence Channel for the movie room
                window.Echo.join(`room.${this.roomUuid}`)
                    .here((users) => {
                        this.participants = users;
                    })
                    .joining((user) => {
                        if (!this.participants.find(p => p.id === user.id)) {
                            this.participants.push(user);
                        }
                    })
                    .leaving((user) => {
                        this.participants = this.participants.filter(p => p.id !== user.id);
                    })
                    .listen('.PlaybackUpdated', (e) => {
                        this.handleRemotePlayback(e);
                    })
                    .listen('.SessionEnded', (e) => {
                        this.roomStatus = 'ended';
                        if (this.videoEl) this.videoEl.pause();
                        alert(e.message || 'The host has ended this movie session.');
                    })
                    .listen('.MessageSent', (e) => {
                        // Avoid duplicate message if already added locally
                        if (!this.messages.find(m => m.id === e.id)) {
                            this.messages.push(e);
                            this.$nextTick(() => this.scrollChatToBottom());
                        }
                    })
                    .listen('.MessageDeleted', (e) => {
                        this.messages = this.messages.filter(m => m.id !== e.message_id);
                    })
                    .listen('.ParticipantMuted', (e) => {
                        if (e.participant_id === this.currentParticipantId) {
                            this.isMuted = e.is_muted;
                        }
                        const p = this.participants.find(part => part.id === e.participant_id);
                        if (p) p.is_muted = e.is_muted;
                    })
                    .listen('.ChatToggled', (e) => {
                        this.chatEnabled = e.chat_enabled;
                    });
            } catch (err) {
                console.warn('Echo/WebSocket initialization note:', err);
            }
        },

        handleRemotePlayback(e) {
            if (!this.videoEl || this.isHost) return;

            this.isRemoteUpdate = true;
            this.roomStatus = e.status;

            // Sync video timestamp if difference is greater than 0.5s
            if (Math.abs(this.videoEl.currentTime - e.current_time) > 0.5) {
                this.videoEl.currentTime = e.current_time;
            }

            if (e.action === 'play') {
                this.videoEl.play().catch(() => {});
            } else if (e.action === 'pause') {
                this.videoEl.pause();
            } else if (e.action === 'stop') {
                this.videoEl.pause();
                this.videoEl.currentTime = 0;
            }

            setTimeout(() => { this.isRemoteUpdate = false; }, 300);
        },

        // Trigger playback from host controls
        triggerPlayback(action) {
            if (!this.isHost || !this.videoEl) return;

            if (action === 'play') {
                this.videoEl.play().catch(() => {});
                this.roomStatus = 'playing';
            } else if (action === 'pause') {
                this.videoEl.pause();
                this.roomStatus = 'paused';
            } else if (action === 'stop') {
                this.videoEl.pause();
                this.videoEl.currentTime = 0;
                this.roomStatus = 'waiting';
            }

            fetch(`/rooms/${this.roomUuid}/playback`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    action: action,
                    current_time: this.videoEl.currentTime || 0,
                }),
            }).catch(err => console.error('Playback update error:', err));
        },

        onLocalPlay() {
            if (this.isHost && !this.isRemoteUpdate && this.roomStatus !== 'playing') {
                this.triggerPlayback('play');
            }
        },

        onLocalPause() {
            if (this.isHost && !this.isRemoteUpdate && this.roomStatus === 'playing') {
                this.triggerPlayback('pause');
            }
        },

        onVideoTimeUpdate() {
            // Periodic sync hook if needed
        },

        endSession() {
            if (!confirm('Are you sure you want to end this movie session for all participants?')) {
                return;
            }

            fetch(`/rooms/${this.roomUuid}/end`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            }).then(() => {
                this.roomStatus = 'ended';
                if (this.videoEl) this.videoEl.pause();
            }).catch(err => console.error('End session error:', err));
        },

        sendMessage() {
            const content = this.newMessage.trim();
            if (!content || this.sending) return;

            this.sending = true;

            fetch(`/rooms/${this.roomUuid}/messages`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ content: content }),
            })
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                } else if (data.message) {
                    if (!this.messages.find(m => m.id === data.message.id)) {
                        this.messages.push(data.message);
                        this.$nextTick(() => this.scrollChatToBottom());
                    }
                    this.newMessage = '';
                }
            })
            .catch(err => console.error('Send message error:', err))
            .finally(() => { this.sending = false; });
        },

        deleteMessage(id) {
            if (!confirm('Delete this message for all participants?')) return;

            fetch(`/rooms/${this.roomUuid}/messages/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            })
            .then(() => {
                this.messages = this.messages.filter(m => m.id !== id);
            })
            .catch(err => console.error('Delete message error:', err));
        },

        muteParticipant(participantId) {
            fetch(`/rooms/${this.roomUuid}/mute/${participantId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            })
            .then(res => res.json())
            .then(data => {
                const p = this.participants.find(part => part.id === participantId);
                if (p) p.is_muted = data.is_muted;
            })
            .catch(err => console.error('Mute participant error:', err));
        },

        toggleChat() {
            fetch(`/rooms/${this.roomUuid}/chat-toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            })
            .then(res => res.json())
            .then(data => {
                this.chatEnabled = data.chat_enabled;
            })
            .catch(err => console.error('Toggle chat error:', err));
        },

        copyShareLink() {
            navigator.clipboard.writeText(this.shareUrl).then(() => {
                this.copied = true;
                setTimeout(() => { this.copied = false; }, 2000);
            });
        },

        scrollChatToBottom() {
            const el = document.getElementById('chatMessages');
            if (el) el.scrollTop = el.scrollHeight;
        },

        formatTime(isoString) {
            if (!isoString) return '';
            try {
                const d = new Date(isoString);
                return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            } catch {
                return '';
            }
        }
    };
}
</script>
@endpush

