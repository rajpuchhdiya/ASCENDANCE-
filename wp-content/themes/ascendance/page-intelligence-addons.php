<?php
/**
 * Template Name: Intelligence Add-ons Storefront
 *
 * @package Ascendance
 */

get_header();

// ──────────────────────────────────────────────────────────────────────────────
// Context & Requested Category Resolution
// ──────────────────────────────────────────────────────────────────────────────
$requested_slug = get_query_var( 'addon_slug' );
if ( empty( $requested_slug ) && isset( $_GET['cat'] ) ) {
    $requested_slug = sanitize_title( $_GET['cat'] );
}

$current_user = wp_get_current_user();
$is_logged_in = $current_user->exists();
$user_id      = $is_logged_in ? $current_user->ID : 0;

$paywall       = class_exists( 'Ascendance\Core\Paywall' ) ? \Ascendance\Core\Paywall::get_instance() : null;
$meta_tier     = $is_logged_in ? get_user_meta( $user_id, 'ascendance_membership_tier', true ) : '';
$is_enterprise = $is_logged_in && ( 'enterprise' === strtolower( (string) $meta_tier )
    || ( in_array( 'administrator', (array) $current_user->roles, true ) || in_array( 'ascendance_enterprise', (array) $current_user->roles, true ) ) );

$raw_entitlements = $is_logged_in ? (array) get_user_meta( $user_id, 'asc_category_entitlements', true ) : array();
$date_format      = get_option( 'date_format' ) ?: 'F j, Y';

