<?php
use App\Models\Document;
use Illuminate\Support\Facades\DB;

try {
    require __DIR__.'/vendor/autoload.php';
    $app = require_once __DIR__.'/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    $docs = Document::all();
    echo "Total Documents: " . $docs->count() . "\n";
    foreach ($docs as $doc) {
        echo "ID: {$doc->id} | Title: {$doc->title} | Published: " . ($doc->is_published ? 'Yes' : 'No') . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
