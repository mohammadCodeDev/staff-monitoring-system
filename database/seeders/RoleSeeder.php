<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Role; //importing the Role model

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Prevents running if the table already has data
        if (Role::count() > 0) {
            return;
        }

        // Define the roles based on the project description
        $roles = [
            ['role_name' => 'System Admin'],          // ID: 1
            ['role_name' => 'Guard'],                 // ID: 2
            ['role_name' => 'System Observer'],       // ID: 3
            ['role_name' => 'University President'],  // ID: 4
            ['role_name' => 'Faculty Head'],          // ID: 5
            ['role_name' => 'Group Manager'],         // ID: 6
        ];

        // Insert the roles into the database
        Role::insert($roles);
    }
}
