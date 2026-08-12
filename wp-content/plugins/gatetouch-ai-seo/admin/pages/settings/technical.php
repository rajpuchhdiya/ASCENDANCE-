<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files are included inside class methods, so render variables are local.

if ( isset( $_POST['gatetouch_save_tech'] ) && check_admin_referer( 'gatetouch_save_tech' ) ) {
    $s_opts = get_option( 'gatetouch_sitemap_settings', [] );
    $s_opts['enabled'] = isset( $_POST['sitemap_enabled'] ) ? 'yes' : 'no';
    update_option( 'gatetouch_sitemap_settings', $s_opts );

    $l_opts = get_option( 'gatetouch_llms_settings', [] );
    $l_opts['enabled'] = isset( $_POST['llms_enabled'] ) ? 'yes' : 'no';
    update_option( 'gatetouch_llms_settings', $l_opts );

    $r_opts = get_option( 'gatetouch_robots_settings', [] );
    $r_opts['enabled']  = isset( $_POST['robots_enabled'] ) ? 'yes' : 'no';
    $r_opts['optimize'] = isset( $_POST['robots_optimize'] ) ? 'yes' : 'no';
    update_option( 'gatetouch_robots_settings', $r_opts );

    $crawl_opts = isset( $_POST['crawl'] ) && is_array( $_POST['crawl'] ) ? wp_unslash( $_POST['crawl'] ) : [];
    update_option( 'gatetouch_crawl_optimization_settings', GateTouch_Crawl_Optimization::sanitize( $crawl_opts ) );

    GateTouch_Helpers::notice( '✅ Technical settings saved!', 'success' );
}

