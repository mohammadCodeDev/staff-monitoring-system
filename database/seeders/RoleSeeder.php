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
            ['role_name' => 'Roles.System Admin'],          // ID: 1
            ['role_name' => 'Roles.Guard'],                 // ID: 2
            ['role_name' => 'Roles.System Observer'],       // ID: 3
            ['role_name' => 'Roles.University President'],  // ID: 4
            ['role_name' => 'Roles.Faculty Head'],          // ID: 5
            ['role_name' => 'Roles.Group Manager'],         // ID: 6
            ['role_name' => 'Roles.No Role'],               // ID: 7
        ];

        // Insert the roles into the database
        Role::insert($roles);
    }
}
