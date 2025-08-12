<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department; // Import the Department model

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Prevents running if the table already has data
        if (Department::count() > 0) {
            return;
        }

        // Using create() method for each department to handle translatable names properly
        Department::create([
            'name' => [
                'en' => 'Computer Engineering',
                'fa' => 'مهندسی کامپیوتر'
            ]
        ]);

        Department::create([
            'name' => [
                'en' => 'Electrical Engineering',
                'fa' => 'مهندسی برق'
            ]
        ]);

        Department::create([
            'name' => [
                'en' => 'Mechanical Engineering',
                'fa' => 'مهندسی مکانیک'
            ]
        ]);

        Department::create([
            'name' => [
                'en' => 'Civil Engineering',
                'fa' => 'مهندسی عمران'
            ]
        ]);

        Department::create([
            'name' => [
                'en' => 'University Management',
                'fa' => 'مدیریت دانشگاه'
            ]
        ]);
    }
}