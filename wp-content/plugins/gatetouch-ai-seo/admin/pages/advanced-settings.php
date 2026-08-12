<?php
/**
 * GateTouch — Advanced Settings Page
 */
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files are included inside class methods, so render variables are local.

if ( isset( $_POST['gatetouch_save_advanced'] ) && check_admin_referer( 'gatetouch_save_advanced' ) ) {
    $advanced = [
        'tru_seo_score'    => isset( $_POST['tru_seo_score'] ) ? '1' : '',
        'headline_analyzer' => isset( $_POST['headline_analyzer'] ) ? '1' : '',
        'admin_bar_menu'   => isset( $_POST['admin_bar_menu'] ) ? '1' : '',
        'dashboard_widgets' => isset( $_POST['dashboard_widgets'] ) ? '1' : '',
        'usage_tracking'   => isset( $_POST['usage_tracking'] ) ? '1' : '',
        'uninstall_cleanup' => isset( $_POST['uninstall_cleanup'] ) ? '1' : '',
    ];
    update_option( 'gatetouch_advanced_settings', $advanced );
    $post_type_columns = isset( $_POST['gatetouch_pts'] ) && is_array( $_POST['gatetouch_pts'] ) ? array_map( 'sanitize_key', wp_unslash( $_POST['gatetouch_pts'] ) ) : [];
    update_option( 'gatetouch_post_type_columns', $post_type_columns );
    GateTouch_Helpers::notice( __( '✅ Advanced settings saved!', 'gatetouch-ai-seo' ), 'success' );
}

$adv = get_option( 'gatetouch_advanced_settings', [
    'tru_seo_score'    => '1',
    'headline_analyzer' => '1',
    'admin_bar_menu'   => '1',
    'dashboard_widgets' => '1',
    'usage_tracking'   => '',
    'uninstall_cleanup' => '',
] );
?>

