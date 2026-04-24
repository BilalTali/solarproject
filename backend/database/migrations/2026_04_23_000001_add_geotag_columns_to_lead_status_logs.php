<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B1 — Add geotag evidence columns to lead_status_logs.
 *
 * Allows technical-team status transitions to embed their
 * geo-tagged selfie path + GPS coordinates directly in the
 * audit log so the admin timeline can display them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lead_status_logs', function (Blueprint $table) {
            $table->string('geotag_photo_path', 500)->nullable()->after('notes');
            $table->decimal('latitude', 10, 8)->nullable()->after('geotag_photo_path');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->string('changed_by_role', 50)->nullable()->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('lead_status_logs', function (Blueprint $table) {
            $table->dropColumn(['geotag_photo_path', 'latitude', 'longitude', 'changed_by_role']);
        });
    }
};
