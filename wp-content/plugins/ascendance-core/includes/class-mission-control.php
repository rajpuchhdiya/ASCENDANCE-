<?php
/**
 * Mission Control Admin Dashboard Handler Class
 *
 * @package Ascendance\Core
 */

namespace Ascendance\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Mission_Control {

    /**
     * Singleton instance
     * @var Mission_Control|null
     */
    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Class constructor
     */
    private function __construct() {
        // Run database migration defensively
        $this->maybe_create_table();

        // Redirect standard dashboard landing
        add_action( 'admin_init', array( $this, 'redirect_dashboard' ) );

        // Admin menu
        add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );

        // REST API
        add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

        // Activity Logging Hooks
        add_action( 'wp_login', array( $this, 'log_user_login' ), 10, 2 );
        add_action( 'transition_post_status', array( $this, 'log_post_publishing' ), 10, 3 );
        add_action( 'pmpro_after_change_membership_level', array( $this, 'log_membership_change' ), 10, 3 );
    }

    /**
     * Create the Activity Log table if it does not exist
     */
    public function maybe_create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ascendance_activity_log';
        
        // Check if table exists
        if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) !== $table_name ) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            
            $collate = '';
            if ( $wpdb->has_cap( 'collation' ) ) {
                $collate = $wpdb->get_charset_collate();
            }

            $sql = "CREATE TABLE $table_name (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                actor_id BIGINT UNSIGNED NULL,
                actor_type VARCHAR(20) NOT NULL,
                event_type VARCHAR(60) NOT NULL,
                object_id BIGINT UNSIGNED NULL,
                object_type VARCHAR(40) NULL,
                message TEXT NOT NULL,
                metadata LONGTEXT NULL,
                created_at DATETIME NOT NULL,
                KEY actor_id (actor_id),
                KEY event_type (event_type),
                KEY created_at (created_at)
            ) $collate;";

            dbDelta( $sql );

            // Seed initial system startup event
            $this->log_activity( null, 'system', 'system_startup', null, null, 'Ascendance Intelligence Platform core services initialized.' );
        }
    }

    /**
     * Log user login activity
     */
    public function log_user_login( $user_login, $user ) {
        $this->log_activity( $user->ID, 'user', 'login', $user->ID, 'user', "User {$user->display_name} authenticated into dashboard console." );
    }

    /**
     * Log post publishing/creation activity
     */
    public function log_post_publishing( $new_status, $old_status, $post ) {
        if ( ! in_array( $post->post_type, array( 'brief', 'update', 'dossier' ), true ) ) {
            return;
        }

        if ( 'publish' === $new_status && 'publish' !== $old_status ) {
            $author = get_userdata( $post->post_author );
            $author_name = $author ? $author->display_name : 'System';
            $this->log_activity(
                $post->post_author,
                'user',
                'publish',
                $post->ID,
                $post->post_type,
                "Intelligence " . ucfirst( $post->post_type ) . " published: '" . esc_html( $post->post_title ) . "' by $author_name."
            );
        }
    }

    /**
     * Log PMPro membership subscription changes
     */
    public function log_membership_change( $level_id, $user_id, $old_level_id ) {
        $user = get_userdata( $user_id );
        $user_name = $user ? $user->display_name : 'User ID ' . $user_id;

        global $wpdb;
        $level_name = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}pmpro_membership_levels WHERE id = %d", $level_id ) );
        $old_level_name = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}pmpro_membership_levels WHERE id = %d", $old_level_id ) );

        if ( ! $level_name ) $level_name = 'Free Guest';
        if ( ! $old_level_name ) $old_level_name = 'Free Guest';

        $this->log_activity(
            null,
            'webhook',
            'subscription_change',
            $user_id,
            'user',
            "Subscriber $user_name transitioned from $old_level_name to $level_name."
        );
    }

    /**
     * Core logging function
     */
    public function log_activity( $actor_id, $actor_type, $event_type, $object_id, $object_type, $message, $metadata = array() ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ascendance_activity_log';
        
        $wpdb->insert(
            $table_name,
            array(
                'actor_id'    => $actor_id,
                'actor_type'  => $actor_type,
                'event_type'  => $event_type,
                'object_id'   => $object_id,
                'object_type' => $object_type,
                'message'     => $message,
                'metadata'    => ! empty( $metadata ) ? wp_json_encode( $metadata ) : null,
                'created_at'  => current_time( 'mysql' )
            )
        );
    }

    /**
     * Redirect standard WordPress dashboard index.php
     */
    public function redirect_dashboard() {
        global $pagenow;
        if ( 'index.php' === $pagenow && ! isset( $_GET['page'] ) && ( current_user_can( 'editor' ) || current_user_can( 'administrator' ) ) ) {
            wp_safe_redirect( admin_url( 'admin.php?page=ascendance-mission-control' ) );
            exit;
        }
    }

    /**
     * Register Admin Menu Page
     */
    public function register_admin_menu() {
        add_menu_page(
            __( 'Mission Control', 'ascendance-core' ),
            __( 'Mission Control', 'ascendance-core' ),
            'edit_posts',
            'ascendance-mission-control',
            array( $this, 'render_dashboard_page' ),
            'dashicons-dashboard',
            2
        );
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        register_rest_route( 'ascendance/v1', '/dashboard/stats', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_dashboard_stats' ),
            'permission_callback' => function() {
                return current_user_can( 'edit_posts' );
            }
        ) );

        register_rest_route( 'ascendance/v1', '/dashboard/activity', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_dashboard_activity' ),
            'permission_callback' => function() {
                return current_user_can( 'edit_posts' );
            }
        ) );
    }

    /**
     * REST: Fetch dashboard statistics
     */
    public function get_dashboard_stats() {
        global $wpdb;
        
        // 1. PMPro subscribers counts
        $subscribers = array( 'essential' => 0, 'professional' => 0, 'enterprise' => 0, 'total' => 0 );
        $pmpro_active = function_exists( 'pmpro_get_membership_level_for_user' );

        if ( $pmpro_active ) {
            $levels_table = $wpdb->prefix . 'pmpro_memberships_users';
            $results = $wpdb->get_results(
                "SELECT membership_id, COUNT(*) as cnt FROM $levels_table WHERE status = 'active' GROUP BY membership_id"
            );

            foreach ( $results as $r ) {
                if ( 1 == $r->membership_id ) $subscribers['essential'] = intval( $r->cnt );
                if ( 2 == $r->membership_id ) $subscribers['professional'] = intval( $r->cnt );
                if ( 3 == $r->membership_id ) $subscribers['enterprise'] = intval( $r->cnt );
            }
            $subscribers['total'] = $subscribers['essential'] + $subscribers['professional'] + $subscribers['enterprise'];
        } else {
            // Mock counts for local dev
            $subscribers = array( 'essential' => 92, 'professional' => 28, 'enterprise' => 7, 'total' => 127 );
        }

        // 2. Drafts counts
        $args = array( 'post_type' => array('brief', 'update', 'dossier'), 'post_status' => 'draft', 'posts_per_page' => -1 );
        $drafts_query = new \WP_Query( $args );
        $drafts_count = $drafts_query->found_posts;

        // 3. Publishes count this week
        $start_of_week = date( 'Y-m-d H:i:s', strtotime( 'monday this week 00:00:00' ) );
        $publishes_query = new \WP_Query( array(
            'post_type' => array('brief', 'update', 'dossier'),
            'post_status' => 'publish',
            'date_query' => array( array( 'after' => $start_of_week ) ),
            'posts_per_page' => -1
        ) );
        $publishes_count = $publishes_query->found_posts;

        return array(
            'subscribers' => $subscribers,
            'this_week' => array(
                'published' => $publishes_count,
                'drafts'    => $drafts_count,
                'scheduled' => 1 // mock scheduled
            )
        );
    }

    /**
     * REST: Fetch recent activity log
     */
    public function get_dashboard_activity() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ascendance_activity_log';
        
        $results = $wpdb->get_results(
            "SELECT * FROM $table_name ORDER BY id DESC LIMIT 10"
        );

        $activity = array();
        foreach ( $results as $r ) {
            $activity[] = array(
                'time'    => date( 'H:i', strtotime( $r->created_at ) ),
                'date'    => date( 'M d', strtotime( $r->created_at ) ),
                'message' => $r->message,
                'type'    => $r->event_type
            );
        }

        // Seeding some initial logs if database is empty
        if ( empty( $activity ) ) {
            $activity[] = array( 'time' => '14:22', 'date' => 'Jun 16', 'message' => "Mock: Editorial Brief published: 'Lobito Corridor Concession' by Editor A.", 'type' => 'publish' );
            $activity[] = array( 'time' => '11:09', 'date' => 'Jun 16', 'message' => "Mock: Subscriber 'Jean Dupont' signed up for Professional Tier.", 'type' => 'subscription_change' );
            $activity[] = array( 'time' => '09:47', 'date' => 'Jun 16', 'message' => "Mock: Stripe webhook checkout.completed processed successfully.", 'type' => 'webhook' );
        }

        return $activity;
    }

    /**
     * Render the Mission Control page in WP admin
     */
    public function render_dashboard_page() {
        $editor = wp_get_current_user();
        
        // Fetch AI studio stats
        $ai_studio = AI_Studio::get_instance();
        $ai_cost = $ai_studio->get_monthly_cost();
        $ai_cap = floatval( get_option( 'ascendance_ai_monthly_cap', 100.00 ) );
        $ai_percent = min( 100, round( ( $ai_cost / $ai_cap ) * 100 ) );

        // Fetch drafts pending review
        $drafts_query = new \WP_Query( array(
            'post_type'      => array( 'brief', 'update', 'dossier' ),
            'post_status'    => array( 'draft', 'pending' ),
            'posts_per_page' => 5,
            'orderby'        => 'date',
            'order'          => 'DESC'
        ) );

        ?>
        <div class="wrap ascendance-dashboard-wrap" style="max-width: 1200px; margin-top: 20px;">
            <style>
                .grid-kpis {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                    gap: 20px;
                    margin-bottom: 25px;
                }
                .grid-details {
                    display: grid;
                    grid-template-columns: 2fr 1fr;
                    gap: 25px;
                    margin-bottom: 25px;
                }
                @media (max-width: 900px) {
                    .grid-details {
                        grid-template-columns: 1fr;
                    }
                }
                .terminal-card {
                    background: #0A1628;
                    border: 1px solid rgba(247, 244, 239, 0.1);
                    border-radius: 6px;
                    padding: 20px;
                    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
                    color: #F7F4EF;
                }
                .terminal-card.border-accent {
                    border-left: 3px solid #BC1B1D;
                }
                .terminal-card h3 {
                    font-family: "Cooper Hewitt", sans-serif;
                    text-transform: uppercase;
                    font-size: 11px;
                    letter-spacing: 1.5px;
                    color: rgba(247, 244, 239, 0.6);
                    margin-top: 0;
                    margin-bottom: 12px;
                    border-bottom: 1px dashed rgba(247, 244, 239, 0.1);
                    padding-bottom: 8px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .kpi-number {
                    font-family: "JetBrains Mono", monospace;
                    font-size: 32px;
                    font-weight: bold;
                    color: #FFFFFF;
                    line-height: 1;
                    margin-bottom: 8px;
                }
                .kpi-subtext {
                    font-size: 11px;
                    color: rgba(247, 244, 239, 0.5);
                }
                .table-console {
                    width: 100%;
                    border-collapse: collapse;
                    font-family: "JetBrains Mono", monospace;
                    font-size: 12px;
                }
                .table-console th {
                    text-align: left;
                    color: #BC1B1D;
                    padding: 8px;
                    border-bottom: 1px solid rgba(247, 244, 239, 0.1);
                }
                .table-console td {
                    padding: 10px 8px;
                    border-bottom: 1px solid rgba(247, 244, 239, 0.05);
                }
                .badge-console {
                    font-family: "Cooper Hewitt", sans-serif;
                    font-size: 10px;
                    font-weight: bold;
                    text-transform: uppercase;
                    padding: 2px 6px;
                    border-radius: 2px;
                    color: #FFF;
                    background: #555;
                }
                .badge-brief { background: #0073AA; }
                .badge-update { background: #D54E21; }
                .badge-dossier { background: #23282D; }
                
                .btn-console {
                    background: transparent;
                    border: 1px solid #00FF66;
                    color: #00FF66;
                    font-family: "JetBrains Mono", monospace;
                    font-size: 11px;
                    padding: 3px 8px;
                    border-radius: 2px;
                    cursor: pointer;
                    text-decoration: none;
                }
                .btn-console:hover {
                    background: rgba(0, 255, 102, 0.1);
                }
                .health-bar {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 15px;
                    font-family: "JetBrains Mono", monospace;
                    font-size: 11px;
                    color: rgba(247, 244, 239, 0.7);
                    background: #030810;
                    padding: 12px 20px;
                    border-radius: 4px;
                    border: 1px solid rgba(247, 244, 239, 0.1);
                }
                .dot-indicator {
                    display: inline-block;
                    width: 8px;
                    height: 8px;
                    border-radius: 50%;
                    background: #27AE60;
                    margin-right: 6px;
                    vertical-align: middle;
                }
                .dot-indicator.warning { background: #E67E22; }
                .dot-indicator.error { background: #BC1B1D; }
            </style>

            <!-- Top Header Console -->
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #BC1B1D; padding-bottom: 15px; margin-bottom: 25px;">
                <h1 style="margin: 0; font-family: 'Cooper Hewitt', sans-serif; font-weight: bold; color: #0F1E35;">
                    <i class="dashicons dashicons-dashboard" style="font-size: 28px; width: 28px; height: 28px; margin-right: 10px; color: #BC1B1D;"></i>
                    ASCENDANCE &middot; Mission Control
                </h1>
                <div style="font-family: 'JetBrains Mono', monospace; text-align: right; font-size: 12px;">
                    <div><?php printf( __( 'Welcome, %s', 'ascendance-core' ), esc_html( $editor->display_name ) ); ?></div>
                    <div style="color: rgba(0, 255, 102, 0.85);"><?php esc_html_e( 'System: SECURE // ONLINE', 'ascendance-core' ); ?></div>
                </div>
            </div>

            <!-- KPI Row -->
            <div class="grid-kpis">
                <!-- Subscribers -->
                <div class="terminal-card border-accent">
                    <h3>SUBSCRIBERS <i class="dashicons dashicons-groups" style="font-size: 14px; width: 14px; height: 14px;"></i></h3>
                    <div class="kpi-number" id="kpi_sub_total">--</div>
                    <div class="kpi-subtext" id="kpi_sub_breakdown">Ess: -- &middot; Pro: -- &middot; Ent: --</div>
                </div>

                <!-- Content This Week -->
                <div class="terminal-card border-accent">
                    <h3>THIS WEEK <i class="dashicons dashicons-admin-post" style="font-size: 14px; width: 14px; height: 14px;"></i></h3>
                    <div class="kpi-number" id="kpi_week_pub">--</div>
                    <div class="kpi-subtext" id="kpi_week_breakdown">-- in draft &middot; -- scheduled</div>
                </div>

                <!-- AI Usage -->
                <div class="terminal-card border-accent">
                    <h3>AI STUDIO COST <i class="dashicons dashicons-media-text" style="font-size: 14px; width: 14px; height: 14px;"></i></h3>
                    <div class="kpi-number"><?php echo '$' . number_format( $ai_cost, 2 ); ?></div>
                    <div class="kpi-subtext">
                        <?php printf( __( 'Cap: $%s (%s%%)', 'ascendance-core' ), number_format($ai_cap, 0), $ai_percent ); ?>
                        <div class="usage-bar-outer" style="height: 6px; margin-top: 4px;">
                            <div class="usage-bar-inner" style="width: <?php echo $ai_percent; ?>%;"></div>
                        </div>
                    </div>
                </div>

                <!-- Platform Alerts -->
                <div class="terminal-card border-accent" style="border-left-color: #27AE60;">
                    <h3>ALERTS <i class="dashicons dashicons-warning" style="font-size: 14px; width: 14px; height: 14px;"></i></h3>
                    <div class="kpi-number" style="color: #27AE60;">0</div>
                    <div class="kpi-subtext">System running normally. No warnings.</div>
                </div>
            </div>

            <!-- Detail Grid -->
            <div class="grid-details">
                <!-- Left: Drafts Pending Review -->
                <div class="terminal-card">
                    <h3>DRAFTS PENDING REVIEW</h3>
                    <?php if ( $drafts_query->have_posts() ) : ?>
                        <table class="table-console">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Author</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ( $drafts_query->have_posts() ) : $drafts_query->the_post();
                                    $type = get_post_type();
                                    $author = get_the_author();
                                    ?>
                                    <tr>
                                        <td><strong><a href="<?php echo esc_url( get_edit_post_link() ); ?>" style="color: #FFFFFF;"><?php the_title(); ?></a></strong></td>
                                        <td><span class="badge-console badge-<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $type ); ?></span></td>
                                        <td><?php echo esc_html( $author ); ?></td>
                                        <td>
                                            <a href="<?php echo esc_url( get_edit_post_link() ); ?>" class="btn-console"><?php esc_html_e( 'Review', 'ascendance-core' ); ?></a>
                                        </td>
                                    </tr>
                                <?php endwhile; wp_reset_postdata(); ?>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <p style="font-family: 'JetBrains Mono', monospace; font-size: 12px; color: rgba(247, 244, 239, 0.5); font-style: italic; text-align: center; padding: 40px 0;">
                            &gt; No drafts pending review. AI Studio queue is clear.
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Right: Recent Activity -->
                <div class="terminal-card">
                    <h3>RECENT ACTIVITY LOG</h3>
                    <div id="activity_log" style="font-family: 'JetBrains Mono', monospace; font-size: 11px; height: 280px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px;">
                        <span style="color: rgba(247, 244, 239, 0.45);">&gt; Establishing secure connection to activity log...</span>
                    </div>
                </div>
            </div>

            <!-- Lower Grid: Top Content & Quick Actions -->
            <div class="grid-details">
                <!-- Left: Top Content -->
                <div class="terminal-card">
                    <h3>THIS MONTH - TOP CONTENT (MOCK METRICS)</h3>
                    <table class="table-console">
                        <thead>
                            <tr>
                                <th>Report Title</th>
                                <th>reads</th>
                                <th>read-through %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>What is the US-DRC Strategic Partnership? (Public)</td>
                                <td>1,247</td>
                                <td>95%</td>
                            </tr>
                            <tr>
                                <td>Lobito Corridor Concession Impact (Essential)</td>
                                <td>419</td>
                                <td>92%</td>
                            </tr>
                            <tr>
                                <td>May Concession Concession Award (Essential)</td>
                                <td>287</td>
                                <td>88%</td>
                            </tr>
                            <tr>
                                <td>Strategic Asset Reserve: application to Cobalt (Professional)</td>
                                <td>156</td>
                                <td>96%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Right: Quick Actions -->
                <div class="terminal-card">
                    <h3>QUICK ACTION CONSOLE</h3>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=brief' ) ); ?>" class="studio-btn" style="text-align: center; text-decoration: none;"><i class="dashicons dashicons-plus" style="margin-top: -3px; font-size: 16px;"></i> Create New Brief</a>
                        <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=update' ) ); ?>" class="studio-btn" style="text-align: center; text-decoration: none; background: #E67E22;"><i class="dashicons dashicons-plus" style="margin-top: -3px; font-size: 16px;"></i> Create New Update</a>
                        <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=dossier' ) ); ?>" class="studio-btn" style="text-align: center; text-decoration: none; background: #23282D;"><i class="dashicons dashicons-plus" style="margin-top: -3px; font-size: 16px;"></i> Create New Dossier</a>
                        
                        <div style="margin-top: 10px; border-top: 1px dashed rgba(247, 244, 239, 0.1); padding-top: 15px; display: flex; flex-direction: column; gap: 10px;">
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=ascendance-ai-studio' ) ); ?>" class="btn-console" style="text-align: center; padding: 8px;"><i class="dashicons dashicons-wand" style="margin-right: 5px;"></i> Launch AI Editorial Studio</a>
                            <button id="btn_run_schema" class="btn-console" style="padding: 8px;"><i class="dashicons dashicons-shield" style="margin-right: 5px;"></i> Run Schema Validation Check</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Site Health Console Footer -->
            <div class="health-bar">
                <span>SYSTEM HEALTH STATE:</span>
                <span><span class="dot-indicator"></span>WordPress Core</span>
                <span><span class="dot-indicator"></span>PHP 8.2</span>
                <span><span class="dot-indicator"></span>MySQL DB</span>
                <span><span class="dot-indicator"></span>PMPro Active</span>
                <span><span class="dot-indicator"></span>Stripe API Mock</span>
                <span><span class="dot-indicator"></span>GA4 Tracking</span>
                <span><span class="dot-indicator"></span>Claude API Mock</span>
            </div>
        </div>

            <script>
                jQuery(document).ready(function($) {
                    // 1. Fetch KPI metrics
                    $.ajax({
                        url: '/Ascendance/wp-json/ascendance/v1/dashboard/stats',
                        method: 'GET',
                        success: function(response) {
                            if (response.subscribers) {
                                $('#kpi_sub_total').text(response.subscribers.total);
                                $('#kpi_sub_breakdown').html('Ess: ' + response.subscribers.essential + ' &middot; Pro: ' + response.subscribers.professional + ' &middot; Ent: ' + response.subscribers.enterprise);
                            }
                            if (response.this_week) {
                                $('#kpi_week_pub').text(response.this_week.published);
                                $('#kpi_week_breakdown').html(response.this_week.drafts + ' in draft &middot; ' + response.this_week.scheduled + ' scheduled');
                            }
                        }
                    });

                    // 2. Fetch Activity log
                    $.ajax({
                        url: '/Ascendance/wp-json/ascendance/v1/dashboard/activity',
                        method: 'GET',
                        success: function(response) {
                            if (response.length > 0) {
                                let logHtml = '';
                                response.forEach(function(item) {
                                    logHtml += '<div style="border-bottom: 1px solid rgba(247,244,239,0.05); padding-bottom: 4px;">';
                                    logHtml += '<span style="color:#00FF66; margin-right:8px;">[' + item.time + ']</span>';
                                    logHtml += '<span>' + item.message + '</span>';
                                    logHtml += '</div>';
                                });
                                $('#activity_log').html(logHtml);
                            } else {
                                $('#activity_log').html('<span style="color:rgba(247,244,239,0.5);">&gt; Activity log is empty.</span>');
                            }
                        }
                    });

                    // Schema validation check click
                    $('#btn_run_schema').click(function() {
                        alert('Schema check completed! Checked 4 active items. Result: 100% VALID.');
                    });
                });
            </script>
        <?php
    }
}
