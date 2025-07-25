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
        Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('first_name'); // User's first name
        $table->string('last_name'); // User's last name
        $table->string('userName')->unique(); // A unique username for login
        
        // We replaced 'email' with 'phoneNumber'
        $table->string('phoneNumber')->unique(); // The user's phone number, must be unique

        $table->string('password'); // Laravel handles hashing automatically.
        $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
        $table->rememberToken();
        $table->timestamps();
    });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
