<?php
// Set up server environment to simulate a request to membership-checkout
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/Ascendance/membership-checkout/';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['REQUEST_METHOD'] = 'GET';

echo "Bootstrapping WordPress...\n";
require_once dirname(__DIR__) . '/wp-load.php';
echo "WordPress bootstrapped.\n";

// Enable detailed error output
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set custom error handler to log everything
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo "PHP Error ($errno): $errstr in $errfile on line $errline\n";
    return false;
});

echo "Querying checkout page...\n";
global $wp_query, $wp_the_query, $post;
$wp_the_query->query(['pagename' => 'membership-checkout']);
$wp_query = $wp_the_query;

if (have_posts()) {
    the_post();
    echo "Found post: " . get_the_title() . " (ID: " . get_the_ID() . ")\n";
    echo "Content snippet: " . substr(strip_tags(get_the_content()), 0, 100) . "...\n";
    
    echo "Starting template rendering...\n";
    $start = microtime(true);
    
    // We will capture output
    ob_start();
    try {
        // Find the page template
        $template = get_page_template();
        echo "Using template: $template\n";
        
        // Include template
        include $template;
        $output = ob_get_clean();
        $duration = microtime(true) - $start;
        echo "Template rendered successfully in " . round($duration, 4) . " seconds.\n";
        echo "Output length: " . strlen($output) . " bytes\n";
        
        // Print the first 300 chars of output to verify it's working
        echo "Output preview:\n" . substr(strip_tags($output), 0, 300) . "...\n";
    } catch (Throwable $e) {
        ob_end_clean();
        echo "EXCEPTION: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        echo "Trace:\n" . $e->getTraceAsString() . "\n";
    }
} else {
    echo "Checkout page not found!\n";
}
