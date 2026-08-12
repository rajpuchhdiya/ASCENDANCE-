<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- migration reads source plugin storage directly.
// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table identifiers are built from $wpdb->prefix.

/**
 * All in One SEO migration adapter.
 *
 * AIOSEO v4 abandoned postmeta for dedicated tables (wp_aioseo_posts,
 * wp_aioseo_terms, wp_aioseo_redirects) and stores settings as a JSON blob in
 * the aioseo_options option. v3 used postmeta with an `_aioseo_` prefix, which
 * this adapter falls back to.
 *
 * Its variable syntax is unusual: tokens are `#hash_prefixed` with no closing
 * delimiter, so translation has to be done longest-token-first via regex.
 */
class GateTouch_Migration_Aioseo extends GateTouch_Migration_Source {

    public function slug() {
        return 'aioseo';
    }

    public function label() {
        return 'All in One SEO';
    }

    private function posts_table() {
        global $wpdb;
        return $wpdb->prefix . 'aioseo_posts';
    }

    private function terms_table() {
        global $wpdb;
        return $wpdb->prefix . 'aioseo_terms';
    }

    public function is_detected() {
        if ( defined( 'AIOSEO_VERSION' ) ) {
            return true;
        }
        if ( $this->table_exists( $this->posts_table() ) ) {
            return true;
        }
        if ( get_option( 'aioseo_options' ) ) {
            return true;
        }

        return $this->count_postmeta( array_keys( $this->legacy_meta_map() ) ) > 0;
    }

    /** AIOSEO v3 postmeta keys. */
    private function legacy_meta_map() {
        return [
            '_aioseo_title'            => 'meta_title',
            '_aioseo_description'      => 'meta_description',
            '_aioseo_keywords'         => 'additional_keywords',
            '_aioseo_og_title'         => 'og_title',
            '_aioseo_og_description'   => 'og_description',
            '_aioseo_twitter_title'    => 'twitter_title',
            '_aioseo_twitter_description' => 'twitter_description',
        ];
    }

    /**
     * AIOSEO tokens have no closing delimiter, so order matters: #post_title
     * must be replaced before #post would ever match.
     */
    protected function variable_map() {
        return [
            '#post_title'              => '#title#',
            '#page_title'              => '#title#',
            '#product_title'           => '#title#',
            '#site_title'              => '#site_title#',
            '#blog_title'              => '#site_title#',
            '#tagline'                 => '#tagline#',
            '#site_description'        => '#tagline#',
            '#separator_sa'            => '#sep#',
            '#separator'               => '#sep#',
            '#post_excerpt_only'       => '#excerpt_only#',
            '#post_excerpt'            => '#excerpt#',
            '#post_content'            => '#excerpt#',
            '#page_number'             => '#pagenumber#',
            '#current_date'            => '#currentdate#',
            '#current_year'            => '#year#',
            '#current_month'           => '#month#',
            '#current_day'             => '#day#',
            '#post_date'               => '#date#',
            '#post_year'               => '#year#',
            '#post_month'              => '#month#',
            '#modified_date'           => '#modified#',
            '#author_first_name'       => '#author_first_name#',
            '#author_last_name'        => '#author_last_name#',
            '#author_name'             => '#author_name#',
            '#author_bio'              => '#author_bio#',
            '#categories'              => '#categories#',
            '#category_title'          => '#term#',
            '#category'                => '#category#',
            '#tags'                    => '#tags#',
            '#taxonomy_title'          => '#term#',
            '#taxonomy_description'    => '#term_description#',
            '#term_title'              => '#term#',
            '#term_description'        => '#term_description#',
            '#archive_title'           => '#archive_title#',
            '#search_term'             => '#search_query#',
            '#post_type_plural_label'  => '#post_type_plural#',
            '#post_type_singular_label' => '#post_type#',
            '#permalink'               => '#url#',
            '#parent_title'            => '#parent_title#',
            '#focus_keyphrase'         => '#focus_keyword#',
            '#product_price'           => '#wc_price#',
            '#product_sku'             => '#wc_sku#',
        ];
    }

