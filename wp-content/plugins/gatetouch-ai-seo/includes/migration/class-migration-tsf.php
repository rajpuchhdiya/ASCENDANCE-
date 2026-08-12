<?php
defined( 'ABSPATH' ) || exit;

/**
 * The SEO Framework migration adapter.
 *
 * TSF still uses the historical Genesis meta keys for posts. Term data is a
 * single serialised array under `autodescription-term-settings`. It has no
 * template variable syntax to translate — values are literal strings.
 */
class GateTouch_Migration_Tsf extends GateTouch_Migration_Source {

    public function slug() {
        return 'tsf';
    }

    public function label() {
        return 'The SEO Framework';
    }

    public function is_detected() {
        if ( defined( 'THE_SEO_FRAMEWORK_VERSION' ) ) {
            return true;
        }
        if ( get_option( 'autodescription-site-settings' ) ) {
            return true;
        }

        return $this->count_postmeta( array_keys( $this->meta_map() ) ) > 0;
    }

    private function meta_map() {
        return [
            '_genesis_title'         => 'meta_title',
            '_genesis_description'   => 'meta_description',
            '_genesis_canonical_uri' => 'canonical',
            '_genesis_noindex'       => 'noindex_raw',
            '_genesis_nofollow'      => 'nofollow_raw',
            '_genesis_noarchive'     => 'noarchive_raw',
            '_open_graph_title'      => 'og_title',
            '_open_graph_description' => 'og_description',
            '_social_image_url'      => 'og_image',
            '_twitter_title'         => 'twitter_title',
            '_twitter_description'   => 'twitter_description',
        ];
    }

    public function counts() {
        return [
            'posts'     => $this->count_postmeta( array_keys( $this->meta_map() ) ),
            'terms'     => count( $this->term_settings() ),
            'users'     => 0,
            'redirects' => 0,
        ];
    }

    public function get_posts( $offset, $limit ) {
        $rows = $this->fetch_postmeta_page( array_keys( $this->meta_map() ), $offset, $limit );
        $out  = [];

        foreach ( $rows as $post_id => $raw ) {
            $meta = [];

            foreach ( $this->meta_map() as $source_key => $target_key ) {
                if ( isset( $raw[ $source_key ] ) && '' !== $raw[ $source_key ] ) {
                    $meta[ $target_key ] = $raw[ $source_key ];
                }
            }

            // TSF uses a tri-state: 0 = default, 1 = force on, -1 = force off.
            foreach ( [ 'noindex_raw' => 'noindex', 'nofollow_raw' => 'nofollow', 'noarchive_raw' => 'noarchive' ] as $raw_key => $target ) {
                if ( isset( $meta[ $raw_key ] ) ) {
                    $meta[ $target ] = ( '1' === (string) $meta[ $raw_key ] ) ? '1' : '';
                    unset( $meta[ $raw_key ] );
                }
            }

            if ( isset( $meta['og_image'] ) ) {
                $meta['og_image'] = $this->image_url( $meta['og_image'] );
            }

            $meta = $this->compact_meta( $meta );
            if ( $meta ) {
                $out[ $post_id ] = $meta;
            }
        }

        return $out;
    }

    /**
     * All term settings, keyed by term ID.
     */
    private function term_settings() {
        static $cache = null;
        if ( null !== $cache ) {
            return $cache;
        }

        $option = get_option( 'autodescription-term-settings' );
        $cache  = [];

        if ( ! is_array( $option ) ) {
            return $cache;
        }

        foreach ( $option as $term_id => $values ) {
            if ( ! is_array( $values ) ) {
                continue;
            }

            $meta = $this->compact_meta( [
                'meta_title'       => (string) ( $values['doctitle'] ?? '' ),
                'meta_description' => (string) ( $values['description'] ?? '' ),
                'canonical'        => (string) ( $values['canonical'] ?? '' ),
                'noindex'          => ( '1' === (string) ( $values['noindex'] ?? '' ) ) ? '1' : '',
                'nofollow'         => ( '1' === (string) ( $values['nofollow'] ?? '' ) ) ? '1' : '',
                'noarchive'        => ( '1' === (string) ( $values['noarchive'] ?? '' ) ) ? '1' : '',
            ] );

            if ( $meta ) {
                $cache[ (int) $term_id ] = $meta;
            }
        }

        return $cache;
    }

    public function get_terms( $offset, $limit ) {
        return array_slice( $this->term_settings(), (int) $offset, (int) $limit, true );
    }

    public function get_settings() {
        $settings = get_option( 'autodescription-site-settings', [] );
        if ( ! is_array( $settings ) ) {
            return [];
        }

        $out = [ 'global' => [] ];

        if ( ! empty( $settings['title_separator'] ) ) {
            $separators = [
                'pipe'   => '|',
                'dash'   => '-',
                'ndash'  => '–',
                'mdash'  => '—',
                'bull'   => '•',
                'middot' => '·',
                'lsaquo' => '‹',
                'rsaquo' => '›',
                'frasl'  => '/',
                'colon'  => ':',
            ];
            $out['global']['title_separator'] = $separators[ $settings['title_separator'] ] ?? '|';
        }

        if ( ! empty( $settings['homepage_title'] ) ) {
            $out['global']['homepage_title'] = sanitize_text_field( $settings['homepage_title'] );
        }
        if ( ! empty( $settings['homepage_description'] ) ) {
            $out['global']['homepage_desc'] = sanitize_textarea_field( $settings['homepage_description'] );
        }
        if ( ! empty( $settings['social_image_fb_url'] ) ) {
            $out['global']['og_default_image'] = esc_url_raw( $settings['social_image_fb_url'] );
        }
        if ( ! empty( $settings['twitter_site'] ) ) {
            $out['global']['twitter_site'] = sanitize_text_field( $settings['twitter_site'] );
        }

        $schema = [];
        if ( ! empty( $settings['knowledge_type'] ) ) {
            $schema['org_type'] = ( 'person' === $settings['knowledge_type'] ) ? 'Person' : 'Organization';
        }
        if ( ! empty( $settings['knowledge_name'] ) ) {
            $schema['org_name'] = sanitize_text_field( $settings['knowledge_name'] );
        }
        if ( ! empty( $settings['knowledge_logo_url'] ) ) {
            $schema['org_logo'] = esc_url_raw( $settings['knowledge_logo_url'] );
        }

        $profiles = [];
        foreach ( [ 'knowledge_facebook', 'knowledge_twitter', 'knowledge_instagram', 'knowledge_youtube', 'knowledge_linkedin', 'knowledge_pinterest' ] as $key ) {
            if ( ! empty( $settings[ $key ] ) ) {
                $profiles[] = esc_url_raw( $settings[ $key ] );
            }
        }
        if ( $profiles ) {
            $schema['_profiles'] = array_values( array_unique( array_filter( $profiles ) ) );
        }

        if ( $schema ) {
            $out['_schema'] = $schema;
        }

        return array_filter( $out );
    }
}
