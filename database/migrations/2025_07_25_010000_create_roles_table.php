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
        Schema::create('roles', function (Blueprint $table) {
        $table->id(); // Corresponds to bigint, unsigned, auto-increment primary key
        $table->string('role_name')->unique(); // The role's name, e.g., 'admin', 'guard'. Must be unique.
        $table->timestamps(); // Adds created_at and updated_at columns
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
