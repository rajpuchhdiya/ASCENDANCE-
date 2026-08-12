<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- migration reads source plugin storage directly.
// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table identifiers are built from $wpdb->prefix.

/**
 * Rank Math SEO migration adapter.
 *
 * Rank Math uses plain post/term/user meta with a `rank_math_` prefix, plus its
 * own redirections table. Robots directives are stored as a serialised array
 * rather than individual flags.
 */
class GateTouch_Migration_Rankmath extends GateTouch_Migration_Source {

    public function slug() {
        return 'rankmath';
    }

    public function label() {
        return 'Rank Math SEO';
    }

    public function is_detected() {
        if ( defined( 'RANK_MATH_VERSION' ) ) {
            return true;
        }
        if ( get_option( 'rank-math-options-titles' ) ) {
            return true;
        }

        return $this->count_postmeta( array_keys( $this->meta_map() ) ) > 0;
    }

    /**
     * Shared across posts, terms and users — Rank Math reuses the same key names.
     */
    private function meta_map() {
        return [
            'rank_math_title'                => 'meta_title',
            'rank_math_description'          => 'meta_description',
            'rank_math_focus_keyword'        => 'focus_keyword',
            'rank_math_canonical_url'        => 'canonical',
            'rank_math_robots'               => 'robots_raw',
            'rank_math_facebook_title'       => 'og_title',
            'rank_math_facebook_description' => 'og_description',
            'rank_math_facebook_image'       => 'og_image',
            'rank_math_twitter_title'        => 'twitter_title',
            'rank_math_twitter_description'  => 'twitter_description',
            'rank_math_twitter_card_type'    => 'twitter_card',
            'rank_math_rich_snippet'         => 'schema_raw',
            'rank_math_breadcrumb_title'     => 'breadcrumb_title',
        ];
    }

    protected function variable_map() {
        return [
            '%title%'             => '#title#',
            '%seo_title%'         => '#title#',
            '%sitename%'          => '#site_title#',
            '%sitedesc%'          => '#tagline#',
            '%sep%'               => '#sep#',
            '%page%'              => '#page#',
            '%pagenumber%'        => '#pagenumber#',
            '%pagetotal%'         => '#pagetotal#',
            '%excerpt%'           => '#excerpt#',
            '%excerpt_only%'      => '#excerpt_only#',
            '%seo_description%'   => '#excerpt#',
            '%date%'              => '#date#',
            '%modified%'          => '#modified#',
            '%currentdate%'       => '#currentdate#',
            '%currentyear%'       => '#year#',
            '%currentmonth%'      => '#month#',
            '%currentday%'        => '#day#',
            '%name%'              => '#author_name#',
            '%post_author%'       => '#author_name#',
            '%user_description%'  => '#author_bio#',
            '%category%'          => '#category#',
            '%categories%'        => '#categories#',
            '%primary_category%'  => '#category#',
            '%tag%'               => '#tag#',
            '%tags%'              => '#tags#',
            '%term%'              => '#term#',
            '%term_description%'  => '#term_description#',
            '%search_query%'      => '#search_query#',
            '%focuskw%'           => '#focus_keyword#',
            '%id%'                => '#post_id#',
            '%parent_title%'      => '#parent_title#',
            '%pt_single%'         => '#post_type#',
            '%pt_plural%'         => '#post_type_plural#',
            '%post_type%'         => '#post_type#',
            '%url%'               => '#url#',
            '%wc_price%'          => '#wc_price#',
            '%wc_sku%'            => '#wc_sku#',
            '%wc_brand%'          => '#wc_brand#',
            '%wc_shortdesc%'      => '#wc_short_desc#',
        ];
    }

    /**
     * Rank Math parameterised variables: %customfield(key)% and %customterm(tax)%.
     */
    protected function translate_dynamic( $text ) {
        $text = preg_replace( '/%customfield\(([A-Za-z0-9_\-]+)\)%/', '#cf_$1#', $text );
        $text = preg_replace( '/%customterm\(([A-Za-z0-9_\-]+)\)%/', '#tax_$1#', $text );

        return $text;
    }

    protected function strip_unknown( $text ) {
        // Rank Math uses single percent delimiters.
        return preg_replace( '/%[A-Za-z0-9_\-]+(\([^)]*\))?%/', '', $text );
    }

    public function counts() {
        return [
            'posts'     => $this->count_postmeta( array_keys( $this->meta_map() ) ),
            'terms'     => $this->count_termmeta( array_keys( $this->meta_map() ) ),
            'users'     => $this->count_usermeta( array_keys( $this->meta_map() ) ),
            'redirects' => $this->count_redirects(),
        ];
    }

    public function get_posts( $offset, $limit ) {
        return $this->normalise_rows( $this->fetch_postmeta_page( array_keys( $this->meta_map() ), $offset, $limit ) );
    }

    public function get_terms( $offset, $limit ) {
        return $this->normalise_rows( $this->fetch_termmeta_page( array_keys( $this->meta_map() ), $offset, $limit ) );
    }

