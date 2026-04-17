<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'super_agent', 'agent', 'enumerator', 'operator', 'super_admin', 'field_technical_team') NOT NULL DEFAULT 'agent'");

        Schema::table('users', function (Blueprint $table) {
            $table->string('technician_type')->nullable()->after('role'); // 'engineer', 'installer', 'worker'
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('technician_type');
        });
    }
};
