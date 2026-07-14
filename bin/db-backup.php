<?php
/*
 * Simple DB backup script. Run from CLI or schedule via cron/Task Scheduler.
 * Usage: php db-backup.php
 */

$root = dirname(__DIR__);
$env = [];
$envFile = $root . '/.env';
if ( file_exists( $envFile ) ) {
    $lines = file( $envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
    foreach ( $lines as $line ) {
        $line = trim( $line );
        if ( empty( $line ) || strpos( $line, '#' ) === 0 ) continue;
        if ( strpos( $line, '=' ) !== false ) {
            list( $k, $v ) = explode( '=', $line, 2 );
            $env[ trim( $k ) ] = trim( $v, " \"'\n\r" );
        }
    }
}

$dbname = $env['DB_NAME'] ?? 'ascendance';
$dbuser = $env['DB_USER'] ?? 'root';
$dbpass = $env['DB_PASSWORD'] ?? '';
$dbhost = $env['DB_HOST'] ?? 'localhost';

$backupDir = $root . '/wp-content/backups';
if ( ! is_dir( $backupDir ) ) {
    mkdir( $backupDir, 0750, true );
}

$timestamp = date( 'Ymd-His' );
$filename = sprintf( '%s/%s-%s.sql.gz', $backupDir, $dbname, $timestamp );

// Build mysqldump command
$mysqldump = 'mysqldump';
$cmd = sprintf('%s -h%s -u%s %s', escapeshellcmd($mysqldump), escapeshellarg($dbhost), escapeshellarg($dbuser), escapeshellarg($dbname));
if ( $dbpass !== '' ) {
    // Use env var to avoid password in argv on some systems
    putenv('MYSQL_PWD=' . $dbpass);
}

$cmd .= ' | gzip > ' . escapeshellarg( $filename );

exec( $cmd, $output, $ret );
if ( $ret === 0 ) {
    echo "Backup written: $filename\n";
    // Rotate: keep last 14 files
    $files = glob( $backupDir . '/' . $dbname . '-*.sql.gz' );
    usort( $files, function( $a, $b ) { return filemtime($b) - filemtime($a); } );
    $keep = 14;
    foreach ( array_slice( $files, $keep ) as $old ) {
        @unlink( $old );
    }
    exit(0);
}

fwrite( STDERR, "Backup failed (exit code: $ret)\n" );
exit(2);
