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
        Schema::table('users', function (Blueprint $table) {
            // Document columns
            $table->string('aadhaar_document', 500)->nullable()->after('aadhaar_number');
            $table->string('pan_document', 500)->nullable()->after('pan_number');
            $table->string('education_level', 50)->nullable()->after('qualification');
            $table->string('education_cert', 500)->nullable()->after('education_level');
            $table->string('resume', 500)->nullable()->after('education_cert');
            $table->string('mou_signed', 500)->nullable()->after('resume');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'aadhaar_document',
                'pan_document',
                'education_level',
                'education_cert',
                'resume',
                'mou_signed'
            ]);
        });
    }
};
