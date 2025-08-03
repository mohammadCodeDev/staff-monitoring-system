<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Calling the RoleSeeder to populate the roles table
        $this->call([
            RoleSeeder::class,
            DepartmentSeeder::class,
        ]);

        //for adding other seeders here in the future
    }
}
