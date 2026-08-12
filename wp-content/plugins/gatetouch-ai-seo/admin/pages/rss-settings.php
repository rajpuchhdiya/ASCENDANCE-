<?php
/**
 * GateTouch — RSS Content Settings Page
 */
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files are included inside class methods, so render variables are local.

if ( isset( $_POST['gatetouch_save_rss'] ) && check_admin_referer( 'gatetouch_save_rss' ) ) {
    $rss_settings = [
        'before_content' => wp_kses_post( wp_unslash( $_POST['rss_before'] ?? '' ) ),
        'after_content'  => wp_kses_post( wp_unslash( $_POST['rss_after'] ?? '' ) ),
    ];
    update_option( 'gatetouch_rss_settings', $rss_settings );
    GateTouch_Helpers::notice( __( '✅ RSS Content settings saved!', 'gatetouch-ai-seo' ), 'success' );
}

$rss = get_option( 'gatetouch_rss_settings', [
    'before_content' => '',
    'after_content'  => '',
] );
?>
<div class="gatetouch-settings-group">
    <?php
    // Rendered as a section of the Advanced tab, so it gets a section heading —
    // not page_header(), which paints the full masthead with logo, version badge
    // and toolbar and reads as the start of a new page.
    ?>
    <div class="gatetouch-section-heading">
        <div class="gatetouch-setting-icon">
            <?php echo wp_kses( GateTouch_Helpers::icon( 'file-text', 20 ), GateTouch_Helpers::svg_kses_allowed() ); ?>
        </div>
        <div class="gatetouch-section-heading__text">
            <strong><?php esc_html_e( 'RSS Content', 'gatetouch-ai-seo' ); ?></strong>
            <p><?php esc_html_e( 'Add custom content before or after each post in your RSS feed — the standard defence against scraper sites republishing you.', 'gatetouch-ai-seo' ); ?></p>
        </div>
    </div>

    <form method="post">
        <?php wp_nonce_field( 'gatetouch_save_rss' ); ?>

        <div class="gatetouch-card">
            <div class="gatetouch-card__header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 11a9 9 0 0 1 9 9M4 4a16 16 0 0 1 16 16"/><circle cx="5" cy="19" r="1"/></svg>
                <?php esc_html_e( 'RSS Feed Settings', 'gatetouch-ai-seo' ); ?>
            </div>
            <div class="gatetouch-card__body" style="padding:0;">
                <div class="gatetouch-settings-rows">

                    <div class="gatetouch-setting-row">
                        <div class="gatetouch-setting-label">
                            <strong><?php esc_html_e( 'RSS Before Content', 'gatetouch-ai-seo' ); ?></strong>
                            <p><?php esc_html_e( 'Content to show before the post in RSS feeds.', 'gatetouch-ai-seo' ); ?></p>
                        </div>
                        <div class="gatetouch-setting-control">
                            <textarea name="rss_before" rows="5" class="gatetouch-textarea" placeholder="&lt;p&gt;This post first appeared on #site_link#.&lt;/p&gt;"><?php echo esc_textarea( $rss['before_content'] ); ?></textarea>
                            <p class="gatetouch-hint">
                                <?php esc_html_e( 'Available tags:', 'gatetouch-ai-seo' ); ?>
                                <code>#post_link#</code> <code>#site_link#</code> <code>#author_link#</code>
                            </p>
                        </div>
                    </div>

                    <div class="gatetouch-setting-row">
                        <div class="gatetouch-setting-label">
                            <strong><?php esc_html_e( 'RSS After Content', 'gatetouch-ai-seo' ); ?></strong>
                            <p><?php esc_html_e( 'Content to show after the post in RSS feeds.', 'gatetouch-ai-seo' ); ?></p>
                        </div>
                        <div class="gatetouch-setting-control">
                            <textarea name="rss_after" rows="5" class="gatetouch-textarea" placeholder="&lt;p&gt;The post #post_link# first appeared on #site_link#.&lt;/p&gt;"><?php echo esc_textarea( $rss['after_content'] ); ?></textarea>
                            <p class="gatetouch-hint">
                                <?php esc_html_e( 'Appended to every item in your feed. A link back to the original is what makes scraped copies point to you.', 'gatetouch-ai-seo' ); ?>
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="gatetouch-form-footer">
            <input type="hidden" name="gatetouch_save_rss" value="1" />
            <button type="submit" class="gatetouch-btn gatetouch-btn--primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                <?php esc_html_e( 'Save RSS Settings', 'gatetouch-ai-seo' ); ?>
            </button>
        </div>
    </form>
</div>
