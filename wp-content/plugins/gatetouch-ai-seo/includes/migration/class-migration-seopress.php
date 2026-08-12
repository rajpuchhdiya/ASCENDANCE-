<?php
defined( 'ABSPATH' ) || exit;

/**
 * SEOPress migration adapter.
 *
 * SEOPress uses plain post/term/user meta with a `_seopress_` prefix. Its robots
 * flags are inverted relative to most plugins: `_seopress_robots_index = 'yes'`
 * means "no-index this", not "index this".
 */
class GateTouch_Migration_Seopress extends GateTouch_Migration_Source {

    public function slug() {
        return 'seopress';
    }

    public function label() {
        return 'SEOPress';
    }

    public function is_detected() {
        if ( defined( 'SEOPRESS_VERSION' ) ) {
            return true;
        }
        if ( get_option( 'seopress_titles_option_name' ) ) {
            return true;
        }

        return $this->count_postmeta( array_keys( $this->meta_map() ) ) > 0;
    }

    private function meta_map() {
        return [
            '_seopress_titles_title'        => 'meta_title',
            '_seopress_titles_desc'         => 'meta_description',
            '_seopress_analysis_target_kw'  => 'focus_keyword',
            '_seopress_robots_canonical'    => 'canonical',
            '_seopress_robots_index'        => 'noindex_raw',
            '_seopress_robots_follow'       => 'nofollow_raw',
            '_seopress_robots_archive'      => 'noarchive_raw',
            '_seopress_robots_imageindex'   => 'noimageindex_raw',
            '_seopress_robots_snippet'      => 'nosnippet_raw',
            '_seopress_social_fb_title'     => 'og_title',
            '_seopress_social_fb_desc'      => 'og_description',
            '_seopress_social_fb_img'       => 'og_image',
            '_seopress_social_twitter_title' => 'twitter_title',
            '_seopress_social_twitter_desc' => 'twitter_description',
        ];
    }

    protected function variable_map() {
        return [
            '%%post_title%%'       => '#title#',
            '%%sitetitle%%'        => '#site_title#',
            '%%sitename%%'         => '#site_title#',
            '%%tagline%%'          => '#tagline#',
            '%%sitedesc%%'         => '#tagline#',
            '%%sep%%'              => '#sep#',
            '%%post_excerpt%%'     => '#excerpt#',
            '%%post_date%%'        => '#date#',
            '%%post_modified_date%%' => '#modified#',
            '%%currentyear%%'      => '#year#',
            '%%currentmonth%%'     => '#month#',
            '%%currentday%%'       => '#day#',
            '%%currentdate%%'      => '#currentdate#',
            '%%post_author%%'      => '#author_name#',
            '%%author_bio%%'       => '#author_bio#',
            '%%_category_title%%'  => '#category#',
            '%%category_title%%'   => '#term#',
            '%%category_description%%' => '#term_description#',
            '%%tag_title%%'        => '#term#',
            '%%tag_description%%'  => '#term_description#',
            '%%term_title%%'       => '#term#',
            '%%term_description%%' => '#term_description#',
            '%%search_keywords%%'  => '#search_query#',
            '%%current_pagination%%' => '#pagenumber#',
            '%%post_type_archive_title%%' => '#archive_title#',
            '%%target_keyword%%'   => '#focus_keyword#',
            '%%wc_single_price%%'  => '#wc_price#',
            '%%wc_sku%%'           => '#wc_sku#',
            '%%wc_single_short_desc%%' => '#wc_short_desc#',
        ];
    }

    protected function translate_dynamic( $text ) {
        return preg_replace( '/%%_cf_([A-Za-z0-9_\-]+)%%/', '#cf_$1#', $text );
    }

