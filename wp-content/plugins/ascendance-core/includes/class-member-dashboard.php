<?php
/**
 * Member Dashboard and Subscription UI Handler Class
 *
 * @package Ascendance\Core
 */

namespace Ascendance\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Member_Dashboard {

    /**
     * Singleton instance
     * @var Member_Dashboard|null
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
        add_shortcode( 'ascendance_member_dashboard', array( $this, 'render_dashboard' ) );
        add_shortcode( 'ascendance_pricing_table', array( $this, 'render_pricing_table' ) );
        add_action( 'show_user_profile', array( $this, 'render_user_preferences' ) );
        add_action( 'edit_user_profile', array( $this, 'render_user_preferences' ) );
        add_action( 'personal_options_update', array( $this, 'save_user_preferences' ) );
        add_action( 'edit_user_profile_update', array( $this, 'save_user_preferences' ) );

        // Phase 2A.2 — Intercept legacy PMPro checkout page
        // Legacy checkout interception removed

        // Phase 4A — Category entitlement admin metabox (admin-only on user-edit.php)
        add_action( 'edit_user_profile', array( $this, 'render_category_entitlements_metabox' ) );
        add_action( 'edit_user_profile_update', array( $this, 'save_category_entitlements' ) );

        // REST & AJAX endpoints for Dashboard 2.0 & 2C Workspace
        add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
        add_action( 'wp_ajax_asc_save_dashboard_preferences', array( $this, 'ajax_save_dashboard_preferences' ) );
        add_action( 'wp_ajax_asc_record_reading_progress', array( $this, 'ajax_record_reading_progress' ) );
        add_action( 'wp_ajax_asc_save_user_note', array( $this, 'ajax_save_user_note' ) );
        add_action( 'wp_ajax_asc_delete_user_note', array( $this, 'ajax_delete_user_note' ) );
        add_action( 'wp_ajax_asc_filter_saved_posts', array( $this, 'ajax_filter_saved_posts' ) );
        add_action( 'wp_ajax_asc_remove_history_item', array( $this, 'ajax_remove_history_item' ) );
        add_action( 'wp_ajax_asc_clear_reading_history', array( $this, 'ajax_clear_reading_history' ) );
        add_action( 'wp_ajax_asc_filter_reading_history', array( $this, 'ajax_filter_reading_history' ) );
        add_action( 'init', array( $this, 'handle_pdf_download_request' ) );
        add_action( 'wp_ajax_asc_generate_pdf_token', array( $this, 'ajax_generate_pdf_token' ) );
    }

    /**
     * Intercepts legacy /membership-checkout/ page to render WP Simple Pay form instead
     *
     * @param string $content
     * @return string
     */
    /**
     * Renders member dashboard HTML
     *
     * @return string Dashboard markup
     */
    public function render_dashboard() {
        if ( ! is_user_logged_in() ) {
            return '<div class="card bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-8 rounded-sm shadow-sm text-center py-16 flex flex-col items-center gap-4">
                <i class="fa-solid fa-lock text-3xl text-brand-red mb-2"></i>
                <h3 class="text-xl font-sans font-bold text-brand-text-primary dark:text-white">' . esc_html__( 'Subscriber Area Restricted', 'ascendance-core' ) . '</h3>
                <p class="text-sm text-brand-text-muted dark:text-cream/70 max-w-[400px] leading-relaxed mb-4">' . esc_html__( 'Please sign in or register to view your custom dashboard feed.', 'ascendance-core' ) . '</p>
                <a href="' . esc_url( wp_login_url( get_permalink() ) ) . '" class="btn btn-primary">' . esc_html__( 'Sign In to Account', 'ascendance-core' ) . '</a>
            </div>';
        }

        $user_id = get_current_user_id();
        $user_data = get_userdata( $user_id );
        
        // Fetch membership details dynamically
        $roles = (array) $user_data->roles;
        $tier_slug = 'free';
        foreach ( $roles as $r ) {
            if ( 0 === strpos( $r, 'ascendance_' ) && 'ascendance_subscriber' !== $r ) {
                $tier_slug = str_replace( 'ascendance_', '', $r );
                break;
            }
        }
        
        $level_names = array(
            'essential'    => __( 'Essential Tier', 'ascendance-core' ),
            'professional' => __( 'Professional Tier', 'ascendance-core' ),
            'enterprise'   => __( 'Enterprise Tier', 'ascendance-core' ),
            'free'         => __( 'Free Guest', 'ascendance-core' ),
        );
        $level_name = isset( $level_names[ $tier_slug ] ) ? $level_names[ $tier_slug ] : ucfirst( $tier_slug );

        $sub_status = get_user_meta( $user_id, 'ascendance_stripe_subscription_status', true ) ?: 'active';
        $period_end = get_user_meta( $user_id, 'ascendance_stripe_period_end', true );
        
        if ( 'free' === $tier_slug ) {
            $billing_info = __( 'Free Access', 'ascendance-core' );
        } else {
            $status_labels = array(
                'active'        => __( 'ACTIVE', 'ascendance-core' ),
                'canceling'     => __( 'CANCELING', 'ascendance-core' ),
                'payment_issue' => __( 'PAYMENT ISSUE', 'ascendance-core' ),
                'canceled'      => __( 'CANCELED', 'ascendance-core' ),
                'revoked'       => __( 'REVOKED', 'ascendance-core' ),
            );
            $status_text = isset( $status_labels[ $sub_status ] ) ? $status_labels[ $sub_status ] : strtoupper( $sub_status );
            $billing_info = ! empty( $period_end ) ? sprintf( '%s (Until %s)', $status_text, date( 'M j, Y', strtotime( $period_end ) ) ) : $status_text;
        }

        // Fetch dynamic counts for telemetry stats
        $briefs_count = wp_count_posts( 'brief' )->publish;
        $dossiers_count = wp_count_posts( 'dossier' )->publish;
        $region_count = wp_count_terms( 'region' );
        $region_text = ! is_wp_error( $region_count ) ? sprintf( _n( '%d Region', '%d Regions', $region_count, 'ascendance-core' ), $region_count ) : 'Global';

        ob_start();
        ?>
        <div class="ascendance-dashboard flex flex-col gap-8">
            <!-- Dashboard Welcome Header -->
            <div class="dashboard-welcome-banner bg-navy text-white border border-brand-divider-dark p-6 rounded-sm shadow-md flex justify-between items-center flex-wrap gap-6 relative overflow-hidden">
                <div class="relative z-10 flex flex-col gap-1">
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 bg-brand-red rounded-full"></span>
                        <span class="text-[9px] font-mono uppercase tracking-widest text-brand-red font-bold dashboard-session-active-tag"><?php esc_html_e( 'Credentials Verified // Session Active', 'ascendance-core' ); ?></span>
                    </div>
                    <h2 class="text-2xl font-sans font-bold text-white"><?php printf( esc_html__( 'Welcome, %s', 'ascendance-core' ), esc_html( $user_data->display_name ) ); ?></h2>
                    <div class="text-[10px] font-mono text-cream/40 flex items-center gap-2.5 mt-1 dashboard-session-meta">
                        <span>UID: <?php echo esc_html( $user_id ); ?></span>
                        <span>|</span>
                    <span>Role: <?php
                        // Dynamic: shows the user's actual WP role instead of hardcoded 'Subscriber'.
                        $user_roles = $user_data->roles;
                        $display_role = ! empty( $user_roles ) ? ucfirst( reset( $user_roles ) ) : 'Subscriber';
                        echo esc_html( $display_role );
                    ?></span>
                        <span>|</span>
                        <span>Session Ref: <?php echo esc_html( substr( md5( $user_id . time() ), 0, 8 ) ); ?></span>
                    </div>
                </div>
                
                <div class="relative z-10 flex items-center gap-4 bg-navy-deep/60 px-4 py-3 rounded-sm border border-brand-divider-dark/40">
                    <div class="text-right">
                        <span class="block text-[9px] font-sans font-bold text-cream/40 uppercase tracking-widest dashboard-active-level-label"><?php esc_html_e( 'ACTIVE LEVEL TIER', 'ascendance-core' ); ?></span>
                        <span class="block text-sm font-sans font-bold text-white uppercase mt-0.5"><?php echo esc_html( $level_name ); ?></span>
                    </div>
                    <div class="w-px h-8 bg-brand-divider-dark/60"></div>
                    <div class="text-left">
                        <span class="block text-[9px] font-sans font-bold text-cream/40 uppercase tracking-widest dashboard-billing-cycle-label"><?php esc_html_e( 'BILLING CYCLE', 'ascendance-core' ); ?></span>
                        <span class="block text-xs font-mono font-bold text-[#E04B4B] uppercase mt-0.5 dashboard-billing-info-val"><?php echo esc_html( $billing_info ); ?></span>
                    </div>
                </div>
            </div>

            <!-- Command Center Telemetry Metrics Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="dashboard-metric-card bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-4 rounded-sm shadow-sm flex items-center gap-4 hover:translate-y-[-1px] transition-all duration-200">
                    <div class="w-10 h-10 rounded-sm bg-brand-red/10 dark:bg-brand-red/20 flex items-center justify-center text-brand-red text-base shrink-0">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[9px] font-mono text-brand-text-muted dark:text-cream/50 uppercase tracking-wider leading-none"><?php esc_html_e( 'SECURITY CLEARANCE', 'ascendance-core' ); ?></span>
                        <span class="text-xs font-sans font-bold text-brand-text-primary dark:text-white mt-1"><?php echo esc_html( $level_name ); ?></span>
                    </div>
                </div>
                <div class="dashboard-metric-card bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-4 rounded-sm shadow-sm flex items-center gap-4 hover:translate-y-[-1px] transition-all duration-200">
                    <div class="w-10 h-10 rounded-sm bg-blue-500/10 dark:bg-blue-500/20 flex items-center justify-center text-blue-500 dark:text-blue-400 text-base shrink-0">
                        <i class="fa-solid fa-earth-americas"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[9px] font-mono text-brand-text-muted dark:text-cream/50 uppercase tracking-wider leading-none"><?php esc_html_e( 'MONITORED REGIONS', 'ascendance-core' ); ?></span>
                        <span class="text-xs font-sans font-bold text-brand-text-primary dark:text-white mt-1"><?php echo esc_html( $region_text ); ?></span>
                    </div>
                </div>
                <div class="dashboard-metric-card bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-4 rounded-sm shadow-sm flex items-center gap-4 hover:translate-y-[-1px] transition-all duration-200">
                    <div class="w-10 h-10 rounded-sm bg-amber-500/10 dark:bg-amber-500/20 flex items-center justify-center text-amber-500 dark:text-amber-400 text-base shrink-0">
                        <i class="fa-solid fa-key"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[9px] font-mono text-brand-text-muted dark:text-cream/50 uppercase tracking-wider leading-none"><?php esc_html_e( 'DECRYPTION KEYSETS', 'ascendance-core' ); ?></span>
                        <span class="text-xs font-sans font-bold text-brand-text-primary dark:text-white mt-1"><?php printf( esc_html__( '%d Active Keys', 'ascendance-core' ), $briefs_count + $dossiers_count ); ?></span>
                    </div>
                </div>
                <div class="dashboard-metric-card bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-4 rounded-sm shadow-sm flex items-center gap-4 hover:translate-y-[-1px] transition-all duration-200">
                    <div class="w-10 h-10 rounded-sm bg-green-500/10 dark:bg-green-500/20 flex items-center justify-center text-green-500 dark:text-green-400 text-base shrink-0">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[9px] font-mono text-brand-text-muted dark:text-cream/50 uppercase tracking-wider leading-none"><?php esc_html_e( 'BILLING CYCLE STATUS', 'ascendance-core' ); ?></span>
                        <span class="text-xs font-sans font-bold text-brand-text-primary dark:text-white mt-1"><?php echo esc_html( $billing_info ); ?></span>
                    </div>
                </div>
            </div>

            <!-- Dashboard Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                <!-- Left: Feeds -->
                <div class="lg:col-span-2 flex flex-col gap-8">
                    <!-- Section: Recent Intelligence Briefs -->
                    <div class="dashboard-feed-card bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-6 md:p-8 rounded-sm shadow-sm">
                        <h3 class="text-sm font-sans font-bold text-brand-text-primary dark:text-white uppercase tracking-wider mb-6 border-b border-brand-divider-light dark:border-brand-divider-dark/40 pb-2.5 flex items-center gap-2">
                            <i class="fa-solid fa-file-invoice text-brand-red text-xs"></i>
                            <?php esc_html_e( 'Latest Intelligence Briefs', 'ascendance-core' ); ?>
                        </h3>
                        <div class="flex flex-col">
                            <?php $this->render_dashboard_feed( 'brief', 3 ); ?>
                        </div>
                    </div>

                    <!-- Section: Latest Updates -->
                    <div class="dashboard-feed-card bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-6 md:p-8 rounded-sm shadow-sm">
                        <h3 class="text-sm font-sans font-bold text-brand-text-primary dark:text-white uppercase tracking-wider mb-6 border-b border-brand-divider-light dark:border-brand-divider-dark/40 pb-2.5 flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-brand-red text-xs"></i>
                            <?php esc_html_e( 'Real-time Intelligence Updates', 'ascendance-core' ); ?>
                        </h3>
                        <div class="flex flex-col">
                            <?php $this->render_dashboard_feed( 'update', 3 ); ?>
                        </div>
                    </div>

                    <!-- Section: Latest Dossiers -->
                    <div class="dashboard-feed-card bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-6 md:p-8 rounded-sm shadow-sm">
                        <h3 class="text-sm font-sans font-bold text-brand-text-primary dark:text-white uppercase tracking-wider mb-6 border-b border-brand-divider-light dark:border-brand-divider-dark/40 pb-2.5 flex items-center gap-2">
                            <i class="fa-solid fa-folder-open text-brand-red text-xs"></i>
                            <?php esc_html_e( 'High-Density Dossiers', 'ascendance-core' ); ?>
                        </h3>
                        <div class="flex flex-col">
                            <?php $this->render_dashboard_feed( 'dossier', 3 ); ?>
                        </div>
                    </div>
                </div>

                <!-- Right: Recommendations & Settings -->
                <div class="lg:col-span-1 flex flex-col gap-8">
                    <!-- Personalized Recommendations -->
                    <div class="bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-6 md:p-8 rounded-sm shadow-sm flex flex-col gap-4">
                        <h4 class="text-xs font-sans font-bold text-brand-text-primary dark:text-white uppercase tracking-wider border-b border-brand-divider-light dark:border-brand-divider-dark/40 pb-2 flex items-center gap-2">
                            <i class="fa-solid fa-wand-magic-sparkles text-brand-red text-xs"></i>
                            <?php esc_html_e( 'Targeted Briefings', 'ascendance-core' ); ?>
                        </h4>
                        <?php $this->render_recommended_feed( $user_id ); ?>
                    </div>

                    <!-- Member Quick Actions -->
                    <div class="bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-6 md:p-8 rounded-sm shadow-sm flex flex-col gap-4">
                        <h4 class="text-xs font-sans font-bold text-brand-text-primary dark:text-white uppercase tracking-wider border-b border-brand-divider-light dark:border-brand-divider-dark/40 pb-2 flex items-center gap-2">
                            <i class="fa-solid fa-user-gear text-brand-red text-xs"></i>
                            <?php esc_html_e( 'Account Services', 'ascendance-core' ); ?>
                        </h4>
                        <div class="dashboard-services-grid grid grid-cols-2 gap-3">
                            <?php 
                            $customer_id = get_user_meta( $user_id, 'ascendance_stripe_customer_id', true );
                            if ( empty( $customer_id ) ) {
                                $customer_id = get_user_meta( $user_id, 'pmpro_stripe_customerid', true );
                            }
                            ?>
                            <a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>" class="flex flex-col items-center justify-center p-3 text-center rounded-sm border border-brand-divider-light dark:border-brand-divider-dark bg-cream/20 dark:bg-navy-deep/20 hover:border-brand-red dark:hover:border-brand-red-light transition-colors group">
                                <i class="fa-regular fa-id-card text-brand-red text-base mb-1.5 transition-transform group-hover:scale-110"></i>
                                <span class="text-[10px] font-sans font-bold text-brand-text-primary dark:text-cream leading-tight"><?php esc_html_e( 'Manage Tier', 'ascendance-core' ); ?></span>
                            </a>
                            <?php if ( ! empty( $customer_id ) ) : ?>
                                <button id="btn-billing-portal" class="flex flex-col items-center justify-center p-3 text-center rounded-sm border border-brand-divider-light dark:border-brand-divider-dark bg-cream/20 dark:bg-navy-deep/20 hover:border-brand-red dark:hover:border-brand-red-light transition-colors group">
                                    <i class="fa-regular fa-credit-card text-brand-red text-base mb-1.5 transition-transform group-hover:scale-110"></i>
                                    <span class="text-[10px] font-sans font-bold text-brand-text-primary dark:text-cream leading-tight"><?php esc_html_e( 'Manage Billing', 'ascendance-core' ); ?></span>
                                </button>
                                <script>
                                if (document.getElementById('btn-billing-portal')) {
                                    document.getElementById('btn-billing-portal').addEventListener('click', function(e) {
                                        e.preventDefault();
                                        const btn = this;
                                        const originalText = btn.innerHTML;
                                        btn.disabled = true;
                                        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mb-1.5"></i>';
                                        fetch('<?php echo esc_url_raw( get_rest_url( null, 'ascendance/v1/billing/portal-session' ) ); ?>', {
                                            method: 'POST'
                                        })
                                        .then(res => res.json())
                                        .then(data => {
                                            if (data.url) {
                                                window.location.href = data.url;
                                            } else {
                                                alert(data.error || 'Failed to redirect to billing portal.');
                                                btn.disabled = false;
                                                btn.innerHTML = originalText;
                                            }
                                        })
                                        .catch(err => {
                                            console.error(err);
                                            alert('An error occurred.');
                                            btn.disabled = false;
                                            btn.innerHTML = originalText;
                                        });
                                    });
                                }
                                </script>
                            <?php else : ?>
                                <a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>" class="flex flex-col items-center justify-center p-3 text-center rounded-sm border border-brand-divider-light dark:border-brand-divider-dark bg-cream/20 dark:bg-navy-deep/20 hover:border-brand-red dark:hover:border-brand-red-light transition-colors group">
                                    <i class="fa-regular fa-credit-card text-brand-red text-base mb-1.5 transition-transform group-hover:scale-110"></i>
                                    <span class="text-[10px] font-sans font-bold text-brand-text-primary dark:text-cream leading-tight"><?php esc_html_e( 'Billing Info', 'ascendance-core' ); ?></span>
                                </a>
                            <?php endif; ?>
                            <a href="<?php echo esc_url( get_edit_user_link() ); ?>" class="flex flex-col items-center justify-center p-3 text-center rounded-sm border border-brand-divider-light dark:border-brand-divider-dark bg-cream/20 dark:bg-navy-deep/20 hover:border-brand-red dark:hover:border-brand-red-light transition-colors group">
                                <i class="fa-regular fa-user text-brand-red text-base mb-1.5 transition-transform group-hover:scale-110"></i>
                                <span class="text-[10px] font-sans font-bold text-brand-text-primary dark:text-cream leading-tight"><?php esc_html_e( 'Preferences', 'ascendance-core' ); ?></span>
                            </a>
                            <a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="flex flex-col items-center justify-center p-3 text-center rounded-sm border border-brand-divider-light dark:border-brand-divider-dark bg-cream/20 dark:bg-navy-deep/20 hover:border-brand-red dark:hover:border-brand-red-light transition-colors group">
                                <i class="fa-solid fa-arrow-right-from-bracket text-brand-red text-base mb-1.5 transition-transform group-hover:scale-110"></i>
                                <span class="text-[10px] font-sans font-bold text-brand-text-primary dark:text-cream leading-tight"><?php esc_html_e( 'Sign Out', 'ascendance-core' ); ?></span>
                            </a>
                        </div>
                    </div>

                    <!-- Platform System Telemetry Logs Panel -->
                    <div class="bg-[#030810] border border-brand-red/20 p-4 rounded-sm shadow-md font-mono text-[9px] text-[#00FF66] flex flex-col gap-2.5 shadow-[0_0_15px_rgba(188,27,29,0.05)]">
                        <div class="flex justify-between border-b border-brand-red/20 pb-1.5 text-brand-red font-bold">
                            <span>SECURE TERMINAL LOG</span>
                            <span>v2.1</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span>[SYS] Decoders online ... OK</span>
                            <span>[FEED] Satellite relays active ... SYNCED</span>
                            <span>[USER] Cryptographic verification ... PASS</span>
                            <span>[MEM] Credentials level matched ... OK</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render vertical lists of feed items
     */
    private function render_dashboard_feed( $post_type, $count ) {
        $query = new \WP_Query( array(
            'post_type'      => $post_type,
            'posts_per_page' => $count,
            'post_status'    => 'publish',
        ) );

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $post_id = get_the_ID();
                
                // Fetch required tier access
                $tier = get_field( 'tier_access', $post_id );
                if ( ! $tier ) {
                    $terms = wp_get_post_terms( $post_id, 'tier', array( 'fields' => 'slugs' ) );
                    $tier = ( ! empty( $terms ) && ! is_wp_error( $terms ) ) ? $terms[0] : 'essential';
                }
                $tier_label = ucfirst( $tier );
                
                // Region list
                $region_list = get_the_term_list( $post_id, 'region', '', ', ', '' ) ?: 'Global';
                
                // Dynamic styling
                $border_class = 'border-l-[3px] border-l-brand-red';
                $extra_badge = '';
                $attachment_block = '';

                if ( 'brief' === $post_type ) {
                    $border_class = 'border-l-[3px] border-l-[#2980B9]';
                    $claim = get_field( 'analytical_claim', $post_id );
                    if ( $claim ) {
                        $attachment_block = '<div class="mt-1.5 pl-3 border-l-2 border-brand-red/30 text-[10px] text-brand-text-muted dark:text-cream/60 leading-relaxed font-sans italic">
                            <span class="font-bold text-brand-red not-italic">CLAIM //</span> ' . esc_html( $claim ) . '
                        </div>';
                    }
                } elseif ( 'update' === $post_type ) {
                    $impact = get_field( 'impact_assessment', $post_id ) ?: 'medium';
                    $impact_details = array(
                        'low'      => array( 'border' => 'border-l-[3px] border-l-[#27AE60]', 'badge' => 'bg-[#27AE60]/10 text-[#27AE60]' ),
                        'medium'   => array( 'border' => 'border-l-[3px] border-l-[#2980B9]', 'badge' => 'bg-[#2980B9]/10 text-[#2980B9]' ),
                        'high'     => array( 'border' => 'border-l-[3px] border-l-[#E67E22]', 'badge' => 'bg-[#E67E22]/10 text-[#E67E22]' ),
                        'critical' => array( 'border' => 'border-l-[3px] border-l-brand-red', 'badge' => 'bg-brand-red/10 text-brand-red' ),
                    );
                    $style = isset( $impact_details[ $impact ] ) ? $impact_details[ $impact ] : $impact_details['medium'];
                    $border_class = $style['border'];
                    $extra_badge = '<span class="' . esc_attr( $style['badge'] ) . ' px-1.5 py-0.5 rounded-sm text-[8px] uppercase tracking-widest font-sans font-bold">' . esc_html( $impact ) . '</span>';
                } elseif ( 'dossier' === $post_type ) {
                    $border_class = 'border-l-[3px] border-l-brand-red';
                    $attachment_block = '<div class="text-[9px] font-mono text-brand-red uppercase tracking-wider mt-1.5 flex items-center gap-1.5"><i class="fa-solid fa-folder-open text-[8px]"></i>' . esc_html__( 'Strategic Intelligence Dossier Volume', 'ascendance-core' ) . '</div>';
                }
                ?>
                <div class="bg-transparent <?php echo esc_attr( $border_class ); ?> pl-4 py-3.5 pr-2 border-b border-brand-divider-light dark:border-brand-divider-dark/40 last:border-b-0 first:pt-0 last:pb-0 hover:translate-x-0.5 transition-transform duration-200 relative flex flex-col gap-1.5">
                    <div class="flex justify-between items-center text-[9px] font-mono font-bold tracking-wider text-brand-text-muted dark:text-cream/50 gap-2">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="uppercase font-sans font-bold text-brand-red"><?php echo esc_html( $post_type ); ?></span>
                            <span>•</span>
                            <span>
                                <?php 
                                if ( 'update' === $post_type ) {
                                    echo get_the_date( 'd.m.Y // H:i \U\T\C' );
                                } else {
                                    echo get_the_date( 'd F Y' );
                                }
                                ?>
                            </span>
                            <?php if ( $extra_badge ) : ?>
                                <span>•</span>
                                <?php echo $extra_badge; ?>
                            <?php endif; ?>
                        </div>
                        <span class="text-brand-text-primary dark:text-cream font-sans uppercase font-bold text-[8px] tracking-widest"><?php echo esc_html( $tier_label ); ?> Tier</span>
                    </div>
                    <h4 class="text-xs font-sans font-bold text-brand-text-primary dark:text-white leading-snug m-0">
                        <a href="<?php the_permalink(); ?>" class="hover:text-brand-red dark:hover:text-brand-red-light transition-colors"><?php the_title(); ?></a>
                    </h4>
                    <div class="text-[10px] text-brand-text-muted dark:text-cream/70 flex items-center gap-1">
                        <span class="font-bold text-brand-text-primary dark:text-cream"><?php esc_html_e( 'Region:', 'ascendance-core' ); ?></span> <span><?php echo strip_tags( $region_list ); ?></span>
                    </div>
                    <?php echo $attachment_block; ?>
                </div>
                <?php
            }
            wp_reset_postdata();
        } else {
            echo '<p class="text-xs text-brand-text-muted dark:text-cream/50 italic py-2">' . sprintf( __( 'No %s published yet.', 'ascendance-core' ), esc_html( $post_type ) . 's' ) . '</p>';
        }
    }

    /**
     * Render dynamic personalized recommendations based on preferred Industry and Region
     */
    private function render_recommended_feed( $user_id ) {
        $recommendations = Recommendation_Engine::get_instance()->get_ranked_recommendations( $user_id, array( 'brief', 'dossier' ), 3 );

        if ( ! empty( $recommendations ) ) {
            echo '<div class="flex flex-col gap-3">';
            
            // Labels for matching items
            $tier_labels = array(
                0 => array( 'label' => __( 'Highest Score', 'ascendance-core' ), 'badge' => 'bg-brand-red/10 text-brand-red border border-brand-red/20' ),
                1 => array( 'label' => __( 'Medium Score', 'ascendance-core' ), 'badge' => 'bg-orange-500/10 text-orange-500 border border-orange-500/20' ),
                2 => array( 'label' => __( 'Lowest Score', 'ascendance-core' ), 'badge' => 'bg-blue-500/10 text-blue-500 border border-blue-500/20' ),
            );

            foreach ( $recommendations as $index => $item ) {
                $post = $item['post'];
                $score_details = $item['score_details'];
                $score = $score_details['total_score'];
                
                // Set up visual metadata
                $has_score = $score > 0;
                $tier_style = $has_score && isset( $tier_labels[$index] ) ? $tier_labels[$index] : null;
                
                // Correctly setup post data
                $GLOBALS['post'] = $post;
                setup_postdata( $post );
                ?>
                <div class="flex items-start justify-between gap-3 text-xs border-b border-brand-divider-light dark:border-brand-divider-dark/40 pb-2.5 last:border-b-0 last:pb-0">
                    <div class="flex flex-col gap-1.5 w-full">
                        <a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" class="font-sans font-bold text-brand-text-primary dark:text-cream hover:text-brand-red dark:hover:text-brand-red-light transition-colors leading-snug"><?php echo esc_html( get_the_title( $post->ID ) ); ?></a>
                        
                        <div class="flex items-center gap-2 flex-wrap mt-0.5">
                            <span class="text-[9px] font-mono text-brand-text-muted dark:text-cream/50 uppercase"><?php echo esc_html( get_post_type( $post->ID ) ); ?></span>
                            
                            <?php if ( $has_score && $tier_style ) : ?>
                                <span class="text-[8px] font-mono text-brand-text-muted dark:text-cream/30">•</span>
                                <span class="px-1.5 py-0.5 rounded-sm text-[8px] font-mono font-bold tracking-wider <?php echo esc_attr( $tier_style['badge'] ); ?>">
                                    <?php echo esc_html( $tier_style['label'] ); ?> (<?php echo esc_html( $score ); ?> pts)
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <i class="fa-solid fa-angle-right text-[10px] text-brand-red/60 mt-1.5 flex-shrink-0"></i>
                </div>
                <?php
            }
            echo '</div>';
            wp_reset_postdata();
        } else {
            echo '<p class="text-xs text-brand-text-muted dark:text-cream/50 italic leading-relaxed">' . esc_html__( 'Configure your profile preferences below to receive specialized intelligence notifications.', 'ascendance-core' ) . '</p>';
        }
    }

    /**
     * Pricing table rendering [ascendance_pricing_table]
     */
    public function render_pricing_table() {
        ob_start();
        ?>
        <div class="pricing-table-matrix">
            <!-- Plan 1: Essential -->
            <div class="card pricing-tier-card">
                <span class="pricing-tier-card-number"><?php esc_html_e( 'Tier 1', 'ascendance-core' ); ?></span>
                <h3><?php esc_html_e( 'Essential', 'ascendance-core' ); ?></h3>
                <?php
                $essential_price    = '150';
                $essential_period   = 'month';
                ?>
                <div class="pricing-tier-card-price-row">
                    <span class="pricing-tier-card-price">$<?php echo esc_html( $essential_price ); ?></span>
                    <span class="pricing-tier-card-price-period">/ <?php echo esc_html( $essential_period ); ?></span>
                </div>
                <p class="pricing-tier-card-desc">
                    <?php esc_html_e( 'Full access to Intelligence Briefs, tracking updates, and primary industry research feeds.', 'ascendance-core' ); ?>
                </p>
                <?php 
                $essential_form_id = get_option( 'ascendance_essential_simpay_form_id' );
                if ( $essential_form_id ) : 
                    echo do_shortcode( sprintf( '[simpay id="%d"]', $essential_form_id ) );
                else : 
                ?>
                    <button class="btn btn-secondary" disabled><?php esc_html_e( 'Select Plan', 'ascendance-core' ); ?></button>
                <?php endif; ?>
            </div>

            <!-- Plan 2: Professional (Recommended) -->
            <div class="card pricing-tier-card featured-pricing">
                <span class="paywall-badge" style="position: absolute; top: -14px; left: 50%; transform: translateX(-50%);"> <?php esc_html_e( 'Recommended', 'ascendance-core' ); ?></span>
                <span class="pricing-tier-card-number" style="color: var(--color-red);"><?php esc_html_e( 'Tier 2', 'ascendance-core' ); ?></span>
                <h3><?php esc_html_e( 'Professional', 'ascendance-core' ); ?></h3>
                <?php
                $professional_price    = '299';
                $professional_period   = 'month';
                ?>
                <div class="pricing-tier-card-price-row">
                    <span class="pricing-tier-card-price">$<?php echo esc_html( $professional_price ); ?></span>
                    <span class="pricing-tier-card-price-period">/ <?php echo esc_html( $professional_period ); ?></span>
                </div>
                <p class="pricing-tier-card-desc">
                    <?php esc_html_e( 'Unlock high-density Dossiers, downloads, stakeholder profiling, and cross-referenced historical indexes.', 'ascendance-core' ); ?>
                </p>
                <?php 
                $professional_form_id = get_option( 'ascendance_professional_simpay_form_id' );
                if ( $professional_form_id ) : 
                    echo do_shortcode( sprintf( '[simpay id="%d"]', $professional_form_id ) );
                else : 
                ?>
                    <button class="btn btn-primary" disabled><?php esc_html_e( 'Activate Professional', 'ascendance-core' ); ?></button>
                <?php endif; ?>
            </div>

            <!-- Plan 3: Enterprise -->
            <div class="card pricing-tier-card">
                <span class="pricing-tier-card-number"><?php esc_html_e( 'Tier 3', 'ascendance-core' ); ?></span>
                <h3><?php esc_html_e( 'Enterprise', 'ascendance-core' ); ?></h3>
                <div class="pricing-tier-card-price-row">
                    <span class="pricing-tier-card-price"><?php esc_html_e( 'Custom', 'ascendance-core' ); ?></span>
                </div>
                <p class="pricing-tier-card-desc">
                    <?php esc_html_e( 'Full access to complete intelligence base, direct API hooks, dedicated dashboard instances, and custom queries.', 'ascendance-core' ); ?>
                </p>
                <?php
                // Dynamic: reads the Contact page ID from Mission Control → Settings → Platform Settings.
                $contact_page_id        = (int) get_option( 'ascendance_contact_page_id', 0 );
                $enterprise_contact_url = $contact_page_id ? get_permalink( $contact_page_id ) : home_url( '/contact/' );
                ?>
                <a href="<?php echo esc_url( $enterprise_contact_url ); ?>" class="btn btn-secondary"><?php esc_html_e( 'Contact Enterprise', 'ascendance-core' ); ?></a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * User profile preference settings (Admin Display)
     */
    public function render_user_preferences( $user ) {
        $preferred_topics = get_user_meta( $user->ID, 'preferred_topics', true );
        if ( empty( $preferred_topics ) ) {
            $preferred_topics = get_user_meta( $user->ID, 'preferred_industries', true );
        }
        $preferred_regions = get_user_meta( $user->ID, 'preferred_regions', true );

        $topics = get_terms( array( 'taxonomy' => 'topic', 'hide_empty' => false ) );
        $regions = get_terms( array( 'taxonomy' => 'region', 'hide_empty' => false ) );

        if ( ! is_array( $preferred_topics ) ) $preferred_topics = array();
        if ( ! is_array( $preferred_regions ) ) $preferred_regions = array();
        ?>
        <h3 style="color:var(--color-white); margin-top:20px;"><?php esc_html_e( 'Ascendance Intelligence Feed Customization', 'ascendance-core' ); ?></h3>
        <table class="form-table">
            <tr>
                <th><label><?php esc_html_e( 'Subscribed Topics', 'ascendance-core' ); ?></label></th>
                <td>
                    <?php if ( ! empty( $topics ) && ! is_wp_error( $topics ) ) : ?>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; max-width:600px;">
                            <?php foreach ( $topics as $topic ) : ?>
                                <label style="display:inline-flex; align-items:center; font-weight:normal;">
                                    <input type="checkbox" name="preferred_topics[]" value="<?php echo esc_attr( $topic->term_id ); ?>" <?php checked( in_array( $topic->term_id, $preferred_topics ) ); ?> style="margin-right:8px;" />
                                    <?php echo esc_html( $topic->name ); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p style="color:var(--text-muted);"><?php esc_html_e( 'No topic categories registered yet.', 'ascendance-core' ); ?></p>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th><label><?php esc_html_e( 'Subscribed Regions', 'ascendance-core' ); ?></label></th>
                <td>
                    <?php if ( ! empty( $regions ) && ! is_wp_error( $regions ) ) : ?>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; max-width:600px;">
                            <?php foreach ( $regions as $region ) : ?>
                                <label style="display:inline-flex; align-items:center; font-weight:normal;">
                                    <input type="checkbox" name="preferred_regions[]" value="<?php echo esc_attr( $region->term_id ); ?>" <?php checked( in_array( $region->term_id, $preferred_regions ) ); ?> style="margin-right:8px;" />
                                    <?php echo esc_html( $region->name ); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p style="color:var(--text-muted);"><?php esc_html_e( 'No region categories registered yet.', 'ascendance-core' ); ?></p>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Save profile preferences
     */
    public function save_user_preferences( $user_id ) {
        if ( ! current_user_can( 'edit_user', $user_id ) ) {
            return;
        }

        $topics = isset( $_POST['preferred_topics'] ) ? array_map( 'intval', $_POST['preferred_topics'] ) : array();
        $regions = isset( $_POST['preferred_regions'] ) ? array_map( 'intval', $_POST['preferred_regions'] ) : array();

        update_user_meta( $user_id, 'preferred_topics', $topics );
        update_user_meta( $user_id, 'preferred_regions', $regions );
    }

    /**
     * Register REST API routes for Dashboard 2.0
     */
    public function register_rest_routes() {
        register_rest_route( 'ascendance/v1', '/user/subscription', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'rest_get_subscription' ),
            'permission_callback' => 'is_user_logged_in',
        ) );

        register_rest_route( 'ascendance/v1', '/billing/portal-session', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'rest_create_portal_session' ),
            'permission_callback' => 'is_user_logged_in',
        ) );

        register_rest_route( 'ascendance/v1', '/category-checkout', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'rest_category_checkout' ),
            'permission_callback' => 'is_user_logged_in',
        ) );

        register_rest_route( 'ascendance/v1', '/user/saved/toggle', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'rest_toggle_saved' ),
            'permission_callback' => 'is_user_logged_in',
        ) );

        register_rest_route( 'ascendance/v1', '/user/reading-progress', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'rest_update_reading_progress' ),
            'permission_callback' => 'is_user_logged_in',
        ) );

        register_rest_route( 'ascendance/v1', '/user/preferences', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'rest_update_preferences' ),
            'permission_callback' => 'is_user_logged_in',
        ) );

        register_rest_route( 'ascendance/v1', '/user/recommendations', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'rest_get_recommendations' ),
            'permission_callback' => 'is_user_logged_in',
        ) );

        register_rest_route( 'ascendance/v1', '/user/saved-posts', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'rest_get_saved_posts' ),
            'permission_callback' => 'is_user_logged_in',
        ) );

        register_rest_route( 'ascendance/v1', '/user/notes', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'rest_get_notes' ),
            'permission_callback' => 'is_user_logged_in',
        ) );

        register_rest_route( 'ascendance/v1', '/user/notes/save', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'rest_save_note' ),
            'permission_callback' => 'is_user_logged_in',
        ) );

        register_rest_route( 'ascendance/v1', '/user/notes/delete', array(
            'methods'             => array( 'POST', 'DELETE' ),
            'callback'            => array( $this, 'rest_delete_note' ),
            'permission_callback' => 'is_user_logged_in',
        ) );

        register_rest_route( 'ascendance/v1', '/user/history', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'rest_get_history' ),
            'permission_callback' => 'is_user_logged_in',
        ) );

        register_rest_route( 'ascendance/v1', '/user/history/remove-item', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'rest_remove_history_item' ),
            'permission_callback' => 'is_user_logged_in',
        ) );

        register_rest_route( 'ascendance/v1', '/user/history/clear-all', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'rest_clear_reading_history' ),
            'permission_callback' => 'is_user_logged_in',
        ) );

        register_rest_route( 'ascendance/v1', '/pdf/token', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'rest_generate_pdf_token' ),
            'permission_callback' => 'is_user_logged_in',
        ) );

        register_rest_route( 'ascendance/v1', '/pdf/download', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'rest_download_pdf' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( 'ascendance/v1', '/user/category-addons', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'rest_get_category_addons' ),
            'permission_callback' => 'is_user_logged_in',
        ) );
    }

    /**
     * REST Handler: Get Current User's Subscription Details
     */
    public function rest_get_subscription( \WP_REST_Request $request ) {
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            return new \WP_REST_Response( array( 'error' => __( 'Authentication required.', 'ascendance-core' ) ), 401 );
        }

        $data = $this->get_user_subscription_data( $user_id );

        $response = new \WP_REST_Response( $data, 200 );
        $response->header( 'Cache-Control', 'private, no-cache, no-store, must-revalidate' );
        return $response;
    }

    /**
     * Helper to resolve authoritative user subscription details
     */
    public function get_user_subscription_data( $user_id ) {
        $user = get_userdata( $user_id );
        $default_data = array(
            'has_subscription'     => false,
            'tier'                 => 'free',
            'status'               => 'none',
            'price'                => '$0',
            'interval'             => '',
            'next_billing_date'    => null,
            'cancellation_date'    => null,
            'cancel_at_period_end' => false,
        );

        if ( ! $user ) {
            return $default_data;
        }

        $tier = 'free';
        $sub_status = 'none';
        $price = '$0';
        $interval = '';
        $next_billing_date = null;
        $cancellation_date = null;
        $cancel_at_period_end = false;
        
        $roles = (array) $user->roles;
        if ( in_array( 'administrator', $roles, true ) ) {
            $tier = 'admin';
            $sub_status = 'active';
            $price = 'System';
        }

        if ( function_exists( 'pmpro_getMembershipLevelForUser' ) ) {
            $level = pmpro_getMembershipLevelForUser( $user_id );
            if ( ! empty( $level ) ) {
                if ( $level->id == 3 ) {
                    $tier = 'enterprise';
                    $price = 'Custom';
                    $interval = 'annual';
                } elseif ( $level->id == 2 ) {
                    $tier = 'professional';
                } elseif ( $level->id == 1 ) {
                    $tier = 'essential';
                }
                
                $sub_status = 'active';
                if ( isset( $level->billing_amount ) && $level->billing_amount > 0 && function_exists( 'pmpro_formatPrice' ) ) {
                    $price = str_replace( '.00', '', pmpro_formatPrice( $level->billing_amount ) );
                    $interval = 'month';
                }
                
                if ( isset( $level->enddate ) && ! empty( $level->enddate ) ) {
                    // PMPro uses UNIX timestamp for enddate
                    $next_billing_date = date( 'M j, Y', $level->enddate );
                    
                    // If a user cancels, PMPro often sets an enddate without a recurring billing
                    $cancel_at_period_end = true;
                    $cancellation_date = $next_billing_date;
                }
            }
        }

        // Fallback for roles if PMPro didn't match (for legacy or admin assignment)
        if ( 'free' === $tier && 'admin' !== $tier ) {
            if ( in_array( 'ascendance_enterprise', $roles, true ) ) {
                $tier = 'enterprise';
                $price = 'Custom';
            } elseif ( in_array( 'ascendance_professional', $roles, true ) ) {
                $tier = 'professional';
                $price = '$299';
                $interval = 'month';
            } elseif ( in_array( 'ascendance_essential', $roles, true ) ) {
                $tier = 'essential';
                $price = '$150';
                $interval = 'month';
            }
            if ( 'free' !== $tier ) {
                $sub_status = 'active';
            }
        }

        $has_sub = in_array( $tier, array( 'essential', 'professional', 'enterprise', 'admin' ), true );

        return array(
            'has_subscription'     => $has_sub,
            'tier'                 => $tier,
            'status'               => $has_sub ? 'active' : 'none',
            'price'                => $price,
            'interval'             => $interval,
            'next_billing_date'    => $next_billing_date,
            'cancellation_date'    => $cancellation_date,
            'cancel_at_period_end' => $cancel_at_period_end,
        );
    }

    /**
     * REST Handler: Create Stripe Customer Portal Session
     * (Deprecated: PMPro natively handles Stripe Customer Portals on the Membership Account page)
     */
    public function rest_create_portal_session( \WP_REST_Request $request ) {
        return new \WP_REST_Response( array( 'error' => __( 'Billing portal is now managed natively through PMPro at /membership-account/', 'ascendance-core' ) ), 400 );
    }

    /**
     * REST Handler: Toggle Saved Post
     */
    public function rest_toggle_saved( \WP_REST_Request $request ) {
        $user_id = get_current_user_id();
        $params = $request->get_json_params() ?: $request->get_params();
        $post_id = isset( $params['post_id'] ) ? intval( $params['post_id'] ) : 0;

        if ( ! $post_id || ! get_post( $post_id ) ) {
            return new \WP_REST_Response( array( 'error' => __( 'Invalid post ID.', 'ascendance-core' ) ), 400 );
        }

        $saved = array_values( array_filter( array_map( 'intval', (array) get_user_meta( $user_id, 'as_saved_posts', true ) ) ) );

        if ( in_array( $post_id, $saved, true ) ) {
            $saved = array_values( array_diff( $saved, array( $post_id ) ) );
            update_user_meta( $user_id, 'as_saved_posts', $saved );
            Recommendation_Engine::get_instance()->invalidate_user_cache( $user_id );
            return new \WP_REST_Response( array( 'action' => 'removed', 'count' => count( $saved ), 'saved' => false ), 200 );
        } else {
            array_unshift( $saved, $post_id );
            // Limit to 100 saved posts max to avoid usermeta bloat
            $saved = array_slice( $saved, 0, 100 );
            update_user_meta( $user_id, 'as_saved_posts', $saved );
            update_post_meta( $post_id, 'as_saved_timestamp', time() );
            Recommendation_Engine::get_instance()->invalidate_user_cache( $user_id );
            return new \WP_REST_Response( array( 'action' => 'saved', 'count' => count( $saved ), 'saved' => true ), 200 );
        }
    }

    /**
     * REST Handler: Update Reading Progress
     */
    public function rest_update_reading_progress( \WP_REST_Request $request ) {
        $user_id = get_current_user_id();
        $params = $request->get_json_params() ?: $request->get_params();
        $post_id = isset( $params['post_id'] ) ? intval( $params['post_id'] ) : 0;
        $progress = isset( $params['progress'] ) ? min( 100, max( 0, floatval( $params['progress'] ) ) ) : 0;

        if ( ! $post_id || ! get_post( $post_id ) ) {
            return new \WP_REST_Response( array( 'error' => __( 'Invalid post ID.', 'ascendance-core' ) ), 400 );
        }

        $this->track_reading_history( $user_id, $post_id, $progress );

        return new \WP_REST_Response( array( 'ok' => true, 'post_id' => $post_id, 'progress' => $progress ), 200 );
    }

    /**
     * REST Handler: Update Preferences
     */
    public function rest_update_preferences( \WP_REST_Request $request ) {
        $user_id = get_current_user_id();
        $params = $request->get_json_params() ?: $request->get_params();

        $topics = isset( $params['topics'] ) ? array_map( 'intval', (array) $params['topics'] ) : array();
        $regions = isset( $params['regions'] ) ? array_map( 'intval', (array) $params['regions'] ) : array();

        update_user_meta( $user_id, 'preferred_topics', $topics );
        update_user_meta( $user_id, 'preferred_regions', $regions );
        Recommendation_Engine::get_instance()->invalidate_user_cache( $user_id );

        return new \WP_REST_Response( array( 'ok' => true, 'message' => __( 'Preferences updated successfully.', 'ascendance-core' ) ), 200 );
    }

    /**
     * AJAX Handler: Save Dashboard Preferences
     */
    public function ajax_save_dashboard_preferences() {
        if ( isset( $_POST['security'] ) ) {
            check_ajax_referer( 'as_save_nonce', 'security' );
        }

        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'ascendance-core' ) ), 401 );
        }

        $topics = isset( $_POST['topics'] ) ? array_map( 'intval', (array) $_POST['topics'] ) : array();
        $regions = isset( $_POST['regions'] ) ? array_map( 'intval', (array) $_POST['regions'] ) : array();

        update_user_meta( $user_id, 'preferred_topics', $topics );
        update_user_meta( $user_id, 'preferred_regions', $regions );
        Recommendation_Engine::get_instance()->invalidate_user_cache( $user_id );

        wp_send_json_success( array( 'message' => __( 'Reading preferences saved successfully!', 'ascendance-core' ) ) );
    }

    /**
     * AJAX Handler: Record Reading Progress
     */
    public function ajax_record_reading_progress() {
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'ascendance-core' ) ), 401 );
        }

        $post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
        $progress = isset( $_POST['progress'] ) ? min( 100, max( 0, floatval( $_POST['progress'] ) ) ) : 0;

        if ( ! $post_id || ! get_post( $post_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid post.', 'ascendance-core' ) ), 400 );
        }

        // Phase 4B: gate progress recording — prevents tracking on content the user cannot access
        if ( class_exists( 'Ascendance\Core\Paywall' ) && ! Paywall::get_instance()->user_has_access( $post_id, $user_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Access denied.', 'ascendance-core' ) ), 403 );
        }

        $this->track_reading_history( $user_id, $post_id, $progress );
        wp_send_json_success( array( 'post_id' => $post_id, 'progress' => $progress ) );
    }

    /**
     * Track reading history and scroll progress for logged in user
     */
    public function track_reading_history( $user_id, $post_id, $progress = 0 ) {
        if ( ! $user_id || ! $post_id ) {
            return;
        }

        $history = (array) get_user_meta( $user_id, 'asc_reading_history', true );
        if ( ! is_array( $history ) ) {
            $history = array();
        }

        // Re-index by post_id to avoid duplicates
        $new_history = array();
        foreach ( $history as $item ) {
            if ( isset( $item['post_id'] ) && (int) $item['post_id'] !== (int) $post_id ) {
                $new_history[] = $item;
            }
        }

        // Prepend current post entry
        array_unshift( $new_history, array(
            'post_id'   => (int) $post_id,
            'timestamp' => time(),
            'progress'  => round( (float) $progress, 1 ),
            'type'      => get_post_type( $post_id ),
        ) );

        // Cap at 50 items to keep usermeta light
        $new_history = array_slice( $new_history, 0, 50 );

        update_user_meta( $user_id, 'asc_reading_history', $new_history );
        Recommendation_Engine::get_instance()->invalidate_user_cache( $user_id );
    }

    /**
     * Get user reading history
     */
    public function get_reading_history( $user_id, $limit = 10 ) {
        $history = (array) get_user_meta( $user_id, 'asc_reading_history', true );
        if ( empty( $history ) || ! is_array( $history ) ) {
            return array();
        }
        return array_slice( $history, 0, $limit );
    }

    /**
     * Get single most recent item needing completion (progress between 5% and 95%)
     */
    public function get_continue_reading( $user_id ) {
        $history = $this->get_reading_history( $user_id, 20 );
        foreach ( $history as $item ) {
            $prog = isset( $item['progress'] ) ? (float) $item['progress'] : 0;
            if ( $prog >= 5 && $prog < 95 && get_post( $item['post_id'] ) ) {
                return $item;
            }
        }
        return null;
    }

    /**
     * REST Handler: Get Ranked Personalized Recommendations
     */
    public function rest_get_recommendations( \WP_REST_Request $request ) {
        $user_id = get_current_user_id();
        $per_page = $request->get_param( 'per_page' ) ? intval( $request->get_param( 'per_page' ) ) : 6;
        $include_locked = (bool) $request->get_param( 'include_locked' );
        $post_type_param = $request->get_param( 'post_type' );

        $post_types = array( 'brief', 'dossier', 'update' );
        if ( $post_type_param ) {
            $post_types = array_intersect( (array) $post_type_param, array( 'brief', 'dossier', 'update' ) );
            if ( empty( $post_types ) ) {
                $post_types = array( 'brief', 'dossier', 'update' );
            }
        }

        $recommendations = Recommendation_Engine::get_instance()->get_ranked_recommendations(
            $user_id,
            $post_types,
            $per_page,
            array( 'include_locked' => $include_locked )
        );

        return new \WP_REST_Response( array(
            'ok'              => true,
            'count'           => count( $recommendations ),
            'recommendations' => $recommendations,
        ), 200 );
    }

    /**
     * Fetch user private notes
     */
    public function get_user_notes( $user_id, $post_id = 0 ) {
        if ( ! $user_id ) return array();
        $notes = get_user_meta( $user_id, 'asc_user_notes', true );
        if ( empty( $notes ) || ! is_array( $notes ) ) {
            $notes = array();
        }

        if ( $post_id ) {
            $post_id = (int) $post_id;
            if ( class_exists( 'Ascendance\Core\Paywall' ) && ! Paywall::get_instance()->user_has_access( $post_id, $user_id ) ) {
                return null;
            }
            return isset( $notes[$post_id] ) ? $notes[$post_id] : null;
        }

        // Phase 4B: full-collection path — add locked flag, suppress note body for gated posts
        $result = array();
        foreach ( $notes as $pid => $note_data ) {
            $pid = (int) $pid;
            $is_locked = class_exists( 'Ascendance\Core\Paywall' )
                && ! Paywall::get_instance()->user_has_access( $pid, $user_id );

            if ( $is_locked ) {
                // Return metadata only — do not expose note body for inaccessible posts
                $result[ $pid ] = array(
                    'note'       => '',
                    'updated_at' => isset( $note_data['updated_at'] ) ? $note_data['updated_at'] : null,
                    'title'      => isset( $note_data['title'] ) ? $note_data['title'] : '',
                    'post_type'  => isset( $note_data['post_type'] ) ? $note_data['post_type'] : '',
                    'permalink'  => isset( $note_data['permalink'] ) ? $note_data['permalink'] : '',
                    'locked'     => true,
                );
            } else {
                $note_data['locked'] = false;
                $result[ $pid ] = $note_data;
            }
        }
        return $result;
    }

    /**
     * Save private subscriber note for a post
     */
    public function save_user_note( $user_id, $post_id, $note_text ) {
        if ( ! $user_id || ! $post_id ) return false;
        $post_id = (int) $post_id;

        if ( class_exists( 'Ascendance\Core\Paywall' ) && ! Paywall::get_instance()->user_has_access( $post_id, $user_id ) ) {
            return false;
        }

        $clean_note = sanitize_textarea_field( substr( trim( $note_text ), 0, 2000 ) );

        $notes = $this->get_user_notes( $user_id );

        if ( empty( $clean_note ) ) {
            unset( $notes[$post_id] );
        } else {
            $notes[$post_id] = array(
                'note'       => $clean_note,
                'updated_at' => time(),
                'title'      => get_the_title( $post_id ),
                'post_type'  => get_post_type( $post_id ),
                'permalink'  => get_permalink( $post_id ),
            );
        }

        // Cap at 50 notes max
        if ( count( $notes ) > 50 ) {
            $notes = array_slice( $notes, -50, 50, true );
        }

        update_user_meta( $user_id, 'asc_user_notes', $notes );
        return true;
    }

    /**
     * Delete private note for a post
     */
    public function delete_user_note( $user_id, $post_id ) {
        if ( ! $user_id || ! $post_id ) return false;
        $post_id = (int) $post_id;
        $notes = $this->get_user_notes( $user_id );
        if ( isset( $notes[$post_id] ) ) {
            unset( $notes[$post_id] );
            update_user_meta( $user_id, 'asc_user_notes', $notes );
        }
        return true;
    }

    /**
     * Get filtered and sorted saved posts for logged-in subscriber
     */
    public function get_user_saved_posts_filtered( $user_id, $args = array() ) {
        $saved_ids = (array) get_user_meta( $user_id, 'as_saved_posts', true );
        $saved_ids = array_values( array_filter( array_map( 'intval', $saved_ids ) ) );

        if ( empty( $saved_ids ) ) {
            return array( 'items' => array(), 'total' => 0, 'pages' => 0 );
        }

        $post_type = ! empty( $args['post_type'] ) && 'all' !== $args['post_type'] ? (array) $args['post_type'] : array( 'brief', 'dossier', 'update' );
        $topic_id  = ! empty( $args['topic'] ) ? (int) $args['topic'] : 0;
        $region_id = ! empty( $args['region'] ) ? (int) $args['region'] : 0;
        $orderby   = ! empty( $args['orderby'] ) ? $args['orderby'] : 'recently_saved';
        $page      = ! empty( $args['page'] ) ? max( 1, (int) $args['page'] ) : 1;
        $per_page  = ! empty( $args['per_page'] ) ? max( 1, (int) $args['per_page'] ) : 10;

        $query_args = array(
            'post__in'       => $saved_ids,
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        );

        $tax_query = array();
        if ( $topic_id ) {
            $tax_query[] = array( 'taxonomy' => 'topic', 'field' => 'term_id', 'terms' => $topic_id );
        }
        if ( $region_id ) {
            $tax_query[] = array( 'taxonomy' => 'region', 'field' => 'term_id', 'terms' => $region_id );
        }
        if ( ! empty( $tax_query ) ) {
            $query_args['tax_query'] = $tax_query;
        }

        $query = new \WP_Query( $query_args );
        $posts = $query->posts;

        $reading_history = (array) get_user_meta( $user_id, 'asc_reading_history', true );
        $progress_map = array();
        if ( is_array( $reading_history ) ) {
            foreach ( $reading_history as $rh ) {
                if ( ! empty( $rh['post_id'] ) ) {
                    $progress_map[(int) $rh['post_id']] = isset( $rh['progress'] ) ? round( (float) $rh['progress'] ) : 0;
                }
            }
        }

        $items = array();
        foreach ( $posts as $p ) {
            $t_terms = wp_get_post_terms( $p->ID, 'topic', array( 'fields' => 'names' ) );
            $r_terms = wp_get_post_terms( $p->ID, 'region', array( 'fields' => 'names' ) );

            // Phase 4B: add locked flag — downgraded users retain saved list but see lock indicator
            $is_locked = class_exists( 'Ascendance\Core\Paywall' )
                && ! Paywall::get_instance()->user_has_access( $p->ID, $user_id );

            $items[] = array(
                'post_id'         => $p->ID,
                'title'           => get_the_title( $p ),
                'permalink'       => get_permalink( $p ),
                'post_type'       => get_post_type( $p ),
                'type_label'      => get_post_type_labels( get_post_type_object( $p->post_type ) )->singular_name ?? ucfirst( $p->post_type ),
                'topic_str'       => ( ! is_wp_error( $t_terms ) && ! empty( $t_terms ) ) ? implode( ', ', $t_terms ) : 'General',
                'region_str'      => ( ! is_wp_error( $r_terms ) && ! empty( $r_terms ) ) ? implode( ', ', $r_terms ) : 'Central Africa',
                'date_label'      => get_the_date( 'j M Y', $p ),
                'saved_timestamp' => (int) get_post_meta( $p->ID, 'as_saved_timestamp', true ) ?: get_post_time( 'U', true, $p->ID ),
                'progress'        => isset( $progress_map[$p->ID] ) ? $progress_map[$p->ID] : 0,
                'post_date_ts'    => get_post_time( 'U', true, $p->ID ),
                'locked'          => $is_locked, // true = user no longer has access (tier downgrade / entitlement revoked)
            );
        }

        // Sorting
        usort( $items, function( $a, $b ) use ( $orderby ) {
            if ( 'oldest_saved' === $orderby ) {
                return $a['saved_timestamp'] - $b['saved_timestamp'];
            }
            if ( 'recently_published' === $orderby ) {
                return $b['post_date_ts'] - $a['post_date_ts'];
            }
            if ( 'oldest_published' === $orderby ) {
                return $a['post_date_ts'] - $b['post_date_ts'];
            }
            return $b['saved_timestamp'] - $a['saved_timestamp']; // default: recently_saved
        } );

        $total = count( $items );
        $pages = ceil( $total / $per_page );
        $offset = ( $page - 1 ) * $per_page;
        $paged_items = array_slice( $items, $offset, $per_page );

        return array(
            'items' => $paged_items,
            'total' => $total,
            'pages' => $pages,
            'page'  => $page,
        );
    }

    /**
     * REST Handler: Get Saved Posts Filtered
     */
    public function rest_get_saved_posts( \WP_REST_Request $request ) {
        $user_id = get_current_user_id();
        $args = array(
            'post_type' => $request->get_param( 'post_type' ),
            'topic'     => $request->get_param( 'topic' ),
            'region'    => $request->get_param( 'region' ),
            'orderby'   => $request->get_param( 'orderby' ),
            'page'      => $request->get_param( 'page' ),
            'per_page'  => $request->get_param( 'per_page' ),
        );
        $data = $this->get_user_saved_posts_filtered( $user_id, $args );
        return new \WP_REST_Response( array( 'ok' => true, 'data' => $data ), 200 );
    }

    /**
     * REST Handler: Get Notes
     */
    public function rest_get_notes( \WP_REST_Request $request ) {
        $user_id = get_current_user_id();
        $post_id = $request->get_param( 'post_id' ) ? (int) $request->get_param( 'post_id' ) : 0;
        $notes = $this->get_user_notes( $user_id, $post_id );
        return new \WP_REST_Response( array( 'ok' => true, 'notes' => $notes ), 200 );
    }

    /**
     * REST Handler: Save Note
     */
    public function rest_save_note( \WP_REST_Request $request ) {
        $user_id = get_current_user_id();
        $params = $request->get_json_params() ?: $request->get_params();
        $post_id = isset( $params['post_id'] ) ? (int) $params['post_id'] : 0;
        $note = isset( $params['note'] ) ? (string) $params['note'] : '';

        if ( ! $post_id || ! get_post( $post_id ) ) {
            return new \WP_REST_Response( array( 'error' => __( 'Invalid post ID.', 'ascendance-core' ) ), 400 );
        }

        $this->save_user_note( $user_id, $post_id, $note );
        return new \WP_REST_Response( array( 'ok' => true, 'message' => __( 'Note saved successfully.', 'ascendance-core' ) ), 200 );
    }

    /**
     * REST Handler: Delete Note
     */
    public function rest_delete_note( \WP_REST_Request $request ) {
        $user_id = get_current_user_id();
        $params = $request->get_json_params() ?: $request->get_params();
        $post_id = isset( $params['post_id'] ) ? (int) $params['post_id'] : 0;

        if ( ! $post_id ) {
            return new \WP_REST_Response( array( 'error' => __( 'Invalid post ID.', 'ascendance-core' ) ), 400 );
        }

        $this->delete_user_note( $user_id, $post_id );
        return new \WP_REST_Response( array( 'ok' => true, 'message' => __( 'Note deleted.', 'ascendance-core' ) ), 200 );
    }

    /**
     * AJAX Handler: Save User Note
     */
    public function ajax_save_user_note() {
        if ( isset( $_POST['security'] ) ) {
            check_ajax_referer( 'as_save_nonce', 'security' );
        }

        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'ascendance-core' ) ), 401 );
        }

        $post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;
        $note = isset( $_POST['note'] ) ? (string) $_POST['note'] : '';

        if ( ! $post_id || ! get_post( $post_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'ascendance-core' ) ), 400 );
        }

        $this->save_user_note( $user_id, $post_id, $note );
        wp_send_json_success( array( 'message' => __( 'Private note saved!', 'ascendance-core' ), 'post_id' => $post_id ) );
    }

    /**
     * AJAX Handler: Delete User Note
     */
    public function ajax_delete_user_note() {
        if ( isset( $_POST['security'] ) ) {
            check_ajax_referer( 'as_save_nonce', 'security' );
        }

        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'ascendance-core' ) ), 401 );
        }

        $post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;
        if ( ! $post_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'ascendance-core' ) ), 400 );
        }

        $this->delete_user_note( $user_id, $post_id );
        wp_send_json_success( array( 'message' => __( 'Note removed.', 'ascendance-core' ), 'post_id' => $post_id ) );
    }

    /**
     * AJAX Handler: Filter Saved Posts
     */
    public function ajax_filter_saved_posts() {
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'ascendance-core' ) ), 401 );
        }

        $args = array(
            'post_type' => isset( $_POST['post_type'] ) ? sanitize_text_field( $_POST['post_type'] ) : 'all',
            'topic'     => isset( $_POST['topic'] ) ? (int) $_POST['topic'] : 0,
            'region'    => isset( $_POST['region'] ) ? (int) $_POST['region'] : 0,
            'orderby'   => isset( $_POST['orderby'] ) ? sanitize_text_field( $_POST['orderby'] ) : 'recently_saved',
            'page'      => isset( $_POST['page'] ) ? (int) $_POST['page'] : 1,
            'per_page'  => 10,
        );

        $data = $this->get_user_saved_posts_filtered( $user_id, $args );
        wp_send_json_success( $data );
    }

    /**
     * Remove single item from subscriber reading history
     */
    public function remove_history_item( $user_id, $post_id ) {
        if ( ! $user_id || ! $post_id ) return false;
        $history = (array) get_user_meta( $user_id, 'asc_reading_history', true );
        $new_history = array();
        foreach ( $history as $item ) {
            if ( isset( $item['post_id'] ) && (int) $item['post_id'] !== (int) $post_id ) {
                $new_history[] = $item;
            }
        }
        update_user_meta( $user_id, 'asc_reading_history', $new_history );
        Recommendation_Engine::get_instance()->invalidate_user_cache( $user_id );
        return count( $new_history );
    }

    /**
     * Clear all reading history for subscriber
     */
    public function clear_reading_history( $user_id ) {
        if ( ! $user_id ) return false;
        update_user_meta( $user_id, 'asc_reading_history', array() );
        Recommendation_Engine::get_instance()->invalidate_user_cache( $user_id );
        return true;
    }

    /**
     * Get filtered subscriber reading history
     */
    public function get_user_history_filtered( $user_id, $args = array() ) {
        $history = (array) get_user_meta( $user_id, 'asc_reading_history', true );
        if ( empty( $history ) || ! is_array( $history ) ) {
            return array( 'items' => array(), 'total' => 0 );
        }

        $post_type = ! empty( $args['post_type'] ) && 'all' !== $args['post_type'] ? (array) $args['post_type'] : array( 'brief', 'dossier', 'update' );
        $topic_id  = ! empty( $args['topic'] ) ? (int) $args['topic'] : 0;
        $region_id = ! empty( $args['region'] ) ? (int) $args['region'] : 0;

        $items = array();
        foreach ( $history as $h ) {
            $p_id = isset( $h['post_id'] ) ? (int) $h['post_id'] : 0;
            if ( ! $p_id || ! get_post( $p_id ) ) continue;

            $p_type = get_post_type( $p_id );
            if ( ! in_array( $p_type, $post_type, true ) ) continue;

            if ( $topic_id && ! has_term( $topic_id, 'topic', $p_id ) ) continue;
            if ( $region_id && ! has_term( $region_id, 'region', $p_id ) ) continue;

            $prog = isset( $h['progress'] ) ? round( (float) $h['progress'] ) : 0;

            // Phase 4B: add locked flag — preserves history entry but signals current access state
            $is_locked = class_exists( 'Ascendance\Core\Paywall' )
                && ! Paywall::get_instance()->user_has_access( $p_id, $user_id );

            $items[] = array(
                'post_id'      => $p_id,
                'title'        => get_the_title( $p_id ),
                'permalink'    => get_permalink( $p_id ),
                'post_type'    => $p_type,
                'progress'     => $prog,
                'is_completed' => ( $prog >= 95 ),
                'last_read'    => isset( $h['timestamp'] ) ? human_time_diff( $h['timestamp'] ) . ' ago' : 'Recently',
                'locked'       => $is_locked, // true = user no longer has access
            );
        }

        return array(
            'items' => $items,
            'total' => count( $items ),
        );
    }

    /**
     * REST Handler: Get Reading History
     */
    public function rest_get_history( \WP_REST_Request $request ) {
        $user_id = get_current_user_id();
        $args = array(
            'post_type' => $request->get_param( 'post_type' ),
            'topic'     => $request->get_param( 'topic' ),
            'region'    => $request->get_param( 'region' ),
        );
        $data = $this->get_user_history_filtered( $user_id, $args );
        return new \WP_REST_Response( array( 'ok' => true, 'data' => $data ), 200 );
    }

    /**
     * REST Handler: Get Subscriber Category Add-ons & Entitlement Status
     */
    public function rest_get_category_addons( \WP_REST_Request $request ) {
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            return new \WP_REST_Response( array( 'error' => __( 'Authentication required.', 'ascendance-core' ) ), 401 );
        }

        $user_obj   = get_userdata( $user_id );
        $meta_tier  = get_user_meta( $user_id, 'ascendance_membership_tier', true );
        $is_enterprise = ( 'enterprise' === strtolower( (string) $meta_tier ) )
            || ( $user_obj && ( in_array( 'administrator', (array) $user_obj->roles, true ) || in_array( 'ascendance_enterprise', (array) $user_obj->roles, true ) ) );

        $cust_id = get_user_meta( $user_id, 'ascendance_stripe_customer_id', true );
        if ( empty( $cust_id ) ) {
            $cust_id = get_user_meta( $user_id, 'pmpro_stripe_customerid', true );
        }
        $has_stripe_customer = ! empty( $cust_id );

        $terms = get_terms( array(
            'taxonomy'   => 'topic',
            'hide_empty' => false,
            'meta_query' => array(
                array(
                    'key'     => 'is_paid_addon',
                    'value'   => 1,
                    'compare' => '=',
                ),
            ),
        ) );

        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return new \WP_REST_Response( array(
                'ok'                  => true,
                'total'               => 0,
                'is_enterprise'       => $is_enterprise,
                'has_stripe_customer' => $has_stripe_customer,
                'addons'              => array(),
            ), 200 );
        }

        $paywall = class_exists( 'Ascendance\Core\Paywall' ) ? Paywall::get_instance() : null;
        $raw_entitlements = (array) get_user_meta( $user_id, 'asc_category_entitlements', true );
        $date_format      = get_option( 'date_format' ) ?: 'F j, Y';

        $addons = array();
        foreach ( $terms as $term ) {
            $status     = get_term_meta( $term->term_id, 'addon_status', true ) ?: 'active';
            $icon       = get_term_meta( $term->term_id, 'addon_icon', true ) ?: 'dashicons-category';
            $desc       = get_term_meta( $term->term_id, 'addon_description', true ) ?: $term->description;
            $amount     = get_term_meta( $term->term_id, 'addon_price_amount', true ) ?: '49.00';
            $currency   = get_term_meta( $term->term_id, 'addon_currency', true ) ?: 'USD';

            $is_entitled        = false;
            $entitlement_status = 'none';
            $entitlement_source = 'none';
            $granted_at         = null;
            $expires_at         = null;
            $expires_formatted  = null;

            if ( $is_enterprise ) {
                $is_entitled        = true;
                $entitlement_status = 'active';
                $entitlement_source = 'enterprise';
            } elseif ( isset( $raw_entitlements[ $term->slug ] ) ) {
                $item = $raw_entitlements[ $term->slug ];
                if ( is_array( $item ) ) {
                    $item_status = isset( $item['status'] ) ? $item['status'] : 'active';
                    $expires     = isset( $item['expires_at'] ) ? $item['expires_at'] : null;
                    $granted     = isset( $item['granted_at'] ) ? $item['granted_at'] : null;

                    if ( $expires && strtotime( $expires ) < time() ) {
                        $is_entitled        = false;
                        $entitlement_status = 'expired';
                    } elseif ( 'active' !== $item_status ) {
                        $is_entitled        = ( 'canceling' === $item_status ); // canceling still has access until period end
                        $entitlement_status = $item_status; // revoked, canceling, payment_issue
                    } else {
                        $is_entitled        = true;
                        $entitlement_status = 'active';
                    }

                    $granted_at = $granted;
                    $expires_at = $expires;

                    $sub_id = get_user_meta( $user_id, 'asc_cat_sub_' . $term->slug, true );
                    $entitlement_source = ! empty( $sub_id ) ? 'stripe' : 'admin';
                } else {
                    $is_entitled        = true;
                    $entitlement_status = 'active';
                    $entitlement_source = 'admin';
                }
            }

            if ( $expires_at ) {
                $expires_formatted = date_i18n( $date_format, strtotime( $expires_at ) );
            }

            $addons[] = array(
                'term_id'            => $term->term_id,
                'name'               => $term->name,
                'slug'               => $term->slug,
                'status'             => $status, // topic status: active, inactive, draft
                'icon'               => $icon,
                'description'        => $desc,
                'price_amount'       => (float) $amount,
                'currency'           => $currency,
                'is_entitled'        => $is_entitled,
                'entitlement_status' => $entitlement_status,
                'entitlement_source' => $entitlement_source,
                'granted_at'         => $granted_at,
                'expires_at'         => $expires_at,
                'expires_formatted'  => $expires_formatted,
            );
        }

        return new \WP_REST_Response( array(
            'ok'                  => true,
            'total'               => count( $addons ),
            'is_enterprise'       => $is_enterprise,
            'has_stripe_customer' => $has_stripe_customer,
            'addons'              => $addons,
        ), 200 );
    }

    /**
     * REST Handler: Remove Single History Item
     */
    public function rest_remove_history_item( \WP_REST_Request $request ) {
        $user_id = get_current_user_id();
        $params = $request->get_json_params() ?: $request->get_params();
        $post_id = isset( $params['post_id'] ) ? (int) $params['post_id'] : 0;

        if ( ! $post_id ) {
            return new \WP_REST_Response( array( 'error' => __( 'Invalid post ID.', 'ascendance-core' ) ), 400 );
        }

        $remaining_count = $this->remove_history_item( $user_id, $post_id );
        return new \WP_REST_Response( array( 'ok' => true, 'count' => $remaining_count ), 200 );
    }

    /**
     * REST Handler: Clear Reading History
     */
    public function rest_clear_reading_history( \WP_REST_Request $request ) {
        $user_id = get_current_user_id();
        $this->clear_reading_history( $user_id );
        return new \WP_REST_Response( array( 'ok' => true, 'message' => __( 'Reading history cleared.', 'ascendance-core' ) ), 200 );
    }

    /**
     * AJAX Handler: Remove Single History Item
     */
    public function ajax_remove_history_item() {
        if ( isset( $_POST['security'] ) ) {
            check_ajax_referer( 'as_save_nonce', 'security' );
        }

        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'ascendance-core' ) ), 401 );
        }

        $post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;
        if ( ! $post_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'ascendance-core' ) ), 400 );
        }

        $count = $this->remove_history_item( $user_id, $post_id );
        wp_send_json_success( array( 'post_id' => $post_id, 'count' => $count ) );
    }

    /**
     * AJAX Handler: Clear Reading History
     */
    public function ajax_clear_reading_history() {
        if ( isset( $_POST['security'] ) ) {
            check_ajax_referer( 'as_save_nonce', 'security' );
        }

        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'ascendance-core' ) ), 401 );
        }

        $this->clear_reading_history( $user_id );
        wp_send_json_success( array( 'message' => __( 'Reading history cleared.', 'ascendance-core' ) ) );
    }

    /**
     * AJAX Handler: Filter Reading History
     */
    public function ajax_filter_reading_history() {
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'ascendance-core' ) ), 401 );
        }

        $args = array(
            'post_type' => isset( $_POST['post_type'] ) ? sanitize_text_field( $_POST['post_type'] ) : 'all',
            'topic'     => isset( $_POST['topic'] ) ? (int) $_POST['topic'] : 0,
            'region'    => isset( $_POST['region'] ) ? (int) $_POST['region'] : 0,
        );

        $data = $this->get_user_history_filtered( $user_id, $args );
        wp_send_json_success( $data );
    }

    /**
     * Generate short-lived signed token for PDF export
     */
    public function generate_pdf_token( $user_id, $post_id ) {
        if ( ! $user_id || ! $post_id ) return false;
        $post_id = (int) $post_id;
        $post = get_post( $post_id );

        if ( ! $post || ! in_array( $post->post_type, array( 'brief', 'dossier' ), true ) ) {
            return false;
        }

        if ( class_exists( 'Ascendance\Core\Paywall' ) && ! Paywall::get_instance()->user_has_access( $post_id, $user_id ) ) {
            return false;
        }

        $token = bin2hex( random_bytes( 16 ) );
        $payload = array(
            'user_id' => $user_id,
            'post_id' => $post_id,
            'expires' => time() + 300, // 5 minute TTL
            'used'    => false,
        );

        set_transient( 'asc_pdf_tok_' . $token, $payload, 300 );

        $download_url = add_query_arg( array(
            'asc_pdf_download' => 1,
            'token'            => $token,
        ), home_url( '/' ) );

        return array(
            'token'        => $token,
            'download_url' => $download_url,
            'expires_in'   => 300,
        );
    }

    /**
     * REST Handler: Generate PDF Token
     */
    public function rest_generate_pdf_token( \WP_REST_Request $request ) {
        $user_id = get_current_user_id();
        $params = $request->get_json_params() ?: $request->get_params();
        $post_id = isset( $params['post_id'] ) ? (int) $params['post_id'] : 0;

        if ( ! $post_id ) {
            return new \WP_REST_Response( array( 'error' => __( 'Invalid post ID.', 'ascendance-core' ) ), 400 );
        }

        $token_data = $this->generate_pdf_token( $user_id, $post_id );
        if ( ! $token_data ) {
            return new \WP_REST_Response( array( 'error' => __( 'Forbidden: Subscription clearance required to export PDF.', 'ascendance-core' ) ), 403 );
        }

        return new \WP_REST_Response( array( 'ok' => true, 'data' => $token_data ), 200 );
    }

    /**
     * REST Handler: Download PDF via token
     */
    public function rest_download_pdf( \WP_REST_Request $request ) {
        $token = $request->get_param( 'token' );
        if ( empty( $token ) ) {
            return new \WP_REST_Response( array( 'error' => __( 'Token required.', 'ascendance-core' ) ), 400 );
        }

        $payload = get_transient( 'asc_pdf_tok_' . $token );
        if ( ! $payload || empty( $payload['user_id'] ) || empty( $payload['post_id'] ) ) {
            return new \WP_REST_Response( array( 'error' => __( 'Invalid or expired download token.', 'ascendance-core' ) ), 403 );
        }

        // Verify token user match
        $current_user = get_current_user_id();
        if ( $current_user && (int) $current_user !== (int) $payload['user_id'] ) {
            return new \WP_REST_Response( array( 'error' => __( 'Forbidden: Token user mismatch.', 'ascendance-core' ) ), 403 );
        }

        // Consume single-use token
        delete_transient( 'asc_pdf_tok_' . $token );

        // Stream PDF
        if ( class_exists( 'Ascendance\Core\PDF_Exporter' ) ) {
            PDF_Exporter::get_instance()->generate_and_stream( $payload['post_id'], $payload['user_id'] );
        }
        exit;
    }

    /**
     * Query Handler: Download PDF via GET /?asc_pdf_download=1&token={token}
     */
    public function handle_pdf_download_request() {
        if ( empty( $_GET['asc_pdf_download'] ) || empty( $_GET['token'] ) ) {
            return;
        }

        $token = sanitize_text_field( $_GET['token'] );
        $payload = get_transient( 'asc_pdf_tok_' . $token );

        if ( ! $payload || empty( $payload['user_id'] ) || empty( $payload['post_id'] ) ) {
            wp_die( 'Forbidden: Invalid or expired download token.', 'PDF Export Error', array( 'response' => 403 ) );
        }

        $current_user = get_current_user_id();
        if ( $current_user && (int) $current_user !== (int) $payload['user_id'] ) {
            wp_die( 'Forbidden: Cross-user token sharing is unauthorized.', 'PDF Export Error', array( 'response' => 403 ) );
        }

        // Invalidate token
        delete_transient( 'asc_pdf_tok_' . $token );

        if ( class_exists( 'Ascendance\Core\PDF_Exporter' ) ) {
            PDF_Exporter::get_instance()->generate_and_stream( $payload['post_id'], $payload['user_id'] );
        }
        exit;
    }

    /**
     * AJAX Handler: Generate PDF Token
     */
    public function ajax_generate_pdf_token() {
        if ( isset( $_POST['security'] ) ) {
            check_ajax_referer( 'as_save_nonce', 'security' );
        }

        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized. Please sign in.', 'ascendance-core' ) ), 401 );
        }

        $post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;
        if ( ! $post_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'ascendance-core' ) ), 400 );
        }

        $token_data = $this->generate_pdf_token( $user_id, $post_id );
        if ( ! $token_data ) {
            wp_send_json_error( array( 'message' => __( 'Forbidden: Professional or Enterprise subscription clearance required.', 'ascendance-core' ) ), 403 );
        }

        wp_send_json_success( $token_data );
    }

    // =========================================================================
    // Phase 4A — Category Entitlement Admin Metabox
    // =========================================================================

    /**
     * Render the Category Add-on Entitlements metabox on the admin user-edit screen.
     * Only visible to administrators. Shows all active paid-add-on topics with
     * checkboxes to grant / revoke entitlement for this specific subscriber.
     *
     * @param \WP_User $user
     */
    public function render_category_entitlements_metabox( \WP_User $user ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Fetch all active paid add-on topics
        $paid_topics = get_terms( array(
            'taxonomy'   => 'topic',
            'hide_empty' => false,
            'meta_query' => array(
                array(
                    'key'   => 'is_paid_addon',
                    'value' => '1',
                ),
            ),
        ) );

        if ( is_wp_error( $paid_topics ) || empty( $paid_topics ) ) {
            return;
        }

        $paywall        = Paywall::get_instance();
        $entitlements   = $paywall->get_user_category_entitlements( $user->ID );
        $nonce_action   = 'asc_cat_entitlement_' . $user->ID;
        wp_nonce_field( $nonce_action, 'asc_cat_entitlement_nonce' );
        ?>
        <h3 style="margin-top:28px; color:var(--color-white);">&#x1F511; <?php esc_html_e( 'Paid Category Add-on Entitlements', 'ascendance-core' ); ?></h3>
        <p style="color:var(--text-muted); margin-bottom:12px; font-size:13px;">
            <?php esc_html_e( 'Grant or revoke per-category access for this subscriber. Enterprise-tier users receive automatic access to all categories.', 'ascendance-core' ); ?>
        </p>
        <table class="form-table">
            <tr>
                <th><label><?php esc_html_e( 'Category Access', 'ascendance-core' ); ?></label></th>
                <td>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; max-width:600px;">
                    <?php foreach ( $paid_topics as $topic ) :
                        $slug      = $topic->slug;
                        $status    = get_term_meta( $topic->term_id, 'addon_status', true ) ?: 'active';
                        $icon      = get_term_meta( $topic->term_id, 'addon_icon', true ) ?: '🏷';
                        $granted   = isset( $entitlements[ $slug ] );
                        $inactive  = 'active' !== $status;
                        ?>
                        <label style="display:inline-flex; align-items:center; font-weight:normal; <?php echo $inactive ? 'opacity:0.55;' : ''; ?>">
                            <input
                                type="checkbox"
                                name="asc_cat_entitlements[]" 
                                value="<?php echo esc_attr( $slug ); ?>"
                                <?php checked( $granted ); ?>
                                <?php disabled( $inactive ); ?>
                                style="margin-right:8px;"
                            />
                            <?php echo esc_html( $icon . ' ' . $topic->name ); ?>
                            <?php if ( $inactive ) : ?>
                                <span style="font-size:10px; color:var(--text-muted); margin-left:6px;">(<?php esc_html_e( 'inactive', 'ascendance-core' ); ?>)</span>
                            <?php endif; ?>
                        </label>
                    <?php endforeach; ?>
                    </div>
                    <?php if ( in_array( 'ascendance_enterprise', (array) $user->roles, true ) || in_array( 'administrator', (array) $user->roles, true ) ) : ?>
                        <p style="margin-top:10px; font-size:12px; color:var(--text-muted);"><em><?php esc_html_e( '&#9432; This user holds an Enterprise or Administrator role and receives automatic category-level override.', 'ascendance-core' ); ?></em></p>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Persist category entitlement selections from the admin user-edit metabox.
     *
     * @param int $user_id
     */
    public function save_category_entitlements( $user_id ) {
        if (
            ! current_user_can( 'manage_options' ) ||
            ! isset( $_POST['asc_cat_entitlement_nonce'] ) ||
            ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['asc_cat_entitlement_nonce'] ) ), 'asc_cat_entitlement_' . $user_id )
        ) {
            return;
        }

        $paywall        = Paywall::get_instance();
        $posted_slugs   = isset( $_POST['asc_cat_entitlements'] ) ? array_map( 'sanitize_key', (array) $_POST['asc_cat_entitlements'] ) : array();

        // Collect all active paid add-on slugs for diffing
        $all_paid_topics = get_terms( array(
            'taxonomy'   => 'topic',
            'hide_empty' => false,
            'meta_query' => array(
                array(
                    'key'   => 'is_paid_addon',
                    'value' => '1',
                ),
            ),
        ) );

        if ( is_wp_error( $all_paid_topics ) ) {
            return;
        }

        foreach ( $all_paid_topics as $topic ) {
            $slug = $topic->slug;
            if ( in_array( $slug, $posted_slugs, true ) ) {
                $paywall->grant_user_category_entitlement( $user_id, $slug );
            } else {
                $paywall->revoke_user_category_entitlement( $user_id, $slug );
            }
        }
    }
}
