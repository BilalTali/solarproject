<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Cache;

echo "Purging all API caches...\n";
$suffixes = ['_guest', '_1', '_2', '_3', '_4', '_5'];
$hosts = ['andleebsurya.in', 'www.andleebsurya.in', 'localhost'];
$paths = ['/api/v1/public/settings', '/api/v1/super-admin/dashboard/stats'];

foreach ($paths as $path) {
    foreach ($hosts as $host) {
        foreach (['http://', 'https://'] as $proto) {
            foreach ($suffixes as $suffix) {
                $url = $proto . $host . $path;
                $key = 'api_cache_' . md5($url . $suffix);
                if (Cache::has($key)) {
                    Cache::forget($key);
                    echo "Cleared: {$url}{$suffix}\n";
                }
            }
        }
    }
}

echo "Purging dashboard specific keys...\n";
Cache::forget('super_admin_dashboard_stats');

echo "Clearing optimization cache...\n";
\Illuminate\Support\Facades\Artisan::call('optimize:clear');

echo "Cache purge complete!\n";
