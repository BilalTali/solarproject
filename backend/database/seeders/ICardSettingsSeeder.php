<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class ICardSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Company Info
            ['key' => 'company_name',     'value' => 'SURYAMITRA SOLAR NETWORK', 'group' => 'company'],
            ['key' => 'company_address',  'value' => 'Residency Road, Srinagar, Jammu & Kashmir', 'group' => 'company'],
            ['key' => 'company_email',    'value' => 'info@suryamitra.in', 'group' => 'company'],
            ['key' => 'company_mobile',   'value' => '+91 XXXXX XXXXX', 'group' => 'company'],
            ['key' => 'company_whatsapp', 'value' => '+91 XXXXX XXXXX', 'group' => 'company'],
            ['key' => 'company_slogan',   'value' => 'Powering the Future with Solar', 'group' => 'branding'],
            
            // Branding
            ['key' => 'company_logo',      'value' => null, 'group' => 'branding'],
            ['key' => 'company_signature', 'value' => null, 'group' => 'branding'],
            ['key' => 'company_seal',      'value' => null, 'group' => 'branding'],

            // iCard
            ['key' => 'authorized_signatory', 'value' => 'General Secretary', 'group' => 'icard'],
            
            // Homepage / CMS
            ['key' => 'hero_headline',    'value' => 'Switch to Solar, Save for Life', 'group' => 'homepage'],
            ['key' => 'hero_subheadline', 'value' => 'The PM Surya Ghar: Muft Bijli Yojana is a government-backed subsidy program to provide free solar energy to households.', 'group' => 'homepage'],
            ['key' => 'welcome_message',  'value' => 'Welcome to SuryaMitra Portal', 'group' => 'portal'],
            ['key' => 'terms_conditions', 'value' => 'Terms and conditions apply for solar installations.', 'group' => 'portal'],

            // Footer
            ['key' => 'footer_about_text', 'value' => 'Providing clean solar energy solutions across J&K and beyond.', 'group' => 'company'],
            ['key' => 'footer_copyright',  'value' => '© 2024 SuryaMitra Solar Network. All rights reserved.', 'group' => 'company'],
            ['key' => 'footer_disclaimer', 'value' => 'Facilitating official SuryaMitra Field Support only.', 'group' => 'company'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'group' => $setting['group']]
            );
        }
    }
}
