<?php
defined( 'ABSPATH' ) || exit;

/**
 * Dynamic SEO variable engine.
 *
 * Templates use #variable# syntax. For compatibility with sites migrating from
 * Yoast SEO / Rank Math, the %%variable%% syntax is accepted as an alias.
 *
 * Every value returned by parse() is PLAIN TEXT (tags stripped, entities decoded).
 * Callers are responsible for escaping at the point of output — do not escape here
 * or values get double-encoded when passed through esc_attr()/esc_html().
 */
class GateTouch_Variables {

    /**
     * Variable catalogue, grouped for the admin picker.
     *
     * @return array<string, array{label:string, vars:array<string,string>}>
     */
    public static function get_grouped_vars() {
        return [
            'basic' => [
                'label' => __( 'Basic', 'gatetouch-ai-seo' ),
                'vars'  => [
                    '#title#'         => __( 'Title of the post, page, product or term', 'gatetouch-ai-seo' ),
                    '#sep#'           => __( 'Title separator', 'gatetouch-ai-seo' ),
                    '#site_title#'    => __( 'Site title', 'gatetouch-ai-seo' ),
                    '#tagline#'       => __( 'Site tagline', 'gatetouch-ai-seo' ),
                    '#excerpt#'       => __( 'Excerpt (auto-generated from content if empty)', 'gatetouch-ai-seo' ),
                    '#url#'           => __( 'URL of the current page', 'gatetouch-ai-seo' ),
                    '#page#'          => __( '"Page 2 of 7" on paginated pages, empty otherwise', 'gatetouch-ai-seo' ),
                    '#pagenumber#'    => __( 'Current page number', 'gatetouch-ai-seo' ),
                    '#pagetotal#'     => __( 'Total number of pages', 'gatetouch-ai-seo' ),
                ],
            ],
            'date' => [
                'label' => __( 'Date & time', 'gatetouch-ai-seo' ),
                'vars'  => [
                    '#date#'          => __( 'Publish date of the post (current date elsewhere)', 'gatetouch-ai-seo' ),
                    '#modified#'      => __( 'Last modified date of the post', 'gatetouch-ai-seo' ),
                    '#currentdate#'   => __( "Today's date", 'gatetouch-ai-seo' ),
                    '#year#'          => __( 'Current year — great for "Best X in 2026" titles', 'gatetouch-ai-seo' ),
                    '#month#'         => __( 'Current month', 'gatetouch-ai-seo' ),
                    '#day#'           => __( 'Current day', 'gatetouch-ai-seo' ),
                    '#archive_date#'  => __( 'Date being viewed on a date archive', 'gatetouch-ai-seo' ),
                ],
            ],
            'post' => [
                'label' => __( 'Post & page', 'gatetouch-ai-seo' ),
                'vars'  => [
                    '#focus_keyword#'    => __( 'Focus keyword set for the post', 'gatetouch-ai-seo' ),
                    '#category#'         => __( 'Primary (first) category', 'gatetouch-ai-seo' ),
                    '#categories#'       => __( 'All categories, comma separated', 'gatetouch-ai-seo' ),
                    '#tag#'              => __( 'First tag', 'gatetouch-ai-seo' ),
                    '#tags#'             => __( 'All tags, comma separated', 'gatetouch-ai-seo' ),
                    '#post_type#'        => __( 'Post type singular label', 'gatetouch-ai-seo' ),
                    '#post_type_plural#' => __( 'Post type plural label', 'gatetouch-ai-seo' ),
                    '#parent_title#'     => __( 'Title of the parent page', 'gatetouch-ai-seo' ),
                    '#comment_count#'    => __( 'Number of comments', 'gatetouch-ai-seo' ),
                    '#post_id#'          => __( 'Post ID', 'gatetouch-ai-seo' ),
                ],
            ],
            'term' => [
                'label' => __( 'Category, tag & taxonomy', 'gatetouch-ai-seo' ),
                'vars'  => [
                    '#term#'              => __( 'Name of the current category / tag / term', 'gatetouch-ai-seo' ),
                    '#term_description#'  => __( 'Description of the current term', 'gatetouch-ai-seo' ),
                    '#term_count#'        => __( 'Number of posts in the term, e.g. "12"', 'gatetouch-ai-seo' ),
                    '#term_posts#'        => __( 'Number of posts with the right wording, e.g. "1 post" / "12 posts"', 'gatetouch-ai-seo' ),
                    '#term_parent#'       => __( 'Name of the parent term', 'gatetouch-ai-seo' ),
                    '#taxonomy_name#'     => __( 'Taxonomy label, e.g. "Categories"', 'gatetouch-ai-seo' ),
                    '#taxonomy_singular#' => __( 'Taxonomy singular label, e.g. "Category"', 'gatetouch-ai-seo' ),
                ],
            ],
            'author' => [
                'label' => __( 'Author', 'gatetouch-ai-seo' ),
                'vars'  => [
                    '#author_name#'        => __( 'Author display name', 'gatetouch-ai-seo' ),
                    '#author_first_name#'  => __( 'Author first name', 'gatetouch-ai-seo' ),
                    '#author_last_name#'   => __( 'Author last name', 'gatetouch-ai-seo' ),
                    '#author_bio#'         => __( 'Author biography', 'gatetouch-ai-seo' ),
                    '#author_job_title#'   => __( 'Author job title (set in their profile)', 'gatetouch-ai-seo' ),
                    '#author_post_count#'  => __( 'Number of posts by the author', 'gatetouch-ai-seo' ),
                ],
            ],
            'search' => [
                'label' => __( 'Search & archives', 'gatetouch-ai-seo' ),
                'vars'  => [
                    '#search_query#'   => __( 'The search term that was entered', 'gatetouch-ai-seo' ),
                    '#search_count#'   => __( 'Number of search results found', 'gatetouch-ai-seo' ),
                    '#archive_title#'  => __( 'Title of the current archive', 'gatetouch-ai-seo' ),
                ],
            ],
            'woocommerce' => [
                'label' => __( 'WooCommerce', 'gatetouch-ai-seo' ),
                'vars'  => [
                    '#wc_price#'          => __( 'Product price, formatted with currency', 'gatetouch-ai-seo' ),
                    '#wc_sku#'            => __( 'Product SKU', 'gatetouch-ai-seo' ),
                    '#wc_brand#'          => __( 'Product brand', 'gatetouch-ai-seo' ),
                    '#wc_short_desc#'     => __( 'Product short description', 'gatetouch-ai-seo' ),
                    '#wc_stock#'          => __( 'Stock status, e.g. "In stock"', 'gatetouch-ai-seo' ),
                    '#wc_category#'       => __( 'First product category', 'gatetouch-ai-seo' ),
                    '#wc_product_count#'  => __( 'Number of products in the current category', 'gatetouch-ai-seo' ),
                    '#wc_rating#'         => __( 'Average product rating', 'gatetouch-ai-seo' ),
                ],
            ],
            'custom' => [
                'label' => __( 'Custom', 'gatetouch-ai-seo' ),
                'vars'  => [
                    '#cf_your_field#'      => __( 'Value of a custom field — replace "your_field" with the meta key', 'gatetouch-ai-seo' ),
                    '#tax_your_taxonomy#'  => __( 'Terms of a custom taxonomy — replace "your_taxonomy" with its slug', 'gatetouch-ai-seo' ),
                ],
            ],
        ];
    }

