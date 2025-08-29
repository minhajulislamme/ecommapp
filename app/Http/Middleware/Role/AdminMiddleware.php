<?php

namespace App\Http\Middleware\Role;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        $user = $request->user();

        if (!$user->is_active) {
            abort(403, 'Your account has been deactivated.');
        }

        // Only allow admin role, not superadmin
        if (!$user->isAdmin()) {
            // Redirect to user's appropriate dashboard instead of showing 403
            return match ($user->role) {
                'superadmin' => redirect()->route('superadmin.dashboard'),
                'user' => redirect()->route('user.dashboard'),
                default => redirect()->route('user.dashboard'),
            };
        }

        return $next($request);
    }
}
