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
    }

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
        
        // Fetch membership details
        $level_name = __( 'Free Guest', 'ascendance-core' );
        $billing_info = __( 'Free Access', 'ascendance-core' );
        $pmpro_active = function_exists( 'pmpro_getMembershipLevelForUser' );

        if ( $pmpro_active ) {
            $user_level = pmpro_getMembershipLevelForUser( $user_id );
            if ( ! empty( $user_level ) ) {
                $level_name = esc_html( $user_level->name );
                $billing_info = sprintf( 
                    /* translators: 1: Billing amount, 2: Billing cycle */
                    __( '$%1$s / %2$s', 'ascendance-core' ),
                    number_format( $user_level->initial_payment, 0 ),
                    $user_level->cycle_number == 1 && $user_level->cycle_period == 'Month' ? 'month' : 'year'
                );
            }
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
                        <span>Role: Subscriber</span>
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
                            <?php if ( function_exists( 'pmpro_url' ) ) : ?>
                                <a href="<?php echo esc_url( add_query_arg( 'portal', '1', pmpro_url( 'account' ) ) ); ?>" class="flex flex-col items-center justify-center p-3 text-center rounded-sm border border-brand-divider-light dark:border-brand-divider-dark bg-cream/20 dark:bg-navy-deep/20 hover:border-brand-red dark:hover:border-brand-red-light transition-colors group">
                                    <i class="fa-regular fa-id-card text-brand-red text-base mb-1.5 transition-transform group-hover:scale-110"></i>
                                    <span class="text-[10px] font-sans font-bold text-brand-text-primary dark:text-cream leading-tight"><?php esc_html_e( 'Manage Tier', 'ascendance-core' ); ?></span>
                                </a>
                                <a href="<?php echo esc_url( pmpro_url( 'billing' ) ); ?>" class="flex flex-col items-center justify-center p-3 text-center rounded-sm border border-brand-divider-light dark:border-brand-divider-dark bg-cream/20 dark:bg-navy-deep/20 hover:border-brand-red dark:hover:border-brand-red-light transition-colors group">
                                    <i class="fa-regular fa-credit-card text-brand-red text-base mb-1.5 transition-transform group-hover:scale-110"></i>
                                    <span class="text-[10px] font-sans font-bold text-brand-text-primary dark:text-cream leading-tight"><?php esc_html_e( 'Billing Info', 'ascendance-core' ); ?></span>
                                </a>
                            <?php endif; ?>
                            <a href="<?php echo esc_url( get_edit_user_link() ); ?>" class="flex flex-col items-center justify-center p-3 text-center rounded-sm border border-brand-divider-light dark:border-brand-divider-dark bg-cream/20 dark:bg-navy-deep/20 hover:border-brand-red dark:hover:border-brand-red-light transition-colors group <?php echo !function_exists( 'pmpro_url' ) ? 'col-span-2' : ''; ?>">
                                <i class="fa-regular fa-user text-brand-red text-base mb-1.5 transition-transform group-hover:scale-110"></i>
                                <span class="text-[10px] font-sans font-bold text-brand-text-primary dark:text-cream leading-tight"><?php esc_html_e( 'Preferences', 'ascendance-core' ); ?></span>
                            </a>
                            <a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="flex flex-col items-center justify-center p-3 text-center rounded-sm border border-brand-divider-light dark:border-brand-divider-dark bg-cream/20 dark:bg-navy-deep/20 hover:border-brand-red dark:hover:border-brand-red-light transition-colors group <?php echo !function_exists( 'pmpro_url' ) ? 'col-span-2' : ''; ?>">
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
        $preferred_topics = get_user_meta( $user_id, 'preferred_topics', true );
        if ( empty( $preferred_topics ) ) {
            $preferred_topics = get_user_meta( $user_id, 'preferred_industries', true );
        }
        $preferred_regions = get_user_meta( $user_id, 'preferred_regions', true );

        $tax_query = array( 'relation' => 'OR' );

        if ( ! empty( $preferred_topics ) ) {
            $tax_query[] = array(
                'taxonomy' => 'topic',
                'field'    => 'term_id',
                'terms'    => $preferred_topics,
            );
        }

        if ( ! empty( $preferred_regions ) ) {
            $tax_query[] = array(
                'taxonomy' => 'region',
                'field'    => 'term_id',
                'terms'    => $preferred_regions,
            );
        }

        $args = array(
            'post_type'      => array( 'brief', 'dossier' ),
            'posts_per_page' => 3,
            'post_status'    => 'publish',
        );

        if ( count( $tax_query ) > 1 ) {
            $args['tax_query'] = $tax_query;
        }

        $query = new \WP_Query( $args );

        if ( $query->have_posts() ) {
            echo '<div class="flex flex-col gap-3">';
            while ( $query->have_posts() ) {
                $query->the_post();
                ?>
                <div class="flex items-start justify-between gap-3 text-xs border-b border-brand-divider-light dark:border-brand-divider-dark/40 pb-2 last:border-b-0 last:pb-0">
                    <div class="flex flex-col gap-1">
                        <a href="<?php the_permalink(); ?>" class="font-sans font-bold text-brand-text-primary dark:text-cream hover:text-brand-red dark:hover:text-brand-red-light transition-colors leading-snug"><?php the_title(); ?></a>
                        <span class="text-[9px] font-mono text-brand-text-muted dark:text-cream/50 uppercase"><?php echo esc_html( get_post_type() ); ?></span>
                    </div>
                    <i class="fa-solid fa-angle-right text-[10px] text-brand-red/60 mt-1"></i>
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
        // Stripe Checkout URL or PMPro level checkout URLs
        $checkout_url = '#';
        if ( function_exists( 'pmpro_url' ) ) {
            $checkout_url = pmpro_url( 'checkout' );
        }

        ob_start();
        ?>
        <div class="pricing-table-matrix">
            <!-- Plan 1: Essential -->
            <div class="card pricing-tier-card">
                <span class="pricing-tier-card-number"><?php esc_html_e( 'Tier 1', 'ascendance-core' ); ?></span>
                <h3><?php esc_html_e( 'Essential', 'ascendance-core' ); ?></h3>
                <div class="pricing-tier-card-price-row">
                    <span class="pricing-tier-card-price">$150</span>
                    <span class="pricing-tier-card-price-period">/ month</span>
                </div>
                <p class="pricing-tier-card-desc">
                    <?php esc_html_e( 'Full access to Intelligence Briefs, tracking updates, and primary industry research feeds.', 'ascendance-core' ); ?>
                </p>
                <a href="<?php echo esc_url( add_query_arg( 'level', '1', $checkout_url ) ); ?>" class="btn btn-secondary"><?php esc_html_e( 'Select Plan', 'ascendance-core' ); ?></a>
            </div>

            <!-- Plan 2: Professional (Recommended) -->
            <div class="card pricing-tier-card featured-pricing">
                <span class="paywall-badge" style="position: absolute; top: -14px; left: 50%; transform: translateX(-50%);"><?php esc_html_e( 'Recommended', 'ascendance-core' ); ?></span>
                <span class="pricing-tier-card-number" style="color: var(--color-red);"><?php esc_html_e( 'Tier 2', 'ascendance-core' ); ?></span>
                <h3><?php esc_html_e( 'Professional', 'ascendance-core' ); ?></h3>
                <div class="pricing-tier-card-price-row">
                    <span class="pricing-tier-card-price">$299</span>
                    <span class="pricing-tier-card-price-period">/ month</span>
                </div>
                <p class="pricing-tier-card-desc">
                    <?php esc_html_e( 'Unlock high-density Dossiers, downloads, stakeholder profiling, and cross-referenced historical indexes.', 'ascendance-core' ); ?>
                </p>
                <a href="<?php echo esc_url( add_query_arg( 'level', '2', $checkout_url ) ); ?>" class="btn btn-primary"><?php esc_html_e( 'Activate Professional', 'ascendance-core' ); ?></a>
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
                <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn btn-secondary"><?php esc_html_e( 'Contact Enterprise', 'ascendance-core' ); ?></a>
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
}
