<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- Link suggestions intentionally exclude the source post from a bounded candidate set.

/**
 * GateTouch Internal Link Assistant
 * 
 * Helps optimize internal linking structure and find suggestions.
 */
class GateTouch_Link_Assistant {

    public function __construct() {
        if ( is_admin() ) {
            add_action( 'add_meta_boxes', [ $this, 'add_suggestions_meta_box' ] );
        }
    }

    /**
     * Get Orphan Pages
     * For simplicity in this version, we'll count results of a keyword-based search 
     * but a true assistant would scan content. We'll start with keyword similarity.
     */
    public static function get_orphan_pages( $limit = 10 ) {
        global $wpdb;
        // Simplified orphan detection: posts with very few incoming links mentioned in metadata
        // In a real-world scenario, we'd crawl post_content for links to these IDs.
        // For this demo/first version, we'll return posts that haven't been optimized yet.
        return get_posts([
            'post_type'      => 'post',
            'posts_per_page' => $limit,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);
    }

    /**
     * Find link suggestions for a specific post
     */
    public static function get_link_suggestions( $post_id, $limit = 5 ) {
        $post = get_post( $post_id );
        if ( ! $post ) return [];

        // Simple algorithm: search for other posts with overlapping title words
        $title_words = explode( ' ', microtime() . ' ' . $post->post_title );
        $title_words = array_filter( $title_words, function($w) { return strlen($w) > 4; } );
        
        $args = [
            'post_type'      => $post->post_type,
            'post__not_in'   => [ $post_id ],
            'posts_per_page' => $limit,
            'orderby'        => 'relevance',
        ];

        if ( ! empty( $title_words ) ) {
            $args['s'] = implode( ' ', array_slice( $title_words, 0, 3 ) );
        }

        return get_posts( $args );
    }

    /**
     * Build a compact internal-link overview for the Link Assistant dashboard.
     */
    public static function get_site_links_summary( $limit = 100 ) {
        $posts = get_posts( [
            'post_type'      => [ 'post', 'page' ],
            'post_status'    => 'publish',
            'posts_per_page' => absint( $limit ),
            'orderby'        => 'modified',
            'order'          => 'DESC',
        ] );

        $path_to_id = [];
        foreach ( $posts as $post ) {
            $path = self::normalize_url_path( get_permalink( $post->ID ) );
            if ( $path ) {
                $path_to_id[ $path ] = (int) $post->ID;
            }
        }

        $stats     = [];
        $site_host = wp_parse_url( home_url( '/' ), PHP_URL_HOST );

        foreach ( $posts as $post ) {
            $stats[ $post->ID ] = [
                'id'             => (int) $post->ID,
                'title'          => get_the_title( $post ),
                'type'           => get_post_type( $post ),
                'edit_url'       => get_edit_post_link( $post->ID, 'raw' ),
                'view_url'       => get_permalink( $post->ID ),
                'incoming_links' => 0,
                'outgoing_links' => 0,
            ];
        }

        foreach ( $posts as $post ) {
            $linked_ids = self::extract_known_internal_link_ids( $post->post_content, $path_to_id, $site_host );
            $linked_ids = array_diff( $linked_ids, [ (int) $post->ID ] );

            $stats[ $post->ID ]['outgoing_links'] = count( $linked_ids );

            foreach ( $linked_ids as $linked_id ) {
                if ( isset( $stats[ $linked_id ] ) ) {
                    $stats[ $linked_id ]['incoming_links']++;
                }
            }
        }

        $pages       = array_values( $stats );
        $total_links = 0;
        foreach ( $pages as &$page ) {
            $page['is_orphan'] = ( 0 === (int) $page['incoming_links'] );
            $total_links      += (int) $page['outgoing_links'];
        }
        unset( $page );

        $orphans = array_values( array_filter(
            $pages,
            static function ( $page ) {
                return ! empty( $page['is_orphan'] );
            }
        ) );

        $top_linked = $pages;
        usort(
            $top_linked,
            static function ( $a, $b ) {
                return (int) $b['incoming_links'] <=> (int) $a['incoming_links'];
            }
        );

        $total_pages = count( $pages );

        return [
            'total_pages'      => $total_pages,
            'total_links'      => $total_links,
            'orphan_count'     => count( $orphans ),
            'average_outgoing' => $total_pages ? round( $total_links / $total_pages, 2 ) : 0,
            'pages'            => $pages,
            'orphans'          => array_slice( $orphans, 0, 10 ),
            'top_linked'       => array_slice( $top_linked, 0, 10 ),
        ];
    }

    private static function extract_known_internal_link_ids( $content, $path_to_id, $site_host ) {
        if ( empty( $content ) || empty( $path_to_id ) ) {
            return [];
        }

        preg_match_all( '/href=[\'"]([^\'"]+)[\'"]/i', $content, $matches );

        $linked_ids = [];
        foreach ( $matches[1] ?? [] as $href ) {
            $href = html_entity_decode( $href, ENT_QUOTES, 'UTF-8' );
            $host = wp_parse_url( $href, PHP_URL_HOST );
            if ( $host && $site_host && strtolower( $host ) !== strtolower( $site_host ) ) {
                continue;
            }

            $path = self::normalize_url_path( $href );
            if ( $path && isset( $path_to_id[ $path ] ) ) {
                $linked_ids[] = $path_to_id[ $path ];
            }
        }

        return array_values( array_unique( $linked_ids ) );
    }

    private static function normalize_url_path( $url ) {
        if ( empty( $url ) ) {
            return '';
        }

        $path = wp_parse_url( $url, PHP_URL_PATH );
        if ( ! $path ) {
            return '';
        }

        return '/' . trim( rawurldecode( $path ), '/' );
    }

    /**
     * Add Link Suggestions Meta Box
     */
    public function add_suggestions_meta_box() {
        $screens = [ 'post', 'page' ];
        foreach ( $screens as $screen ) {
            add_meta_box(
                'gatetouch-link-suggestions',
                __( 'GT SEO/GEO/AEO Internal Link Suggestions', 'gatetouch-ai-seo' ),
                [ $this, 'render_suggestions_meta_box' ],
                $screen,
                'side',
                'low'
            );
        }
    }

    public function render_suggestions_meta_box( $post ) {
        $suggestions = self::get_link_suggestions( $post->ID );
        ?>
        <div class="gatetouch-link-suggestions-panel">
            <?php if ( empty( $suggestions ) ) : ?>
                <p style="font-size:12px; color:#6b7280;">No relevant internal links found yet. Try adding more content.</p>
            <?php else : ?>
                <p style="font-size:12px; font-weight:600; margin-bottom:10px;">Connect this post to:</p>
                <ul style="margin:0; padding:0; list-style:none;">
                    <?php foreach ( $suggestions as $suggest ) : ?>
                        <li style="margin-bottom:8px; padding-bottom:8px; border-bottom:1px solid #f3f4f6;">
                            <a href="<?php echo esc_url( get_edit_post_link( $suggest->ID ) ); ?>" style="display:block; font-size:13px; font-weight:600; color:var(--riq-primary); text-decoration:none;">
                                <?php echo esc_html( $suggest->post_title ); ?>
                            </a>
                            <span style="font-size:11px; color:#9ca3af;">Type: <?php echo esc_html( ucfirst($suggest->post_type) ); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <div style="margin-top:12px;">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=gatetouch-links' ) ); ?>" style="font-size:12px; color:var(--riq-primary); text-decoration:none; font-weight:700;">View Linking Dashboard →</a>
            </div>
        </div>
        <?php
    }
}
