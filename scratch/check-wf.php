<?php
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/Ascendance/';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
require_once dirname(__DIR__) . '/wp-load.php';

echo "=== Wordfence Security Status ===\n";

if (!class_exists('wfConfig')) {
    echo "Wordfence (wfConfig class) is not loaded or active.\n";
    exit(1);
}

$last_scan_completed_raw = wfConfig::get('lastScanCompleted', false);
$last_scan_completed = is_numeric($last_scan_completed_raw) ? date('Y-m-d H:i:s', $last_scan_completed_raw) : $last_scan_completed_raw;
$last_scan_failure = wfConfig::get('lastScanFailureType', false);
$last_scheduled_scan_raw = wfConfig::get('lastScheduledScanStart', false);
$last_scheduled_scan = is_numeric($last_scheduled_scan_raw) ? date('Y-m-d H:i:s', $last_scheduled_scan_raw) : $last_scheduled_scan_raw;
$peak_memory = wfConfig::get('wfPeakMemory', false);

echo "Last Scan Completed: " . ($last_scan_completed ? $last_scan_completed : 'Never') . "\n";
echo "Last Scan Failure Type: " . ($last_scan_failure ? $last_scan_failure : 'None') . "\n";
echo "Last Scheduled Scan Start: " . ($last_scheduled_scan ? $last_scheduled_scan : 'None') . "\n";
echo "Wordfence Peak Memory Usage: " . ($peak_memory ? size_format($peak_memory) : 'Unknown') . "\n";

// Let's query recent issues in Wordfence if table exists
global $wpdb;
$issues_table = $wpdb->prefix . 'wfissues';
$issues_exist = $wpdb->get_var("SHOW TABLES LIKE '$issues_table'");
if ($issues_exist) {
    $unresolved_issues = $wpdb->get_var("SELECT COUNT(*) FROM $issues_table WHERE status = 'new'");
    echo "Unresolved Security Issues: " . $unresolved_issues . "\n";
    if ($unresolved_issues > 0) {
        $recent_issues = $wpdb->get_results("SELECT type, severity, shortMsg FROM $issues_table WHERE status = 'new' ORDER BY time DESC LIMIT 5");
        echo "\nTop 5 Unresolved Issues:\n";
        foreach ($recent_issues as $issue) {
            echo "- [" . strtoupper($issue->severity) . "] " . $issue->type . ": " . $issue->shortMsg . "\n";
        }
    }
} else {
    echo "Wordfence issues table does not exist.\n";
}
