<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Group;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Prevents running if the table already has data
        if (Group::count() > 0) {
            return;
        }

        Group::create([
            'name' => ['en' => 'Software Engineering', 'fa' => 'مهندسی نرم افزار'],
            'department_id' => 1, // Assumes a department with id=1 exists
        ]);
        Group::create([
            'name' => ['en' => 'Artificial Intelligence', 'fa' => 'هوش مصنوعی'],
            'department_id' => 1,
        ]);
    }
}
