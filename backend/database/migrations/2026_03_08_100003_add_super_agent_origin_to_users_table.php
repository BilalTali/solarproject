<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Track which super agent originally created this agent (even if SA assignment changes later)
            $table->unsignedBigInteger('created_by_super_agent_id')->nullable()->after('super_agent_id');
            $table->foreign('created_by_super_agent_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['created_by_super_agent_id']);
            $table->dropColumn('created_by_super_agent_id');
        });
    }
};
