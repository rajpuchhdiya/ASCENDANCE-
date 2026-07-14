<?php
// create_gdpr_pages.php
// Creates/updates Privacy Policy and Cookie Policy pages from docs/*.md

if ( php_sapi_name() !== 'cli' ) {
    echo "Run from CLI.\n";
    exit(1);
}

$root = dirname(__DIR__);
require_once $root . '/wp-load.php';

function md_to_html($md_path) {
    if ( ! file_exists($md_path) ) return '';
    $txt = file_get_contents($md_path);
    // Very small conversion: preserve headings and paragraphs
    $html = '';
    $lines = preg_split("/\r\n|\n|\r/", $txt);
    foreach ($lines as $l) {
        $l = trim($l);
        if ($l === '') { $html .= "<p></p>\n"; continue; }
        if (preg_match('/^# (.+)/', $l, $m)) { $html .= '<h1>' . esc_html($m[1]) . '</h1>\n'; continue; }
        if (preg_match('/^## (.+)/', $l, $m)) { $html .= '<h2>' . esc_html($m[1]) . '</h2>\n'; continue; }
        if (preg_match('/^### (.+)/', $l, $m)) { $html .= '<h3>' . esc_html($m[1]) . '</h3>\n'; continue; }
        $html .= '<p>' . esc_html($l) . '</p>\n';
    }
    return $html;
}

$pages = array(
    array('slug' => 'privacy-policy', 'title' => 'Privacy Policy', 'file' => $root . '/docs/privacy-policy.md'),
    array('slug' => 'cookie-policy', 'title' => 'Cookie Policy', 'file' => $root . '/docs/cookie-policy.md'),
);

foreach ($pages as $p) {
    $content = md_to_html($p['file']);
    if (empty($content)) {
        echo "Skipping {$p['slug']}: source not found.\n";
        continue;
    }

    $existing = get_page_by_path($p['slug']);
    $post = array(
        'post_title'   => $p['title'],
        'post_name'    => $p['slug'],
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'post_content' => $content,
    );

    if ($existing) {
        $post['ID'] = $existing->ID;
        wp_update_post($post);
        echo "Updated page: {$p['title']} (ID={$existing->ID})\n";
    } else {
        $id = wp_insert_post($post);
        if ($id) echo "Created page: {$p['title']} (ID={$id})\n";
        else echo "Failed to create page: {$p['title']}\n";
    }
}

echo "Done.\n";
