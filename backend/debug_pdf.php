<?php

use App\Models\User;
use App\Services\JoiningLetterService;
use Illuminate\Support\Facades\Storage;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = User::where('name', 'like', '%TASLEEMAH%')->first();
if (!$user) {
    die("User not found\n");
}

$service = app(JoiningLetterService::class);
$pdfContent = $service->generatePdf($user, false); // generate output

file_put_contents('debug_letter.pdf', $pdfContent);

echo "PDF generated to debug_letter.pdf\n";
$header = substr($pdfContent, 0, 10);
echo "Header: " . bin2hex($header) . " (" . $header . ")\n";
