<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->enum('action', ['verified', 'reverted'])->index();
            $table->unsignedBigInteger('performed_by');
            $table->foreign('performed_by')->references('id')->on('users');
            $table->enum('performer_role', ['super_agent', 'admin']);
            $table->text('reason')->nullable(); // required when action='reverted'
            $table->unsignedTinyInteger('revert_count_at_time')->default(0);
            // Immutable audit log — no updated_at
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_verifications');
    }
};
