<?php
/**
 * Custom Subscription Paywall Engine Class
 *
 * @package Ascendance\Core
 */

namespace Ascendance\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Paywall {

    /**
     * Singleton instance
     * @var Paywall|null
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
        add_filter( 'the_content', array( $this, 'enforce_paywall' ), 99 );
        add_action( 'init', array( $this, 'register_paywall_cta_block' ) );

        // Gate REST API responses for briefs, dossiers, and updates
        add_filter( 'rest_prepare_brief', array( $this, 'gate_rest_response' ), 10, 3 );
        add_filter( 'rest_prepare_update', array( $this, 'gate_rest_response' ), 10, 3 );
        add_filter( 'rest_prepare_dossier', array( $this, 'gate_rest_response' ), 10, 3 );
    }

    /**
     * Register Gutenberg block mockup or shortcode for the Paywall CTA
     */
    public function register_paywall_cta_block() {
        // Register shortcode fallback
        add_shortcode( 'ascendance_paywall_cta', array( $this, 'render_paywall_cta' ) );
    }

    /**
     * Enforce paywall restriction logic on post content
     *
     * @param string $content The post content.
     * @return string Truncated content with paywall CTA if restricted, otherwise full content.
     */
    public function enforce_paywall( $content ) {
        // Check if singular post page or RSS feed
        $is_feed = is_feed();
        $is_singular = is_singular( array( 'brief', 'dossier', 'update' ) ) && in_the_loop() && is_main_query();

        if ( ! $is_singular && ! $is_feed ) {
            return $content;
        }

        // For feed, ensure it's one of our post types
        if ( $is_feed ) {
            $post_type = get_post_type();
            if ( ! in_array( $post_type, array( 'brief', 'dossier', 'update' ), true ) ) {
                return $content;
            }
        }

        $post_id = get_the_ID();
        $post_type = get_post_type( $post_id );

        // 1. Determine curated teaser content: try author-curated ACF teaser fields first
        $teaser = '';
        $has_curated = false;

        if ( in_array( $post_type, array( 'brief', 'dossier' ), true ) ) {
            $acf_excerpt = get_field( 'public_excerpt', $post_id );
            if ( ! empty( $acf_excerpt ) ) {
                $teaser = '<div class="aeo-lead-citation-block" itemprop="description"><p class="aeo-lead-citation">' . esc_html( $acf_excerpt ) . '</p></div>';
                $has_curated = true;
            }
        } elseif ( 'update' === $post_type ) {
            $acf_summary = get_field( 'one_line_summary', $post_id );
            if ( ! empty( $acf_summary ) ) {
                $teaser = '<div class="aeo-lead-citation-block update-teaser" itemprop="description"><p class="aeo-lead-citation">' . esc_html( $acf_summary ) . '</p></div>';
                $has_curated = true;
            }
        }

        // Fallback to first paragraph of content if no curated teaser was found (only for guests/non-access)
        if ( empty( $teaser ) ) {
            if ( preg_match( '/<p[^>]*>.*?<\/p>/s', $content, $matches ) ) {
                $teaser = '<div class="aeo-lead-citation-block" itemprop="description"><div class="aeo-lead-citation">' . $matches[0] . '</div></div>';
            } else {
                $teaser = '<div class="aeo-lead-citation-block" itemprop="description"><p class="aeo-lead-citation">' . wp_trim_words( wp_strip_all_tags( $content ), 60, '...' ) . '</p></div>';
            }
        }

        // 2. Canonical access decision — reason code drives context-aware CTA copy
        $access = $this->check_access( $post_id );
        if ( $access['allowed'] ) {
            // For authorized users, prepend the curated teaser/citable lead if it exists
            if ( $has_curated ) {
                return $teaser . $content;
            }
            return $content;
        }

        // 3. For feeds, return only the teaser with a short plain text paywall note
        if ( $is_feed ) {
            // Dynamic: reads the Pricing page ID from Platform Settings.
            $pricing_page_id = (int) get_option( 'ascendance_pricing_page_id', 0 );
            $pricing_url = $pricing_page_id ? get_permalink( $pricing_page_id ) : site_url( '/pricing' );
            $feed_paywall_note = sprintf(
                "\n\n<p><strong>[%s]</strong> %s <a href='%s'>%s</a></p>",
                esc_html__( 'Gated Content', 'ascendance-core' ),
                esc_html__( 'This analytical report requires an active subscription to read.', 'ascendance-core' ),
                esc_url( $pricing_url ),
                esc_html__( 'View Membership Plans', 'ascendance-core' )
            );
            return $teaser . $feed_paywall_note;
        }

        // 4. Append the dynamic paywall block (reason passed for context-aware CTA copy)
        $paywall_cta = $this->render_paywall_cta( $post_id, $access['reason'] );

        return $teaser . $paywall_cta;
    }

