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
        // Validate that the selected locale is one of the allowed values.
        $request->validate([
            'locale' => ['required', 'string', 'in:en,fa,system'],
        ]);

        // Store the selected locale in the user's session.
        Session::put('locale', $request->locale);

        // Redirect the user back to the settings page with a success message.
        return Redirect::route('settings')->with('status', 'locale-updated');
    }
}
