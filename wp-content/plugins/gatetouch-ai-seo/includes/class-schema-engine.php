<?php
defined( 'ABSPATH' ) || exit;

/**
 * Structured data engine.
 *
 * Emits ONE connected @graph per page rather than a pile of disconnected JSON-LD
 * blocks. Every node carries a stable @id and references the others, which is what
 * lets Google resolve the site into a knowledge-graph entity and lets AI answer
 * engines attribute a claim to a publisher and an author.
 *
 * Node coverage:
 *   Organization / LocalBusiness / Person   (the publisher entity)
 *   WebSite                                 (+ SearchAction)
 *   WebPage / CollectionPage / ProfilePage / SearchResultsPage / ItemPage
 *   BreadcrumbList                          (every page type, not just posts)
 *   Article / BlogPosting / NewsArticle / Product / Service / …
 *   Person                                  (author entity with sameAs + credentials)
 *   FAQPage / HowTo                         (answer-engine surfaces)
 *   ItemList                                (archive inventories)
 */
class GateTouch_Schema_Engine {

    public function __construct() {
        add_action( 'wp_head', [ $this, 'output_schema' ], 20 );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Settings
    // ─────────────────────────────────────────────────────────────────────────

    public static function settings() {
        $defaults = [
            'enabled'           => '1',
            'org_type'          => 'Organization',
            'org_name'          => '',
            'org_logo'          => '',
            'org_description'   => '',
            'org_phone'         => '',
            'org_email'         => '',
            'org_founding_date' => '',
            'org_vat_id'        => '',
            'breadcrumb_schema' => '1',
            'website_schema'    => '1',
            'author_schema'     => '1',
            'faq_automation'    => '1',
            'item_list_schema'  => '1',
            'speakable_schema'  => '1',
        ];

        $saved = get_option( 'gatetouch_schema_settings', [] );
        $saved = is_array( $saved ) ? $saved : [];

        // Self-heal installs damaged before 1.3.1: the registered sanitizer for
        // this option treated it as flags-only, so every Organization field was
        // rewritten to the literal string '0'. Those are never valid values for
        // these fields, so read them back as empty rather than showing "0" in
        // the UI and emitting it into the schema graph.
        foreach ( [ 'org_name', 'org_logo', 'org_description', 'org_phone', 'org_email', 'org_founding_date', 'org_vat_id' ] as $field ) {
            if ( isset( $saved[ $field ] ) && '0' === (string) $saved[ $field ] ) {
                $saved[ $field ] = '';
            }
        }
        if ( isset( $saved['org_type'] ) && '0' === (string) $saved['org_type'] ) {
            $saved['org_type'] = 'Organization';
        }

        return wp_parse_args( $saved, $defaults );
    }

    public static function is_enabled() {
        $settings = self::settings();
        return '1' === ( $settings['enabled'] ?? '1' );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Output
    // ─────────────────────────────────────────────────────────────────────────

    public function output_schema() {
        if ( class_exists( 'GateTouch_Core' ) && GateTouch_Core::has_conflicts() ) {
            return;
        }
        if ( is_feed() || is_embed() || ! self::is_enabled() ) {
            return;
        }

        $graph = self::build_graph();
        if ( empty( $graph ) ) {
            return;
        }

        echo "\n<!-- GT SEO/GEO/AEO — Structured Data -->\n";
        GateTouch_Helpers::print_json_ld(
            [
                '@context' => 'https://schema.org',
                '@graph'   => array_values( $graph ),
            ],
            'gatetouch-schema'
        );
    }

    /**
     * Assemble the complete graph for the current request.
     */
    public static function build_graph() {
        $settings = self::settings();
        $ctx      = GateTouch_Search_Appearance::context();
        $meta     = GateTouch_Search_Appearance::object_meta( $ctx );

        $graph = [];

        // A hand-written schema on the object replaces everything we would infer.
        if ( ! empty( $meta['custom_schema'] ) ) {
            $custom = json_decode( $meta['custom_schema'], true );
            if ( is_array( $custom ) ) {
                // Accept either a bare node or a full graph.
                if ( isset( $custom['@graph'] ) && is_array( $custom['@graph'] ) ) {
                    return $custom['@graph'];
                }
                $graph[] = $custom;
            }
        }

        // 1. Publisher entity — present on every page so references resolve.
        $graph[] = self::organization_node( $settings );

        // 2. WebSite.
        if ( '1' === $settings['website_schema'] ) {
            $graph[] = self::website_node();
        }

        // 3. The page itself.
        $graph[] = self::webpage_node( $ctx, $meta );

        // 4. Breadcrumbs.
        if ( '1' === $settings['breadcrumb_schema'] ) {
            $breadcrumb = self::breadcrumb_node( $ctx );
            if ( $breadcrumb ) {
                $graph[] = $breadcrumb;
            }
        }

        // 5. Primary entity of the page.
        $graph = array_merge( $graph, self::primary_entity_nodes( $ctx, $meta, $settings ) );

        // 6. FAQ.
        $faq = self::faq_node( $ctx, $meta, $settings );
        if ( $faq ) {
            $graph[] = $faq;
        }

        // 7. Archive inventory.
        if ( '1' === $settings['item_list_schema'] ) {
            $list = self::item_list_node( $ctx );
            if ( $list ) {
                $graph[] = $list;
            }
        }

        $graph = array_values( array_filter( $graph ) );

        /**
         * Filter the complete schema graph before output.
         *
         * @param array $graph Graph nodes.
         * @param array $ctx   Resolved page context.
         */
        return apply_filters( 'gatetouch_schema_graph', $graph, $ctx );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Publisher entity
    // ─────────────────────────────────────────────────────────────────────────

    public static function organization_id() {
        return home_url( '/#organization' );
    }

    public static function website_id() {
        return home_url( '/#website' );
    }

    /**
     * Organization, Person or LocalBusiness — whichever the site represents.
     */
    public static function organization_node( array $settings = null ) {
        $settings = $settings ?: self::settings();
        $sa       = GateTouch_Search_Appearance::settings();
        $kg       = $sa['kg'] ?? [];
        $local    = class_exists( 'GateTouch_Local_SEO' ) ? GateTouch_Local_SEO::get_settings() : [];

        $type = $settings['org_type'] ?: ( $kg['type'] ?? 'Organization' );

        // A configured local business is the same entity, just a more specific type.
        $is_local = ( $local['enabled'] ?? 'no' ) === 'yes';
        if ( $is_local && ! empty( $local['business_type'] ) ) {
            $type = $local['business_type'];
        }

        $name = $settings['org_name']
            ?: ( $kg['name'] ?? '' )
            ?: ( $is_local ? ( $local['business_name'] ?? '' ) : '' )
            ?: get_bloginfo( 'name' );

        $node = [
            '@type' => $type,
            '@id'   => self::organization_id(),
            'name'  => wp_strip_all_tags( $name ),
            'url'   => home_url( '/' ),
        ];

        $description = $settings['org_description'] ?: ( $kg['description'] ?? '' ) ?: get_bloginfo( 'description' );
        if ( $description ) {
            $node['description'] = wp_strip_all_tags( $description );
        }

        if ( ! empty( $kg['alt_name'] ) ) {
            $node['alternateName'] = wp_strip_all_tags( $kg['alt_name'] );
        }

        // Logo doubles as the entity image — Google requires ImageObject here.
        $logo = $settings['org_logo'] ?: ( $kg['logo'] ?? '' );
        if ( ! $logo ) {
            $logo = self::site_logo_url();
        }
        if ( $logo ) {
            $node['logo'] = [
                '@type'      => 'ImageObject',
                '@id'        => home_url( '/#logo' ),
                'url'        => $logo,
                'contentUrl' => $logo,
                'caption'    => wp_strip_all_tags( $name ),
            ];
            $node['image'] = [ '@id' => home_url( '/#logo' ) ];
        }

        $phone = $settings['org_phone'] ?: ( $kg['phone'] ?? '' ) ?: ( $local['phone'] ?? '' );
        if ( $phone ) {
            $node['telephone'] = $phone;
        }

        $email = $settings['org_email'] ?: ( $kg['email'] ?? '' ) ?: ( $local['email'] ?? '' );
        if ( $email ) {
            $node['email'] = $email;
        }

        if ( ! empty( $settings['org_founding_date'] ) ) {
            $node['foundingDate'] = $settings['org_founding_date'];
        }
        if ( ! empty( $settings['org_vat_id'] ) ) {
            $node['vatID'] = $settings['org_vat_id'];
        }

        // Physical location and hours make the entity eligible for local packs.
        if ( $is_local ) {
            $address = array_filter( [
                '@type'           => 'PostalAddress',
                'streetAddress'   => $local['address'] ?? '',
                'addressLocality' => $local['city'] ?? '',
                'addressRegion'   => $local['state'] ?? '',
                'postalCode'      => $local['zip'] ?? '',
                'addressCountry'  => $local['country'] ?? '',
            ] );
            if ( count( $address ) > 1 ) {
                $node['address'] = $address;
            }
            if ( ! empty( $local['opening_hours'] ) ) {
                $node['openingHours'] = $local['opening_hours'];
            }
            if ( ! empty( $local['price_range'] ) ) {
                $node['priceRange'] = $local['price_range'];
            }
        }

        // sameAs is how a search engine reconciles this site with a known entity.
        $same_as = self::organization_same_as();
        if ( $same_as ) {
            $node['sameAs'] = $same_as;
        }

        if ( $phone ) {
            $node['contactPoint'] = [
                '@type'       => 'ContactPoint',
                'telephone'   => $phone,
                'contactType' => 'customer service',
                'url'         => home_url( '/' ),
            ];
        }

        /**
         * Filter the publisher entity node.
         *
         * @param array $node Organization/Person/LocalBusiness node.
         */
        return apply_filters( 'gatetouch_organization_schema', $node );
    }

    private static function site_logo_url() {
        $logo_id = (int) get_theme_mod( 'custom_logo' );
        if ( $logo_id ) {
            $url = wp_get_attachment_image_url( $logo_id, 'full' );
            if ( $url ) {
                return $url;
            }
        }

        $icon = get_site_icon_url( 512 );
        return $icon ?: '';
    }

    private static function organization_same_as() {
        $social  = get_option( 'gatetouch_social_settings', [] );
        $profiles = [];

        if ( ! empty( $social['profiles'] ) && is_array( $social['profiles'] ) ) {
            $profiles = array_values( $social['profiles'] );
        }

        // Individual handle fields used elsewhere in the plugin.
        if ( ! empty( $social['twitter']['site_handle'] ) ) {
            $profiles[] = 'https://x.com/' . ltrim( $social['twitter']['site_handle'], '@' );
        }

        $profiles = array_filter( array_map( 'esc_url_raw', array_filter( $profiles ) ) );

        return array_values( array_unique( $profiles ) );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // WebSite / WebPage
    // ─────────────────────────────────────────────────────────────────────────

    public static function website_node() {
        $node = [
            '@type'       => 'WebSite',
            '@id'         => self::website_id(),
            'url'         => home_url( '/' ),
            'name'        => wp_strip_all_tags( get_bloginfo( 'name' ) ),
            'description' => wp_strip_all_tags( get_bloginfo( 'description' ) ),
            'publisher'   => [ '@id' => self::organization_id() ],
            'inLanguage'  => self::language(),
        ];

        // Sitelinks search box.
        $node['potentialAction'] = [
            [
                '@type'       => 'SearchAction',
                'target'      => [
                    '@type'       => 'EntryPoint',
                    'urlTemplate' => home_url( '/?s={search_term_string}' ),
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];

        return $node;
    }

    /**
     * The WebPage-family node describing the current URL.
     */
    public static function webpage_node( array $ctx, array $meta ) {
        $url  = GateTouch_Search_Appearance::canonical();
        $type = self::webpage_type( $ctx, $meta );

        $node = [
            '@type'      => $type,
            '@id'        => $url . '#webpage',
            'url'        => $url,
            'name'       => GateTouch_Search_Appearance::title(),
            'isPartOf'   => [ '@id' => self::website_id() ],
            'about'      => [ '@id' => self::organization_id() ],
            'inLanguage' => self::language(),
        ];

        $description = GateTouch_Search_Appearance::description();
        if ( $description ) {
            $node['description'] = $description;
        }

        $settings = self::settings();
        if ( '1' === $settings['breadcrumb_schema'] ) {
            $node['breadcrumb'] = [ '@id' => $url . '#breadcrumb' ];
        }

        // Dates — meaningful for singular content only.
        if ( $ctx['object'] instanceof \WP_Post ) {
            $node['datePublished'] = get_the_date( 'c', $ctx['object'] );
            $node['dateModified']  = get_the_modified_date( 'c', $ctx['object'] );
        }

        $image = self::primary_image( $ctx );
        if ( $image ) {
            $node['primaryImageOfPage'] = [ '@id' => $url . '#primaryimage' ];
        }

        // Speakable marks the parts an assistant should read aloud.
        if ( '1' === $settings['speakable_schema'] && in_array( $ctx['type'], [ 'singular', 'front_page' ], true ) ) {
            $node['speakable'] = [
                '@type'       => 'SpeakableSpecification',
                'cssSelector' => [ 'h1', '.entry-content > p:first-of-type' ],
            ];
        }

        return $node;
    }

    private static function webpage_type( array $ctx, array $meta ) {
        if ( ! empty( $meta['schema_type'] ) && in_array( $meta['schema_type'], [ 'CollectionPage', 'WebPage', 'ProfilePage', 'SearchResultsPage', 'AboutPage', 'FAQPage', 'ItemPage' ], true ) ) {
            return $meta['schema_type'];
        }

        switch ( $ctx['type'] ) {
            case 'term':
            case 'post_type_archive':
            case 'date':
            case 'blog_home':
                return 'CollectionPage';
            case 'author':
                return 'ProfilePage';
            case 'search':
                return 'SearchResultsPage';
            case 'singular':
                if ( $ctx['object'] instanceof \WP_Post && 'product' === $ctx['object']->post_type ) {
                    return 'ItemPage';
                }
                return 'WebPage';
        }

        return 'WebPage';
    }

    private static function primary_image( array $ctx ) {
        $social = GateTouch_Search_Appearance::social();
        return $social['image'] ?? '';
    }

    public static function language() {
        return str_replace( '_', '-', get_locale() );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Breadcrumbs
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * A complete breadcrumb trail for any page type.
     */
    public static function breadcrumb_node( array $ctx ) {
        $settings   = GateTouch_Breadcrumbs::settings();
        $home_label = $settings['home_label'] ?? __( 'Home', 'gatetouch-ai-seo' );

        $trail = [ [ 'name' => $home_label, 'url' => home_url( '/' ) ] ];

        switch ( $ctx['type'] ) {
            case 'front_page':
                // A single-item breadcrumb carries no information.
                return null;

            case 'singular':
                $trail = array_merge( $trail, self::post_trail( $ctx['object'] ) );
                break;

            case 'term':
                $trail = array_merge( $trail, self::term_trail( $ctx['object'] ) );
                break;

            case 'author':
                if ( $ctx['object'] instanceof \WP_User ) {
                    $trail[] = [ 'name' => $ctx['object']->display_name, 'url' => get_author_posts_url( $ctx['object']->ID ) ];
                }
                break;

            case 'post_type_archive':
                $pt     = get_post_type_object( substr( $ctx['key'], 3 ) );
                $pt_url = $pt ? get_post_type_archive_link( $pt->name ) : '';
                if ( $pt ) {
                    $trail[] = [ 'name' => $pt->labels->name, 'url' => $pt_url ?: GateTouch_Search_Appearance::current_url() ];
                }
                break;

            case 'date':
                $trail = array_merge( $trail, self::date_trail() );
                break;

            case 'search':
                /* translators: %s: the search term entered by the visitor. */
                $trail[] = [ 'name' => sprintf( __( 'Search results for "%s"', 'gatetouch-ai-seo' ), get_search_query() ), 'url' => GateTouch_Search_Appearance::current_url() ];
                break;

            case 'blog_home':
                if ( $ctx['object_id'] ) {
                    $trail[] = [ 'name' => get_the_title( $ctx['object_id'] ), 'url' => get_permalink( $ctx['object_id'] ) ];
                }
                break;

            case 'notfound':
                return null;
        }

        if ( count( $trail ) < 2 ) {
            return null;
        }

        $items    = [];
        $position = 1;
        foreach ( $trail as $crumb ) {
            if ( empty( $crumb['name'] ) ) {
                continue;
            }
            $item = [
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => wp_strip_all_tags( $crumb['name'] ),
            ];
            // The final crumb intentionally omits `item` — it is the current page.
            if ( ! empty( $crumb['url'] ) && $position <= count( $trail ) ) {
                $item['item'] = $crumb['url'];
            }
            $items[] = $item;
        }

        return [
            '@type'           => 'BreadcrumbList',
            '@id'             => GateTouch_Search_Appearance::canonical() . '#breadcrumb',
            'itemListElement' => $items,
        ];
    }

    /**
     * Ancestors of a post: page hierarchy, or the primary term hierarchy.
     */
    private static function post_trail( $post ) {
        if ( ! $post instanceof \WP_Post ) {
            return [];
        }

        $trail = [];

        // Shop → category for products.
        if ( 'product' === $post->post_type && function_exists( 'wc_get_page_id' ) ) {
            $shop_id = (int) wc_get_page_id( 'shop' );
            if ( $shop_id > 0 ) {
                $trail[] = [ 'name' => get_the_title( $shop_id ), 'url' => get_permalink( $shop_id ) ];
            }
        }

        if ( is_post_type_hierarchical( $post->post_type ) ) {
            foreach ( array_reverse( get_post_ancestors( $post ) ) as $ancestor_id ) {
                $trail[] = [ 'name' => get_the_title( $ancestor_id ), 'url' => get_permalink( $ancestor_id ) ];
            }
        } else {
            $taxonomy = self::primary_taxonomy_for( $post->post_type );
            if ( $taxonomy ) {
                $term = GateTouch_Variables::primary_term( $post, $taxonomy );
                if ( $term ) {
                    $trail = array_merge( $trail, self::term_trail( $term ) );
                }
            }
        }

        $trail[] = [ 'name' => get_the_title( $post ), 'url' => get_permalink( $post ) ];

        return $trail;
    }

    /**
     * A term and all of its ancestors.
     */
    private static function term_trail( $term ) {
        if ( ! $term instanceof \WP_Term ) {
            return [];
        }

        $trail = [];

        // Products live under the shop page.
        if ( 'product_cat' === $term->taxonomy && function_exists( 'wc_get_page_id' ) ) {
            $shop_id = (int) wc_get_page_id( 'shop' );
            if ( $shop_id > 0 ) {
                $trail[] = [ 'name' => get_the_title( $shop_id ), 'url' => get_permalink( $shop_id ) ];
            }
        }

        foreach ( array_reverse( (array) get_ancestors( $term->term_id, $term->taxonomy, 'taxonomy' ) ) as $ancestor_id ) {
            $ancestor = get_term( $ancestor_id, $term->taxonomy );
            if ( $ancestor instanceof \WP_Term ) {
                $link    = get_term_link( $ancestor );
                $trail[] = [ 'name' => $ancestor->name, 'url' => is_wp_error( $link ) ? '' : $link ];
            }
        }

        $link    = get_term_link( $term );
        $trail[] = [ 'name' => $term->name, 'url' => is_wp_error( $link ) ? '' : $link ];

        return $trail;
    }

    private static function date_trail() {
        $year  = (int) get_query_var( 'year' );
        $month = (int) get_query_var( 'monthnum' );
        $day   = (int) get_query_var( 'day' );

        $trail = [];

        if ( $year ) {
            $trail[] = [ 'name' => (string) $year, 'url' => get_year_link( $year ) ];
        }
        if ( $month ) {
            $trail[] = [ 'name' => date_i18n( 'F', mktime( 0, 0, 0, $month, 1, $year ?: (int) gmdate( 'Y' ) ) ), 'url' => get_month_link( $year, $month ) ];
        }
        if ( $day ) {
            $trail[] = [ 'name' => (string) $day, 'url' => get_day_link( $year, $month, $day ) ];
        }

        return $trail;
    }

    /**
     * The taxonomy that best represents a post type's hierarchy in breadcrumbs.
     */
    private static function primary_taxonomy_for( $post_type ) {
        if ( 'post' === $post_type ) {
            return 'category';
        }
        if ( 'product' === $post_type ) {
            return 'product_cat';
        }

        $taxonomies = get_object_taxonomies( $post_type, 'objects' );
        foreach ( $taxonomies as $taxonomy ) {
            if ( $taxonomy->hierarchical && $taxonomy->public ) {
                return $taxonomy->name;
            }
        }

        return '';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Primary entity
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * The Article / Product / Service node plus its author entity.
     */
    private static function primary_entity_nodes( array $ctx, array $meta, array $settings ) {
        if ( 'singular' !== $ctx['type'] || ! $ctx['object'] instanceof \WP_Post ) {
            return [];
        }

        $post  = $ctx['object'];
        $nodes = [];
        $url   = GateTouch_Search_Appearance::canonical();

        // Products get the full commerce treatment.
        if ( 'product' === $post->post_type && GateTouch_WooCommerce::is_active() ) {
            $product = GateTouch_WooCommerce::product_schema( $post->ID );
            if ( $product ) {
                $product['mainEntityOfPage'] = [ '@id' => $url . '#webpage' ];
                $nodes[] = $product;
            }
            $image = self::image_node( $ctx, $url );
            if ( $image ) {
                $nodes[] = $image;
            }
            return $nodes;
        }

        $type = self::article_type( $post, $meta );

        // Pages are usually not articles — emitting Article for a contact page is
        // a common cause of "unparsable structured data" warnings.
        if ( 'WebPage' === $type || 'None' === $type ) {
            $image = self::image_node( $ctx, $url );
            if ( $image ) {
                $nodes[] = $image;
            }
            return $nodes;
        }

        $node = [
            '@type'            => $type,
            '@id'              => $url . '#article',
            'isPartOf'         => [ '@id' => $url . '#webpage' ],
            'mainEntityOfPage' => [ '@id' => $url . '#webpage' ],
            'headline'         => GateTouch_Helpers::truncate( wp_strip_all_tags( get_the_title( $post ) ), 110, '' ),
            'name'             => wp_strip_all_tags( get_the_title( $post ) ),
            'datePublished'    => get_the_date( 'c', $post ),
            'dateModified'     => get_the_modified_date( 'c', $post ),
            'publisher'        => [ '@id' => self::organization_id() ],
            'inLanguage'       => self::language(),
        ];

        $description = GateTouch_Search_Appearance::description();
        if ( $description ) {
            $node['description'] = $description;
        }

        // Author entity.
        if ( '1' === $settings['author_schema'] ) {
            $author = self::author_node( (int) $post->post_author );
            if ( $author ) {
                $node['author'] = [ '@id' => $author['@id'] ];
                $nodes[]        = $author;
            }
        }

        $image = self::image_node( $ctx, $url );
        if ( $image ) {
            $node['image'] = [ '@id' => $url . '#primaryimage' ];
            $nodes[]       = $image;
        }

        // Section and keywords give topical context.
        $primary = GateTouch_Variables::primary_term( $post, 'category' );
        if ( $primary ) {
            $node['articleSection'] = $primary->name;
        }

        $keywords = GateTouch_Search_Appearance::keywords( $ctx );
        if ( $keywords ) {
            $node['keywords'] = $keywords;
        }

        // Word count and reading time are used by Discover and AI summarisers.
        $word_count = str_word_count( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ) );
        if ( $word_count > 0 ) {
            $node['wordCount'] = $word_count;
            $node['timeRequired'] = 'PT' . max( 1, (int) ceil( $word_count / 200 ) ) . 'M';
        }

        $comment_count = (int) $post->comment_count;
        if ( $comment_count > 0 ) {
            $node['commentCount'] = $comment_count;
        }

        array_unshift( $nodes, $node );

        return $nodes;
    }

    /**
     * Resolve the schema type for a post: explicit override, then per-post-type
     * template, then a sensible default.
     */
    private static function article_type( \WP_Post $post, array $meta ) {
        if ( ! empty( $meta['schema_type'] ) ) {
            return $meta['schema_type'];
        }
        if ( ! empty( $meta['ai_detected_schema'] ) ) {
            return $meta['ai_detected_schema'];
        }

        $config = GateTouch_Search_Appearance::group( 'content_types', $post->post_type );
        if ( ! empty( $config['schema_type'] ) ) {
            return $config['schema_type'];
        }

        return 'page' === $post->post_type ? 'WebPage' : 'Article';
    }

    /**
     * Person node for an author, including everything that signals expertise.
     */
    public static function author_node( $user_id ) {
        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return null;
        }

        $meta = GateTouch_User_Meta::get( $user_id );

        $node = [
            '@type' => 'Person',
            '@id'   => home_url( '/#/schema/person/' . $user_id ),
            'name'  => wp_strip_all_tags( $user->display_name ),
            'url'   => get_author_posts_url( $user_id ),
        ];

        if ( $user->description ) {
            $node['description'] = wp_strip_all_tags( $user->description );
        }

        $avatar = get_avatar_url( $user_id, [ 'size' => 512 ] );
        if ( $avatar ) {
            $node['image'] = [
                '@type'      => 'ImageObject',
                'url'        => $avatar,
                'contentUrl' => $avatar,
                'caption'    => wp_strip_all_tags( $user->display_name ),
            ];
        }

        if ( ! empty( $meta['job_title'] ) ) {
            $node['jobTitle'] = $meta['job_title'];
        }

        if ( ! empty( $meta['expertise'] ) ) {
            $node['knowsAbout'] = array_values( array_filter( array_map( 'trim', explode( ',', $meta['expertise'] ) ) ) );
        }

        if ( ! empty( $meta['credentials'] ) ) {
            $node['hasCredential'] = [
                '@type'               => 'EducationalOccupationalCredential',
                'credentialCategory'  => $meta['credentials'],
            ];
        }

        $same_as = GateTouch_User_Meta::same_as( $user_id );
        if ( $same_as ) {
            $node['sameAs'] = $same_as;
        }

        $node['worksFor'] = [ '@id' => self::organization_id() ];

        return $node;
    }

    private static function image_node( array $ctx, $url ) {
        $image = self::primary_image( $ctx );
        if ( ! $image ) {
            return null;
        }

        $node = [
            '@type'      => 'ImageObject',
            '@id'        => $url . '#primaryimage',
            'url'        => $image,
            'contentUrl' => $image,
        ];

        // Real dimensions when the image is in the media library.
        $attachment_id = attachment_url_to_postid( $image );
        if ( $attachment_id ) {
            $data = wp_get_attachment_image_src( $attachment_id, 'full' );
            if ( $data ) {
                $node['width']  = (int) $data[1];
                $node['height'] = (int) $data[2];
            }
            $alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
            if ( $alt ) {
                $node['caption'] = $alt;
            }
        }

        return $node;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FAQ + ItemList
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * FAQPage built from explicit FAQ items, or extracted from question headings.
     */
    private static function faq_node( array $ctx, array $meta, array $settings ) {
        $faqs = ( ! empty( $meta['faqs'] ) && is_array( $meta['faqs'] ) ) ? $meta['faqs'] : [];

        if ( empty( $faqs ) && '1' === $settings['faq_automation'] && $ctx['object'] instanceof \WP_Post ) {
            $faqs = self::extract_faqs( $ctx['object']->post_content );
        }

        if ( empty( $faqs ) ) {
            return null;
        }

        $questions = [];
        foreach ( $faqs as $faq ) {
            $question = trim( wp_strip_all_tags( $faq['question'] ?? '' ) );
            $answer   = trim( wp_strip_all_tags( $faq['answer'] ?? '' ) );

            if ( '' === $question || '' === $answer ) {
                continue;
            }

            $questions[] = [
                '@type'          => 'Question',
                'name'           => $question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => $answer,
                ],
            ];
        }

        if ( empty( $questions ) ) {
            return null;
        }

        $url = GateTouch_Search_Appearance::canonical();

        return [
            '@type'      => 'FAQPage',
            '@id'        => $url . '#faq',
            'isPartOf'   => [ '@id' => $url . '#webpage' ],
            'mainEntity' => $questions,
        ];
    }

    /**
     * Pull Q&A pairs out of content: an H2/H3 ending in "?" followed by prose.
     */
    private static function extract_faqs( $content ) {
        $content = do_blocks( $content );
        $faqs    = [];

        if ( preg_match_all( '/<h[23][^>]*>(.*?\?)\s*<\/h[23]>\s*((?:<p[^>]*>.*?<\/p>\s*){1,2})/is', $content, $matches, PREG_SET_ORDER ) ) {
            foreach ( $matches as $match ) {
                $question = trim( wp_strip_all_tags( $match[1] ) );
                $answer   = trim( wp_strip_all_tags( $match[2] ) );

                // Skip answers too short to be useful in an answer engine.
                if ( strlen( $answer ) < 40 ) {
                    continue;
                }

                $faqs[] = [ 'question' => $question, 'answer' => $answer ];

                if ( count( $faqs ) >= 10 ) {
                    break;
                }
            }
        }

        return $faqs;
    }

    /**
     * ItemList describing what is actually listed on an archive page.
     */
    private static function item_list_node( array $ctx ) {
        if ( ! in_array( $ctx['type'], [ 'term', 'post_type_archive', 'blog_home', 'date' ], true ) ) {
            return null;
        }

        global $wp_query;
        if ( empty( $wp_query->posts ) ) {
            return null;
        }

        $items    = [];
        $position = 1;

        foreach ( array_slice( $wp_query->posts, 0, 30 ) as $post ) {
            if ( ! $post instanceof \WP_Post ) {
                continue;
            }
            $items[] = [
                '@type'    => 'ListItem',
                'position' => $position++,
                'url'      => get_permalink( $post ),
                'name'     => wp_strip_all_tags( get_the_title( $post ) ),
            ];
        }

        if ( empty( $items ) ) {
            return null;
        }

        $url = GateTouch_Search_Appearance::canonical();

        return [
            '@type'           => 'ItemList',
            '@id'             => $url . '#itemlist',
            'mainEntityOfPage' => [ '@id' => $url . '#webpage' ],
            'numberOfItems'   => count( $items ),
            'itemListElement' => $items,
        ];
    }
}
