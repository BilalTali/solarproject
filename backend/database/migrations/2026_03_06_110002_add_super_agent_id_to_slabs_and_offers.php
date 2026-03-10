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
        Schema::table('commission_slabs', function (Blueprint $table) {
            $table->dropUnique(['capacity']);
            $table->foreignId('super_agent_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
            $table->unique(['capacity', 'super_agent_id']);
        });

        Schema::table('incentive_offers', function (Blueprint $table) {
            $table->foreignId('super_agent_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('commission_slabs', function (Blueprint $table) {
            $table->dropUnique(['capacity', 'super_agent_id']);
            $table->dropForeign(['super_agent_id']);
            $table->dropColumn('super_agent_id');
            $table->unique(['capacity']);
        });

        Schema::table('incentive_offers', function (Blueprint $table) {
            $table->dropForeign(['super_agent_id']);
            $table->dropColumn('super_agent_id');
        });
    }
};
