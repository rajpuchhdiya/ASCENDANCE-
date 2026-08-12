<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files are included inside class methods, so render variables are local.

$visibility_tabs       = [ 'tab-sitemap', 'tab-robots', 'tab-llms', 'tab-security' ];
$active_visibility_tab = 'tab-sitemap';

if ( isset( $_POST['gatetouch_save_sitemap'] ) && check_admin_referer( 'gatetouch_save_sitemap' ) ) {
    $submitted_visibility_tab = sanitize_key( wp_unslash( $_POST['gatetouch_active_visibility_tab'] ?? '' ) );
    if ( in_array( $submitted_visibility_tab, $visibility_tabs, true ) ) {
        $active_visibility_tab = $submitted_visibility_tab;
    }

    update_option( 'gatetouch_sitemap_settings', [
        'enable_sitemap_index'   => isset( $_POST['enable_sitemap_index'] )   ? 'yes' : 'no',
        'enable_posts_sitemap'   => isset( $_POST['enable_posts_sitemap'] )   ? 'yes' : 'no',
        'enable_pages_sitemap'   => isset( $_POST['enable_pages_sitemap'] )   ? 'yes' : 'no',
        'enable_products_sitemap' => isset( $_POST['enable_products_sitemap'] ) ? 'yes' : 'no',
        'enable_categories_sitemap' => isset( $_POST['enable_categories_sitemap'] ) ? 'yes' : 'no',
        'enable_tags_sitemap'       => isset( $_POST['enable_tags_sitemap'] )       ? 'yes' : 'no',
        'enable_prod_cats_sitemap'  => isset( $_POST['enable_prod_cats_sitemap'] )  ? 'yes' : 'no',
        'enable_prod_tags_sitemap'  => isset( $_POST['enable_prod_tags_sitemap'] )  ? 'yes' : 'no',
        'enable_images_sitemap'  => isset( $_POST['enable_images_sitemap'] )  ? 'yes' : 'no',
        'enable_news_sitemap'    => isset( $_POST['enable_news_sitemap'] )    ? 'yes' : 'no',
        'enable_cpt_sitemaps'    => isset( $_POST['enable_cpt_sitemaps'] )    ? 'yes' : 'no',
        'enable_ping'            => isset( $_POST['enable_ping'] )            ? 'yes' : 'no',
        'posts_per_sitemap'      => intval( wp_unslash( $_POST['posts_per_sitemap'] ?? 200 ) ),
        'sitemap_changefreq'     => sanitize_text_field( wp_unslash( $_POST['sitemap_changefreq'] ?? 'weekly' ) ),
        'sitemap_priority'       => sanitize_text_field( wp_unslash( $_POST['sitemap_priority']   ?? '0.8' ) ),
        'exclude_ids'            => sanitize_text_field( wp_unslash( $_POST['exclude_ids']        ?? '' ) ),
        'news_publication_name'  => sanitize_text_field( wp_unslash( $_POST['news_publication_name'] ?? get_bloginfo('name') ) ),
        'news_language'          => sanitize_text_field( wp_unslash( $_POST['news_language']      ?? 'en' ) ),
        'include_noindex'        => isset( $_POST['include_noindex'] )        ? 'yes' : 'no',
    ] );

    // Save robots settings
    $r_opts = get_option( 'gatetouch_robots_settings', [] );
    if ( ! is_array( $r_opts ) ) {
        $r_opts = [];
    }
    $r_opts['robots_mode']      = sanitize_text_field( wp_unslash( $_POST['robots_mode'] ?? 'auto' ) );
    $r_opts['robots_custom']    = sanitize_textarea_field( wp_unslash( $_POST['robots_custom'] ?? '' ) );
    $r_opts['crawl_delay']      = intval( wp_unslash( $_POST['crawl_delay'] ?? 1 ) );
    // Driven off the crawler registry so adding a bot in one place is enough —
    // the previous hardcoded list silently dropped any crawler not named here.
    foreach ( GateTouch_Robots::ai_bot_defaults() as $gt_bot_key => $gt_bot ) {
        $r_opts[ $gt_bot_key ] = isset( $_POST[ $gt_bot_key ] ) ? 'yes' : 'no';
    }
    update_option( 'gatetouch_robots_settings', $r_opts );

    // Save LLMs settings
    update_option( 'gatetouch_llms_settings', [
        'enable_llms_txt'      => isset( $_POST['enable_llms_txt'] )      ? 'yes' : 'no',
        'enable_llms_full_txt' => isset( $_POST['enable_llms_full_txt'] ) ? 'yes' : 'no',
        'site_description'     => sanitize_textarea_field( wp_unslash( $_POST['llms_site_description'] ?? '' ) ),
        'llms_max_posts'       => intval( wp_unslash( $_POST['llms_max_posts'] ?? 20 ) ),
        'llms_full_max_pages'  => intval( wp_unslash( $_POST['llms_full_max_pages'] ?? 5 ) ),
        'llms_custom_intro'    => sanitize_textarea_field( wp_unslash( $_POST['llms_custom_intro'] ?? '' ) ),
        'llms_include_cpts'    => isset( $_POST['llms_include_cpts'] ) && is_array( $_POST['llms_include_cpts'] ) ? array_map( 'sanitize_key', wp_unslash( $_POST['llms_include_cpts'] ) ) : [],
    ] );

    // Save security.txt
    update_option( 'gatetouch_security_txt_settings', [
        'enable'               => isset( $_POST['sec_enable'] ) ? '1' : '',
        'contact'              => sanitize_text_field( wp_unslash( $_POST['sec_contact']  ?? '' ) ),
        'expires'              => sanitize_text_field( wp_unslash( $_POST['sec_expires']  ?? '' ) ),
        'policy'               => esc_url_raw( wp_unslash( $_POST['sec_policy'] ?? '' ) ),
        'preferred_languages'  => sanitize_text_field( wp_unslash( $_POST['sec_languages'] ?? 'en' ) ),
    ] );

    // Flush rewrite rules
    GateTouch_Sitemap::register_rewrites();
    flush_rewrite_rules();

    // Bust LLMs cache
    delete_transient( 'gatetouch_llms_txt' );
    delete_transient( 'gatetouch_llms_full_txt' );

    $save_message = ( 'tab-robots' === $active_visibility_tab )
        ? __( 'robots.txt settings saved. Rewrite rules flushed.', 'gatetouch-ai-seo' )
        : __( 'Crawl & Visibility settings saved. Rewrite rules flushed.', 'gatetouch-ai-seo' );
    GateTouch_Helpers::notice( $save_message, 'success' );
}

