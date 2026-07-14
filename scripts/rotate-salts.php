<?php
// rotate-salts.php
// Usage: php rotate-salts.php /path/to/.env

if ( PHP_SAPI !== 'cli' ) {
    fwrite( STDERR, "This script must be run from CLI.\n" );
    exit(2);
}

$envPath = $argv[1] ?? __DIR__ . '/../.env';
if ( ! file_exists( $envPath ) ) {
    fwrite( STDERR, "Env file not found: $envPath\n" );
    exit(2);
}

$backup = $envPath . '.' . date( 'Ymd-His' ) . '.bak';
copy( $envPath, $backup );
echo "Backup written to: $backup\n";

$saltUrl = 'https://api.wordpress.org/secret-key/1.1/salt/';
$saltData = @file_get_contents( $saltUrl );
if ( ! $saltData ) {
    fwrite( STDERR, "Failed to fetch salts from WordPress API.\n" );
    exit(2);
}

$lines = preg_split("/\r\n|\n|\r/", $saltData );
$env = file( $envPath, FILE_IGNORE_NEW_LINES );

$map = array(
    'AUTH_KEY' => null,
    'SECURE_AUTH_KEY' => null,
    'LOGGED_IN_KEY' => null,
    'NONCE_KEY' => null,
    'AUTH_SALT' => null,
    'SECURE_AUTH_SALT' => null,
    'LOGGED_IN_SALT' => null,
    'NONCE_SALT' => null,
);

foreach ( $lines as $l ) {
    if ( preg_match('/define\(\s*\'([A-Z_]+)\'\s*,\s*\'(.+)\'\s*\);/', $l, $m) ) {
        // Not expected; skip
        continue;
    }
    // Each line looks like: define('AUTH_KEY', '...');
    if ( preg_match("/define\(\s*'([A-Z_]+)'\s*,\s*'(.+)'\s*\);/", $l, $m) ) {
        $k = $m[1];
        $v = $m[2];
        if ( array_key_exists( $k, $map ) ) {
            $map[$k] = $v;
        }
    }
}

// Fallback: parse saltData by known salt function format
preg_match_all("/define\(\s*'([A-Z_]+)'\s*,\s*'([^']+)'\s*\);/", $saltData, $matches, PREG_SET_ORDER);
foreach ( $matches as $m ) {
    $k = $m[1];
    $v = $m[2];
    if ( array_key_exists( $k, $map ) ) {
        $map[$k] = $v;
    }
}

// Replace or append in env file
foreach ( $map as $k => $v ) {
    if ( $v === null ) continue;
    $found = false;
    foreach ( $env as &$line ) {
        if ( preg_match('/^' . preg_quote( $k, '/' ) . '\s*=/', $line ) ) {
            $line = $k . '="' . $v . '"';
            $found = true;
            break;
        }
    }
    if ( ! $found ) {
        $env[] = $k . '="' . $v . '"';
    }
}

file_put_contents( $envPath, implode("\n", $env) . "\n" );
echo "Updated salts in: $envPath\n";

echo "Done. Restart PHP-FPM or web server if necessary.\n";
