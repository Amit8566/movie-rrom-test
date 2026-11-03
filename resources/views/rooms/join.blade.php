@extends('layouts.app')

@section('content')
<div class="flex-1 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xl text-center">
        <!-- Room Icon -->
        <div class="w-14 h-14 bg-rose-500/10 border border-rose-500/20 text-rose-500 rounded-2xl flex items-center justify-center mx-auto mb-5">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-white tracking-tight mb-1">
            Join "{{ $room->name }}"
        </h1>
        <p class="text-xs text-slate-400 mb-6">
            Hosted by <span class="text-rose-400 font-semibold">{{ $room->host_name }}</span>
        </p>

        @if ($errors->any())
            <div class="mb-5 p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs text-left">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('rooms.join', $room->uuid) }}" method="POST" class="space-y-4 text-left">
            @csrf

            <div>
                <label for="display_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                    Enter Your Display Name
                </label>
                <input
                    type="text"
                    id="display_name"
                    name="display_name"
                    value="{{ old('display_name') }}"
                    placeholder="e.g. Sarah"
                    required
                    autofocus
                    maxlength="50"
                    class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition text-sm"
                >
                <p class="text-xs text-slate-500 mt-1.5">
                    This will identify you in the room participant list and live chat.
                </p>
            </div>

            <button
                type="submit"
                class="w-full py-3 px-4 bg-rose-600 hover:bg-rose-500 text-white font-semibold rounded-xl shadow-lg shadow-rose-900/40 transition text-sm flex items-center justify-center gap-2"
            >
                <span>Enter Room</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </form>
    </div>
</div>
@endsection