    /**
     * Flat map of variable => description. Used by legacy callers.
     */
    public static function get_supported_vars() {
        $flat = [];
        foreach ( self::get_grouped_vars() as $group ) {
            $flat += $group['vars'];
        }
        return $flat;
    }

    /**
     * Replace every variable in $text with its value for the given context.
     *
     * @param string           $text    Template string.
     * @param int|WP_Post|WP_Term|WP_User $id  Object (or object ID) to resolve against. 0 = current query.
     * @param string           $context One of post|term|user|auto.
     * @return string Plain text, unescaped.
     */
    public static function parse( $text, $id = 0, $context = 'auto' ) {
        $text = (string) $text;
        if ( '' === $text ) {
            return '';
        }
        if ( false === strpos( $text, '#' ) && false === strpos( $text, '%%' ) ) {
            return $text;
        }

        // Normalise Yoast / Rank Math syntax so one replacement pass handles both.
        $text = preg_replace( '/%%([a-z0-9_]+)%%/i', '#$1#', $text );

        $object       = self::resolve_object( $id, $context );
        $replacements = self::build_replacements( $object );

        // Pattern-based variables (#cf_x#, #tax_x#) need a callback pass.
        $text = preg_replace_callback(
            '/#(cf|tax)_([A-Za-z0-9_\-]+)#/',
            function ( $m ) use ( $object ) {
                return 'cf' === $m[1]
                    ? self::custom_field_value( $m[2], $object )
                    : self::custom_taxonomy_value( $m[2], $object );
            },
            $text
        );

        $text = str_replace( array_keys( $replacements ), array_values( $replacements ), $text );

        // Drop any variable that had no value so templates never leak "#foo#".
        $text = preg_replace( '/#[A-Za-z0-9_\-]+#/', '', $text );

        return self::tidy( $text );
    }

