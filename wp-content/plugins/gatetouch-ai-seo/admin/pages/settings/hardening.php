<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files are included inside class methods, so render variables are local.

if ( isset( $_POST['gatetouch_save_hardening'] ) && check_admin_referer( 'gatetouch_save_hardening' ) ) {
    update_option( 'gatetouch_security_settings', [
        'remove_wp_version'   => isset( $_POST['remove_wp_version'] ) ? '1' : '0',
        'remove_rsd_link'     => isset( $_POST['remove_rsd_link'] ) ? '1' : '0',
        'remove_wlw_manifest' => isset( $_POST['remove_wlw_manifest'] ) ? '1' : '0',
        'disable_xmlrpc'      => isset( $_POST['disable_xmlrpc'] ) ? '1' : '0',
        'security_headers'    => isset( $_POST['security_headers'] ) ? '1' : '0',
    ] );

    update_option( 'gatetouch_general_settings', [
        'disable_emojis'           => isset( $_POST['disable_emojis'] ) ? '1' : '0',
        'disable_embeds'           => isset( $_POST['disable_embeds'] ) ? '1' : '0',
        'clean_head'               => isset( $_POST['clean_head'] ) ? '1' : '0',
        'remove_shortlinks'        => isset( $_POST['remove_shortlinks'] ) ? '1' : '0',
        'remove_rest_links'        => isset( $_POST['remove_rest_links'] ) ? '1' : '0',
        'remove_unnecessary_feeds' => isset( $_POST['remove_unnecessary_feeds'] ) ? '1' : '0',
    ] );

    GateTouch_Helpers::notice( '✅ Hardening & Performance settings saved!', 'success' );
}

$sec = get_option( 'gatetouch_security_settings', [] );
$gen = get_option( 'gatetouch_general_settings', [] );
?>
<div class="gatetouch-settings-group">
    <form method="post">
        <?php wp_nonce_field( 'gatetouch_save_hardening' ); ?>

        <div class="gatetouch-card">
            <div class="gatetouch-card__header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Security Hardening
            </div>
            <div class="gatetouch-settings-rows">
                <div class="gatetouch-setting-row">
                    <div class="gatetouch-setting-label">
                        <strong><?php esc_html_e( 'Disable XML-RPC', 'gatetouch-ai-seo' ); ?></strong>
                        <p>Prevents brute force attacks via xmlrpc.php.</p>
                    </div>
                    <div class="gatetouch-setting-control">
                        <?php GateTouch_Helpers::toggle( 'disable_xmlrpc', ( $sec['disable_xmlrpc'] ?? '1' ) === '1' ? 'yes' : 'no' ); ?>
                    </div>
                </div>
                <div class="gatetouch-setting-row">
                    <div class="gatetouch-setting-label">
                        <strong><?php esc_html_e( 'Security Headers', 'gatetouch-ai-seo' ); ?></strong>
                        <p><?php esc_html_e( 'Adds nosniff, SAMEORIGIN, and Referrer-Policy headers.', 'gatetouch-ai-seo' ); ?></p>
                    </div>
                    <div class="gatetouch-setting-control">
                        <?php GateTouch_Helpers::toggle( 'security_headers', ( $sec['security_headers'] ?? '1' ) === '1' ? 'yes' : 'no' ); ?>
                    </div>
                </div>
                <div class="gatetouch-setting-row">
                    <div class="gatetouch-setting-label">
                        <strong>Clean wp_head</strong>
                        <p><?php esc_html_e( 'Removes RSD, WLW, Shortlinks, and REST API links from head.', 'gatetouch-ai-seo' ); ?></p>
                    </div>
                    <div class="gatetouch-setting-control">
                        <?php GateTouch_Helpers::toggle( 'clean_head', ( $gen['clean_head'] ?? '1' ) === '1' ? 'yes' : 'no' ); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="gatetouch-card">
            <div class="gatetouch-card__header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                Crawl Optimization
            </div>
            <div class="gatetouch-settings-rows">
                <div class="gatetouch-setting-row">
                    <div class="gatetouch-setting-label">
                        <strong><?php esc_html_e( 'Disable Emojis', 'gatetouch-ai-seo' ); ?></strong>
                        <p><?php esc_html_e( 'Removes extra JS/CSS for emojis, improving load time.', 'gatetouch-ai-seo' ); ?></p>
                    </div>
                    <div class="gatetouch-setting-control">
                        <?php GateTouch_Helpers::toggle( 'disable_emojis', ( $gen['disable_emojis'] ?? '1' ) === '1' ? 'yes' : 'no' ); ?>
                    </div>
                </div>
                <div class="gatetouch-setting-row">
                    <div class="gatetouch-setting-label">
                        <strong><?php esc_html_e( 'Disable Embed Scripts', 'gatetouch-ai-seo' ); ?></strong>
                        <p><?php esc_html_e( 'Prevents oEmbed scripts from loading on every page.', 'gatetouch-ai-seo' ); ?></p>
                    </div>
                    <div class="gatetouch-setting-control">
                        <?php GateTouch_Helpers::toggle( 'disable_embeds', ( $gen['disable_embeds'] ?? '1' ) === '1' ? 'yes' : 'no' ); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="gatetouch-form-footer">
            <input type="hidden" name="gatetouch_save_hardening" value="1" />
            <button type="submit" class="gatetouch-btn gatetouch-btn--primary"><?php esc_html_e( 'Save Hardening Settings', 'gatetouch-ai-seo' ); ?></button>
        </div>
    </form>
</div>
