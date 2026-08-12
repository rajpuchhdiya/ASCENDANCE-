<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files are included inside class methods, so render variables are local.
// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Admin tab routing uses sanitized read-only GET parameters and does not change state.

$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'audit';
?>
<div class="gatetouch-admin-wrap">
    <?php GateTouch_Helpers::page_header( __( 'Website Audit & Health', 'gatetouch-ai-seo' ), __( 'Enterprise technical foundation and automation center.', 'gatetouch-ai-seo' ) ); ?>

    <nav class="gatetouch-nav-tabs">
        <a href="?page=gatetouch-audit&tab=audit" class="gatetouch-nav-tab <?php echo esc_attr( $active_tab === 'audit' ? 'active' : '' ); ?>"><?php echo wp_kses( GateTouch_Helpers::icon( 'chart-bar', 15 ), GateTouch_Helpers::svg_kses_allowed() ); ?> <?php esc_html_e( 'Full Audit', 'gatetouch-ai-seo' ); ?></a>
        <a href="?page=gatetouch-audit&tab=aeo" class="gatetouch-nav-tab <?php echo esc_attr( $active_tab === 'aeo' ? 'active' : '' ); ?>"><?php echo wp_kses( GateTouch_Helpers::icon( 'brain', 15 ), GateTouch_Helpers::svg_kses_allowed() ); ?> <?php esc_html_e( 'AEO &amp; GEO', 'gatetouch-ai-seo' ); ?></a>
        <a href="?page=gatetouch-audit&tab=links" class="gatetouch-nav-tab <?php echo esc_attr( $active_tab === 'links' ? 'active' : '' ); ?>"><?php echo wp_kses( GateTouch_Helpers::icon( 'link', 15 ), GateTouch_Helpers::svg_kses_allowed() ); ?> <?php esc_html_e( 'Link Assistant', 'gatetouch-ai-seo' ); ?></a>
        <a href="?page=gatetouch-audit&tab=automation" class="gatetouch-nav-tab <?php echo esc_attr( $active_tab === 'automation' ? 'active' : '' ); ?>"><?php echo wp_kses( GateTouch_Helpers::icon( 'sparkles', 15 ), GateTouch_Helpers::svg_kses_allowed() ); ?> <?php esc_html_e( 'AI Automation', 'gatetouch-ai-seo' ); ?></a>
    </nav>

    <div class="gatetouch-tab-content">
        <?php
        switch ( $active_tab ) {
            case 'aeo':
                include GATETOUCH_PATH . 'admin/pages/aeo-readiness.php';
                break;
            case 'links':
                include GATETOUCH_PATH . 'admin/pages/link-assistant.php';
                break;
            case 'automation':
                include GATETOUCH_PATH . 'admin/pages/automation.php';
                break;
            case 'audit':
            default:
                include GATETOUCH_PATH . 'admin/pages/seo-analysis.php';
                break;
        }
        ?>
    </div>
</div>
