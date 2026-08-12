<?php
defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce SEO integration.
 *
 * Produces the Product / Offer / AggregateRating markup that drives price,
 * availability and review stars in Google Shopping and organic rich results,
 * plus the product-specific Open Graph tags read by Facebook and Pinterest
 * catalogues.
 *
 * WooCommerce ships its own JSON-LD. Running both produces duplicate Product
 * nodes, which Google reports as invalid, so by default we switch Woo's off and
 * emit the richer graph ourselves. That is reversible in settings.
 */
class GateTouch_WooCommerce {

    public function __construct() {
        // WooCommerce itself loads on plugins_loaded:10, after this plugin boots,
        // so nothing here may test for it at construction time.
        add_action( 'init', [ $this, 'boot' ], 5 );
    }

    public function boot() {
        if ( ! self::is_active() ) {
            return;
        }

        add_action( 'wp_loaded', [ $this, 'maybe_disable_woo_structured_data' ] );
        add_filter( 'gatetouch_seo_taxonomies', [ $this, 'ensure_product_taxonomies' ] );
    }

    public static function is_active() {
        return class_exists( 'WooCommerce' );
    }

    public static function settings() {
        $settings = GateTouch_Search_Appearance::settings();
        return $settings['woocommerce'] ?? [];
    }

    public static function setting( $key, $fallback = '' ) {
        $settings = self::settings();
        return $settings[ $key ] ?? $fallback;
    }

    public function ensure_product_taxonomies( $taxonomies ) {
        foreach ( [ 'product_cat', 'product_tag' ] as $taxonomy ) {
            if ( taxonomy_exists( $taxonomy ) && ! in_array( $taxonomy, $taxonomies, true ) ) {
                $taxonomies[] = $taxonomy;
            }
        }
        return $taxonomies;
    }

