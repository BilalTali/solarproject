<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$keys = ['footer_link_about', 'footer_link_scheme', 'footer_link_contact', 'footer_link_faq', 'footer_link_privacy', 'footer_link_terms', 'footer_link_refund'];
$count = 0;
foreach(\App\Models\Setting::whereIn('key', $keys)->get() as $s) {
    echo $s->key . ' | user: ' . ($s->user_id ?: 'NULL') . ' | val: ' . $s->value . "\n";
    $count++;
}
if ($count === 0) {
    echo "No settings found in database matching those keys.\n";
}
