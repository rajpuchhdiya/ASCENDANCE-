<?php
/**
 * GateTouch — Complete Sitemap Generator
 */
defined( 'ABSPATH' ) || exit;

/**
 * Generates XML, RSS, HTML, and AI-readable sitemap documents.
 */
class GateTouch_Sitemap {

    private $opts;

    public function __construct() {
        $this->opts = wp_parse_args(
            get_option( 'gatetouch_sitemap_settings', [] ),
            $this->defaults()
        );

        add_action( 'init',              [ $this, 'register_rewrites' ] );
        add_filter( 'query_vars',        [ $this, 'add_query_vars' ] );
        add_action( 'template_redirect', [ $this, 'handle_request' ], 1 );
        add_action( 'save_post',         [ $this, 'on_publish' ], 10, 2 );
        add_action( 'wp_head',           [ $this, 'add_sitemap_link' ] );
        add_shortcode( 'gatetouch_sitemap', [ $this, 'render_html_sitemap' ] );

        // Disable WordPress Core sitemaps
        add_filter( 'wp_sitemaps_enabled', '__return_false' );
    }

    private function defaults() {
        return [
            'enable_posts_sitemap'      => 'yes',
            'enable_pages_sitemap'      => 'yes',
            'enable_products_sitemap'   => 'yes',
            'enable_categories_sitemap' => 'yes',
            'enable_tags_sitemap'       => 'yes',
            'enable_prod_cats_sitemap'  => 'yes',
            'enable_prod_tags_sitemap'  => 'yes',
            'enable_cpt_sitemaps'       => 'yes',
            'links_per_sitemap'         => 1000,
            'post_types'             => [ 'post', 'page' ],
            'taxonomies'             => [ 'category', 'post_tag' ],
            'exclude_ids'            => '',
            'rss_sitemap' => [
                'enabled' => 'no',
                'limit'   => 50,
            ],
            'html_sitemap' => [
                'enabled' => 'no',
                'display' => 'page',
            ],
            'llms_txt' => [
                'enabled'     => 'no',
                'title'       => get_bloginfo( 'name' ),
                'description' => get_bloginfo( 'description' ),
                'limit'       => 1000,
                'post_types'  => [ 'post', 'page' ],
            ],
        ];
    }

    public static function register_rewrites() {
        add_rewrite_rule( '^sitemap\.xml$',       'index.php?gatetouch_sitemap=index', 'top' );
        add_rewrite_rule( '^sitemap_index\.xml$', 'index.php?gatetouch_sitemap=index', 'top' );
        add_rewrite_rule( '^sitemap-rss\.xml$',   'index.php?gatetouch_sitemap=rss',   'top' );
        add_rewrite_rule( '^gatetouch-indexnow-key\.txt$', 'index.php?gatetouch_indexnow_key=1', 'top' );
        
        add_rewrite_rule(
            '^sitemap-([a-zA-Z0-9_-]+)\.xml$',
            'index.php?gatetouch_sitemap=$matches[1]',
            'top'
        );
    }

    public function add_query_vars( $vars ) {
        $vars[] = 'gatetouch_sitemap';
        $vars[] = 'gatetouch_indexnow_key';
        return $vars;
    }

