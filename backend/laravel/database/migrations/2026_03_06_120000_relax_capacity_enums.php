<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Change leads table system_capacity to string
        Schema::table('leads', function (Blueprint $table) {
            $table->string('system_capacity')->nullable()->change();
        });

        // Change commission_slabs table capacity to string
        Schema::table('commission_slabs', function (Blueprint $table) {
            $table->string('capacity')->change();
        });
    }

    public function down(): void
    {
        // Reverting to enums is complex if custom strings were added,
        // but for safety we'll just leave them as strings or handle it if needed.
    }
};
