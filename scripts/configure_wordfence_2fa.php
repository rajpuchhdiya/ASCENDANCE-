<?php
// This file is intended to be run via WP-CLI: wp eval-file scripts/configure_wordfence_2fa.php
// It configures Wordfence and Two-Factor programmatically and starts a Wordfence scan.

// Wordfence config
if (class_exists('wfConfig')) {
    try {
        wfConfig::set('firewallEnabled', true);
        wfConfig::set('scheduledScansEnabled', true);
        wfConfig::set('loginSec_requireAdminTwoFactor', true);
        echo "wfConfig: firewallEnabled + scheduledScansEnabled + loginSec_requireAdminTwoFactor set\n";
    } catch (Exception $e) {
        echo "wfConfig::set error: " . $e->getMessage() . "\n";
    }
}

// If WAF storage is available, sync advanced blocking flag
if (class_exists('wfWAF') && wfWAF::getInstance()) {
    try {
        $enabled = (wfConfig::get('firewallEnabled') ? true : false);
        wfWAF::getInstance()->getStorageEngine()->setConfig('advancedBlockingEnabled', $enabled, 'synced');
        echo "WAF storage: advancedBlockingEnabled synced\n";
    } catch (Exception $e) {
        echo "WAF storage setConfig error: " . $e->getMessage() . "\n";
    }
}

// Start an initial Wordfence scan (standard)
if (class_exists('wfScanEngine') && class_exists('wfScanner')) {
    try {
        wfScanEngine::startScan(false, wfScanner::SCAN_TYPE_STANDARD);
        echo "Wordfence scan started (standard)\n";
    } catch (Exception $e) {
        echo "wfScanEngine::startScan error: " . $e->getMessage() . "\n";
    }
} else {
    echo "wfScanEngine or wfScanner not available; cannot start scan.\n";
}

// Enable Two-Factor site-wide providers option
$site_providers = array('Two_Factor_Totp','Two_Factor_Backup_Codes','Two_Factor_Email');
update_option('two_factor_enabled_providers', $site_providers);
echo "Site option two_factor_enabled_providers updated\n";

// Enable 2FA for all administrator users by setting user meta
$admins = get_users(array('role' => 'Administrator'));
if (!empty($admins)) {
    foreach ($admins as $a) {
        update_user_meta($a->ID, '_two_factor_enabled_providers', $site_providers);
        echo "Updated 2FA providers for user ID {$a->ID}\n";
    }
} else {
    echo "No administrator users found.\n";
}

// Final note
echo "Configuration script completed. Administrators will need to complete provider setup on next login.\n";
