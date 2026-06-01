<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     * Ensures the user is authenticated via the admin guard.
     *
     * Instead of redirecting to a login page (which reveals its existence),
     * we abort with 404 to make the admin panel invisible to unauthorized users.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('admin')->check()) {
            abort(404);
        }

        return $next($request);
    }
}
