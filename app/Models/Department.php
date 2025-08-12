<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Department extends Model
{
    use HasFactory, HasTranslations;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    public $translatable = ['name'];

    protected $fillable = [
        'name',
        'manager_id',
    ];

    /**
     * Get the employees belonging to this department.
     * A Department can have many Employees.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Get the groups belonging to this department.
     * A Department can have many Groups.
     */
    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    /**
     * Get the user that manages the department.
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
}
