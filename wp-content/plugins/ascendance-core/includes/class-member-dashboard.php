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
            return '<div style="text-align:center; padding:var(--space-50) 0;">
                <h3>' . esc_html__( 'Subscriber Area Restricted', 'ascendance-core' ) . '</h3>
                <p style="color:var(--text-secondary); margin-bottom:var(--space-30);">' . esc_html__( 'Please sign in or register to view your custom dashboard feed.', 'ascendance-core' ) . '</p>
                <a href="' . esc_url( wp_login_url( get_permalink() ) ) . '" class="btn btn-primary">' . esc_html__( 'Sign In to Account', 'ascendance-core' ) . '</a>
            </div>';
        }

        $user_id = get_current_user_id();
        $user_data = get_userdata( $user_id );
        
        // Fetch membership details
        $level_name = __( 'Free Guest', 'ascendance-core' );
        $billing_info = __( 'Free Access', 'ascendance-core' );
        $pmpro_active = function_exists( 'pmpro_get_membership_level_for_user' );

        if ( $pmpro_active ) {
            $user_level = pmpro_get_membership_level_for_user( $user_id );
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

        ob_start();
        ?>
        <div class="ascendance-dashboard">
            <!-- Dashboard Welcome Header -->
            <div style="background-color: var(--color-navy); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: var(--space-40); margin-bottom: var(--space-40); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: var(--space-20);">
                <div>
                    <span style="color: var(--color-red); text-transform: uppercase; font-family: var(--font-heading); font-weight: var(--weight-bold); font-size: var(--font-size-xs); letter-spacing: 1px;"><?php esc_html_e( 'Subscriber Portal', 'ascendance-core' ); ?></span>
                    <h2 style="margin-bottom: 0; margin-top: 4px;"><?php printf( esc_html__( 'Welcome, %s', 'ascendance-core' ), esc_html( $user_data->display_name ) ); ?></h2>
                </div>
                <div style="text-align: right;">
                    <span style="font-family: var(--font-heading); font-size: var(--font-size-xs); color: var(--text-muted); display: block;"><?php esc_html_e( 'ACTIVE TIER', 'ascendance-core' ); ?></span>
                    <span class="paywall-badge" style="margin-bottom: 0; margin-top: 4px;"><?php echo esc_html( $level_name ); ?></span>
                    <span style="display: block; font-size: var(--font-size-xs); color: var(--text-secondary); margin-top: 4px;"><?php echo esc_html( $billing_info ); ?></span>
                </div>
            </div>

            <!-- Dashboard Grid -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: var(--space-40);">
                <!-- Left: Feeds -->
                <div>
                    <!-- Section: Recent Intelligence Briefs -->
                    <h3 style="border-bottom: 1px solid var(--border-color); padding-bottom: 10px; margin-bottom: var(--space-20);"><i class="fa-solid fa-file-invoice" style="color: var(--color-red); margin-right: 10px;"></i><?php esc_html_e( 'Latest Intelligence Briefs', 'ascendance-core' ); ?></h3>
                    <div style="display: flex; flex-direction: column; gap: var(--space-20); margin-bottom: var(--space-40);">
                        <?php $this->render_dashboard_feed( 'brief', 3 ); ?>
                    </div>

                    <!-- Section: Latest Updates -->
                    <h3 style="border-bottom: 1px solid var(--border-color); padding-bottom: 10px; margin-bottom: var(--space-20);"><i class="fa-solid fa-clock-rotate-left" style="color: var(--color-red); margin-right: 10px;"></i><?php esc_html_e( 'Real-time Intelligence Updates', 'ascendance-core' ); ?></h3>
                    <div style="display: flex; flex-direction: column; gap: var(--space-20); margin-bottom: var(--space-40);">
                        <?php $this->render_dashboard_feed( 'update', 3 ); ?>
                    </div>

                    <!-- Section: Latest Dossiers -->
                    <h3 style="border-bottom: 1px solid var(--border-color); padding-bottom: 10px; margin-bottom: var(--space-20);"><i class="fa-solid fa-folder-open" style="color: var(--color-red); margin-right: 10px;"></i><?php esc_html_e( 'High-Density Dossiers', 'ascendance-core' ); ?></h3>
                    <div style="display: flex; flex-direction: column; gap: var(--space-20); margin-bottom: var(--space-40);">
                        <?php $this->render_dashboard_feed( 'dossier', 3 ); ?>
                    </div>
                </div>

                <!-- Right: Recommendations & Settings -->
                <div>
                    <!-- Personalized Recommendations -->
                    <div style="background-color: var(--color-navy); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: var(--space-30); margin-bottom: var(--space-30);">
                        <h4 style="margin-bottom: var(--space-20); border-bottom: 1px dashed var(--border-color); padding-bottom: 10px;"><i class="fa-solid fa-wand-magic-sparkles" style="color: var(--color-red); margin-right: 8px;"></i><?php esc_html_e( 'Personalized For You', 'ascendance-core' ); ?></h4>
                        <?php $this->render_recommended_feed( $user_id ); ?>
                    </div>

                    <!-- Member Quick Actions -->
                    <div style="background-color: var(--color-navy); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: var(--space-30);">
                        <h4 style="margin-bottom: var(--space-20); border-bottom: 1px dashed var(--border-color); padding-bottom: 10px;"><i class="fa-solid fa-user-gear" style="color: var(--color-red); margin-right: 8px;"></i><?php esc_html_e( 'Account Services', 'ascendance-core' ); ?></h4>
                        <ul style="list-style: none; display: flex; flex-direction: column; gap: 12px; font-size: var(--font-size-sm); font-family: var(--font-heading);">
                            <?php if ( function_exists( 'pmpro_url' ) ) : ?>
                                <li><a href="<?php echo esc_url( pmpro_url( 'account' ) ); ?>"><i class="fa-regular fa-id-card" style="margin-right:8px;"></i> <?php esc_html_e( 'Manage Subscription', 'ascendance-core' ); ?></a></li>
                                <li><a href="<?php echo esc_url( pmpro_url( 'billing' ) ); ?>"><i class="fa-regular fa-credit-card" style="margin-right:8px;"></i> <?php esc_html_e( 'Update Billing Info', 'ascendance-core' ); ?></a></li>
                            <?php endif; ?>
                            <li><a href="<?php echo esc_url( get_edit_user_link() ); ?>"><i class="fa-regular fa-user" style="margin-right:8px;"></i> <?php esc_html_e( 'Edit Profile Preferences', 'ascendance-core' ); ?></a></li>
                            <li><a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><i class="fa-solid fa-arrow-right-from-bracket" style="margin-right:8px; color: var(--color-red);"></i> <?php esc_html_e( 'Sign Out', 'ascendance-core' ); ?></a></li>
                        </ul>
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
                
                // Fetch required tier access
                $tier = get_field( 'tier_access', get_the_ID() );
                $tier_label = $tier ? ucfirst( $tier ) : 'Essential';
                
                // Dynamic styling: updates show impact assess, briefs show claims
                $extra_meta = '';
                if ( 'update' === $post_type ) {
                    $impact = get_field( 'impact_assessment', get_the_ID() );
                    $impact_colors = array( 'low' => '#00FF66', 'medium' => '#FFCC00', 'high' => '#FF6600', 'critical' => 'var(--color-red)' );
                    $color = isset( $impact_colors[ $impact ] ) ? $impact_colors[ $impact ] : '#FFCC00';
                    $extra_meta = '<span style="color:' . esc_attr( $color ) . '; font-family: var(--font-mono); font-size:11px; text-transform:uppercase; border:1px solid ' . esc_attr( $color ) . '; padding:2px 6px; border-radius:3px; margin-left:10px;">Impact: ' . esc_html( $impact ) . '</span>';
                }
                
                ?>
                <div class="card" style="padding: var(--space-20); display: flex; flex-direction: column; gap: 8px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                        <div class="card-meta" style="margin-bottom: 0;">
                            <span class="card-tag"><?php echo esc_html( $post_type ); ?></span>
                            <span><?php echo get_the_date(); ?></span>
                            <?php echo $extra_meta; ?>
                        </div>
                        <span style="font-size: 11px; font-family: var(--font-heading); color: var(--color-cream); background: rgba(255,255,255,0.08); padding: 2px 8px; border-radius: 4px; border: 1px solid var(--border-color);"><?php echo esc_html( $tier_label ); ?> Tier</span>
                    </div>
                    <h4 style="margin-bottom: 0; font-size: var(--font-size-sm);"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                    
                    <?php if ( 'brief' === $post_type && get_field( 'analytical_claim' ) ) : ?>
                        <div style="font-family: var(--font-mono); font-size: 12px; color: #00FF66; background: #030810; padding: 8px 12px; border-radius: 4px; border: 1px solid rgba(0,255,102,0.1); margin-top:4px;">
                            <strong style="color:var(--color-red);">CLAIM // </strong> <?php echo esc_html( get_field( 'analytical_claim' ) ); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php
            }
            wp_reset_postdata();
        } else {
            echo '<p style="color: var(--text-muted); font-size: var(--font-size-sm); font-style: italic;">' . sprintf( __( 'No %s published yet.', 'ascendance-core' ), esc_html( $post_type ) . 's' ) . '</p>';
        }
    }

    /**
     * Render dynamic personalized recommendations based on preferred Industry and Region
     */
    private function render_recommended_feed( $user_id ) {
        $preferred_industries = get_user_meta( $user_id, 'preferred_industries', true );
        $preferred_regions = get_user_meta( $user_id, 'preferred_regions', true );

        $tax_query = array( 'relation' => 'OR' );

        if ( ! empty( $preferred_industries ) ) {
            $tax_query[] = array(
                'taxonomy' => 'industry',
                'field'    => 'term_id',
                'terms'    => $preferred_industries,
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
            echo '<div style="display: flex; flex-direction: column; gap: 12px;">';
            while ( $query->have_posts() ) {
                $query->the_post();
                ?>
                <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 8px; font-size: var(--font-size-xs);">
                    <a href="<?php the_permalink(); ?>" style="color: var(--text-primary); font-weight: var(--weight-bold); display: block; margin-bottom: 4px;"><?php the_title(); ?></a>
                    <span style="color: var(--color-red); text-transform: uppercase; font-family: var(--font-heading); font-size: 10px; font-weight: bold;"><?php echo esc_html( get_post_type() ); ?></span>
                </div>
                <?php
            }
            echo '</div>';
            wp_reset_postdata();
        } else {
            echo '<p style="color: var(--text-muted); font-size: var(--font-size-xs); font-style: italic;">' . esc_html__( 'Configure your profile preferences below to receive specialized intelligence notifications.', 'ascendance-core' ) . '</p>';
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
        <div class="pricing-matrix" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: var(--space-30); padding: var(--space-40) 0;">
            <!-- Plan 1: Essential -->
            <div class="card" style="text-align: center; display: flex; flex-direction: column; border-top: 3px solid var(--border-color);">
                <span style="font-family: var(--font-heading); text-transform: uppercase; color: var(--text-muted); font-size: var(--font-size-xs); font-weight: bold; letter-spacing: 1px;"><?php esc_html_e( 'Tier 1', 'ascendance-core' ); ?></span>
                <h3 style="margin-top: 8px; margin-bottom: 4px;"><?php esc_html_e( 'Essential', 'ascendance-core' ); ?></h3>
                <div style="font-family: var(--font-heading); margin-bottom: var(--space-30);">
                    <span style="font-size: var(--font-size-xl); font-weight: bold; color: var(--color-white);">$150</span>
                    <span style="color: var(--text-muted);">/ month</span>
                </div>
                <p style="color: var(--text-secondary); font-size: var(--font-size-sm); margin-bottom: var(--space-40); flex-grow: 1;">
                    <?php esc_html_e( 'Full access to Intelligence Briefs, tracking updates, and primary industry research feeds.', 'ascendance-core' ); ?>
                </p>
                <a href="<?php echo esc_url( add_query_arg( 'level', '1', $checkout_url ) ); ?>" class="btn btn-secondary" style="width: 100%;"><?php esc_html_e( 'Select Plan', 'ascendance-core' ); ?></a>
            </div>

            <!-- Plan 2: Professional (Recommended) -->
            <div class="card" style="text-align: center; display: flex; flex-direction: column; border-top: 3px solid var(--color-red); box-shadow: var(--shadow-lg), var(--shadow-red); transform: scale(1.02); position: relative; background: linear-gradient(180deg, rgba(15,30,53,1) 0%, rgba(10,22,40,1) 100%);">
                <span class="paywall-badge" style="position: absolute; top: -14px; left: 50%; transform: translateX(-50%);"><?php esc_html_e( 'Recommended', 'ascendance-core' ); ?></span>
                <span style="font-family: var(--font-heading); text-transform: uppercase; color: var(--color-red); font-size: var(--font-size-xs); font-weight: bold; letter-spacing: 1px; margin-top: 8px;"><?php esc_html_e( 'Tier 2', 'ascendance-core' ); ?></span>
                <h3 style="margin-top: 8px; margin-bottom: 4px; color: var(--color-white);"><?php esc_html_e( 'Professional', 'ascendance-core' ); ?></h3>
                <div style="font-family: var(--font-heading); margin-bottom: var(--space-30);">
                    <span style="font-size: var(--font-size-xl); font-weight: bold; color: var(--color-white);">$299</span>
                    <span style="color: var(--text-muted);">/ month</span>
                </div>
                <p style="color: var(--text-secondary); font-size: var(--font-size-sm); margin-bottom: var(--space-40); flex-grow: 1;">
                    <?php esc_html_e( 'Unlock high-density Dossiers, downloads, stakeholder profiling, and cross-referenced historical indexes.', 'ascendance-core' ); ?>
                </p>
                <a href="<?php echo esc_url( add_query_arg( 'level', '2', $checkout_url ) ); ?>" class="btn btn-primary" style="width: 100%;"><?php esc_html_e( 'Activate Professional', 'ascendance-core' ); ?></a>
            </div>

            <!-- Plan 3: Enterprise -->
            <div class="card" style="text-align: center; display: flex; flex-direction: column; border-top: 3px solid var(--border-color);">
                <span style="font-family: var(--font-heading); text-transform: uppercase; color: var(--text-muted); font-size: var(--font-size-xs); font-weight: bold; letter-spacing: 1px;"><?php esc_html_e( 'Tier 3', 'ascendance-core' ); ?></span>
                <h3 style="margin-top: 8px; margin-bottom: 4px;"><?php esc_html_e( 'Enterprise', 'ascendance-core' ); ?></h3>
                <div style="font-family: var(--font-heading); margin-bottom: var(--space-30);">
                    <span style="font-size: var(--font-size-xl); font-weight: bold; color: var(--color-white);">$599</span>
                    <span style="color: var(--text-muted);">/ month</span>
                </div>
                <p style="color: var(--text-secondary); font-size: var(--font-size-sm); margin-bottom: var(--space-40); flex-grow: 1;">
                    <?php esc_html_e( 'Full access to complete intelligence base, direct API hooks, dedicated dashboard instances, and custom queries.', 'ascendance-core' ); ?>
                </p>
                <a href="<?php echo esc_url( add_query_arg( 'level', '3', $checkout_url ) ); ?>" class="btn btn-secondary" style="width: 100%;"><?php esc_html_e( 'Contact Enterprise', 'ascendance-core' ); ?></a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * User profile preference settings (Admin Display)
     */
    public function render_user_preferences( $user ) {
        $preferred_industries = get_user_meta( $user->ID, 'preferred_industries', true );
        $preferred_regions = get_user_meta( $user->ID, 'preferred_regions', true );

        $industries = get_terms( array( 'taxonomy' => 'industry', 'hide_empty' => false ) );
        $regions = get_terms( array( 'taxonomy' => 'region', 'hide_empty' => false ) );

        if ( ! is_array( $preferred_industries ) ) $preferred_industries = array();
        if ( ! is_array( $preferred_regions ) ) $preferred_regions = array();
        ?>
        <h3 style="color:var(--color-white); margin-top:20px;"><?php esc_html_e( 'Ascendance Intelligence Feed Customization', 'ascendance-core' ); ?></h3>
        <table class="form-table">
            <tr>
                <th><label><?php esc_html_e( 'Subscribed Industries', 'ascendance-core' ); ?></label></th>
                <td>
                    <?php if ( ! empty( $industries ) && ! is_wp_error( $industries ) ) : ?>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; max-width:600px;">
                            <?php foreach ( $industries as $industry ) : ?>
                                <label style="display:inline-flex; align-items:center; font-weight:normal;">
                                    <input type="checkbox" name="preferred_industries[]" value="<?php echo esc_attr( $industry->term_id ); ?>" <?php checked( in_array( $industry->term_id, $preferred_industries ) ); ?> style="margin-right:8px;" />
                                    <?php echo esc_html( $industry->name ); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p style="color:var(--text-muted);"><?php esc_html_e( 'No industry taxonomy categories registered yet.', 'ascendance-core' ); ?></p>
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

        $industries = isset( $_POST['preferred_industries'] ) ? array_map( 'intval', $_POST['preferred_industries'] ) : array();
        $regions = isset( $_POST['preferred_regions'] ) ? array_map( 'intval', $_POST['preferred_regions'] ) : array();

        update_user_meta( $user_id, 'preferred_industries', $industries );
        update_user_meta( $user_id, 'preferred_regions', $regions );
    }
}
