<?php
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/Ascendance/wp-admin/admin.php?page=ascendance-ai-studio';
$_SERVER['SCRIPT_NAME'] = '/Ascendance/wp-admin/admin.php';
$_GET['page'] = 'ascendance-ai-studio';

require_once __DIR__ . '/../wp-load.php';
require_once ABSPATH . 'wp-admin/includes/admin.php';

// Log in as admin user (ID 1)
wp_set_current_user(1);

ob_start();
\Ascendance\Core\AI_Studio::get_instance()->render_ai_studio_page();
$output = ob_get_clean();

echo "=== MANUAL VERIFICATION RESULTS ===\n";
echo "Rendered Length: " . strlen($output) . " bytes\n\n";

$checks = array(
    'ASCENDANCE · AI Studio Header'  => strpos($output, 'ASCENDANCE &middot; AI Studio') !== false,
    'ARTICLE GENERATION SETUP Card'  => strpos($output, '1. ARTICLE GENERATION SETUP') !== false,
    'REAL-TIME COMPILED DRAFT Card'   => strpos($output, '2. REAL-TIME COMPILED DRAFT') !== false,
    'Primary Focus Dropdown'          => strpos($output, 'id="article_topic"') !== false,
    'Geographic Regions Checkboxes'  => strpos($output, 'class="region-checkbox"') !== false,
    'AI Engine Provider Selection'   => strpos($output, 'id="ai_provider"') !== false,
    'Compile Draft Button'            => strpos($output, 'COMPILE INTELLIGENCE DRAFT') !== false,
    'Budget Bar'                      => strpos($output, 'MONTHLY BUDGET CAP PROGRESS') !== false,
    'Terminal Output Console'         => strpos($output, 'id="draft_output"') !== false,
    'Settings Redirect Link'          => strpos($output, 'SYSTEM PROMPT & API KEY OVERRIDES') !== false,
);

$all_passed = true;
foreach ($checks as $name => $passed) {
    if ($passed) {
        echo "[SUCCESS] $name -> PASSED\n";
    } else {
        echo "[FAILURE] $name -> FAILED\n";
        $all_passed = false;
    }
}

if ($all_passed) {
    echo "\n>>> VERIFICATION COMPLETE: ALL UI COMPONENTS RENDERED SUCCESSFULLY WITHOUT ERRORS! <<<\n";
} else {
    echo "\n>>> VERIFICATION FAILED: SOME COMPONENTS MISSING! <<<\n";
}
