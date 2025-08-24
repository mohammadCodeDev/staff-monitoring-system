<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Employee extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'department_id',
        'group_id',
        'is_active',
        'profile_photo_path',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];


    /**
     * Appends custom attributes to the model's array form.
     *
     * @var array
     */
    protected $appends = ['full_name', 'profile_photo_url'];

    /**
     * Get the department that the employee belongs to.
     * An Employee belongs to one Department.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the group that the employee belongs to.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the attendance records for the employee.
     * An Employee can have many Attendance records.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the employee's full name.
     * This is an accessor to combine first_name and last_name.
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value, array $attributes) => $attributes['first_name'] . ' ' . $attributes['last_name'],
        );
    }

    /**
     * Get the URL for the employee's profile photo.
     * 
     */
    public function getProfilePhotoUrlAttribute(): string
    {
        if ($this->profile_photo_path) {
            // Returns the public URL to the stored image
            return Storage::url($this->profile_photo_path);
        }

        // Returns a default avatar from ui-avatars.com
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->fullName) . '&color=7F9CF5&background=EBF4FF';
    }
}
