<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files are included inside class methods, so render variables are local.

if ( isset( $_POST['gatetouch_save_advanced'] ) && check_admin_referer( 'gatetouch_save_advanced' ) ) {
    update_option( 'gatetouch_ai_model', sanitize_text_field( wp_unslash( $_POST['ai_model'] ?? 'gpt-4o' ) ) );
    
    GateTouch_Helpers::notice( '✅ Advanced settings saved!', 'success' );
}

$model = get_option( 'gatetouch_ai_model', 'gpt-4o' );
?>
<div class="gatetouch-settings-group">
    <form method="post">
        <?php wp_nonce_field( 'gatetouch_save_advanced' ); ?>

        <div class="gatetouch-card">
            <div class="gatetouch-card__header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                Expert Configuration
            </div>
            <div class="gatetouch-settings-rows">
                <div class="gatetouch-setting-row">
                    <div class="gatetouch-setting-label">
                        <strong><?php esc_html_e( 'AI Engine Model', 'gatetouch-ai-seo' ); ?></strong>
                        <p><?php esc_html_e( 'Select which model to use for SEO generation.', 'gatetouch-ai-seo' ); ?></p>
                    </div>
                    <div class="gatetouch-setting-control">
                        <select name="ai_model" class="gatetouch-select">
                            <option value="gpt-4o" <?php selected($model, 'gpt-4o'); ?>>GPT-4o (Recommended)</option>
                            <option value="gpt-4-turbo" <?php selected($model, 'gpt-4-turbo'); ?>>GPT-4 Turbo</option>
                            <option value="gpt-3.5-turbo" <?php selected($model, 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="gatetouch-card">
            <div class="gatetouch-card__header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                Developer Tools
            </div>
            <div class="gatetouch-settings-rows">
                <div class="gatetouch-setting-row">
                    <div class="gatetouch-setting-label">
                        <strong><?php esc_html_e( 'Export SEO Meta', 'gatetouch-ai-seo' ); ?></strong>
                        <p><?php esc_html_e( 'Download all post metadata as a JSON file.', 'gatetouch-ai-seo' ); ?></p>
                    </div>
                    <div class="gatetouch-setting-control">
                        <a class="gatetouch-btn" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-ajax.php?action=gatetouch_export_meta' ), 'gatetouch_ajax', 'nonce' ) ); ?>"><?php esc_html_e( 'Export JSON', 'gatetouch-ai-seo' ); ?></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="gatetouch-card">
            <div class="gatetouch-card__header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                System Debug Logs
            </div>
            <div class="gatetouch-card__body" style="padding:24px;">
                <p style="font-size:13px; color:#64748b; margin-bottom:15px;"><?php esc_html_e( 'Monitor AI API connections, bulk processing errors, and system health in real-time.', 'gatetouch-ai-seo' ); ?></p>
                <div style="background:#0f172a; color:#f8fafc; padding:15px; border-radius:8px; font-family:monospace; font-size:12px; height:200px; overflow-y:auto; margin-bottom:15px; line-height:1.6;" id="riq-log-viewer">
                    Loading logs...
                </div>
                <div style="display:flex; gap:10px;">
                    <button type="button" class="gatetouch-btn" id="riq-refresh-logs"><?php esc_html_e( 'Refresh Logs', 'gatetouch-ai-seo' ); ?></button>
                    <button type="button" class="gatetouch-btn gatetouch-btn--secondary" id="riq-clear-logs"><?php esc_html_e( 'Clear History', 'gatetouch-ai-seo' ); ?></button>
                </div>
            </div>
        </div>


        <div class="gatetouch-form-footer">
            <input type="hidden" name="gatetouch_save_advanced" value="1" />
            <button type="submit" class="gatetouch-btn gatetouch-btn--primary"><?php esc_html_e( 'Save Expert Settings', 'gatetouch-ai-seo' ); ?></button>
        </div>
    </form>
</div>

<?php
// RSS content protection — a complete panel that previously shipped with no
// route to it. Rendered here so the advertised feature is actually reachable.
include GATETOUCH_PATH . 'admin/pages/rss-settings.php';
