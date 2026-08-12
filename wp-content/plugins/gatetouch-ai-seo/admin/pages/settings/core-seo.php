<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files are included inside class methods, so render variables are local.

if ( isset( $_POST['gatetouch_save_core'] ) && check_admin_referer( 'gatetouch_save_core' ) ) {
    update_option( 'gatetouch_title_separator', sanitize_text_field( wp_unslash( $_POST['title_separator'] ?? '|' ) ) );
    $existing_bc = get_option( 'gatetouch_breadcrumb_settings', [] );
    $existing_bc = is_array( $existing_bc ) ? $existing_bc : [];
    $placement   = sanitize_key( wp_unslash( $_POST['bc_placement'] ?? ( $existing_bc['placement'] ?? 'content' ) ) );
    if ( ! in_array( $placement, [ 'manual', 'content', 'body_open' ], true ) ) {
        $placement = 'content';
    }

    $bc_settings = [
        'enabled'    => isset( $_POST['bc_enabled'] ) ? '1' : '0',
        'placement'  => $placement,
        'separator'  => sanitize_text_field( wp_unslash( $_POST['bc_separator'] ?? '›' ) ),
        'home_label' => sanitize_text_field( wp_unslash( $_POST['bc_home_label'] ?? 'Home' ) ),
    ];
    update_option( 'gatetouch_breadcrumb_settings', array_merge( $existing_bc, $bc_settings ) );

    GateTouch_Helpers::notice( '✅ Core SEO settings saved!', 'success' );
}

$sep = get_option( 'gatetouch_title_separator', '|' );
$bc  = get_option( 'gatetouch_breadcrumb_settings', [] );
$bc  = wp_parse_args( is_array( $bc ) ? $bc : [], GateTouch_Breadcrumbs::defaults() );
?>
<div class="gatetouch-settings-group">
    <form method="post">
        <?php wp_nonce_field( 'gatetouch_save_core' ); ?>

        <div class="gatetouch-card">
            <div class="gatetouch-card__header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Search Appearance
            </div>
            <div class="gatetouch-settings-rows">
                <div class="gatetouch-setting-row" style="display:block;">
                    <div class="gatetouch-setting-label" style="margin-bottom:20px;">
                        <strong style="font-size:16px; color:var(--riq-text);"><?php esc_html_e( 'Title Separator', 'gatetouch-ai-seo' ); ?></strong>
                        <p style="font-size:14px; color:var(--riq-text-light);"><?php esc_html_e( 'This character will appear between your post title and site name in search results.', 'gatetouch-ai-seo' ); ?></p>
                    </div>
                    
                    <div class="gatetouch-setting-control" style="flex-direction:column; align-items:flex-start; justify-content:flex-start;">
                        <div class="gatetouch-visual-picker" id="gatetouch-title-sep-picker" style="margin-bottom:24px;">
                            <input type="hidden" name="title_separator" id="title_separator_input" value="<?php echo esc_attr($sep); ?>">
                            <?php foreach ( [ '|', '-', '•', '·', '—', '»', '/', '›', '∗' ] as $s ) : ?>
                                <div class="gatetouch-visual-picker__item <?php echo $sep === $s ? 'is-active' : ''; ?>" data-sep="<?php echo esc_attr($s); ?>">
                                    <?php echo esc_html($s); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="gatetouch-preview-box" style="width:100%; max-width:500px; margin-top:0;">
                            <div class="gatetouch-preview-box__label"><?php esc_html_e( 'Google Search Preview', 'gatetouch-ai-seo' ); ?></div>
                            <div class="gatetouch-preview-box__content" style="font-size:15px;">
                                My Awesome Post <span class="gatetouch-preview-box__sep" id="gatetouch-sep-preview"><?php echo esc_html($sep); ?></span> Site Name
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="gatetouch-card">
            <div class="gatetouch-card__header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                Breadcrumb Strategy
            </div>
            <div class="gatetouch-settings-rows">
                <div class="gatetouch-setting-row">
                    <div class="gatetouch-setting-label">
                        <strong><?php esc_html_e( 'Enable Breadcrumbs', 'gatetouch-ai-seo' ); ?></strong>
                        <p><?php esc_html_e( 'Adds JSON-LD BreadcrumbList schema to your pages.', 'gatetouch-ai-seo' ); ?></p>
                    </div>
                    <div class="gatetouch-setting-control">
                        <?php GateTouch_Helpers::toggle( 'bc_enabled', ( $bc['enabled'] ?? '1' ) === '1' ? 'yes' : 'no' ); ?>
                    </div>
                </div>
                <div class="gatetouch-setting-row">
                    <div class="gatetouch-setting-label">
                        <strong><?php esc_html_e( 'Display Placement', 'gatetouch-ai-seo' ); ?></strong>
                        <p><?php esc_html_e( 'Choose automatic before-content output or manual shortcode placement.', 'gatetouch-ai-seo' ); ?></p>
                    </div>
                    <div class="gatetouch-setting-control">
                        <select name="bc_placement" class="gatetouch-select">
                            <option value="content" <?php selected( $bc['placement'], 'content' ); ?>><?php esc_html_e( 'Before main content', 'gatetouch-ai-seo' ); ?></option>
                            <option value="manual" <?php selected( $bc['placement'], 'manual' ); ?>><?php esc_html_e( 'Manual shortcode only', 'gatetouch-ai-seo' ); ?></option>
                            <option value="body_open" <?php selected( $bc['placement'], 'body_open' ); ?>><?php esc_html_e( 'Theme wp_body_open hook', 'gatetouch-ai-seo' ); ?></option>
                        </select>
                        <p class="gatetouch-hint"><code>[gatetouch_breadcrumbs]</code></p>
                    </div>
                </div>
                <div class="gatetouch-setting-row">
                    <div class="gatetouch-setting-label">
                        <strong><?php esc_html_e( 'Separator Symbol', 'gatetouch-ai-seo' ); ?></strong>
                    </div>
                    <div class="gatetouch-setting-control">
                        <input type="text" name="bc_separator" value="<?php echo esc_attr( $bc['separator'] ?? '›' ); ?>" class="gatetouch-input-sm" />
                    </div>
                </div>
                <div class="gatetouch-setting-row">
                    <div class="gatetouch-setting-label">
                        <strong><?php esc_html_e( 'Home Label', 'gatetouch-ai-seo' ); ?></strong>
                    </div>
                    <div class="gatetouch-setting-control">
                        <input type="text" name="bc_home_label" value="<?php echo esc_attr( $bc['home_label'] ?? 'Home' ); ?>" class="gatetouch-input-full" />
                    </div>
                </div>
            </div>
        </div>

        <div class="gatetouch-form-footer">
            <input type="hidden" name="gatetouch_save_core" value="1" />
            <button type="submit" class="gatetouch-btn gatetouch-btn--primary"><?php esc_html_e( 'Save Core Settings', 'gatetouch-ai-seo' ); ?></button>
        </div>
    </form>
</div>
