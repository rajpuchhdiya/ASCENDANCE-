<?php
$urls = [
    'http://localhost/Ascendance/',
    'http://localhost/Ascendance/wp-json/',
];

// Let's also bootstrap WordPress first to find actual published posts/Briefs/Dossiers
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/Ascendance/';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
require_once dirname(__DIR__) . '/wp-load.php';

$posts = get_posts([
    'post_type' => ['brief', 'update', 'dossier', 'post', 'page'],
    'posts_per_page' => 10
]);

foreach ($posts as $p) {
    $urls[] = get_permalink($p->ID);
}

// Filter unique URLs
$urls = array_unique($urls);

echo "Testing the following " . count($urls) . " URLs:\n";
foreach ($urls as $url) {
    echo "- $url\n";
}
echo "\n";

foreach ($urls as $url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    // Disable SSL verification for localhost if needed
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $effective_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($response === false) {
        echo "FAIL: $url -> Curl Error: $error\n";
    } else {
        // Separate headers and body
        list($headers, $body) = explode("\r\n\r\n", $response, 2);
        
        // Check for common PHP error indicators in body
        $has_fatal = (stripos($body, 'Fatal error') !== false || stripos($body, 'Parse error') !== false || stripos($body, 'wp-die') !== false || stripos($body, 'Call to undefined function') !== false);
        $has_warning = (stripos($body, 'Warning:') !== false);
        $has_notice = (stripos($body, 'Notice:') !== false);
        
        if ($http_code >= 200 && $http_code < 400 && !$has_fatal) {
            echo "PASS ($http_code): $url (Redirected to: $effective_url)\n";
            if ($has_warning) {
                echo "  -> WARNING: Page contains PHP Warning text!\n";
            }
            if ($has_notice) {
                echo "  -> NOTICE: Page contains PHP Notice text!\n";
            }
        } else {
            echo "FAIL ($http_code): $url\n";
            if ($has_fatal) {
                echo "  -> FATAL: PHP Error/Fatal detected in response content!\n";
                // Print a small snippet of the error
                preg_match('/(Fatal error|Parse error|Call to undefined function).*?(\n|<br)/i', $body, $matches);
                if (isset($matches[0])) {
                    echo "     Error Snippet: " . strip_tags($matches[0]) . "\n";
                }
            }
        }
    }
}
