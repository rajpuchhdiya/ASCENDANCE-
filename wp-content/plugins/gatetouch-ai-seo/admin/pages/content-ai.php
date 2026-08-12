<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files are included inside class methods, so render variables are local.
// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Admin tab routing uses sanitized read-only GET parameters and does not change state.

if ( ! GateTouch_AI_Engine::has_api_key() ) {
    // The gate must sit inside the standard page wrapper. Without it the screen
    // renders edge-to-edge with no max-width or gutter, unlike every other page.
    echo '<div class="gatetouch-admin-wrap">';
    GateTouch_Helpers::page_header( __( 'Content AI Optimizer', 'gatetouch-ai-seo' ), __( 'Scale your content production with enterprise-grade AI.', 'gatetouch-ai-seo' ) );
    GateTouch_Helpers::api_key_gate( __( 'Content AI Optimizer', 'gatetouch-ai-seo' ) );
    echo '</div>';
    return;
}

$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'bulk';
?>
<div class="gatetouch-admin-wrap">
    <?php GateTouch_Helpers::page_header( __( 'Content AI Optimizer', 'gatetouch-ai-seo' ), __( 'Scale your content production with enterprise-grade AI.', 'gatetouch-ai-seo' ) ); ?>

    <nav class="gatetouch-nav-tabs">
        <a href="?page=gatetouch-content-ai&tab=brief" class="gatetouch-nav-tab <?php echo esc_attr( $active_tab === 'brief' ? 'active' : '' ); ?>"><?php echo wp_kses( GateTouch_Helpers::icon( 'compass', 15 ), GateTouch_Helpers::svg_kses_allowed() ); ?> <?php esc_html_e( 'Content Brief', 'gatetouch-ai-seo' ); ?></a>
        <a href="?page=gatetouch-content-ai&tab=bulk" class="gatetouch-nav-tab <?php echo esc_attr( $active_tab === 'bulk' ? 'active' : '' ); ?>"><?php echo wp_kses( GateTouch_Helpers::icon( 'stack-2', 15 ), GateTouch_Helpers::svg_kses_allowed() ); ?> <?php esc_html_e( 'Bulk AI Meta', 'gatetouch-ai-seo' ); ?></a>
        <a href="?page=gatetouch-content-ai&tab=social" class="gatetouch-nav-tab <?php echo esc_attr( $active_tab === 'social' ? 'active' : '' ); ?>"><?php echo wp_kses( GateTouch_Helpers::icon( 'share', 15 ), GateTouch_Helpers::svg_kses_allowed() ); ?> <?php esc_html_e( 'Social AI', 'gatetouch-ai-seo' ); ?></a>
        <a href="?page=gatetouch-content-ai&tab=images" class="gatetouch-nav-tab <?php echo esc_attr( $active_tab === 'images' ? 'active' : '' ); ?>"><?php echo wp_kses( GateTouch_Helpers::icon( 'photo', 15 ), GateTouch_Helpers::svg_kses_allowed() ); ?> <?php esc_html_e( 'AI Image Tools', 'gatetouch-ai-seo' ); ?></a>
    </nav>

    <div class="gatetouch-tab-content">
        <?php
        switch ( $active_tab ) {
            case 'brief':
                include GATETOUCH_PATH . 'admin/pages/content-brief.php';
                break;
            case 'social':
                include GATETOUCH_PATH . 'admin/pages/social-ai.php';
                break;
            case 'images':
                include GATETOUCH_PATH . 'admin/pages/image-ai.php';
                break;
            case 'bulk':
            default:
                include GATETOUCH_PATH . 'admin/pages/bulk-meta.php';
                break;
        }
        ?>
    </div>
</div>
