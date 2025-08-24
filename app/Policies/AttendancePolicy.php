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
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Attendance $attendance): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
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
            // Check if the record's creation timestamp is within the last 7 days.
            return Carbon::parse($attendance->created_at)->greaterThanOrEqualTo(Carbon::now()->subDays(7));
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
