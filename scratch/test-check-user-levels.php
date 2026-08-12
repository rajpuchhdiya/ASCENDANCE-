<?php
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/Ascendance/';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
require_once dirname(__DIR__) . '/wp-load.php';

$test_users = [
    3 => 'Essential',
    4 => 'Professional',
    5 => 'Enterprise'
];

echo "=== Test User Membership Status ===\n";
foreach ($test_users as $user_id => $expected_level) {
    $user = get_userdata($user_id);
    if ($user) {
        echo "User ID $user_id ({$user->user_login} | {$user->user_email}):\n";
        $level = pmpro_getMembershipLevelForUser($user_id);
        if ($level) {
            echo "  Active Level: ID {$level->id} - {$level->name} (Status: {$level->status})\n";
        } else {
            echo "  Active Level: NONE\n";
        }
    } else {
        echo "User ID $user_id does NOT exist.\n";
    }
    echo "\n";
}
