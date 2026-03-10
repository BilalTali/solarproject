<?php
$file = '/Users/computergallery/Documents/pmsuryaghar/backend/resources/views/icard/index.blade.php';
$content = file_get_contents($file);

// Replace asset($logoPath) -> public_path($logoPath)
$content = str_replace('asset($logoPath)', 'public_path($logoPath)', $content);

// Replace asset('storage/' . $user->profile_photo) -> storage_path('app/public/' . $user->profile_photo)
$content = str_replace("asset('storage/' . \$user->profile_photo)", "storage_path('app/public/' . \$user->profile_photo)", $content);

// Find barcode image and update it. It currently might be asset('temp/' . basename($barcodePath))
// Let's replace it with $barcodePath directly since it's already an absolute storage_path.
$content = preg_replace('/\{\{\s*asset\([^\}]*\$barcodePath[^\}]*\)\s*\}\}/', '{{ $barcodePath }}', $content);

file_put_contents($file, $content);
echo "Image paths replaced to absolute file paths.\n";
