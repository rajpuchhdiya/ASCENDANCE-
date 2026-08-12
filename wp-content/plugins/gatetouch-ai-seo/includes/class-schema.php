<?php
defined( 'ABSPATH' ) || exit;

/**
 * Builds and outputs standard JSON-LD schema for frontend pages.
 */
class GateTouch_Schema {

    /**
     * Output JSON-LD to <head>
     */
    public static function output( \WP_Post $post, $meta ) {
        // If user has custom schema, output that
        if ( ! empty( $meta['custom_schema'] ) ) {
            $decoded = json_decode( $meta['custom_schema'], true );
            if ( json_last_error() === JSON_ERROR_NONE ) {
                GateTouch_Helpers::print_json_ld( $decoded );
                return;
            }
        }

        $schema_type = $meta['schema_type'] ?? 'Article';
        
        // Zero-Config Auto-Detection
        if ( get_option( 'gatetouch_auto_schema', 'no' ) === 'yes' && ( $schema_type === 'Article' || empty( $schema_type ) ) ) {
            // Use the AI detected type if available in meta
            if ( ! empty( $meta['ai_detected_schema'] ) ) {
                $schema_type = $meta['ai_detected_schema'];
            }
        }

        $schema = self::build_schema( $post, $meta, $schema_type );

        // FAQPage supplement
        if ( ! empty( $meta['faqs'] ) && is_array( $meta['faqs'] ) ) {
            self::output_faq_schema( $meta['faqs'] );
        }

        GateTouch_Helpers::print_json_ld( $schema );

        // BreadcrumbList
        self::output_breadcrumb_schema( $post );

        // Website schema on homepage
        if ( is_front_page() ) {
            self::output_website_schema();
        }
    }

    private static function build_schema( \WP_Post $post, $meta, $type ) {
        $site_name = get_bloginfo( 'name' );
        $permalink = get_permalink( $post->ID );
        $thumbnail = get_the_post_thumbnail_url( $post->ID, 'full' );
        $og_image  = $meta['og_image'] ?? $thumbnail;
        $author    = get_the_author_meta( 'display_name', $post->post_author );

        $base = [
            '@context'        => 'https://schema.org',
            '@type'           => $type,
            'headline'        => $meta['meta_title'] ?? $post->post_title,
            'name'            => $meta['meta_title'] ?? $post->post_title,
            'description'     => $meta['meta_description'] ?? '',
            'url'             => $permalink,
            'datePublished'   => get_the_date( 'c', $post->ID ),
            'dateModified'    => get_the_modified_date( 'c', $post->ID ),
            'inLanguage'      => get_locale(),
            'author'          => [
                '@type' => 'Person',
                'name'  => $author,
            ],
            'publisher'       => [
                '@type' => 'Organization',
                'name'  => $site_name,
                'url'   => home_url( '/' ),
            ],
        ];

        if ( $og_image ) {
            $base['image'] = [
                '@type'  => 'ImageObject',
                'url'    => $og_image,
                'width'  => 1200,
                'height' => 630,
            ];
        }

        if ( ! empty( $meta['focus_keyword'] ) ) {
            $base['keywords'] = $meta['focus_keyword'];
        }

        return $base;
    }

    private static function output_faq_schema( $faqs ) {
        $items = [];
        foreach ( $faqs as $faq ) {
            if ( empty( $faq['question'] ) || empty( $faq['answer'] ) ) continue;
            $items[] = [
                '@type'          => 'Question',
                'name'           => sanitize_text_field( $faq['question'] ),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => sanitize_textarea_field( $faq['answer'] ),
                ],
            ];
        }

        if ( empty( $items ) ) return;

        $schema = [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => $items,
        ];

        GateTouch_Helpers::print_json_ld( $schema );
    }

    private static function output_breadcrumb_schema( \WP_Post $post ) {
        $schema_settings = get_option( 'gatetouch_schema_settings', [] );
        if ( isset( $schema_settings['breadcrumb_schema'] ) && $schema_settings['breadcrumb_schema'] !== '1' ) {
            return;
        }

        if ( class_exists( 'GateTouch_Breadcrumbs' ) ) {
            if ( ! GateTouch_Breadcrumbs::is_enabled() || ! GateTouch_Breadcrumbs::is_allowed_for_post( $post->ID ) ) {
                return;
            }
        }

        $items    = [];
        $position = 1;

        // Home
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => get_bloginfo( 'name' ),
            'item'     => home_url( '/' ),
        ];

        // Category (for posts)
        if ( $post->post_type === 'post' ) {
            $cats = get_the_category( $post->ID );
            if ( ! empty( $cats ) ) {
                $items[] = [
                    '@type'    => 'ListItem',
                    'position' => $position++,
                    'name'     => $cats[0]->name,
                    'item'     => get_category_link( $cats[0]->term_id ),
                ];
            }
        }

        // Current page
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $position,
            'name'     => $post->post_title,
            'item'     => get_permalink( $post->ID ),
        ];

        $schema = [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $items,
        ];

        GateTouch_Helpers::print_json_ld( $schema );
    }

    private static function output_website_schema() {
        $schema = [
            '@context'        => 'https://schema.org',
            '@type'           => 'WebSite',
            'name'            => get_bloginfo( 'name' ),
            'url'             => home_url( '/' ),
            'description'     => get_bloginfo( 'description' ),
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => [
                    '@type'       => 'EntryPoint',
                    'urlTemplate' => home_url( '/?s={search_term_string}' ),
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];

        GateTouch_Helpers::print_json_ld( $schema );
    }
}
