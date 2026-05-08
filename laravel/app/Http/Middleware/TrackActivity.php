<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackActivity
{
    /**
     * Track last activity time and check idle timeout.
     * Auto logout jika idle > 30 menit (1800 detik).
     */
    public function handle(Request $request, Closure $next, int $idleTimeout = 1800): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            $now = now();

            // Cek idle timeout
            if ($user->last_activity_at) {
                $idleSeconds = $now->diffInSeconds($user->last_activity_at);
                if ($idleSeconds > $idleTimeout) {
                    // Log idle logout
                    if (class_exists('\App\Models\ActivityLog')) {
                        \App\Models\ActivityLog::log('auto_logout', 'Auto logout karena idle > ' . ($idleTimeout / 60) . ' menit');
                    }
                    auth()->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Sesi berakhir karena tidak ada aktivitas. Silakan login ulang.',
                            'idle_logout' => true,
                        ], 401);
                    }

                    return redirect()->route('login')->with('error', 'Sesi berakhir karena tidak ada aktivitas.');
                }
            }

            // Update last activity
            $user->last_activity_at = $now;
            $user->saveQuietly(); // no events
        }

        return $next($request);
    }
}