    public function get_users( $offset, $limit ) {
        return $this->normalise_rows( $this->fetch_usermeta_page( array_keys( $this->meta_map() ), $offset, $limit ) );
    }

    private function normalise_rows( array $rows ) {
        $out = [];

        foreach ( $rows as $id => $raw ) {
            $meta = [];

            foreach ( $this->meta_map() as $source_key => $target_key ) {
                if ( ! isset( $raw[ $source_key ] ) || '' === $raw[ $source_key ] ) {
                    continue;
                }
                $meta[ $target_key ] = $raw[ $source_key ];
            }

            foreach ( [ 'meta_title', 'meta_description', 'og_title', 'og_description', 'twitter_title', 'twitter_description' ] as $key ) {
                if ( isset( $meta[ $key ] ) ) {
                    $meta[ $key ] = $this->translate( $meta[ $key ] );
                }
            }

            // Robots is a serialised list such as a:2:{i:0;s:7:"noindex";...}.
            if ( isset( $meta['robots_raw'] ) ) {
                $directives = maybe_unserialize( $meta['robots_raw'] );
                $directives = is_array( $directives ) ? $directives : [];

                foreach ( [ 'noindex', 'nofollow', 'noarchive', 'noimageindex', 'nosnippet' ] as $directive ) {
                    if ( in_array( $directive, $directives, true ) ) {
                        $meta[ $directive ] = '1';
                    }
                }
                unset( $meta['robots_raw'] );
            }

            // rich_snippet holds the schema type, e.g. "article", "product".
            if ( isset( $meta['schema_raw'] ) ) {
                $type = (string) $meta['schema_raw'];
                if ( $type && 'off' !== $type ) {
                    $meta['schema_type'] = $this->schema_type( $type, $raw );
                }
                unset( $meta['schema_raw'] );
            }

            if ( isset( $meta['og_image'] ) ) {
                $meta['og_image'] = $this->image_url( $meta['og_image'] );
            }

            $meta = $this->compact_meta( $meta );
            if ( $meta ) {
                $out[ $id ] = $meta;
            }
        }

        return $out;
    }

