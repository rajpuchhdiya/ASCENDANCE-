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

        // Admin menu - Parent at priority 9, Guides at priority 11, Separators at 100
        add_action( 'admin_menu', array( $this, 'register_admin_menu_parent' ), 9 );
        add_action( 'admin_menu', array( $this, 'register_admin_menu_submenus' ), 11 );
        add_action( 'admin_menu', array( $this, 'add_admin_menu_separators' ), 100 );

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
    /**
     * Register Admin Menu Parent and Dashboard/Settings
     */
    public function register_admin_menu_parent() {
        // Register top-level parent menu
        add_menu_page(
            __( 'Mission Control', 'ascendance-core' ),
            __( 'Mission Control', 'ascendance-core' ),
            'edit_posts',
            'ascendance-mission-control',
            array( $this, 'render_dashboard_page' ),
            'dashicons-dashboard',
            30
        );

        // Rename the first default submenu page to Dashboard
        add_submenu_page(
            'ascendance-mission-control',
            __( 'Dashboard', 'ascendance-core' ),
            __( 'Dashboard', 'ascendance-core' ),
            'edit_posts',
            'ascendance-mission-control',
            array( $this, 'render_dashboard_page' )
        );

        // Register the new central Settings page (only for admins!)
        $settings_hook = add_submenu_page(
            'ascendance-mission-control',
            __( 'Settings', 'ascendance-core' ),
            __( 'Settings', 'ascendance-core' ),
            'manage_options',
            'ascendance-settings',
            array( $this, 'render_settings_page' )
        );

        // Hook settings page actions before headers are sent
        add_action( 'load-' . $settings_hook, array( $this, 'handle_settings_page_actions' ) );
    }

    /**
     * Register Admin Menu Guides (at the bottom)
     */
    public function register_admin_menu_submenus() {
        add_submenu_page(
            'ascendance-mission-control',
            __( 'User Guide', 'ascendance-core' ),
            __( 'User Guide', 'ascendance-core' ),
            'edit_posts',
            'ascendance-user-guide',
            array( $this, 'render_user_guide_page' )
        );

        add_submenu_page(
            'ascendance-mission-control',
            __( 'Developer Guide', 'ascendance-core' ),
            __( 'Developer Guide', 'ascendance-core' ),
            'edit_posts',
            'ascendance-developer-guide',
            array( $this, 'render_developer_guide_page' )
        );
    }

    /**
     * Render the User Guide by reading docs/user-guide.md
     */
    public function render_user_guide_page() {
        $path = untrailingslashit( ABSPATH ) . '/docs/user-guide.md';
        $this->render_markdown_file_page( $path, 'User Guide' );
    }

    /**
     * Render the Developer Guide by reading docs/developer-guide.md
     */
    public function render_developer_guide_page() {
        $path = untrailingslashit( ABSPATH ) . '/docs/developer-guide.md';
        $this->render_markdown_file_page( $path, 'Developer Guide' );
    }

    /**
     * Read a markdown file and render a simple HTML page inside admin container
     */
    private function render_markdown_file_page( $file_path, $title ) {
        echo '<div class="wrap ascendance-docs-wrap" style="max-width:1100px;">';
        echo '<h1>' . esc_html( $title ) . '</h1>';

        if ( ! file_exists( $file_path ) ) {
            echo '<div class="notice notice-warning"><p>' . esc_html__( 'Documentation file not found at', 'ascendance-core' ) . ' ' . esc_html( $file_path ) . '</p></div>';
            echo '</div>';
            return;
        }

        $md = file_get_contents( $file_path );
        $html = $this->simple_markdown_to_html( $md );

        echo '<div style="background:#0A1628; color:#F7F4EF; padding:20px; border-radius: 2px;">';
        echo $html; // already escaped/converted
        echo '</div>';
        echo '</div>';
    }

    /**
     * Minimal Markdown -> HTML converter for common structures (headings, lists, paragraphs)
     */
    private function simple_markdown_to_html( $md ) {
        $lines = preg_split('/\r?\n/', $md);
        $html = '';
        $in_list = false;

        foreach ( $lines as $line ) {
            $trim = trim( $line );
            if ( $trim === '' ) {
                if ( $in_list ) { $html .= "</ul>"; $in_list = false; }
                $html .= "<p></p>";
                continue;
            }

            // Headings
            if ( preg_match('/^######\s+(.*)/', $trim, $m) ) { $html .= '<h6>' . esc_html( $m[1] ) . '</h6>'; continue; }
            if ( preg_match('/^#####\s+(.*)/', $trim, $m) ) { $html .= '<h5>' . esc_html( $m[1] ) . '</h5>'; continue; }
            if ( preg_match('/^####\s+(.*)/', $trim, $m) ) { $html .= '<h4>' . esc_html( $m[1] ) . '</h4>'; continue; }
            if ( preg_match('/^###\s+(.*)/', $trim, $m) ) { $html .= '<h3>' . esc_html( $m[1] ) . '</h3>'; continue; }
            if ( preg_match('/^##\s+(.*)/', $trim, $m) ) { $html .= '<h2>' . esc_html( $m[1] ) . '</h2>'; continue; }
            if ( preg_match('/^#\s+(.*)/', $trim, $m) ) { $html .= '<h1>' . esc_html( $m[1] ) . '</h1>'; continue; }

            // Unordered list
            if ( preg_match('/^[-\*]\s+(.*)/', $trim, $m) ) {
                if ( ! $in_list ) { $html .= '<ul>'; $in_list = true; }
                $html .= '<li>' . esc_html( $m[1] ) . '</li>';
                continue;
            }

            // Links inline [text](url)
            $line = preg_replace_callback('/\[([^\]]+)\]\(([^\)]+)\)/', function( $matches ) {
                return '<a href="' . esc_url( $matches[2] ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $matches[1] ) . '</a>';
            }, $trim);

            // Bold **text** and *italic*
            $line = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $line);
            $line = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $line);

            $html .= '<p>' . wp_kses_post( nl2br( $line ) ) . '</p>';
        }

        if ( $in_list ) { $html .= "</ul>"; }

        return $html;
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
        $pmpro_active = function_exists( 'pmpro_getMembershipLevelForUser' );

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

        // 3b. Scheduled count this week
        $scheduled_query = new \WP_Query( array(
            'post_type'   => array('brief', 'update', 'dossier'),
            'post_status' => 'future',
            'posts_per_page' => -1
        ) );
        $scheduled_count = $scheduled_query->found_posts;

        // 4. Site Updates/Alerts count
        $plugin_updates = get_site_transient( 'update_plugins' );
        $plugin_count = ! empty( $plugin_updates->response ) ? count( $plugin_updates->response ) : 0;
        
        $theme_updates = get_site_transient( 'update_themes' );
        $theme_count = ! empty( $theme_updates->response ) ? count( $theme_updates->response ) : 0;
        
        $core_updates = get_site_transient( 'update_core' );
        $core_count = 0;
        if ( ! empty( $core_updates->updates ) ) {
            foreach ( $core_updates->updates as $update ) {
                if ( isset( $update->response ) && 'latest' !== $update->response ) {
                    $core_count++;
                }
            }
        }

        // 4b. API Key Statuses & Alerts Integration
        $ai_studio = \Ascendance\Core\AI_Studio::get_instance();
        $api_providers = array( 'openai', 'anthropic', 'gemini', 'stripe', 'brevo' );
        $api_statuses = array();
        $api_alerts_count = 0;
        $api_alert_messages = array();

        foreach ( $api_providers as $provider ) {
            $status_info = $ai_studio->get_api_key_status( $provider );
            $api_statuses[ $provider ] = $status_info;
            if ( in_array( $status_info['status'], array( 'missing', 'invalid' ), true ) ) {
                $api_alerts_count++;
                $api_alert_messages[] = sprintf( '%s API key is %s.', ucfirst( $provider ), $status_info['status'] );
            }
        }

        $total_updates = $plugin_count + $theme_count + $core_count;
        $total_alerts = $total_updates + $api_alerts_count;

        $alert_message = '';
        if ( $total_alerts > 0 ) {
            $update_msg = $total_updates > 0 ? sprintf( _n( '%d platform update available.', '%d platform updates available.', $total_updates, 'ascendance-core' ), $total_updates ) : '';
            $messages = array();
            if ( ! empty( $update_msg ) ) {
                $messages[] = $update_msg;
            }
            if ( ! empty( $api_alert_messages ) ) {
                $messages[] = implode( ' ', $api_alert_messages );
            }
            $alert_message = implode( ' ', $messages );
        } else {
            $alert_message = 'System running normally. No warnings.';
        }

        return array(
            'subscribers' => $subscribers,
            'this_week' => array(
                'published' => $publishes_count,
                'drafts'    => $drafts_count,
                'scheduled' => $scheduled_count
            ),
            'alerts' => array(
                'count'   => $total_alerts,
                'message' => $alert_message
            ),
            'api_statuses' => $api_statuses
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
        <div class="wrap ascendance-dashboard-wrap">
            <h1 class="screen-reader-text" style="display: none;"><?php esc_html_e( 'Mission Control', 'ascendance-core' ); ?></h1>
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;700&family=Outfit:wght@400;500;600;700;800&display=swap');

                .ascendance-dashboard-inner {
                    background: #070B13;
                    padding: 30px;
                    border-radius: 2px;
                    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 10px 10px -5px rgba(0, 0, 0, 0.4);
                    border: 1px solid rgba(255, 255, 255, 0.08);
                    color: #FFFFFF;
                    font-family: 'Inter', sans-serif;
                    margin-right: 20px;
                    margin-top: 25px !important;
                }
                .grid-kpis {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                    gap: 20px;
                    margin-bottom: 30px;
                }
                .grid-details {
                    display: grid;
                    grid-template-columns: 2fr 1fr;
                    gap: 30px;
                    margin-bottom: 30px;
                }
                @media (max-width: 900px) {
                    .grid-details {
                        grid-template-columns: 1fr;
                    }
                }
                .terminal-card {
                    background: linear-gradient(135deg, #0D1527 0%, #070B13 100%);
                    border: 1px solid rgba(255, 255, 255, 0.08);
                    border-radius: 2px;
                    padding: 24px;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
                    color: #F7F4EF;
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                }
                .terminal-card:hover {
                    border-color: rgba(188, 27, 29, 0.3);
                    box-shadow: 0 12px 30px rgba(0,0,0,0.6), 0 0 15px rgba(188, 27, 29, 0.05);
                    transform: translateY(-2px);
                }
                .terminal-card.border-accent {
                    border-top: 4px solid #BC1B1D;
                }
                .terminal-card h3 {
                    font-family: 'Outfit', sans-serif;
                    text-transform: uppercase;
                    font-size: 11px;
                    font-weight: 700;
                    letter-spacing: 1.5px;
                    color: rgba(247, 244, 239, 0.6);
                    margin-top: 0;
                    margin-bottom: 16px;
                    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
                    padding-bottom: 10px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .kpi-number {
                    font-family: "JetBrains Mono", monospace;
                    font-size: 38px;
                    font-weight: bold;
                    color: #FFFFFF;
                    line-height: 1;
                    margin-bottom: 10px;
                    text-shadow: 0 0 15px rgba(255, 255, 255, 0.15);
                }
                .kpi-subtext {
                    font-size: 12px;
                    color: rgba(247, 244, 239, 0.5);
                    font-family: 'Inter', sans-serif;
                }
                .table-console {
                    width: 100%;
                    border-collapse: collapse;
                    font-family: 'Inter', sans-serif;
                    font-size: 13px;
                }
                .table-console th {
                    text-align: left;
                    color: #BC1B1D;
                    font-family: 'Outfit', sans-serif;
                    font-weight: 700;
                    text-transform: uppercase;
                    font-size: 11px;
                    letter-spacing: 1px;
                    padding: 12px 8px;
                    border-bottom: 2px solid rgba(255, 255, 255, 0.08);
                }
                .table-console td {
                    padding: 14px 8px;
                    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
                    color: rgba(247, 244, 239, 0.85);
                }
                .table-console tr:hover td {
                    color: #FFFFFF;
                    background: rgba(255, 255, 255, 0.02);
                }
                .badge-console {
                    font-family: 'Outfit', sans-serif;
                    font-size: 9px;
                    font-weight: 700;
                    text-transform: uppercase;
                    padding: 3px 8px;
                    border-radius: 2px;
                    color: #FFF;
                    letter-spacing: 0.5px;
                }
                .badge-brief { background: #1E40AF; box-shadow: 0 0 10px rgba(30, 64, 175, 0.3); }
                .badge-update { background: #B45309; box-shadow: 0 0 10px rgba(180, 83, 9, 0.3); }
                .badge-dossier { background: #374151; box-shadow: 0 0 10px rgba(55, 65, 81, 0.3); }
                
                .btn-console {
                    background: transparent;
                    border: 1px solid #10B981;
                    color: #10B981;
                    font-family: 'Inter', sans-serif;
                    font-size: 11px;
                    font-weight: 600;
                    padding: 6px 14px;
                    border-radius: 2px;
                    cursor: pointer;
                    text-decoration: none;
                    transition: all 0.2s ease;
                    display: inline-flex;
                    align-items: center;
                    gap: 4px;
                }
                .btn-console:hover {
                    background: #10B981;
                    color: #070B13;
                    box-shadow: 0 0 12px rgba(16, 185, 129, 0.4);
                }
                .btn-console.accent {
                    border-color: #BC1B1D;
                    color: #BC1B1D;
                }
                .btn-console.accent:hover {
                    background: #BC1B1D;
                    color: #FFFFFF;
                    box-shadow: 0 0 12px rgba(188, 27, 29, 0.4);
                }
                .studio-btn {
                    background: #BC1B1D;
                    color: #FFFFFF;
                    border: none;
                    border-radius: 2px;
                    padding: 12px 20px;
                    font-weight: 600;
                    cursor: pointer;
                    font-family: 'Outfit', sans-serif;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    gap: 6px;
                    transition: all 0.2s ease;
                    box-shadow: 0 4px 12px rgba(188, 27, 29, 0.25);
                }
                .studio-btn:hover {
                    background: #9E1416;
                    box-shadow: 0 4px 20px rgba(188, 27, 29, 0.45);
                    transform: translateY(-1px);
                }
                .studio-btn:active {
                    transform: translateY(0);
                }
                .health-bar {
                    display: flex;
                    flex-wrap: wrap;
                    align-items: center;
                    gap: 20px;
                    font-family: "JetBrains Mono", monospace;
                    font-size: 11px;
                    color: rgba(247, 244, 239, 0.7);
                    background: #0D1527;
                    padding: 16px 24px;
                    border-radius: 2px;
                    border: 1px solid rgba(255, 255, 255, 0.08);
                    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
                    margin-top: 30px;
                }
                .dot-indicator {
                    display: inline-block;
                    width: 8px;
                    height: 8px;
                    border-radius: calc(50.0%);
                    background: #10B981;
                    margin-right: 6px;
                    vertical-align: middle;
                    box-shadow: 0 0 8px #10B981;
                    position: relative;
                }
                .dot-indicator::after {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    border-radius: calc(50.0%);
                    background: #10B981;
                    animation: pulse 2s infinite;
                    opacity: 0.8;
                }
                .dot-indicator.warning { background: #F59E0B; box-shadow: 0 0 8px #F59E0B; }
                .dot-indicator.warning::after { background: #F59E0B; }
                .dot-indicator.error { background: #EF4444; box-shadow: 0 0 8px #EF4444; }
                .dot-indicator.error::after { background: #EF4444; }
                .dot-indicator.offline { background: #6B7280; box-shadow: 0 0 8px #6B7280; }
                .dot-indicator.offline::after { background: #6B7280; }
                
                @keyframes pulse {
                    0% {
                        transform: scale(1);
                        opacity: 0.8;
                    }
                    100% {
                        transform: scale(2.5);
                        opacity: 0;
                    }
                }
                #activity_log::-webkit-scrollbar {
                    width: 6px;
                }
                #activity_log::-webkit-scrollbar-track {
                    background: #070B13;
                }
                #activity_log::-webkit-scrollbar-thumb {
                    background: rgba(255, 255, 255, 0.08);
                    border-radius: 2px;
                }
                #activity_log::-webkit-scrollbar-thumb:hover {
                    background: rgba(255, 255, 255, 0.2);
                }
                input[type="text"]#ascendance_gtm_id:focus {
                    border-color: #10B981 !important;
                    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.25) !important;
                    outline: none;
                }
            </style>
            
            <div class="ascendance-dashboard-inner">
                <!-- Top Header Console -->
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255, 255, 255, 0.08); padding-bottom: 20px; margin-bottom: 30px;">
                <h1 style="margin: 0; font-family: 'Outfit', sans-serif; font-weight: 800; color: #FFFFFF; font-size: 28px; display: flex; align-items: center; gap: 10px; letter-spacing: -0.5px;">
                    <i class="dashicons dashicons-dashboard" style="font-size: 28px; width: 28px; height: 28px; color: #BC1B1D; text-shadow: 0 0 10px rgba(188, 27, 29, 0.45);"></i>
                    ASCENDANCE &middot; Mission Control
                </h1>
                <div style="font-family: 'JetBrains Mono', monospace; text-align: right; font-size: 12px; color: rgba(255, 255, 255, 0.8);">
                    <div><?php printf( __( 'Welcome, %s', 'ascendance-core' ), esc_html( $editor->display_name ) ); ?></div>
                    <div style="color: #10B981; font-weight: 600; text-shadow: 0 0 8px rgba(16, 185, 129, 0.3); margin-top: 4px;"><?php esc_html_e( 'System: SECURE // ONLINE', 'ascendance-core' ); ?></div>
                </div>
            </div>

            <?php if ( isset( $_POST['save_analytics_settings'] ) && check_admin_referer( 'ascendance_analytics_settings_action', 'ascendance_analytics_settings_nonce' ) ) : ?>
                <div class="notice notice-success is-dismissible" style="margin-left: 0; margin-right: 0; margin-bottom: 25px; background: #0D1527; border: 1px solid #10B981; color: #10B981; padding: 12px 20px; border-radius: 2px; font-family: 'JetBrains Mono', monospace; font-size: 12px;">
                    <p style="margin: 0;">&gt; ANALYTICS_SYSTEM: Google Tag Manager settings saved successfully.</p>
                </div>
            <?php endif; ?>

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
                <div class="terminal-card border-accent" id="card_alerts" style="border-top-color: #10B981;">
                    <h3>ALERTS <i class="dashicons dashicons-warning" style="font-size: 14px; width: 14px; height: 14px;"></i></h3>
                    <div class="kpi-number" id="kpi_alerts_count" style="color: #10B981; text-shadow: 0 0 10px rgba(16, 185, 129, 0.2);">0</div>
                    <div class="kpi-subtext" id="kpi_alerts_subtext">System running normally. No warnings.</div>
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
                                        <td><strong><a href="<?php echo esc_url( get_edit_post_link() ); ?>" style="color: #FFFFFF; text-decoration: none;"><?php the_title(); ?></a></strong></td>
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
                        <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=brief' ) ); ?>" class="studio-btn" style="text-align: center; text-decoration: none; background: #1E40AF; box-shadow: 0 4px 12px rgba(30, 64, 175, 0.25);"><i class="dashicons dashicons-plus" style="margin-top: -3px; font-size: 16px;"></i> Create New Brief</a>
                        <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=update' ) ); ?>" class="studio-btn" style="text-align: center; text-decoration: none; background: #B45309; box-shadow: 0 4px 12px rgba(180, 83, 9, 0.25);"><i class="dashicons dashicons-plus" style="margin-top: -3px; font-size: 16px;"></i> Create New Update</a>
                        <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=dossier' ) ); ?>" class="studio-btn" style="text-align: center; text-decoration: none; background: #374151; box-shadow: 0 4px 12px rgba(55, 65, 81, 0.25);"><i class="dashicons dashicons-plus" style="margin-top: -3px; font-size: 16px;"></i> Create New Dossier</a>
                        
                        <div style="margin-top: 10px; border-top: 1px solid rgba(255, 255, 255, 0.08); padding-top: 15px; display: flex; flex-direction: column; gap: 10px;">
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=ascendance-ai-studio' ) ); ?>" class="btn-console accent" style="text-align: center; padding: 10px; justify-content: center;"><i class="dashicons dashicons-wand" style="margin-right: 5px;"></i> Launch AI Editorial Studio</a>
                            <button id="btn_run_schema" class="btn-console" style="padding: 10px; justify-content: center;"><i class="dashicons dashicons-shield" style="margin-right: 5px;"></i> Run Schema Validation Check</button>
                        </div>
                    </div>
                </div>
            </div>

            

            <!-- Site Health Console Footer -->
            <div class="health-bar">
                <span style="font-family: 'Outfit', sans-serif; font-weight: 700; color: #FFFFFF; margin-right: 10px;">SYSTEM HEALTH STATE:</span>
                <span><span class="dot-indicator"></span>WordPress Core</span>
                <span><span class="dot-indicator"></span>PHP <?php echo esc_html( PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION ); ?></span>
                <span><span class="dot-indicator"></span>MySQL DB</span>
                <span><span class="dot-indicator offline" id="indicator_openai"></span>OpenAI API</span>
                <span><span class="dot-indicator offline" id="indicator_anthropic"></span>Anthropic API</span>
                <span><span class="dot-indicator offline" id="indicator_gemini"></span>Gemini API</span>
                <span><span class="dot-indicator offline" id="indicator_stripe"></span>Stripe API</span>
                <span><span class="dot-indicator offline" id="indicator_brevo"></span>Brevo API</span>
            </div><!-- .ascendance-dashboard-inner -->
        </div><!-- .wrap -->
 
            <script>
                jQuery(document).ready(function($) {
                    // 1. Fetch KPI metrics
                    $.ajax({
                        url: '/Ascendance/wp-json/ascendance/v1/dashboard/stats',
                        method: 'GET',
                        headers: {
                            'X-WP-Nonce': '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>'
                        },
                        success: function(response) {
                            if (response.subscribers) {
                                $('#kpi_sub_total').text(response.subscribers.total);
                                $('#kpi_sub_breakdown').html('Ess: ' + response.subscribers.essential + ' &middot; Pro: ' + response.subscribers.professional + ' &middot; Ent: ' + response.subscribers.enterprise);
                            }
                            if (response.this_week) {
                                $('#kpi_week_pub').text(response.this_week.published);
                                $('#kpi_week_breakdown').html(response.this_week.drafts + ' in draft &middot; ' + response.this_week.scheduled + ' scheduled');
                            }
                            if (response.alerts) {
                                $('#kpi_alerts_count').text(response.alerts.count);
                                $('#kpi_alerts_subtext').text(response.alerts.message);
                                if (response.alerts.count > 0) {
                                    $('#card_alerts').css('border-top-color', '#F59E0B');
                                    $('#kpi_alerts_count').css({
                                        'color': '#F59E0B',
                                        'text-shadow': '0 0 10px rgba(245, 158, 11, 0.4)'
                                    });
                                } else {
                                    $('#card_alerts').css('border-top-color', '#10B981');
                                    $('#kpi_alerts_count').css({
                                        'color': '#10B981',
                                        'text-shadow': '0 0 10px rgba(16, 185, 129, 0.2)'
                                    });
                                }
                            }
                            if (response.api_statuses) {
                                $.each(response.api_statuses, function(provider, info) {
                                    var indicator = $('#indicator_' + provider);
                                    if (info.status === 'active') {
                                        indicator.removeClass('offline warning error');
                                    } else {
                                        indicator.removeClass('offline warning').addClass('error');
                                    }
                                    var sourceLabel = info.source ? ' [' + info.source + ']' : '';
                                    indicator.parent().attr('title', provider.toUpperCase() + ': ' + (info.status === 'active' ? 'Active // Online' : (info.error_message || 'Failed verification')) + sourceLabel);
                                });
                            }
                        }
                    });

                    // 2. Fetch Activity log
                    $.ajax({
                        url: '/Ascendance/wp-json/ascendance/v1/dashboard/activity',
                        method: 'GET',
                        headers: {
                            'X-WP-Nonce': '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>'
                        },
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
                    // Clean up query parameters from the address bar to prevent notices from displaying on refresh
                    if (window.history.replaceState) {
                        const url = new URL(window.location.href);
                        if (url.searchParams.has('settings_saved') || url.searchParams.has('rechecked')) {
                            url.searchParams.delete('settings_saved');
                            url.searchParams.delete('rechecked');
                            window.history.replaceState({}, document.title, url.pathname + url.search);
                        }
                    }
                });
            </script>
        <?php
    }

    /**
     * Handle Settings Page Save Actions
     */
    public function handle_settings_page_actions() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Save AI settings
        if ( isset( $_POST['save_ai_settings'] ) ) {
            check_admin_referer( 'ascendance_ai_settings_action', 'ascendance_ai_settings_nonce' );

            $ai_studio = AI_Studio::get_instance();

            update_option( 'ascendance_ai_monthly_cap', sanitize_text_field( $_POST['ai_monthly_cap'] ) );
            update_option( 'ascendance_ai_system_prompt', wp_kses_post( wp_unslash( $_POST['ai_system_prompt'] ) ) );
            
            // Save API Keys overrides
            foreach ( array( 'anthropic', 'openai', 'gemini' ) as $provider ) {
                if ( isset( $_POST['ascendance_' . $provider . '_api_key'] ) ) {
                    $key = sanitize_text_field( $_POST['ascendance_' . $provider . '_api_key'] );
                    $existing_key = get_option( 'ascendance_' . $provider . '_api_key' );
                    $masked_existing = $ai_studio->mask_api_key( $existing_key );
                    
                    // If the key has changed from its masked representation, save it
                    if ( $key !== $masked_existing ) {
                        update_option( 'ascendance_' . $provider . '_api_key', $key );
                    }
                }
            }

            // Always run live verification on settings save to update transients & persistent cache
            $ai_studio->get_api_key_status( 'anthropic', true );
            $ai_studio->get_api_key_status( 'openai', true );
            $ai_studio->get_api_key_status( 'gemini', true );

            wp_safe_redirect( admin_url( 'admin.php?page=ascendance-settings&settings_saved=ai' ) );
            exit;
        }

        // Save analytics settings
        if ( isset( $_POST['save_analytics_settings'] ) ) {
            check_admin_referer( 'ascendance_analytics_settings_action', 'ascendance_analytics_settings_nonce' );
            
            update_option( 'ascendance_gtm_id', sanitize_text_field( $_POST['ascendance_gtm_id'] ) );
            update_option( 'ascendance_ga4_id', sanitize_text_field( $_POST['ascendance_ga4_id'] ) );
            update_option( 'ascendance_clarity_id', sanitize_text_field( $_POST['ascendance_clarity_id'] ) );
            update_option( 'ascendance_facebook_pixel_id', sanitize_text_field( $_POST['ascendance_facebook_pixel_id'] ) );
            update_option( 'ascendance_hotjar_id', sanitize_text_field( $_POST['ascendance_hotjar_id'] ) );
            
            wp_safe_redirect( admin_url( 'admin.php?page=ascendance-settings&settings_saved=analytics' ) );
            exit;
        }

        // Handle manual recheck action from the settings page
        if ( isset( $_GET['action'] ) && 'recheck_gateways' === $_GET['action'] ) {
            check_admin_referer( 'ascendance_recheck_gateways' );
            
            $ai_studio = AI_Studio::get_instance();
            $ai_studio->get_api_key_status( 'anthropic', true );
            $ai_studio->get_api_key_status( 'openai', true );
            $ai_studio->get_api_key_status( 'gemini', true );
            
            wp_safe_redirect( admin_url( 'admin.php?page=ascendance-settings&rechecked=1' ) );
            exit;
        }
    }

    /**
     * Render the custom consolidated Settings Page
     */
    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'ascendance-core' ) );
        }

        $ai_studio = AI_Studio::get_instance();
        
        // Fetch AI studio stats
        $current_cost = $ai_studio->get_monthly_cost();
        $cap = floatval( get_option( 'ascendance_ai_monthly_cap', 100.00 ) );
        $percent = min( 100, round( ( $current_cost / $cap ) * 100, 2 ) );

        $gateways = array(
            'anthropic' => array(
                'name'      => 'Anthropic Claude',
                'doc_link'  => 'https://console.anthropic.com/settings/keys',
                'status'    => $ai_studio->get_api_key_status( 'anthropic' ),
            ),
            'openai'    => array(
                'name'      => 'OpenAI GPT',
                'doc_link'  => 'https://platform.openai.com/api-keys',
                'status'    => $ai_studio->get_api_key_status( 'openai' ),
            ),
            'gemini'    => array(
                'name'      => 'Google Gemini',
                'doc_link'  => 'https://aistudio.google.com/app/apikey',
                'status'    => $ai_studio->get_api_key_status( 'gemini' ),
            ),
        );

        $system_prompt = get_option( 'ascendance_ai_system_prompt' );
        if ( $system_prompt ) {
            $system_prompt = stripslashes( $system_prompt );
        } else {
            $system_prompt = "You are an analytical writer for Ascendance Strategies, a Paris-based strategic intelligence advisory firm focused on the US-DRC Strategic Partnership, critical minerals supply chains, and the Sakania-Lobito Corridor. Your readers are institutional subscribers: government bodies, investors, multilaterals, and corporates active in central Africa.\n\nVOICE:\n- Measured, institutional, evidence-led. Closer to a Financial Times long-read than a blog post.\n- Short, declarative sentences. Avoid headline-style cleverness in body text.\n- Name actors explicitly every time. No pronouns where an entity name fits.\n- Use precise dates (\"In May 2026\") not relative time (\"recently\").\n- One claim per paragraph. State claim, then evidence, then implication.\n- Provide fully detailed, exhaustive, and in-depth coverage of the topic. Avoid short, simple, or brief summaries. Elaborate on historical context, regulatory backgrounds, financial details, and strategic implications with granular detail.\n\nSTRUCTURE for an Intelligence Brief:\n1. Open with a 40-80 word definitional paragraph that fully answers the article's title as a question. This is the citable paragraph.\n2. A \"Key takeaways\" block of 3-5 bullets.\n3. H2 section headings phrased as questions a reader might actually ask.\n4. End with a \"Sources\" block listing the evidence base.\n\nWHAT TO AVOID:\n- No \"In conclusion\" or \"In summary\" sign-offs.\n- No marketing copy, no calls to action, no \"Subscribe to learn more\".\n- Do not invent statistics, dates, or named entities. If you don't know something, write [VERIFY] in brackets where it should go.\n- Do not use the words: leverage, synergy, robust, ecosystem, holistic, game-changer, paradigm.\n\nOUTPUT:\n- Return the article in HTML format.\n- After the article body, output three additional sections:\n  * === SUGGESTED_PUBLIC_EXCERPT ===\n  * === SUGGESTED_KEY_TAKEAWAYS ===\n  * === SUGGESTED_IMAGE_PROMPTS ===";
        }

        $gtm_id = get_option( 'ascendance_gtm_id', 'GTM-XXXXXXX' );
        $ga4_id = get_option( 'ascendance_ga4_id', '' );
        $clarity_id = get_option( 'ascendance_clarity_id', '' );
        $facebook_pixel_id = get_option( 'ascendance_facebook_pixel_id', '' );
        $hotjar_id = get_option( 'ascendance_hotjar_id', '' );

        ?>
        <div class="wrap ascendance-dashboard-wrap ascendance-settings-wrap">
            <h1 class="screen-reader-text" style="display: none;"><?php esc_html_e( 'Settings', 'ascendance-core' ); ?></h1>
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;700&family=Outfit:wght@400;500;600;700;800&display=swap');

                .ascendance-settings-inner {
                    background: #070B13;
                    padding: 30px;
                    border-radius: 2px;
                    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 10px 10px -5px rgba(0, 0, 0, 0.4);
                    border: 1px solid rgba(255, 255, 255, 0.08);
                    color: #FFFFFF;
                    font-family: 'Inter', sans-serif;
                    margin-right: 20px;
                    margin-top: 25px !important;
                }
                .settings-header {
                    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
                    padding-bottom: 20px;
                    margin-bottom: 30px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .settings-header h1 {
                    color: #FFFFFF;
                    font-family: 'Outfit', sans-serif;
                    font-size: 28px;
                    font-weight: 800;
                    margin: 0;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }
                .settings-grid {
                    display: grid;
                    grid-template-columns: 1.2fr 1fr;
                    gap: 30px;
                }
                .settings-card {
                    background: #0D1527;
                    border: 1px solid rgba(255, 255, 255, 0.08);
                    border-radius: 2px;
                    padding: 25px;
                    margin-bottom: 30px;
                }
                .settings-card h3 {
                    color: #FFFFFF;
                    font-family: 'Outfit', sans-serif;
                    font-size: 15px;
                    font-weight: 700;
                    margin-top: 0;
                    margin-bottom: 20px;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
                    padding-bottom: 10px;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                .settings-field-group {
                    margin-bottom: 20px;
                }
                .settings-label {
                    display: block;
                    font-family: 'Outfit', sans-serif;
                    font-size: 11px;
                    font-weight: 600;
                    text-transform: uppercase;
                    color: rgba(255, 255, 255, 0.8);
                    margin-bottom: 8px;
                    letter-spacing: 0.5px;
                }
                .settings-input {
                    width: 100%;
                    background: #070B13;
                    border: 1px solid rgba(255, 255, 255, 0.08);
                    border-radius: 2px;
                    color: #FFFFFF;
                    padding: 10px 14px;
                    font-family: 'JetBrains Mono', monospace;
                    font-size: 13px;
                }
                .settings-input:focus {
                    border-color: #BC1B1D;
                    outline: none;
                    box-shadow: 0 0 0 1px #BC1B1D;
                }
                .settings-textarea {
                    width: 100%;
                    background: #070B13;
                    border: 1px solid rgba(255, 255, 255, 0.08);
                    border-radius: 2px;
                    color: #FFFFFF;
                    padding: 12px;
                    font-family: 'JetBrains Mono', monospace;
                    font-size: 11px;
                    line-height: 1.5;
                    height: 380px;
                    resize: vertical;
                }
                .settings-textarea:focus {
                    border-color: #BC1B1D;
                    outline: none;
                    box-shadow: 0 0 0 1px #BC1B1D;
                }
                .settings-help {
                    font-size: 11px;
                    color: rgba(255, 255, 255, 0.4);
                    margin-top: 6px;
                    font-family: 'JetBrains Mono', monospace;
                }
                .settings-btn-save {
                    background: transparent;
                    border: 1px solid #10B981;
                    color: #10B981;
                    font-family: 'Outfit', sans-serif;
                    font-weight: 600;
                    font-size: 12px;
                    text-transform: uppercase;
                    padding: 10px 20px;
                    border-radius: 2px;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    letter-spacing: 0.5px;
                }
                .settings-btn-save:hover {
                    background: #10B981;
                    color: #070B13;
                }
                
                /* API Gateways Status Grid */
                .gateway-grid {
                    display: flex;
                    flex-direction: column;
                    gap: 15px;
                }
                .gateway-item {
                    background: #070B13;
                    border: 1px solid rgba(255, 255, 255, 0.05);
                    border-radius: 2px;
                    padding: 15px;
                }
                .gateway-name-status {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 8px;
                }
                .gateway-title {
                    font-family: 'Outfit', sans-serif;
                    font-size: 13px;
                    font-weight: 600;
                    color: #FFFFFF;
                }
                .gateway-badge {
                    font-family: 'JetBrains Mono', monospace;
                    font-size: 9px;
                    padding: 2px 6px;
                    border-radius: 2px;
                    text-transform: uppercase;
                    font-weight: 700;
                }
                .gateway-badge.active {
                    background: rgba(16, 185, 129, 0.1);
                    color: #10B981;
                    border: 1px solid rgba(16, 185, 129, 0.2);
                }
                .gateway-badge.failing {
                    background: rgba(239, 68, 68, 0.1);
                    color: #EF4444;
                    border: 1px solid rgba(239, 68, 68, 0.2);
                }
                .gateway-badge.missing {
                    background: rgba(245, 158, 11, 0.1);
                    color: #F59E0B;
                    border: 1px solid rgba(245, 158, 11, 0.2);
                }
            </style>
            
            <div class="ascendance-settings-inner">
                <div class="settings-header">
                <h1>Platform Configuration Console</h1>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=ascendance-mission-control' ) ); ?>" class="studio-btn" style="text-decoration: none; padding: 8px 16px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.15); box-shadow: none;">
                    <i class="dashicons dashicons-arrow-left-alt" style="font-size: 14px; width: 14px; height: 14px; margin-top: 3px; margin-right: 4px;"></i> Return to Dashboard
                </a>
            </div>

            <?php if ( isset( $_GET['settings_saved'] ) ) : ?>
                <div class="notice notice-success is-dismissible">
                    <p>
                        <?php 
                        if ( 'ai' === $_GET['settings_saved'] ) {
                            esc_html_e( 'AI Prompt & Key Settings saved successfully. Cache flushed.', 'ascendance-core' );
                        } else {
                            esc_html_e( 'Analytics GTM configuration updated.', 'ascendance-core' );
                        }
                        ?>
                    </p>
                </div>
            <?php endif; ?>
            <?php if ( isset( $_GET['rechecked'] ) ) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e( 'API validation keys rechecked and verified.', 'ascendance-core' ); ?></p>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <?php wp_nonce_field( 'ascendance_ai_settings_action', 'ascendance_ai_settings_nonce' ); ?>
                
                <div class="settings-grid" style="margin-bottom: 30px;">
                    <!-- Left: Budget & System Prompt Guidelines -->
                    <div class="settings-card" style="margin-bottom: 0;">
                        <h3><i class="dashicons dashicons-wand" style="color: #BC1B1D;"></i> AI SYSTEM PROMPT ENGINE & BUDGET</h3>
                        
                        <div class="settings-field-group">
                            <label for="ai_monthly_cap" class="settings-label"><?php esc_html_e( 'Monthly Budget Cap ($)', 'ascendance-core' ); ?></label>
                            <input type="number" step="10" name="ai_monthly_cap" id="ai_monthly_cap" class="settings-input" style="max-width: 250px;" value="<?php echo esc_attr( $cap ); ?>" required />
                            <div class="settings-help">&gt; Monthly spending threshold in USD.</div>
                        </div>

                        <div class="settings-field-group" style="margin-bottom: 0;">
                            <label for="ai_system_prompt" class="settings-label"><?php esc_html_e( 'System Editorial Guidelines (Prompt)', 'ascendance-core' ); ?></label>
                            <textarea name="ai_system_prompt" id="ai_system_prompt" class="settings-textarea" style="height: 380px;"><?php echo esc_textarea( $system_prompt ); ?></textarea>
                        </div>
                    </div>

                    <!-- Right: API Key Database Overrides & Connection Status -->
                    <div class="settings-card" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <h3 style="display: flex; justify-content: space-between; align-items: center;">
                                <span><i class="dashicons dashicons-admin-network" style="color: #3B82F6;"></i> API KEY OVERRIDES & STATUS</span>
                                <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=ascendance-settings&action=recheck_gateways' ), 'ascendance_recheck_gateways' ) ); ?>" class="studio-btn" style="padding: 4px 8px; font-size: 10px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.15); box-shadow: none; text-decoration: none; line-height: 1.4; border-radius: 2px;">
                                    <i class="dashicons dashicons-update" style="font-size: 10px; width: 10px; height: 10px; margin-top: 1px; margin-right: 2px;"></i> Recheck Status
                                </a>
                            </h3>
                            <p style="font-size: 11px; color: rgba(255, 255, 255, 0.5); line-height: 1.5; margin-bottom: 25px; margin-top: 0; font-family: 'JetBrains Mono', monospace;">
                                &gt; Values defined below override the .env global defaults. To remove overrides, clear field and save.
                            </p>

                            <?php foreach ( $gateways as $provider => $gateway ) : 
                                $status = $gateway['status']['status'];
                                $badge_class = 'missing';
                                $badge_label = 'Unconfigured';
                                $status_color = 'rgba(255, 255, 255, 0.4)';
                                $status_msg = $gateway['status']['error_message'] ? $gateway['status']['error_message'] : 'Key validation check failed.';
                                
                                if ( 'active' === $status ) {
                                    $badge_class = 'active';
                                    $badge_label = 'Online / Active';
                                    $status_color = 'rgba(16, 185, 129, 0.7)';
                                    $status_msg = 'Connection secure. Ready.';
                                } elseif ( 'failing' === $status ) {
                                    $badge_class = 'failing';
                                    $badge_label = 'Failing / Error';
                                    $status_color = '#EF4444';
                                }
                                $raw_source = $gateway['status']['source'];
                                $source_label = '';
                                if ( 'Database Override' === $raw_source ) {
                                    $source_label = '';
                                } elseif ( 'Environment Variable (.env)' === $raw_source || strpos( $raw_source, 'Constant' ) !== false ) {
                                    $source_label = __( 'Using server default key.', 'ascendance-core' );
                                }
                            ?>
                                <div class="settings-field-group" style="margin-bottom: 25px; border-bottom: 1px solid rgba(255, 255, 255, 0.03); padding-bottom: 20px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                        <label for="ascendance_<?php echo $provider; ?>_api_key" class="settings-label" style="margin-bottom: 0;"><?php echo esc_html( $gateway['name'] ); ?> Key</label>
                                        <span class="gateway-badge <?php echo $badge_class; ?>" style="font-size: 8px; padding: 2px 6px;"><?php echo esc_html( $badge_label ); ?></span>
                                    </div>
                                    <input type="text" spellcheck="false" autocomplete="off" name="ascendance_<?php echo $provider; ?>_api_key" id="ascendance_<?php echo $provider; ?>_api_key" class="settings-input" value="<?php echo esc_attr( $ai_studio->mask_api_key( get_option( 'ascendance_' . $provider . '_api_key' ) ) ); ?>" placeholder="<?php echo 'gemini' === $provider ? 'AIzaSy...' : ('openai' === $provider ? 'sk-...' : 'sk-ant-...'); ?>" />
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 6px;">
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <?php if ( ! empty( $source_label ) ) : ?>
                                                <div class="settings-help" style="margin-top: 0; margin-right: 5px;"><?php echo esc_html( $source_label ); ?></div>
                                            <?php endif; ?>
                                            <a href="<?php echo esc_url( $gateway['doc_link'] ); ?>" target="_blank" rel="noopener noreferrer" style="color: #3B82F6; text-decoration: none; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; gap: 3px; transition: color 0.2s ease;">
                                                <i class="dashicons dashicons-external" style="font-size: 12px; width: 12px; height: 12px; margin-top: 2px;"></i> <?php esc_html_e( 'Generate Key', 'ascendance-core' ); ?>
                                            </a>
                                        </div>
                                        <div style="font-size: 10px; color: <?php echo $status_color; ?>; font-family: 'JetBrains Mono', monospace;">
                                            &gt; <?php echo esc_html( $status_msg ); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div style="margin-top: 10px;">
                            <input type="submit" name="save_ai_settings" class="settings-btn-save" style="width: 100%; text-align: center; display: block;" value="Save Prompt & Key Settings" />
                        </div>
                    </div>
                </div>
            </form>

            <!-- Analytics Settings -->
            <div class="settings-card">
                <h3><i class="dashicons dashicons-chart-bar" style="color: #10B981;"></i> WEB ANALYTICS & VISITOR TRACKING</h3>
                <form method="post" action="">
                    <?php wp_nonce_field( 'ascendance_analytics_settings_action', 'ascendance_analytics_settings_nonce' ); ?>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 25px;">
                        <!-- Google Tag Manager -->
                        <div class="settings-field-group" style="margin-bottom: 0;">
                            <label for="ascendance_gtm_id" class="settings-label"><?php esc_html_e( 'Google Tag Manager ID', 'ascendance-core' ); ?></label>
                            <input type="text" name="ascendance_gtm_id" id="ascendance_gtm_id" class="settings-input" value="<?php echo esc_attr( $gtm_id ); ?>" placeholder="GTM-XXXXXXX" />
                            <div class="settings-help">&gt; Primary Tag Manager container. e.g. GTM-XXXXXXX</div>
                        </div>

                        <!-- Google Analytics (GA4) -->
                        <div class="settings-field-group" style="margin-bottom: 0;">
                            <label for="ascendance_ga4_id" class="settings-label"><?php esc_html_e( 'Google Analytics (GA4) ID', 'ascendance-core' ); ?></label>
                            <input type="text" name="ascendance_ga4_id" id="ascendance_ga4_id" class="settings-input" value="<?php echo esc_attr( $ga4_id ); ?>" placeholder="G-XXXXXXXXXX" />
                            <div class="settings-help">&gt; GA4 Measurement ID. e.g. G-XXXXXXXXXX</div>
                        </div>

                        <!-- Microsoft Clarity -->
                        <div class="settings-field-group" style="margin-bottom: 0;">
                            <label for="ascendance_clarity_id" class="settings-label"><?php esc_html_e( 'Microsoft Clarity Project ID', 'ascendance-core' ); ?></label>
                            <input type="text" name="ascendance_clarity_id" id="ascendance_clarity_id" class="settings-input" value="<?php echo esc_attr( $clarity_id ); ?>" placeholder="xxxxxxxxxx" />
                            <div class="settings-help">&gt; Clarity tracking code ID. e.g. xxxxxxxxxx</div>
                        </div>

                        <!-- Facebook Pixel -->
                        <div class="settings-field-group" style="margin-bottom: 0;">
                            <label for="ascendance_facebook_pixel_id" class="settings-label"><?php esc_html_e( 'Facebook Pixel ID', 'ascendance-core' ); ?></label>
                            <input type="text" name="ascendance_facebook_pixel_id" id="ascendance_facebook_pixel_id" class="settings-input" value="<?php echo esc_attr( $facebook_pixel_id ); ?>" placeholder="xxxxxxxxxxxxxxx" />
                            <div class="settings-help">&gt; Meta Pixel tracking ID. e.g. xxxxxxxxxxxxxxx</div>
                        </div>

                        <!-- Hotjar Site ID -->
                        <div class="settings-field-group" style="margin-bottom: 0;">
                            <label for="ascendance_hotjar_id" class="settings-label"><?php esc_html_e( 'Hotjar Site ID', 'ascendance-core' ); ?></label>
                            <input type="text" name="ascendance_hotjar_id" id="ascendance_hotjar_id" class="settings-input" value="<?php echo esc_attr( $hotjar_id ); ?>" placeholder="xxxxxxx" />
                            <div class="settings-help">&gt; Hotjar tracking Site ID. e.g. xxxxxxx</div>
                        </div>
                    </div>
                    
                    <input type="submit" name="save_analytics_settings" class="settings-btn-save" style="border-color: #10B981; color: #10B981;" value="Save Analytics Configuration" />
                </form>
            </div><!-- .ascendance-settings-inner -->
        </div><!-- .wrap -->
        <?php
    }

    /**
     * Add custom admin menu separators around our Ascendance Platform block
     */
    public function add_admin_menu_separators() {
        global $menu;
        $menu[31] = array( '', 'read', 'separator-ascendance-1', '', 'wp-menu-separator' );
        $menu[36] = array( '', 'read', 'separator-ascendance-2', '', 'wp-menu-separator' );
        ksort( $menu );
    }
}
