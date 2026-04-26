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
        Schema::table('super_agent_absorbed_points', function (Blueprint $table) {
            // Drop foreign keys first to allow dropping the unique index they depend on
            $table->dropForeign(['source_agent_id']);
            $table->dropForeign(['offer_id']);
            
            // Drop the old unique point mapping
            $table->dropUnique('idx_sa_absorbed_unique');
            
            // Add the new column
            $table->foreignId('lead_id')->nullable()->after('offer_id')->constrained()->nullOnDelete();
            
            // Re-add the dropped foreign keys (Laravel will create individual indexes as needed)
            $table->foreign('source_agent_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('offer_id')->references('id')->on('offers')->cascadeOnDelete();
            
            // New unique constraint: one absorption record per lead per offer per agent
            $table->unique(['offer_id', 'lead_id', 'source_agent_id'], 'idx_sa_lead_absorbed_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('super_agent_absorbed_points', function (Blueprint $table) {
            $table->dropUnique('idx_sa_lead_absorbed_unique');
            $table->dropForeign(['lead_id']);
            $table->dropColumn('lead_id');
            
            // Restore legacy unique constraint
            $table->unique(['offer_id', 'source_agent_id'], 'idx_sa_absorbed_unique');
        });
    }
};
