<?php
require_once( 'c:\XAMPP\htdocs\Ascendance\wp-load.php' );
require_once( ABSPATH . 'wp-admin/includes/post.php' );
require_once( ABSPATH . 'wp-admin/includes/taxonomy.php' );
require_once( __DIR__ . '/Parsedown.php' );

$parsedown = new Parsedown();
$dir = 'C:\XAMPP\htdocs\Ascendance\as\project\uploads';
$files = glob($dir . '/*.md');

// First pass: identify the hub page so we can get its ID for parents
$hub_id = 0;

function parse_front_matter($file_path) {
    $content = file_get_contents($file_path);
    $front_matter = [];
    $body = $content;
    if (preg_match('/^---\s*(.*?)\s*---\s*(.*)$/s', $content, $matches)) {
        $yaml = $matches[1];
        $body = trim($matches[2]);
        
        $lines = explode("\n", $yaml);
        foreach ($lines as $line) {
            if (preg_match('/^([a-zA-Z0-9_-]+):\s*(.*)$/', trim($line), $yaml_matches)) {
                $key = $yaml_matches[1];
                $val = trim($yaml_matches[2], " \"'");
                // Simple array parsing
                if (preg_match('/^\[(.*)\]$/', $val, $arr_matches)) {
                    $arr = array_map('trim', explode(',', $arr_matches[1]));
                    $val = $arr;
                }
                $front_matter[$key] = $val;
            }
        }
    }
    return ['meta' => $front_matter, 'body' => $body];
}

// 1. Process the hub
foreach ($files as $file) {
    if (basename($file) === 'explainer-01-what-is-the-spa.md') {
        $parsed = parse_front_matter($file);
        $meta = $parsed['meta'];
        $html = $parsedown->text($parsed['body']);
        
        $post_data = array(
            'post_title'    => $meta['title'],
            'post_content'  => $html,
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'us-drc-partnership'
        );
        
        $existing = get_page_by_path('us-drc-partnership', OBJECT, 'page');
        if ($existing) {
            $post_data['ID'] = $existing->ID;
            $hub_id = wp_update_post($post_data);
            echo "Updated hub page: $hub_id\n";
        } else {
            $hub_id = wp_insert_post($post_data);
            echo "Created hub page: $hub_id\n";
        }
        
        // Ensure page template is set
        update_post_meta($hub_id, '_wp_page_template', 'page-us-drc-partnership.php');
        break;
    }
}

// 2. Process all other explainers and stubs
foreach ($files as $file) {
    $basename = basename($file);
    if (in_array($basename, ['manifest.md', '301-redirects.md', '301-redirects-analysis.md', 'ANALYSIS_SECTION_SUMMARY.md', 'explainer-01-what-is-the-spa.md'])) {
        continue;
    }
    
    $parsed = parse_front_matter($file);
    $meta = $parsed['meta'];
    if (empty($meta)) continue; // skip non-content files
    
    $html = $parsedown->text($parsed['body']);
    $slug = basename(parse_url($meta['slug'], PHP_URL_PATH));
    
    $is_explainer = strpos($basename, 'explainer-') === 0;
    
    $post_data = array(
        'post_title'    => $meta['title'],
        'post_content'  => $html,
        'post_status'   => 'publish',
        'post_type'     => $is_explainer ? 'page' : 'brief',
        'post_name'     => $slug,
        'post_parent'   => $is_explainer ? $hub_id : 0,
    );
    
    $existing = null;
    if ($is_explainer) {
        $existing = get_page_by_path('us-drc-partnership/' . $slug, OBJECT, 'page');
    } else {
        $existing = get_page_by_path($slug, OBJECT, 'brief');
    }
    
    if ($existing) {
        $post_data['ID'] = $existing->ID;
        $post_id = wp_update_post($post_data);
        echo "Updated post: $post_id ($slug)\n";
    } else {
        $post_id = wp_insert_post($post_data);
        echo "Created post: $post_id ($slug)\n";
    }
    
    // Set tags if any
    if (isset($meta['tags']) && is_array($meta['tags'])) {
        wp_set_post_tags($post_id, $meta['tags'], false);
    }
    
    // Set excerpt if meta description exists
    if (isset($meta['meta_description'])) {
        $update_excerpt = array('ID' => $post_id, 'post_excerpt' => $meta['meta_description']);
        wp_update_post($update_excerpt);
    }
}

echo "Import complete.\n";
