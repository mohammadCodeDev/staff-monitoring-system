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

        // Define the departments
        $departments = [
            ['name' => 'departments.computer_engineering'],
            ['name' => 'departments.electrical_engineering'],
            ['name' => 'departments.mechanical_engineering'],
            ['name' => 'departments.civil_engineering'],
            ['name' => 'departments.university_management'],
        ];

        // Insert the departments into the database
        Department::insert($departments);
    }
}