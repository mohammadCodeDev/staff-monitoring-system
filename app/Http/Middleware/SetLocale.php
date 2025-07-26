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
        $localePreference = null;

        // Priority 1: Get locale from the authenticated user's profile.
        if ($user && $user->locale) {
            $localePreference = $user->locale;
        }
        // Priority 2: If no user or user has no preference, check the session.
        elseif (Session::has('locale')) {
            $localePreference = Session::get('locale');
        }

        // Determine the final locale to set.
        $localeToSet = config('app.fallback_locale'); // Default to fallback locale

        if ($localePreference === 'system') {
            // Resolve 'system' to the browser's preferred language.
            $localeToSet = $request->getPreferredLanguage(['en', 'fa']) ?? config('app.fallback_locale');
        } elseif (in_array($localePreference, ['en', 'fa'])) {
            // Use the specific preference if it's 'en' or 'fa'.
            $localeToSet = $localePreference;
        }

        // Apply the final locale.
        App::setLocale($localeToSet);

        return $next($request);
    }
}
