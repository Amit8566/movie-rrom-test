@extends('layouts.app')

@section('content')
<div class="flex-1 flex flex-col items-center justify-center px-4 py-16 sm:py-24 text-center max-w-4xl mx-auto">
    <!-- Hero Badge -->
    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs font-medium mb-6">
        <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
        Synchronized Virtual Cinema
    </div>

    <!-- Hero Title -->
    <h1 class="text-4xl sm:text-6xl font-extrabold tracking-tight text-white mb-6">
        Watch Movies Together, <br class="hidden sm:inline">
        <span class="text-transparent bg-clip-text bg-gradient-to-r from-rose-400 via-rose-500 to-amber-400">
            In Perfect Real-Time Sync.
        </span>
    </h1>

    <p class="text-base sm:text-lg text-slate-400 max-w-2xl mb-10 leading-relaxed">
        Host virtual movie watch-parties. Share a public MP4 URL, invite friends with a link, and enjoy synchronized playback with live moderation and chat.
    </p>

    <!-- Call to action -->
    <div class="flex flex-col sm:flex-row items-center gap-4 mb-16">
        <a href="{{ route('rooms.create') }}" class="w-full sm:w-auto px-8 py-3.5 bg-rose-600 hover:bg-rose-500 text-white font-semibold rounded-xl shadow-lg shadow-rose-900/40 transition transform hover:-translate-y-0.5">
            Create Your Movie Room &rarr;
        </a>
    </div>

    <!-- Feature Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-left w-full mt-4">
        <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-slate-700 transition">
            <div class="w-10 h-10 rounded-lg bg-rose-500/10 text-rose-400 flex items-center justify-center font-bold text-lg mb-4">
                1
            </div>
            <h2 class="text-white font-semibold mb-2 text-base">Host Exclusivity</h2>
            <p class="text-sm text-slate-400">
                Only the host holds playback controls: play, pause, seek, and end session, guaranteed via server policy.
            </p>
        </div>

        <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-slate-700 transition">
            <div class="w-10 h-10 rounded-lg bg-rose-500/10 text-rose-400 flex items-center justify-center font-bold text-lg mb-4">
                2
            </div>
            <h2 class="text-white font-semibold mb-2 text-base">Instant WebSockets</h2>
            <p class="text-sm text-slate-400">
                Driven by WebSockets for zero-refresh playback updates, presence tracking, and synchronized frames.
            </p>
        </div>

        <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-slate-700 transition">
            <div class="w-10 h-10 rounded-lg bg-rose-500/10 text-rose-400 flex items-center justify-center font-bold text-lg mb-4">
                3
            </div>
            <h2 class="text-white font-semibold mb-2 text-base">Moderated Live Chat</h2>
            <p class="text-sm text-slate-400">
                XSS-sanitized chat with host moderation: delete inappropriate messages, mute troublemakers, or toggle chat.
            </p>
        </div>
    </div>
</div>
@endsection
