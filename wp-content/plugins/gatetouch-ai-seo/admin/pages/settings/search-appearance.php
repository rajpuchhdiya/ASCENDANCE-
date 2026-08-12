<?php
/**
 * Settings → Search Appearance.
 *
 * One screen that controls the title, description, robots and schema defaults for
 * every page type on the site: the homepage, each post type, each taxonomy,
 * author/date/search/404 archives, post type archives and WooCommerce.
 */
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files are included inside class methods, so render variables are local.
// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Sub-tab selection is a read-only GET parameter.

$gt_sub = isset( $_GET['sub'] ) ? sanitize_key( wp_unslash( $_GET['sub'] ) ) : 'global';

// ── Save ─────────────────────────────────────────────────────────────────────
if ( isset( $_POST['gatetouch_save_sa'] ) && check_admin_referer( 'gatetouch_save_sa' ) ) {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have permission to change these settings.', 'gatetouch-ai-seo' ) );
    }

    $gt_raw = isset( $_POST['sa'] ) && is_array( $_POST['sa'] )
        ? wp_unslash( $_POST['sa'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised inside GateTouch_Helpers::sanitize_search_appearance().
        : [];

    $gt_existing = get_option( GateTouch_Search_Appearance::OPTION, [] );
    $gt_existing = is_array( $gt_existing ) ? $gt_existing : [];

    $gt_clean = GateTouch_Helpers::sanitize_search_appearance( $gt_raw );

    // Merge per group so saving one sub-tab never wipes the others.
    foreach ( $gt_clean as $gt_group => $gt_values ) {
        $gt_existing[ $gt_group ] = is_array( $gt_values ) && isset( $gt_existing[ $gt_group ] ) && is_array( $gt_existing[ $gt_group ] )
            ? array_merge( $gt_existing[ $gt_group ], $gt_values )
            : $gt_values;
    }

    update_option( GateTouch_Search_Appearance::OPTION, $gt_existing );

    // Keep the standalone separator option in sync — other modules still read it.
    if ( isset( $gt_clean['global']['title_separator'] ) ) {
        update_option( 'gatetouch_title_separator', $gt_clean['global']['title_separator'] );
    }

    GateTouch_Search_Appearance::flush();
    GateTouch_Helpers::notice( __( 'Search Appearance settings saved.', 'gatetouch-ai-seo' ), 'success' );
}

$gt_settings   = GateTouch_Search_Appearance::settings();
$gt_separators = [ '|', '-', '–', '—', '•', '·', '»', '›', '/', '⋆' ];

$gt_subtabs = [
    'global'      => __( 'Global & Homepage', 'gatetouch-ai-seo' ),
    'content'     => __( 'Post Types', 'gatetouch-ai-seo' ),
    'taxonomies'  => __( 'Taxonomies', 'gatetouch-ai-seo' ),
    'archives'    => __( 'Archives & Special Pages', 'gatetouch-ai-seo' ),
    'schema'      => __( 'Schema Defaults', 'gatetouch-ai-seo' ),
    'advanced'    => __( 'Advanced', 'gatetouch-ai-seo' ),
];

if ( GateTouch_WooCommerce::is_active() ) {
    $gt_subtabs['woocommerce'] = __( 'WooCommerce', 'gatetouch-ai-seo' );
}

if ( ! isset( $gt_subtabs[ $gt_sub ] ) ) {
    $gt_sub = 'global';
}

/**
 * Render the shared title / description / robots block for one entity.
 *
 * @param string $group    Settings group key (content_types, taxonomies, …).
 * @param string $key      Entity key within the group.
 * @param array  $values   Current values.
 * @param array  $options  robots => bool, schema => array|false, extra => callable|null.
 */
$gt_render_template_card = function ( $group, $key, $values, $label, $description = '', $options = [] ) {
    $name = "sa[{$group}][{$key}]";
    ?>
    <div class="gatetouch-card gatetouch-sa-card">
        <div class="gatetouch-card__header">
            <?php echo esc_html( $label ); ?>
            <code class="gatetouch-sa-slug"><?php echo esc_html( $key ); ?></code>
        </div>
        <div class="gatetouch-card__body">
            <?php if ( $description ) : ?>
                <p class="gatetouch-sa-desc"><?php echo esc_html( $description ); ?></p>
            <?php endif; ?>

            <div class="gatetouch-sa-field">
                <label><strong><?php esc_html_e( 'Title Template', 'gatetouch-ai-seo' ); ?></strong></label>
                <input type="text"
                       name="<?php echo esc_attr( $name ); ?>[title]"
                       value="<?php echo esc_attr( $values['title'] ?? '' ); ?>"
                       class="gatetouch-input-full gatetouch-sa-input"
                       data-gt-var-target="1"
                       data-gt-counter="title" />
                <span class="gatetouch-sa-counter"></span>
            </div>

            <div class="gatetouch-sa-field">
                <label><strong><?php esc_html_e( 'Description Template', 'gatetouch-ai-seo' ); ?></strong></label>
                <textarea name="<?php echo esc_attr( $name ); ?>[desc]"
                          rows="2"
                          class="gatetouch-textarea gatetouch-input-full gatetouch-sa-input"
                          data-gt-var-target="1"
                          data-gt-counter="desc"><?php echo esc_textarea( $values['desc'] ?? '' ); ?></textarea>
                <span class="gatetouch-sa-counter"></span>
            </div>

            <?php if ( ! empty( $options['schema'] ) ) : ?>
                <div class="gatetouch-sa-field gatetouch-sa-field--half">
                    <label><strong><?php esc_html_e( 'Default Schema Type', 'gatetouch-ai-seo' ); ?></strong></label>
                    <select name="<?php echo esc_attr( $name ); ?>[schema_type]" class="gatetouch-select">
                        <?php foreach ( $options['schema'] as $gt_type ) : ?>
                            <option value="<?php echo esc_attr( $gt_type ); ?>" <?php selected( $values['schema_type'] ?? '', $gt_type ); ?>>
                                <?php echo esc_html( $gt_type ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="gatetouch-sa-hint"><?php esc_html_e( 'Individual posts can override this in the editor.', 'gatetouch-ai-seo' ); ?></p>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $options['robots'] ) ) : ?>
                <div class="gatetouch-sa-field">
                    <label><strong><?php esc_html_e( 'Robots', 'gatetouch-ai-seo' ); ?></strong></label>
                    <div class="gatetouch-sa-robots">
                        <?php
                        $gt_directives = [
                            'noindex'      => __( 'No-index (hide from search results)', 'gatetouch-ai-seo' ),
                            'nofollow'     => __( 'No-follow links', 'gatetouch-ai-seo' ),
                            'noarchive'    => __( 'No cached copy', 'gatetouch-ai-seo' ),
                            'noimageindex' => __( 'Do not index images', 'gatetouch-ai-seo' ),
                        ];
                        foreach ( $gt_directives as $gt_flag => $gt_flag_label ) :
                            ?>
                            <label class="gatetouch-sa-check">
                                <input type="checkbox"
                                       name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $gt_flag ); ?>]"
                                       value="1" <?php checked( '1', $values[ $gt_flag ] ?? '' ); ?> />
                                <?php echo esc_html( $gt_flag_label ); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php
            if ( ! empty( $options['extra'] ) && is_callable( $options['extra'] ) ) {
                call_user_func( $options['extra'], $name, $values );
            }
            ?>
        </div>
    </div>
    <?php
};
?>