    public function handle_request() {
        if ( get_query_var( 'gatetouch_indexnow_key' ) ) {
            $this->serve_indexnow_key();
        }

        $sitemap = get_query_var( 'gatetouch_sitemap' );
        if ( ! $sitemap ) return;

        $sitemap_enabled_raw = $this->opts['enabled'] ?? null;
        $sitemap_is_on       = ( $sitemap_enabled_raw === null ) || ! in_array( $sitemap_enabled_raw, [ 'no', '0', '', false ], true );
        if ( $sitemap !== 'llms' && ! $sitemap_is_on ) return;

        if ( ! defined( 'DONOTCACHEPAGE' ) ) {
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- Standard WordPress cache-bypass constant used by cache plugins.
            define( 'DONOTCACHEPAGE', true );
        }

        while ( ob_get_level() ) {
            ob_end_clean();
        }

        if ( $sitemap === 'llms' ) {
            header( 'Content-Type: text/plain; charset=UTF-8' );
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Plain-text document is sanitized while being assembled.
            echo $this->build_llms_txt();
            exit;
        }

        header( 'HTTP/1.1 200 OK' );
        header( 'Content-Type: application/xml; charset=UTF-8' );
        header( 'X-Robots-Tag: noindex, follow' );

        switch ( $sitemap ) {
            case 'index':
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- XML document is escaped while being assembled.
                echo $this->build_sitemap_index();
                break;
            case 'rss':
                if ( ( $this->opts['rss_sitemap']['enabled'] ?? 'no' ) !== 'yes' ) {
                    status_header( 404 );
                    exit;
                }
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- XML document is escaped while being assembled.
                echo $this->build_rss_sitemap();
                break;
            default:
                $cpt = sanitize_key( $sitemap );
                if ( post_type_exists( $cpt ) ) {
                    // Check granular settings
                    $is_enabled = true;
                    if ( $cpt === 'post' ) {
                        $is_enabled = ( $this->opts['enable_posts_sitemap'] ?? 'yes' ) === 'yes';
                    } elseif ( $cpt === 'page' ) {
                        $is_enabled = ( $this->opts['enable_pages_sitemap'] ?? 'yes' ) === 'yes';
                    } else {
                        $is_enabled = ( $this->opts['enable_cpt_sitemaps'] ?? 'yes' ) === 'yes';
                    }

                    if ( ! $is_enabled ) {
                        status_header( 404 );
                        exit;
                    }

                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- XML document is escaped while being assembled.
                    echo $this->build_post_type_sitemap( $cpt );
                } elseif ( taxonomy_exists( $sitemap ) ) {
                    // Check granular taxonomy settings
                    $tax_map = [
                        'category'    => 'enable_categories_sitemap',
                        'post_tag'    => 'enable_tags_sitemap',
                        'product_cat' => 'enable_prod_cats_sitemap',
                        'product_tag' => 'enable_prod_tags_sitemap',
                    ];
                    $is_enabled = ( $this->opts[ $tax_map[$sitemap] ?? '' ] ?? 'yes' ) === 'yes';

                    if ( ! $is_enabled ) {
                        status_header( 404 );
                        exit;
                    }

                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- XML document is escaped while being assembled.
                    echo $this->build_taxonomy_sitemap( $sitemap );
                } else {
                    status_header( 404 );
                    echo '<?xml version="1.0" encoding="UTF-8"?><error>Sitemap not found</error>';
                }
                break;
        }
        exit;
    }

    private function build_sitemap_index() {
        $home = trailingslashit( home_url() );
        $xml  = $this->xml_declaration();
        $xml .= $this->xsl_stylesheet();
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // 1. Posts Sitemap
        if ( ( $this->opts['enable_posts_sitemap'] ?? 'yes' ) === 'yes' ) {
            if ( (int) wp_count_posts( 'post' )->publish > 0 ) {
                $xml .= $this->index_entry( $home . 'sitemap-post.xml', $this->get_last_modified( 'post' ) );
            }
        }

        // 2. Pages Sitemap
        if ( ( $this->opts['enable_pages_sitemap'] ?? 'yes' ) === 'yes' ) {
            if ( (int) wp_count_posts( 'page' )->publish > 0 ) {
                $xml .= $this->index_entry( $home . 'sitemap-page.xml', $this->get_last_modified( 'page' ) );
            }
        }

        // 3. Products Sitemap (WooCommerce)
        if ( ( $this->opts['enable_products_sitemap'] ?? 'yes' ) === 'yes' && post_type_exists( 'product' ) ) {
            if ( (int) wp_count_posts( 'product' )->publish > 0 ) {
                $xml .= $this->index_entry( $home . 'sitemap-product.xml', $this->get_last_modified( 'product' ) );
            }
        }

        // 4. Taxonomies
        $taxonomies = [
            'category'    => 'enable_categories_sitemap',
            'post_tag'    => 'enable_tags_sitemap',
            'product_cat' => 'enable_prod_cats_sitemap',
            'product_tag' => 'enable_prod_tags_sitemap',
        ];

        foreach ( $taxonomies as $tax => $opt_key ) {
            if ( ( $this->opts[ $opt_key ] ?? 'yes' ) === 'yes' && taxonomy_exists( $tax ) ) {
                $terms = get_terms( [ 'taxonomy' => $tax, 'number' => 1, 'hide_empty' => true ] );
                if ( ! empty( $terms ) ) {
                    $xml .= $this->index_entry( $home . 'sitemap-' . $tax . '.xml', gmdate( 'c' ) );
                }
            }
        }

        // 5. CPT Sitemaps
        if ( ( $this->opts['enable_cpt_sitemaps'] ?? 'yes' ) === 'yes' ) {
            $cpts = get_post_types( [ 'public' => true, '_builtin' => false ] );
            $exclude = [ 'product' ]; // Handled separately
            foreach ( $cpts as $pt ) {
                if ( in_array( $pt, $exclude ) ) continue;
                if ( (int) wp_count_posts( $pt )->publish > 0 ) {
                    $xml .= $this->index_entry( $home . 'sitemap-' . $pt . '.xml', $this->get_last_modified( $pt ) );
                }
            }
        }

        if ( ( $this->opts['rss_sitemap']['enabled'] ?? 'no' ) === 'yes' ) {
            $xml .= $this->index_entry( $home . 'sitemap-rss.xml', gmdate('c') );
        }

        $xml .= '</sitemapindex>';
        return $xml;
    }