    /**
     * Rank Math stores lowercase type slugs; schema.org needs proper casing.
     */
    private function schema_type( $type, array $raw ) {
        $map = [
            'article'    => 'Article',
            'book'       => 'Book',
            'course'     => 'Course',
            'event'      => 'Event',
            'jobposting' => 'JobPosting',
            'music'      => 'MusicGroup',
            'product'    => 'Product',
            'recipe'     => 'Recipe',
            'restaurant' => 'Restaurant',
            'service'    => 'Service',
            'software'   => 'SoftwareApplication',
            'video'      => 'VideoObject',
            'person'     => 'Person',
            'review'     => 'Review',
            'faq'        => 'FAQPage',
            'howto'      => 'HowTo',
        ];

        $normalised = strtolower( $type );

        // Articles carry a sub-type: BlogPosting / NewsArticle / Article.
        if ( 'article' === $normalised && ! empty( $raw['rank_math_snippet_article_type'] ) ) {
            return sanitize_text_field( $raw['rank_math_snippet_article_type'] );
        }

        return $map[ $normalised ] ?? sanitize_text_field( $type );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Settings
    // ─────────────────────────────────────────────────────────────────────────

    public function get_settings() {
        $titles = get_option( 'rank-math-options-titles', [] );
        $general = get_option( 'rank-math-options-general', [] );

        if ( ! is_array( $titles ) ) {
            $titles = [];
        }
        unset( $general );

        $out = [ 'global' => [], 'content_types' => [], 'taxonomies' => [], 'archives' => [] ];

        if ( ! empty( $titles['title_separator'] ) ) {
            $out['global']['title_separator'] = sanitize_text_field( $titles['title_separator'] );
        }
        if ( isset( $titles['homepage_title'] ) ) {
            $out['global']['homepage_title'] = $this->translate( $titles['homepage_title'] );
        }
        if ( isset( $titles['homepage_description'] ) ) {
            $out['global']['homepage_desc'] = $this->translate( $titles['homepage_description'] );
        }
        if ( ! empty( $titles['open_graph_image'] ) ) {
            $out['global']['og_default_image'] = $this->image_url( $titles['open_graph_image'] );
        }
        if ( ! empty( $titles['twitter_author_names'] ) ) {
            $out['global']['twitter_site'] = sanitize_text_field( $titles['twitter_author_names'] );
        }

        foreach ( get_post_types( [ 'public' => true ], 'names' ) as $post_type ) {
            $row = [];

            if ( isset( $titles[ "pt_{$post_type}_title" ] ) ) {
                $row['title'] = $this->translate( $titles[ "pt_{$post_type}_title" ] );
            }
            if ( isset( $titles[ "pt_{$post_type}_description" ] ) ) {
                $row['desc'] = $this->translate( $titles[ "pt_{$post_type}_description" ] );
            }
            if ( isset( $titles[ "pt_{$post_type}_custom_robots" ] ) && 'on' === $titles[ "pt_{$post_type}_custom_robots" ] ) {
                $robots = (array) ( $titles[ "pt_{$post_type}_robots" ] ?? [] );
                $row['noindex'] = in_array( 'noindex', $robots, true ) ? '1' : '';
            }

            if ( array_filter( $row, static function ( $v ) { return '' !== $v; } ) ) {
                $out['content_types'][ $post_type ] = $row;
            }
        }

        foreach ( get_taxonomies( [ 'public' => true ], 'names' ) as $taxonomy ) {
            $row = [];

            if ( isset( $titles[ "tax_{$taxonomy}_title" ] ) ) {
                $row['title'] = $this->translate( $titles[ "tax_{$taxonomy}_title" ] );
            }
            if ( isset( $titles[ "tax_{$taxonomy}_description" ] ) ) {
                $row['desc'] = $this->translate( $titles[ "tax_{$taxonomy}_description" ] );
            }
            if ( isset( $titles[ "tax_{$taxonomy}_custom_robots" ] ) && 'on' === $titles[ "tax_{$taxonomy}_custom_robots" ] ) {
                $robots = (array) ( $titles[ "tax_{$taxonomy}_robots" ] ?? [] );
                $row['noindex'] = in_array( 'noindex', $robots, true ) ? '1' : '';
            }

            if ( array_filter( $row, static function ( $v ) { return '' !== $v; } ) ) {
                $out['taxonomies'][ $taxonomy ] = $row;
            }
        }

        $archive_map = [
            'author'   => [ 'author_archive_title', 'author_archive_description' ],
            'date'     => [ 'date_archive_title', 'date_archive_description' ],
            'search'   => [ 'search_title', null ],
            'notfound' => [ '404_title', null ],
        ];

        foreach ( $archive_map as $key => [ $title_key, $desc_key ] ) {
            $row = [];

            if ( isset( $titles[ $title_key ] ) ) {
                $row['title'] = $this->translate( $titles[ $title_key ] );
            }
            if ( $desc_key && isset( $titles[ $desc_key ] ) ) {
                $row['desc'] = $this->translate( $titles[ $desc_key ] );
            }

            if ( array_filter( $row, static function ( $v ) { return '' !== $v; } ) ) {
                $out['archives'][ $key ] = $row;
            }
        }

        if ( isset( $titles['disable_author_archives'] ) && 'on' === $titles['disable_author_archives'] ) {
            $out['archives']['author']['noindex'] = '1';
        }
        if ( isset( $titles['disable_date_archives'] ) && 'on' === $titles['disable_date_archives'] ) {
            $out['archives']['date']['noindex'] = '1';
        }

        // Publisher entity.
        $schema = [];
        $type   = $titles['knowledgegraph_type'] ?? '';
        if ( 'company' === $type ) {
            $schema['org_type'] = 'Organization';
        } elseif ( 'person' === $type ) {
            $schema['org_type'] = 'Person';
        }
        if ( ! empty( $titles['knowledgegraph_name'] ) ) {
            $schema['org_name'] = sanitize_text_field( $titles['knowledgegraph_name'] );
        }
        if ( ! empty( $titles['knowledgegraph_logo'] ) ) {
            $schema['org_logo'] = $this->image_url( $titles['knowledgegraph_logo'] );
        }
        if ( ! empty( $titles['url'] ) ) {
            $schema['_profiles'] = [ esc_url_raw( $titles['url'] ) ];
        }

        if ( $schema ) {
            $out['_schema'] = $schema;
        }

        return array_filter( $out );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Redirects
    // ─────────────────────────────────────────────────────────────────────────

    private function redirect_table() {
        global $wpdb;
        return $wpdb->prefix . 'rank_math_redirections';
    }

    private function count_redirects() {
        global $wpdb;

        if ( ! $this->table_exists( $this->redirect_table() ) ) {
            return 0;
        }

        $table = $this->redirect_table();
        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE status = 'active'" );
    }

    public function get_redirects() {
        global $wpdb;

        if ( ! $this->table_exists( $this->redirect_table() ) ) {
            return [];
        }

        $table = $this->redirect_table();
        $rows  = $wpdb->get_results( "SELECT sources, url_to, header_code FROM {$table} WHERE status = 'active' LIMIT 5000" );

        $out = [];
        foreach ( (array) $rows as $row ) {
            // `sources` is a serialised list of matcher definitions.
            $sources = maybe_unserialize( $row->sources );
            if ( ! is_array( $sources ) ) {
                continue;
            }

            foreach ( $sources as $source ) {
                $pattern = is_array( $source ) ? ( $source['pattern'] ?? '' ) : (string) $source;
                if ( '' === $pattern ) {
                    continue;
                }

                $comparison = is_array( $source ) ? ( $source['comparison'] ?? 'exact' ) : 'exact';

                $out[] = [
                    'source' => $pattern,
                    'target' => (string) $row->url_to,
                    'type'   => (int) $row->header_code ?: 301,
                    'format' => ( 'regex' === $comparison ) ? 'regex' : 'exact',
                ];
            }
        }

        return $out;
    }
}