// Fetch active paid topic categories
$all_paid_terms = get_terms( array(
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

$active_categories = array();
$requested_category = null;

if ( ! is_wp_error( $all_paid_terms ) && ! empty( $all_paid_terms ) ) {
    foreach ( $all_paid_terms as $term ) {
        $status = get_term_meta( $term->term_id, 'addon_status', true ) ?: 'active';
        if ( 'active' === $status ) {
            $active_categories[] = $term;
            if ( ! empty( $requested_slug ) && $term->slug === $requested_slug ) {
                $requested_category = $term;
            }
        }
    }
}

// ──────────────────────────────────────────────────────────────────────────────
// Efficient Aggregate Content Counting (1 Batch Query)
// ──────────────────────────────────────────────────────────────────────────────
$content_counts = array();
if ( ! empty( $active_categories ) ) {
    global $wpdb;
    $term_ids = wp_list_pluck( $active_categories, 'term_id' );
    $ids_in   = implode( ',', array_map( 'intval', $term_ids ) );

    $counts_sql = "
        SELECT tt.term_id, p.post_type, COUNT(DISTINCT p.ID) AS total_count
        FROM {$wpdb->term_relationships} tr
        INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
        INNER JOIN {$wpdb->posts} p ON tr.object_id = p.ID
        WHERE tt.term_id IN ($ids_in)
          AND p.post_status = 'publish'
          AND p.post_type IN ('brief', 'update', 'dossier')
        GROUP BY tt.term_id, p.post_type
    ";
    $results = $wpdb->get_results( $counts_sql );

    if ( ! empty( $results ) ) {
        foreach ( $results as $row ) {
            $tid  = (int) $row->term_id;
            $ptype = $row->post_type;
            if ( ! isset( $content_counts[ $tid ] ) ) {
                $content_counts[ $tid ] = array( 'brief' => 0, 'update' => 0, 'dossier' => 0, 'total' => 0 );
            }
            $content_counts[ $tid ][ $ptype ] = (int) $row->total_count;
            $content_counts[ $tid ]['total']  += (int) $row->total_count;
        }
    }
}

// ──────────────────────────────────────────────────────────────────────────────
// Helper: Resolve Status-Aware Card CTA & Badges
// ──────────────────────────────────────────────────────────────────────────────
function asc_storefront_get_status_info( $term, $user_id, $is_logged_in, $is_enterprise, $raw_entitlements, $paywall, $date_format ) {
    if ( ! $is_logged_in ) {
        return array(
            'state'        => 'logged_out',
            'is_entitled'  => false,
            'badge_html'   => '<span class="addon-badge addon-badge-expired">Add-on Desk</span>',
            'btn_html'     => '<a href="' . esc_url( home_url( '/login/?redirect_to=' . urlencode( home_url( '/intelligence-add-ons/?cat=' . $term->slug ) ) ) ) . '" class="btn btn-primary" style="font-size:11px; padding:6px 14px;">Login to Add &rarr;</a>',
        );
    }

    if ( $is_enterprise ) {
        return array(
            'state'        => 'enterprise',
            'is_entitled'  => true,
            'badge_html'   => '<span class="addon-badge addon-badge-enterprise">Included with Enterprise</span>',
            'btn_html'     => '<button type="button" class="btn btn-ghost" disabled style="font-size:11px; padding:6px 12px; cursor:default;">✓ Included</button>',
            'price_label'  => 'Enterprise Clearance',
        );
    }

    $is_entitled        = false;
    $entitlement_status = 'none';
    $entitlement_source = 'none';
    $expires_formatted  = null;

    if ( isset( $raw_entitlements[ $term->slug ] ) ) {
        $item = $raw_entitlements[ $term->slug ];
        if ( is_array( $item ) ) {
            $item_status = isset( $item['status'] ) ? $item['status'] : 'active';
            $expires     = isset( $item['expires_at'] ) ? $item['expires_at'] : null;

            if ( $expires && strtotime( $expires ) < time() ) {
                $entitlement_status = 'expired';
                $is_entitled        = false;
            } elseif ( 'active' !== $item_status ) {
                $entitlement_status = $item_status;
                $is_entitled        = ( 'canceling' === $item_status );
            } else {
                $entitlement_status = 'active';
                $is_entitled        = true;
            }

            if ( $expires ) {
                $expires_formatted = date_i18n( $date_format, strtotime( $expires ) );
            }

            $sub_id = get_user_meta( $user_id, 'asc_cat_sub_' . $term->slug, true );
            $entitlement_source = ! empty( $sub_id ) ? 'stripe' : 'admin';
        } else {
            $is_entitled        = true;
            $entitlement_status = 'active';
            $entitlement_source = 'admin';
        }
    }

    if ( $entitlement_source === 'admin' && $is_entitled ) {
        return array(
            'state'       => 'admin_granted',
            'is_entitled' => true,
            'badge_html'  => '<span class="addon-badge addon-badge-admin">✓ Granted by Advisory Desk</span>',
            'btn_html'    => '<button type="button" class="btn btn-ghost" disabled style="font-size:11px; padding:6px 12px; cursor:default;">✓ Active Access</button>',
            'price_label' => 'Complimentary Desk',
        );
    }

    if ( $entitlement_status === 'canceling' ) {
        $exp_str = $expires_formatted ?: 'period end';
        return array(
            'state'       => 'canceling',
            'is_entitled' => true,
            'badge_html'  => '<span class="addon-badge addon-badge-canceling">⚠ Access Ends ' . esc_html( $exp_str ) . '</span>',
            'btn_html'    => '<button type="button" class="btn btn-secondary btn-portal-addon" style="font-size:11px; padding:6px 12px;">Manage Billing</button>',
        );
    }

    if ( $is_entitled ) {
        return array(
            'state'       => 'active',
            'is_entitled' => true,
            'badge_html'  => '<span class="addon-badge addon-badge-active">✓ Active</span>',
            'btn_html'    => '<button type="button" class="btn btn-secondary btn-portal-addon" style="font-size:11px; padding:6px 12px;">Manage Billing</button>',
        );
    }

    if ( $entitlement_status === 'expired' ) {
        return array(
            'state'       => 'expired',
            'is_entitled' => false,
            'badge_html'  => '<span class="addon-badge addon-badge-expired">Expired</span>',
            'btn_html'    => '<button type="button" class="btn btn-primary btn-purchase-addon" data-slug="' . esc_attr( $term->slug ) . '" style="font-size:11px; padding:6px 14px;">Renew Access &rarr;</button>',
        );
    }

    if ( $entitlement_status === 'payment_issue' ) {
        return array(
            'state'       => 'payment_issue',
            'is_entitled' => false,
            'badge_html'  => '<span class="addon-badge addon-badge-issue">⚠ Payment Issue</span>',
            'btn_html'    => '<button type="button" class="btn btn-secondary btn-portal-addon" style="font-size:11px; padding:6px 12px;">Manage Billing</button>',
        );
    }

    // Default: Logged-in eligible subscriber without entitlement
    return array(
        'state'       => 'available',
        'is_entitled' => false,
        'badge_html'  => '<span class="addon-badge addon-badge-expired">Add-on Required</span>',
        'btn_html'    => '<button type="button" class="btn btn-primary btn-purchase-addon" data-slug="' . esc_attr( $term->slug ) . '" style="font-size:11px; padding:6px 14px;">Add ' . esc_html( $term->name ) . ' &rarr;</button>',
    );
}

// ──────────────────────────────────────────────────────────────────────────────
// Schema.org Structured Data (JSON-LD) Generator
// ──────────────────────────────────────────────────────────────────────────────
$schema_data = array();
if ( $requested_category ) {
    $amount_val = (float) ( get_term_meta( $requested_category->term_id, 'addon_price_amount', true ) ?: 49.00 );
    $desc       = get_term_meta( $requested_category->term_id, 'addon_description', true ) ?: $requested_category->description;

    $schema_data = array(
        '@context'    => 'https://schema.org',
        '@type'       => 'Product',
        'name'        => 'Ascendance Intelligence Add-on — ' . $requested_category->name,
        'description' => $desc ?: 'Specialist intelligence coverage and analytical reports.',
        'brand'       => array(
            '@type' => 'Organization',
            'name'  => 'Ascendance Advisory Platform',
        ),
        'offers'      => array(
            '@type'         => 'Offer',
            'price'         => number_format( $amount_val, 2, '.', '' ),
            'priceCurrency' => 'USD',
            'availability'  => 'https://schema.org/InStock',
            'url'           => home_url( '/intelligence-add-ons/?cat=' . $requested_category->slug ),
        ),
    );
}
?>

<main id="primary" class="as-page-wrap">

    <?php if ( ! empty( $schema_data ) ) : ?>
    <!-- Schema.org Product Structured Data -->
    <script type="application/ld+json">
    <?php echo json_encode( $schema_data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ); ?>
    </script>
    <?php endif; ?>

    <!-- Checkout Notification Return Banner -->
    <div id="storefront-checkout-banner" style="display:none; max-width:1140px; margin:20px auto 0; padding:14px 18px; border-radius:4px; font-size:13px; font-weight:600;"></div>

    <?php if ( $requested_category ) : ?>
    <!-- ════════════════════════════════════════════════════════════════════════
         CATEGORY DETAIL VIEW
         ════════════════════════════════════════════════════════════════════════ -->
    <?php
    $cat_desc   = get_term_meta( $requested_category->term_id, 'addon_description', true ) ?: $requested_category->description;
    $cat_icon   = get_term_meta( $requested_category->term_id, 'addon_icon', true ) ?: 'dashicons-category';
    $cat_amount = (float) ( get_term_meta( $requested_category->term_id, 'addon_price_amount', true ) ?: 49.00 );
    $status_info = asc_storefront_get_status_info( $requested_category, $user_id, $is_logged_in, $is_enterprise, $raw_entitlements, $paywall, $date_format );
    $counts     = isset( $content_counts[ $requested_category->term_id ] ) ? $content_counts[ $requested_category->term_id ] : array( 'brief' => 0, 'update' => 0, 'dossier' => 0, 'total' => 0 );
    ?>
    <section class="as-page-hero" style="background: var(--navy-deep, #0b132b); color: #fff; padding: 48px 20px;">
        <div class="as-page-hero-inner" style="max-width: 1140px; margin: 0 auto;">
            <div style="font-size: 11px; font-family: var(--font-mono); text-transform: uppercase; color: var(--accent, #BC1B1D); font-weight: bold; margin-bottom: 12px;">
                <a href="<?php echo esc_url( home_url( '/intelligence-add-ons/' ) ); ?>" style="color: inherit; text-decoration: none;">&larr; All Intelligence Desks</a>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px;">
                <div style="flex: 1; min-width: 300px;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                        <h1 class="as-page-title" style="font-size: 32px; font-weight: 800; margin: 0;"><?php echo esc_html( $requested_category->name ); ?></h1>
                        <?php echo $status_info['badge_html']; ?>
                    </div>
                    <p class="as-page-desc" style="font-size: 15px; color: rgba(255,255,255,0.8); max-width: 720px; line-height: 1.6; margin-top: 8px;">
                        <?php echo esc_html( $cat_desc ?: 'Specialist intelligence desk monitoring policy, commercial developments, and strategic risks.' ); ?>
                    </p>
                </div>
                <div style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; padding: 20px 24px; min-width: 240px; text-align: right;">
                    <div style="font-size: 24px; font-weight: 800; font-family: var(--font-mono); color: #fff;">
                        <?php echo isset( $status_info['price_label'] ) ? esc_html( $status_info['price_label'] ) : '$' . number_format( $cat_amount, 2 ); ?>
                    </div>
                    <div style="font-size: 11px; color: rgba(255,255,255,0.6); font-family: var(--font-mono); margin-bottom: 14px;">
                        <?php echo isset( $status_info['price_label'] ) ? 'All-inclusive coverage' : 'USD monthly recurring'; ?>
                    </div>
                    <div><?php echo $status_info['btn_html']; ?></div>
                </div>
            </div>
        </div>
    </section>

    <div class="as-page-body" style="max-width: 1140px; margin: 0 auto; padding: 40px 20px;">
        
        <!-- Coverage Stats Bar -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 40px;">
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 16px; text-align: center;">
                <div style="font-size: 22px; font-weight: 800; color: var(--accent, #BC1B1D); font-family: var(--font-mono);"><?php echo $counts['brief']; ?></div>
                <div style="font-size: 11px; text-transform: uppercase; font-family: var(--font-mono); color: #64748b; margin-top: 4px;">Published Briefs</div>
            </div>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 16px; text-align: center;">
                <div style="font-size: 22px; font-weight: 800; color: #2563eb; font-family: var(--font-mono);"><?php echo $counts['dossier']; ?></div>
                <div style="font-size: 11px; text-transform: uppercase; font-family: var(--font-mono); color: #64748b; margin-top: 4px;">Strategic Dossiers</div>
            </div>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 16px; text-align: center;">
                <div style="font-size: 22px; font-weight: 800; color: #16a34a; font-family: var(--font-mono);"><?php echo $counts['update']; ?></div>
                <div style="font-size: 11px; text-transform: uppercase; font-family: var(--font-mono); color: #64748b; margin-top: 4px;">Real-Time Updates</div>
            </div>
        </div>

        <!-- Example Intelligence Feed (Public Excerpts Only — Protected Body Never Leaked) -->
        <div style="margin-bottom: 40px;">
            <h2 style="font-size: 20px; font-weight: 700; margin-bottom: 16px;">Example Intelligence Coverage</h2>
            <?php
            $ex_query = new \WP_Query( array(
                'post_type'      => array( 'brief', 'dossier', 'update' ),
                'posts_per_page' => 4,
                'post_status'    => 'publish',
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'topic',
                        'field'    => 'term_id',
                        'terms'    => $requested_category->term_id,
                    ),
                ),
            ) );

            if ( $ex_query->have_posts() ) :
                while ( $ex_query->have_posts() ) : $ex_query->the_post();
                    $ex_post_id = get_the_ID();
                    $ex_excerpt = get_the_excerpt( $ex_post_id ) ?: wp_trim_words( get_post_field( 'post_content', $ex_post_id ), 25 );
            ?>
            <div style="padding: 18px 0; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;">
                <div style="flex: 1;">
                    <div style="font-size: 10px; font-family: var(--font-mono); text-transform: uppercase; color: #64748b; font-weight: bold; margin-bottom: 4px;">
                        <?php echo esc_html( get_post_type_labels( get_post_type_object( get_post_type() ) )->singular_name ?? ucfirst( get_post_type() ) ); ?> &middot; <?php echo esc_html( get_the_date( 'j M Y' ) ); ?>
                    </div>
                    <h3 style="margin: 0 0 6px 0; font-size: 16px; font-weight: 700;">
                        <a href="<?php echo esc_url( get_permalink() ); ?>" style="color: inherit; text-decoration: none;"><?php the_title(); ?></a>
                    </h3>
                    <p style="font-size: 13px; color: #475569; line-height: 1.5; margin: 0;"><?php echo esc_html( $ex_excerpt ); ?></p>
                </div>
                <a href="<?php echo esc_url( get_permalink() ); ?>" class="btn btn-ghost" style="font-size: 11px; padding: 6px 12px; flex-shrink: 0; margin-top: 4px;">
                    Read &rarr;
                </a>
            </div>
            <?php
                endwhile;
                wp_reset_postdata();
            else :
            ?>
            <p style="font-size: 13px; color: #64748b;">No public intelligence briefings filed under this category yet.</p>
            <?php endif; ?>
        </div>

        <!-- Add-on Desk FAQ -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 28px; margin-bottom: 40px;">
            <h2 style="font-size: 18px; font-weight: 700; margin-top: 0; margin-bottom: 16px;">Frequently Asked Questions</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <div>
                    <h4 style="font-size: 14px; font-weight: 700; margin: 0 0 6px 0;">Can I add multiple intelligence desks?</h4>
                    <p style="font-size: 13px; color: #475569; margin: 0; line-height: 1.5;">Yes. Subscribers can subscribe to multiple add-on desks independently. Each desk is managed transparently via your Subscriber Dashboard.</p>
                </div>
                <div>
                    <h4 style="font-size: 14px; font-weight: 700; margin: 0 0 6px 0;">Does Enterprise membership include category add-ons?</h4>
                    <p style="font-size: 13px; color: #475569; margin: 0; line-height: 1.5;">Yes. Subscribers with Enterprise Clearance receive full all-inclusive access to every specialist category add-on desk at no extra charge.</p>
                </div>
                <div>
                    <h4 style="font-size: 14px; font-weight: 700; margin: 0 0 6px 0;">Can I cancel a category add-on separately?</h4>
                    <p style="font-size: 13px; color: #475569; margin: 0; line-height: 1.5;">Yes. You can manage or cancel any category add-on subscription individually at any time via the Stripe Customer Billing Portal without affecting your base membership.</p>
                </div>
            </div>
        </div>
    </div>

    <?php else : ?>
    <!-- ════════════════════════════════════════════════════════════════════════
         STOREFRONT GRID VIEW
         ════════════════════════════════════════════════════════════════════════ -->
    <section class="as-page-hero" style="background: var(--navy-deep, #0b132b); color: #fff; padding: 56px 20px; text-align: center;">
        <div class="as-page-hero-inner" style="max-width: 800px; margin: 0 auto;">
            <p class="as-page-eyebrow" style="font-size: 11px; font-family: var(--font-mono); text-transform: uppercase; letter-spacing: 2px; color: var(--accent, #BC1B1D); font-weight: bold; margin-bottom: 8px;">// SPECIALIST INTELLIGENCE ADD-ONS</p>
            <h1 class="as-page-title" style="font-size: 36px; font-weight: 800; margin: 0 0 16px 0; line-height: 1.2;">Expand Your Intelligence Coverage</h1>
            <p class="as-page-desc" style="font-size: 16px; color: rgba(255,255,255,0.8); line-height: 1.6; margin: 0 auto 24px auto;">
                Subscribe to specialist sector and regional intelligence desks to unlock targeted briefs, dossiers, and real-time policy alerts tailored to your mission.
            </p>
        </div>
    </section>

    <!-- Plan vs Add-on Distinction Banner -->
    <div style="background: #f1f5f9; border-bottom: 1px solid #e2e8f0; padding: 14px 20px; text-align: center; font-size: 12px; font-family: var(--font-mono); color: #475569;">
        <strong>BASE PLAN</strong> (Essential / Professional / Enterprise) &nbsp;+&nbsp; <strong>SPECIALIST ADD-ONS</strong> (Critical Minerals, Energy, Geopolitics, Infrastructure, DRC)
    </div>

    <div class="as-page-body" style="max-width: 1140px; margin: 0 auto; padding: 48px 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h2 style="font-size: 20px; font-weight: 800; margin: 0;">Available Intelligence Desks</h2>
            <span style="font-size: 12px; font-family: var(--font-mono); color: #64748b;"><?php echo count( $active_categories ); ?> Desks Active</span>
        </div>

        <?php if ( ! empty( $active_categories ) ) : ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px; margin-bottom: 48px;">
            <?php foreach ( $active_categories as $term ) :
                $cat_desc   = get_term_meta( $term->term_id, 'addon_description', true ) ?: $term->description;
                $cat_amount = (float) ( get_term_meta( $term->term_id, 'addon_price_amount', true ) ?: 49.00 );
                $status_info = asc_storefront_get_status_info( $term, $user_id, $is_logged_in, $is_enterprise, $raw_entitlements, $paywall, $date_format );
                $counts     = isset( $content_counts[ $term->term_id ] ) ? $content_counts[ $term->term_id ] : array( 'brief' => 0, 'update' => 0, 'dossier' => 0, 'total' => 0 );
            ?>
            <div class="addon-card" style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 24px; background: #fff; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 12px;">
                        <h3 style="font-size: 18px; font-weight: 700; margin: 0; color: #0f172a;"><?php echo esc_html( $term->name ); ?></h3>
                        <?php echo $status_info['badge_html']; ?>
                    </div>
                    <p style="font-size: 13px; color: #475569; line-height: 1.5; margin-bottom: 16px;">
                        <?php echo esc_html( $cat_desc ?: 'Specialist intelligence coverage and analytical reports.' ); ?>
                    </p>
                    <div style="font-size: 11px; font-family: var(--font-mono); color: #64748b; background: #f8fafc; padding: 6px 10px; border-radius: 4px; margin-bottom: 20px;">
                        <strong>Coverage:</strong> <?php echo $counts['brief']; ?> Briefs &middot; <?php echo $counts['dossier']; ?> Dossiers &middot; <?php echo $counts['update']; ?> Updates
                    </div>
                </div>

                <div style="border-top: 1px solid #f1f5f9; padding-top: 16px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 16px; font-weight: 800; font-family: var(--font-mono); color: #0f172a;">
                            <?php echo isset( $status_info['price_label'] ) ? esc_html( $status_info['price_label'] ) : '$' . number_format( $cat_amount, 2 ); ?>
                        </div>
                        <div style="font-size: 10px; color: #94a3b8; font-family: var(--font-mono);">
                            <?php echo isset( $status_info['price_label'] ) ? 'All desks included' : '/ month recurring'; ?>
                        </div>
                    </div>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <a href="<?php echo esc_url( home_url( '/intelligence-add-ons/?cat=' . $term->slug ) ); ?>" class="btn btn-ghost" style="font-size: 11px; padding: 6px 10px;">Explore &rarr;</a>
                        <?php echo $status_info['btn_html']; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else : ?>
        <div style="padding: 60px 20px; text-align: center; color: #64748b; background: #f8fafc; border-radius: 6px;">
            <p style="font-size: 15px; margin: 0;">No additional intelligence categories are currently available.</p>
        </div>
        <?php endif; ?>

        <!-- Frequently Asked Questions -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 32px; margin-top: 40px;">
            <h2 style="font-size: 20px; font-weight: 700; margin-top: 0; margin-bottom: 20px;">Storefront Frequently Asked Questions</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
                <div>
                    <h4 style="font-size: 14px; font-weight: 700; margin: 0 0 6px 0;">How do category add-ons work with my base subscription?</h4>
                    <p style="font-size: 13px; color: #475569; margin: 0; line-height: 1.5;">Category add-ons extend your base membership by unlocking specialist intelligence desks. Your base subscription tier remains completely unchanged.</p>
                </div>
                <div>
                    <h4 style="font-size: 14px; font-weight: 700; margin: 0 0 6px 0;">Does Enterprise membership include category add-ons?</h4>
                    <p style="font-size: 13px; color: #475569; margin: 0; line-height: 1.5;">Yes. Enterprise clearance provides all-inclusive access to all present and future specialist category add-on desks at zero additional charge.</p>
                </div>
                <div>
                    <h4 style="font-size: 14px; font-weight: 700; margin: 0 0 6px 0;">How does category access affect PDF exports?</h4>
                    <p style="font-size: 13px; color: #475569; margin: 0; line-height: 1.5;">When you hold an active category entitlement, full PDF export rights for intelligence briefings published under that category are automatically unlocked.</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</main>

