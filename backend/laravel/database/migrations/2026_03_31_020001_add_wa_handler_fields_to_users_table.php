<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Marks which admins are designated WhatsApp Lead Handlers by the Super Admin.
            // Only these admins will see whatsapp_chatbot source leads in their queue.
            $table->boolean('is_wa_lead_handler')
                ->default(false)
                ->after('is_public_contact');

            // Incremented each time this admin is assigned a WA chatbot lead (round-robin).
            // Admin with the lowest counter gets the next lead.
            // Super Admin can reset all counters to rebalance.
            $table->unsignedInteger('wa_lead_round_robin_counter')
                ->default(0)
                ->after('is_wa_lead_handler');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_wa_lead_handler', 'wa_lead_round_robin_counter']);
        });
    }
};
