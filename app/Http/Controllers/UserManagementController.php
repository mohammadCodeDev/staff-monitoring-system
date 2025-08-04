<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    /**
     * Display a list of users to manage their roles.
     */
    public function index()
    {
        // Get all users except the current admin, as they cannot change their own role.
        $users = User::with('role')->where('id', '!=', Auth::id())->get();
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Update the role of a specific user.
     */
    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')],
        ]);

        $newRoleId = $request->input('role_id');
        $adminRole = Role::where('role_name', 'Roles.System Admin')->first();
        $observerRole = Role::where('role_name', 'Roles.System Observer')->first();

        if ($newRoleId == $adminRole->id) {
            DB::transaction(function () use ($user, $adminRole, $observerRole) {
                // THE FIX: Fetch a fresh, full Eloquent model instance of the current admin.
                $currentAdmin = User::find(Auth::id());

                // Now, the update method will work correctly on this instance.
                $currentAdmin->update(['role_id' => $observerRole->id]);

                // Promote the target user to 'System Admin'
                $user->update(['role_id' => $adminRole->id]);
            });
        } else {
            $user->update(['role_id' => $newRoleId]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', __('User role updated successfully.'));
    }
}
