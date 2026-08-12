<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files are included inside class methods, so render variables are local.

if ( isset( $_POST['gatetouch_save_automation'] ) && check_admin_referer( 'gatetouch_save_automation' ) ) {
    update_option( 'gatetouch_auto_linker',    isset( $_POST['gatetouch_auto_linker'] ) ? 'yes' : 'no' );
    update_option( 'gatetouch_smart_redirects', isset( $_POST['gatetouch_smart_redirects'] ) ? 'yes' : 'no' );
    update_option( 'gatetouch_auto_schema',    isset( $_POST['gatetouch_auto_schema'] ) ? 'yes' : 'no' );
    GateTouch_Helpers::notice( '✅ Automation settings saved successfully!', 'success' );
}

$auto_linker = get_option( 'gatetouch_auto_linker', 'no' );
$smart_redir = get_option( 'gatetouch_smart_redirects', 'no' );
$auto_schema = get_option( 'gatetouch_auto_schema', 'no' );

$has_key      = GateTouch_AI_Engine::has_api_key();
$lock_class   = ! $has_key ? 'gatetouch-locked-feature' : '';
$lock_tooltip = __( 'AI API Key Required. Click to configure.', 'gatetouch-ai-seo' );
$lock_url     = admin_url( 'admin.php?page=gatetouch-settings&tab=ai' );
?>
<div class="gatetouch-admin-wrap">

    <?php if ( ! $has_key ) : ?>
        <div class="gatetouch-notice gatetouch-notice--warn" style="background:#fff7ed; border-color:#fed7aa; color:#9a3412;">
            <strong><?php esc_html_e( 'Ready to Automate?', 'gatetouch-ai-seo' ); ?></strong> Add your AI provider API key in AI Settings to enable these autonomous features.
            <a href="<?php echo esc_url( admin_url('admin.php?page=gatetouch-settings&tab=ai') ); ?>" class="gatetouch-btn gatetouch-btn--primary gatetouch-btn--sm" style="margin-left:15px;">Setup AI Now →</a>
        </div>
    <?php endif; ?>

    <div class="gatetouch-grid" style="display:block;">
        <div class="gatetouch-main-col" style="max-width:800px; margin:0 auto;">
            <form method="post">
                <?php wp_nonce_field( 'gatetouch_save_automation' ); ?>
                
                <!-- AUTO LINKER -->
                <div class="gatetouch-card gatetouch-card--premium">
                    <div class="gatetouch-card__header">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <span style="display:flex;align-items:center;gap:8px;"><?php echo wp_kses( GateTouch_Helpers::icon( 'link', 16 ), GateTouch_Helpers::svg_kses_allowed() ); ?> Auto-Internal Linker</span>
                        </div>
                        <div class="<?php echo esc_attr( $lock_class ); ?>" <?php if ( ! $has_key ) : ?>data-tooltip="<?php echo esc_attr( $lock_tooltip ); ?>" onclick="window.location.href='<?php echo esc_js( esc_url_raw( $lock_url ) ); ?>';"<?php endif; ?>>
                            <?php GateTouch_Helpers::toggle( 'gatetouch_auto_linker', ( $has_key && $auto_linker === 'yes' ) ? '1' : '' ); ?>
                        </div>
                    </div>
                    <div class="gatetouch-card__body">
                        <p>Automatically analyzes the "entities" in your new posts and inserts 2-3 links to your older relevant content. Improves "link juice" distribution without manual editing.</p>
                        <ul class="gatetouch-feature-list">
                            <li style="display:flex;align-items:center;gap:7px;"><?php echo wp_kses( GateTouch_Helpers::icon( 'sparkles', 14 ), GateTouch_Helpers::svg_kses_allowed() ); ?> AI-driven semantic entity extraction</li>
                            <li style="display:flex;align-items:center;gap:7px;"><?php echo wp_kses( GateTouch_Helpers::icon( 'shield-check', 14 ), GateTouch_Helpers::svg_kses_allowed() ); ?> Safety first: Never over-links (max 3 per post)</li>
                            <li style="display:flex;align-items:center;gap:7px;"><?php echo wp_kses( GateTouch_Helpers::icon( 'refresh', 14 ), GateTouch_Helpers::svg_kses_allowed() ); ?> Scans existing site index for best matches</li>
                        </ul>
                    </div>
                </div>

                <!-- SMART REDIRECTS -->
                <div class="gatetouch-card gatetouch-card--premium">
                    <div class="gatetouch-card__header">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <span>🚦 Smart AI Redirects</span>
                        </div>
                        <div class="<?php echo esc_attr( $lock_class ); ?>" <?php if ( ! $has_key ) : ?>data-tooltip="<?php echo esc_attr( $lock_tooltip ); ?>" onclick="window.location.href='<?php echo esc_js( esc_url_raw( $lock_url ) ); ?>';"<?php endif; ?>>
                            <?php GateTouch_Helpers::toggle( 'gatetouch_smart_redirects', ( $has_key && $smart_redir === 'yes' ) ? '1' : '' ); ?>
                        </div>
                    </div>
                    <div class="gatetouch-card__body">
                        <p><?php esc_html_e( 'When you delete a post, GT SEO/GEO/AEO analyzes the deleted content and automatically redirects the URL to the most semantically similar live page on your site.', 'gatetouch-ai-seo' ); ?></p>
                        <div class="gatetouch-alert gatetouch-alert--info" style="margin-top:15px;">
                            <strong>Semantic Matching:</strong> Uses AI embeddings to ensure users and Google find the next best thing instead of a 404 page.
                        </div>
                    </div>
                </div>

                <!-- AUTO SCHEMA -->
                <div class="gatetouch-card gatetouch-card--premium">
                    <div class="gatetouch-card__header">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <span style="display:flex;align-items:center;gap:8px;"><?php echo wp_kses( GateTouch_Helpers::icon( 'tag', 16 ), GateTouch_Helpers::svg_kses_allowed() ); ?> Zero-Config Auto-Schema</span>
                        </div>
                        <div class="<?php echo esc_attr( $lock_class ); ?>" <?php if ( ! $has_key ) : ?>data-tooltip="<?php echo esc_attr( $lock_tooltip ); ?>" onclick="window.location.href='<?php echo esc_js( esc_url_raw( $lock_url ) ); ?>';"<?php endif; ?>>
                            <?php GateTouch_Helpers::toggle( 'gatetouch_auto_schema', ( $has_key && $auto_schema === 'yes' ) ? '1' : '' ); ?>
                        </div>
                    </div>
                    <div class="gatetouch-card__body">
                        <p><?php esc_html_e( 'Automatically detects if a page is a Recipe, Product, FAQ, or HowTo based on the actual content and injects the JSON-LD markup instantly.', 'gatetouch-ai-seo' ); ?></p>
                    </div>
                </div>

                <div class="gatetouch-form-footer <?php echo esc_attr( $lock_class ); ?>" <?php if ( ! $has_key ) : ?>data-tooltip="<?php echo esc_attr( $lock_tooltip ); ?>" onclick="window.location.href='<?php echo esc_js( esc_url_raw( $lock_url ) ); ?>';"<?php endif; ?>>
                    <input type="hidden" name="gatetouch_save_automation" value="1" />
                    <button type="submit" class="gatetouch-btn gatetouch-btn--primary gatetouch-btn--lg" <?php disabled( ! $has_key ); ?>>Update Automation Center</button>
                </div>
            </form>
        </div>
    </div>
</div>
