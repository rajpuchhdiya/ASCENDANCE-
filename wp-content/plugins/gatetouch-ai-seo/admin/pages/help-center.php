<?php
/**
 * Help & Support — documentation plus the diagnostics tools that used to sit at
 * the top level. Diagnostics is a troubleshooting aid, not a workflow stage, so
 * it belongs beside the docs rather than competing with Settings for attention.
 */
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files are included inside class methods, so render variables are local.
// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Admin tab routing uses sanitized read-only GET parameters and does not change state.

$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'docs';

$gt_help_tabs = [
    'docs'        => [ 'file-text', __( 'Documentation',  'gatetouch-ai-seo' ), 'help-docs.php' ],
    'diagnostics' => [ 'tool',      __( 'AI Diagnostics', 'gatetouch-ai-seo' ), 'api-diagnostics.php' ],
];

if ( ! isset( $gt_help_tabs[ $active_tab ] ) ) {
    $active_tab = 'docs';
}
?>
<div class="gatetouch-admin-wrap">
    <?php GateTouch_Helpers::page_header( __( 'Help & Support', 'gatetouch-ai-seo' ), __( 'Documentation, guides, and connection diagnostics', 'gatetouch-ai-seo' ) ); ?>

    <nav class="gatetouch-nav-tabs">
        <?php foreach ( $gt_help_tabs as $gt_slug => $gt_tab ) : ?>
        <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'gatetouch-help', 'tab' => $gt_slug ], admin_url( 'admin.php' ) ) ); ?>"
           class="gatetouch-nav-tab <?php echo esc_attr( $active_tab === $gt_slug ? 'active' : '' ); ?>">
            <?php echo wp_kses( GateTouch_Helpers::icon( $gt_tab[0], 15 ), GateTouch_Helpers::svg_kses_allowed() ); ?>
            <?php echo esc_html( $gt_tab[1] ); ?>
        </a>
        <?php endforeach; ?>
    </nav>

    <div class="gatetouch-tab-content">
        <?php include GATETOUCH_PATH . 'admin/pages/' . $gt_help_tabs[ $active_tab ][2]; ?>
    </div>
</div>