<div class="gatetouch-admin-wrap">
    <div class="gatetouch-admin-header">
        <div class="gatetouch-admin-header__left">
            <div class="gatetouch-admin-header__icon" style="background: linear-gradient(135deg, #4f46e5, #06b6d4); color:#fff; width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:24px;">
                <span class="dashicons dashicons-admin-generic" style="font-size:24px; width:auto; height:auto;"></span>
            </div>
            <div>
                <h1><?php esc_html_e( 'Advanced Settings', 'gatetouch-ai-seo' ); ?> <span class="gatetouch-version-badge" style="background:var(--riq-ai-gradient); color:white; font-size:11px; padding:4px 12px; border-radius:40px; text-transform:uppercase; margin-left:10px;">V<?php echo esc_html( GATETOUCH_VERSION ); ?></span></h1>
                <p><?php esc_html_e( 'Fine-tune how GT SEO/GEO/AEO integrates with your WordPress dashboard.', 'gatetouch-ai-seo' ); ?></p>
            </div>
        </div>
        <div class="gatetouch-admin-header__right">
            <div style="display:flex; gap:10px;">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=gatetouch-settings&tab=files#tab-sitemap' ) ); ?>" class="gatetouch-btn gatetouch-btn--ghost" style="background:#fff; border:1px solid #e2e8f0; color:#475569;">
                    <span class="dashicons dashicons-networking" style="font-size:16px; margin-right:5px; width:auto; height:auto;"></span> <?php esc_html_e( 'Sitemap', 'gatetouch-ai-seo' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=gatetouch-settings&tab=files#tab-llms' ) ); ?>" class="gatetouch-btn gatetouch-btn--ghost" style="background:#fff; border:1px solid #e2e8f0; color:#475569;">
                    <span class="dashicons dashicons-shield" style="font-size:16px; margin-right:5px; width:auto; height:auto;"></span> <?php esc_html_e( 'LLMs.txt', 'gatetouch-ai-seo' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=gatetouch-settings&tab=files#tab-robots' ) ); ?>" class="gatetouch-btn gatetouch-btn--ghost" style="background:#fff; border:1px solid #e2e8f0; color:#475569;">
                    <span class="dashicons dashicons-admin-generic" style="font-size:16px; margin-right:5px; width:auto; height:auto;"></span> <?php esc_html_e( 'Robots.txt', 'gatetouch-ai-seo' ); ?>
                </a>
            </div>
        </div>
    </div>
    
    <form method="post" style="margin-top:30px;">
        <?php wp_nonce_field( 'gatetouch_save_advanced' ); ?>

        <!-- Global SEO Features -->
        <div class="gatetouch-card">
            <div class="gatetouch-card__header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:10px;"><path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"/></svg>
                <?php esc_html_e( 'Global SEO Features', 'gatetouch-ai-seo' ); ?>
            </div>
            <div class="gatetouch-card__body">
                <div class="gatetouch-setting-row">
                    <div class="gatetouch-setting-label">
                        <strong><?php esc_html_e( 'TruSEO Score & Content', 'gatetouch-ai-seo' ); ?></strong>
                        <p><?php esc_html_e( 'Enable the SEO score and analysis box in the post editor.', 'gatetouch-ai-seo' ); ?></p>
                    </div>
                    <div class="gatetouch-setting-control">
                        <?php GateTouch_Helpers::toggle( 'tru_seo_score', $adv['tru_seo_score'] === '1' ); ?>
                    </div>
                </div>

                <div class="gatetouch-setting-row">
                    <div class="gatetouch-setting-label">
                        <strong><?php esc_html_e( 'Headline Analyzer', 'gatetouch-ai-seo' ); ?></strong>
                        <p><?php esc_html_e( 'Enable our advanced tool to help you write irresistible headlines.', 'gatetouch-ai-seo' ); ?></p>
                    </div>
                    <div class="gatetouch-setting-control">
                        <?php GateTouch_Helpers::toggle( 'headline_analyzer', $adv['headline_analyzer'] === '1' ); ?>
                    </div>
                </div>

                <div class="gatetouch-setting-row">
                    <div class="gatetouch-setting-label">
                        <strong><?php esc_html_e( 'Post Type Columns', 'gatetouch-ai-seo' ); ?></strong>
                        <p><?php esc_html_e( 'Select which post types should show GT SEO/GEO/AEO columns in the list table.', 'gatetouch-ai-seo' ); ?></p>
                    </div>
                    <div class="gatetouch-setting-control">
                        <div style="display:grid; grid-template-columns: repeat(2, 1fr); gap:10px;">
                            <?php
                            $pts = get_post_types( [ 'public' => true ], 'objects' );
                            $selected_pts = get_option( 'gatetouch_post_type_columns', [ 'post', 'page' ] );
                            foreach ( $pts as $pt ) : ?>
                                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; padding:8px 12px; border:1px solid #f1f5f9; border-radius:8px; transition:all 0.2s;">
                                    <input type="checkbox" name="gatetouch_pts[]" value="<?php echo esc_attr( $pt->name ); ?>" <?php checked( in_array( $pt->name, $selected_pts ) ); ?>>
                                    <span style="font-size:14px; font-weight:500; color:#475569;"><?php echo esc_html( $pt->label ); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin UI Settings -->
        <div class="gatetouch-card">
            <div class="gatetouch-card__header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:10px;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                <?php esc_html_e( 'Admin UI Settings', 'gatetouch-ai-seo' ); ?>
            </div>
            <div class="gatetouch-card__body">
                <div class="gatetouch-setting-row">
                    <div class="gatetouch-setting-label">
                        <strong><?php esc_html_e( 'Admin Bar Menu', 'gatetouch-ai-seo' ); ?></strong>
                        <p><?php esc_html_e( 'Show GT SEO/GEO/AEO menu in the top WordPress admin bar.', 'gatetouch-ai-seo' ); ?></p>
                    </div>
                    <div class="gatetouch-setting-control">
                        <?php GateTouch_Helpers::toggle( 'admin_bar_menu', $adv['admin_bar_menu'] === '1' ); ?>
                    </div>
                </div>

                <div class="gatetouch-setting-row">
                    <div class="gatetouch-setting-label">
                        <strong><?php esc_html_e( 'Dashboard Widgets', 'gatetouch-ai-seo' ); ?></strong>
                        <p><?php esc_html_e( 'Show GT SEO/GEO/AEO summary widgets on the main WP Dashboard.', 'gatetouch-ai-seo' ); ?></p>
                    </div>
                    <div class="gatetouch-setting-control">
                        <?php GateTouch_Helpers::toggle( 'dashboard_widgets', $adv['dashboard_widgets'] === '1' ); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Privacy & Cleanup -->
        <div class="gatetouch-card">
            <div class="gatetouch-card__header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:10px;"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <?php esc_html_e( 'Privacy & Cleanup', 'gatetouch-ai-seo' ); ?>
            </div>
            <div class="gatetouch-card__body">
                <div class="gatetouch-setting-row">
                    <div class="gatetouch-setting-label">
                        <strong><?php esc_html_e( 'Usage Tracking', 'gatetouch-ai-seo' ); ?></strong>
                        <p><?php esc_html_e( 'Help us improve by sharing anonymous usage data.', 'gatetouch-ai-seo' ); ?></p>
                    </div>
                    <div class="gatetouch-setting-control">
                        <?php GateTouch_Helpers::toggle( 'usage_tracking', $adv['usage_tracking'] === '1' ); ?>
                    </div>
                </div>

                <div class="gatetouch-setting-row">
                    <div class="gatetouch-setting-label">
                        <strong><?php esc_html_e( 'Uninstall Cleanup', 'gatetouch-ai-seo' ); ?></strong>
                        <p><?php esc_html_e( 'Delete all GT SEO/GEO/AEO data and settings upon plugin deletion.', 'gatetouch-ai-seo' ); ?></p>
                    </div>
                    <div class="gatetouch-setting-control">
                        <?php GateTouch_Helpers::toggle( 'uninstall_cleanup', $adv['uninstall_cleanup'] === '1' ); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="gatetouch-form-footer">
            <input type="hidden" name="gatetouch_save_advanced" value="1" />
            <button type="submit" class="gatetouch-btn gatetouch-btn--primary" style="padding:15px 30px; font-size:16px; font-weight:700;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:10px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                <?php esc_html_e( 'Save Advanced Settings', 'gatetouch-ai-seo' ); ?>
            </button>
        </div>
    </form>
</div>
