<?php
/**
 * MU-plugin: Consent-gated analytics helper
 * Use `ascendance_enqueue_analytics()` from theme or plugin to print GTM/analytics only when consent present.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function ascendance_has_analytics_consent() {
    // Complianz sets cookies like `cmplz_statistics` and `cmplz_marketing` when consent granted.
    if ( ! empty( $_COOKIE['cmplz_statistics'] ) ) {
        return true;
    }
    if ( ! empty( $_COOKIE['cmplz_marketing'] ) ) {
        return true;
    }
    // Fallback: check generic cookie consent status
    if ( ! empty( $_COOKIE['cmplz_consent_status'] ) && in_array( $_COOKIE['cmplz_consent_status'], array( 'yes', 'allow' ), true ) ) {
        return true;
    }
    return false;
}

function ascendance_enqueue_analytics() {
    // This function echoes GTM container snippet when consent exists.
    // Replace 'GTM-XXXXXX' with your container ID via .env or theme settings.
    $gtm = getenv( 'ASCENDANCE_GTM_ID' );
    if ( ! $gtm ) {
        return; // no GTM configured
    }

    if ( ! ascendance_has_analytics_consent() ) {
        // Do not print analytics if consent not given
        return;
    }

    // Print GTM head snippet
    echo "<!-- Google Tag Manager -->\n";
    echo "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id=' + i + dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','" . esc_js( $gtm ) . "');</script>\n";
}

// Example: theme should call ascendance_enqueue_analytics() in header.php where GTM is expected.
