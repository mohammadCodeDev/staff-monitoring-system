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
        // The order of priority for setting the locale is:
        // 1. Check the session for a 'locale' value.
        // 2. If not in session, check the browser's Accept-Language header.
        // 3. If none of the above, use the default from config/app.php.

        $locale = null;

        if (Session::has('locale')) {
            $locale = Session::get('locale');
        } else {
            // Get the browser's preferred language.
            $browserLocale = $request->getPreferredLanguage(['en', 'fa']);
            if ($browserLocale) {
                $locale = $browserLocale;
                // Store it in the session for subsequent requests.
                Session::put('locale', $locale);
            }
        }

        // If a valid locale was determined, set it for the application.
        if ($locale) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
