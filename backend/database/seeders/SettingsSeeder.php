<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Commission
            ['key' => 'commission_per_lead', 'value' => '1000', 'group' => 'commission'],
            ['key' => 'commission_per_installation', 'value' => '2000', 'group' => 'commission'],
            ['key' => 'super_agent_override_percentage', 'value' => '10', 'group' => 'commission'],
            ['key' => 'commission_per_super_agent_installation', 'value' => '500', 'group' => 'commission'],

            // ── Company / Letterhead ──────────────────────────────────────────
            ['key' => 'company_name',             'value' => 'SuryaMitra Solar Network',       'group' => 'company'],
            ['key' => 'company_tagline',          'value' => 'Empowering India with Free Solar Energy', 'group' => 'company'],
            ['key' => 'company_registration_no',  'value' => 'REG/SMS/2026/0892', 'group' => 'company'],
            ['key' => 'company_affiliated_with',  'value' => 'Government of India / MNRE', 'group' => 'company'],
            ['key' => 'company_address',          'value' => 'Basement Suite 4, Residency Road, Near Regal Chowk, Srinagar, J&K - 190001', 'group' => 'company'],
            ['key' => 'company_website',          'value' => 'https://suryamitra.in', 'group' => 'company'],
            ['key' => 'company_email',            'value' => 'info@suryamitra.in',      'group' => 'company'],
            ['key' => 'company_phone',            'value' => '+91-99067 66655',         'group' => 'company'],

            // ── Authorized Signatory ──────────────────────────────────────────
            ['key' => 'signatory_name',           'value' => 'Er. Sameer Ahmad', 'group' => 'signatory'],
            ['key' => 'signatory_title',          'value' => 'Chief Operations Officer', 'group' => 'signatory'],
            ['key' => 'signatory_mobile',         'value' => '+91-90000 00000', 'group' => 'signatory'],
            ['key' => 'company_signature',        'value' => 'branding/signature.png', 'group' => 'branding'],
            ['key' => 'company_logo',             'value' => 'branding/logo.png', 'group' => 'branding'],
            ['key' => 'company_favicon',          'value' => 'branding/favicon.ico', 'group' => 'branding'],
            ['key' => 'company_seal',             'value' => 'branding/seal.png', 'group' => 'branding'],

            // ── ICard Specific ────────────────────────────────────────────────
            ['key' => 'authorized_signatory',     'value' => 'Er. Sameer Ahmad', 'group' => 'icard'],
            ['key' => 'authorized_signatory_title', 'value' => 'Chief Operations Officer', 'group' => 'icard'],
            ['key' => 'icard_clearance',          'value' => 'Level-V (Elite)', 'group' => 'icard'],
            ['key' => 'company_emergency',        'value' => '9906766655', 'group' => 'icard'],
            ['key' => 'icard_verified_by',        'value' => 'CHIEF OPERATIONS OFFICER', 'group' => 'icard'],
            ['key' => 'icard_warning_text',       'value' => "THIS IDENTITY INSTRUMENT IS ISSUED BY SURYAMITRA SOLAR NETWORK. \nISSUED FOR SECURE ACCESS ONLY. \nIF FOUND, PLEASE RETURN TO A REGIONAL FACILITY OR THE RESIDENCY ROAD HQ.", 'group' => 'icard'],

            // ── Letter Content ────────────────────────────────────────────────
            ['key' => 'letter_agent_body',        'value' => "With reference to your application and subsequent interaction with our team, we are pleased to appoint you as a <strong>Business Development Executive (BDE)</strong> for <strong>SuryaMitra Solar Network</strong> under the PM Surya Ghar Muft Bijli Yojana facilitation programme, with effect from {joining_date}.\n\nAs a Business Development Executive bearing code <strong>{agent_id}</strong>, you will be responsible for identifying, registering, and facilitating potential beneficiaries in <strong>{territory}</strong> for rooftop solar installation under the aforementioned government scheme.\n\nYou will work under the guidance of your assigned Business Development Manager and report to the administrative team. Your commissions and incentives shall be as per the prevailing schedule notified by the company from time to time.", 'group' => 'letter'],
            ['key' => 'letter_super_agent_body',  'value' => "With reference to your application and the evaluation conducted by our senior team, we are pleased to appoint you as a <strong>Business Development Manager (BDM)</strong> for <strong>SuryaMitra Solar Network</strong> under the PM Surya Ghar Muft Bijli Yojana facilitation programme, with effect from {joining_date}.\n\nAs a Business Development Manager bearing code <strong>{super_agent_code}</strong>, you will be responsible for building, managing, and mentoring a team of Business Development Executives across <strong>{territory}</strong>. You will oversee lead verification, agent performance, and coordinated facilitation of solar installations in your assigned region.\n\nYour override commissions, team incentives, and other benefits shall be as per the prevailing schedule communicated by the administration.", 'group' => 'letter'],
            ['key' => 'letter_terms',             'value' => "This appointment is for facilitation under PM Surya Ghar Muft Bijli Yojana only and does not constitute a formal employment contract.\nThis letter is non-transferable and valid only for the individual named herein.\nThe appointee must not collect any money, fees, or charges from beneficiaries under any circumstances.\nThe appointee agrees to maintain confidentiality of all beneficiary data and company information.\nThis appointment may be revoked by the company at any time without prior notice in case of misconduct.\nThe appointee must comply with all applicable laws, government guidelines, and company policies.\nCommission rates are subject to change and will be communicated separately.\nAny disputes are subject to the exclusive jurisdiction of the courts in Srinagar, J&K.", 'group' => 'letter'],
            ['key' => 'letter_footer_note',       'value' => 'This is a computer-generated document and carries a digital signature for authenticity.', 'group' => 'letter'],

            // Homepage Dynamic Cards
            ['key' => 'hero_headline', 'value' => 'Get 300 Units FREE Electricity Every Month', 'group' => 'homepage'],
            ['key' => 'hero_subheadline', 'value' => 'Government of India scheme — up to ₹78,000 subsidy for rooftop solar installation. We guide you end-to-end, completely free.', 'group' => 'homepage'],
            ['key' => 'hero_stats_json', 'value' => json_encode([
                ['icon' => 'IndianRupee', 'value' => '₹78,000', 'label' => 'Max Subsidy'],
                ['icon' => 'Zap', 'value' => '300 Units', 'label' => 'Free / Month'],
                ['icon' => 'Home', 'value' => '1 Crore+', 'label' => 'Target Homes'],
                ['icon' => 'BarChart3', 'value' => '25 Years', 'label' => 'Panel Life'],
            ]), 'group' => 'homepage'],
            ['key' => 'how_it_works_json', 'value' => json_encode([
                ['icon' => 'ClipboardList', 'step' => '1', 'title' => 'Submit Query', 'desc' => 'Fill our simple form with your details'],
                ['icon' => 'PhoneCall', 'step' => '2', 'title' => 'We Call You', 'desc' => 'Our team calls within 24 hours'],
                ['icon' => 'Building2', 'step' => '3', 'title' => 'Govt Registration', 'desc' => 'We register you on pmsuryaghar.gov.in'],
                ['icon' => 'Sun', 'step' => '4', 'title' => 'Installation', 'desc' => 'Solar panels installed at your home'],
                ['icon' => 'Zap', 'step' => '5', 'title' => 'Free Electricity', 'desc' => 'Enjoy up to 300 units free every month!'],
            ]), 'group' => 'homepage'],
            ['key' => 'why_choose_us_json', 'value' => json_encode([
                ['icon' => 'Sparkles', 'title' => 'Free Guidance', 'desc' => 'No charges from beneficiaries. Our service is completely free for homeowners.'],
                ['icon' => 'Zap', 'title' => 'Faster Registration', 'desc' => 'We handle all paperwork and portal registration so you don\'t have to.'],
                ['icon' => 'PhoneCall', 'title' => 'End-to-End Support', 'desc' => 'From application to installation, we stay with you throughout the entire process.'],
            ]), 'group' => 'homepage'],

            // Eligibility Checker Settings
            ['key' => 'eligibility_headline', 'value' => 'Check Your Eligibility', 'group' => 'homepage'],
            ['key' => 'eligibility_subheadline', 'value' => 'Answer 4 quick questions to find out if you qualify', 'group' => 'homepage'],
            ['key' => 'eligibility_questions_json', 'value' => json_encode([
                ['id' => 'q1', 'text' => 'Do you own the house where solar panels will be installed?', 'expected' => 'yes'],
                ['id' => 'q2', 'text' => 'Do you have an active electricity connection in your name?', 'expected' => 'yes'],
                ['id' => 'q3', 'text' => 'Do you have a valid Aadhaar-linked bank account?', 'expected' => 'yes'],
                ['id' => 'q4', 'text' => 'Have you NOT availed any solar subsidy before?', 'expected' => 'yes'],
            ]), 'group' => 'homepage'],
            ['key' => 'eligibility_success_title', 'value' => '✅ You are Eligible for PM Surya Ghar!', 'group' => 'homepage'],
            ['key' => 'eligibility_success_desc', 'value' => 'Great news! You meet the basic criteria. Please fill the Lead Form below and our team will guide you through the entire free installation process.', 'group' => 'homepage'],
            ['key' => 'eligibility_error_title', 'value' => '⚠️ You might not be eligible', 'group' => 'homepage'],
            ['key' => 'eligibility_error_desc', 'value' => 'Based on your answers, you may not qualify for the government subsidy at this moment. However, please contact our support team as there may still be other options available for you.', 'group' => 'homepage'],

            // Footer
            ['key' => 'footer_about_text', 'value' => 'Facilitating solar rooftop installations under the PM Surya Ghar Muft Bijli Yojana across India.', 'group' => 'company'],
            ['key' => 'footer_copyright', 'value' => '© 2026 SuryaMitra. All rights reserved.', 'group' => 'company'],
            ['key' => 'footer_disclaimer', 'value' => '⚠️ SuryaMitra is an independent facilitation agency. Not a government body. PM Surya Ghar Muft Bijli Yojana is a Government of India scheme.', 'group' => 'company'],
        ];

        foreach ($settings as $setting) {
            // Protect existing user-uploaded branding to avoid overwriting with placeholders
            if (in_array($setting['key'], ['company_logo', 'company_favicon', 'company_signature', 'company_seal', 'company_logo_2'])) {
                $existing = \App\Models\Setting::where('key', $setting['key'])->first();
                if ($existing && !empty($existing->value) && str_contains($existing->value, 'branding/')) {
                    // Only update if current value is the placeholder we are trying to set (e.g. logo.png)
                    if ($existing->value !== $setting['value']) {
                        continue;
                    }
                }
            }

            \App\Models\Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
