<?php
defined( 'ABSPATH' ) || exit;

/**
 * SEO fields for categories, tags, product categories and every other public taxonomy.
 *
 * Values are stored as a single array in term meta under GATETOUCH_META_KEY so the
 * shape matches post meta exactly — GateTouch_Search_Appearance can then treat a
 * term override and a post override identically.
 */
class GateTouch_Term_Meta {

    /** Legacy flat keys written by versions before the term editor existed. */
    const LEGACY_TITLE = GATETOUCH_META_KEY . '_title';
    const LEGACY_DESC  = GATETOUCH_META_KEY . '_description';

    public function __construct() {
        add_action( 'admin_init', [ $this, 'register_taxonomy_hooks' ] );
        add_action( 'wp_ajax_gatetouch_generate_term_meta', [ $this, 'ajax_generate' ] );
    }

    /**
     * Every taxonomy a user can realistically want to optimise.
     */
    public static function taxonomies() {
        $taxonomies = get_taxonomies( [ 'public' => true, 'show_ui' => true ], 'objects' );

        $names = [];
        foreach ( $taxonomies as $taxonomy ) {
            if ( 'post_format' === $taxonomy->name ) {
                continue;
            }
            $names[] = $taxonomy->name;
        }

        /**
         * Filter the taxonomies that get GateTouch SEO fields.
         *
         * @param string[] $names Taxonomy slugs.
         */
        return apply_filters( 'gatetouch_seo_taxonomies', $names );
    }

