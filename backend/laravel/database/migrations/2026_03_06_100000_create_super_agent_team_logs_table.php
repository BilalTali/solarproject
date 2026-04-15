<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('super_agent_team_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('super_agent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('agent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users');
            $table->timestamp('assigned_at');
            $table->timestamp('unassigned_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['super_agent_id', 'agent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('super_agent_team_logs');
    }
};