    /**
     * Canonical access decision method with structured reason codes.
     *
     * Returns an array with keys:
     *   'allowed' (bool)   — whether access is granted
     *   'reason'  (string) — internal reason code (never expose directly to subscribers)
     *
     * Reason codes:
     *   ALLOW: allowed_editor, allowed_crawler, allowed_public, allowed_enterprise,
     *          allowed_tier, allowed_category
     *   DENY:  denied_not_logged_in, denied_tier, denied_category,
     *          denied_revoked_entitlement, denied_expired_entitlement
     *
     * Multi-category rule: ANY — user needs entitlement for at least one paid category.
     * Inactive categories (addon_status != 'active') are not enforced.
     * Enterprise / Administrator roles bypass category gates entirely.
     *
     * @param int|null $post_id Post ID. Defaults to current post in loop.
     * @param int      $user_id User ID. 0 = resolve from current logged-in user.
     * @return array { allowed: bool, reason: string }
     */
    public function check_access( $post_id = null, $user_id = 0 ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }
        if ( ! $post_id ) {
            return array( 'allowed' => false, 'reason' => 'denied_not_logged_in' );
        }

        // Admins and editors bypass paywall
        if ( current_user_can( 'edit_post', $post_id ) ) {
            return array( 'allowed' => true, 'reason' => 'allowed_editor' );
        }

        // SEO and AI Bot search crawlers bypass paywall for indexability (Preview Mode)
        if ( $this->is_search_crawler() ) {
            return array( 'allowed' => true, 'reason' => 'allowed_crawler' );
        }

