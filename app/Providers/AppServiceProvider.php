<?php

namespace App\Providers;

use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Custom user resolver for broadcasting channel authorization
        // Enables session-based participants without full User model auth
        $this->app->rebinding('request', function ($app, $request) {
            $request->setUserResolver(function ($guard = null) use ($request) {
                if ($request->hasSession()) {
                    $channelName = $request->input('channel_name', '');
                    if (preg_match('/room\.([a-f0-9\-]+)/', $channelName, $matches)) {
                        $uuid = $matches[1];
                        $participantId = $request->session()->get("participant_{$uuid}");
                        if ($participantId) {
                            return Participant::find($participantId);
                        }
                    }

                    // Fallback to searching active participant by session ID
                    return Participant::where('session_id', $request->session()->getId())
                        ->whereNull('left_at')
                        ->latest('id')
                        ->first();
                }

                return null;
            });
        });
    }
}