$s = get_option( 'gatetouch_sitemap_settings', [] );
$r = get_option( 'gatetouch_robots_settings', [] );
$l = get_option( 'gatetouch_llms_settings', [] );
$sec = get_option( 'gatetouch_security_txt_settings', [] );
$home = trailingslashit( home_url() );
?>
<div class="gatetouch-admin-wrap">

    <div class="gatetouch-page-tabs">
        <button type="button" class="<?php echo esc_attr( 'gatetouch-page-tab' . ( 'tab-sitemap' === $active_visibility_tab ? ' active' : '' ) ); ?>" data-target="tab-sitemap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            Sitemaps
        </button>
        <button type="button" class="<?php echo esc_attr( 'gatetouch-page-tab' . ( 'tab-robots' === $active_visibility_tab ? ' active' : '' ) ); ?>" data-target="tab-robots">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"/><circle cx="12" cy="5" r="2"/><path d="M12 7v4"/></svg>
            Robots.txt
        </button>
        <button type="button" class="<?php echo esc_attr( 'gatetouch-page-tab' . ( 'tab-llms' === $active_visibility_tab ? ' active' : '' ) ); ?>" data-target="tab-llms">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
            LLMs.txt
        </button>
        <button type="button" class="<?php echo esc_attr( 'gatetouch-page-tab' . ( 'tab-security' === $active_visibility_tab ? ' active' : '' ) ); ?>" data-target="tab-security">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Security.txt
        </button>
    </div>

    <form method="post" class="gatetouch-visibility-form">
        <?php wp_nonce_field( 'gatetouch_save_sitemap' ); ?>
        <input type="hidden" name="gatetouch_active_visibility_tab" value="<?php echo esc_attr( $active_visibility_tab ); ?>" />

        <!-- SITEMAPS TAB -->
        <div class="<?php echo esc_attr( 'gatetouch-page-tab-content' . ( 'tab-sitemap' === $active_visibility_tab ? ' active' : '' ) ); ?>" id="tab-sitemap"<?php if ( 'tab-sitemap' !== $active_visibility_tab ) : ?> style="display:none;"<?php endif; ?>>
            <div class="gatetouch-card">
                <div class="gatetouch-card__header">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    XML Sitemap Settings
                    <a href="<?php echo esc_url( $home . 'sitemap.xml' ); ?>" target="_blank" rel="noopener noreferrer"
                       class="gatetouch-btn gatetouch-btn--primary gatetouch-btn--sm" style="margin-left:auto;">
                       View Sitemap ↗
                    </a>
                </div>
                <div class="gatetouch-settings-rows">
                    <?php
                    $sitemap_types = [
                        'enable_sitemap_index' => [
                            'title' => 'Sitemap Index',
                            'desc'  => 'Master index at /sitemap.xml — required for all sitemaps',
                            'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>',
                            'def'   => true
                        ],
                        'enable_posts_sitemap' => [
                            'title' => 'Posts Sitemap',
                            'desc'  => 'All published blog posts at /sitemap-posts.xml',
                            'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>',
                            'def'   => true
                        ],
                        'enable_pages_sitemap' => [
                            'title' => 'Pages Sitemap',
                            'desc'  => 'All published pages at /sitemap-pages.xml',
                            'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>',
                            'def'   => true
                        ],
                        'enable_products_sitemap' => [
                            'title' => 'Products Sitemap',
                            'desc'  => 'All WooCommerce products at /sitemap-product.xml',
                            'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path><path d="M3 6h18"></path><path d="M16 10a4 4 0 0 1-8 0"></path></svg>',
                            'def'   => true
                        ],
                        'enable_categories_sitemap' => [
                            'title' => 'Post Categories',
                            'desc'  => 'Archive pages for post categories at /sitemap-category.xml',
                            'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>',
                            'def'   => true
                        ],
                        'enable_tags_sitemap' => [
                            'title' => 'Post Tags',
                            'desc'  => 'Archive pages for post tags at /sitemap-post_tag.xml',
                            'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m12 2 3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>',
                            'def'   => true
                        ],
                        'enable_prod_cats_sitemap' => [
                            'title' => 'Product Categories',
                            'desc'  => 'WooCommerce product categories at /sitemap-product_cat.xml',
                            'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v3"></path><path d="M21 16v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-3"></path><path d="M4 12H2"></path><path d="M10 12H8"></path><path d="M16 12h-2"></path><path d="M22 12h-2"></path></svg>',
                            'def'   => true
                        ],
                        'enable_prod_tags_sitemap' => [
                            'title' => 'Product Tags',
                            'desc'  => 'WooCommerce product tags at /sitemap-product_tag.xml',
                            'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>',
                            'def'   => true
                        ],
                        'enable_images_sitemap' => [
                            'title' => 'Image Sitemap',
                            'desc'  => 'All media images for Google Image Search at /sitemap-images.xml',
                            'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>',
                            'def'   => true
                        ],
                        'enable_news_sitemap' => [
                            'title' => 'News Sitemap',
                            'desc'  => 'Posts from last 48 hours for Google News at /sitemap-news.xml',
                            'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 11a9 9 0 0 1 9 9"></path><path d="M4 4a16 16 0 0 1 16 16"></path><circle cx="5" cy="19" r="1"></circle></svg>',
                            'def'   => false
                        ],
                        'enable_cpt_sitemaps' => [
                            'title' => 'CPT Sitemaps',
                            'desc'  => 'Auto-generates one sitemap per active Custom Post Type',
                            'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12.83 2.18a2 2 0 0 0-1.66 0L2.1 6.3a2 2 0 0 0 0 3.6l9.07 4.12a2 2 0 0 0 1.66 0l9.07-4.12a2 2 0 0 0 0-3.6z"></path><path d="m2.1 14.7 9.07 4.12a2 2 0 0 0 1.66 0l9.07-4.12"></path><path d="m2.1 10.5 9.07 4.12a2 2 0 0 0 1.66 0l9.07-4.12"></path></svg>',
                            'def'   => true
                        ],
                        'enable_ping' => [
                            'title' => 'Auto-Ping',
                            'desc'  => __( 'Notify supported search engines when you publish new content', 'gatetouch-ai-seo' ),
                            'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="2"></circle><path d="M16.24 7.76a6 6 0 0 1 0 8.49m-8.48-.01a6 6 0 0 1 0-8.49m11.31-2.82a10 10 0 0 1 0 14.14m-14.15 0a10 10 0 0 1 0-14.14"></path></svg>',
                            'def'   => true
                        ],
                    ];

                    foreach ( $sitemap_types as $key => $data ) :
                        $checked = ( $s[ $key ] ?? ( $data['def'] ? 'yes' : 'no' ) ) === 'yes';
                    ?>
                    <div class="gatetouch-setting-row">
                        <div class="gatetouch-setting-label-wrap">
                            <div class="gatetouch-setting-icon">
                                <?php
                                echo wp_kses(
                                    $data['icon'],
                                    [
                                        'svg'    => [ 'viewBox' => true, 'viewbox' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true ],
                                        'path'   => [ 'd' => true ],
                                        'circle' => [ 'cx' => true, 'cy' => true, 'r' => true ],
                                        'line'   => [ 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true ],
                                    ]
                                );
                                ?>
                            </div>
                            <div class="gatetouch-setting-label">
                                <strong><?php echo esc_html( $data['title'] ); ?></strong>
                                <p><?php echo esc_html( $data['desc'] ); ?></p>
                            </div>
                        </div>
                        <div class="gatetouch-setting-control">
                            <?php GateTouch_Helpers::toggle( $key, $checked ? 'yes' : 'no' ); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <div class="gatetouch-setting-row">
                        <div class="gatetouch-setting-label-wrap">
                            <div class="gatetouch-setting-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                            </div>
                            <div class="gatetouch-setting-label">
                                <strong><?php esc_html_e( 'Max URLs per Sitemap', 'gatetouch-ai-seo' ); ?></strong>
                                <p><?php esc_html_e( 'Maximum number of links allowed in each XML file.', 'gatetouch-ai-seo' ); ?></p>
                            </div>
                        </div>
                        <div class="gatetouch-setting-control">
                            <input type="number" name="posts_per_sitemap"
                                   value="<?php echo esc_attr( intval( $s['posts_per_sitemap'] ?? 200 ) ); ?>"
                                   min="10" max="1000" class="gatetouch-input-sm" style="width: 100px; text-align: center;" />
                        </div>
                    </div>

                    <div class="gatetouch-setting-row">
                        <div class="gatetouch-setting-label-wrap">
                            <div class="gatetouch-setting-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                            </div>
                            <div class="gatetouch-setting-label">
                                <strong><?php esc_html_e( 'Change Frequency', 'gatetouch-ai-seo' ); ?></strong>
                                <p><?php esc_html_e( 'Tells search engines how often this content usually changes.', 'gatetouch-ai-seo' ); ?></p>
                            </div>
                        </div>
                        <div class="gatetouch-setting-control">
                            <select name="sitemap_changefreq" class="gatetouch-select" style="width: 180px;">
                                <?php foreach ( [ 'always','hourly','daily','weekly','monthly','yearly','never' ] as $f ) : ?>
                                <option value="<?php echo esc_attr( $f ); ?>"
                                    <?php selected( $s['sitemap_changefreq'] ?? 'weekly', $f ); ?>>
                                    <?php echo esc_html( ucfirst( $f ) ); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="gatetouch-setting-row">
                        <div class="gatetouch-setting-label-wrap">
                            <div class="gatetouch-setting-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
                            </div>
                            <div class="gatetouch-setting-label">
                                <strong><?php esc_html_e( 'Exclude Post/Page IDs', 'gatetouch-ai-seo' ); ?></strong>
                                <p><?php esc_html_e( 'Comma-separated IDs to exclude from all sitemaps.', 'gatetouch-ai-seo' ); ?></p>
                            </div>
                        </div>
                        <div class="gatetouch-setting-control">
                            <input type="text" name="exclude_ids"
                                   value="<?php echo esc_attr( $s['exclude_ids'] ?? '' ); ?>"
                                   placeholder="e.g. 12, 45, 78" class="gatetouch-input" style="width: 240px;" />
                        </div>
                    </div>

                    <div class="gatetouch-setting-row">
                        <div class="gatetouch-setting-label-wrap">
                            <div class="gatetouch-setting-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="4 7 4 4 20 4 20 7"></polyline><line x1="9" y1="20" x2="15" y2="20"></line><line x1="12" y1="4" x2="12" y2="20"></line></svg>
                            </div>
                            <div class="gatetouch-setting-label">
                                <strong><?php esc_html_e( 'News Publication Name', 'gatetouch-ai-seo' ); ?></strong>
                                <p><?php esc_html_e( 'Required for Google News visibility.', 'gatetouch-ai-seo' ); ?></p>
                            </div>
                        </div>
                        <div class="gatetouch-setting-control">
                            <input type="text" name="news_publication_name"
                                   value="<?php echo esc_attr( $s['news_publication_name'] ?? get_bloginfo('name') ); ?>"
                                   class="gatetouch-input" style="width: 240px;" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROBOTS TAB -->
        <div class="<?php echo esc_attr( 'gatetouch-page-tab-content' . ( 'tab-robots' === $active_visibility_tab ? ' active' : '' ) ); ?>" id="tab-robots"<?php if ( 'tab-robots' !== $active_visibility_tab ) : ?> style="display:none;"<?php endif; ?>>
            <div class="gatetouch-card">
                <div class="gatetouch-card__header">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"/><circle cx="12" cy="5" r="2"/><path d="M12 7v4"/></svg>
                    robots.txt Manager
                    <a href="<?php echo esc_url( $home . 'robots.txt' ); ?>" target="_blank" rel="noopener noreferrer"
                       class="gatetouch-btn gatetouch-btn--ghost gatetouch-btn--sm" style="margin-left:auto; border-radius: 40px;">View robots.txt →</a>
                </div>
                <div class="gatetouch-settings-rows">
                    <div class="gatetouch-setting-row">
                        <div class="gatetouch-setting-label"><strong>Mode</strong></div>
                        <div class="gatetouch-setting-control">
                            <div style="display:flex; flex-direction:column; gap:12px;">
                                <label style="display:flex;align-items:center;gap:12px; cursor:pointer;" class="riq-radio-group">
                                    <input type="radio" name="robots_mode" value="auto"
                                        <?php checked( $r['robots_mode'] ?? 'auto', 'auto' ); ?> />
                                    <div style="font-size:14px; color:var(--riq-text);">
                                        <strong><?php esc_html_e( 'Auto-Pilot', 'gatetouch-ai-seo' ); ?></strong>
                                        <div style="font-size:12px; color:var(--riq-text-light);"><?php esc_html_e( 'Plugin automatically generates the perfect robots.txt for humans and AI.', 'gatetouch-ai-seo' ); ?></div>
                                    </div>
                                </label>
                                <label style="display:flex;align-items:center;gap:12px; cursor:pointer;" class="riq-radio-group">
                                    <input type="radio" name="robots_mode" value="custom"
                                        <?php checked( $r['robots_mode'] ?? 'auto', 'custom' ); ?> />
                                    <div style="font-size:14px; color:var(--riq-text);">
                                        <strong><?php esc_html_e( 'Custom Expert Mode', 'gatetouch-ai-seo' ); ?></strong>
                                        <div style="font-size:12px; color:var(--riq-text-light);"><?php esc_html_e( 'Manually edit and override with your own directives below.', 'gatetouch-ai-seo' ); ?></div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="gatetouch-setting-row gatetouch-ai-crawlers">
                        <div class="gatetouch-setting-label">
                            <strong><?php esc_html_e( 'AI Crawler Control', 'gatetouch-ai-seo' ); ?></strong>
                            <p><?php esc_html_e( 'Not all "AI bots" are the same. Blocking the wrong group removes you from AI answers without protecting anything.', 'gatetouch-ai-seo' ); ?></p>
                        </div>
                        <div class="gatetouch-setting-control">
                            <?php
                            $gt_group_style = [
                                'citation'  => [ '#10b981', '#ecfdf5', '#a7f3d0' ],
                                'training'  => [ '#f59e0b', '#fffbeb', '#fde68a' ],
                                'seo_tools' => [ '#6366f1', '#eef2ff', '#c7d2fe' ],
                            ];
                            foreach ( GateTouch_Robots::ai_bots() as $gt_gkey => $gt_group ) :
                                list( $gt_dot, $gt_bg, $gt_border ) = $gt_group_style[ $gt_gkey ];
                                ?>
                                <div style="margin-bottom:26px;">
                                    <div style="background:<?php echo esc_attr( $gt_bg ); ?>; border:1px solid <?php echo esc_attr( $gt_border ); ?>; border-radius:12px; padding:14px 16px; margin-bottom:12px;">
                                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                                            <span style="width:9px; height:9px; border-radius:50%; background:<?php echo esc_attr( $gt_dot ); ?>;"></span>
                                            <strong style="font-size:14px; color:#0f172a;"><?php echo esc_html( $gt_group['label'] ); ?></strong>
                                        </div>
                                        <p style="margin:0; font-size:12.5px; line-height:1.55; color:#475569;"><?php echo esc_html( $gt_group['description'] ); ?></p>
                                    </div>

                                    <table class="gatetouch-ai-bots-table">
                                        <thead>
                                            <tr>
                                                <th><?php esc_html_e( 'Crawler', 'gatetouch-ai-seo' ); ?></th>
                                                <th><?php esc_html_e( 'What it does', 'gatetouch-ai-seo' ); ?></th>
                                                <th><?php esc_html_e( 'Allow', 'gatetouch-ai-seo' ); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ( $gt_group['bots'] as $gt_bot => $gt_meta ) :
                                            $gt_allowed = ( $r[ $gt_meta['key'] ] ?? $gt_meta['default'] ) === 'yes';
                                            ?>
                                            <tr>
                                                <td style="vertical-align:top;">
                                                    <div style="display:flex; align-items:center; gap:8px;">
                                                        <span style="width:8px; height:8px; border-radius:50%; background:<?php echo esc_attr( $gt_dot ); ?>; box-shadow:0 0 8px <?php echo esc_attr( $gt_dot ); ?>44; flex-shrink:0;"></span>
                                                        <div>
                                                            <strong style="font-size:13px; display:block;"><?php echo esc_html( $gt_bot ); ?></strong>
                                                            <span style="font-size:11px; color:var(--riq-text-light);"><?php echo esc_html( $gt_meta['owner'] ); ?></span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td style="color:var(--riq-text-light); font-size:12.5px; line-height:1.5; vertical-align:top; max-width:420px;">
                                                    <?php echo esc_html( $gt_meta['note'] ); ?>
                                                </td>
                                                <td style="vertical-align:top;">
                                                    <?php GateTouch_Helpers::toggle( $gt_meta['key'], $gt_allowed ? 'yes' : 'no' ); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="gatetouch-setting-row">
                        <div class="gatetouch-setting-label">
                            <strong><?php esc_html_e( 'Custom robots.txt', 'gatetouch-ai-seo' ); ?></strong>
                            <p><?php esc_html_e( 'Only used when Custom mode is selected above.', 'gatetouch-ai-seo' ); ?></p>
                        </div>
                        <div class="gatetouch-setting-control">
                            <textarea name="robots_custom" rows="15"
                                      class="gatetouch-input-full gatetouch-textarea--code"><?php
                                echo esc_textarea( $r['robots_custom'] ?? '' );
                            ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="gatetouch-card__footer" style="display:flex; justify-content:flex-end; padding:18px 24px;">
                    <button type="submit" id="gatetouch-save-robots-settings" class="gatetouch-btn gatetouch-btn--primary gatetouch-tab-submit" data-active-tab="tab-robots">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        <?php esc_html_e( 'Save robots.txt Settings', 'gatetouch-ai-seo' ); ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- LLMs TAB -->
        <div class="<?php echo esc_attr( 'gatetouch-page-tab-content' . ( 'tab-llms' === $active_visibility_tab ? ' active' : '' ) ); ?>" id="tab-llms"<?php if ( 'tab-llms' !== $active_visibility_tab ) : ?> style="display:none;"<?php endif; ?>>
            <div class="gatetouch-card">
                <div class="gatetouch-card__header">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                    LLMs.txt — AI Search Visibility
                </div>
                <div class="gatetouch-notice gatetouch-notice--info" style="margin:16px 20px 0;">
                    <strong><?php esc_html_e( 'What is llms.txt?', 'gatetouch-ai-seo' ); ?></strong> A Markdown file that tells ChatGPT, Claude, and Perplexity
                    what your most important content is, so they cite it when users ask relevant questions.
                    <a href="<?php echo esc_url( $home . 'llms.txt' ); ?>" target="_blank" rel="noopener noreferrer">View yours →</a>
                </div>
                <div class="gatetouch-settings-rows">
                    <div class="gatetouch-setting-row">
                        <div class="gatetouch-setting-label">
                            <strong><?php esc_html_e( 'Enable llms.txt', 'gatetouch-ai-seo' ); ?></strong>
                            <p><?php esc_html_e( 'Serves Markdown guide at', 'gatetouch-ai-seo' ); ?> <code>/llms.txt</code></p>
                        </div>
                        <div class="gatetouch-setting-control">
                            <?php GateTouch_Helpers::toggle( 'enable_llms_txt', $l['enable_llms_txt'] ?? 'no' ); ?>
                        </div>
                    </div>
                    <div class="gatetouch-setting-row">
                        <div class="gatetouch-setting-label">
                            <strong><?php esc_html_e( 'Enable llms-full.txt', 'gatetouch-ai-seo' ); ?></strong>
                            <p><?php esc_html_e( 'Full page content at', 'gatetouch-ai-seo' ); ?> <code>/llms-full.txt</code> <?php esc_html_e( 'for AI coding tools', 'gatetouch-ai-seo' ); ?></p>
                        </div>
                        <div class="gatetouch-setting-control">
                            <?php GateTouch_Helpers::toggle( 'enable_llms_full_txt', $l['enable_llms_full_txt'] ?? 'no' ); ?>
                        </div>
                    </div>
                    <div class="gatetouch-setting-row">
                        <div class="gatetouch-setting-label">
                            <strong><?php esc_html_e( 'Site Description for AI', 'gatetouch-ai-seo' ); ?></strong>
                            <p>2-3 sentences about what your site does. AI uses this as context.</p>
                        </div>
                        <div class="gatetouch-setting-control">
                            <textarea name="llms_site_description" rows="4"
                                      class="gatetouch-input-full"><?php
                                echo esc_textarea( $l['site_description'] ?? get_bloginfo('description') );
                            ?></textarea>
                        </div>
                    </div>
                    <div class="gatetouch-setting-row">
                        <div class="gatetouch-setting-label">
                            <strong><?php esc_html_e( 'Max Posts to List', 'gatetouch-ai-seo' ); ?></strong>
                            <p>Recommended: 20–30 posts max.</p>
                        </div>
                        <div class="gatetouch-setting-control">
                            <input type="number" name="llms_max_posts"
                                   value="<?php echo esc_attr( intval( $l['llms_max_posts'] ?? 20 ) ); ?>"
                                   min="5" max="50" class="gatetouch-input-sm" />
                        </div>
                    </div>
                    <div class="gatetouch-setting-row">
                        <div class="gatetouch-setting-label">
                            <strong><?php esc_html_e( 'Custom Intro (Markdown)', 'gatetouch-ai-seo' ); ?></strong>
                        </div>
                        <div class="gatetouch-setting-control">
                            <textarea name="llms_custom_intro" rows="5"
                                      class="gatetouch-input-full"
                                      placeholder="## Our Expertise&#10;We specialize in..."><?php
                                echo esc_textarea( $l['llms_custom_intro'] ?? '' );
                            ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECURITY.TXT TAB -->
        <div class="<?php echo esc_attr( 'gatetouch-page-tab-content' . ( 'tab-security' === $active_visibility_tab ? ' active' : '' ) ); ?>" id="tab-security"<?php if ( 'tab-security' !== $active_visibility_tab ) : ?> style="display:none;"<?php endif; ?>>
            <div class="gatetouch-card">
                <div class="gatetouch-card__header">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    security.txt (RFC 9116)
                </div>
                <div class="gatetouch-settings-rows">
                    <div class="gatetouch-setting-row">
                        <div class="gatetouch-setting-label">
                            <strong><?php esc_html_e( 'Enable security.txt', 'gatetouch-ai-seo' ); ?></strong>
                            <p><?php esc_html_e( 'Served at', 'gatetouch-ai-seo' ); ?> <code>/.well-known/security.txt</code></p>
                        </div>
                        <div class="gatetouch-setting-control">
                            <?php GateTouch_Helpers::toggle( 'sec_enable', ! empty( $sec['enable'] ) ? 'yes' : 'no' ); ?>
                        </div>
                    </div>
                    <div class="gatetouch-setting-row">
                        <div class="gatetouch-setting-label"><strong><?php esc_html_e( 'Security Contact', 'gatetouch-ai-seo' ); ?></strong></div>
                        <div class="gatetouch-setting-control">
                            <input type="text" name="sec_contact"
                                   value="<?php echo esc_attr( $sec['contact'] ?? '' ); ?>"
                                   placeholder="mailto:security@yourdomain.com"
                                   class="gatetouch-input-full" />
                        </div>
                    </div>
                    <div class="gatetouch-setting-row">
                        <div class="gatetouch-setting-label"><strong><?php esc_html_e( 'Expires', 'gatetouch-ai-seo' ); ?></strong></div>
                        <div class="gatetouch-setting-control">
                            <input type="text" name="sec_expires"
                                   value="<?php echo esc_attr( $sec['expires'] ?? '' ); ?>"
                                   placeholder="<?php echo esc_attr( gmdate( 'Y-m-d', strtotime( '+1 year' ) ) ); ?>T00:00:00z"
                                   class="gatetouch-input-full" />
                        </div>
                    </div>
                    <div class="gatetouch-setting-row">
                        <div class="gatetouch-setting-label"><strong><?php esc_html_e( 'Security Policy URL', 'gatetouch-ai-seo' ); ?></strong></div>
                        <div class="gatetouch-setting-control">
                            <input type="url" name="sec_policy"
                                   value="<?php echo esc_url( $sec['policy'] ?? '' ); ?>"
                                   placeholder="https://yourdomain.com/security-policy"
                                   class="gatetouch-input-full" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="gatetouch-form-footer" style="padding: 24px 30px;">
            <input type="hidden" name="gatetouch_save_sitemap" value="1" />
            <button type="submit" class="gatetouch-btn gatetouch-btn--primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save All Settings
            </button>
        </div>
    </form>
</div>