    public function counts() {
        return [
            'posts'     => $this->count_postmeta( array_keys( $this->meta_map() ) ),
            'terms'     => $this->count_termmeta( array_keys( $this->meta_map() ) ),
            'users'     => $this->count_usermeta( array_keys( $this->meta_map() ) ),
            'redirects' => 0,
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
                if ( isset( $raw[ $source_key ] ) && '' !== $raw[ $source_key ] ) {
                    $meta[ $target_key ] = $raw[ $source_key ];
                }
            }

            foreach ( [ 'meta_title', 'meta_description', 'og_title', 'og_description', 'twitter_title', 'twitter_description' ] as $key ) {
                if ( isset( $meta[ $key ] ) ) {
                    $meta[ $key ] = $this->translate( $meta[ $key ] );
                }
            }

            // SEOPress stores the NEGATIVE: 'yes' on _robots_index means noindex.
            $inverted = [
                'noindex_raw'      => 'noindex',
                'nofollow_raw'     => 'nofollow',
                'noarchive_raw'    => 'noarchive',
                'noimageindex_raw' => 'noimageindex',
                'nosnippet_raw'    => 'nosnippet',
            ];

            foreach ( $inverted as $raw_key => $target ) {
                if ( isset( $meta[ $raw_key ] ) ) {
                    $meta[ $target ] = ( 'yes' === (string) $meta[ $raw_key ] ) ? '1' : '';
                    unset( $meta[ $raw_key ] );
                }
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

    public function get_settings() {
        $titles = get_option( 'seopress_titles_option_name', [] );
        $social = get_option( 'seopress_social_option_name', [] );

        if ( ! is_array( $titles ) ) {
            $titles = [];
        }
        if ( ! is_array( $social ) ) {
            $social = [];
        }

        $out = [ 'global' => [], 'content_types' => [], 'taxonomies' => [], 'archives' => [] ];

        if ( ! empty( $titles['seopress_titles_sep'] ) ) {
            $out['global']['title_separator'] = sanitize_text_field( $titles['seopress_titles_sep'] );
        }
        if ( isset( $titles['seopress_titles_home_site_title'] ) ) {
            $out['global']['homepage_title'] = $this->translate( $titles['seopress_titles_home_site_title'] );
        }
        if ( isset( $titles['seopress_titles_home_site_desc'] ) ) {
            $out['global']['homepage_desc'] = $this->translate( $titles['seopress_titles_home_site_desc'] );
        }
        if ( ! empty( $social['seopress_social_facebook_img'] ) ) {
            $out['global']['og_default_image'] = esc_url_raw( $social['seopress_social_facebook_img'] );
        }
        if ( ! empty( $social['seopress_social_twitter_card_img'] ) && empty( $out['global']['og_default_image'] ) ) {
            $out['global']['og_default_image'] = esc_url_raw( $social['seopress_social_twitter_card_img'] );
        }

        // Post types and taxonomies are nested arrays keyed by object name.
        foreach ( (array) ( $titles['seopress_titles_single_titles'] ?? [] ) as $post_type => $config ) {
            $row = [];
            if ( isset( $config['title'] ) ) {
                $row['title'] = $this->translate( $config['title'] );
            }
            if ( isset( $config['description'] ) ) {
                $row['desc'] = $this->translate( $config['description'] );
            }
            if ( ! empty( $config['noindex'] ) ) {
                $row['noindex'] = '1';
            }
            if ( array_filter( $row, static function ( $v ) { return '' !== $v; } ) ) {
                $out['content_types'][ sanitize_key( $post_type ) ] = $row;
            }
        }

        foreach ( (array) ( $titles['seopress_titles_tax_titles'] ?? [] ) as $taxonomy => $config ) {
            $row = [];
            if ( isset( $config['title'] ) ) {
                $row['title'] = $this->translate( $config['title'] );
            }
            if ( isset( $config['description'] ) ) {
                $row['desc'] = $this->translate( $config['description'] );
            }
            if ( ! empty( $config['noindex'] ) ) {
                $row['noindex'] = '1';
            }
            if ( array_filter( $row, static function ( $v ) { return '' !== $v; } ) ) {
                $out['taxonomies'][ sanitize_key( $taxonomy ) ] = $row;
            }
        }

        $archives = [
            'author' => 'seopress_titles_archives_author',
            'date'   => 'seopress_titles_archives_date',
            'search' => 'seopress_titles_archives_search',
        ];

        foreach ( $archives as $key => $prefix ) {
            $row = [];
            if ( isset( $titles[ $prefix . '_title' ] ) ) {
                $row['title'] = $this->translate( $titles[ $prefix . '_title' ] );
            }
            if ( isset( $titles[ $prefix . '_desc' ] ) ) {
                $row['desc'] = $this->translate( $titles[ $prefix . '_desc' ] );
            }
            if ( ! empty( $titles[ $prefix . '_noindex' ] ) ) {
                $row['noindex'] = '1';
            }
            if ( array_filter( $row, static function ( $v ) { return '' !== $v; } ) ) {
                $out['archives'][ $key ] = $row;
            }
        }

        $schema = [];
        if ( ! empty( $social['seopress_social_knowledge_type'] ) ) {
            $schema['org_type'] = ( 'Person' === $social['seopress_social_knowledge_type'] ) ? 'Person' : 'Organization';
        }
        if ( ! empty( $social['seopress_social_knowledge_name'] ) ) {
            $schema['org_name'] = sanitize_text_field( $social['seopress_social_knowledge_name'] );
        }
        if ( ! empty( $social['seopress_social_knowledge_img'] ) ) {
            $schema['org_logo'] = esc_url_raw( $social['seopress_social_knowledge_img'] );
        }
        if ( ! empty( $social['seopress_social_knowledge_phone'] ) ) {
            $schema['org_phone'] = sanitize_text_field( $social['seopress_social_knowledge_phone'] );
        }

        $profiles = [];
        foreach ( [ 'facebook', 'twitter', 'pinterest', 'instagram', 'youtube', 'linkedin' ] as $network ) {
            $key = "seopress_social_accounts_{$network}";
            if ( ! empty( $social[ $key ] ) ) {
                $value      = (string) $social[ $key ];
                $profiles[] = ( 0 === strpos( $value, 'http' ) )
                    ? esc_url_raw( $value )
                    : 'https://x.com/' . sanitize_text_field( ltrim( $value, '@' ) );
            }
        }
        if ( $profiles ) {
            $schema['_profiles'] = array_values( array_unique( $profiles ) );
        }

        if ( $schema ) {
            $out['_schema'] = $schema;
        }

        return array_filter( $out );
    }
}
