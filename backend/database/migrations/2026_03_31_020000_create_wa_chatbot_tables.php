<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table 1: WhatsApp chatbot categories
        // name MUST match the faqs.category string values (e.g. "Subsidy")
        Schema::create('wa_chatbot_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');               // Links to faqs.category — no FK, soft link by string
            $table->string('description')->nullable();  // Shown as subtitle in WA interactive list
            $table->string('icon_emoji', 10)->nullable(); // e.g. 💰
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Table 2: Per-user conversation state tracking
        Schema::create('wa_chatbot_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('wa_phone')->index();   // E.164 format e.g. 919876543210
            $table->string('state')->default('menu'); // menu|category|register|done
            $table->json('context')->nullable();
            // context shape:
            // {
            //   "selected_category": "Subsidy",
            //   "reg_step": 3,
            //   "reg_data": {
            //     "name": "Ramesh Kumar",
            //     "mobile": "9876543210",
            //     "state": "Uttar Pradesh",
            //     "district": "Lucknow",
            //     "area": "Aliganj",
            //     "elec_bill_media_id": "1234567890",
            //     "aadhaar_media_id": "0987654321",
            //     "referral_code": "SM-2026-1042"
            //   }
            // }
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_chatbot_sessions');
        Schema::dropIfExists('wa_chatbot_categories');
    }
};