    public function register_taxonomy_hooks() {
        foreach ( self::taxonomies() as $taxonomy ) {
            add_action( "{$taxonomy}_add_form_fields",  [ $this, 'render_add_fields' ], 20 );
            add_action( "{$taxonomy}_edit_form_fields", [ $this, 'render_edit_fields' ], 20, 2 );
            add_action( "created_{$taxonomy}", [ $this, 'save' ], 10 );
            add_action( "edited_{$taxonomy}",  [ $this, 'save' ], 10 );

            add_filter( "manage_edit-{$taxonomy}_columns",          [ $this, 'add_column' ] );
            add_filter( "manage_{$taxonomy}_custom_column",         [ $this, 'render_column' ], 10, 3 );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Data access
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Normalised SEO meta for a term, including legacy single-key fallbacks.
     */
    public static function get( $term_id ) {
        $term_id = (int) $term_id;
        if ( ! $term_id ) {
            return [];
        }

        $meta = get_term_meta( $term_id, GATETOUCH_META_KEY, true );
        $meta = is_array( $meta ) ? $meta : [];

        if ( empty( $meta['meta_title'] ) ) {
            $legacy = get_term_meta( $term_id, self::LEGACY_TITLE, true );
            if ( $legacy ) {
                $meta['meta_title'] = $legacy;
            }
        }
        if ( empty( $meta['meta_description'] ) ) {
            $legacy = get_term_meta( $term_id, self::LEGACY_DESC, true );
            if ( $legacy ) {
                $meta['meta_description'] = $legacy;
            }
        }

        return $meta;
    }

    public static function update( $term_id, array $meta ) {
        update_term_meta( (int) $term_id, GATETOUCH_META_KEY, $meta );
    }

    public static function defaults() {
        return [
            'meta_title'         => '',
            'meta_description'   => '',
            'focus_keyword'      => '',
            'canonical'          => '',
            'noindex'            => '',
            'nofollow'           => '',
            'og_title'           => '',
            'og_description'     => '',
            'og_image'           => '',
            'schema_type'        => '',
            'faqs'               => [],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Rendering
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Fields on the "Add new term" screen (stacked layout, no table row).
     */
    public function render_add_fields( $taxonomy ) {
        $meta = self::defaults();
        ?>
        <div class="form-field gatetouch-term-seo">
            <h2 class="gatetouch-term-seo__heading"><?php esc_html_e( 'SEO — GT AI SEO/GEO/AEO', 'gatetouch-ai-seo' ); ?></h2>
            <p class="gatetouch-term-seo__intro">
                <?php esc_html_e( 'Leave blank to use the site-wide template. Anything you enter here overrides it for this term only.', 'gatetouch-ai-seo' ); ?>
            </p>
            <?php $this->render_fields( $meta, $taxonomy, 0, false ); ?>
        </div>
        <?php
        $this->print_assets();
    }

    /**
     * Fields on the "Edit term" screen (WordPress table layout).
     */
    public function render_edit_fields( $term, $taxonomy ) {
        $meta = wp_parse_args( self::get( $term->term_id ), self::defaults() );
        ?>
        <tr class="form-field">
            <th colspan="2" style="padding-bottom:0;">
                <h2 class="gatetouch-term-seo__heading"><?php esc_html_e( 'SEO — GT AI SEO/GEO/AEO', 'gatetouch-ai-seo' ); ?></h2>
                <p class="gatetouch-term-seo__intro">
                    <?php esc_html_e( 'Leave blank to use the site-wide template. Anything you enter here overrides it for this term only.', 'gatetouch-ai-seo' ); ?>
                </p>
            </th>
        </tr>
        <tr class="form-field gatetouch-term-seo">
            <td colspan="2">
                <?php $this->render_fields( $meta, $taxonomy, (int) $term->term_id, true ); ?>
            </td>
        </tr>
        <?php
        $this->print_assets();
    }

    /**
     * The shared field markup used by both screens.
     */
    private function render_fields( array $meta, $taxonomy, $term_id, $is_edit ) {
        wp_nonce_field( 'gatetouch_save_term_meta', 'gatetouch_term_nonce' );

        $tax_object   = get_taxonomy( $taxonomy );
        $tax_label    = $tax_object ? $tax_object->labels->singular_name : __( 'Term', 'gatetouch-ai-seo' );
        $default_row  = GateTouch_Search_Appearance::group( 'taxonomies', $taxonomy );
        $preview_url  = $term_id ? get_term_link( $term_id, $taxonomy ) : home_url( '/' );
        $preview_url  = is_wp_error( $preview_url ) ? home_url( '/' ) : $preview_url;
        $has_api      = class_exists( 'GateTouch_AI_Engine' ) && GateTouch_AI_Engine::is_api_operational();
        ?>
        <div class="gatetouch-term-panel" data-term-id="<?php echo esc_attr( $term_id ); ?>" data-taxonomy="<?php echo esc_attr( $taxonomy ); ?>">

            <div class="gatetouch-term-preview">
                <div class="gatetouch-term-preview__label"><?php esc_html_e( 'Google preview', 'gatetouch-ai-seo' ); ?></div>
                <div class="gatetouch-term-preview__url"><?php echo esc_html( urldecode( $preview_url ) ); ?></div>
                <div class="gatetouch-term-preview__title" data-preview="title"><?php echo esc_html( $default_row['title'] ?? '' ); ?></div>
                <div class="gatetouch-term-preview__desc" data-preview="desc"><?php echo esc_html( $default_row['desc'] ?? '' ); ?></div>
            </div>

            <?php if ( $has_api ) : ?>
                <p class="gatetouch-term-ai">
                    <button type="button" class="button button-secondary gatetouch-term-generate" <?php disabled( ! $term_id ); ?>>
                        <span class="dashicons dashicons-superhero" style="vertical-align:text-bottom;"></span>
                        <?php esc_html_e( 'Generate with AI', 'gatetouch-ai-seo' ); ?>
                    </button>
                    <?php if ( ! $term_id ) : ?>
                        <span class="gatetouch-term-hint"><?php esc_html_e( 'Save the term first to use AI generation.', 'gatetouch-ai-seo' ); ?></span>
                    <?php endif; ?>
                    <span class="gatetouch-term-status" role="status" aria-live="polite"></span>
                </p>
            <?php endif; ?>

            <p>
                <label for="gatetouch_meta_title"><strong><?php esc_html_e( 'SEO Title', 'gatetouch-ai-seo' ); ?></strong></label>
                <input type="text" name="gatetouch_seo[meta_title]" id="gatetouch_meta_title" class="gatetouch-term-input"
                       value="<?php echo esc_attr( $meta['meta_title'] ); ?>"
                       placeholder="<?php echo esc_attr( $default_row['title'] ?? '' ); ?>"
                       data-counter="title" />
                <span class="gatetouch-term-counter" data-counter-for="title"></span>
                <span class="description">
                    <?php
                    printf(
                        /* translators: %s: taxonomy singular label, e.g. "Category". */
                        esc_html__( 'Aim for 50–60 characters. Variables such as #term#, #term_count#, #site_title# and #sep# are replaced automatically for this %s.', 'gatetouch-ai-seo' ),
                        esc_html( strtolower( $tax_label ) )
                    );
                    ?>
                </span>
            </p>

            <p>
                <label for="gatetouch_meta_description"><strong><?php esc_html_e( 'Meta Description', 'gatetouch-ai-seo' ); ?></strong></label>
                <textarea name="gatetouch_seo[meta_description]" id="gatetouch_meta_description" rows="3" class="gatetouch-term-input"
                          placeholder="<?php echo esc_attr( $default_row['desc'] ?? '' ); ?>"
                          data-counter="desc"><?php echo esc_textarea( $meta['meta_description'] ); ?></textarea>
                <span class="gatetouch-term-counter" data-counter-for="desc"></span>
                <span class="description"><?php esc_html_e( 'Aim for 145–160 characters. Describe what a visitor will find in this archive.', 'gatetouch-ai-seo' ); ?></span>
            </p>

            <p>
                <label for="gatetouch_focus_keyword"><strong><?php esc_html_e( 'Focus Keyword', 'gatetouch-ai-seo' ); ?></strong></label>
                <input type="text" name="gatetouch_seo[focus_keyword]" id="gatetouch_focus_keyword" class="gatetouch-term-input"
                       value="<?php echo esc_attr( $meta['focus_keyword'] ); ?>" />
                <span class="description"><?php esc_html_e( 'The primary term you want this archive to rank for.', 'gatetouch-ai-seo' ); ?></span>
            </p>

            <div class="gatetouch-term-grid">
                <p>
                    <label for="gatetouch_og_image"><strong><?php esc_html_e( 'Social Image URL', 'gatetouch-ai-seo' ); ?></strong></label>
                    <input type="url" name="gatetouch_seo[og_image]" id="gatetouch_og_image" class="gatetouch-term-input"
                           value="<?php echo esc_attr( $meta['og_image'] ); ?>" />
                    <span class="description"><?php esc_html_e( 'Falls back to the term image, then the site default.', 'gatetouch-ai-seo' ); ?></span>
                </p>

                <p>
                    <label for="gatetouch_canonical"><strong><?php esc_html_e( 'Canonical URL', 'gatetouch-ai-seo' ); ?></strong></label>
                    <input type="url" name="gatetouch_seo[canonical]" id="gatetouch_canonical" class="gatetouch-term-input"
                           value="<?php echo esc_attr( $meta['canonical'] ); ?>" />
                    <span class="description"><?php esc_html_e( 'Only set this if this archive duplicates another URL.', 'gatetouch-ai-seo' ); ?></span>
                </p>
            </div>

            <div class="gatetouch-term-grid">
                <p>
                    <label for="gatetouch_schema_type"><strong><?php esc_html_e( 'Schema Type', 'gatetouch-ai-seo' ); ?></strong></label>
                    <select name="gatetouch_seo[schema_type]" id="gatetouch_schema_type" class="gatetouch-term-input">
                        <option value=""><?php esc_html_e( 'Use default (CollectionPage)', 'gatetouch-ai-seo' ); ?></option>
                        <?php
                        $types = [ 'CollectionPage', 'WebPage', 'ItemList', 'Blog', 'FAQPage', 'AboutPage', 'ProfilePage', 'SearchResultsPage' ];
                        foreach ( $types as $type ) {
                            printf(
                                '<option value="%1$s" %2$s>%1$s</option>',
                                esc_attr( $type ),
                                selected( $meta['schema_type'], $type, false )
                            );
                        }
                        ?>
                    </select>
                </p>

                <p class="gatetouch-term-robots">
                    <strong><?php esc_html_e( 'Robots', 'gatetouch-ai-seo' ); ?></strong><br />
                    <label>
                        <input type="checkbox" name="gatetouch_seo[noindex]" value="1" <?php checked( ! empty( $meta['noindex'] ) ); ?> />
                        <?php esc_html_e( 'No-index — hide this archive from search engines', 'gatetouch-ai-seo' ); ?>
                    </label><br />
                    <label>
                        <input type="checkbox" name="gatetouch_seo[nofollow]" value="1" <?php checked( ! empty( $meta['nofollow'] ) ); ?> />
                        <?php esc_html_e( 'No-follow — do not follow links on this archive', 'gatetouch-ai-seo' ); ?>
                    </label>
                </p>
            </div>
        </div>
        <?php
        unset( $is_edit );
    }

    /**
     * Inline CSS/JS for the term editor. Kept self-contained so the fields work on
     * taxonomy screens without loading the full plugin admin bundle.
     */
    private function print_assets() {
        static $printed = false;
        if ( $printed ) {
            return;
        }
        $printed = true;

        $css = '
        .gatetouch-term-seo__heading{font-size:14px;margin:24px 0 4px;padding-top:16px;border-top:1px solid #dcdcde;}
        .gatetouch-term-seo__intro{color:#646970;margin:0 0 12px;font-size:13px;}
        .gatetouch-term-panel label{display:block;margin-bottom:4px;}
        .gatetouch-term-panel .gatetouch-term-input{width:100%;max-width:640px;}
        .gatetouch-term-panel .description{display:block;margin-top:4px;color:#646970;font-size:12px;}
        .gatetouch-term-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:0 24px;}
        .gatetouch-term-preview{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:14px 16px;max-width:640px;margin-bottom:16px;}
        .gatetouch-term-preview__label{font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:#787c82;margin-bottom:8px;font-weight:600;}
        .gatetouch-term-preview__url{color:#202124;font-size:12px;margin-bottom:2px;word-break:break-all;}
        .gatetouch-term-preview__title{color:#1a0dab;font-size:18px;line-height:1.3;margin-bottom:2px;}
        .gatetouch-term-preview__desc{color:#4d5156;font-size:13px;line-height:1.45;}
        .gatetouch-term-counter{font-size:11px;color:#787c82;float:right;}
        .gatetouch-term-counter.is-good{color:#00a32a;}
        .gatetouch-term-counter.is-over{color:#d63638;}
        .gatetouch-term-status{margin-left:8px;font-style:italic;color:#646970;}
        .gatetouch-term-hint{margin-left:8px;color:#787c82;font-size:12px;}
        ';

        wp_register_style( 'gatetouch-term-seo', false, [], GATETOUCH_VERSION );
        wp_enqueue_style( 'gatetouch-term-seo' );
        wp_add_inline_style( 'gatetouch-term-seo', $css );

        $limits = [ 'title' => [ 50, 60 ], 'desc' => [ 145, 160 ] ];

        $js = '(function(){
        var panel=document.querySelector(".gatetouch-term-panel");
        if(!panel)return;
        var limits=' . wp_json_encode( $limits ) . ';
        var titleEl=panel.querySelector(\'[data-preview="title"]\');
        var descEl=panel.querySelector(\'[data-preview="desc"]\');
        function sync(field){
            var input=panel.querySelector(\'[data-counter="\'+field+\'"]\');
            var counter=panel.querySelector(\'[data-counter-for="\'+field+\'"]\');
            if(!input)return;
            var val=input.value||input.getAttribute("placeholder")||"";
            var target=field==="title"?titleEl:descEl;
            if(target)target.textContent=val;
            if(counter){
                var len=input.value.length;
                counter.textContent=len?len+" / "+limits[field][1]:"";
                counter.className="gatetouch-term-counter"+(len>limits[field][1]?" is-over":(len>=limits[field][0]?" is-good":""));
            }
        }
        ["title","desc"].forEach(function(f){
            var input=panel.querySelector(\'[data-counter="\'+f+\'"]\');
            if(input){input.addEventListener("input",function(){sync(f);});sync(f);}
        });
        var btn=panel.querySelector(".gatetouch-term-generate");
        if(btn&&window.gatetouchTerm){
            btn.addEventListener("click",function(){
                var status=panel.querySelector(".gatetouch-term-status");
                btn.disabled=true;
                if(status)status.textContent=gatetouchTerm.generating;
                var body=new URLSearchParams();
                body.append("action","gatetouch_generate_term_meta");
                body.append("nonce",gatetouchTerm.nonce);
                body.append("term_id",panel.getAttribute("data-term-id"));
                body.append("taxonomy",panel.getAttribute("data-taxonomy"));
                fetch(gatetouchTerm.ajaxUrl,{method:"POST",credentials:"same-origin",body:body})
                .then(function(r){return r.json();})
                .then(function(res){
                    btn.disabled=false;
                    if(res&&res.success&&res.data){
                        var t=panel.querySelector(\'[data-counter="title"]\');
                        var d=panel.querySelector(\'[data-counter="desc"]\');
                        var k=panel.querySelector("#gatetouch_focus_keyword");
                        if(t&&res.data.meta_title){t.value=res.data.meta_title;sync("title");}
                        if(d&&res.data.meta_description){d.value=res.data.meta_description;sync("desc");}
                        if(k&&res.data.focus_keyword&&!k.value){k.value=res.data.focus_keyword;}
                        if(status)status.textContent=gatetouchTerm.done;
                    }else{
                        if(status)status.textContent=(res&&res.data)?res.data:gatetouchTerm.error;
                    }
                })
                .catch(function(){btn.disabled=false;if(status)status.textContent=gatetouchTerm.error;});
            });
        }
        })();';

        wp_register_script( 'gatetouch-term-seo', '', [], GATETOUCH_VERSION, true );
        wp_enqueue_script( 'gatetouch-term-seo' );
        wp_localize_script( 'gatetouch-term-seo', 'gatetouchTerm', [
            'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
            'nonce'      => wp_create_nonce( 'gatetouch_ajax' ),
            'generating' => __( 'Generating…', 'gatetouch-ai-seo' ),
            'done'       => __( 'Done — remember to save.', 'gatetouch-ai-seo' ),
            'error'      => __( 'Generation failed. Try again.', 'gatetouch-ai-seo' ),
        ] );
        wp_add_inline_script( 'gatetouch-term-seo', $js );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Saving
    // ─────────────────────────────────────────────────────────────────────────

    public function save( $term_id ) {
        if ( ! isset( $_POST['gatetouch_term_nonce'] ) ) {
            return;
        }
        if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['gatetouch_term_nonce'] ) ), 'gatetouch_save_term_meta' ) ) {
            return;
        }

        $term = get_term( $term_id );
        if ( ! $term instanceof \WP_Term ) {
            return;
        }

        $taxonomy = get_taxonomy( $term->taxonomy );
        if ( ! $taxonomy || ! current_user_can( $taxonomy->cap->edit_terms ) ) {
            return;
        }

        $raw = isset( $_POST['gatetouch_seo'] ) && is_array( $_POST['gatetouch_seo'] )
            ? wp_unslash( $_POST['gatetouch_seo'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised field by field below.
            : [];

        $existing = self::get( $term_id );
        $clean    = self::sanitize( $raw );

        self::update( $term_id, array_merge( $existing, $clean ) );
    }

    public static function sanitize( array $raw ) {
        $schema_types = [ 'CollectionPage', 'WebPage', 'ItemList', 'Blog', 'FAQPage', 'AboutPage', 'ProfilePage', 'SearchResultsPage' ];

        $schema_type = sanitize_text_field( $raw['schema_type'] ?? '' );
        if ( ! in_array( $schema_type, $schema_types, true ) ) {
            $schema_type = '';
        }

        return [
            'meta_title'       => sanitize_text_field( $raw['meta_title'] ?? '' ),
            'meta_description' => sanitize_textarea_field( $raw['meta_description'] ?? '' ),
            'focus_keyword'    => sanitize_text_field( $raw['focus_keyword'] ?? '' ),
            'canonical'        => esc_url_raw( $raw['canonical'] ?? '' ),
            'og_title'         => sanitize_text_field( $raw['og_title'] ?? '' ),
            'og_description'   => sanitize_textarea_field( $raw['og_description'] ?? '' ),
            'og_image'         => esc_url_raw( $raw['og_image'] ?? '' ),
            'schema_type'      => $schema_type,
            'noindex'          => empty( $raw['noindex'] ) ? '' : '1',
            'nofollow'         => empty( $raw['nofollow'] ) ? '' : '1',
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Term list column
    // ─────────────────────────────────────────────────────────────────────────

    public function add_column( $columns ) {
        $columns['gatetouch_seo'] = __( 'SEO', 'gatetouch-ai-seo' );
        return $columns;
    }

    public function render_column( $content, $column, $term_id ) {
        if ( 'gatetouch_seo' !== $column ) {
            return $content;
        }

        $meta = self::get( $term_id );

        if ( ! empty( $meta['noindex'] ) ) {
            return '<span style="color:#d63638;font-weight:600;">' . esc_html__( 'No-index', 'gatetouch-ai-seo' ) . '</span>';
        }

        $filled = ! empty( $meta['meta_title'] ) || ! empty( $meta['meta_description'] );

        return $filled
            ? '<span style="color:#00a32a;font-weight:600;">' . esc_html__( 'Custom', 'gatetouch-ai-seo' ) . '</span>'
            : '<span style="color:#787c82;">' . esc_html__( 'Template', 'gatetouch-ai-seo' ) . '</span>';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AI generation
    // ─────────────────────────────────────────────────────────────────────────

    public function ajax_generate() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );

        $term_id  = isset( $_POST['term_id'] ) ? absint( $_POST['term_id'] ) : 0;
        $taxonomy = isset( $_POST['taxonomy'] ) ? sanitize_key( wp_unslash( $_POST['taxonomy'] ) ) : '';

        $tax_object = get_taxonomy( $taxonomy );
        if ( ! $tax_object || ! current_user_can( $tax_object->cap->edit_terms ) ) {
            wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );
        }

        $term = get_term( $term_id, $taxonomy );
        if ( ! $term instanceof \WP_Term ) {
            wp_send_json_error( __( 'Term not found.', 'gatetouch-ai-seo' ) );
        }

        $result = self::generate_for_term( $term );

        if ( isset( $result['error'] ) ) {
            wp_send_json_error( $result['error'] );
        }

        wp_send_json_success( $result );
    }

    /**
     * Ask the AI provider for an optimised title/description for one term.
     */
    public static function generate_for_term( \WP_Term $term ) {
        if ( ! class_exists( 'GateTouch_AI_Engine' ) || ! GateTouch_AI_Engine::is_api_operational() ) {
            return [ 'error' => __( 'No AI provider configured.', 'gatetouch-ai-seo' ) ];
        }

        $tax_object = get_taxonomy( $term->taxonomy );
        $tax_label  = $tax_object ? $tax_object->labels->singular_name : 'archive';

        // Give the model real signal: what actually lives in this archive.
        $posts = get_posts( [
            'post_type'      => 'any',
            'posts_per_page' => 8,
            'post_status'    => 'publish',
            'tax_query'      => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- bounded to 8 rows for a single admin-triggered request.
                [
                    'taxonomy' => $term->taxonomy,
                    'field'    => 'term_id',
                    'terms'    => $term->term_id,
                ],
            ],
        ] );

        $titles = implode( "\n- ", wp_list_pluck( $posts, 'post_title' ) );

        $system = 'You are an SEO, AEO and GEO strategist optimising archive pages for Google, ChatGPT and Perplexity. Respond ONLY with valid JSON, no markdown fence.';
        $user   = sprintf(
            "Site: %s\nSite tagline: %s\n%s archive name: %s\nExisting description: %s\nNumber of items: %d\nSample items:\n- %s\n\n"
            . "Write metadata for this archive page.\n"
            . "STRICT REQUIREMENTS:\n"
            . "1. meta_title: between 50 and 60 characters. Lead with the archive topic. Do NOT append the site name — that is added automatically.\n"
            . "2. meta_description: between 145 and 160 characters. Say concretely what a visitor finds here and why it is worth browsing. Natural language, no keyword stuffing.\n"
            . "3. focus_keyword: the single head term this archive should rank for.\n"
            . "4. intro: 2 sentences of plain-language answer-style copy suitable for AI search engines to cite.\n\n"
            . 'Return JSON with exactly these keys: {"meta_title":"","meta_description":"","focus_keyword":"","intro":""}',
            wp_strip_all_tags( get_bloginfo( 'name' ) ),
            wp_strip_all_tags( get_bloginfo( 'description' ) ),
            $tax_label,
            $term->name,
            wp_strip_all_tags( $term->description ) ?: '(none)',
            (int) $term->count,
            $titles ?: '(no published items yet)'
        );

        $response = GateTouch_AI_Engine::call( $system, $user, '', 0.6, 600 );

        if ( isset( $response['error'] ) ) {
            return [ 'error' => $response['error'] ];
        }

        $decoded = self::decode_ai_json( $response );
        if ( ! $decoded ) {
            return [ 'error' => __( 'The AI response could not be read. Try again.', 'gatetouch-ai-seo' ) ];
        }

        return [
            'meta_title'       => sanitize_text_field( $decoded['meta_title'] ?? '' ),
            'meta_description' => sanitize_textarea_field( $decoded['meta_description'] ?? '' ),
            'focus_keyword'    => sanitize_text_field( $decoded['focus_keyword'] ?? '' ),
            'intro'            => sanitize_textarea_field( $decoded['intro'] ?? '' ),
        ];
    }

    /**
     * AI providers return the payload in different envelopes and sometimes wrap it
     * in a markdown fence — normalise all of that into an array.
     */
    private static function decode_ai_json( $response ) {
        $raw = '';

        if ( is_string( $response ) ) {
            $raw = $response;
        } elseif ( is_array( $response ) ) {
            $raw = $response['content'] ?? ( $response['text'] ?? ( $response['message'] ?? '' ) );
            if ( ! $raw && isset( $response['meta_title'] ) ) {
                return $response;
            }
        }

        if ( ! is_string( $raw ) || '' === $raw ) {
            return null;
        }

        $raw = trim( preg_replace( '/^```(?:json)?|```$/m', '', $raw ) );

        $decoded = json_decode( $raw, true );
        if ( is_array( $decoded ) ) {
            return $decoded;
        }

        // Last resort: pull the first JSON object out of a chatty response.
        if ( preg_match( '/\{.*\}/s', $raw, $m ) ) {
            $decoded = json_decode( $m[0], true );
            if ( is_array( $decoded ) ) {
                return $decoded;
            }
        }

        return null;
    }
}