        // Fetch required access tier from ACF metadata
        $required_tier = get_field( 'tier_access', $post_id );
        if ( ! $required_tier ) {
            // Fallback to check taxonomy 'tier'
            $terms = wp_get_post_terms( $post_id, 'tier', array( 'fields' => 'slugs' ) );
            if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                $required_tier = $terms[0];
            } else {
                // Dynamic: per-post-type defaults set in Mission Control → Settings → Platform Settings.
                $post_type = get_post_type( $post_id );
                if ( 'dossier' === $post_type ) {
                    $required_tier = get_option( 'ascendance_default_tier_dossier', 'professional' );
                } elseif ( 'update' === $post_type ) {
                    $required_tier = get_option( 'ascendance_default_tier_update', 'essential' );
                } else {
                    $required_tier = get_option( 'ascendance_default_tier_brief', 'essential' );
                }
            }
        }

        if ( $required_tier ) {
            $required_tier = str_replace( array( 'tier-', '-tier' ), '', strtolower( $required_tier ) );
        }

        // Tier hierarchy scores: public/free=0, essential=1, professional=2, enterprise=3
        $tier_hierarchy = array(
            'public'       => 0,
            'free'         => 0,
            'essential'    => 1,
            'professional' => 2,
            'enterprise'   => 3,
        );

        $required_score = isset( $tier_hierarchy[ $required_tier ] ) ? $tier_hierarchy[ $required_tier ] : 1;

        // Public / free content — no authentication required
        if ( 0 === $required_score ) {
            return array( 'allowed' => true, 'reason' => 'allowed_public' );
        }

        // From here on the user must be authenticated
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }
        if ( ! $user_id ) {
            return array( 'allowed' => false, 'reason' => 'denied_not_logged_in' );
        }

        // Build a map of PMPro level ID → tier score using the configured level IDs.
        $essential_id    = (int) get_option( 'ascendance_essential_level_id', 1 );
        $professional_id = (int) get_option( 'ascendance_professional_level_id', 2 );
        $enterprise_id   = (int) get_option( 'ascendance_enterprise_level_id', 3 );

        $level_score_map = array(
            $essential_id    => 1,
            $professional_id => 2,
            $enterprise_id   => 3,
        );

        // Get user's membership level score
        $user_score = 0;
        $meta_tier  = get_user_meta( $user_id, 'ascendance_membership_tier', true );

        if ( ! empty( $meta_tier ) && isset( $tier_hierarchy[ strtolower( $meta_tier ) ] ) ) {
            $user_score = $tier_hierarchy[ strtolower( $meta_tier ) ];
        } elseif ( function_exists( 'pmpro_getMembershipLevelForUser' ) && $user_id ) {
            $user_level = pmpro_getMembershipLevelForUser( $user_id );

            if ( ! empty( $user_level ) ) {
                $user_score = isset( $level_score_map[ (int) $user_level->id ] )
                    ? $level_score_map[ (int) $user_level->id ]
                    : 0;
            }
        } elseif ( $user_id ) {
            $user = get_userdata( $user_id );
            if ( $user ) {
                if ( in_array( 'administrator', (array) $user->roles, true ) || in_array( 'ascendance_enterprise', (array) $user->roles, true ) ) {
                    $user_score = 3;
                } elseif ( in_array( 'ascendance_professional', (array) $user->roles, true ) ) {
                    $user_score = 2;
                } else {
                    $user_score = 1; // Default logged-in subscribers to Essential tier
                }
            }
        }

        if ( $user_score < $required_score ) {
            return array( 'allowed' => false, 'reason' => 'denied_tier' );
        }

        // Category Add-on Entitlement Check (uses centralized helper — never repeat this logic)
        $paid_categories = $this->get_post_paid_categories( $post_id );

        if ( empty( $paid_categories ) ) {
            // No active paid category gate — tier check alone is sufficient
            return array( 'allowed' => true, 'reason' => 'allowed_tier' );
        }

        // Enterprise / Administrator override — bypass category gate entirely
        $user_obj = get_userdata( $user_id );
        if ( $user_obj && ( in_array( 'administrator', (array) $user_obj->roles, true ) || in_array( 'ascendance_enterprise', (array) $user_obj->roles, true ) ) ) {
            return array( 'allowed' => true, 'reason' => 'allowed_enterprise' );
        }
        if ( 'enterprise' === strtolower( (string) $meta_tier ) ) {
            return array( 'allowed' => true, 'reason' => 'allowed_enterprise' );
        }

        // ANY rule: one matching active, non-expired entitlement across any paid category is sufficient
        $deny_reason  = 'denied_category';
        $entitlements = (array) get_user_meta( $user_id, 'asc_category_entitlements', true );

        foreach ( $paid_categories as $paid_cat ) {
            $slug = $paid_cat->slug;

            if ( isset( $entitlements[ $slug ] ) ) {
                $item = $entitlements[ $slug ];
                if ( is_array( $item ) ) {
                    $status  = isset( $item['status'] ) ? $item['status'] : 'active';
                    $expires = isset( $item['expires_at'] ) ? $item['expires_at'] : null;

                    if ( 'canceling' === $status && $expires && strtotime( $expires ) >= time() ) {
                        return array( 'allowed' => true, 'reason' => 'allowed_category' );
                    }
                    if ( 'payment_issue' === $status ) {
                        $deny_reason = 'denied_payment_issue';
                        continue; // Try next category
                    }
                    if ( 'active' !== $status ) {
                        $deny_reason = 'denied_revoked_entitlement';
                        continue; // Try next category (ANY rule)
                    }
                    if ( $expires && strtotime( $expires ) < time() ) {
                        $deny_reason = 'denied_expired_entitlement';
                        continue; // Try next category
                    }
                    return array( 'allowed' => true, 'reason' => 'allowed_category' );
                }
                // Legacy scalar-value entitlement format
                return array( 'allowed' => true, 'reason' => 'allowed_category' );
            }

            // Legacy: slug stored as array value (not associative key)
            if ( in_array( $slug, $entitlements, true ) ) {
                return array( 'allowed' => true, 'reason' => 'allowed_category' );
            }
        }

        return array( 'allowed' => false, 'reason' => $deny_reason );
    }

    /**
     * Check if a user has access to a post.
     *
     * Thin backward-compatible wrapper around check_access().
     * All existing call sites continue to work without modification.
     *
     * @param int|null $post_id Optional post ID to check.
     * @param int      $user_id Optional user ID. 0 = current logged-in user.
     * @return bool True if user has access, false otherwise.
     */
    public function user_has_access( $post_id = null, $user_id = 0 ) {
        return $this->check_access( $post_id, $user_id )['allowed'];
    }

    /**
     * Get active paid add-on topic categories associated with a post.
     *
     * Returns only topic terms that have is_paid_addon=true AND addon_status=active.
     * This is the single source of truth for paid category resolution.
     * Do NOT repeat this taxonomy/meta lookup in templates, PDF, search, or recommendations.
     *
     * @param int $post_id
     * @return WP_Term[] Array of WP_Term objects; empty array if none.
     */
    public function get_post_paid_categories( $post_id ) {
        $post_topics = wp_get_post_terms( (int) $post_id, 'topic' );
        if ( is_wp_error( $post_topics ) || empty( $post_topics ) ) {
            return array();
        }
        $paid = array();
        foreach ( $post_topics as $pt ) {
            $is_paid = get_term_meta( $pt->term_id, 'is_paid_addon', true );
            $status  = get_term_meta( $pt->term_id, 'addon_status', true ) ?: 'active';
            if ( $is_paid && 'active' === $status ) {
                $paid[] = $pt;
            }
        }
        return $paid;
    }

    /**
     * Check if a subscriber has category entitlement
     *
     * @param int $user_id
     * @param int|string $category_slug_or_id
     * @return bool
     */
    public function user_has_category_entitlement( $user_id, $category_slug_or_id ) {
        if ( ! $user_id ) {
            return false;
        }

        // Administrators and Enterprise tier subscribers receive full override access
        $user = get_userdata( $user_id );
        if ( $user && ( in_array( 'administrator', (array) $user->roles, true ) || in_array( 'ascendance_enterprise', (array) $user->roles, true ) ) ) {
            return true;
        }

        $meta_tier = get_user_meta( $user_id, 'ascendance_membership_tier', true );
        if ( 'enterprise' === strtolower( $meta_tier ) ) {
            return true;
        }

        $term_slug = '';
        if ( is_numeric( $category_slug_or_id ) ) {
            $t = get_term( (int) $category_slug_or_id, 'topic' );
            if ( $t && ! is_wp_error( $t ) ) {
                $term_slug = $t->slug;
            }
        } else {
            $term_slug = sanitize_title( $category_slug_or_id );
        }

        if ( ! $term_slug ) {
            return false;
        }

        $entitlements = (array) get_user_meta( $user_id, 'asc_category_entitlements', true );
        if ( isset( $entitlements[ $term_slug ] ) ) {
            $item = $entitlements[ $term_slug ];
            if ( is_array( $item ) ) {
                $status  = $item['status'] ?? 'active';
                $expires = $item['expires_at'] ?? null;
                if ( 'canceling' === $status && $expires && strtotime( $expires ) >= time() ) {
                    return true;
                }
                if ( 'active' !== $status ) {
                    return false;
                }
                if ( $expires && strtotime( $expires ) < time() ) {
                    return false;
                }
                return true;
            }
            return true;
        }

        if ( in_array( $term_slug, $entitlements, true ) ) {
            return true;
        }

        return false;
    }

    /**
     * Grant a category add-on entitlement to a subscriber
     */
    public function grant_user_category_entitlement( $user_id, $category_slug_or_id, $expires_at = null ) {
        $user_id = (int) $user_id;
        if ( ! $user_id ) return false;

        $term_slug = '';
        if ( is_numeric( $category_slug_or_id ) ) {
            $t = get_term( (int) $category_slug_or_id, 'topic' );
            if ( $t && ! is_wp_error( $t ) ) {
                $term_slug = $t->slug;
            }
        } else {
            $term_slug = sanitize_title( $category_slug_or_id );
        }

        if ( ! $term_slug ) return false;

        $entitlements = (array) get_user_meta( $user_id, 'asc_category_entitlements', true );
        $entitlements[ $term_slug ] = array(
            'status'     => 'active',
            'granted_at' => time(),
            'expires_at' => $expires_at,
        );

        update_user_meta( $user_id, 'asc_category_entitlements', $entitlements );
        $this->invalidate_user_entitlements_cache( $user_id );
        return true;
    }

    /**
     * Revoke a category add-on entitlement from a subscriber
     */
    public function revoke_user_category_entitlement( $user_id, $category_slug_or_id ) {
        $user_id = (int) $user_id;
        if ( ! $user_id ) return false;

        $term_slug = '';
        if ( is_numeric( $category_slug_or_id ) ) {
            $t = get_term( (int) $category_slug_or_id, 'topic' );
            if ( $t && ! is_wp_error( $t ) ) {
                $term_slug = $t->slug;
            }
        } else {
            $term_slug = sanitize_title( $category_slug_or_id );
        }

        $entitlements = (array) get_user_meta( $user_id, 'asc_category_entitlements', true );
        if ( isset( $entitlements[ $term_slug ] ) ) {
            unset( $entitlements[ $term_slug ] );
            update_user_meta( $user_id, 'asc_category_entitlements', $entitlements );
            $this->invalidate_user_entitlements_cache( $user_id );
        }

        return true;
    }

    /**
     * Invalidate cached entitlement state and recommendations for a user.
     *
     * @param int $user_id
     * @return bool
     */
    public function invalidate_user_entitlements_cache( $user_id ) {
        $user_id = (int) $user_id;
        if ( ! $user_id ) return false;

        delete_transient( 'asc_user_ent_' . $user_id );
        clean_user_cache( $user_id );

        if ( class_exists( 'Ascendance\Core\Recommendation_Engine' ) ) {
            Recommendation_Engine::get_instance()->invalidate_user_cache( $user_id );
        }
        return true;
    }

    /**
     * Get subscriber's active category entitlements
     */
    public function get_user_category_entitlements( $user_id ) {
        $user_id = (int) $user_id;
        if ( ! $user_id ) return array();

        $raw = (array) get_user_meta( $user_id, 'asc_category_entitlements', true );
        $active = array();

        foreach ( $raw as $slug => $data ) {
            if ( is_array( $data ) ) {
                if ( 'active' === ( $data['status'] ?? 'active' ) ) {
                    $exp = $data['expires_at'] ?? null;
                    if ( ! $exp || strtotime( $exp ) >= time() ) {
                        $active[ $slug ] = $data;
                    }
                }
            } else {
                $active[ $data ] = array( 'status' => 'active', 'granted_at' => null, 'expires_at' => null );
            }
        }

        return $active;
    }

    /**
     * Check if current user-agent is a verified/known search crawler
     *
     * @return bool
     */
    private function is_search_crawler() {
        if ( empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
            return false;
        }

        $user_agent = strtolower( $_SERVER['HTTP_USER_AGENT'] );
        $crawlers = array(
            'googlebot',
            'bingbot',
            'slurp',
            'duckduckbot',
            'baiduspider',
            'yandexbot',
            // AI Search engine crawlers
            'gptbot',
            'claudebot',
            'perplexitybot',
            'gemini',
        );

        foreach ( $crawlers as $crawler ) {
            if ( strpos( $user_agent, $crawler ) !== false ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Render HTML block for Paywall CTA.
     *
     * Accepts an optional reason code from check_access() to display context-aware copy.
     * A category-denied user sees a category add-on CTA instead of a tier upgrade CTA.
     * Never expose the raw reason string to subscribers — it is internal only.
     *
     * @param int|null $post_id Optional post ID. Defaults to current post in loop.
     * @param string   $reason  Optional denial reason code from check_access().
     * @return string HTML rendering of the paywall banner.
     */
    public function render_paywall_cta( $post_id = null, $reason = '' ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }

        // Category-specific CTA for denied_category / denied_revoked_entitlement / denied_expired_entitlement
        $is_category_denial = in_array( $reason, array( 'denied_category', 'denied_revoked_entitlement', 'denied_expired_entitlement' ), true );

        if ( $is_category_denial ) {
            $paid_categories = $this->get_post_paid_categories( $post_id );
            $cat_names = array();
            foreach ( $paid_categories as $cat ) {
                $icon = get_term_meta( $cat->term_id, 'addon_icon', true ) ?: '';
                $cat_names[] = trim( $icon . ' ' . $cat->name );
            }
            $cat_label = ! empty( $cat_names ) ? implode( ' / ', $cat_names ) : __( 'Specialist Category', 'ascendance-core' );

            // Contact page from Platform Settings, fallback to /contact/
            $contact_page_id = (int) get_option( 'ascendance_contact_page_id', 0 );
            $contact_url     = $contact_page_id ? get_permalink( $contact_page_id ) : home_url( '/contact/' );
            $login_url       = wp_login_url( get_permalink( $post_id ) );

            ob_start();
            ?>
            <div class="paywall-container">
                <div class="paywall-fade"></div>
                <div class="paywall-cta-box">
                    <span class="paywall-badge paywall-badge-addon"><?php echo esc_html( $cat_label ); ?> &mdash; <?php esc_html_e( 'Add-on Required', 'ascendance-core' ); ?></span>
                    <h3 class="paywall-title"><?php esc_html_e( 'Category Intelligence Locked', 'ascendance-core' ); ?></h3>
                    <p class="paywall-description">
                        <?php
                        printf(
                            esc_html__( 'This intelligence report is part of the %s specialist category and requires a separate add-on subscription. Contact our team to unlock access for your account.', 'ascendance-core' ),
                            '<strong>' . esc_html( $cat_label ) . '</strong>'
                        );
                        ?>
                    </p>
                    <div class="paywall-actions">
                        <?php if ( ! is_user_logged_in() ) : ?>
                            <a href="<?php echo esc_url( $contact_url ); ?>" class="btn btn-primary paywall-subscribe-cta"><?php esc_html_e( 'Unlock Category Access', 'ascendance-core' ); ?></a>
                            <a href="<?php echo esc_url( $login_url ); ?>" class="btn btn-secondary paywall-login-cta"><?php esc_html_e( 'Subscriber Sign In', 'ascendance-core' ); ?></a>
                        <?php else : ?>
                            <a href="<?php echo esc_url( $contact_url ); ?>" class="btn btn-primary paywall-subscribe-cta"><?php esc_html_e( 'Contact Us to Unlock', 'ascendance-core' ); ?></a>
                            <span class="paywall-status">
                                <?php
                                $current_level = function_exists( 'pmpro_getMembershipLevelForUser' ) ? pmpro_getMembershipLevelForUser( get_current_user_id() ) : null;
                                if ( $current_level ) {
                                    printf( esc_html__( 'Your subscription level (%s) does not include this category add-on.', 'ascendance-core' ), '<strong>' . esc_html( $current_level->name ) . '</strong>' );
                                } else {
                                    esc_html_e( 'This category add-on is not included in your current plan.', 'ascendance-core' );
                                }
                                ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }

        // ---- Standard Tier Upgrade CTA (existing behaviour) ----
        $required_tier = get_field( 'tier_access', $post_id );
        if ( ! $required_tier ) {
            $terms = wp_get_post_terms( $post_id, 'tier', array( 'fields' => 'slugs' ) );
            $required_tier = ( ! empty( $terms ) && ! is_wp_error( $terms ) ) ? $terms[0] : 'essential';
        }

        $tier_name = ucfirst( $required_tier );
        // Dynamic: reads page ID from Platform Settings first, then tries PMPro, then falls back to slug.
        $levels_page_id = (int) get_option( 'ascendance_membership_levels_page_id', 0 );
        if ( $levels_page_id ) {
            $pricing_url = get_permalink( $levels_page_id );
        } elseif ( function_exists( 'pmpro_url' ) ) {
            $pricing_url = pmpro_url( 'levels' );
        } else {
            $pricing_url = home_url( '/membership-levels/' );
        }
        $login_url = wp_login_url( get_permalink( $post_id ) );

        ob_start();
        ?>
        <div class="paywall-container">
            <div class="paywall-fade"></div>
            <div class="paywall-cta-box">
                <span class="paywall-badge"><?php echo esc_html( $tier_name ); ?> <?php esc_html_e( 'Membership Required', 'ascendance-core' ); ?></span>
                <h3 class="paywall-title"><?php esc_html_e( 'Intelligence Protected', 'ascendance-core' ); ?></h3>
                <p class="paywall-description">
                    <?php 
                    printf( 
                        esc_html__( 'This analytical report, compiled by our leading intelligence desk, is restricted to members subscribed to the %s tier. Upgrade your account to unlock full document analysis.', 'ascendance-core' ),
                        '<strong>' . esc_html( $tier_name ) . '</strong>'
                    ); 
                    ?>
                </p>
                
                <div class="paywall-actions">
                    <?php if ( ! is_user_logged_in() ) : ?>
                        <a href="<?php echo esc_url( $pricing_url ); ?>" class="btn btn-primary paywall-subscribe-cta"><?php esc_html_e( 'View Membership Plans', 'ascendance-core' ); ?></a>
                        <a href="<?php echo esc_url( $login_url ); ?>" class="btn btn-secondary paywall-login-cta"><?php esc_html_e( 'Subscriber Sign In', 'ascendance-core' ); ?></a>
                    <?php else : ?>
                        <a href="<?php echo esc_url( $pricing_url ); ?>" class="btn btn-primary paywall-subscribe-cta"><?php esc_html_e( 'Upgrade to Unlock', 'ascendance-core' ); ?></a>
                        <span class="paywall-status">
                            <?php 
                            $current_level = function_exists( 'pmpro_getMembershipLevelForUser' ) ? pmpro_getMembershipLevelForUser( get_current_user_id() ) : null;
                            if ( $current_level ) {
                                printf( esc_html__( 'Current membership level: %s', 'ascendance-core' ), '<strong>' . esc_html( $current_level->name ) . '</strong>' );
                            } else {
                                esc_html_e( 'You do not have an active membership tier.', 'ascendance-core' );
                            }
                            ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Gate REST API response content and fields for unauthorized users
     *
     * @param WP_REST_Response $response The response object.
     * @param WP_Post $post The post object.
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response The modified response object.
     */
    public function gate_rest_response( $response, $post, $request ) {
        // Bypass gating in the block editor / context=edit requests
        if ( isset( $request['context'] ) && 'edit' === $request['context'] ) {
            return $response;
        }

        // Bypass gating if the request originates from the WordPress admin dashboard (e.g. Gutenberg editor iframe or metaboxes)
        if ( ! empty( $_SERVER['HTTP_REFERER'] ) && strpos( $_SERVER['HTTP_REFERER'], '/wp-admin/' ) !== false ) {
            return $response;
        }

        // Bypass if user has access to this post
        $access = $this->check_access( $post->ID );
        if ( $access['allowed'] ) {
            return $response;
        }

        // User does not have access. Truncate/sanitize response data.
        $data = $response->get_data();

        // 1. Teaser calculation
        $teaser = '';
        $post_type = $post->post_type;

        if ( in_array( $post_type, array( 'brief', 'dossier' ), true ) ) {
            $acf_excerpt = get_field( 'public_excerpt', $post->ID );
            if ( ! empty( $acf_excerpt ) ) {
                $teaser = '<p class="teaser-text">' . esc_html( $acf_excerpt ) . '</p>';
            }
        } elseif ( 'update' === $post_type ) {
            $acf_summary = get_field( 'one_line_summary', $post->ID );
            if ( ! empty( $acf_summary ) ) {
                $teaser = '<p class="teaser-text update-teaser">' . esc_html( $acf_summary ) . '</p>';
            }
        }

        if ( empty( $teaser ) ) {
            $raw_content = isset( $data['content']['raw'] ) ? $data['content']['raw'] : ( isset( $data['content']['rendered'] ) ? $data['content']['rendered'] : '' );
            if ( preg_match( '/<p[^>]*>.*?<\/p>/s', $raw_content, $matches ) ) {
                $teaser = $matches[0];
            } else {
                $teaser = '<p>' . wp_trim_words( wp_strip_all_tags( $raw_content ), 60, '...' ) . '</p>';
            }
        }

        $paywall_cta = $this->render_paywall_cta( $post->ID, $access['reason'] );

        // Truncate the rendered content field
        if ( isset( $data['content']['rendered'] ) ) {
            $data['content']['rendered'] = $teaser . $paywall_cta;
        }
        if ( isset( $data['content']['raw'] ) ) {
            $data['content']['raw'] = wp_strip_all_tags( $teaser ) . "\n\n[Gated Content]";
        }

        // Clear out any sensitive CPT metadata or ACF fields that shouldn't leak
        if ( isset( $data['acf'] ) ) {
            // Keep public fields, clear gated ones
            $public_acf = array( 'public_excerpt', 'one_line_summary', 'tier_access' );
            foreach ( $data['acf'] as $key => $val ) {
                if ( ! in_array( $key, $public_acf, true ) ) {
                    unset( $data['acf'][ $key ] );
                }
            }
        }

        $response->set_data( $data );
        return $response;
    }
}
