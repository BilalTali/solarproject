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
        // 1. Drop existing agent_commissions table
        Schema::dropIfExists('agent_commissions');

        // 2. Create new commissions table
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            
            // Which lead this commission is for
            $table->unsignedBigInteger('lead_id');
            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
            
            // Who receives this commission (the payee)
            $table->unsignedBigInteger('payee_id');
            $table->foreign('payee_id')->references('id')->on('users')->onDelete('cascade');
            
            // 'super_agent' = admin is paying a super agent for this lead
            // 'agent'       = super agent (or admin directly) is paying an agent for this lead
            $table->enum('payee_role', ['super_agent', 'agent']);
            
            // The commission amount (entered manually by admin or super agent)
            $table->decimal('amount', 10, 2);
            
            // Who entered this commission
            $table->unsignedBigInteger('entered_by');
            $table->foreign('entered_by')->references('id')->on('users');
            
            // Payment tracking
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid');
            $table->timestamp('paid_at')->nullable();
            
            $table->unsignedBigInteger('paid_by')->nullable();
            $table->foreign('paid_by')->references('id')->on('users')->onDelete('set null');
            
            $table->enum('payment_method', ['bank_transfer', 'upi', 'cash', 'cheque'])->nullable();
            $table->string('payment_reference', 150)->nullable();
            $table->text('payment_notes')->nullable();
            
            // Edit lock: commission is locked from editing after 24 hours
            $table->timestamp('locked_at')->nullable();
            
            // Soft audit
            $table->timestamps();
            $table->softDeletes();
            
            // Constraints: only ONE commission record per lead per payee_role
            $table->unique(['lead_id', 'payee_role'], 'unique_lead_payee_role');
        });

        // 3. Add commission_entry_status to leads table
        Schema::table('leads', function (Blueprint $table) {
            $table->enum('commission_entry_status', ['none', 'super_agent_entered', 'agent_entered', 'both_entered'])
                  ->default('none')
                  ->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove commission_entry_status from leads table
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('commission_entry_status');
        });

        // Drop the new commissions table
        Schema::dropIfExists('commissions');

        // Recreate the old agent_commissions table (basic structure to allow rollback)
        Schema::create('agent_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('super_agent_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('agent_commission_amount', 10, 2)->default(0);
            $table->decimal('super_agent_amount', 10, 2)->default(0);
            $table->enum('agent_commission_status', ['pending', 'paid'])->default('pending');
            $table->enum('super_agent_status', ['pending', 'paid'])->default('pending');
            $table->timestamp('agent_commission_paid_at')->nullable();
            $table->timestamp('super_agent_paid_at')->nullable();
            $table->timestamps();
        });
    }
};
