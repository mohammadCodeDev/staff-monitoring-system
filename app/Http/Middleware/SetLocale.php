<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        // Start with the application's default fallback locale.
        $localeToSet = config('app.fallback_locale');

        if ($user) {
            // --- LOGIC FOR AUTHENTICATED USERS ---
            // If the user is logged in, use their saved preference.
            $preference = $user->locale;

            if ($preference === 'system') {
                // Resolve 'system' preference to the browser's language.
                $localeToSet = $request->getPreferredLanguage(['en', 'fa']) ?? config('app.fallback_locale');
            } elseif (in_array($preference, ['en', 'fa'])) {
                // Use the specific preference if it's 'en' or 'fa'.
                $localeToSet = $preference;
            }
        } else {
            // --- LOGIC FOR GUEST USERS ---
            // If the user is not logged in, always default to Persian.
            $localeToSet = 'fa';
        }

        // Apply the final determined locale for the request.
        App::setLocale($localeToSet);

        return $next($request);
    }
}
