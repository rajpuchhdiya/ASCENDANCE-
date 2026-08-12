<?php
/**
 * Create WordPress Pages and Assign Templates according to the Ascendance Design System
 */

// Load WordPress environment
require_once dirname( __DIR__ ) . '/wp-load.php';

if ( ! function_exists( 'wp_insert_post' ) ) {
    die( "WordPress environment not loaded properly." );
}

$pages = array(
    array(
        'title'    => 'Advisory',
        'slug'     => 'advisory',
        'template' => 'page-advisory.php',
    ),
    array(
        'title'    => 'Methodology',
        'slug'     => 'methodology',
        'template' => 'page-methodology.php',
    ),
    array(
        'title'    => 'Industries We Cover',
        'slug'     => 'industries-we-cover',
        'template' => 'page-industries.php',
    ),
    array(
        'title'    => 'FAQ',
        'slug'     => 'faq',
        'template' => 'page-faq.php',
    ),
    array(
        'title'    => 'Subscribe',
        'slug'     => 'pricing',
        'template' => 'page-pricing.php',
    ),
    array(
        'title'    => 'Log in',
        'slug'     => 'login',
        'template' => 'page-login.php',
    ),
    array(
        'title'    => 'Legal',
        'slug'     => 'legal',
        'template' => 'page-legal.php',
    ),
    array(
        'title'    => 'Account Dashboard',
        'slug'     => 'account',
        'template' => 'page-dashboard.php',
    ),
    array(
        'title'    => 'US-DRC Strategic Partnership Hub',
        'slug'     => 'us-drc-partnership',
        'template' => 'page-us-drc-partnership.php',
    ),
    array(
        'title'    => 'SPA Glossary',
        'slug'     => 'spa-glossary',
        'template' => 'page-spa-glossary.php',
    ),
    array(
        'title'    => 'CAMI Mining Cadastre Registry',
        'slug'     => 'cami-registry',
        'template' => 'page-cami-registry.php',
    ),
    array(
        'title'    => 'Regulatory Reform Tracker',
        'slug'     => 'regulatory-reform-tracker',
        'template' => 'page-regulatory-reform-tracker.php',
    ),
    array(
        'title'    => 'Strategic Asset Reserve Registry',
        'slug'     => 'sar-registry',
        'template' => 'page-sar-registry.php',
    ),
    array(
        'title'    => 'DRC Sovereign Alternative Rating',
        'slug'     => 'drc-sovereign-rating',
        'template' => 'page-drc-sovereign-rating.php',
    ),
    array(
        'title'    => 'Lobito Corridor Dossier',
        'slug'     => 'lobito-corridor',
        'template' => 'page-lobito-corridor.php',
    ),
);

foreach ( $pages as $p ) {
    $existing = get_page_by_path( $p['slug'] );
    if ( $existing ) {
        $page_id = $existing->ID;
        update_post_meta( $page_id, '_wp_page_template', $p['template'] );
        echo "Updated template for existing page '{$p['title']}' (ID: {$page_id}) -> {$p['template']}\n";
    } else {
        $page_id = wp_insert_post( array(
            'post_title'   => $p['title'],
            'post_name'    => $p['slug'],
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'meta_input'   => array(
                '_wp_page_template' => $p['template'],
            ),
        ) );
        if ( is_wp_error( $page_id ) ) {
            echo "Failed to create page '{$p['title']}': " . $page_id->get_error_message() . "\n";
        } else {
            echo "Created new page '{$p['title']}' (ID: {$page_id}) with template '{$p['template']}'\n";
        }
    }
}

echo "All pages processed successfully.\n";
