<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Services\ICardService;
use App\Services\JoiningLetterService;
use Illuminate\Support\Facades\Log;

try {
    $user = User::where('role', 'agent')->first();
    if (!$user) {
        die("No agent found to test.");
    }

    echo "Testing ICard generation for: {$user->name} (Role: {$user->role})\n";
    $iCardService = app(ICardService::class);
    $icardPdf = $iCardService->generateAndDownload($user);
    $icardContent = $icardPdf->getContent();
    echo "ICard PDF generated. Size: " . strlen($icardContent) . " bytes\n";
    file_put_contents('test_icard.pdf', $icardContent);
    echo "Saved to test_icard.pdf\n";

    echo "\nTesting Joining Letter generation...\n";
    $joiningLetterService = app(JoiningLetterService::class);
    $letterPdfContent = $joiningLetterService->generatePdf($user, false);
    echo "Joining Letter PDF generated. Size: " . strlen($letterPdfContent) . " bytes\n";
    file_put_contents('test_joining.pdf', $letterPdfContent);
    echo "Saved to test_joining.pdf\n";

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
