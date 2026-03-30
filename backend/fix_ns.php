<?php
$base = __DIR__ . '/app/Http/Controllers';
$dir = new RecursiveDirectoryIterator($base);
$ite = new RecursiveIteratorIterator($dir);

foreach ($ite as $file) {
    if ($file->getExtension() === 'php') {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        
        // Calculate correct namespace based on folder structure
        // /Users/.../backend/app/Http/Controllers/Solar -> App\Http\Controllers\Solar
        $relPath = str_replace(__DIR__ . '/app/', '', dirname($path));
        $correctNS = 'App\\' . str_replace('/', '\\', $relPath);
        
        // Standardize namespace
        $content = preg_replace('/namespace\s+[a-zA-Z0-9_\\\\]+;/', "namespace $correctNS;", $content);
        file_put_contents($path, $content);
        echo "Fixed namespace for {$file->getBasename()} to $correctNS\n";
    }
}
