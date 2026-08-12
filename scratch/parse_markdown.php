<?php
require_once( 'c:\XAMPP\htdocs\Ascendance\wp-load.php' );

$dir = 'C:\XAMPP\htdocs\Ascendance\as\project\uploads';
$files = glob($dir . '/*.md');

foreach ($files as $file) {
    if (basename($file) === 'manifest.md' || basename($file) === '301-redirects.md' || basename($file) === '301-redirects-analysis.md' || basename($file) === 'ANALYSIS_SECTION_SUMMARY.md') {
        continue;
    }
    
    $content = file_get_contents($file);
    
    // Parse YAML front matter
    $front_matter = [];
    $body = $content;
    if (preg_match('/^---\s*(.*?)\s*---\s*(.*)$/s', $content, $matches)) {
        $yaml = $matches[1];
        $body = $matches[2];
        
        $lines = explode("\n", $yaml);
        foreach ($lines as $line) {
            if (preg_match('/^([a-zA-Z0-9_-]+):\s*(.*)$/', trim($line), $yaml_matches)) {
                $key = $yaml_matches[1];
                $val = trim($yaml_matches[2], " \"'");
                $front_matter[$key] = $val;
            }
        }
    }
    
    echo "Processing: " . basename($file) . "\n";
    echo "Title: " . ($front_matter['title'] ?? 'No Title') . "\n";
    echo "Slug: " . ($front_matter['slug'] ?? 'No Slug') . "\n\n";
    
    // Determine post type and parent.
    // If it's an explainer and role is 'spoke' or 'hub', it should probably be a page.
    $post_type = 'page';
    $post_name = basename(parse_url($front_matter['slug'] ?? '', PHP_URL_PATH));
    if ($front_matter['role'] === 'hub') {
        $post_name = 'us-drc-partnership';
    }
    
    if (strpos(basename($file), 'stub-') === 0) {
        $post_type = 'brief'; // Or post?
    }
    
    echo "Mapped to type $post_type, slug $post_name\n----------------\n";
}
