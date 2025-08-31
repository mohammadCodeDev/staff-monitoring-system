<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Carbon\Carbon;

class AttendancePolicy
{
    /**
     * Determine whether the user can view any models.
     * This is checked for the index and raw-log pages.
     */
    public function viewAny(User $user): bool
    {
        // Allow users with specific roles to view the attendance logs.
        // We list all roles that are allowed to see any kind of log.
        $allowedRoles = [
            'Roles.System Admin',
            'Roles.Guard',
            'Roles.System Observer',
            'Roles.University President',
            'Roles.Faculty Head',
            'Roles.Group Manager'
        ];

        return in_array($user->role->role_name, $allowedRoles);
    }

    /**
     * Determine whether the user can view the model.
     * This is not currently used, but we'll set it to false for now.
     */
    public function view(User $user, Attendance $attendance): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     * This is checked before showing the "Log Attendance" button/page.
     */
    public function create(User $user): bool
    {
        // Only System Admins and Guards should be able to create new records.
        return in_array($user->role->role_name, [
            'Roles.System Admin',
            'Roles.Guard'
        ]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Attendance $attendance): bool
    {
        // A System Admin can always update.
        if ($user->role->role_name === 'Roles.System Admin') {
            return true;
        }

        // A Guard can update only if the record is not older than 7 days.
        if ($user->role->role_name === 'Roles.Guard') {
            return $attendance->timestamp->greaterThanOrEqualTo(Carbon::now()->subDays(7));
        }

        // Other roles cannot update.
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Attendance $attendance): bool
    {
        // We can reuse the same logic from the update method.
        return $this->update($user, $attendance);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Attendance $attendance): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Attendance $attendance): bool
    {
        return false;
    }
}
