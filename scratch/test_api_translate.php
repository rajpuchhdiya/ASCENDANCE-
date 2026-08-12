<?php
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/Ascendance/wp-json/ascendance/v1/ai-studio/translate';

require_once __DIR__ . '/../wp-load.php';
wp_set_current_user(1);

$request = new \WP_REST_Request('POST', '/ascendance/v1/ai-studio/translate');
$request->set_header('content-type', 'application/json');
$request->set_body(json_encode(array(
    'text_en'  => '<h2>Introduction</h2><p>This brief assesses the geopolitical landscape of Critical Minerals across Central Africa.</p>',
    'provider' => 'mock',
    'model'    => 'mock-model'
)));

$start_time = microtime(true);
$response = rest_do_request($request);
$end_time = microtime(true);

$data = $response->get_data();
$duration = round($end_time - $start_time, 3);

echo "=== ASYNC TRANSLATION REST TEST ===\n";
echo "HTTP Status: " . $response->get_status() . "\n";
echo "Response Time: {$duration} seconds\n";
echo "French Output: " . (!empty($data['text_fr']) ? 'YES' : 'NO') . "\n";
echo "Content: " . ($data['text_fr'] ?? 'N/A') . "\n";

if ($response->get_status() === 200 && !empty($data['text_fr'])) {
    echo "\n>>> TRANSLATION REST API TEST PASSED! <<<\n";
} else {
    echo "\n>>> TRANSLATION TEST FAILED! <<<\n";
}
