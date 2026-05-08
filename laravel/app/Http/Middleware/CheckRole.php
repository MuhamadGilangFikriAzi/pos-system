<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle role check.
     * Usage: role:admin or role:admin,supervisor (multiple roles allowed)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            abort(401, 'Unauthorized.');
        }

        $userRole = $request->user()->role;

        // Check if user's role is in allowed roles
        $allowed = collect($roles)->filter()->map('trim')->toArray();
        if (empty($allowed)) {
            $allowed = ['admin']; // default
        }

        if (!in_array($userRole, $allowed)) {
            abort(403, 'Akses ditolak. Hanya untuk: ' . implode(', ', $allowed) . '.');
        }

        return $next($request);
    }
}
