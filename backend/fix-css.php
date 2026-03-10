<?php
$file = '/Users/computergallery/Documents/pmsuryaghar/backend/resources/views/icard/index.blade.php';
$content = file_get_contents($file);

// Replace flex in body
$content = preg_replace('/display:\s*flex;\s*align-items:\s*center;\s*justify-content:\s*center;\s*padding:\s*60px\s*30px;\s*gap:\s*52px;\s*flex-wrap:\s*wrap;/sm', 'position: relative; padding: 20px;', $content);

// Update .cards-wrapper
$content = preg_replace('/\.cards-wrapper\s*\{.*?\}/s', ".cards-wrapper { position: relative; width: 100%; height: 550px; }", $content);

// Update .card-container
if (!preg_match('/\.card-container/', $content)) {
    // Add it after .card
    $content = preg_replace('/\.card\s*\{/', ".card-container { position: absolute; top: 20px; }\n        .front-card { left: 10px; }\n        .back-card { left: 360px; }\n\n        .card {", $content);
}

// Add classes to HTML
$content = preg_replace('/<div class="card-container">/is', '<div class="card-container front-card">', $content, 1);
$content = preg_replace('/<div class="card-container">/is', '<div class="card-container back-card">', $content, 1);

// Remove flex from header
$content = preg_replace('/display:\s*flex;\s*flex-direction:\s*column;\s*align-items:\s*center;/s', 'text-align: center;', $content);

// Remove flex from logo-ring
$content = preg_replace('/display:\s*flex;\s*align-items:\s*center;\s*justify-content:\s*center;/s', 'display: inline-block; vertical-align: middle; text-align: center;', $content);

// Org badge
$content = preg_replace('/display:\s*inline-flex;\s*align-items:\s*center;/s', 'display: inline-block;', $content);

// initials-avatar
$content = preg_replace('/\.initials-avatar\s*\{.*?\}/s', ".initials-avatar {\n            width: 100%; height: 100%;\n            background: linear-gradient(145deg, #1548A0 0%, #072564 100%);\n            text-align: center; line-height: 94px;\n            font-family: 'Cinzel', serif;\n            font-size: 30px; font-weight: 700;\n            color: #FFD070; letter-spacing: 2px;\n        }", $content);

// Front body
$content = str_replace(
    "display: flex; flex-direction: column;\n            align-items: center;", 
    "text-align: center;", 
    $content
);

// Body icons
$content = str_replace(
    "display: flex; flex-direction: column;\n            align-items: center; justify-content: space-around;",
    "text-align: center;",
    $content
);

// Designation wrap
$content = preg_replace('/\.designation-wrap\s*\{.*?\}/s', ".designation-wrap {\n            text-align: center;\n            margin-bottom: 5px; position: relative; z-index: 1;\n        }", $content);
$content = preg_replace('/\.desig-line\s*\{.*?\}/s', ".desig-line { display: inline-block; vertical-align: middle; width: 20px; height: 1px; background: #FFB020; margin: 0 4px; }", $content);
$content = preg_replace('/\.designation\s*\{.*?\}/s', ".designation {\n            display: inline-block; vertical-align: middle;\n            font-size: 7.5px; font-weight: 700; color: #B86A00;\n            letter-spacing: 3.5px; text-transform: uppercase;\n        }", $content);

// info-row
$content = preg_replace('/\.info-row\s*\{.*?\}/s', ".info-row {\n            display: table; width: 100%; table-layout: fixed;\n            padding: 4.5px 10px;\n            border-bottom: 1px solid rgba(10,36,90,0.05);\n        }", $content);

// info parts
$content = preg_replace('/\.info-label\s*\{.*?\}/s', ".info-label { display: table-cell; vertical-align: middle; text-align: left; font-size: 6.8px; font-weight: 700; color: rgba(10,36,90,0.38); text-transform: uppercase; letter-spacing: 0.9px; width: 70px; }", $content);
$content = preg_replace('/\.info-colon\s*\{.*?\}/s', ".info-colon { display: table-cell; vertical-align: middle; text-align: center; color: #E09010; font-weight: 700; font-size: 10px; width: 14px; }", $content);
$content = preg_replace('/\.info-value\s*\{.*?\}/s', ".info-value { display: table-cell; vertical-align: middle; text-align: left; font-size: 8.5px; font-weight: 600; color: #152040; }", $content);

// Back body
$content = str_replace(
    "display: flex; flex-direction: column; align-items: center;",
    "text-align: center;",
    $content
);

// Back content
$content = str_replace(
    "display: flex; flex-direction: column;\n            align-items: center; justify-content: center;",
    "text-align: center;",
    $content
);

// Barcode zone
$content = str_replace(
    "display: flex; flex-direction: column;\n            align-items: center; justify-content: center;",
    "text-align: center;",
    $content
);

file_put_contents($file, $content);
echo "CSS flexbox replaced with absolute and table layout.\n";
