<?php
$urls = [
    'http://127.0.0.1/Ascendance/',
    'http://127.0.0.1/Ascendance/wp-json/',
    'http://127.0.0.1/Ascendance/edit-profile/',
    'http://127.0.0.1/Ascendance/cookie-policy/',
    'http://127.0.0.1/Ascendance/preferences/',
    'http://127.0.0.1/Ascendance/dashboard/',
    'http://127.0.0.1/Ascendance/login/',
    'http://127.0.0.1/Ascendance/lostpassword/',
    'http://127.0.0.1/Ascendance/membership-account/',
    'http://127.0.0.1/Ascendance/membership-billing/',
    'http://127.0.0.1/Ascendance/membership-cancel/',
    'http://127.0.0.1/Ascendance/membership-checkout/',
    'http://127.0.0.1/Ascendance/briefs/us-drc-strategic-partnership/',
    'http://127.0.0.1/Ascendance/briefs/sakania-lobito-corridor-aeo/'
];

echo "Testing " . count($urls) . " URLs via HTTP request (using IP, Host: localhost, Connection: close):\n\n";

$all_passed = true;

foreach ($urls as $url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Host: localhost',
        'Connection: close'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $start_time = microtime(true);
    $response = curl_exec($ch);
    $duration = microtime(true) - $start_time;
    
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $effective_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($response === false) {
        echo "FAIL (" . round($duration, 2) . "s): $url -> Curl Error: $error\n";
        $all_passed = false;
    } else {
        list($headers, $body) = explode("\r\n\r\n", $response, 2);
        
        $has_fatal = (stripos($body, 'Fatal error') !== false || stripos($body, 'Parse error') !== false || stripos($body, 'wp-die') !== false || stripos($body, 'Call to undefined function') !== false);
        $has_warning = (stripos($body, 'Warning:') !== false);
        $has_notice = (stripos($body, 'Notice:') !== false);
        
        if ($http_code >= 200 && $http_code < 400 && !$has_fatal) {
            echo "PASS ($http_code) in " . round($duration, 2) . "s: $url\n";
            if ($effective_url !== $url) {
                echo "  -> Redirected to: $effective_url\n";
            }
            if ($has_warning) {
                echo "  -> WARNING: Page contains PHP Warning text!\n";
                $all_passed = false;
            }
            if ($has_notice) {
                echo "  -> NOTICE: Page contains PHP Notice text!\n";
            }
        } else {
            echo "FAIL ($http_code) in " . round($duration, 2) . "s: $url\n";
            $all_passed = false;
            if ($has_fatal) {
                echo "  -> FATAL: PHP Error/Fatal detected in response content!\n";
                preg_match('/(Fatal error|Parse error|Call to undefined function).*?(\n|<br)/i', $body, $matches);
                if (isset($matches[0])) {
                    echo "     Error Snippet: " . strip_tags($matches[0]) . "\n";
                }
            }
        }
    }
}

if ($all_passed) {
    echo "\nALL PAGES PASSED! The site is responsive and free of PHP fatals/warnings.\n";
    exit(0);
} else {
    echo "\nSOME PAGES FAILED OR GENERATED WARNINGS. Please inspect the logs above.\n";
    exit(1);
}
