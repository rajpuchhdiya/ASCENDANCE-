<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- migration reads source plugin storage directly.
// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table identifiers are built from $wpdb->prefix.

/**
 * Yoast SEO migration adapter.
 *
 * Yoast keeps the same data in two places. Modern versions maintain the
 * wp_yoast_indexable table, which covers posts, terms, users AND the special
 * system pages (404, search, home, date archive) in one schema. Older data — and
 * anything Yoast has not reindexed — lives in postmeta. This adapter reads the
 * indexable table when it exists and falls back to postmeta, so it works whether
 * the site is on Yoast 14 or 26, active or already deactivated.
 */
class GateTouch_Migration_Yoast extends GateTouch_Migration_Source {

    public function slug() {
        return 'yoast';
    }

    public function label() {
        return 'Yoast SEO';
    }

    private function indexable_table() {
        global $wpdb;
        return $wpdb->prefix . 'yoast_indexable';
    }

    private function has_indexables() {
        return $this->table_exists( $this->indexable_table() );
    }

    public function is_detected() {
        if ( defined( 'WPSEO_VERSION' ) ) {
            return true;
        }
        if ( get_option( 'wpseo_titles' ) || get_option( 'wpseo' ) ) {
            return true;
        }
        if ( $this->has_indexables() ) {
            return true;
        }

        return $this->count_postmeta( array_keys( $this->post_meta_map() ) ) > 0;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Field maps
    // ─────────────────────────────────────────────────────────────────────────

    private function post_meta_map() {
        return [
            '_yoast_wpseo_title'                 => 'meta_title',
            '_yoast_wpseo_metadesc'              => 'meta_description',
            '_yoast_wpseo_focuskw'               => 'focus_keyword',
            '_yoast_wpseo_canonical'             => 'canonical',
            '_yoast_wpseo_opengraph-title'       => 'og_title',
            '_yoast_wpseo_opengraph-description' => 'og_description',
            '_yoast_wpseo_opengraph-image'       => 'og_image',
            '_yoast_wpseo_twitter-title'         => 'twitter_title',
            '_yoast_wpseo_twitter-description'   => 'twitter_description',
            '_yoast_wpseo_twitter-image'         => 'twitter_image',
            '_yoast_wpseo_bctitle'               => 'breadcrumb_title',
            '_yoast_wpseo_schema_page_type'      => 'schema_page_type',
            '_yoast_wpseo_schema_article_type'   => 'schema_type',
            '_yoast_wpseo_meta-robots-noindex'   => 'noindex_raw',
            '_yoast_wpseo_meta-robots-nofollow'  => 'nofollow_raw',
            '_yoast_wpseo_meta-robots-adv'       => 'robots_adv',
        ];
    }

    /**
     * Yoast's %%variable%% vocabulary mapped onto ours.
     */
    protected function variable_map() {
        return [
            '%%title%%'             => '#title#',
            '%%sitename%%'          => '#site_title#',
            '%%sitedesc%%'          => '#tagline#',
            '%%sep%%'               => '#sep#',
            '%%page%%'              => '#page#',
            '%%pagenumber%%'        => '#pagenumber#',
            '%%pagetotal%%'         => '#pagetotal#',
            '%%excerpt%%'           => '#excerpt#',
            '%%excerpt_only%%'      => '#excerpt_only#',
            '%%date%%'              => '#date#',
            '%%modified%%'          => '#modified#',
            '%%currentdate%%'       => '#currentdate#',
            '%%currentyear%%'       => '#year#',
            '%%currentmonth%%'      => '#month#',
            '%%currentday%%'        => '#day#',
            '%%name%%'              => '#author_name#',
            '%%user_description%%'  => '#author_bio#',
            '%%category%%'          => '#categories#',
            '%%primary_category%%'  => '#category#',
            '%%category_title%%'    => '#term#',
            '%%tag%%'               => '#tags#',
            '%%term_title%%'        => '#term#',
            '%%term_description%%'  => '#term_description#',
            '%%term_hierarchy%%'    => '#term_parent#',
            '%%tax_title%%'         => '#term#',
            '%%searchphrase%%'      => '#search_query#',
            '%%focuskw%%'           => '#focus_keyword#',
            '%%id%%'                => '#post_id#',
            '%%parent_title%%'      => '#parent_title#',
            '%%pt_single%%'         => '#post_type#',
            '%%pt_plural%%'         => '#post_type_plural#',
            '%%archive_title%%'     => '#archive_title#',
            '%%caption%%'           => '#excerpt#',
            '%%wc_price%%'          => '#wc_price#',
            '%%wc_sku%%'            => '#wc_sku#',
            '%%wc_brand%%'          => '#wc_brand#',
            '%%wc_shortdesc%%'      => '#wc_short_desc#',
            // Not supported — remove rather than leak the token.
            '%%page_number%%'       => '#pagenumber#',
            '%%sitename_or_title%%' => '#site_title#',
        ];
    }

    /**
     * Yoast custom fields: %%cf_<key>%% and taxonomy terms %%ct_<taxonomy>%%.
     */
    protected function translate_dynamic( $text ) {
        $text = preg_replace( '/%%cf_([A-Za-z0-9_\-]+)%%/', '#cf_$1#', $text );
        $text = preg_replace( '/%%ct_([A-Za-z0-9_\-]+)%%/', '#tax_$1#', $text );
        $text = preg_replace( '/%%ct_desc_([A-Za-z0-9_\-]+)%%/', '#tax_$1#', $text );

        return $text;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Counting
    // ─────────────────────────────────────────────────────────────────────────

    public function counts() {
        return [
            'posts'     => $this->count_objects( 'post' ),
            'terms'     => $this->count_objects( 'term' ),
            'users'     => $this->count_objects( 'user' ),
            'redirects' => count( $this->get_redirects() ),
        ];
    }

    /**
     * Count records that carry at least one non-empty SEO field.
     */
    private function count_objects( $type ) {
        global $wpdb;

        if ( $this->has_indexables() ) {
            $table = $this->indexable_table();
            return (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$table}
                     WHERE object_type = %s
                       AND ( ( title IS NOT NULL AND title != '' )
                          OR ( description IS NOT NULL AND description != '' )
                          OR ( canonical IS NOT NULL AND canonical != '' )
                          OR ( open_graph_title IS NOT NULL AND open_graph_title != '' )
                          OR ( open_graph_description IS NOT NULL AND open_graph_description != '' )
                          OR ( twitter_title IS NOT NULL AND twitter_title != '' )
                          OR ( primary_focus_keyword IS NOT NULL AND primary_focus_keyword != '' )
                          OR is_robots_noindex = 1 )",
                    $type
                )
            );
        }

        if ( 'post' === $type ) {
            return $this->count_postmeta( array_keys( $this->post_meta_map() ) );
        }
        if ( 'term' === $type ) {
            return count( $this->legacy_term_meta() );
        }
        if ( 'user' === $type ) {
            return $this->count_usermeta( [ 'wpseo_title', 'wpseo_metadesc', 'wpseo_noindex_author' ] );
        }

        return 0;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Posts
    // ─────────────────────────────────────────────────────────────────────────

    public function get_posts( $offset, $limit ) {
        if ( $this->has_indexables() ) {
            return $this->get_from_indexables( 'post', $offset, $limit );
        }

        $rows = $this->fetch_postmeta_page( array_keys( $this->post_meta_map() ), $offset, $limit );
        $out  = [];

        foreach ( $rows as $post_id => $meta ) {
            $normalised = $this->normalise_postmeta( $meta );
            if ( $normalised ) {
                $out[ $post_id ] = $normalised;
            }
        }

        return $out;
    }

    /**
     * Map a row of raw Yoast postmeta onto the normalised shape.
     */
    private function normalise_postmeta( array $meta ) {
        $out = [];

        foreach ( $this->post_meta_map() as $source_key => $target_key ) {
            if ( ! isset( $meta[ $source_key ] ) || '' === $meta[ $source_key ] ) {
                continue;
            }
            $out[ $target_key ] = $meta[ $source_key ];
        }

        // Titles and descriptions are templates, not literals.
        foreach ( [ 'meta_title', 'meta_description', 'og_title', 'og_description', 'twitter_title', 'twitter_description' ] as $key ) {
            if ( isset( $out[ $key ] ) ) {
                $out[ $key ] = $this->translate( $out[ $key ] );
            }
        }

        // Yoast robots: '1' = noindex, '2' = index, '' = inherit the default.
        if ( isset( $out['noindex_raw'] ) ) {
            $out['noindex'] = ( '1' === (string) $out['noindex_raw'] ) ? '1' : '';
            unset( $out['noindex_raw'] );
        }
        if ( isset( $out['nofollow_raw'] ) ) {
            $out['nofollow'] = ( '1' === (string) $out['nofollow_raw'] ) ? '1' : '';
            unset( $out['nofollow_raw'] );
        }

        // meta-robots-adv is a comma list: noarchive, noimageindex, nosnippet.
        if ( isset( $out['robots_adv'] ) ) {
            $advanced = array_map( 'trim', explode( ',', (string) $out['robots_adv'] ) );
            foreach ( [ 'noarchive', 'noimageindex', 'nosnippet' ] as $directive ) {
                if ( in_array( $directive, $advanced, true ) ) {
                    $out[ $directive ] = '1';
                }
            }
            unset( $out['robots_adv'] );
        }

        // Prefer the more specific article type over the generic page type.
        if ( empty( $out['schema_type'] ) && ! empty( $out['schema_page_type'] ) ) {
            $out['schema_type'] = $out['schema_page_type'];
        }
        unset( $out['schema_page_type'] );

        if ( isset( $out['og_image'] ) ) {
            $out['og_image'] = $this->image_url( $out['og_image'] );
        }
        unset( $out['twitter_image'] );

        return $this->compact_meta( $out );
    }

    /**
     * Read normalised records straight out of wp_yoast_indexable.
     */
    private function get_from_indexables( $object_type, $offset, $limit ) {
        global $wpdb;

        $table = $this->indexable_table();

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table}
                 WHERE object_type = %s
                   AND ( ( title IS NOT NULL AND title != '' )
                      OR ( description IS NOT NULL AND description != '' )
                      OR ( canonical IS NOT NULL AND canonical != '' )
                      OR ( open_graph_title IS NOT NULL AND open_graph_title != '' )
                      OR ( open_graph_description IS NOT NULL AND open_graph_description != '' )
                      OR ( twitter_title IS NOT NULL AND twitter_title != '' )
                      OR ( primary_focus_keyword IS NOT NULL AND primary_focus_keyword != '' )
                      OR is_robots_noindex = 1 )
                 ORDER BY object_id ASC
                 LIMIT %d OFFSET %d",
                $object_type,
                (int) $limit,
                (int) $offset
            ),
            ARRAY_A
        );

        $out = [];
        foreach ( (array) $rows as $row ) {
            $meta = $this->normalise_indexable( $row );
            if ( $meta ) {
                $out[ (int) $row['object_id'] ] = $meta;
            }
        }

        return $out;
    }

    private function normalise_indexable( array $row ) {
        $meta = [
            'meta_title'         => $this->translate( $row['title'] ?? '' ),
            'meta_description'   => $this->translate( $row['description'] ?? '' ),
            'focus_keyword'      => (string) ( $row['primary_focus_keyword'] ?? '' ),
            'canonical'          => (string) ( $row['canonical'] ?? '' ),
            'breadcrumb_title'   => (string) ( $row['breadcrumb_title'] ?? '' ),
            'og_title'           => $this->translate( $row['open_graph_title'] ?? '' ),
            'og_description'     => $this->translate( $row['open_graph_description'] ?? '' ),
            'og_image'           => (string) ( $row['open_graph_image'] ?? '' ),
            'twitter_title'      => $this->translate( $row['twitter_title'] ?? '' ),
            'twitter_description' => $this->translate( $row['twitter_description'] ?? '' ),
            'noindex'            => ( '1' === (string) ( $row['is_robots_noindex'] ?? '' ) ) ? '1' : '',
            'nofollow'           => ( '1' === (string) ( $row['is_robots_nofollow'] ?? '' ) ) ? '1' : '',
            'noarchive'          => ( '1' === (string) ( $row['is_robots_noarchive'] ?? '' ) ) ? '1' : '',
            'noimageindex'       => ( '1' === (string) ( $row['is_robots_noimageindex'] ?? '' ) ) ? '1' : '',
            'nosnippet'          => ( '1' === (string) ( $row['is_robots_nosnippet'] ?? '' ) ) ? '1' : '',
            'schema_type'        => (string) ( $row['schema_article_type'] ?? '' ),
        ];

        // schema_article_type is "None" for pages; fall back to the page type.
        if ( '' === $meta['schema_type'] || 'None' === $meta['schema_type'] ) {
            $page_type           = (string) ( $row['schema_page_type'] ?? '' );
            $meta['schema_type'] = ( 'None' === $page_type ) ? '' : $page_type;
        }

        return $this->compact_meta( $meta );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Terms
    // ─────────────────────────────────────────────────────────────────────────

    public function get_terms( $offset, $limit ) {
        if ( $this->has_indexables() ) {
            return $this->get_from_indexables( 'term', $offset, $limit );
        }

        $all = $this->legacy_term_meta();

        return array_slice( $all, (int) $offset, (int) $limit, true );
    }

    /**
     * Pre-indexable Yoast stored all term SEO in one serialised option keyed by
     * taxonomy then term ID.
     *
     * @return array<int, array>
     */
    private function legacy_term_meta() {
        static $cache = null;
        if ( null !== $cache ) {
            return $cache;
        }

        $option = get_option( 'wpseo_taxonomy_meta' );
        $cache  = [];

        if ( ! is_array( $option ) ) {
            return $cache;
        }

        foreach ( $option as $terms ) {
            if ( ! is_array( $terms ) ) {
                continue;
            }
            foreach ( $terms as $term_id => $values ) {
                if ( ! is_array( $values ) ) {
                    continue;
                }

                $meta = $this->compact_meta( [
                    'meta_title'       => $this->translate( $values['wpseo_title'] ?? '' ),
                    'meta_description' => $this->translate( $values['wpseo_desc'] ?? '' ),
                    'focus_keyword'    => (string) ( $values['wpseo_focuskw'] ?? '' ),
                    'canonical'        => (string) ( $values['wpseo_canonical'] ?? '' ),
                    'og_title'         => $this->translate( $values['wpseo_opengraph-title'] ?? '' ),
                    'og_description'   => $this->translate( $values['wpseo_opengraph-description'] ?? '' ),
                    'og_image'         => (string) ( $values['wpseo_opengraph-image'] ?? '' ),
                    'noindex'          => ( 'noindex' === ( $values['wpseo_noindex'] ?? '' ) ) ? '1' : '',
                ] );

                if ( $meta ) {
                    $cache[ (int) $term_id ] = $meta;
                }
            }
        }

        return $cache;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Users
    // ─────────────────────────────────────────────────────────────────────────

    public function get_users( $offset, $limit ) {
        if ( $this->has_indexables() ) {
            return $this->get_from_indexables( 'user', $offset, $limit );
        }

        $rows = $this->fetch_usermeta_page( [ 'wpseo_title', 'wpseo_metadesc', 'wpseo_noindex_author' ], $offset, $limit );
        $out  = [];

        foreach ( $rows as $user_id => $meta ) {
            $normalised = $this->compact_meta( [
                'meta_title'       => $this->translate( $meta['wpseo_title'] ?? '' ),
                'meta_description' => $this->translate( $meta['wpseo_metadesc'] ?? '' ),
                'noindex'          => $this->flag( $meta['wpseo_noindex_author'] ?? '' ),
            ] );

            if ( $normalised ) {
                $out[ $user_id ] = $normalised;
            }
        }

        return $out;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Settings
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Yoast's separator is stored as a token name, not the character itself.
     */
    private function separator_char( $token ) {
        $map = [
            'sc-dash'   => '-',
            'sc-ndash'  => '–',
            'sc-mdash'  => '—',
            'sc-colon'  => ':',
            'sc-middot' => '·',
            'sc-bull'   => '•',
            'sc-star'   => '*',
            'sc-smstar' => '⋆',
            'sc-pipe'   => '|',
            'sc-tilde'  => '~',
            'sc-laquo'  => '«',
            'sc-raquo'  => '»',
            'sc-lt'     => '<',
            'sc-gt'     => '>',
        ];

        return $map[ $token ] ?? '|';
    }

    public function get_settings() {
        $titles = get_option( 'wpseo_titles', [] );
        $social = get_option( 'wpseo_social', [] );

        if ( ! is_array( $titles ) ) {
            $titles = [];
        }
        if ( ! is_array( $social ) ) {
            $social = [];
        }

        $out = [ 'global' => [], 'content_types' => [], 'taxonomies' => [], 'archives' => [], 'post_type_archives' => [] ];

        // Global.
        if ( ! empty( $titles['separator'] ) ) {
            $out['global']['title_separator'] = $this->separator_char( $titles['separator'] );
        }
        if ( isset( $titles['title-home-wpseo'] ) ) {
            $out['global']['homepage_title'] = $this->translate( $titles['title-home-wpseo'] );
        }
        if ( isset( $titles['metadesc-home-wpseo'] ) ) {
            $out['global']['homepage_desc'] = $this->translate( $titles['metadesc-home-wpseo'] );
        }
        if ( ! empty( $social['og_default_image'] ) ) {
            $out['global']['og_default_image'] = esc_url_raw( $social['og_default_image'] );
        }
        if ( ! empty( $social['twitter_site'] ) ) {
            $out['global']['twitter_site'] = sanitize_text_field( $social['twitter_site'] );
        }
        if ( ! empty( $social['facebook_site'] ) ) {
            $out['global']['facebook_page'] = esc_url_raw( $social['facebook_site'] );
        }

        // Post types.
        foreach ( get_post_types( [ 'public' => true ], 'names' ) as $post_type ) {
            $row = [];

            if ( isset( $titles[ "title-{$post_type}" ] ) ) {
                $row['title'] = $this->translate( $titles[ "title-{$post_type}" ] );
            }
            if ( isset( $titles[ "metadesc-{$post_type}" ] ) ) {
                $row['desc'] = $this->translate( $titles[ "metadesc-{$post_type}" ] );
            }
            if ( isset( $titles[ "noindex-{$post_type}" ] ) ) {
                $row['noindex'] = $this->flag( $titles[ "noindex-{$post_type}" ] );
            }

            if ( array_filter( $row, static function ( $v ) { return '' !== $v; } ) ) {
                $out['content_types'][ $post_type ] = $row;
            }
        }

        // Taxonomies.
        foreach ( get_taxonomies( [ 'public' => true ], 'names' ) as $taxonomy ) {
            $row = [];

            if ( isset( $titles[ "title-tax-{$taxonomy}" ] ) ) {
                $row['title'] = $this->translate( $titles[ "title-tax-{$taxonomy}" ] );
            }
            if ( isset( $titles[ "metadesc-tax-{$taxonomy}" ] ) ) {
                $row['desc'] = $this->translate( $titles[ "metadesc-tax-{$taxonomy}" ] );
            }
            if ( isset( $titles[ "noindex-tax-{$taxonomy}" ] ) ) {
                $row['noindex'] = $this->flag( $titles[ "noindex-tax-{$taxonomy}" ] );
            }

            if ( array_filter( $row, static function ( $v ) { return '' !== $v; } ) ) {
                $out['taxonomies'][ $taxonomy ] = $row;
            }
        }

        // Author / date / search / 404.
        $archive_map = [
            'author' => [ 'title-author-wpseo', 'metadesc-author-wpseo', 'noindex-author-wpseo' ],
            'date'   => [ 'title-archive-wpseo', 'metadesc-archive-wpseo', 'noindex-archive-wpseo' ],
            'search' => [ 'title-search-wpseo', null, null ],
            'notfound' => [ 'title-404-wpseo', null, null ],
        ];

        foreach ( $archive_map as $key => [ $title_key, $desc_key, $noindex_key ] ) {
            $row = [];

            if ( $title_key && isset( $titles[ $title_key ] ) ) {
                $row['title'] = $this->translate( $titles[ $title_key ] );
            }
            if ( $desc_key && isset( $titles[ $desc_key ] ) ) {
                $row['desc'] = $this->translate( $titles[ $desc_key ] );
            }
            if ( $noindex_key && isset( $titles[ $noindex_key ] ) ) {
                $row['noindex'] = $this->flag( $titles[ $noindex_key ] );
            }

            if ( array_filter( $row, static function ( $v ) { return '' !== $v; } ) ) {
                $out['archives'][ $key ] = $row;
            }
        }

        // Yoast's "no author archives if the author has no posts" maps onto our
        // single-author rule closely enough to be worth carrying over.
        if ( ! empty( $titles['noindex-author-noposts-wpseo'] ) ) {
            $out['archives']['author']['noindex_single_author'] = '1';
        }

        // Publisher entity for the schema graph.
        $out['_schema'] = $this->schema_settings( $titles, $social );

        return array_filter( $out );
    }

    /**
     * Knowledge-graph settings, returned under a private key the engine unpacks
     * into gatetouch_schema_settings.
     */
    private function schema_settings( array $titles, array $social ) {
        $schema = [];

        $type = $titles['company_or_person'] ?? '';
        if ( 'company' === $type ) {
            $schema['org_type'] = 'Organization';
            if ( ! empty( $titles['company_name'] ) ) {
                $schema['org_name'] = sanitize_text_field( $titles['company_name'] );
            }
            if ( ! empty( $titles['company_logo'] ) ) {
                $schema['org_logo'] = esc_url_raw( $titles['company_logo'] );
            }
        } elseif ( 'person' === $type ) {
            $schema['org_type'] = 'Person';
            if ( ! empty( $titles['person_name'] ) ) {
                $schema['org_name'] = sanitize_text_field( $titles['person_name'] );
            }
        }

        // Social profiles become Organization.sameAs.
        $profiles = [];
        foreach ( [ 'facebook_site', 'instagram_url', 'linkedin_url', 'myspace_url', 'pinterest_url', 'youtube_url', 'wikipedia_url', 'tumblr_url', 'soundcloud_url' ] as $key ) {
            if ( ! empty( $social[ $key ] ) ) {
                $profiles[] = esc_url_raw( $social[ $key ] );
            }
        }
        if ( ! empty( $social['twitter_site'] ) ) {
            $handle     = ltrim( (string) $social['twitter_site'], '@' );
            $profiles[] = 0 === strpos( $handle, 'http' ) ? esc_url_raw( $handle ) : 'https://x.com/' . sanitize_text_field( $handle );
        }
        if ( ! empty( $social['other_social_urls'] ) && is_array( $social['other_social_urls'] ) ) {
            foreach ( $social['other_social_urls'] as $url ) {
                $profiles[] = esc_url_raw( $url );
            }
        }

        if ( $profiles ) {
            $schema['_profiles'] = array_values( array_unique( array_filter( $profiles ) ) );
        }

        return $schema;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Redirects (Yoast Premium)
    // ─────────────────────────────────────────────────────────────────────────

    public function get_redirects() {
        $out = [];

        foreach ( [ 'wpseo-premium-redirects-export-plain', 'wpseo-premium-redirects-export-regex' ] as $option ) {
            $stored = get_option( $option );
            if ( ! is_array( $stored ) ) {
                continue;
            }

            $format = ( false !== strpos( $option, 'regex' ) ) ? 'regex' : 'exact';

            foreach ( $stored as $source => $data ) {
                $target = is_array( $data ) ? ( $data['url'] ?? '' ) : (string) $data;
                $type   = is_array( $data ) ? (int) ( $data['type'] ?? 301 ) : 301;

                if ( '' === $source ) {
                    continue;
                }

                $out[] = [
                    'source' => (string) $source,
                    'target' => (string) $target,
                    'type'   => $type ?: 301,
                    'format' => $format,
                ];
            }
        }

        return $out;
    }
}
