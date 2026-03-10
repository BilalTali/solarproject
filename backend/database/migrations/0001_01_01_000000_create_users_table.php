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
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('mobile', 10)->unique();
            $table->string('password')->nullable();
            $table->enum('role', ['admin', 'super_agent', 'agent'])->default('agent');
            $table->enum('status', ['active', 'inactive', 'pending'])->default('pending');
            $table->string('agent_id', 20)->unique()->nullable();
            $table->string('super_agent_code', 20)->unique()->nullable(); // SSM-YYYY-XXXX (super agents only)
            $table->foreignId('super_agent_id')->nullable()->constrained('users')->nullOnDelete(); // agents → their super agent
            $table->json('managed_states')->nullable(); // super agents only: array of state names
            $table->string('district')->nullable();
            $table->string('state')->nullable();
            $table->string('area')->nullable();
            $table->text('aadhaar_number')->nullable();
            $table->string('whatsapp_number', 10)->nullable();
            $table->string('occupation')->nullable();
            $table->string('profile_photo')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
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
