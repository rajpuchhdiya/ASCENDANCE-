<?php
// Define constants to prevent CLI warning from Polylang if possible
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/Ascendance/';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

require_once dirname(__DIR__) . '/wp-load.php';

echo "WordPress Bootstrapped Successfully!\n";
echo "Site Name: " . get_bloginfo('name') . "\n";
echo "Site URL: " . site_url() . "\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "WordPress Version: " . $GLOBALS['wp_version'] . "\n\n";

// Check active plugins
echo "=== Active Plugins ===\n";
$active_plugins = get_option('active_plugins');
if (!is_array($active_plugins)) {
    echo "No active plugins found.\n";
} else {
    foreach ($active_plugins as $plugin) {
        echo "- " . $plugin . "\n";
    }
}
echo "\n";

// Check custom post types
echo "=== Custom Post Types ===\n";
$post_types = get_post_types();
$custom_types = ['brief', 'update', 'dossier'];
foreach ($custom_types as $type) {
    if (in_array($type, $post_types)) {
        echo "- Post Type '$type' is registered.\n";
    } else {
        echo "- WARNING: Post Type '$type' is NOT registered!\n";
    }
}
echo "\n";

// Check custom taxonomies
echo "=== Custom Taxonomies ===\n";
$taxonomies = get_taxonomies();
$custom_taxs = ['topic', 'region', 'tier', 'post_tag'];
foreach ($custom_taxs as $tax) {
    if (in_array($tax, $taxonomies)) {
        echo "- Taxonomy '$tax' is registered.\n";
    } else {
        echo "- WARNING: Taxonomy '$tax' is NOT registered!\n";
    }
}
echo "\n";

// Test custom paywall/gating logic if we can find it
if (class_exists('Ascendance\Core\Paywall')) {
    echo "- Ascendance Core Paywall class exists.\n";
} else {
    echo "- WARNING: Ascendance Core Paywall class does NOT exist.\n";
}

if (class_exists('Ascendance\Core\AI_Studio')) {
    echo "- Ascendance Core AI Studio class exists.\n";
} else {
    echo "- WARNING: Ascendance Core AI Studio class does NOT exist.\n";
}

if (class_exists('Ascendance\Core\Search_SEO')) {
    echo "- Ascendance Core Search_SEO class exists.\n";
} else {
    echo "- WARNING: Ascendance Core Search_SEO class does NOT exist.\n";
}

if (class_exists('Ascendance\Core\Mission_Control')) {
    echo "- Ascendance Core Mission Control class exists.\n";
} else {
    echo "- WARNING: Ascendance Core Mission Control class does NOT exist.\n";
}

echo "\nVerification script finished.\n";
