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
        Schema::create('offer_expiry_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained('offers')->cascadeOnDelete();

            $table->unsignedInteger('agents_processed');
              // Number of agents whose progress was evaluated
            $table->unsignedInteger('agents_absorbed');
              // Number whose partial points were absorbed by their SA
            $table->unsignedInteger('agents_grace_period_expired');
              // Number who were eligible but grace period passed
            $table->unsignedInteger('total_points_absorbed');
              // Sum of all absorbed_installations in this run
            $table->unsignedInteger('total_points_discarded');
              // Points belonging to agents with no SA (no one to absorb)

            $table->json('agent_breakdown')->nullable();
              // JSON summary of each agent: {agent_id, status, absorbed_to}

            $table->timestamp('processed_at')->useCurrent();
            $table->index('offer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_expiry_logs');
    }
};
