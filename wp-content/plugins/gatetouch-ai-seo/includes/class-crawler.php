<?php
defined( 'ABSPATH' ) || exit;

/**
 * Crawls site URLs to collect status, redirect, and broken-link data.
 */
class GateTouch_Crawler {

    private $visited = [];
    private $queue   = [];
    private $results = [
        'broken_links' => [],
        'redirects'    => [],
        'success'      => 0,
        'failed'       => 0,
    ];

    /**
     * Start a crawl from a specific URL
     */
    public function run( $start_url = null, $limit = 50, $state = [] ) {
        if ( ! empty( $state ) ) {
            $this->queue   = $state['queue']   ?? [];
            $this->visited = $state['visited'] ?? [];
            $this->results = $state['results'] ?? $this->results;
        }

        if ( $start_url && ! in_array( $start_url, $this->queue ) && ! in_array( $start_url, $this->visited ) ) {
            $this->queue[] = $start_url;
        }
        
        $count = 0;

        while ( ! empty( $this->queue ) && $count < $limit ) {
            $url = array_shift( $this->queue );
            if ( in_array( $url, $this->visited ) ) continue;

            $this->visited[] = $url;
            $count++;

            $response = wp_remote_get( $url, [ 'timeout' => 10, 'redirection' => 0 ] );
            
            if ( is_wp_error( $response ) ) {
                $this->results['failed']++;
                continue;
            }

            $code = wp_remote_retrieve_response_code( $response );
            
            if ( $code === 404 ) {
                $this->results['broken_links'][] = $url;
                $this->results['failed']++;
            } elseif ( in_array( $code, [ 301, 302 ] ) ) {
                $loc = wp_remote_retrieve_header( $response, 'location' );
                $this->results['redirects'][] = [ 'from' => $url, 'to' => $loc, 'code' => $code ];
                $this->results['success']++;
            } elseif ( $code === 200 ) {
                $this->results['success']++;
                
                // Parse HTML for internal links to crawl recursively
                $html = wp_remote_retrieve_body( $response );
                if ( preg_match_all( '/<a\s+[^>]*href=["\']([^"\']+)["\']/i', $html, $matches ) ) {
                    $home_url = home_url();
                    foreach ( $matches[1] as $link ) {
                        // Normalize relative links
                        if ( strpos( $link, '/' ) === 0 && strpos( $link, '//' ) !== 0 ) {
                            $link = rtrim($home_url, '/') . $link;
                        }
                        
                        // Remove hash fragments
                        $link = explode('#', $link)[0];
                        
                        // Only add internal, unvisited, non-asset links to the queue
                        if ( strpos( $link, $home_url ) === 0 && ! in_array( $link, $this->visited ) && ! in_array( $link, $this->queue ) ) {
                            if ( ! preg_match( '/\.(jpg|jpeg|png|gif|pdf|zip|mp4)$/i', $link ) ) {
                                $this->queue[] = $link;
                            }
                        }
                    }
                }
            }
        }

        return $this->results;
    }

    /**
     * Get current crawler state
     */
    public function get_state() {
        return [
            'queue'   => $this->queue,
            'visited' => $this->visited,
            'results' => $this->results
        ];
    }

    /**
     * Get a summary of the crawl
     */
    public static function get_summary() {
        $data = get_transient( 'gatetouch_crawl_summary' );
        if ( ! $data ) {
            return [
                'status' => 'never_run',
                'last_run' => null,
                'data' => null
            ];
        }
        return $data;
    }

    /**
     * Fetch external URL for Competitor Analysis
     */
    public static function fetch_competitor_data( $url ) {
        $response = wp_remote_get( $url, [ 'timeout' => 15 ] );
        
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( $code !== 200 ) {
            return new \WP_Error( 'http_error', 'Target URL returned HTTP ' . $code );
        }

        $html = wp_remote_retrieve_body( $response );
        if ( empty( $html ) ) {
            return new \WP_Error( 'empty_body', 'Target URL returned empty content.' );
        }

        $data = [
            'title'       => '',
            'description' => '',
            'h1'          => [],
            'h2'          => [],
            'word_count'  => 0,
        ];

        // Title
        if ( preg_match( '/<title[^>]*>(.*?)<\/title>/is', $html, $matches ) ) {
            $data['title'] = trim( wp_strip_all_tags( $matches[1] ) );
        }

        // Meta Description
        if ( preg_match( '/<meta[^>]*name=["\']description["\'][^>]*content=["\'](.*?)["\']/is', $html, $matches ) ) {
            $data['description'] = trim( wp_strip_all_tags( $matches[1] ) );
        }

        // H1s
        if ( preg_match_all( '/<h1[^>]*>(.*?)<\/h1>/is', $html, $matches ) ) {
            $data['h1'] = array_map( 'wp_strip_all_tags', $matches[1] );
        }

        // H2s
        if ( preg_match_all( '/<h2[^>]*>(.*?)<\/h2>/is', $html, $matches ) ) {
            $data['h2'] = array_map( 'wp_strip_all_tags', $matches[1] );
        }

        // Word Count (Clean noise)
        $clean_html = preg_replace( [
            '@<script[^>]*?>.*?</script>@si',
            '@<style[^>]*?>.*?</style>@si',
            '@<nav[^>]*?>.*?</nav>@si',
            '@<footer[^>]*?>.*?</footer>@si',
            '@<header[^>]*?>.*?</header>@si',
        ], '', $html );
        
        $text = wp_strip_all_tags( $clean_html );
        $text = preg_replace( '/\s+/', ' ', $text );
        $data['word_count'] = str_word_count( trim($text) );

        return $data;
    }
}
