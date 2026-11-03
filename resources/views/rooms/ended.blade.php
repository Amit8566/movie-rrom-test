@extends('layouts.app')

@section('content')
<div class="flex-1 flex items-center justify-center px-4 py-16 text-center">
    <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-2xl">
        <div class="w-16 h-16 bg-slate-800 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-5">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-white tracking-tight mb-2">Movie Session Ended</h1>
        <p class="text-sm text-slate-400 mb-6">
            The host has concluded the watch session for <span class="text-slate-200 font-semibold">"{{ $room->name }}"</span>.
            Room access and chat are now disabled.
        </p>

        <a href="{{ route('rooms.index') }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-rose-600 hover:bg-rose-500 text-white font-medium text-sm rounded-xl transition">
            <span>Back to Home</span>
            &rarr;
        </a>
    </div>
</div>
@endsection
