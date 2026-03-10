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
            // ── PERSONAL DETAILS ───────────────────────────────────────────
            // some fields like father_name, dob, blood_group might already exist from previous tasks, 
            // but let's ensure they are all here or handled safely.
            // Check User model/previous migrations: 
            // father_name, dob, blood_group were present in User.php $fillable and some migrations.
            
            $table->string('religion', 100)->nullable()->after('blood_group');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('dob');
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable()->after('religion');

            // ── CONTACT & ADDRESS ──────────────────────────────────────────
            $table->text('permanent_address')->nullable()->after('area');
            $table->text('current_address')->nullable()->after('permanent_address');
            $table->string('pincode', 10)->nullable()->after('current_address');
            $table->string('landmark', 255)->nullable()->after('pincode');

            // ── IDENTITY DOCUMENTS ────────────────────────────────────────
            $table->string('pan_number', 10)->nullable()->after('aadhaar_number');
            $table->string('voter_id', 20)->nullable()->after('pan_number');

            // ── BANK DETAILS (for commission payments) ────────────────────
            $table->string('bank_name', 255)->nullable()->after('voter_id');
            $table->string('bank_account_number', 255)->nullable()->after('bank_name'); // encrypted
            $table->string('bank_ifsc', 11)->nullable()->after('bank_account_number');
            $table->string('bank_branch', 255)->nullable()->after('bank_ifsc');
            $table->string('upi_id', 100)->nullable()->after('bank_branch');

            // ── EMPLOYMENT/BUSINESS DETAILS ───────────────────────────────
            $table->string('qualification', 255)->nullable()->after('occupation');
            $table->tinyInteger('experience_years')->unsigned()->nullable()->after('qualification');
            $table->json('languages_known')->nullable()->after('experience_years');
            $table->string('reference_name', 255)->nullable()->after('languages_known');
            $table->string('reference_mobile', 10)->nullable()->after('reference_name');
            $table->text('territory')->nullable()->after('reference_mobile');
            $table->smallInteger('target_monthly')->unsigned()->nullable()->after('territory');

            // ── JOINING LETTER SPECIFIC ───────────────────────────────────
            $table->timestamp('approved_at')->nullable()->after('status');
            $table->foreignId('approved_by')->nullable()->constrained('users')->after('approved_at');
            $table->string('letter_number', 50)->unique()->nullable()->after('approved_by');
            $table->date('joining_date')->nullable()->after('letter_number');

            // ── SIGNATURE UPLOAD ──
            $table->string('signature_image', 500)->nullable()->after('profile_photo');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'religion', 'gender', 'marital_status',
                'permanent_address', 'current_address', 'pincode', 'landmark',
                'pan_number', 'voter_id',
                'bank_name', 'bank_account_number', 'bank_ifsc', 'bank_branch', 'upi_id',
                'qualification', 'experience_years', 'languages_known',
                'reference_name', 'reference_mobile', 'territory', 'target_monthly',
                'approved_at', 'approved_by', 'letter_number', 'joining_date',
                'signature_image'
            ]);
        });
    }
};
