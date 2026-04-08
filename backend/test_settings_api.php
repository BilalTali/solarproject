<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('role', 'super_admin')->first();
\Illuminate\Support\Facades\Auth::login($user);

$controller = app(\App\Http\Controllers\Admin\SettingController::class);
$response = $controller->index();
echo $response->getContent();
