<?php
// Run under WP-CLI: php C:\wp-cli\wp-cli.phar eval-file scripts/check_wordfence_scan.php --path='C:\XAMPP\htdocs\Ascendance'
if (!class_exists('wfConfig')) {
    echo "wfConfig class not available. Is Wordfence active?\n";
    return;
}
$status = array();
$status['lastScanCompleted'] = wfConfig::get('lastScanCompleted', false);
$status['lastScanFailureType'] = wfConfig::get('lastScanFailureType', false);
$status['lastScheduledScanStart'] = wfConfig::get('lastScheduledScanStart', false);
$status['wfPeakMemory'] = wfConfig::get('wfPeakMemory', false);
echo json_encode($status, JSON_PRETTY_PRINT) . "\n";
