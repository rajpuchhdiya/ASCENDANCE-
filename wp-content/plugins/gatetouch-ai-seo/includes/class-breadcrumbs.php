<?php
defined( 'ABSPATH' ) || exit;

/**
 * Builds breadcrumb trails and shortcode output for frontend navigation.
 */
class GateTouch_Breadcrumbs {

    public function __construct() {
        // Breadcrumbs available via shortcode or function call
        add_shortcode( 'gatetouch_breadcrumbs', [ $this, 'shortcode' ] );
    }

    public function shortcode( $atts ) {
        if ( ! self::is_enabled() ) {
            return '';
        }

        return $this->render();
    }

    public static function defaults() {
        return [
            'enabled'        => '1',
            'placement'      => 'content',
            'separator'      => '›',
            'home_label'     => __( 'Home', 'gatetouch-ai-seo' ),
            'prefix'         => '',
            'show_blog'      => '',
            'show_current'   => '1',
            'link_current'   => '',
            'archive_format' => __( 'Archives for #taxonomy#', 'gatetouch-ai-seo' ),
            'search_format'  => __( 'Search for "#search_query#"', 'gatetouch-ai-seo' ),
            'error_format'   => __( '404 Error: Page not found', 'gatetouch-ai-seo' ),
        ];
    }

    public static function settings() {
        $settings = get_option( 'gatetouch_breadcrumb_settings', [] );
        return wp_parse_args( is_array( $settings ) ? $settings : [], self::defaults() );
    }

    public static function is_enabled() {
        $settings = self::settings();
        return ( $settings['enabled'] ?? '1' ) === '1';
    }

    public static function is_allowed_for_post( $post_id ) {
        if ( ! $post_id ) {
            return false;
        }

        $meta = get_post_meta( $post_id, GATETOUCH_META_KEY, true );
        if ( ! is_array( $meta ) ) {
            return true;
        }

        return ( $meta['breadcrumbs_enabled'] ?? '1' ) === '1';
    }

    public function render() {
        if ( ! self::is_enabled() || is_front_page() ) {
            return '';
        }

        global $post;
        if ( is_singular() && ! $post instanceof WP_Post ) {
            return '';
        }

        $opts      = self::settings();
        $separator = $opts['separator'] ?? ' › ';
        $home      = $opts['home_label'] ?? __( 'Home', 'gatetouch-ai-seo' );
        $prefix    = trim( (string) ( $opts['prefix'] ?? '' ) );
        $show_current = ( $opts['show_current'] ?? '1' ) === '1';
        $link_current = ( $opts['link_current'] ?? '' ) === '1';
        $items     = [];

        $items[] = '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( $home ) . '</a>';

        if ( is_singular() ) {
            if ( $post->post_type === 'post' ) {
                $cats = get_the_category( $post->ID );
                if ( ! empty( $cats ) ) {
                    $items[] = '<a href="' . esc_url( get_category_link( $cats[0]->term_id ) ) . '">'
	                               . esc_html( $cats[0]->name ) . '</a>';
                }
            }
            if ( $show_current ) {
                $current = esc_html( get_the_title( $post ) );
                $items[] = $link_current
                    ? '<a class="gatetouch-bc-current" href="' . esc_url( get_permalink( $post ) ) . '">' . $current . '</a>'
                    : '<span class="gatetouch-bc-current">' . $current . '</span>';
            }
        } elseif ( is_category() ) {
            $label = str_replace( '#taxonomy#', single_cat_title( '', false ), $opts['archive_format'] ?? 'Archives for #taxonomy#' );
            $items[] = '<span class="gatetouch-bc-current">' . esc_html( $label ) . '</span>';
        } elseif ( is_tag() ) {
            $label = str_replace( '#taxonomy#', single_tag_title( '', false ), $opts['archive_format'] ?? 'Archives for #taxonomy#' );
            $items[] = '<span class="gatetouch-bc-current">' . esc_html( $label ) . '</span>';
        } elseif ( is_archive() ) {
            $items[] = '<span class="gatetouch-bc-current">' . esc_html( get_the_archive_title() ) . '</span>';
        } elseif ( is_search() ) {
            $label = str_replace( '#search_query#', get_search_query(), $opts['search_format'] ?? 'Search for "#search_query#"' );
            $items[] = '<span class="gatetouch-bc-current">' . esc_html( $label ) . '</span>';
        } elseif ( is_404() ) {
            $items[] = '<span class="gatetouch-bc-current">' . esc_html( $opts['error_format'] ?? '404 Error: Page not found' ) . '</span>';
        }

        // Don't show "Home > Home" on front page
        if ( is_front_page() && count($items) <= 1 ) {
            return '';
        }

        $sep_html = ' <span class="gatetouch-bc-sep">' . esc_html( $separator ) . '</span> ';
        $prefix_html = $prefix !== '' ? '<span class="gatetouch-bc-prefix">' . esc_html( $prefix ) . '</span> ' : '';

        return '<nav class="gatetouch-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'gatetouch-ai-seo' ) . '">'
               . $prefix_html . implode( $sep_html, $items ) . '</nav>';
    }
}
