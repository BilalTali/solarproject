<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$keys = ['footer_link_about', 'footer_link_scheme', 'footer_link_contact', 'footer_link_faq', 'footer_link_privacy', 'footer_link_terms', 'footer_link_refund'];
$userSettings = \App\Models\Setting::whereIn('key', $keys)->whereNotNull('user_id')->latest()->get()->groupBy('key');

foreach($keys as $key) {
    if (!\App\Models\Setting::where('key', $key)->whereNull('user_id')->exists()) {
        if ($userSettings->has($key)) {
            $oldVal = $userSettings[$key]->first()->value;
            \App\Models\Setting::create([
                'key' => $key,
                'value' => $oldVal,
                'user_id' => null,
                'group' => 'branding'
            ]);
            echo "Migrated {$key} => {$oldVal}\n";
        }
    }
}
echo "Done.\n";
