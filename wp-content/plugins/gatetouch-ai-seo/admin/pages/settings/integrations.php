<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files are included inside class methods, so render variables are local.

if ( isset( $_POST['gatetouch_save_integrations'] ) && check_admin_referer( 'gatetouch_save_integrations' ) ) {
    $integrations = [
        'gsc_code'        => isset( $_POST['gsc_code'] ) ? sanitize_text_field( wp_unslash( $_POST['gsc_code'] ) ) : '',
        'bing_code'       => isset( $_POST['bing_code'] ) ? sanitize_text_field( wp_unslash( $_POST['bing_code'] ) ) : '',
        'pinterest_code'  => isset( $_POST['pinterest_code'] ) ? sanitize_text_field( wp_unslash( $_POST['pinterest_code'] ) ) : '',
        'yandex_code'     => isset( $_POST['yandex_code'] ) ? sanitize_text_field( wp_unslash( $_POST['yandex_code'] ) ) : '',
        'baidu_code'      => isset( $_POST['baidu_code'] ) ? sanitize_text_field( wp_unslash( $_POST['baidu_code'] ) ) : '',
        'facebook_verify' => isset( $_POST['facebook_verify'] ) ? sanitize_text_field( wp_unslash( $_POST['facebook_verify'] ) ) : '',
        
        'ga4_id'         => isset( $_POST['ga4_id'] ) ? sanitize_text_field( wp_unslash( $_POST['ga4_id'] ) ) : '',
        'gtm_id'         => isset( $_POST['gtm_id'] ) ? sanitize_text_field( wp_unslash( $_POST['gtm_id'] ) ) : '',
        'meta_pixel'     => isset( $_POST['meta_pixel'] ) ? sanitize_text_field( wp_unslash( $_POST['meta_pixel'] ) ) : '',
        'bing_uet_id'    => isset( $_POST['bing_uet_id'] ) ? sanitize_text_field( wp_unslash( $_POST['bing_uet_id'] ) ) : '',
        'clarity_id'     => isset( $_POST['clarity_id'] ) ? sanitize_text_field( wp_unslash( $_POST['clarity_id'] ) ) : '',
        'linkedin_id'    => isset( $_POST['linkedin_id'] ) ? sanitize_text_field( wp_unslash( $_POST['linkedin_id'] ) ) : '',
        'tiktok_id'      => isset( $_POST['tiktok_id'] ) ? sanitize_text_field( wp_unslash( $_POST['tiktok_id'] ) ) : '',

        'header_scripts' => isset( $_POST['header_scripts'] ) ? wp_kses_post( wp_unslash( $_POST['header_scripts'] ) ) : '',
        'body_scripts'   => isset( $_POST['body_scripts'] ) ? wp_kses_post( wp_unslash( $_POST['body_scripts'] ) ) : '',
        'footer_scripts' => isset( $_POST['footer_scripts'] ) ? wp_kses_post( wp_unslash( $_POST['footer_scripts'] ) ) : '',
        'schema_scripts' => isset( $_POST['schema_scripts'] ) ? wp_kses_post( wp_unslash( $_POST['schema_scripts'] ) ) : '',
    ];
    update_option( 'gatetouch_integrations', $integrations );

    // IndexNow lives in the webmaster option because that is where the sitemap
    // engine reads it from. Previously the only field that wrote it shipped in
    // an unreachable page, so the feature could never be switched on.
    if ( isset( $_POST['indexnow_key'] ) ) {
        $webmaster = get_option( 'gatetouch_webmaster_settings', [] );
        $webmaster = is_array( $webmaster ) ? $webmaster : [];
        $webmaster['indexnow_key'] = sanitize_text_field( wp_unslash( $_POST['indexnow_key'] ) );
        update_option( 'gatetouch_webmaster_settings', $webmaster );
    }

    GateTouch_Helpers::notice( __( '✅ Integrations architecture synchronized!', 'gatetouch-ai-seo' ), 'success' );
}

$opts           = get_option( 'gatetouch_integrations', [] );
$gt_webmaster   = get_option( 'gatetouch_webmaster_settings', [] );
$gt_indexnow    = is_array( $gt_webmaster ) ? ( $gt_webmaster['indexnow_key'] ?? '' ) : '';

