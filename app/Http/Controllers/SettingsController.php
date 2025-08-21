<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    /**
     * Update the application's locale.
     */
    public function updateLocale(Request $request): RedirectResponse
    {
        // Validate the incoming locale
        $request->validate([
            'locale' => ['required', 'string', 'in:en,fa,system'],
        ]);

        // Get the authenticated user
        $user = $request->user();

        // Update the user's locale in the database
        $user->locale = $request->locale;
        $user->save();

        // Also update the session immediately for the response
        Session::put('locale', $request->locale);

        return Redirect::route('settings')->with('status', 'locale-updated');
    }

    /**
     * Update the application's theme.
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateTheme(Request $request): RedirectResponse
    {
        // 1. Validate the incoming theme
        $request->validate([
            'theme' => ['required', 'string', Rule::in(['light', 'dark', 'system'])],
        ]);

        // 2. Get the authenticated user
        $user = $request->user();

        // 3. Update the user's theme in the database
        $user->theme = $request->theme;
        $user->save();

        // 4. Redirect back with a success message
        return Redirect::route('settings')->with('status', 'theme-updated');
    }

    /**
     * Update the user's font size preference.
     */
    public function updateFontSize(Request $request): RedirectResponse
    {
        // 1. Validate the incoming font size (must be a number between 80 and 120)
        $request->validate([
            'font_size' => ['required', 'numeric', 'min:80', 'max:120'],
        ]);

        // 2. Get the user and update the value
        $user = $request->user();
        $user->font_size = $request->font_size;
        $user->save();

        // 3. Redirect back with a success message
        return Redirect::route('settings')->with('status', 'font-size-updated');
    }
}
