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
        Schema::table('users', function (Blueprint $table) {
            // Add a new 'locale' column to store the user's language preference.
            // Default to 'fa' (Persian).
            $table->string('locale', 10)->default('fa')->after('role_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove the 'locale' column if the migration is rolled back.
            $table->dropColumn('locale');
        });
    }
};
