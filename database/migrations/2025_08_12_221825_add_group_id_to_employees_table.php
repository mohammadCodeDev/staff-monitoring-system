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
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('group_id')
                ->nullable() // Making the group optional
                ->after('department_id')
                ->constrained('groups')
                ->onDelete('set null'); // If group is deleted, set employee's group_id to null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
        $table->dropColumn('group_id');
        });
    }
};
