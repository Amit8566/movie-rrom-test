<?php

namespace App\Http\Middleware;

use App\Models\Participant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveRoomParticipant
{
    /**
     * Handle an incoming request and resolve the participant as the request user.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->setUserResolver(function () use ($request) {
            if ($request->hasSession()) {
                // If this is a broadcasting auth request for a room channel:
                $channelName = $request->input('channel_name', '');
                if (preg_match('/room\.([a-f0-9\-]+)/', $channelName, $matches)) {
                    $uuid = $matches[1];
                    $participantId = $request->session()->get("participant_{$uuid}");
                    if ($participantId) {
                        $p = Participant::find($participantId);
                        if ($p) {
                            return $p;
                        }
                    }
                }

                // If accessing a room route with {uuid} parameter:
                $routeUuid = $request->route('uuid');
                if ($routeUuid) {
                    $participantId = $request->session()->get("participant_{$routeUuid}");
                    if ($participantId) {
                        $p = Participant::find($participantId);
                        if ($p) {
                            return $p;
                        }
                    }
                }

                // Fallback: active participant matching current session ID
                return Participant::where('session_id', $request->session()->getId())
                    ->whereNull('left_at')
                    ->latest('id')
                    ->first();
            }

            return null;
        });

        return $next($request);
    }
}

