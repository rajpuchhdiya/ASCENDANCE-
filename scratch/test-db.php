<?php
mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli('127.0.0.1', 'root', '', 'ascendance');
if ($conn->connect_error) {
    echo "DB Connection failed: " . $conn->connect_error . "\n";
    exit(1);
}
echo "DB Connected successfully.\n";

$result = $conn->query("SHOW TABLES");
if (!$result) {
    echo "Failed to query tables: " . $conn->error . "\n";
    exit(1);
}

$tables = [];
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}

echo "Found " . count($tables) . " tables.\n";
if (count($tables) > 0) {
    echo "First 10 tables:\n";
    print_r(array_slice($tables, 0, 10));
}

// Check wp_options siteurl/home
$res_url = $conn->query("SELECT option_name, option_value FROM wp_options WHERE option_name IN ('siteurl', 'home')");
if ($res_url) {
    while ($row = $res_url->fetch_assoc()) {
        echo "Option " . $row['option_name'] . ": " . $row['option_value'] . "\n";
    }
}
$conn->close();
