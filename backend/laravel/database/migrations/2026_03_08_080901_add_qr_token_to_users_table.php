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
        Schema::table('users', function (Blueprint $table) {
            $table->string('qr_token', 64)->unique()->nullable()->after('letter_number');
            $table->timestamp('qr_generated_at')->nullable()->after('qr_token');
            $table->timestamp('last_verified_at')->nullable()->after('qr_generated_at');
            $table->integer('scan_count')->unsigned()->default(0)->after('last_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['qr_token', 'qr_generated_at', 'last_verified_at', 'scan_count']);
        });
    }
};