$sitemap = get_option( 'gatetouch_sitemap_settings', [] );
$llms    = get_option( 'gatetouch_llms_settings', [] );
$robots  = get_option( 'gatetouch_robots_settings', [] );
$crawl   = GateTouch_Crawl_Optimization::settings();
?>
<div class="gatetouch-settings-group">
    <form method="post">
        <?php wp_nonce_field( 'gatetouch_save_tech' ); ?>

        <div class="gatetouch-card">
            <div class="gatetouch-card__header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                Automated Discovery Files
            </div>
            <div class="gatetouch-settings-rows">
                <div class="gatetouch-setting-row">
                    <div class="gatetouch-setting-label">
                        <strong><?php esc_html_e( 'XML Sitemap', 'gatetouch-ai-seo' ); ?></strong>
                        <p><?php esc_html_e( 'Automatically generate a dynamic XML sitemap for search engines.', 'gatetouch-ai-seo' ); ?></p>
                        <a href="<?php echo esc_url( home_url( 'sitemap.xml' ) ); ?>" target="_blank" rel="noopener noreferrer" class="gatetouch-link">View Sitemap →</a>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=gatetouch-settings&tab=files#tab-sitemap' ) ); ?>" class="gatetouch-link" style="margin-left: 15px;">Configure Settings</a>
                    </div>
                    <div class="gatetouch-setting-control">
                        <?php GateTouch_Helpers::toggle( 'sitemap_enabled', ( $sitemap['enabled'] ?? 'yes' ) === 'yes' ? 'yes' : 'no' ); ?>
                    </div>
                </div>
                <div class="gatetouch-setting-row">
                    <div class="gatetouch-setting-label">
                        <strong><?php esc_html_e( 'LLMs.txt Generation', 'gatetouch-ai-seo' ); ?></strong>
                        <p><?php esc_html_e( 'Creates an AI-readable index for ChatGPT, Claude, and Gemini.', 'gatetouch-ai-seo' ); ?></p>
                        <a href="<?php echo esc_url( home_url( 'llms.txt' ) ); ?>" target="_blank" rel="noopener noreferrer" class="gatetouch-link">View LLMs.txt →</a>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=gatetouch-settings&tab=files#tab-llms' ) ); ?>" class="gatetouch-link" style="margin-left: 15px;">Configure Settings</a>
                    </div>
                    <div class="gatetouch-setting-control">
                        <?php GateTouch_Helpers::toggle( 'llms_enabled', ( $llms['enabled'] ?? 'yes' ) === 'yes' ? 'yes' : 'no' ); ?>
                    </div>
                </div>
                <div class="gatetouch-setting-row">
                    <div class="gatetouch-setting-label">
                        <strong><?php esc_html_e( 'Robots.txt Optimization', 'gatetouch-ai-seo' ); ?></strong>
                        <p><?php esc_html_e( 'Automatically optimize robots directives for better crawl budget.', 'gatetouch-ai-seo' ); ?></p>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=gatetouch-settings&tab=files#tab-robots' ) ); ?>" class="gatetouch-link">Configure Settings</a>
                    </div>
                    <div class="gatetouch-setting-control">
                        <?php GateTouch_Helpers::toggle( 'robots_optimize', ( $robots['optimize'] ?? 'yes' ) === 'yes' ? 'yes' : 'no' ); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="gatetouch-card">
            <div class="gatetouch-card__header" style="display:flex; align-items:center; justify-content:space-between; gap:16px;">
                <span style="display:flex; align-items:center; gap:10px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19h16"/><path d="M4 15h16"/><path d="M8 11h12"/><path d="M12 7h8"/><path d="M16 3h4"/></svg>
                    <?php esc_html_e( 'Free Crawl Optimization', 'gatetouch-ai-seo' ); ?>
                </span>
                <span class="gatetouch-badge gatetouch-badge--green"><?php esc_html_e( 'Included Free', 'gatetouch-ai-seo' ); ?></span>
            </div>
            <div class="gatetouch-settings-rows">
                <div class="gatetouch-setting-row" style="grid-template-columns:1fr; gap:8px; align-items:start;">
                    <div class="gatetouch-setting-label">
                        <strong><?php esc_html_e( 'Yoast-style crawl budget controls without a premium lock', 'gatetouch-ai-seo' ); ?></strong>
                        <p><?php esc_html_e( 'Remove low-value metadata, disable unwanted feeds, manage AI crawlers, protect internal search URLs, and clean tracking parameters from one free GateTouch panel.', 'gatetouch-ai-seo' ); ?></p>
                    </div>
                </div>

                <?php foreach ( GateTouch_Crawl_Optimization::sections() as $section_key => $section ) : ?>
                    <div class="gatetouch-setting-row" style="grid-template-columns:1fr; gap:8px; align-items:start; background:#f8fafc;">
                        <div class="gatetouch-setting-label">
                            <strong><?php echo esc_html( $section['title'] ); ?></strong>
                            <p><?php echo esc_html( $section['description'] ); ?></p>
                        </div>
                    </div>

                    <?php foreach ( $section['fields'] as $field_key => $field_label ) : ?>
                        <div class="gatetouch-setting-row">
                            <div class="gatetouch-setting-label">
                                <strong><?php echo esc_html( $field_label ); ?></strong>
                                <p>
                                    <?php
                                    switch ( $field_key ) {
                                        case 'remove_shortlinks':
                                            esc_html_e( 'Removes rel=shortlink output from the page source and HTTP headers.', 'gatetouch-ai-seo' );
                                            break;
                                        case 'remove_rest_links':
                                            esc_html_e( 'Removes REST API discovery links from the head and link headers.', 'gatetouch-ai-seo' );
                                            break;
                                        case 'remove_rsd_wlw_links':
                                            esc_html_e( 'Removes legacy editor discovery links that most modern sites do not need.', 'gatetouch-ai-seo' );
                                            break;
                                        case 'remove_oembed_links':
                                            esc_html_e( 'Removes oEmbed discovery links and host JavaScript from frontend output.', 'gatetouch-ai-seo' );
                                            break;
                                        case 'remove_generator_tag':
                                            esc_html_e( 'Hides WordPress version generator metadata from frontend source.', 'gatetouch-ai-seo' );
                                            break;
                                        case 'remove_pingback_header':
                                            esc_html_e( 'Removes the X-Pingback header from frontend responses.', 'gatetouch-ai-seo' );
                                            break;
                                        case 'remove_powered_by_header':
                                            esc_html_e( 'Removes the PHP X-Powered-By header when the server allows it.', 'gatetouch-ai-seo' );
                                            break;
                                        case 'remove_wp_json_discovery':
                                            esc_html_e( 'Adds a robots.txt disallow rule for /wp-json/ discovery crawling.', 'gatetouch-ai-seo' );
                                            break;
                                        case 'remove_emoji_scripts':
                                            esc_html_e( 'Stops WordPress emoji scripts and styles from loading on the frontend.', 'gatetouch-ai-seo' );
                                            break;
                                        case 'block_adsbot':
                                        case 'block_google_extended':
                                        case 'block_gptbot':
                                        case 'block_ccbot':
                                        case 'block_claudebot':
                                        case 'prevent_search_crawling':
                                            esc_html_e( 'Adds robots.txt directives when GateTouch robots optimization is enabled.', 'gatetouch-ai-seo' );
                                            break;
                                        case 'clean_ga_params':
                                            esc_html_e( 'Redirects URLs with common analytics and ad parameters to their clean canonical form.', 'gatetouch-ai-seo' );
                                            break;
                                        case 'remove_unregistered_params':
                                            esc_html_e( 'Redirects unknown query parameters unless they are WordPress-safe or explicitly allowed below.', 'gatetouch-ai-seo' );
                                            break;
                                        default:
                                            esc_html_e( 'Helps reduce duplicate URLs, thin crawl targets, or unnecessary frontend output.', 'gatetouch-ai-seo' );
                                            break;
                                    }
                                    ?>
                                </p>
                            </div>
                            <div class="gatetouch-setting-control">
                                <?php GateTouch_Helpers::toggle( 'crawl[' . $field_key . ']', ! empty( $crawl[ $field_key ] ) ? 'yes' : 'no' ); ?>
                            </div>
                        </div>

                        <?php if ( 'filter_search_terms' === $field_key ) : ?>
                            <div class="gatetouch-setting-row">
                                <div class="gatetouch-setting-label">
                                    <strong><?php esc_html_e( 'Maximum search query length', 'gatetouch-ai-seo' ); ?></strong>
                                    <p><?php esc_html_e( 'Search URLs longer than this character count are blocked when long search filtering is enabled.', 'gatetouch-ai-seo' ); ?></p>
                                </div>
                                <div class="gatetouch-setting-control">
                                    <input type="number" min="1" max="250" name="crawl[max_search_length]" value="<?php echo esc_attr( $crawl['max_search_length'] ?? '50' ); ?>" class="gatetouch-input-full" style="max-width:220px;" />
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <?php if ( 'url' === $section_key ) : ?>
                        <div class="gatetouch-setting-row" style="align-items:start;">
                            <div class="gatetouch-setting-label">
                                <strong><?php esc_html_e( 'Additional URL parameters to allow', 'gatetouch-ai-seo' ); ?></strong>
                                <p><?php esc_html_e( 'Comma or space separated. These are preserved when unknown parameter cleanup is enabled.', 'gatetouch-ai-seo' ); ?></p>
                            </div>
                            <div class="gatetouch-setting-control" style="display:block;">
                                <textarea name="crawl[allowed_url_params]" rows="3" class="gatetouch-input-full" placeholder="affiliate, ref, campaign"><?php echo esc_textarea( $crawl['allowed_url_params'] ?? '' ); ?></textarea>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="gatetouch-form-footer">
            <input type="hidden" name="gatetouch_save_tech" value="1" />
            <button type="submit" class="gatetouch-btn gatetouch-btn--primary"><?php esc_html_e( 'Save Technical Settings', 'gatetouch-ai-seo' ); ?></button>
        </div>
    </form>
</div>
