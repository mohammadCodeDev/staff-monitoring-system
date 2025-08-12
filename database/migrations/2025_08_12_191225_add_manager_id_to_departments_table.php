<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            // Add the new column for the foreign key
            $table->foreignId('manager_id')
                ->nullable() // The manager can be optional
                ->after('name') // Place it after the 'name' column
                ->constrained('users') // Create foreign key to 'id' on 'users' table
                ->onDelete('set null'); // If a manager (user) is deleted, set this field to null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            // To properly drop the column, first drop the foreign key constraint
            $table->dropForeign(['manager_id']);
            $table->dropColumn('manager_id');
        });
    }
};
