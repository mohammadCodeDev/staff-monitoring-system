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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            // Foreign key for the employee/professor being tracked
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');

            // Foreign key for the guard who registered the event
            $table->foreignId('guard_id')->constrained('users')->onDelete('cascade');

            // The exact time of the event
            $table->timestamp('timestamp');

            // The type of event: 'entry' or 'exit'
            $table->enum('event_type', ['entry', 'exit']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
