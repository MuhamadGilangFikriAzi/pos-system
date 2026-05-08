<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckShift
{
    /**
     * Kasir wajib buka shift sebelum bisa transaksi.
     * Admin/supervisor bypass.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();

            // Admin dan supervisor tidak perlu shift
            if (in_array($user->role, ['admin', 'supervisor'])) {
                return $next($request);
            }

            // Kasir: cek apakah ada shift open
            $activeShift = \App\Models\Shift::getActiveShift($user->id, $user->outlet_id ?? 1);

            if (!$activeShift) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda harus buka shift terlebih dahulu sebelum bertransaksi.',
                        'need_shift' => true,
                    ], 403);
                }

                return redirect()->route('pos.shift.open')
                    ->with('error', 'Silakan buka shift terlebih dahulu.');
            }

            // Inject shift ke request untuk digunakan di controller
            $request->merge(['active_shift' => $activeShift]);
        }

        return $next($request);
    }
}