    /**
     * Collapse the whitespace and dangling separators left behind by empty variables.
     */
    private static function tidy( $text ) {
        $sep = preg_quote( self::separator(), '/' );

        $text = preg_replace( '/\s+/u', ' ', $text );
        // "Title | | Site" -> "Title | Site"
        $text = preg_replace( '/(\s*' . $sep . '\s*){2,}/u', ' ' . self::separator() . ' ', $text );
        // Leading/trailing separators.
        $text = preg_replace( '/^\s*' . $sep . '\s*|\s*' . $sep . '\s*$/u', '', $text );
        // Leftover punctuation from empty values, e.g. "Shoes ,  Blue" or "Name - ".
        $text = preg_replace( '/\s+([,.;:])/u', '$1', $text );
        $text = preg_replace( '/([,;:])\s*([,.;:])/u', '$1', $text );

        return trim( $text );
    }

    public static function separator() {
        $sa = get_option( 'gatetouch_search_appearance', [] );
        $sep = $sa['global']['title_separator'] ?? get_option( 'gatetouch_title_separator', '|' );
        return $sep ? $sep : '|';
    }

    /**
     * Turn a loose id/context pair into the object the variables describe.
     *
     * @return array{type:string, post:?WP_Post, term:?WP_Term, user:?WP_User}
     */
    private static function resolve_object( $id, $context ) {
        $out = [ 'type' => 'none', 'post' => null, 'term' => null, 'user' => null ];

        // Explicit objects win.
        if ( $id instanceof \WP_Post ) {
            return [ 'type' => 'post', 'post' => $id, 'term' => null, 'user' => null ];
        }
        if ( $id instanceof \WP_Term ) {
            return [ 'type' => 'term', 'post' => null, 'term' => $id, 'user' => null ];
        }
        if ( $id instanceof \WP_User ) {
            return [ 'type' => 'user', 'post' => null, 'term' => null, 'user' => $id ];
        }

        $id = (int) $id;

        if ( $id ) {
            if ( 'term' === $context ) {
                $term = get_term( $id );
                return [ 'type' => 'term', 'post' => null, 'term' => ( $term instanceof \WP_Term ? $term : null ), 'user' => null ];
            }
            if ( 'user' === $context ) {
                $user = get_userdata( $id );
                return [ 'type' => 'user', 'post' => null, 'term' => null, 'user' => ( $user ?: null ) ];
            }
            $post = get_post( $id );
            return [ 'type' => 'post', 'post' => ( $post ?: null ), 'term' => null, 'user' => null ];
        }

        // No id — read the main query.
        if ( is_admin() ) {
            return $out;
        }

        if ( is_singular() ) {
            $post = get_post( get_queried_object_id() );
            return [ 'type' => 'post', 'post' => ( $post ?: null ), 'term' => null, 'user' => null ];
        }
        if ( is_category() || is_tag() || is_tax() ) {
            $term = get_queried_object();
            return [ 'type' => 'term', 'post' => null, 'term' => ( $term instanceof \WP_Term ? $term : null ), 'user' => null ];
        }
        if ( is_author() ) {
            $user = get_queried_object();
            return [ 'type' => 'user', 'post' => null, 'term' => null, 'user' => ( $user instanceof \WP_User ? $user : null ) ];
        }

        return $out;
    }

