<?php
header('Content-Type: text/plain');
echo "Starting Nuclear Cache-Buster...\n";

// 1. Rename assets folder
if (is_dir('assets')) {
    if (is_dir('assets_v1')) {
        echo "INFO: assets_v1 already exists. Deleting it to refresh.\n";
        system('rm -rf assets_v1');
    }
    if (rename('assets', 'assets_v1')) {
        echo "SUCCESS: Renamed assets to assets_v1\n";
    } else {
        echo "ERROR: Failed to rename assets\n";
    }
} else {
    echo "INFO: 'assets' folder not found. Checking if assets_v1 exists...\n";
    if (is_dir('assets_v1')) {
        echo "INFO: assets_v1 already exists. No rename needed.\n";
    } else {
        echo "ERROR: Neither 'assets' nor 'assets_v1' found!\n";
    }
}

// 2. Update index.html
if (file_exists('index.html')) {
    $html = file_get_contents('index.html');
    if (strpos($html, '/assets/') !== false) {
        $newHtml = str_replace('/assets/', '/assets_v1/', $html);
        file_put_contents('index.html', $newHtml);
        echo "SUCCESS: Updated index.html paths\n";
    } else {
        echo "INFO: index.html already points to /assets_v1/ or has no asset references.\n";
    }
}

// 3. Fix MIME in .htaccess
if (file_exists('.htaccess')) {
    $htaccess = file_get_contents('.htaccess');
    if (strpos($htaccess, 'AddType application/javascript') === false) {
        $mimePatch = "# Force MIME types\nAddType application/javascript .js\nAddType text/css .css\n\n";
        file_put_contents('.htaccess', $mimePatch . $htaccess);
        echo "SUCCESS: Patched .htaccess with MIME types\n";
    }
}

// 4. Permissions check
system('chmod -R 755 assets_v1');
echo "SUCCESS: Set permissions on assets_v1\n";

echo "Recovery Complete!\n";
?>
