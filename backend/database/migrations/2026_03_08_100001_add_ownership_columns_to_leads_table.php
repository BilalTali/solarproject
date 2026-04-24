<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Extend the source ENUM via raw SQL (avoids doctrine/dbal dependency)
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE leads MODIFY COLUMN source ENUM('public_form','agent_submission','super_agent_submission','admin_manual') NOT NULL DEFAULT 'public_form'");
        }

        Schema::table('leads', function (Blueprint $table) {
            // Track which super agent created this lead
            $table->unsignedBigInteger('created_by_super_agent_id')->nullable()->after('submitted_by_agent_id');
            $table->foreign('created_by_super_agent_id')->references('id')->on('users')->nullOnDelete();

            // Who currently "owns" this lead (controls visibility)
            $table->enum('owner_type', ['admin_pool', 'super_agent_pool', 'agent_pool'])
                ->default('admin_pool')
                ->after('created_by_super_agent_id');

            // Verification pipeline
            $table->enum('verification_status', [
                'not_required',
                'pending_super_agent_verification',
                'super_agent_verified',
                'reverted_to_agent',
                'admin_override',
            ])->default('not_required')->after('owner_type');

            $table->unsignedTinyInteger('revert_count')->default(0)->after('verification_status');
            $table->text('revert_reason')->nullable()->after('revert_count');

            $table->unsignedBigInteger('verified_by_super_agent_id')->nullable()->after('revert_reason');
            $table->foreign('verified_by_super_agent_id')->references('id')->on('users')->nullOnDelete();

            $table->timestamp('verified_at')->nullable()->after('verified_by_super_agent_id');
            $table->timestamp('reverted_at')->nullable()->after('verified_at');

            $table->unsignedBigInteger('reverted_by')->nullable()->after('reverted_at');
            $table->foreign('reverted_by')->references('id')->on('users')->nullOnDelete();
        });

        // Backfill: set owner_type based on existing data
        DB::statement("
            UPDATE leads SET owner_type = 
                CASE 
                    WHEN assigned_agent_id IS NOT NULL THEN 'agent_pool'
                    WHEN assigned_super_agent_id IS NOT NULL THEN 'super_agent_pool'
                    ELSE 'admin_pool'
                END
        ");
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['created_by_super_agent_id']);
            $table->dropForeign(['verified_by_super_agent_id']);
            $table->dropForeign(['reverted_by']);
            $table->dropColumn([
                'created_by_super_agent_id',
                'owner_type',
                'verification_status',
                'revert_count',
                'revert_reason',
                'verified_by_super_agent_id',
                'verified_at',
                'reverted_at',
                'reverted_by',
            ]);
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE leads MODIFY COLUMN source ENUM('public_form','agent_submission') NOT NULL DEFAULT 'public_form'");
        }

    }
};
