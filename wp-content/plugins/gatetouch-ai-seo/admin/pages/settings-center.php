<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files are included inside class methods, so render variables are local.
// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Admin settings tabs use sanitized read-only GET parameters and do not change state.

$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'appearance';
$expert_mode = get_option( 'gatetouch_expert_mode' ) === '1';

// Tab registry — single source of truth for the nav and the router below, so
// the two can no longer drift apart (which is how the Analytics panel ended up
// orphaned).
$gt_tabs = [
    'appearance'   => [ 'search',           __( 'Search Appearance', 'gatetouch-ai-seo' ), 'settings/search-appearance.php' ],
    'core'         => [ 'target',           __( 'Core SEO',          'gatetouch-ai-seo' ), 'settings/core-seo.php' ],
    'ai'           => [ 'robot',            __( 'AI & AEO',          'gatetouch-ai-seo' ), 'settings/ai-aeo.php' ],
    'files'        => [ 'sitemap',          __( 'Sitemaps & Files',  'gatetouch-ai-seo' ), 'sitemap-settings.php' ],
    'redirects'    => [ 'arrows-right-left',__( 'Redirects',         'gatetouch-ai-seo' ), 'redirect-manager.php' ],
    'tech'         => [ 'settings-2',       __( 'Technical',         'gatetouch-ai-seo' ), 'settings/technical.php' ],
    'integrations' => [ 'plug-connected',   __( 'Integrations',      'gatetouch-ai-seo' ), 'settings/integrations.php' ],
    'hardening'    => [ 'shield-check',     __( 'Hardening',         'gatetouch-ai-seo' ), 'settings/hardening.php' ],
    'business'     => [ 'building',         __( 'Business',          'gatetouch-ai-seo' ), 'settings/business.php' ],
    'expert'       => [ 'rocket',           __( 'Advanced',          'gatetouch-ai-seo' ), 'settings/advanced.php' ],
];

if ( ! isset( $gt_tabs[ $active_tab ] ) ) {
    $active_tab = 'appearance';
}
?>
<div class="gatetouch-admin-wrap">
    <?php GateTouch_Helpers::page_header( __( 'Enterprise SEO Architecture', 'gatetouch-ai-seo' ), __( 'Smart AI-powered configuration engine', 'gatetouch-ai-seo' ) ); ?>

    <div class="gatetouch-tabs-shell">
        <nav class="gatetouch-nav-tabs gatetouch-nav-tabs--vertical" aria-label="<?php esc_attr_e( 'Settings sections', 'gatetouch-ai-seo' ); ?>">
            <div class="gatetouch-tabs-shell__rail-title"><?php esc_html_e( 'Settings', 'gatetouch-ai-seo' ); ?></div>
            <?php foreach ( $gt_tabs as $gt_slug => $gt_tab ) : ?>
            <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'gatetouch-settings', 'tab' => $gt_slug ], admin_url( 'admin.php' ) ) ); ?>"
               class="gatetouch-nav-tab <?php echo esc_attr( $active_tab === $gt_slug ? 'active' : '' ); ?>"
               <?php echo $active_tab === $gt_slug ? 'aria-current="page"' : ''; ?>>
                <?php echo wp_kses( GateTouch_Helpers::icon( $gt_tab[0], 15 ), GateTouch_Helpers::svg_kses_allowed() ); ?>
                <?php echo esc_html( $gt_tab[1] ); ?>
            </a>
            <?php endforeach; ?>
        </nav>

        <div class="gatetouch-tab-content">
            <?php include GATETOUCH_PATH . 'admin/pages/' . $gt_tabs[ $active_tab ][2]; ?>
        </div>
    </div>
</div>

