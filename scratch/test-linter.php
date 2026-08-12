<?php
$directories = [
    'wp-content/plugins/ascendance-core',
    'wp-content/themes/ascendance'
];

$errors = [];
$checked_count = 0;

foreach ($directories as $dir) {
    $full_path = dirname(__DIR__) . '/' . $dir;
    if (!is_dir($full_path)) {
        echo "Directory not found: $full_path\n";
        continue;
    }
    
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($full_path));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            // Ignore node_modules
            if (strpos($file->getPathname(), 'node_modules') !== false) {
                continue;
            }
            
            $checked_count++;
            $filepath = $file->getPathname();
            $output = [];
            $return_var = 0;
            exec("php -l " . escapeshellarg($filepath), $output, $return_var);
            
            if ($return_var !== 0) {
                $errors[] = [
                    'file' => $filepath,
                    'error' => implode("\n", $output)
                ];
            }
        }
    }
}

echo "Checked $checked_count PHP files.\n";
if (empty($errors)) {
    echo "LINT PASSED: No PHP syntax errors found.\n";
} else {
    echo "LINT FAILED: Found " . count($errors) . " files with syntax errors:\n";
    foreach ($errors as $err) {
        echo "----------------------------------------\n";
        echo "File: " . $err['file'] . "\n";
        echo "Error: " . $err['error'] . "\n";
    }
    exit(1);
}
