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
        Schema::create('super_agent_absorbed_points', function (Blueprint $table) {
            $table->id();

            $table->foreignId('super_agent_id')
                  ->constrained('users')
                  ->cascadeOnDelete()
                  ->comment('The super agent who absorbs the points');

            $table->foreignId('source_agent_id')
                  ->constrained('users')
                  ->cascadeOnDelete()
                  ->comment('The agent whose points are being absorbed');

            $table->foreignId('offer_id')
                  ->constrained('offers')
                  ->cascadeOnDelete()
                  ->comment('The offer from which points are absorbed');

            $table->unsignedInteger('absorbed_installations')
                  ->comment('Number of partial installations absorbed (the shortfall amount)');

            $table->unsignedInteger('agent_total_installations')
                  ->comment('Agent\'s total installations in this offer at time of expiry');

            $table->unsignedInteger('offer_target')
                  ->comment('The target_installations of the offer — stored for historical accuracy');

            $table->enum('absorption_reason', [
                'agent_fell_short',          // agent's unredeemed < target at expiry
                'grace_period_expired',      // agent was eligible but did not claim in grace period
            ])->default('agent_fell_short');

            $table->timestamp('absorbed_at')
                  ->useCurrent()
                  ->comment('When the absorption was processed (offer expiry job ran)');

            $table->enum('status', ['unclaimed', 'claimed', 'delivered'])
                  ->default('unclaimed')
                  ->comment('unclaimed: SA has not claimed yet · claimed: admin notified · delivered: admin confirmed');

            $table->foreignId('claimed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->comment('Null until claimed — the super agent who clicked claim');

            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->foreignId('approved_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->text('admin_notes')->nullable();

            $table->timestamps();

            // Composite unique: one absorption row per (super_agent × agent × offer)
            $table->unique(['super_agent_id', 'source_agent_id', 'offer_id'], 'uq_absorption');

            $table->index(['super_agent_id', 'status']);
            $table->index(['offer_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('super_agent_absorbed_points');
    }
};
