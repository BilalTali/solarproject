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
        if (!Schema::hasTable('offers')) {
            Schema::create('offers', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('prize_label');
                $table->decimal('prize_amount', 10, 2)->nullable();
                $table->string('prize_image_url')->nullable();
                
                // Types
                $table->enum('offer_type', ['individual', 'collective'])->default('individual');
                $table->enum('visible_to', ['agent', 'super_agent', 'both'])->default('both');
                
                // Decimal for Target Points (v3 point-based system)
                $table->decimal('target_installations', 8, 2)->default(0); 
                
                // Active period
                $table->timestamp('offer_from')->nullable();
                $table->timestamp('offer_to')->nullable();
                
                // Status and meta
                $table->enum('status', ['draft', 'live', 'ended'])->default('draft');
                $table->boolean('is_featured')->default(false);
                $table->boolean('is_annual')->default(false);
                $table->integer('display_order')->default(0);
                
                // Collective fields
                $table->unsignedInteger('current_installations')->default(0); // Optional: keep as int if collective remains raw count, or change to decimal if collective uses points. Keeping int for now as default.
                $table->boolean('collective_redeemed')->default(false);
                $table->timestamp('collective_redeemed_at')->nullable();
                
                // Expiry tracking
                $table->timestamp('absorption_processed_at')->nullable();
                
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('offer_progress')) {
            Schema::create('offer_progress', function (Blueprint $table) {
                $table->id();
                $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                
                $table->enum('role_context', ['agent', 'super_agent'])->default('agent');
                
                // v3 Decimal points tracking
                $table->decimal('total_installations', 8, 2)->default(0);
                $table->decimal('redeemed_installations', 8, 2)->default(0);
                $table->decimal('unredeemed_installations', 8, 2)->default(0);
                
                $table->integer('redemption_count')->default(0);
                $table->boolean('can_redeem')->default(false);
                $table->integer('pending_redemption_count')->default(0);
                
                $table->timestamp('first_installation_at')->nullable();
                $table->timestamp('last_installation_at')->nullable();
                $table->timestamp('last_redeemed_at')->nullable();
                
                $table->timestamp('offer_ended_zeroed_at')->nullable();
                
                $table->timestamps();
                
                $table->unique(['offer_id', 'user_id']);
            });
        }

        if (!Schema::hasTable('offer_redemptions')) {
            Schema::create('offer_redemptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                
                $table->integer('redemption_number');
                $table->decimal('installations_used', 8, 2); // decimal points used
                
                $table->enum('status', ['pending', 'approved', 'delivered', 'rejected'])->default('pending');
                $table->text('notes')->nullable();
                
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                
                $table->timestamp('claimed_at');
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('offer_installation_logs')) {
            Schema::create('offer_installation_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
                $table->decimal('points_awarded', 8, 2)->default(0);
                $table->timestamp('installed_at');
                $table->timestamps();
                
                $table->unique(['offer_id', 'lead_id']); // Idempotency key
            });
        }
        
        // Track absorbed points for Super Agents
        if (!Schema::hasTable('super_agent_absorbed_points')) {
            Schema::create('super_agent_absorbed_points', function (Blueprint $table) {
                $table->id();
                $table->foreignId('super_agent_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('source_agent_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('offer_id')->constrained('offers')->cascadeOnDelete();
                
                $table->decimal('absorbed_installations', 8, 2);
                $table->decimal('agent_total_installations', 8, 2);
                $table->decimal('offer_target', 8, 2);
                $table->string('absorption_reason');
                
                $table->timestamp('absorbed_at');
                $table->enum('status', ['unclaimed', 'claimed'])->default('unclaimed');
                $table->timestamp('claimed_at')->nullable();
                
                $table->timestamps();
                $table->unique(['offer_id', 'source_agent_id'], 'idx_sa_absorbed_unique');
            });
        }
        
        // Expiry run logs
        if (!Schema::hasTable('offer_expiry_logs')) {
            Schema::create('offer_expiry_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
                $table->integer('agents_processed');
                $table->integer('agents_absorbed');
                $table->integer('agents_grace_period_expired');
                $table->decimal('total_points_absorbed', 8, 2);
                $table->decimal('total_points_discarded', 8, 2);
                $table->json('agent_breakdown');
                $table->timestamp('processed_at');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_expiry_logs');
        Schema::dropIfExists('super_agent_absorbed_points');
        Schema::dropIfExists('offer_installation_logs');
        Schema::dropIfExists('offer_redemptions');
        Schema::dropIfExists('offer_progress');
        Schema::dropIfExists('offers');
    }
};
