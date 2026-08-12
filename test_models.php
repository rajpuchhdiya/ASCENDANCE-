<?php
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
require_once( dirname( __FILE__ ) . '/wp-load.php' );

function get_my_api_key( $provider ) {
    $env_key = strtoupper( $provider ) . '_API_KEY';
    if ( defined( $env_key ) && ! empty( constant( $env_key ) ) ) {
        return trim( constant( $env_key ) );
    }
    $db_key = get_option( 'ascendance_' . $provider . '_api_key' );
    if ( ! empty( $db_key ) ) {
        return trim( $db_key );
    }
    return '';
}

$anthropic_key = get_my_api_key('anthropic');
$openai_key = get_my_api_key('openai');
$gemini_key = get_my_api_key('gemini');

echo "Anthropic Key: " . ($anthropic_key ? substr($anthropic_key, 0, 10) . "..." : "None") . "\n";
echo "OpenAI Key: " . ($openai_key ? substr($openai_key, 0, 10) . "..." : "None") . "\n";
echo "Gemini Key: " . ($gemini_key ? substr($gemini_key, 0, 10) . "..." : "None") . "\n";

function test_openai_model($key, $model) {
    if (!$key) return "Skipped";
    $body = array(
        'model'      => $model,
        'max_tokens' => 10,
        'messages'   => array(
            array( 'role' => 'user', 'content' => 'hello' )
        )
    );
    $response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', array(
        'timeout' => 15,
        'headers' => array(
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $key
        ),
        'body' => wp_json_encode( $body )
    ) );
    if ( is_wp_error( $response ) ) {
        return "WP Error: " . $response->get_error_message();
    }
    $code = wp_remote_retrieve_response_code( $response );
    $body_content = wp_remote_retrieve_body( $response );
    if ($code !== 200) {
        $json = json_decode($body_content, true);
        if ($json && isset($json['error'])) {
            return "Failed: HTTP $code - " . $json['error']['message'];
        }
        return "Failed: HTTP $code - " . current(explode("\n", wordwrap($body_content, 100)));
    }
    return "Success";
}

function test_gemini_model($key, $model) {
    if (!$key) return "Skipped";
    $body = array(
        'contents' => array(
            array( 'parts' => array( array('text' => 'hello') ) )
        )
    );
    $response = wp_remote_post( "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$key}", array(
        'timeout' => 15,
        'headers' => array(
            'Content-Type' => 'application/json'
        ),
        'body' => wp_json_encode( $body )
    ) );
    if ( is_wp_error( $response ) ) {
        return "WP Error: " . $response->get_error_message();
    }
    $code = wp_remote_retrieve_response_code( $response );
    $body_content = wp_remote_retrieve_body( $response );
    if ($code !== 200) {
        $json = json_decode($body_content, true);
        if ($json && isset($json['error'])) {
            return "Failed: HTTP $code - " . $json['error']['message'];
        }
        return "Failed: HTTP $code - " . current(explode("\n", wordwrap($body_content, 100)));
    }
    return "Success";
}

$openai_models = array(
    'gpt-4o',
    'gpt-4-turbo',
    'gpt-4',
    'gpt-3.5-turbo'
);

echo "\nTesting OpenAI Models:\n";
foreach ($openai_models as $model) {
    echo "- $model: " . test_openai_model($openai_key, $model) . "\n";
}

$gemini_models = array(
    'gemini-1.5-pro',
    'gemini-1.5-flash',
    'gemini-pro'
);

echo "\nTesting Gemini Models:\n";
foreach ($gemini_models as $model) {
    echo "- $model: " . test_gemini_model($gemini_key, $model) . "\n";
}
