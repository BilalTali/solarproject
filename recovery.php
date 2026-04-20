<?php
header('Content-Type: text/plain');
echo "Starting recovery...\n";

// 1. Rename Assets
if (is_dir('assets')) {
    if (!is_dir('assets_v1')) {
        if (rename('assets', 'assets_v1')) {
            echo "SUCCESS: Renamed assets to assets_v1\n";
        } else {
            echo "ERROR: Failed to rename assets\n";
        }
    } else {
        echo "INFO: assets_v1 already exists, merge/overwrite needed?\n";
    }
} else {
    echo "INFO: 'assets' directory not found.\n";
}

// 2. Patch index.html
if (file_exists('index.html')) {
    $html = file_get_contents('index.html');
    if (strpos($html, '/assets/') !== false) {
        $newHtml = str_replace('/assets/', '/assets_v1/', $html);
        file_put_contents('index.html', $newHtml);
        echo "SUCCESS: Patched index.html\n";
    } else {
        echo "INFO: index.html already patched or /assets/ not found.\n";
    }
}

// 3. Patch .htaccess for MIME and Cache
if (file_exists('.htaccess')) {
    $htaccess = file_get_contents('.htaccess');
    if (strpos($htaccess, 'AddType application/javascript') === false) {
        $mime = "# Force MIME Types\nAddType application/javascript .js\nAddType text/css .css\n\n# Cache Busting\n<IfModule mod_headers.c>\n    <FilesMatch \"\\.(js|css)$\">\n        Header set Cache-Control \"no-cache, no-store, must-revalidate\"\n    </FilesMatch>\n</IfModule>\n\n";
        file_put_contents('.htaccess', $mime . $htaccess);
        echo "SUCCESS: Patched .htaccess\n";
    }
}

echo "Recovery check complete.\n";
?>
