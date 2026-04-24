<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B1 — Add downline visibility flag to lead_documents.
 *
 * Enables admins/BDEs to mark individual documents as
 * visible to agents/enumerators (downlines). Default FALSE
 * preserves existing behaviour (nothing shared).
 *
 * Also records the role of the uploader for audit purposes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lead_documents', function (Blueprint $table) {
            $table->boolean('visible_to_downline')->default(false)->after('uploaded_by');
            $table->string('uploaded_by_role', 50)->nullable()->after('visible_to_downline');
        });
    }

    public function down(): void
    {
        Schema::table('lead_documents', function (Blueprint $table) {
            $table->dropColumn(['visible_to_downline', 'uploaded_by_role']);
        });
    }
};