<script>
(function() {
    var checkoutApiUrl = '<?php echo esc_url_raw( get_rest_url( null, 'ascendance/v1/category-checkout' ) ); ?>';
    var portalApiUrl   = '<?php echo esc_url_raw( get_rest_url( null, 'ascendance/v1/billing/portal-session' ) ); ?>';

    /* Purchase Add-on Handler */
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-purchase-addon');
        if (!btn) return;
        var slug = btn.dataset.slug;
        if (!slug) return;

        var origText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Redirecting to checkout...';

        fetch(checkoutApiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ category_slug: slug }),
            credentials: 'same-origin'
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.url) {
                window.location.href = res.url;
            } else {
                alert(res.error || 'Failed to initiate checkout session.');
                btn.disabled = false;
                btn.textContent = origText;
            }
        })
        .catch(function(err) {
            console.error(err);
            alert('Connection error.');
            btn.disabled = false;
            btn.textContent = origText;
        });
    });

    /* Portal Handler */
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-portal-addon');
        if (!btn) return;

        var origText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Connecting to Stripe...';

        fetch(portalApiUrl, { method: 'POST', credentials: 'same-origin' })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.url) {
                window.location.href = res.url;
            } else {
                alert(res.error || 'Failed to open portal.');
                btn.disabled = false;
                btn.textContent = origText;
            }
        })
        .catch(function(err) {
            console.error(err);
            alert('Connection error.');
            btn.disabled = false;
            btn.textContent = origText;
        });
    });

    /* URL Return Listener */
    (function handleCheckoutReturn() {
        var params = new URLSearchParams(window.location.search);
        var banner = document.getElementById('storefront-checkout-banner');
        var checkoutState = params.get('checkout');

        if (checkoutState && banner) {
            if (checkoutState === 'success') {
                banner.style.display = 'block';
                banner.style.background = 'rgba(39, 174, 96, 0.1)';
                banner.style.color = '#27ae60';
                banner.style.border = '1px solid rgba(39, 174, 96, 0.3)';
                banner.innerHTML = '✓ Payment received. Your access is being confirmed by our servers...';
            } else if (checkoutState === 'cancelled') {
                banner.style.display = 'block';
                banner.style.background = 'rgba(217, 119, 6, 0.1)';
                banner.style.color = '#d97706';
                banner.style.border = '1px solid rgba(217, 119, 6, 0.3)';
                banner.innerHTML = '⚠ Checkout canceled. No changes were made to your account.';
            }
        }
    })();
})();
</script>

<?php
get_footer();
