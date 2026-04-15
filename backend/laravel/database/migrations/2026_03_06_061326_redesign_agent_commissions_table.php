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
        Schema::table('agent_commissions', function (Blueprint $table) {
            // Drop obsolete columns based on actual SQLite schema
            $table->dropColumn([
                'paid_at',
                'notes',
            ]);

            // ── AGENT PAYMENT TRACK ──────────────────────────────────────────
            $table->decimal('agent_commission_amount', 10, 2)->default(0)->after('agent_id');
            $table->enum('agent_commission_status', ['pending', 'paid'])->default('pending')->after('agent_commission_amount');
            $table->timestamp('agent_commission_paid_at')->nullable()->after('agent_commission_status');
            $table->foreignId('agent_commission_paid_by')->nullable()->constrained('users')->nullOnDelete()->after('agent_commission_paid_at');
            $table->string('agent_commission_payment_method', 50)->nullable()->after('agent_commission_paid_by');
            $table->string('agent_commission_payment_ref', 100)->nullable()->after('agent_commission_payment_method');
            $table->text('agent_commission_notes')->nullable()->after('agent_commission_payment_ref');

            // ── SUPER AGENT PAYMENT TRACK ────────────────────────────────────
            // super_agent_id already exists from a previous migration, but let's ensure it's positioned correctly conceptually
            $table->decimal('super_agent_amount', 10, 2)->default(0)->after('super_agent_id');
            $table->enum('super_agent_status', ['pending', 'paid'])->default('pending')->after('super_agent_amount');
            $table->timestamp('super_agent_paid_at')->nullable()->after('super_agent_status');
            $table->foreignId('super_agent_paid_by')->nullable()->constrained('users')->nullOnDelete()->after('super_agent_paid_at');
            $table->string('super_agent_payment_method', 50)->nullable()->after('super_agent_paid_by');
            $table->string('super_agent_payment_ref', 100)->nullable()->after('super_agent_payment_method');
            $table->text('super_agent_notes')->nullable()->after('super_agent_payment_ref');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_commissions', function (Blueprint $table) {
            // Drop new columns
            $table->dropForeign(['agent_commission_paid_by']);
            $table->dropForeign(['super_agent_paid_by']);
            $table->dropColumn([
                'agent_commission_amount',
                'agent_commission_status',
                'agent_commission_paid_at',
                'agent_commission_paid_by',
                'agent_commission_payment_method',
                'agent_commission_payment_ref',
                'agent_commission_notes',
                'super_agent_amount',
                'super_agent_status',
                'super_agent_paid_at',
                'super_agent_paid_by',
                'super_agent_payment_method',
                'super_agent_payment_ref',
                'super_agent_notes',
            ]);

            // Re-add obsolete columns (with defaults to avoid errors on rollback)
            $table->enum('status', ['pending', 'approved', 'paid'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->decimal('amount', 10, 2)->default(0);
            $table->string('payment_method')->nullable();
            $table->string('transaction_reference')->nullable();

            $table->decimal('super_agent_override_amount', 10, 2)->default(0);
            $table->enum('super_agent_override_status', ['pending', 'approved', 'paid'])->default('pending');
            $table->timestamp('super_agent_override_paid_at')->nullable();
            $table->string('super_agent_override_payment_ref')->nullable();
        });
    }
};
