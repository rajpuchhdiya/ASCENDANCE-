<?php
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/Ascendance/wp-admin/admin.php?page=ascendance-ai-studio';
require_once __DIR__ . '/../wp-load.php';

echo "START RENDER TEST\n";
try {
    \Ascendance\Core\AI_Studio::get_instance()->render_ai_studio_page();
    echo "\nEND RENDER TEST SUCCESS\n";
} catch (\Throwable $t) {
    echo "\nEXCEPTION: " . $t->getMessage() . "\n" . $t->getTraceAsString() . "\n";
}