<div class="gatetouch-settings-group gatetouch-sa">

    <div class="gatetouch-sa-intro">
        <h2><?php esc_html_e( 'Search Appearance', 'gatetouch-ai-seo' ); ?></h2>
        <p>
            <?php esc_html_e( 'These templates decide how every page on your site is titled and described in Google, Bing, ChatGPT and Perplexity — including pages that have no meta box of their own, such as category, tag, author and date archives.', 'gatetouch-ai-seo' ); ?>
            <strong><?php esc_html_e( 'Sensible defaults are already applied.', 'gatetouch-ai-seo' ); ?></strong>
            <?php esc_html_e( 'A single post, page, category or tag can always override its template from its own editor screen.', 'gatetouch-ai-seo' ); ?>
        </p>
    </div>

    <nav class="gatetouch-sa-subnav">
        <?php foreach ( $gt_subtabs as $gt_key => $gt_label ) : ?>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=gatetouch-settings&tab=appearance&sub=' . $gt_key ) ); ?>"
               class="gatetouch-sa-subnav__item <?php echo $gt_sub === $gt_key ? 'is-active' : ''; ?>">
                <?php echo esc_html( $gt_label ); ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <form method="post">
        <?php wp_nonce_field( 'gatetouch_save_sa' ); ?>
        <input type="hidden" name="gatetouch_save_sa" value="1" />

        <div class="gatetouch-sa-layout">
            <div class="gatetouch-sa-main">

                <?php if ( 'global' === $gt_sub ) : ?>

                    <div class="gatetouch-card">
                        <div class="gatetouch-card__header"><?php esc_html_e( 'Title Separator', 'gatetouch-ai-seo' ); ?></div>
                        <div class="gatetouch-card__body">
                            <p class="gatetouch-sa-desc"><?php esc_html_e( 'Used wherever a template contains #sep#.', 'gatetouch-ai-seo' ); ?></p>
                            <div class="gatetouch-visual-picker" id="gatetouch-sa-sep">
                                <input type="hidden" name="sa[global][title_separator]" id="gatetouch-sa-sep-input"
                                       value="<?php echo esc_attr( $gt_settings['global']['title_separator'] ); ?>" />
                                <?php foreach ( $gt_separators as $gt_sep ) : ?>
                                    <div class="gatetouch-visual-picker__item <?php echo $gt_settings['global']['title_separator'] === $gt_sep ? 'is-active' : ''; ?>"
                                         data-sep="<?php echo esc_attr( $gt_sep ); ?>"><?php echo esc_html( $gt_sep ); ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <?php
                    $gt_render_template_card(
                        'global',
                        'homepage',
                        [
                            'title' => $gt_settings['global']['homepage_title'] ?? '',
                            'desc'  => $gt_settings['global']['homepage_desc'] ?? '',
                        ],
                        __( 'Homepage', 'gatetouch-ai-seo' ),
                        __( 'Shown for your front page and blog index. This is the single most important entry in this screen.', 'gatetouch-ai-seo' )
                    );
                    ?>

                    <div class="gatetouch-card">
                        <div class="gatetouch-card__header"><?php esc_html_e( 'Site-wide Social Defaults', 'gatetouch-ai-seo' ); ?></div>
                        <div class="gatetouch-card__body">
                            <div class="gatetouch-sa-field">
                                <label><strong><?php esc_html_e( 'Default Social Share Image', 'gatetouch-ai-seo' ); ?></strong></label>
                                <div class="gatetouch-sa-inline">
                                    <input type="url" name="sa[global][og_default_image]" id="gatetouch-sa-og-image"
                                           value="<?php echo esc_attr( $gt_settings['global']['og_default_image'] ?? '' ); ?>"
                                           class="gatetouch-input-full" />
                                    <button type="button" class="gatetouch-btn gatetouch-btn--secondary gatetouch-upload-btn"
                                            data-target="#gatetouch-sa-og-image"><?php esc_html_e( 'Upload', 'gatetouch-ai-seo' ); ?></button>
                                </div>
                                <p class="gatetouch-sa-hint"><?php esc_html_e( 'Used when a page has no featured image. Recommended size 1200 × 630.', 'gatetouch-ai-seo' ); ?></p>
                            </div>

                            <div class="gatetouch-sa-field gatetouch-sa-field--half">
                                <label><strong><?php esc_html_e( 'Twitter Card Type', 'gatetouch-ai-seo' ); ?></strong></label>
                                <select name="sa[global][twitter_card]" class="gatetouch-select">
                                    <option value="summary_large_image" <?php selected( $gt_settings['global']['twitter_card'] ?? '', 'summary_large_image' ); ?>><?php esc_html_e( 'Large image', 'gatetouch-ai-seo' ); ?></option>
                                    <option value="summary" <?php selected( $gt_settings['global']['twitter_card'] ?? '', 'summary' ); ?>><?php esc_html_e( 'Summary', 'gatetouch-ai-seo' ); ?></option>
                                </select>
                            </div>

                            <div class="gatetouch-sa-field gatetouch-sa-field--half">
                                <label><strong><?php esc_html_e( 'Site X (Twitter) Handle', 'gatetouch-ai-seo' ); ?></strong></label>
                                <input type="text" name="sa[global][twitter_site]" class="gatetouch-input-full"
                                       value="<?php echo esc_attr( $gt_settings['global']['twitter_site'] ?? '' ); ?>" placeholder="@yoursite" />
                            </div>

                            <div class="gatetouch-sa-field">
                                <label class="gatetouch-sa-check">
                                    <input type="checkbox" name="sa[global][homepage_noindex]" value="1"
                                        <?php checked( '1', $gt_settings['global']['homepage_noindex'] ?? '' ); ?> />
                                    <?php esc_html_e( 'No-index the homepage', 'gatetouch-ai-seo' ); ?>
                                </label>
                                <p class="gatetouch-sa-hint"><?php esc_html_e( 'Only enable this for staging sites.', 'gatetouch-ai-seo' ); ?></p>
                            </div>
                        </div>
                    </div>

                <?php elseif ( 'content' === $gt_sub ) : ?>

                    <?php
                    $gt_schema_types = [ 'Article', 'BlogPosting', 'NewsArticle', 'WebPage', 'Product', 'Service', 'Recipe', 'HowTo', 'Course', 'Event', 'VideoObject', 'SoftwareApplication', 'Book', 'JobPosting', 'RealEstateListing', 'None' ];

                    foreach ( get_post_types( [ 'public' => true ], 'objects' ) as $gt_pt ) :
                        $gt_values = GateTouch_Search_Appearance::group( 'content_types', $gt_pt->name );
                        $gt_render_template_card(
                            'content_types',
                            $gt_pt->name,
                            $gt_values,
                            $gt_pt->labels->name,
                            sprintf(
                                /* translators: %s: post type plural label. */
                                __( 'Applies to every %s that has no override of its own.', 'gatetouch-ai-seo' ),
                                strtolower( $gt_pt->labels->name )
                            ),
                            [ 'robots' => true, 'schema' => $gt_schema_types ]
                        );
                    endforeach;
                    ?>

                <?php elseif ( 'taxonomies' === $gt_sub ) : ?>

                    <div class="gatetouch-sa-note">
                        <?php esc_html_e( 'Category and tag pages are frequently the highest-value pages on a site and are almost always left unoptimised. These templates give every one of them a unique, keyword-bearing title and description automatically — and each individual category or tag can still be overridden from its own edit screen.', 'gatetouch-ai-seo' ); ?>
                    </div>

                    <?php
                    $gt_tax_schema = [ 'CollectionPage', 'WebPage', 'ItemList', 'Blog' ];

                    foreach ( get_taxonomies( [ 'public' => true ], 'objects' ) as $gt_tax ) :
                        if ( 'post_format' === $gt_tax->name ) {
                            continue;
                        }
                        $gt_values = GateTouch_Search_Appearance::group( 'taxonomies', $gt_tax->name );
                        $gt_render_template_card(
                            'taxonomies',
                            $gt_tax->name,
                            $gt_values,
                            $gt_tax->labels->name,
                            '',
                            [
                                'robots' => true,
                                'schema' => $gt_tax_schema,
                                'extra'  => function ( $name, $values ) {
                                    ?>
                                    <div class="gatetouch-sa-field">
                                        <label class="gatetouch-sa-check">
                                            <input type="checkbox" name="<?php echo esc_attr( $name ); ?>[noindex_empty]" value="1"
                                                <?php checked( '1', $values['noindex_empty'] ?? '' ); ?> />
                                            <?php esc_html_e( 'No-index terms that have no posts', 'gatetouch-ai-seo' ); ?>
                                        </label>
                                        <p class="gatetouch-sa-hint"><?php esc_html_e( 'Recommended. Empty archives are thin content and dilute crawl budget.', 'gatetouch-ai-seo' ); ?></p>
                                    </div>
                                    <?php
                                },
                            ]
                        );
                    endforeach;
                    ?>

                <?php elseif ( 'archives' === $gt_sub ) : ?>

                    <?php
                    $gt_render_template_card(
                        'archives',
                        'author',
                        GateTouch_Search_Appearance::group( 'archives', 'author' ),
                        __( 'Author Archives', 'gatetouch-ai-seo' ),
                        __( 'Author pages carry your E-E-A-T signals. Each author can add their own bio, credentials and verified profiles from their user profile screen.', 'gatetouch-ai-seo' ),
                        [
                            'robots' => true,
                            'extra'  => function ( $name, $values ) {
                                ?>
                                <div class="gatetouch-sa-field">
                                    <label class="gatetouch-sa-check">
                                        <input type="checkbox" name="<?php echo esc_attr( $name ); ?>[noindex_single_author]" value="1"
                                            <?php checked( '1', $values['noindex_single_author'] ?? '' ); ?> />
                                        <?php esc_html_e( 'No-index author archives when the site has only one author', 'gatetouch-ai-seo' ); ?>
                                    </label>
                                    <p class="gatetouch-sa-hint"><?php esc_html_e( 'Recommended. On a one-author site the author archive duplicates the blog index.', 'gatetouch-ai-seo' ); ?></p>
                                </div>
                                <?php
                            },
                        ]
                    );

                    $gt_render_template_card(
                        'archives',
                        'date',
                        GateTouch_Search_Appearance::group( 'archives', 'date' ),
                        __( 'Date Archives', 'gatetouch-ai-seo' ),
                        __( 'Date archives rarely earn traffic and usually duplicate your blog index. No-indexed by default.', 'gatetouch-ai-seo' ),
                        [ 'robots' => true ]
                    );

                    $gt_render_template_card(
                        'archives',
                        'search',
                        GateTouch_Search_Appearance::group( 'archives', 'search' ),
                        __( 'Search Results', 'gatetouch-ai-seo' ),
                        __( 'Google explicitly asks that internal search results are not indexed. Leave this no-indexed.', 'gatetouch-ai-seo' ),
                        [ 'robots' => true ]
                    );

                    $gt_render_template_card(
                        'archives',
                        'notfound',
                        GateTouch_Search_Appearance::group( 'archives', 'notfound' ),
                        __( '404 — Page Not Found', 'gatetouch-ai-seo' ),
                        '',
                        []
                    );

                    // Post type archives — only meaningful for types that have one.
                    foreach ( get_post_types( [ 'public' => true, 'has_archive' => true ], 'objects' ) as $gt_pt ) :
                        $gt_key = 'pt_' . $gt_pt->name;
                        $gt_render_template_card(
                            'post_type_archives',
                            $gt_key,
                            GateTouch_Search_Appearance::group( 'post_type_archives', $gt_key ),
                            sprintf(
                                /* translators: %s: post type plural label. */
                                __( '%s Archive', 'gatetouch-ai-seo' ),
                                $gt_pt->labels->name
                            ),
                            '',
                            [ 'robots' => true ]
                        );
                    endforeach;
                    ?>

                <?php elseif ( 'schema' === $gt_sub ) : ?>

                    <?php
                    $gt_schema = GateTouch_Schema_Engine::settings();
                    ?>
                    <div class="gatetouch-sa-note">
                        <?php esc_html_e( 'These fields build the publisher entity that appears in every page\'s structured data. Filling them in is what lets Google and AI answer engines recognise your site as a real, identifiable organisation rather than an anonymous website.', 'gatetouch-ai-seo' ); ?>
                    </div>

                    <div class="gatetouch-card">
                        <div class="gatetouch-card__header"><?php esc_html_e( 'Publisher Entity', 'gatetouch-ai-seo' ); ?></div>
                        <div class="gatetouch-card__body">
                            <div class="gatetouch-sa-field gatetouch-sa-field--half">
                                <label><strong><?php esc_html_e( 'This site represents', 'gatetouch-ai-seo' ); ?></strong></label>
                                <select name="sa[schema][org_type]" class="gatetouch-select">
                                    <?php foreach ( [ 'Organization', 'Person', 'NewsMediaOrganization', 'EducationalOrganization', 'GovernmentOrganization', 'NGO', 'OnlineStore' ] as $gt_type ) : ?>
                                        <option value="<?php echo esc_attr( $gt_type ); ?>" <?php selected( $gt_schema['org_type'], $gt_type ); ?>><?php echo esc_html( $gt_type ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="gatetouch-sa-hint"><?php esc_html_e( 'If Local SEO is enabled, the business type set there takes precedence.', 'gatetouch-ai-seo' ); ?></p>
                            </div>

                            <div class="gatetouch-sa-field gatetouch-sa-field--half">
                                <label><strong><?php esc_html_e( 'Name', 'gatetouch-ai-seo' ); ?></strong></label>
                                <input type="text" name="sa[schema][org_name]" class="gatetouch-input-full"
                                       value="<?php echo esc_attr( $gt_schema['org_name'] ); ?>"
                                       placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
                            </div>

                            <div class="gatetouch-sa-field">
                                <label><strong><?php esc_html_e( 'Logo', 'gatetouch-ai-seo' ); ?></strong></label>
                                <div class="gatetouch-sa-inline">
                                    <input type="url" name="sa[schema][org_logo]" id="gatetouch-sa-logo" class="gatetouch-input-full"
                                           value="<?php echo esc_attr( $gt_schema['org_logo'] ); ?>" />
                                    <button type="button" class="gatetouch-btn gatetouch-btn--secondary gatetouch-upload-btn"
                                            data-target="#gatetouch-sa-logo"><?php esc_html_e( 'Upload', 'gatetouch-ai-seo' ); ?></button>
                                </div>
                                <p class="gatetouch-sa-hint"><?php esc_html_e( 'Falls back to your theme logo, then the site icon. Minimum 112 × 112.', 'gatetouch-ai-seo' ); ?></p>
                            </div>

                            <div class="gatetouch-sa-field">
                                <label><strong><?php esc_html_e( 'Description', 'gatetouch-ai-seo' ); ?></strong></label>
                                <textarea name="sa[schema][org_description]" rows="2" class="gatetouch-textarea gatetouch-input-full"><?php echo esc_textarea( $gt_schema['org_description'] ); ?></textarea>
                            </div>

                            <div class="gatetouch-sa-field gatetouch-sa-field--half">
                                <label><strong><?php esc_html_e( 'Phone', 'gatetouch-ai-seo' ); ?></strong></label>
                                <input type="text" name="sa[schema][org_phone]" class="gatetouch-input-full" value="<?php echo esc_attr( $gt_schema['org_phone'] ); ?>" />
                            </div>

                            <div class="gatetouch-sa-field gatetouch-sa-field--half">
                                <label><strong><?php esc_html_e( 'Email', 'gatetouch-ai-seo' ); ?></strong></label>
                                <input type="email" name="sa[schema][org_email]" class="gatetouch-input-full" value="<?php echo esc_attr( $gt_schema['org_email'] ); ?>" />
                            </div>

                            <div class="gatetouch-sa-field gatetouch-sa-field--half">
                                <label><strong><?php esc_html_e( 'Founding Date', 'gatetouch-ai-seo' ); ?></strong></label>
                                <input type="date" name="sa[schema][org_founding_date]" class="gatetouch-input-full" value="<?php echo esc_attr( $gt_schema['org_founding_date'] ); ?>" />
                            </div>
                        </div>
                    </div>

                    <div class="gatetouch-card">
                        <div class="gatetouch-card__header"><?php esc_html_e( 'Automatic Structured Data', 'gatetouch-ai-seo' ); ?></div>
                        <div class="gatetouch-card__body">
                            <?php
                            $gt_schema_toggles = [
                                'enabled'          => [ __( 'Output structured data', 'gatetouch-ai-seo' ), __( 'Master switch for the whole JSON-LD graph.', 'gatetouch-ai-seo' ) ],
                                'website_schema'   => [ __( 'WebSite schema with search box', 'gatetouch-ai-seo' ), __( 'Makes your site eligible for a sitelinks search box.', 'gatetouch-ai-seo' ) ],
                                'breadcrumb_schema' => [ __( 'Breadcrumb schema', 'gatetouch-ai-seo' ), __( 'Replaces the plain URL in search results with a readable path.', 'gatetouch-ai-seo' ) ],
                                'author_schema'    => [ __( 'Author entity on articles', 'gatetouch-ai-seo' ), __( 'Person markup with credentials and verified profiles — a direct E-E-A-T signal.', 'gatetouch-ai-seo' ) ],
                                'faq_automation'   => [ __( 'Detect FAQs in content', 'gatetouch-ai-seo' ), __( 'Turns question headings and their answers into FAQ markup that AI engines quote directly.', 'gatetouch-ai-seo' ) ],
                                'item_list_schema' => [ __( 'ItemList on archives', 'gatetouch-ai-seo' ), __( 'Tells AI engines exactly what is listed on a category or shop page.', 'gatetouch-ai-seo' ) ],
                                'speakable_schema' => [ __( 'Speakable markup', 'gatetouch-ai-seo' ), __( 'Marks the passages voice assistants should read aloud.', 'gatetouch-ai-seo' ) ],
                            ];

                            foreach ( $gt_schema_toggles as $gt_flag => $gt_copy ) :
                                ?>
                                <div class="gatetouch-setting-row">
                                    <div class="gatetouch-setting-label">
                                        <strong><?php echo esc_html( $gt_copy[0] ); ?></strong>
                                        <p><?php echo esc_html( $gt_copy[1] ); ?></p>
                                    </div>
                                    <div class="gatetouch-setting-control">
                                        <label class="gatetouch-sa-check">
                                            <input type="checkbox" name="sa[schema][<?php echo esc_attr( $gt_flag ); ?>]" value="1"
                                                <?php checked( '1', $gt_schema[ $gt_flag ] ?? '' ); ?> />
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                <?php elseif ( 'woocommerce' === $gt_sub ) : ?>

                    <?php $gt_woo = $gt_settings['woocommerce'] ?? []; ?>

                    <div class="gatetouch-sa-note">
                        <?php esc_html_e( 'Product markup drives price, stock and review stars in Google Shopping and organic results. WooCommerce ships a thinner version of this markup; running both produces duplicate product entities, so this plugin replaces it by default.', 'gatetouch-ai-seo' ); ?>
                    </div>

                    <div class="gatetouch-card">
                        <div class="gatetouch-card__header"><?php esc_html_e( 'Product Structured Data', 'gatetouch-ai-seo' ); ?></div>
                        <div class="gatetouch-card__body">
                            <?php
                            $gt_woo_toggles = [
                                'replace_woo_schema' => [ __( 'Replace WooCommerce structured data', 'gatetouch-ai-seo' ), __( 'Recommended. Emits richer Product, Offer and Review markup in a single connected graph.', 'gatetouch-ai-seo' ) ],
                                'review_schema'      => [ __( 'Include product reviews', 'gatetouch-ai-seo' ), __( 'Adds up to five recent reviews plus the aggregate rating.', 'gatetouch-ai-seo' ) ],
                                'noindex_cart'       => [ __( 'No-index cart, checkout and account pages', 'gatetouch-ai-seo' ), __( 'Recommended. These pages should never appear in search results.', 'gatetouch-ai-seo' ) ],
                                'noindex_filtered'   => [ __( 'No-index filtered archive URLs', 'gatetouch-ai-seo' ), __( 'Recommended. Layered-nav and price filters create near-infinite duplicate URLs.', 'gatetouch-ai-seo' ) ],
                            ];

                            foreach ( $gt_woo_toggles as $gt_flag => $gt_copy ) :
                                ?>
                                <div class="gatetouch-setting-row">
                                    <div class="gatetouch-setting-label">
                                        <strong><?php echo esc_html( $gt_copy[0] ); ?></strong>
                                        <p><?php echo esc_html( $gt_copy[1] ); ?></p>
                                    </div>
                                    <div class="gatetouch-setting-control">
                                        <label class="gatetouch-sa-check">
                                            <input type="checkbox" name="sa[woocommerce][<?php echo esc_attr( $gt_flag ); ?>]" value="1"
                                                <?php checked( '1', $gt_woo[ $gt_flag ] ?? '' ); ?> />
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <div class="gatetouch-sa-field">
                                <label><strong><?php esc_html_e( 'Fallback Brand Name', 'gatetouch-ai-seo' ); ?></strong></label>
                                <input type="text" name="sa[woocommerce][default_brand]" class="gatetouch-input-full"
                                       value="<?php echo esc_attr( $gt_woo['default_brand'] ?? '' ); ?>"
                                       placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
                                <p class="gatetouch-sa-hint"><?php esc_html_e( 'Used when a product has no brand taxonomy term. Google requires a brand for merchant listings.', 'gatetouch-ai-seo' ); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="gatetouch-sa-note gatetouch-sa-note--muted">
                        <?php esc_html_e( 'Product, product category and product tag templates are edited on the Post Types and Taxonomies tabs alongside everything else.', 'gatetouch-ai-seo' ); ?>
                    </div>

                <?php else : ?>

                    <?php $gt_adv = $gt_settings['advanced'] ?? []; ?>

                    <div class="gatetouch-card">
                        <div class="gatetouch-card__header"><?php esc_html_e( 'Snippet Controls', 'gatetouch-ai-seo' ); ?></div>
                        <div class="gatetouch-card__body">
                            <p class="gatetouch-sa-desc"><?php esc_html_e( 'These directives decide how much of your content Google may show in a result or an AI Overview. The defaults are deliberately permissive — restricting them almost always reduces visibility.', 'gatetouch-ai-seo' ); ?></p>

                            <div class="gatetouch-sa-field gatetouch-sa-field--half">
                                <label><strong><?php esc_html_e( 'Maximum snippet length', 'gatetouch-ai-seo' ); ?></strong></label>
                                <input type="text" name="sa[advanced][max_snippet]" class="gatetouch-input-full"
                                       value="<?php echo esc_attr( $gt_adv['max_snippet'] ?? '-1' ); ?>" />
                                <p class="gatetouch-sa-hint"><?php esc_html_e( '-1 means no limit. Recommended.', 'gatetouch-ai-seo' ); ?></p>
                            </div>

                            <div class="gatetouch-sa-field gatetouch-sa-field--half">
                                <label><strong><?php esc_html_e( 'Maximum image preview', 'gatetouch-ai-seo' ); ?></strong></label>
                                <select name="sa[advanced][max_image_preview]" class="gatetouch-select">
                                    <?php foreach ( [ 'large', 'standard', 'none' ] as $gt_size ) : ?>
                                        <option value="<?php echo esc_attr( $gt_size ); ?>" <?php selected( $gt_adv['max_image_preview'] ?? 'large', $gt_size ); ?>><?php echo esc_html( $gt_size ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="gatetouch-sa-hint"><?php esc_html_e( '"large" is required for Google Discover eligibility.', 'gatetouch-ai-seo' ); ?></p>
                            </div>

                            <div class="gatetouch-sa-field gatetouch-sa-field--half">
                                <label><strong><?php esc_html_e( 'Maximum video preview', 'gatetouch-ai-seo' ); ?></strong></label>
                                <input type="text" name="sa[advanced][max_video_preview]" class="gatetouch-input-full"
                                       value="<?php echo esc_attr( $gt_adv['max_video_preview'] ?? '-1' ); ?>" />
                            </div>
                        </div>
                    </div>

                    <div class="gatetouch-card">
                        <div class="gatetouch-card__header"><?php esc_html_e( 'Canonicals & Indexing', 'gatetouch-ai-seo' ); ?></div>
                        <div class="gatetouch-card__body">
                            <?php
                            $gt_adv_toggles = [
                                'use_meta_keywords'  => [ __( 'Output meta keywords', 'gatetouch-ai-seo' ), __( 'Ignored by Google. Some regional engines still read them.', 'gatetouch-ai-seo' ) ],
                                'no_paged_canonical' => [ __( 'Point paginated canonicals at page 1', 'gatetouch-ai-seo' ), __( 'Not recommended. Google treats each page of a series as its own URL.', 'gatetouch-ai-seo' ) ],
                                'noindex_paged'      => [ __( 'No-index paginated archives', 'gatetouch-ai-seo' ), __( 'Not recommended. Blocks discovery of older content.', 'gatetouch-ai-seo' ) ],
                                'crawl_cleanup_rss'  => [ __( 'Remove RSS feed links from the head', 'gatetouch-ai-seo' ), __( 'Reduces crawl noise on sites that do not publish a feed.', 'gatetouch-ai-seo' ) ],
                            ];

                            foreach ( $gt_adv_toggles as $gt_flag => $gt_copy ) :
                                ?>
                                <div class="gatetouch-setting-row">
                                    <div class="gatetouch-setting-label">
                                        <strong><?php echo esc_html( $gt_copy[0] ); ?></strong>
                                        <p><?php echo esc_html( $gt_copy[1] ); ?></p>
                                    </div>
                                    <div class="gatetouch-setting-control">
                                        <label class="gatetouch-sa-check">
                                            <input type="checkbox" name="sa[advanced][<?php echo esc_attr( $gt_flag ); ?>]" value="1"
                                                <?php checked( '1', $gt_adv[ $gt_flag ] ?? '' ); ?> />
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="gatetouch-card">
                        <div class="gatetouch-card__header"><?php esc_html_e( 'Image SEO', 'gatetouch-ai-seo' ); ?></div>
                        <div class="gatetouch-card__body">
                            <div class="gatetouch-setting-row">
                                <div class="gatetouch-setting-label">
                                    <strong><?php esc_html_e( 'Redirect attachment pages', 'gatetouch-ai-seo' ); ?></strong>
                                    <p><?php esc_html_e( 'Recommended. Attachment pages are empty pages that compete with your real content.', 'gatetouch-ai-seo' ); ?></p>
                                </div>
                                <div class="gatetouch-setting-control">
                                    <label class="gatetouch-sa-check">
                                        <input type="checkbox" name="sa[image_seo][redirect_attachments]" value="1"
                                            <?php checked( '1', $gt_settings['image_seo']['redirect_attachments'] ?? '' ); ?> />
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endif; ?>

                <div class="gatetouch-form-footer">
                    <button type="submit" class="gatetouch-btn gatetouch-btn--primary">
                        <?php esc_html_e( 'Save Search Appearance', 'gatetouch-ai-seo' ); ?>
                    </button>
                </div>
            </div>

            <?php if ( in_array( $gt_sub, [ 'global', 'content', 'taxonomies', 'archives' ], true ) ) : ?>
                <aside class="gatetouch-sa-side">
                    <div class="gatetouch-card gatetouch-sa-vars">
                        <div class="gatetouch-card__header"><?php esc_html_e( 'Variables', 'gatetouch-ai-seo' ); ?></div>
                        <div class="gatetouch-card__body">
                            <p class="gatetouch-sa-hint">
                                <?php esc_html_e( 'Click a variable to insert it into the field you last used. Variables that have no value on a given page are removed cleanly, along with any separator left behind.', 'gatetouch-ai-seo' ); ?>
                            </p>
                            <?php foreach ( GateTouch_Variables::get_grouped_vars() as $gt_group_key => $gt_group ) : ?>
                                <?php if ( 'woocommerce' === $gt_group_key && ! GateTouch_WooCommerce::is_active() ) { continue; } ?>
                                <div class="gatetouch-sa-vargroup">
                                    <h4><?php echo esc_html( $gt_group['label'] ); ?></h4>
                                    <?php foreach ( $gt_group['vars'] as $gt_var => $gt_desc ) : ?>
                                        <button type="button" class="gatetouch-sa-var" data-var="<?php echo esc_attr( $gt_var ); ?>"
                                                title="<?php echo esc_attr( $gt_desc ); ?>">
                                            <?php echo esc_html( $gt_var ); ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </aside>
            <?php endif; ?>
        </div>
    </form>
</div>
