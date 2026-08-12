<?php
defined( 'ABSPATH' ) || exit;

/**
 * Stores and processes deferred GateTouch background tasks.
 */
class GateTouch_Queue {

    public function __construct() {
        add_action( 'gatetouch_process_queue', [ $this, 'process' ] );
    }

    /**
     * Add a task to the queue
     */
    public static function add( $action, $args = [] ) {
        $queue = get_option( 'gatetouch_task_queue', [] );
        $queue[] = [
            'action' => $action,
            'args'   => $args,
            'time'   => time(),
        ];
        update_option( 'gatetouch_task_queue', $queue );

        if ( ! wp_next_scheduled( 'gatetouch_process_queue' ) ) {
            wp_schedule_single_event( time() + 30, 'gatetouch_process_queue' );
        }
    }

    /**
     * Process the queue (called via cron)
     */
    public function process() {
        $queue = get_option( 'gatetouch_task_queue', [] );
        if ( empty( $queue ) ) {
            delete_option( 'gatetouch_bulk_progress' );
            return;
        }

        $task = array_shift( $queue );
        update_option( 'gatetouch_task_queue', $queue );

        // Track progress
        $progress = get_option( 'gatetouch_bulk_progress', [ 'total' => 0, 'current' => 0 ] );

        switch ( $task['action'] ) {
            case 'bulk_meta':
                $res = GateTouch_Fix_Engine::fix( 'missing_meta', [
                    'post_ids' => $task['args']['post_ids'] ?? [],
                    'batch_size' => 20 // Process more in background
                ] );
                $progress['current'] += $res['fixed'];
                break;
            case 'bulk_alt':
                GateTouch_Fix_Engine::fix( 'missing_alt', $task['args'] );
                $progress['current']++;
                break;
            case 'bulk_audit':
                require_once GATETOUCH_PATH . 'includes/class-analysis.php';
                $post_ids = $task['args']['post_ids'] ?? [];
                foreach ($post_ids as $id) {
                    $analysis = \GateTouch_Analysis::analyze( $id );
                    $meta = get_post_meta( $id, GATETOUCH_META_KEY, true ) ?: [];
                    $meta['score'] = $analysis['score'] ?? 0;
                    $meta['checks'] = array_merge(
                        $analysis['site_issues'] ?? [],
                        $analysis['checks'] ?? []
                    );
                    update_post_meta( $id, GATETOUCH_META_KEY, $meta );
                }

                // Invalidate transient caches in a Redis-compatible way
                update_option( 'gatetouch_audit_cache_version', time() );

                $progress['current'] += count($post_ids);
                break;
            case 'ping_search':
                $sitemap = new GateTouch_Sitemap();
                $sitemap->ping_search_engines( $task['args']['urls'] ?? [] );
                break;
            case 'crawl_site':
                require_once GATETOUCH_PATH . 'includes/class-crawler.php';
                $crawler = new \GateTouch_Crawler();
                $state   = get_transient( 'gatetouch_crawl_state' ) ?: [];
                $crawler->run( home_url(), 50, $state );

                $new_state = $crawler->get_state();

                if ( empty( $new_state['queue'] ) ) {
                    // Crawl complete! Save final summary.
                    $new_state['last_run'] = time();
                    $new_state['status']   = 'completed';
                    set_transient( 'gatetouch_crawl_summary', $new_state, YEAR_IN_SECONDS );
                    delete_transient( 'gatetouch_crawl_state' );
                } else {
                    // Save progress and re-queue
                    set_transient( 'gatetouch_crawl_state', $new_state, DAY_IN_SECONDS );
                    array_unshift( $queue, $task );
                }
                break;
        }

        update_option( 'gatetouch_bulk_progress', $progress );

        // If queue still has items, schedule next run IMMEDIATELY for high performance
        if ( ! empty( $queue ) ) {
            wp_schedule_single_event( time(), 'gatetouch_process_queue' );
        } else {
            delete_option( 'gatetouch_bulk_progress' );
        }
    }
}