if ( ! function_exists( 'gatetouch_integration_row' ) ) {
    function gatetouch_integration_row( $id, $label, $desc, $placeholder, $value, $guide_url, $platform_url, $example = '' ) {
        $is_connected = ! empty( $value );
        ?>
        <div class="gatetouch-card gatetouch-integration-card">
            <div class="gatetouch-card__body">
                <div class="gatetouch-platform-header">
                    <div class="gatetouch-platform-info">
                        <div class="gatetouch-platform-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/></svg>
                        </div>
                        <div class="gatetouch-platform-name"><?php echo esc_html( $label ); ?></div>
                    </div>
                    <div class="gatetouch-status-pill <?php echo esc_attr( $is_connected ? 'gatetouch-status--connected' : 'gatetouch-status--disconnected' ); ?>">
                        <?php echo $is_connected ? esc_html__( 'Connected', 'gatetouch-ai-seo' ) : esc_html__( 'Not Connected', 'gatetouch-ai-seo' ); ?>
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <input type="text" name="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $value ); ?>"
                           placeholder="<?php echo esc_attr( $placeholder ); ?>" class="gatetouch-input-full" />
                    <?php if ( $example ) : ?>
                    <div class="gatetouch-validation-hint">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        <?php esc_html_e( 'Example:', 'gatetouch-ai-seo' ); ?> <?php echo esc_html( $example ); ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="gatetouch-help-box" style="margin-top: 0; background: transparent; border: none; padding: 0;">
                    <div class="gatetouch-help-text" style="margin-bottom: 12px;"><?php echo esc_html( $desc ); ?></div>
                    <div class="gatetouch-help-actions">
                        <a href="<?php echo esc_url( $platform_url ); ?>" target="_blank" rel="noopener noreferrer" class="gatetouch-btn-platform">
                            <span class="dashicons dashicons-external"></span> <?php esc_html_e( 'Open Platform', 'gatetouch-ai-seo' ); ?>
                        </a>
                        <a href="<?php echo esc_url( $guide_url ); ?>" target="_blank" rel="noopener noreferrer" class="gatetouch-btn-platform">
                            <span class="dashicons dashicons-media-text"></span> <?php esc_html_e( 'Setup Guide', 'gatetouch-ai-seo' ); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
?>

<div class="gatetouch-settings-group">
    <form method="post">
        <?php wp_nonce_field( 'gatetouch_save_integrations' ); ?>

        <!-- Global AI Insight -->
        <div class="gatetouch-card" style="background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%); border: 1px solid rgba(99, 102, 241, 0.2);">
            <div class="gatetouch-card__body" style="display: flex; align-items: center; gap: 20px;">
                <div class="rk-icon-box rk-icon-box--lg rk-icon-box--ai"><?php echo wp_kses( GateTouch_Helpers::icon( 'robot', 24 ), GateTouch_Helpers::svg_kses_allowed() ); ?></div>
                <div>
                    <strong style="display: block; font-size: 16px; color: #1e293b; margin-bottom: 5px;"><?php esc_html_e( 'AI Architecture Insight', 'gatetouch-ai-seo' ); ?></strong>
                    <p style="margin: 0; font-size: 14px; color: #475569; line-height: 1.5;">
                        <?php esc_html_e( 'These integrations help AI crawlers like', 'gatetouch-ai-seo' ); ?> <strong><?php esc_html_e( 'GPTBot', 'gatetouch-ai-seo' ); ?></strong>, <strong><?php esc_html_e( 'Claude-Bot', 'gatetouch-ai-seo' ); ?></strong>, <?php esc_html_e( 'and', 'gatetouch-ai-seo' ); ?> <strong><?php esc_html_e( 'Perplexity', 'gatetouch-ai-seo' ); ?></strong> <?php esc_html_e( 'verify your site identity.', 'gatetouch-ai-seo' ); ?>
                        <?php esc_html_e( 'Connecting these platforms enables deep semantic entity mapping and improves your site discoverability in AI search results and generative answers.', 'gatetouch-ai-seo' ); ?>
                    </p>
                </div>
            </div>
        </div>

        <div style="margin: 40px 0 30px;">
            <h3 style="font-size:18px; font-weight:800; color:#1e293b; margin-bottom:10px;"><?php esc_html_e( 'Search Engine Verification', 'gatetouch-ai-seo' ); ?></h3>
            <p style="color:#64748b; font-size:14px;"><?php esc_html_e( 'Establish trust with major search engines and AI discovery platforms.', 'gatetouch-ai-seo' ); ?></p>
        </div>

        <?php
        gatetouch_integration_row(
            'gsc_code', 
            __( 'Google Search Console', 'gatetouch-ai-seo' ),
            __( 'Verify your website ownership with Google Search Console to enable indexing insights, SEO monitoring, and AI search visibility.', 'gatetouch-ai-seo' ),
            'google-site-verification=...',
            $opts['gsc_code'] ?? '',
            'https://support.google.com/webmasters/answer/9008080',
            'https://search.google.com/search-console',
            'google-site-verification=xxxxxxx'
        );

        gatetouch_integration_row(
            'bing_code', 
            __( 'Bing Webmaster', 'gatetouch-ai-seo' ),
            __( 'Improve your visibility in Bing and Microsoft Copilot AI search by verifying your domain.', 'gatetouch-ai-seo' ),
            __( 'Enter verification code...', 'gatetouch-ai-seo' ),
            $opts['bing_code'] ?? '',
            'https://www.bing.com/webmasters/help/add-and-verify-site-12184f8b',
            'https://www.bing.com/webmasters/',
            '8DE7XXXXXXX'
        );

        gatetouch_integration_row(
            'pinterest_code', 
            __( 'Pinterest Verification', 'gatetouch-ai-seo' ),
            __( 'Claim your website on Pinterest to see analytics for your pins and enable rich pins.', 'gatetouch-ai-seo' ),
            __( 'Enter meta tag content...', 'gatetouch-ai-seo' ),
            $opts['pinterest_code'] ?? '',
            'https://help.pinterest.com/en/business/article/claim-your-website',
            'https://www.pinterest.com/settings/claim',
            'p:domain_verify=xxxxxxx'
        );

        gatetouch_integration_row(
            'indexnow_key',
            __( 'IndexNow', 'gatetouch-ai-seo' ),
            __( 'Pushes new and updated URLs straight to Bing, Yandex, Seznam and Naver the moment you publish, instead of waiting to be crawled. Bing\'s index is what feeds Microsoft Copilot, so this is the fastest route into that AI surface. Generate any random 32-character key — the plugin serves it for verification automatically.', 'gatetouch-ai-seo' ),
            __( 'Enter or paste a 32-character key...', 'gatetouch-ai-seo' ),
            $gt_indexnow,
            'https://www.indexnow.org/documentation',
            'https://www.bing.com/indexnow',
            'a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6'
        );
        ?>

        <div style="margin:40px 0 30px;">
            <h3 style="font-size:18px; font-weight:800; color:#1e293b; margin-bottom:10px;"><?php esc_html_e( 'Analytics & Pixels', 'gatetouch-ai-seo' ); ?></h3>
            <p style="color:#64748b; font-size:14px;"><?php esc_html_e( 'Track user behavior and optimize for Generative Engine Conversion (GEC).', 'gatetouch-ai-seo' ); ?></p>
        </div>

        <?php
        gatetouch_integration_row(
            'ga4_id', 
            __( 'Google Analytics (GA4)', 'gatetouch-ai-seo' ),
            __( 'Connect your site to Google Analytics 4 to track modern user engagement metrics.', 'gatetouch-ai-seo' ),
            'G-XXXXXXXXXX',
            $opts['ga4_id'] ?? '',
            'https://support.google.com/analytics/answer/9304153',
            'https://analytics.google.com/',
            'G-ABC123XYZ'
        );

        gatetouch_integration_row(
            'gtm_id', 
            __( 'Google Tag Manager', 'gatetouch-ai-seo' ),
            __( 'Manage all your website tags without editing code using Google Tag Manager.', 'gatetouch-ai-seo' ),
            'GTM-XXXXXXX',
            $opts['gtm_id'] ?? '',
            'https://support.google.com/tagmanager/answer/6103696',
            'https://tagmanager.google.com/',
            'GTM-XXXXXXX'
        );

        gatetouch_integration_row(
            'meta_pixel', 
            __( 'Meta Pixel (Facebook)', 'gatetouch-ai-seo' ),
            __( 'Track conversions and build retargeting audiences for Facebook and Instagram.', 'gatetouch-ai-seo' ),
            __( 'Pixel ID', 'gatetouch-ai-seo' ),
            $opts['meta_pixel'] ?? '',
            'https://www.facebook.com/business/help/952192354843755',
            'https://adsmanager.facebook.com/',
            '123456789012345'
        );

        gatetouch_integration_row(
            'bing_uet_id', 
            __( 'Bing Analytics (UET)', 'gatetouch-ai-seo' ),
            __( 'Track what happens after users click on your ad with Bing Universal Event Tracking.', 'gatetouch-ai-seo' ),
            __( 'UET Tag ID', 'gatetouch-ai-seo' ),
            $opts['bing_uet_id'] ?? '',
            'https://help.ads.microsoft.com/#apex/ads/en/56910/2',
            'https://ads.microsoft.com/',
            '12345678'
        );

        gatetouch_integration_row(
            'clarity_id', 
            __( 'Microsoft Clarity', 'gatetouch-ai-seo' ),
            __( 'Get free heatmaps and session recordings to understand how people use your site.', 'gatetouch-ai-seo' ),
            __( 'Clarity Project ID', 'gatetouch-ai-seo' ),
            $opts['clarity_id'] ?? '',
            'https://learn.microsoft.com/en-us/clarity/setup-and-installation/clarity-setup',
            'https://clarity.microsoft.com/',
            'abcdefghij'
        );
        ?>

        <div style="margin:40px 0 30px;">
            <h3 style="font-size:18px; font-weight:800; color:#1e293b; margin-bottom:10px;"><?php esc_html_e( 'Advanced Developer Tools', 'gatetouch-ai-seo' ); ?></h3>
            <p style="color:#64748b; font-size:14px;"><?php esc_html_e( 'Additional developer-only integrations and data hooks.', 'gatetouch-ai-seo' ); ?></p>
        </div>

        <?php
        gatetouch_integration_row(
            'linkedin_id', 
            __( 'LinkedIn Insight Tag', 'gatetouch-ai-seo' ),
            __( 'Measure your LinkedIn ad performance and professional demographics.', 'gatetouch-ai-seo' ),
            __( 'Partner ID', 'gatetouch-ai-seo' ),
            $opts['linkedin_id'] ?? '',
            'https://www.linkedin.com/help/lms/answer/a422021',
            'https://www.linkedin.com/campaignmanager/',
            '1234567'
        );

        gatetouch_integration_row(
            'tiktok_id', 
            __( 'TikTok Pixel', 'gatetouch-ai-seo' ),
            __( 'Measure ad performance and optimize your TikTok marketing campaigns.', 'gatetouch-ai-seo' ),
            __( 'Pixel Code', 'gatetouch-ai-seo' ),
            $opts['tiktok_id'] ?? '',
            'https://ads.tiktok.com/help/article/getting-started-pixel',
            'https://ads.tiktok.com/',
            'CBTXXXXXXXXXXXXXXXX'
        );
        ?>

        <div style="margin:40px 0 30px;">
            <h3 style="font-size:18px; font-weight:800; color:#1e293b; margin-bottom:10px;"><?php esc_html_e( 'Advanced Verification Markup', 'gatetouch-ai-seo' ); ?></h3>
            <p style="color:#64748b; font-size:14px;"><?php esc_html_e( 'Add sanitized HTML or meta verification tags to specific parts of your website structure.', 'gatetouch-ai-seo' ); ?></p>
        </div>

        <div class="gatetouch-card">
            <div class="gatetouch-card__body">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:30px;">
                    <div>
                        <label class="gatetouch-bulk-label">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                            <?php esc_html_e( 'Header Markup', 'gatetouch-ai-seo' ); ?>
                        </label>
                        <p style="font-size:12px; color:#64748b; margin-bottom:10px;"><?php esc_html_e( 'Printed before </head> after WordPress sanitization.', 'gatetouch-ai-seo' ); ?></p>
                        <textarea name="header_scripts" rows="6" class="gatetouch-input-full" style="font-family:monospace; font-size:12px; background:#fafafa;"><?php echo esc_textarea( $opts['header_scripts'] ?? '' ); ?></textarea>
                    </div>
                    <div>
                        <label class="gatetouch-bulk-label">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                            <?php esc_html_e( 'Footer Markup', 'gatetouch-ai-seo' ); ?>
                        </label>
                        <p style="font-size:12px; color:#64748b; margin-bottom:10px;"><?php esc_html_e( 'Printed before </body> after WordPress sanitization.', 'gatetouch-ai-seo' ); ?></p>
                        <textarea name="footer_scripts" rows="6" class="gatetouch-input-full" style="font-family:monospace; font-size:12px; background:#fafafa;"><?php echo esc_textarea( $opts['footer_scripts'] ?? '' ); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="gatetouch-form-footer">
            <input type="hidden" name="gatetouch_save_integrations" value="1" />
            <button type="submit" class="gatetouch-btn gatetouch-btn--primary"><?php esc_html_e( 'Sync Integrations Architecture', 'gatetouch-ai-seo' ); ?></button>
        </div>
    </form>
</div>