    /**
     * Stop WooCommerce emitting its own Product/Breadcrumb JSON-LD.
     */
    public function maybe_disable_woo_structured_data() {
        if ( '0' === self::setting( 'replace_woo_schema', '1' ) ) {
            return;
        }

        if ( ! isset( WC()->structured_data ) ) {
            return;
        }

        $sd = WC()->structured_data;

        remove_action( 'woocommerce_before_main_content',       [ $sd, 'generate_website_data' ], 30 );
        remove_action( 'woocommerce_breadcrumb',                [ $sd, 'generate_breadcrumblist_data' ], 10 );
        remove_action( 'woocommerce_shop_loop',                 [ $sd, 'generate_product_data' ], 10 );
        remove_action( 'woocommerce_single_product_summary',    [ $sd, 'generate_product_data' ], 60 );
        remove_action( 'woocommerce_review_meta',               [ $sd, 'generate_review_data' ], 20 );
        remove_action( 'woocommerce_email_order_details',       [ $sd, 'generate_order_data' ], 20 );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Product data
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Brand name for a product, checking every common brand taxonomy and the
     * Woo 9.x native brand taxonomy.
     */
    public static function get_brand_name( $product_id ) {
        $taxonomies = apply_filters(
            'gatetouch_product_brand_taxonomies',
            [ 'product_brand', 'pwb-brand', 'yith_product_brand', 'pa_brand', 'brand' ]
        );

        foreach ( $taxonomies as $taxonomy ) {
            if ( ! taxonomy_exists( $taxonomy ) ) {
                continue;
            }
            $terms = get_the_terms( $product_id, $taxonomy );
            if ( $terms && ! is_wp_error( $terms ) ) {
                return $terms[0]->name;
            }
        }

        // Fall back to a configured site-wide brand, then the site name.
        $fallback = self::setting( 'default_brand', '' );
        return $fallback ?: '';
    }

    /**
     * Map WooCommerce stock status onto a schema.org availability URL.
     */
    private static function availability( $product ) {
        if ( ! $product->is_in_stock() ) {
            return 'https://schema.org/OutOfStock';
        }
        if ( $product->is_on_backorder( 1 ) ) {
            return 'https://schema.org/BackOrder';
        }
        if ( $product->is_type( 'external' ) ) {
            return 'https://schema.org/InStock';
        }
        return 'https://schema.org/InStock';
    }

    /**
     * Full Product node for the schema graph.
     *
     * @return array|null
     */
    public static function product_schema( $product_id ) {
        if ( ! function_exists( 'wc_get_product' ) ) {
            return null;
        }

        $product = wc_get_product( $product_id );
        if ( ! $product ) {
            return null;
        }

        $permalink = get_permalink( $product_id );
        $currency  = get_woocommerce_currency();

        $schema = [
            '@type'       => 'Product',
            '@id'         => $permalink . '#product',
            'name'        => wp_strip_all_tags( $product->get_name() ),
            'url'         => $permalink,
            'description' => self::product_description( $product ),
        ];

        // Identifiers. Google requires at least one to show merchant listings.
        if ( $product->get_sku() ) {
            $schema['sku']     = $product->get_sku();
            $schema['mpn']     = $product->get_sku();
        }

        foreach ( [ 'gtin', 'gtin8', 'gtin12', 'gtin13', 'gtin14' ] as $gtin_key ) {
            $gtin = get_post_meta( $product_id, '_' . $gtin_key, true );
            if ( $gtin ) {
                $schema[ $gtin_key ] = $gtin;
                break;
            }
        }

        $brand = self::get_brand_name( $product_id );
        if ( $brand ) {
            $schema['brand'] = [ '@type' => 'Brand', 'name' => $brand ];
        }

        // Images — the gallery matters for Shopping surfaces.
        $images = [];
        $main   = get_the_post_thumbnail_url( $product_id, 'full' );
        if ( $main ) {
            $images[] = $main;
        }
        foreach ( array_slice( (array) $product->get_gallery_image_ids(), 0, 5 ) as $image_id ) {
            $url = wp_get_attachment_image_url( $image_id, 'full' );
            if ( $url && ! in_array( $url, $images, true ) ) {
                $images[] = $url;
            }
        }
        if ( $images ) {
            $schema['image'] = $images;
        }

        // Category breadcrumb-style string.
        $cats = get_the_terms( $product_id, 'product_cat' );
        if ( $cats && ! is_wp_error( $cats ) ) {
            $schema['category'] = $cats[0]->name;
        }

        $schema += self::offers( $product, $currency, $permalink );

        $rating = self::rating( $product );
        if ( $rating ) {
            $schema += $rating;
        }

        $reviews = self::reviews( $product );
        if ( $reviews ) {
            $schema['review'] = $reviews;
        }

        /**
         * Filter the generated Product schema node.
         *
         * @param array      $schema  Product node.
         * @param WC_Product $product Product object.
         */
        return apply_filters( 'gatetouch_product_schema', $schema, $product );
    }

    private static function product_description( $product ) {
        $desc = $product->get_short_description() ?: $product->get_description();
        $desc = wp_strip_all_tags( strip_shortcodes( $desc ) );
        $desc = trim( preg_replace( '/\s+/u', ' ', $desc ) );

        return GateTouch_Helpers::truncate( $desc, 5000, '' );
    }

    /**
     * Offer or AggregateOffer, depending on whether the product has variations.
     */
    private static function offers( $product, $currency, $permalink ) {
        $availability   = self::availability( $product );
        $price_valid_to = gmdate( 'Y-m-d', strtotime( '+1 year' ) );

        $shared = [
            'priceCurrency'   => $currency,
            'availability'    => $availability,
            'url'             => $permalink,
            'priceValidUntil' => $price_valid_to,
            'seller'          => [
                '@type' => 'Organization',
                'name'  => get_bloginfo( 'name' ),
                'url'   => home_url( '/' ),
            ],
        ];

        if ( $product->is_type( 'variable' ) ) {
            $prices = $product->get_variation_prices( true );

            if ( ! empty( $prices['price'] ) ) {
                return [
                    'offers' => array_merge( $shared, [
                        '@type'      => 'AggregateOffer',
                        'lowPrice'   => wc_format_decimal( current( $prices['price'] ), wc_get_price_decimals() ),
                        'highPrice'  => wc_format_decimal( end( $prices['price'] ), wc_get_price_decimals() ),
                        'offerCount' => count( $prices['price'] ),
                    ] ),
                ];
            }
        }

        $price = $product->get_price();
        if ( '' === $price || null === $price ) {
            return [];
        }

        return [
            'offers' => array_merge( $shared, [
                '@type' => 'Offer',
                'price' => wc_format_decimal( $price, wc_get_price_decimals() ),
            ] ),
        ];
    }

    private static function rating( $product ) {
        if ( ! $product->get_rating_count() ) {
            return [];
        }

        return [
            'aggregateRating' => [
                '@type'       => 'AggregateRating',
                'ratingValue' => (string) $product->get_average_rating(),
                'reviewCount' => (string) $product->get_review_count(),
                'bestRating'  => '5',
                'worstRating' => '1',
            ],
        ];
    }

    /**
     * The most recent approved reviews, capped so the graph stays lean.
     */
    private static function reviews( $product ) {
        if ( '0' === self::setting( 'review_schema', '1' ) ) {
            return [];
        }

        $comments = get_comments( [
            'post_id' => $product->get_id(),
            'status'  => 'approve',
            'type'    => 'review',
            'number'  => 5,
        ] );

        $reviews = [];
        foreach ( $comments as $comment ) {
            $rating = (int) get_comment_meta( $comment->comment_ID, 'rating', true );
            if ( ! $rating ) {
                continue;
            }

            $reviews[] = [
                '@type'         => 'Review',
                'reviewRating'  => [
                    '@type'       => 'Rating',
                    'ratingValue' => (string) $rating,
                    'bestRating'  => '5',
                    'worstRating' => '1',
                ],
                'author'        => [ '@type' => 'Person', 'name' => $comment->comment_author ],
                'datePublished' => gmdate( 'c', strtotime( $comment->comment_date_gmt ) ),
                'reviewBody'    => wp_strip_all_tags( $comment->comment_content ),
            ];
        }

        return $reviews;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Open Graph
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * product:* Open Graph tags. Read by Facebook/Instagram/Pinterest catalogues.
     */
    public static function output_product_og_tags( $product_id ) {
        if ( ! function_exists( 'wc_get_product' ) ) {
            return;
        }

        $product = wc_get_product( $product_id );
        if ( ! $product ) {
            return;
        }

        $price = $product->get_price();
        if ( '' !== $price && null !== $price ) {
            echo '<meta property="product:price:amount" content="' . esc_attr( wc_format_decimal( $price, wc_get_price_decimals() ) ) . '" />' . "\n";
            echo '<meta property="product:price:currency" content="' . esc_attr( get_woocommerce_currency() ) . '" />' . "\n";
        }

        echo '<meta property="product:availability" content="' . esc_attr( $product->is_in_stock() ? 'in stock' : 'out of stock' ) . '" />' . "\n";

        if ( $product->get_sku() ) {
            echo '<meta property="product:retailer_item_id" content="' . esc_attr( $product->get_sku() ) . '" />' . "\n";
        }

        $brand = self::get_brand_name( $product_id );
        if ( $brand ) {
            echo '<meta property="product:brand" content="' . esc_attr( $brand ) . '" />' . "\n";
        }

        $cats = get_the_terms( $product_id, 'product_cat' );
        if ( $cats && ! is_wp_error( $cats ) ) {
            echo '<meta property="product:category" content="' . esc_attr( $cats[0]->name ) . '" />' . "\n";
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Archives
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * ItemList of the products shown on the current shop / category archive.
     * Gives AI engines a machine-readable inventory of the page.
     */
    public static function archive_item_list( $limit = 20 ) {
        global $wp_query;

        if ( empty( $wp_query->posts ) ) {
            return null;
        }

        $items    = [];
        $position = 1;

        foreach ( array_slice( $wp_query->posts, 0, $limit ) as $post ) {
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

        return [
            '@type'           => 'ItemList',
            'numberOfItems'   => count( $items ),
            'itemListElement' => $items,
        ];
    }

    /**
     * Whether the current request is a WooCommerce page we should never index
     * (cart, checkout, account, order confirmations).
     */
    public static function is_noindex_page() {
        if ( ! self::is_active() ) {
            return false;
        }

        foreach ( [ 'is_cart', 'is_checkout', 'is_account_page' ] as $callback ) {
            if ( function_exists( $callback ) && call_user_func( $callback ) ) {
                return true;
            }
        }

        return false;
    }
}
