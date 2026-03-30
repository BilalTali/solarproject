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
        Schema::table('leads', function (Blueprint $table) {
            // Change status to VARCHAR(50) to support new 13-status set and future flexibility
            $table->string('status', 50)->default('NEW')->change();
            
            // Also change verification_status to VARCHAR(50) to relax ENUM restrictions
            $table->string('verification_status', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting to ENUM is risky if data already exists, but for completeness:
        Schema::table('leads', function (Blueprint $table) {
            // Reverting to a basic set if needed, but usually we leave it as string once relaxed
            $table->string('status', 100)->change();
            $table->string('verification_status', 100)->change();
        });
    }
};
