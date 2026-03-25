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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('ulid')->unique();
            $table->enum('source', ['public_form', 'agent_submission'])->default('public_form');
            $table->foreignId('assigned_agent_id')->nullable()->constrained('users');
            $table->foreignId('submitted_by_agent_id')->nullable()->constrained('users');

            // Beneficiary info
            $table->string('beneficiary_name');
            $table->string('beneficiary_mobile', 10);
            $table->string('beneficiary_whatsapp', 10)->nullable();
            $table->string('beneficiary_email')->nullable();
            $table->string('beneficiary_state');
            $table->string('beneficiary_district');
            $table->text('beneficiary_address')->nullable();
            $table->string('beneficiary_pincode')->nullable();

            // Technical info
            $table->string('consumer_number', 100)->nullable();
            $table->string('discom_name')->nullable();
            $table->enum('roof_size', ['less_100', '100_200', '200_300', '300_plus'])->nullable();
            $table->enum('system_capacity', ['1kw', '2kw', '3kw', 'above_3kw'])->nullable();
            $table->decimal('monthly_bill_amount', 10, 2)->nullable();

            // Status pipeline
            $table->enum('status', [
                'new',
                'registered',
                'installed',
                'rejected',
                'on_hold',
                'completed',
            ])->default('new');

            // Notes & tracking
            $table->text('query_message')->nullable();
            $table->text('admin_notes')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->string('govt_application_number', 100)->nullable();
            $table->string('rejection_reason')->nullable();

            // Commission
            $table->decimal('commission_amount', 10, 2)->nullable();
            $table->boolean('commission_paid')->default(false);
            $table->timestamp('commission_paid_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
