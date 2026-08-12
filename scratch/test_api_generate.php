<?php
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/Ascendance/wp-json/ascendance/v1/ai-studio/generate';

require_once __DIR__ . '/../wp-load.php';
wp_set_current_user(1);

$request = new \WP_REST_Request('POST', '/ascendance/v1/ai-studio/generate');
$request->set_header('content-type', 'application/json');
$request->set_body(json_encode(array(
    'type'          => 'brief',
    'provider'      => 'mock',
    'topic'         => 'Critical Minerals',
    'regions'       => array('DRC', 'CENTRAL AFRICA'),
    'notes'         => 'Test briefing notes on cobalt supply chain.',
    'custom_prompt' => '',
    'keywords'      => 'cobalt, Sakania',
    'tone'          => 'institutional'
)));

$start_time = microtime(true);
$response = rest_do_request($request);
$end_time = microtime(true);

$data = $response->get_data();
$duration = round($end_time - $start_time, 3);

echo "HTTP Status: " . $response->get_status() . "\n";
echo "Response Time: {$duration} seconds\n";
echo "Data: " . print_r($data, true) . "\n";
