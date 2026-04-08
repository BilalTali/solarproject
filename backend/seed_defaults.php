<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$defaults = [
    'calculator_values_json' => '[{"id":"3kw","label":"3KW System","central":94800,"state":0,"savings":1800,"payback":48},{"id":"above_3kw","label":"Above 3kW System","central":94800,"state":0,"savings":2400,"payback":42}]',
    'eligibility_questions_json' => '[{"id":"q1","text":"Do you own the house where solar panels will be installed?","expected":"yes"},{"id":"q2","text":"Do you have an active electricity connection in your name?","expected":"yes"},{"id":"q3","text":"Do you have a valid Aadhaar-linked bank account?","expected":"yes"},{"id":"q4","text":"Have you NOT availed any solar subsidy before?","expected":"yes"}]',
    'calculator_headline' => 'Subsidy Calculator',
    'calculator_subheadline' => 'See how much you can save based on your system size',
    'eligibility_headline' => 'Check Your Eligibility',
    'eligibility_subheadline' => 'Answer 4 quick questions to find out if you qualify',
    'hero_stats_json' => '[{"icon":"IndianRupee","value":"₹94,800","label":"Max Subsidy"},{"icon":"Zap","value":"300 Units","label":"Free / Month"},{"icon":"Home","value":"1 Crore+","label":"Target Homes"},{"icon":"BarChart3","value":"25 Years","label":"Panel Life"}]',
    'how_it_works_json' => '[{"icon":"ClipboardList","title":"Submit Query","desc":"Fill our simple form with your details"},{"icon":"PhoneCall","title":"We Call You","desc":"Our team calls within 24 hours"},{"icon":"Building2","title":"Govt Registration","desc":"We register you on pmsuryaghar.gov.in"},{"icon":"Sun","title":"Installation","desc":"Solar panels installed at your home"},{"icon":"Zap","title":"Free Electricity","desc":"Enjoy up to 300 units free every month!"}]',
    'why_choose_us_json' => '[{"icon":"Sparkles","title":"Free Guidance","desc":"No charges from beneficiaries. Our service is completely free for homeowners."},{"icon":"Zap","title":"Faster Registration","desc":"We handle all paperwork and portal registration so you don\'t have to."},{"icon":"PhoneCall","title":"End-to-End Support","desc":"From application to installation, we stay with you throughout the entire process."}]'
];

foreach($defaults as $key => $val) {
    if (!\App\Models\Setting::where('key', $key)->whereNull('user_id')->exists()) {
        \App\Models\Setting::create([
            'key' => $key,
            'value' => $val,
            'user_id' => null,
            'group' => 'homepage'
        ]);
        echo "Inserted default for {$key}\n";
    }
}
echo "Done seeding defaults.\n";