    /**
     * Build the full replacement map for a resolved object.
     */
    private static function build_replacements( array $object ) {
        $sep = self::separator();

        $map = [
            '#sep#'           => $sep,
            '#separator#'     => $sep,
            '#site_title#'    => self::text( get_bloginfo( 'name' ) ),
            '#sitename#'      => self::text( get_bloginfo( 'name' ) ),
            '#tagline#'       => self::text( get_bloginfo( 'description' ) ),
            '#site_tagline#'  => self::text( get_bloginfo( 'description' ) ),
            '#sitedesc#'      => self::text( get_bloginfo( 'description' ) ),
            '#currentdate#'   => date_i18n( get_option( 'date_format' ) ),
            '#currenttime#'   => date_i18n( get_option( 'time_format' ) ),
            '#year#'          => date_i18n( 'Y' ),
            '#currentyear#'   => date_i18n( 'Y' ),
            '#month#'         => date_i18n( 'F' ),
            '#currentmonth#'  => date_i18n( 'F' ),
            '#day#'           => date_i18n( 'j' ),
            '#currentday#'    => date_i18n( 'j' ),
            '#date#'          => date_i18n( get_option( 'date_format' ) ),
        ];

        $map += self::pagination_replacements();
        $map += self::query_replacements();

        switch ( $object['type'] ) {
            case 'post':
                $map = self::post_replacements( $object['post'] ) + $map;
                break;
            case 'term':
                $map = self::term_replacements( $object['term'] ) + $map;
                break;
            case 'user':
                $map = self::user_replacements( $object['user'] ) + $map;
                break;
        }

        /**
         * Filter the resolved variable map, e.g. to register site-specific variables.
         *
         * @param array $map    Variable => value (plain text).
         * @param array $object Resolved object context.
         */
        return apply_filters( 'gatetouch_seo_variables', $map, $object );
    }

    private static function post_replacements( $post ) {
        if ( ! $post instanceof \WP_Post ) {
            return [];
        }

        $meta      = get_post_meta( $post->ID, GATETOUCH_META_KEY, true ) ?: [];
        $pt_object = get_post_type_object( $post->post_type );

        $map = [
            '#title#'            => self::text( get_the_title( $post ) ),
            '#post_title#'       => self::text( get_the_title( $post ) ),
            '#post_id#'          => (string) $post->ID,
            '#url#'              => get_permalink( $post ),
            '#excerpt#'          => self::excerpt( $post ),
            '#excerpt_only#'     => self::text( $post->post_excerpt ),
            '#focus_keyword#'    => self::text( $meta['focus_keyword'] ?? '' ),
            '#focuskw#'          => self::text( $meta['focus_keyword'] ?? '' ),
            '#date#'             => get_the_date( get_option( 'date_format' ), $post ),
            '#modified#'         => get_the_modified_date( get_option( 'date_format' ), $post ),
            '#comment_count#'    => (string) (int) $post->comment_count,
            '#post_type#'        => $pt_object ? self::text( $pt_object->labels->singular_name ) : '',
            '#post_type_plural#' => $pt_object ? self::text( $pt_object->labels->name ) : '',
        ];

        if ( $post->post_parent ) {
            $map['#parent_title#'] = self::text( get_the_title( $post->post_parent ) );
        }

        // Categories / tags — resolved generically so custom taxonomies work too.
        $primary_cat = self::primary_term( $post, 'category' );
        if ( $primary_cat ) {
            $map['#category#']         = self::text( $primary_cat->name );
            $map['#primary_category#'] = self::text( $primary_cat->name );
        }
        $map['#categories#'] = self::term_list( $post, 'category' );
        $tags                = get_the_terms( $post, 'post_tag' );
        if ( $tags && ! is_wp_error( $tags ) ) {
            $map['#tag#'] = self::text( $tags[0]->name );
        }
        $map['#tags#'] = self::term_list( $post, 'post_tag' );

        // Author of the post.
        $map += self::user_replacements( get_userdata( (int) $post->post_author ) );

        if ( 'product' === $post->post_type ) {
            $map += self::product_replacements( $post );
        }

        return array_filter( $map, static function ( $v ) { return '' !== $v && null !== $v; } );
    }

