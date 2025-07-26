<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            //userName and phoneNumber are unique in the 'users' table.
            'userName' => ['required', 'string', 'max:255', 'unique:'.User::class],
            'phoneNumber' => ['required', 'string', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'userName' => $request->userName,
            'phoneNumber' => $request->phoneNumber,
            'password' => Hash::make($request->password),
            // IMPORTANT: Assign a default role_id for new users.
            // You should fetch this ID dynamically from the roles table based on the role name,
            // e.g., Role::where('role_name', 'guard')->first()->id.
            // Here, we are assuming '2' is the ID for a default role like 'Guard'.
            'role_id' => 2,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
