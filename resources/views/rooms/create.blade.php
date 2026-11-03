@extends('layouts.app')

@section('content')
<div class="flex-1 flex items-center justify-center px-4 py-12" x-data="{
    videoUrl: '{{ old('video_url', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4') }}',
    setSample(url) {
        this.videoUrl = url;
    }
}">
    <div class="w-full max-w-lg bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xl">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-white tracking-tight">Create a Movie Room</h1>
            <p class="text-sm text-slate-400 mt-1">Set up your virtual room and invite friends to watch with you.</p>
        </div>

        @if ($errors->any())
            <div class="mb-5 p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('rooms.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Room Name -->
            <div>
                <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                    Room Name
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', 'Friday Night Movie') }}"
                    placeholder="e.g. Movie Marathon with Friends"
                    required
                    maxlength="100"
                    class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition text-sm"
                >
            </div>

            <!-- Host Display Name -->
            <div>
                <label for="host_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                    Your Display Name (Host)
                </label>
                <input
                    type="text"
                    id="host_name"
                    name="host_name"
                    value="{{ old('host_name', 'Host') }}"
                    placeholder="e.g. Alex"
                    required
                    maxlength="50"
                    class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition text-sm"
                >
            </div>

            <!-- Video URL -->
            <div>
                <label for="video_url" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                    Direct Video URL (.mp4 / .webm)
                </label>
                <input
                    type="url"
                    id="video_url"
                    name="video_url"
                    x-model="videoUrl"
                    placeholder="https://domain.com/path/video.mp4"
                    required
                    maxlength="500"
                    class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition text-sm font-mono text-xs"
                >
                <div class="mt-2 text-xs text-slate-400">
                    <span class="text-slate-500">Quick samples:</span>
                    <button type="button" @click="setSample('https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4')" class="text-rose-400 hover:underline ml-1">Big Buck Bunny</button>
                    <span class="text-slate-600">&bull;</span>
                    <button type="button" @click="setSample('https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4')" class="text-rose-400 hover:underline ml-1">Elephants Dream</button>
                    <span class="text-slate-600">&bull;</span>
                    <button type="button" @click="setSample('https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/TearsOfSteel.mp4')" class="text-rose-400 hover:underline ml-1">Tears of Steel</button>
                </div>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                class="w-full py-3 px-4 bg-rose-600 hover:bg-rose-500 text-white font-semibold rounded-xl shadow-lg shadow-rose-900/40 transition font-medium text-sm flex items-center justify-center gap-2"
            >
                <span>Launch Movie Room</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </form>
    </div>
</div>
@endsection

