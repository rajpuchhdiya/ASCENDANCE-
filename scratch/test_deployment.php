<?php
echo "=== ASCENDANCE DEPLOYMENT VERIFICATION ===" . PHP_EOL;

// 1. Sitemap.xml test
if (file_exists('sitemap.xml')) {
    $xml = simplexml_load_file('sitemap.xml');
    echo "[PASS] sitemap.xml is valid XML with " . count($xml->url) . " URLs." . PHP_EOL;
} else {
    echo "[FAIL] sitemap.xml missing." . PHP_EOL;
}

// 2. site.webmanifest test
if (file_exists('site.webmanifest')) {
    $manifest = json_decode(file_get_contents('site.webmanifest'), true);
    if (json_last_error() === JSON_ERROR_NONE && isset($manifest['name'])) {
        echo "[PASS] site.webmanifest is valid JSON (Name: " . $manifest['name'] . ")." . PHP_EOL;
    } else {
        echo "[FAIL] site.webmanifest is invalid JSON." . PHP_EOL;
    }
} else {
    echo "[FAIL] site.webmanifest missing." . PHP_EOL;
}

// 3. robots.txt test
if (file_exists('robots.txt')) {
    $lines = file('robots.txt');
    echo "[PASS] robots.txt exists (" . count($lines) . " lines)." . PHP_EOL;
} else {
    echo "[FAIL] robots.txt missing." . PHP_EOL;
}

// 4. favicon.svg test
if (file_exists('favicon.svg')) {
    echo "[PASS] favicon.svg exists (" . filesize('favicon.svg') . " bytes)." . PHP_EOL;
} else {
    echo "[FAIL] favicon.svg missing." . PHP_EOL;
}

// 5. colors_and_type.css test
if (file_exists('colors_and_type.css')) {
    $css = file_get_contents('colors_and_type.css');
    if (strpos($css, '--red-fill') !== false && strpos($css, '#BC1B1D') !== false) {
        echo "[PASS] colors_and_type.css contains required brand tokens (--red-fill, --red)." . PHP_EOL;
    } else {
        echo "[FAIL] colors_and_type.css is missing tokens." . PHP_EOL;
    }
} else {
    echo "[FAIL] colors_and_type.css missing." . PHP_EOL;
}

// 6. MCP Server test
if (file_exists('mcp/server.js') && file_exists('mcp/design-system.js')) {
    echo "[PASS] MCP server files present." . PHP_EOL;
} else {
    echo "[FAIL] MCP server files missing." . PHP_EOL;
}

// 7. WordPress Theme colors_and_type.css test
$themeCss = 'wp-content/themes/ascendance/colors_and_type.css';
if (file_exists($themeCss)) {
    $tCss = file_get_contents($themeCss);
    if (strpos($tCss, '--red-fill') !== false) {
        echo "[PASS] Theme colors_and_type.css synchronized with --red-fill token." . PHP_EOL;
    } else {
        echo "[FAIL] Theme colors_and_type.css missing --red-fill." . PHP_EOL;
    }
}

echo "==========================================" . PHP_EOL;