    private static function term_replacements( $term ) {
        if ( ! $term instanceof \WP_Term ) {
            return [];
        }

        $tax = get_taxonomy( $term->taxonomy );

        $map = [
            '#title#'             => self::text( $term->name ),
            '#term#'              => self::text( $term->name ),
            '#term_title#'        => self::text( $term->name ),
            '#taxonomy_title#'    => self::text( $term->name ),
            '#term_description#'  => self::text( $term->description ),
            '#taxonomy_desc#'     => self::text( $term->description ),
            '#term_count#'        => (string) (int) $term->count,
            // Pre-pluralised so default templates never read "1 posts".
            '#term_posts#'        => sprintf(
                /* translators: %s: number of posts in a category, tag or term. */
                _n( '%s post', '%s posts', (int) $term->count, 'gatetouch-ai-seo' ),
                number_format_i18n( (int) $term->count )
            ),
            '#url#'               => (string) get_term_link( $term ),
            '#taxonomy_name#'     => $tax ? self::text( $tax->labels->name ) : '',
            '#taxonomy_singular#' => $tax ? self::text( $tax->labels->singular_name ) : '',
            '#archive_title#'     => self::text( $term->name ),
        ];

        if ( $term->parent ) {
            $parent = get_term( $term->parent, $term->taxonomy );
            if ( $parent instanceof \WP_Term ) {
                $map['#term_parent#'] = self::text( $parent->name );
            }
        }

        if ( in_array( $term->taxonomy, [ 'product_cat', 'product_tag' ], true ) ) {
            $map['#wc_product_count#'] = (string) (int) $term->count;
            $map['#wc_category#']      = self::text( $term->name );
        }

        return array_filter( $map, static function ( $v ) { return '' !== $v && null !== $v; } );
    }

    private static function user_replacements( $user ) {
        if ( ! $user instanceof \WP_User ) {
            return [];
        }

        $map = [
            '#author_name#'       => self::text( $user->display_name ),
            '#author_first_name#' => self::text( $user->first_name ),
            '#author_last_name#'  => self::text( $user->last_name ),
            '#author_bio#'        => self::text( $user->description ),
            '#author_job_title#'  => self::text( get_user_meta( $user->ID, 'gatetouch_job_title', true ) ),
            '#author_post_count#' => (string) (int) count_user_posts( $user->ID ),
        ];

        if ( is_author() && ! is_singular() ) {
            $map['#title#']         = self::text( $user->display_name );
            $map['#archive_title#'] = self::text( $user->display_name );
            $map['#url#']           = get_author_posts_url( $user->ID );
        }

        return array_filter( $map, static function ( $v ) { return '' !== $v && null !== $v; } );
    }

    private static function product_replacements( \WP_Post $post ) {
        if ( ! function_exists( 'wc_get_product' ) ) {
            return [];
        }

        $product = wc_get_product( $post->ID );
        if ( ! $product ) {
            return [];
        }

        $stock = $product->is_in_stock()
            ? __( 'In stock', 'gatetouch-ai-seo' )
            : __( 'Out of stock', 'gatetouch-ai-seo' );

        $map = [
            '#wc_price#'      => self::text( wp_strip_all_tags( wc_price( $product->get_price() ) ) ),
            '#wc_sku#'        => self::text( $product->get_sku() ),
            '#wc_short_desc#' => self::text( $product->get_short_description() ),
            '#wc_stock#'      => $stock,
            '#wc_rating#'     => $product->get_average_rating() > 0 ? (string) $product->get_average_rating() : '',
        ];

        $cats = get_the_terms( $post, 'product_cat' );
        if ( $cats && ! is_wp_error( $cats ) ) {
            $map['#wc_category#'] = self::text( $cats[0]->name );
        }

        $brand = GateTouch_WooCommerce::get_brand_name( $post->ID );
        if ( $brand ) {
            $map['#wc_brand#'] = self::text( $brand );
        }

        return array_filter( $map, static function ( $v ) { return '' !== $v && null !== $v; } );
    }

    private static function pagination_replacements() {
        global $wp_query;

        $current = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
        $total   = isset( $wp_query->max_num_pages ) ? max( 1, (int) $wp_query->max_num_pages ) : 1;

        $page = '';
        if ( $total > 1 && $current > 1 ) {
            /* translators: 1: current page number, 2: total number of pages */
            $page = sprintf( __( 'Page %1$d of %2$d', 'gatetouch-ai-seo' ), $current, $total );
        }

        return [
            '#page#'       => $page,
            '#pagenumber#' => (string) $current,
            '#pagetotal#'  => (string) $total,
        ];
    }

