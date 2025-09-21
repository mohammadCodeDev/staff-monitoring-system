<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'userName',
        'phoneNumber',
        'password',
        'role_id',
        'locale',
        'theme',
        'font_size',
        'date_format',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the role associated with the user.
     * A User belongs to one Role.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the groups managed by the user.
     * A User (as a manager) can manage many Groups.
     */
    public function managedGroups(): HasMany
    {
        return $this->hasMany(Group::class, 'manager_id');
    }

    /**
     * Get the attendance records created by this user (guard).
     * A User (as a guard) can create many Attendance records.
     */
    public function recordedAttendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'guard_id');
    }
}
