<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$defaults = [
    // Navigation Links
    'nav_home' => '/',
    'nav_rewards' => '/#benefits',
    'nav_calculator' => '/solar-subsidy-calculator',
    'nav_track_status' => '/track-status',
    'nav_portal_login' => 'Portal Login',
    'nav_cta_electricity' => 'Apply for Subsidy',
    
    // Footer Section Labels
    'footer_section_quick_links' => 'Quick Links',
    'footer_section_legal' => 'Legal',
    
    // Footer Links
    'footer_link_about' => '/#about',
    'footer_link_scheme' => 'https://pmsuryaghar.gov.in/',
    'footer_link_contact' => '/#contact',
    'footer_link_faq' => '/#faq',
    'footer_link_privacy' => '/privacy-policy',
    'footer_link_terms' => '/terms-conditions',
    'footer_link_refund' => '/refund-policy',
];

$count = 0;
foreach($defaults as $key => $val) {
    if (!\App\Models\Setting::where('key', $key)->whereNull('user_id')->exists()) {
        \App\Models\Setting::create([
            'key' => $key,
            'value' => $val,
            'user_id' => null,
            'group' => str_starts_with($key, 'nav_') ? 'portal' : 'branding'
        ]);
        echo "Inserted default for {$key}\n";
        $count++;
    } else {
        // If it exists but is empty/null, update it
        $s = \App\Models\Setting::where('key', $key)->whereNull('user_id')->first();
        if (empty($s->value)) {
            $s->update(['value' => $val]);
            echo "Updated empty setting for {$key}\n";
            $count++;
        }
    }
}
echo "Seeded {$count} navigation/footer settings.\n";