    private static function query_replacements() {
        $map = [];

        if ( is_search() ) {
            global $wp_query;
            $map['#search_query#'] = self::text( get_search_query() );
            $map['#title#']        = self::text( get_search_query() );
            $map['#search_count#'] = (string) (int) ( $wp_query->found_posts ?? 0 );
        }

        if ( is_date() ) {
            if ( is_year() ) {
                $date = get_the_date( 'Y' );
            } elseif ( is_month() ) {
                $date = get_the_date( 'F Y' );
            } else {
                $date = get_the_date( get_option( 'date_format' ) );
            }
            $map['#archive_date#']  = $date;
            $map['#date#']          = $date;
            $map['#title#']         = $date;
            $map['#archive_title#'] = $date;
        }

        if ( is_post_type_archive() ) {
            $pt = get_post_type_object( get_query_var( 'post_type' ) ?: get_post_type() );
            if ( $pt ) {
                $map['#title#']            = self::text( $pt->labels->name );
                $map['#archive_title#']    = self::text( $pt->labels->name );
                $map['#post_type#']        = self::text( $pt->labels->singular_name );
                $map['#post_type_plural#'] = self::text( $pt->labels->name );
            }
        }

        if ( ! is_admin() && ! isset( $map['#url#'] ) ) {
            $map['#url#'] = GateTouch_Search_Appearance::current_url();
        }

        return $map;
    }

    private static function custom_field_value( $key, array $object ) {
        if ( $object['post'] instanceof \WP_Post ) {
            $value = get_post_meta( $object['post']->ID, $key, true );
        } elseif ( $object['term'] instanceof \WP_Term ) {
            $value = get_term_meta( $object['term']->term_id, $key, true );
        } elseif ( $object['user'] instanceof \WP_User ) {
            $value = get_user_meta( $object['user']->ID, $key, true );
        } else {
            return '';
        }

        return is_scalar( $value ) ? self::text( $value ) : '';
    }

    private static function custom_taxonomy_value( $taxonomy, array $object ) {
        if ( ! $object['post'] instanceof \WP_Post ) {
            return '';
        }
        return self::term_list( $object['post'], $taxonomy );
    }

    private static function term_list( $post, $taxonomy ) {
        $terms = get_the_terms( $post, $taxonomy );
        if ( ! $terms || is_wp_error( $terms ) ) {
            return '';
        }
        return self::text( implode( ', ', wp_list_pluck( $terms, 'name' ) ) );
    }

    /**
     * Primary term for a post, honouring Yoast/Rank Math primary-term meta when present.
     */
    public static function primary_term( $post, $taxonomy ) {
        $post_id = $post instanceof \WP_Post ? $post->ID : (int) $post;

        foreach ( [ '_gatetouch_primary_' . $taxonomy, '_yoast_wpseo_primary_' . $taxonomy, 'rank_math_primary_' . $taxonomy ] as $key ) {
            $primary_id = (int) get_post_meta( $post_id, $key, true );
            if ( $primary_id ) {
                $term = get_term( $primary_id, $taxonomy );
                if ( $term instanceof \WP_Term ) {
                    return $term;
                }
            }
        }

        $terms = get_the_terms( $post_id, $taxonomy );
        if ( $terms && ! is_wp_error( $terms ) ) {
            return $terms[0];
        }

        return null;
    }

    private static function excerpt( \WP_Post $post ) {
        if ( ! empty( $post->post_excerpt ) ) {
            return self::text( $post->post_excerpt );
        }

        $content = strip_shortcodes( $post->post_content );
        $content = excerpt_remove_blocks( $content );

        return self::text( wp_trim_words( $content, 55, '' ) );
    }

    /**
     * Normalise any value to clean, single-line plain text.
     */
    private static function text( $value ) {
        $value = wp_strip_all_tags( (string) $value, true );
        $value = html_entity_decode( $value, ENT_QUOTES, get_bloginfo( 'charset' ) );
        return trim( preg_replace( '/\s+/u', ' ', $value ) );
    }
}