    private function build_rss_sitemap() {
        $limit = $this->opts['rss_sitemap']['limit'] ?? 50;
        $posts = get_posts( [
            'post_type'      => $this->opts['post_types'] ?? [ 'post', 'page' ],
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'orderby'        => 'modified',
            'order'          => 'DESC',
        ] );

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/">' . "\n";
        $xml .= '  <channel>' . "\n";
        $xml .= '    <title>' . esc_html( get_bloginfo('name') ) . ' RSS Sitemap</title>' . "\n";
        $xml .= '    <link>' . esc_url( home_url('/') ) . '</link>' . "\n";
        $xml .= '    <description>Latest content updates</description>' . "\n";

        foreach ( $posts as $post ) {
            $xml .= "    <item>\n";
            $xml .= "      <title>" . esc_html( $post->post_title ) . "</title>\n";
            $xml .= "      <link>" . esc_url( get_permalink( $post->ID ) ) . "</link>\n";
            $xml .= "      <pubDate>" . mysql2date( 'D, d M Y H:i:s +0000', $post->post_modified_gmt, false ) . "</pubDate>\n";
            $xml .= "      <guid isPermaLink=\"false\">" . esc_html( $post->ID ) . '@' . esc_url( home_url( '/' ) ) . "</guid>\n";
            $xml .= "    </item>\n";
        }

        $xml .= '  </channel>' . "\n";
        $xml .= '</rss>';
        return $xml;
    }

    private function build_llms_txt() {
        if ( ( $this->opts['llms_txt']['enabled'] ?? 'no' ) !== 'yes' ) {
            status_header( 404 );
            return 'llms.txt is disabled.';
        }

        $title = wp_strip_all_tags( $this->opts['llms_txt']['title'] ?? get_bloginfo( 'name' ) );
        $desc  = wp_strip_all_tags( $this->opts['llms_txt']['description'] ?? get_bloginfo( 'description' ) );
        $pts   = $this->opts['llms_txt']['post_types'] ?? [ 'post', 'page' ];
        $limit = $this->opts['llms_txt']['limit'] ?? 1000;

        $out  = '# ' . sanitize_text_field( $title ) . "\n\n";
        $out .= '> ' . sanitize_text_field( $desc ) . "\n\n";
        $out .= "## Content\n\n";

        foreach ( $pts as $pt ) {
            $obj   = get_post_type_object( $pt );
            $posts = get_posts( [
                'post_type'      => $pt,
                'post_status'    => 'publish',
                'posts_per_page' => $limit,
                'orderby'        => 'modified',
                'order'          => 'DESC',
            ] );

            if ( ! empty( $posts ) ) {
                $out .= '### ' . sanitize_text_field( $obj->label ) . "\n\n";
                foreach ( $posts as $p ) {
                    $out .= '- [' . sanitize_text_field( wp_strip_all_tags( $p->post_title ) ) . '](' . esc_url_raw( get_permalink( $p->ID ) ) . ")\n";
                }
                $out .= "\n";
            }
        }
        return $out;
    }

