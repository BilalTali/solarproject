<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach(\App\Models\Setting::where('key', 'like', 'calculator%')->get() as $s) {
    echo $s->key . ' (' . ($s->user_id ?: 'null') . "): " . substr($s->value, 0, 50) . "\n";
}
echo "Done calculators.\n";
