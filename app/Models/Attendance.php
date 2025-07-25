<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',
        'guard_id',
        'timestamp',
        'event_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'timestamp' => 'datetime',
    ];

    /**
     * Get the employee for this attendance record.
     * An Attendance record belongs to one Employee.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the guard (user) who created this record.
     * The relationship is renamed to "recorder" to avoid conflict with a built-in Laravel method.
     * An Attendance record belongs to one User (the guard).
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guard_id');
    }
}
