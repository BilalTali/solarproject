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
        Schema::create('agent_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users');
            $table->foreignId('lead_id')->constrained();
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'approved', 'paid'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('transaction_reference')->nullable();
            $table->text('notes')->nullable();
            // Super Agent override commission (stored on same record — not a separate table)
            $table->foreignId('super_agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('super_agent_override_amount', 10, 2)->default(0);
            $table->enum('super_agent_override_status', ['pending', 'approved', 'paid'])->default('pending');
            $table->timestamp('super_agent_override_paid_at')->nullable();
            $table->string('super_agent_override_payment_ref', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_commissions');
    }
};
