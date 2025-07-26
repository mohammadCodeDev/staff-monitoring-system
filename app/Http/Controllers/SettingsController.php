<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

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
}
