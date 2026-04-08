<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$defaults = [
    // Presence
    'hero_headline' => 'Sustainable Energy for Your Home',
    'hero_subheadline' => 'Join the solar revolution with PM Surya Ghar Muft Bijli Yojana. Clean energy, zero bills.',
    
    // Content
    'hero_stats_json' => '[{"icon":"IndianRupee","value":"₹94,800","label":"Max Subsidy"},{"icon":"Zap","value":"300 Units","label":"Free / Month"},{"icon":"Home","value":"1 Crore+","label":"Target Homes"},{"icon":"BarChart3","value":"25 Years","label":"Panel Life"}]',
    'how_it_works_json' => '[{"icon":"ClipboardList","title":"Submit Query","desc":"Fill our simple form with your details"},{"icon":"PhoneCall","title":"We Call You","desc":"Our team calls within 24 hours"},{"icon":"Building2","title":"Govt Registration","desc":"We register you on pmsuryaghar.gov.in"},{"icon":"Sun","title":"Installation","desc":"Solar panels installed at your home"},{"icon":"Zap","title":"Free Electricity","desc":"Enjoy up to 300 units free every month!"}]',
    'why_choose_us_json' => '[{"icon":"Sparkles","title":"Free Guidance","desc":"No charges from beneficiaries. Our service is completely free for homeowners."},{"icon":"Zap","title":"Faster Registration","desc":"We handle all paperwork and portal registration so you don\'t have to."},{"icon":"PhoneCall","title":"End-to-End Support","desc":"From application to installation, we stay with you throughout the entire process."}]',
    'eligibility_headline' => 'Check Your Eligibility',
    'eligibility_subheadline' => 'Answer 4 quick questions to find out if you qualify',
    'eligibility_questions_json' => '[{"id":"q1","text":"Do you own the house?","expected":"yes"},{"id":"q2","text":"Active electricity connection?","expected":"yes"},{"id":"q3","text":"Aadhaar linked bank?","expected":"yes"},{"id":"q4","text":"No previous subsidy?","expected":"yes"}]',
    'calculator_headline' => 'Subsidy Calculator',
    'calculator_subheadline' => 'Estimate your solar savings',
    'calculator_values_json' => '[{"id":"3kw","label":"3kW System","central":94800,"state":0,"savings":1800,"payback":48}]',
    
    // Nav
    'nav_home' => '/',
    'nav_rewards' => '/#benefits',
    'nav_calculator' => '/solar-subsidy-calculator',
    'nav_track_status' => '/track-status',
    'nav_portal_login' => 'Portal Login',
    'nav_cta_electricity' => 'Apply Now',
    
    // Footer
    'footer_about_text' => 'Facilitating solar rooftop installations under PM Surya Ghar Muft Bijli Yojana.',
    'footer_copyright' => 'All rights reserved.',
    'footer_disclaimer' => 'Not an official government website.',
    'footer_section_quick_links' => 'Quick Links',
    'footer_section_legal' => 'Legal',
    'footer_link_about' => '/#about',
    'footer_link_scheme' => 'https://pmsuryaghar.gov.in/',
    'footer_link_contact' => '/#contact',
    'footer_link_faq' => '/#faq',
    'footer_link_privacy' => '/privacy-policy',
    'footer_link_terms' => '/terms-conditions',
    'footer_link_refund' => '/refund-policy',
    
    // Branding
    'company_name' => 'PM Surya Ghar',
    'company_registration_no' => 'REG123456',
    'company_slogan' => 'Powering Homes, Empowering Lives',
    
    // Contact
    'company_email' => 'info@andleebsurya.in',
    'company_phone' => '+91-99067 66655',
    'company_mobile' => '+91-9419012345',
    'company_whatsapp' => '+91-99067 66655',
    'company_website' => 'https://andleebsurya.in',
    'company_address' => 'Basement Suite 4, Residency Road, Srinagar, J&K',
];

$count = 0;
foreach($defaults as $key => $val) {
    if (!\App\Models\Setting::where('key', $key)->whereNull('user_id')->exists()) {
        $group = 'general';
        if (str_starts_with($key, 'hero_') || str_starts_with($key, 'how_') || str_starts_with($key, 'why_') || str_starts_with($key, 'eligibility_') || str_starts_with($key, 'calculator_')) {
            $group = 'homepage';
        } elseif (str_starts_with($key, 'nav_')) {
            $group = 'portal';
        } elseif (str_starts_with($key, 'footer_') || str_starts_with($key, 'company_')) {
            $group = 'branding';
        }
        
        \App\Models\Setting::create([
            'key' => $key,
            'value' => $val,
            'user_id' => null,
            'group' => $group
        ]);
        echo "Created Master Authority entry for {$key}\n";
        $count++;
    } else {
        // Ensure even existing ones have a group assigned for the sidebar UI
        $s = \App\Models\Setting::where('key', $key)->whereNull('user_id')->first();
        if (empty($s->group)) {
             $group = 'general';
             if (str_starts_with($key, 'hero_') || str_starts_with($key, 'how_') || str_starts_with($key, 'why_') || str_starts_with($key, 'eligibility_') || str_starts_with($key, 'calculator_')) $group = 'homepage';
             elseif (str_starts_with($key, 'nav_')) $group = 'portal';
             else $group = 'branding';
             $s->update(['group' => $group]);
             echo "Updated existing group for {$key}\n";
        }
    }
}
echo "Authority Hub Master Seed Complete. {$count} entries added/updated.\n";
