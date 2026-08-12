<?php
require_once dirname( __DIR__ ) . '/wp-load.php';

$slug = 'lostpassword';
$title = 'Reset Credentials';
$template = 'page-lostpassword.php';

$existing = get_page_by_path( $slug );
if ( $existing ) {
    update_post_meta( $existing->ID, '_wp_page_template', $template );
    echo "Updated template for existing page '{$title}' (ID: {$existing->ID}) -> {$template}\n";
} else {
    $page_id = wp_insert_post( array(
        'post_title'   => $title,
        'post_name'    => $slug,
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'meta_input'   => array(
            '_wp_page_template' => $template,
        ),
    ) );
    echo "Created new page '{$title}' (ID: {$page_id}) with template '{$template}'\n";
}
