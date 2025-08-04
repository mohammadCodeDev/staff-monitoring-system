<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class ForceGuestLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If the user is a guest (not logged in)
        if (Auth::guest()) {
            // Force the application locale to Persian ('fa')
            App::setLocale('fa');
        }

        return $next($request);
    }
}