    protected function translate_dynamic( $text ) {
        return preg_replace( '/#custom_field-([A-Za-z0-9_\-]+)/', '#cf_$1#', $text );
    }

    protected function strip_unknown( $text ) {
        // Remove any remaining bare #token that is not already our #var# form.
        return preg_replace( '/#[a-z][a-z0-9_\-]*(?!#)\b(?!#)/', '', $text );
    }

    public function counts() {
        return [
            'posts'     => $this->count_table( $this->posts_table(), 'post_id' ),
            'terms'     => $this->count_table( $this->terms_table(), 'term_id' ),
            'users'     => 0,
            'redirects' => count( $this->get_redirects() ),
        ];
    }

    private function count_table( $table, $id_column ) {
        global $wpdb;

        if ( ! $this->table_exists( $table ) ) {
            if ( $table === $this->posts_table() ) {
                return $this->count_postmeta( array_keys( $this->legacy_meta_map() ) );
            }
            return 0;
        }

        unset( $id_column );

        return (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$table}
             WHERE ( title IS NOT NULL AND title != '' )
                OR ( description IS NOT NULL AND description != '' )
                OR ( canonical_url IS NOT NULL AND canonical_url != '' )
                OR robots_noindex = 1"
        );
    }

    public function get_posts( $offset, $limit ) {
        global $wpdb;

        if ( ! $this->table_exists( $this->posts_table() ) ) {
            return $this->legacy_posts( $offset, $limit );
        }

        $table = $this->posts_table();
        $rows  = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table}
                 WHERE ( title IS NOT NULL AND title != '' )
                    OR ( description IS NOT NULL AND description != '' )
                    OR ( canonical_url IS NOT NULL AND canonical_url != '' )
                    OR robots_noindex = 1
                 ORDER BY post_id ASC LIMIT %d OFFSET %d",
                (int) $limit,
                (int) $offset
            ),
            ARRAY_A
        );

        $out = [];
        foreach ( (array) $rows as $row ) {
            $meta = $this->normalise_row( $row );
            if ( $meta ) {
                $out[ (int) $row['post_id'] ] = $meta;
            }
        }

        return $out;
    }

    public function get_terms( $offset, $limit ) {
        global $wpdb;

        if ( ! $this->table_exists( $this->terms_table() ) ) {
            return [];
        }

        $table = $this->terms_table();
        $rows  = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table}
                 WHERE ( title IS NOT NULL AND title != '' )
                    OR ( description IS NOT NULL AND description != '' )
                    OR ( canonical_url IS NOT NULL AND canonical_url != '' )
                    OR robots_noindex = 1
                 ORDER BY term_id ASC LIMIT %d OFFSET %d",
                (int) $limit,
                (int) $offset
            ),
            ARRAY_A
        );

        $out = [];
        foreach ( (array) $rows as $row ) {
            $meta = $this->normalise_row( $row );
            if ( $meta ) {
                $out[ (int) $row['term_id'] ] = $meta;
            }
        }

        return $out;
    }

    /**
     * Map an aioseo_posts / aioseo_terms row onto the normalised shape.
     */
    private function normalise_row( array $row ) {
        $meta = [
            'meta_title'          => $this->translate( $row['title'] ?? '' ),
            'meta_description'    => $this->translate( $row['description'] ?? '' ),
            'canonical'           => (string) ( $row['canonical_url'] ?? '' ),
            'og_title'            => $this->translate( $row['og_title'] ?? '' ),
            'og_description'      => $this->translate( $row['og_description'] ?? '' ),
            'og_image'            => (string) ( $row['og_image_custom_url'] ?? '' ),
            'twitter_title'       => $this->translate( $row['twitter_title'] ?? '' ),
            'twitter_description' => $this->translate( $row['twitter_description'] ?? '' ),
            'twitter_card'        => (string) ( $row['twitter_card'] ?? '' ),
            'noindex'             => $this->flag( $row['robots_noindex'] ?? '' ),
            'nofollow'            => $this->flag( $row['robots_nofollow'] ?? '' ),
            'noarchive'           => $this->flag( $row['robots_noarchive'] ?? '' ),
            'noimageindex'        => $this->flag( $row['robots_noimageindex'] ?? '' ),
            'nosnippet'           => $this->flag( $row['robots_nosnippet'] ?? '' ),
        ];

        // Keyphrases are stored as JSON: {"focus":{"keyphrase":"..."},"additional":[...]}
        if ( ! empty( $row['keyphrases'] ) ) {
            $keyphrases = json_decode( (string) $row['keyphrases'], true );
            if ( is_array( $keyphrases ) ) {
                if ( ! empty( $keyphrases['focus']['keyphrase'] ) ) {
                    $meta['focus_keyword'] = sanitize_text_field( $keyphrases['focus']['keyphrase'] );
                }
                if ( ! empty( $keyphrases['additional'] ) && is_array( $keyphrases['additional'] ) ) {
                    $extra = [];
                    foreach ( $keyphrases['additional'] as $item ) {
                        if ( ! empty( $item['keyphrase'] ) ) {
                            $extra[] = sanitize_text_field( $item['keyphrase'] );
                        }
                    }
                    if ( $extra ) {
                        $meta['additional_keywords'] = implode( ', ', $extra );
                    }
                }
            }
        }

        // schema_type_options is JSON describing the chosen graph.
        if ( ! empty( $row['schema_type'] ) && 'default' !== $row['schema_type'] ) {
            $meta['schema_type'] = sanitize_text_field( (string) $row['schema_type'] );
        }

        return $this->compact_meta( $meta );
    }

    private function legacy_posts( $offset, $limit ) {
        $rows = $this->fetch_postmeta_page( array_keys( $this->legacy_meta_map() ), $offset, $limit );
        $out  = [];

        foreach ( $rows as $post_id => $raw ) {
            $meta = [];
            foreach ( $this->legacy_meta_map() as $source_key => $target_key ) {
                if ( isset( $raw[ $source_key ] ) && '' !== $raw[ $source_key ] ) {
                    $meta[ $target_key ] = $this->translate( $raw[ $source_key ] );
                }
            }

            $meta = $this->compact_meta( $meta );
            if ( $meta ) {
                $out[ $post_id ] = $meta;
            }
        }

        return $out;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Settings
    // ─────────────────────────────────────────────────────────────────────────

    public function get_settings() {
        $raw = get_option( 'aioseo_options' );
        if ( ! $raw ) {
            return [];
        }

        $options = is_string( $raw ) ? json_decode( $raw, true ) : $raw;
        if ( ! is_array( $options ) ) {
            return [];
        }

        $search = $options['searchAppearance'] ?? [];
        $out    = [ 'global' => [], 'content_types' => [], 'taxonomies' => [], 'archives' => [] ];

        if ( ! empty( $search['global']['separator'] ) ) {
            $out['global']['title_separator'] = sanitize_text_field( $search['global']['separator'] );
        }
        if ( isset( $search['global']['siteTitle'] ) ) {
            $out['global']['homepage_title'] = $this->translate( $search['global']['siteTitle'] );
        }
        if ( isset( $search['global']['metaDescription'] ) ) {
            $out['global']['homepage_desc'] = $this->translate( $search['global']['metaDescription'] );
        }

        foreach ( (array) ( $search['dynamic']['postTypes'] ?? [] ) as $post_type => $config ) {
            $row = [];
            if ( isset( $config['title'] ) ) {
                $row['title'] = $this->translate( $config['title'] );
            }
            if ( isset( $config['metaDescription'] ) ) {
                $row['desc'] = $this->translate( $config['metaDescription'] );
            }
            if ( isset( $config['advanced']['robotsMeta']['noindex'] ) ) {
                $row['noindex'] = $config['advanced']['robotsMeta']['noindex'] ? '1' : '';
            }
            if ( array_filter( $row, static function ( $v ) { return '' !== $v; } ) ) {
                $out['content_types'][ sanitize_key( $post_type ) ] = $row;
            }
        }

        foreach ( (array) ( $search['dynamic']['taxonomies'] ?? [] ) as $taxonomy => $config ) {
            $row = [];
            if ( isset( $config['title'] ) ) {
                $row['title'] = $this->translate( $config['title'] );
            }
            if ( isset( $config['metaDescription'] ) ) {
                $row['desc'] = $this->translate( $config['metaDescription'] );
            }
            if ( array_filter( $row, static function ( $v ) { return '' !== $v; } ) ) {
                $out['taxonomies'][ sanitize_key( $taxonomy ) ] = $row;
            }
        }

        $archive_map = [
            'author'   => $search['archives']['author'] ?? [],
            'date'     => $search['archives']['date'] ?? [],
            'search'   => $search['archives']['search'] ?? [],
        ];

        foreach ( $archive_map as $key => $config ) {
            if ( ! is_array( $config ) ) {
                continue;
            }
            $row = [];
            if ( isset( $config['title'] ) ) {
                $row['title'] = $this->translate( $config['title'] );
            }
            if ( isset( $config['metaDescription'] ) ) {
                $row['desc'] = $this->translate( $config['metaDescription'] );
            }
            if ( isset( $config['advanced']['robotsMeta']['noindex'] ) ) {
                $row['noindex'] = $config['advanced']['robotsMeta']['noindex'] ? '1' : '';
            }
            if ( array_filter( $row, static function ( $v ) { return '' !== $v; } ) ) {
                $out['archives'][ $key ] = $row;
            }
        }

        // Publisher entity.
        $schema  = [];
        $identity = $options['searchAppearance']['global']['schema'] ?? [];
        if ( ! empty( $identity['siteRepresents'] ) ) {
            $schema['org_type'] = ( 'person' === $identity['siteRepresents'] ) ? 'Person' : 'Organization';
        }
        if ( ! empty( $identity['organizationName'] ) ) {
            $schema['org_name'] = sanitize_text_field( $identity['organizationName'] );
        }
        if ( ! empty( $identity['organizationLogo'] ) ) {
            $schema['org_logo'] = esc_url_raw( $identity['organizationLogo'] );
        }
        if ( ! empty( $identity['phone'] ) ) {
            $schema['org_phone'] = sanitize_text_field( $identity['phone'] );
        }

        $profiles = $options['social']['profiles']['urls'] ?? [];
        if ( is_array( $profiles ) ) {
            $urls = array_values( array_filter( array_map( 'esc_url_raw', array_filter( $profiles, 'is_string' ) ) ) );
            if ( $urls ) {
                $schema['_profiles'] = $urls;
            }
        }

        if ( $schema ) {
            $out['_schema'] = $schema;
        }

        return array_filter( $out );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Redirects
    // ─────────────────────────────────────────────────────────────────────────

    public function get_redirects() {
        global $wpdb;

        $table = $wpdb->prefix . 'aioseo_redirects';
        if ( ! $this->table_exists( $table ) ) {
            return [];
        }

        $rows = $wpdb->get_results( "SELECT source_url, target_url, type, regex FROM {$table} WHERE enabled = 1 LIMIT 5000" );

        $out = [];
        foreach ( (array) $rows as $row ) {
            if ( empty( $row->source_url ) ) {
                continue;
            }
            $out[] = [
                'source' => (string) $row->source_url,
                'target' => (string) $row->target_url,
                'type'   => (int) $row->type ?: 301,
                'format' => ! empty( $row->regex ) ? 'regex' : 'exact',
            ];
        }

        return $out;
    }
}