    public function render_html_sitemap() {
        if ( ( $this->opts['html_sitemap']['enabled'] ?? 'no' ) !== 'yes' ) return '';
        $pts = $this->opts['post_types'] ?? [ 'post', 'page' ];
        $html = '<div class="gatetouch-html-sitemap">';
        foreach ( $pts as $pt ) {
            $obj = get_post_type_object( $pt );
            $posts = get_posts( [
                'post_type'      => $pt,
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => $this->opts['html_sitemap']['sort_order'] ?? 'publish_date',
                'order'          => 'DESC',
            ] );
            if ( ! empty( $posts ) ) {
                $html .= '<div class="gatetouch-sitemap-section"><h3>' . esc_html( $obj->label ) . '</h3><ul>';
                foreach ( $posts as $p ) {
                    $html .= '<li><a href="' . esc_url( get_permalink( $p->ID ) ) . '">' . esc_html( $p->post_title ) . '</a></li>';
                }
                $html .= '</ul></div>';
            }
        }
        $html .= '</div>';
        return $html;
    }

    private function build_post_type_sitemap( $post_type ) {
        $posts = get_posts( [
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'posts_per_page' => $this->opts['links_per_sitemap'] ?? 1000,
            'orderby'        => 'modified',
            'order'          => 'DESC',
        ] );

        $xml  = $this->xml_declaration();
        $xml .= $this->xsl_stylesheet();
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ( $posts as $post ) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . esc_url( get_permalink( $post->ID ) ) . "</loc>\n";
            $xml .= "    <lastmod>" . esc_html( get_the_modified_date( 'c', $post->ID ) ) . "</lastmod>\n";
            $xml .= "    <changefreq>" . esc_html( $this->opts['sitemap_changefreq'] ?? 'weekly' ) . "</changefreq>\n";
            $xml .= "    <priority>" . esc_html( $this->opts['sitemap_priority'] ?? '0.8' ) . "</priority>\n";
            $xml .= "  </url>\n\n";
        }
        $xml .= '</urlset>';
        return $xml;
    }

    private function build_taxonomy_sitemap( $taxonomy ) {
        $terms = get_terms( [
            'taxonomy'   => $taxonomy,
            'hide_empty' => true,
            'number'     => $this->opts['links_per_sitemap'] ?? 1000,
        ] );

        $xml  = $this->xml_declaration();
        $xml .= $this->xsl_stylesheet();
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ( $terms as $term ) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . esc_url( get_term_link( $term ) ) . "</loc>\n";
            $xml .= "    <lastmod>" . gmdate( 'c' ) . "</lastmod>\n"; // Taxonomies don't have a specific modified date easily accessible
            $xml .= "    <changefreq>weekly</changefreq>\n";
            $xml .= "    <priority>0.6</priority>\n";
            $xml .= "  </url>\n\n";
        }
        $xml .= '</urlset>';
        return $xml;
    }

    private function index_entry( $loc, $lastmod ) {
        return "  <sitemap>\n    <loc>" . esc_url( $loc ) . "</loc>\n    <lastmod>" . esc_html( $lastmod ) . "</lastmod>\n  </sitemap>\n\n";
    }

    private function xml_declaration() {
        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    }

    private function xsl_stylesheet() {
        return '<?xml-stylesheet type="text/xsl" href="' . esc_url( GATETOUCH_URL . 'assets/sitemap.xsl' ) . '"?>' . "\n";
    }

    private function get_last_modified( $post_type ) {
        $posts = get_posts( [ 'post_type' => $post_type, 'post_status' => 'publish', 'posts_per_page' => 1, 'orderby' => 'modified' ] );
        return ! empty( $posts ) ? get_the_modified_date( 'c', $posts[0]->ID ) : gmdate( 'c' );
    }

    public function on_publish( $post_id, \WP_Post $post ) {
        if ( $post->post_status !== 'publish' || wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) return;
        if ( ( $this->opts['enable_ping'] ?? 'yes' ) !== 'yes' ) return;
        if ( ! is_post_type_viewable( $post->post_type ) ) return;

        $permalink = get_permalink( $post_id );
        if ( ! $permalink ) return;

        GateTouch_Queue::add( 'ping_search', [ 'urls' => [ $permalink ] ] );
    }

    public function add_sitemap_link() {
        echo '<link rel="sitemap" type="application/xml" title="Sitemap" href="' . esc_url( home_url( '/sitemap.xml' ) ) . '">' . "\n";
    }

    public function ping_search_engines( $urls = [] ) {
        $sitemap_url = home_url( '/sitemap.xml' );
        $urls        = $this->normalize_ping_urls( $urls );

        if ( empty( $urls ) ) {
            $urls = [ home_url( '/' ), $sitemap_url ];
        }

        $results = [
            'google' => [
                'status'  => 'skipped',
                'message' => __( 'Google no longer supports anonymous sitemap ping requests. Keep the sitemap discoverable through robots.txt and Search Console.', 'gatetouch-ai-seo' ),
            ],
            'bing'   => [
                'status'  => 'skipped',
                'message' => __( 'Bing no longer supports anonymous sitemap ping requests. IndexNow is used when an API key is configured.', 'gatetouch-ai-seo' ),
            ],
        ];

        if ( ( $this->opts['enable_ping'] ?? 'yes' ) !== 'yes' ) {
            $results['indexnow'] = [
                'status'  => 'skipped',
                'message' => __( 'Auto-ping is disabled in GT SEO/GEO/AEO sitemap settings.', 'gatetouch-ai-seo' ),
            ];
        } else {
            $indexnow_key = $this->get_indexnow_key();
            if ( $indexnow_key ) {
                $results['indexnow'] = $this->submit_indexnow_urls( $urls, $indexnow_key );
            } else {
                $results['indexnow'] = [
                    'status'  => 'skipped',
                    'message' => __( 'Add an IndexNow API key in Webmaster Tools to notify supported search engines automatically.', 'gatetouch-ai-seo' ),
                ];
            }
        }

        /**
         * Allows site-specific integrations to submit the sitemap or changed URLs
         * through authenticated provider APIs.
         */
        $results = apply_filters( 'gatetouch_sitemap_ping_results', $results, $sitemap_url, $urls );

        update_option( 'gatetouch_last_sitemap_ping', [
            'time'        => current_time( 'mysql' ),
            'sitemap_url' => $sitemap_url,
            'urls'        => $urls,
            'results'     => $results,
        ] );

        return [
            'success'     => true,
            'sitemap_url' => $sitemap_url,
            'urls'        => $urls,
            'results'     => $results,
        ];
    }

    private function normalize_ping_urls( $urls ) {
        $urls = is_array( $urls ) ? $urls : [ $urls ];
        $urls = array_map( 'esc_url_raw', $urls );
        $urls = array_filter( $urls );

        return array_values( array_unique( $urls ) );
    }

    private function get_indexnow_key() {
        $settings = get_option( 'gatetouch_webmaster_settings', [] );
        return isset( $settings['indexnow_key'] ) ? sanitize_text_field( $settings['indexnow_key'] ) : '';
    }

    private function get_indexnow_key_location() {
        return add_query_arg( 'gatetouch_indexnow_key', '1', home_url( '/' ) );
    }

    private function serve_indexnow_key() {
        $key = $this->get_indexnow_key();
        if ( ! $key ) {
            status_header( 404 );
            exit;
        }

        nocache_headers();
        header( 'Content-Type: text/plain; charset=UTF-8' );
        echo esc_html( $key );
        exit;
    }

    private function submit_indexnow_urls( $urls, $key ) {
        $host = wp_parse_url( home_url( '/' ), PHP_URL_HOST );
        if ( ! $host ) {
            return [
                'status'  => 'error',
                'message' => __( 'Could not determine the site host for IndexNow.', 'gatetouch-ai-seo' ),
            ];
        }

        $response = wp_remote_post( 'https://api.indexnow.org/indexnow', [
            'timeout'   => 15,
            'sslverify' => true,
            'headers'   => [
                'Content-Type' => 'application/json; charset=utf-8',
            ],
            'body'      => wp_json_encode( [
                'host'        => $host,
                'key'         => $key,
                'keyLocation' => $this->get_indexnow_key_location(),
                'urlList'     => $urls,
            ] ),
        ] );

        if ( is_wp_error( $response ) ) {
            return [
                'status'  => 'error',
                'message' => $response->get_error_message(),
            ];
        }

        $code = (int) wp_remote_retrieve_response_code( $response );
        if ( in_array( $code, [ 200, 202 ], true ) ) {
            return [
                'status'  => 'success',
                'code'    => $code,
                'message' => __( 'Submitted URLs to IndexNow.', 'gatetouch-ai-seo' ),
            ];
        }

        return [
            'status'  => 'error',
            'code'    => $code,
            'message' => wp_trim_words( wp_remote_retrieve_body( $response ), 30, '...' ),
        ];
    }
}
