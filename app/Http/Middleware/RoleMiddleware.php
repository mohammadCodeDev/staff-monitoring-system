<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles The roles allowed to access the route.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect('login');
        }

        // Check if the user's role is in the list of allowed roles
        foreach ($roles as $role) {
            if (Auth::user()->role->role_name == $role) {
                return $next($request); // User has the role, proceed
            }
        }

        // If user does not have the required role, abort with a 403 Forbidden error
        abort(403, 'Unauthorized Action');
    }
}
