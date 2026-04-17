<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('assigned_surveyor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_installer_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['assigned_surveyor_id']);
            $table->dropForeign(['assigned_installer_id']);
            $table->dropColumn(['assigned_surveyor_id', 'assigned_installer_id']);
        });
    }
};
