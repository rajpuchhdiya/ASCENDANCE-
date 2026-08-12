<?php
/**
 * The template for displaying all pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 * @package Ascendance
 */

get_header();

while ( have_posts() ) :
	the_post();

	$eyebrow = '// ' . strtoupper( get_the_title() );
	$desc    = '';
	$uri     = $_SERVER['REQUEST_URI'];

	if ( strpos( $uri, 'membership-account' ) !== false ) {
		$eyebrow = '// Member Portal';
		$desc    = 'Manage your active subscription tiers, billing cycles, and profile settings.';
	} elseif ( strpos( $uri, 'edit-profile' ) !== false ) {
		$eyebrow = '// Member Profile';
		$desc    = 'Update your credentials, personal info, and password settings.';
	} elseif ( strpos( $uri, 'membership-levels' ) !== false ) {
		$eyebrow = '// Subscription Plans';
		$desc    = 'Select a strategic intelligence tier to unlock full access to the Ascendance Platform.';
	} elseif ( strpos( $uri, 'membership-checkout' ) !== false ) {
		$eyebrow = '// Secure Checkout';
		$desc    = 'Review your details and finalize your credentials to activate platform access.';
	} elseif ( strpos( $uri, 'membership-billing' ) !== false ) {
		$eyebrow = '// Billing Details';
		$desc    = 'Update payment methods, view invoice history, and manage your billing settings.';
	} elseif ( strpos( $uri, 'membership-confirmation' ) !== false ) {
		$eyebrow = '// System Status: Active';
		$desc    = 'Your credentials have been provisioned. Welcome to Ascendance.';
	} else {
		$desc = get_the_excerpt();
	}
?>

<?php if ( strpos( $_SERVER['REQUEST_URI'], 'membership-levels' ) !== false ) : ?>
<main id="primary">

	<!-- Page Hero Band -->
	<section class="as-page-hero">
		<div class="as-page-hero-inner">
			<p class="as-page-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<h1 class="as-page-title"><?php the_title(); ?></h1>
			<?php if ( ! empty( $desc ) ) : ?>
				<p class="as-page-desc"><?php echo esc_html( $desc ); ?></p>
			<?php endif; ?>
		</div>
	</section>

	<!-- Subscription Tiers -->
	<section class="section" id="tiers" style="padding:20px 0 40px;">
		<?php
		$is_logged_in = is_user_logged_in();
		$user = wp_get_current_user();
		$current_tier = '';
		if ( $is_logged_in ) {
			if ( function_exists( 'pmpro_hasMembershipLevel' ) ) {
				if ( pmpro_hasMembershipLevel( 3 ) ) {
					$current_tier = 'enterprise';
				} elseif ( pmpro_hasMembershipLevel( 2 ) ) {
					$current_tier = 'professional';
				} elseif ( pmpro_hasMembershipLevel( 1 ) ) {
					$current_tier = 'essential';
				}
			}
			if ( empty( $current_tier ) ) {
				if ( in_array( 'ascendance_enterprise', (array) $user->roles ) ) {
					$current_tier = 'enterprise';
				} elseif ( in_array( 'ascendance_professional', (array) $user->roles ) ) {
					$current_tier = 'professional';
				} elseif ( in_array( 'ascendance_essential', (array) $user->roles ) ) {
					$current_tier = 'essential';
				}
			}
		}

		if ( ! function_exists( 'ascendance_get_tier_cta' ) ) {
			function ascendance_get_tier_cta( $tier_slug, $current_tier, $level_id, $btn_class, $btn_text ) {
				if ( $current_tier === $tier_slug || ( 'essential' === $tier_slug && in_array( $current_tier, array( 'essential', 'professional', 'enterprise' ), true ) ) || ( 'professional' === $tier_slug && in_array( $current_tier, array( 'professional', 'enterprise' ), true ) ) ) {
					return '<a class="' . esc_attr( $btn_class ) . '" href="' . esc_url( home_url( '/dashboard/' ) ) . '">✓ ' . esc_html__( 'Current Plan', 'ascendance' ) . '</a>';
				}
				if ( 'essential' === $tier_slug || 'professional' === $tier_slug ) {
					return '<a class="' . esc_attr( $btn_class ) . '" href="' . esc_url( home_url( '/membership-checkout/?level=' . intval( $level_id ) ) ) . '">' . esc_html( $btn_text ) . '</a>';
				}
				return '<a class="' . esc_attr( $btn_class ) . '" href="' . esc_url( home_url( '/contact/' ) ) . '">' . esc_html( $btn_text ) . '</a>';
			}
		}

		if ( ! function_exists( 'ascendance_get_pmpro_price' ) ) {
			function ascendance_get_pmpro_price( $level_id, $default_price ) {
				if ( function_exists( 'pmpro_getLevel' ) && function_exists( 'pmpro_formatPrice' ) ) {
					$level = pmpro_getLevel( $level_id );
					if ( ! empty( $level ) && isset( $level->initial_payment ) ) {
						$price = pmpro_formatPrice( $level->initial_payment );
						return str_replace( '.00', '', $price );
					}
				}
				return $default_price;
			}
		}
		?>
		<div class="wrap">
			<div class="price-grid">

				<div class="price-card">
					<div class="price-name"><?php esc_html_e( 'Essential', 'ascendance' ); ?></div>
					<?php $price_1 = ascendance_get_pmpro_price( 1, '$150' ); ?>
					<div class="price-amt"><?php echo esc_html($price_1); ?><span>/month</span></div>
					<div class="price-sub"><?php esc_html_e( 'For staying current on the partnership.', 'ascendance' ); ?></div>
					<?php echo ascendance_get_tier_cta( 'essential', $current_tier, 1, 'btn-outline-red', __( 'Start Essential', 'ascendance' ) ); ?>
					<ul class="price-list">
						<li><?php esc_html_e( 'The weekly brief, opening extract', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'The full explainer library', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'The weekly email', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'Single seat', 'ascendance' ); ?></li>
					</ul>
				</div>

				<div class="price-card featured">
					<div class="price-flag"><?php esc_html_e( 'Most chosen', 'ascendance' ); ?></div>
					<div class="price-name"><?php esc_html_e( 'Professional', 'ascendance' ); ?></div>
					<?php $price_2 = ascendance_get_pmpro_price( 2, '$299' ); ?>
					<div class="price-amt"><?php echo esc_html($price_2); ?><span>/month</span></div>
					<div class="price-sub"><?php esc_html_e( 'For principals with live DRC exposure.', 'ascendance' ); ?></div>
					<?php echo ascendance_get_tier_cta( 'professional', $current_tier, 2, 'btn-primary full', __( 'Start Professional', 'ascendance' ) ); ?>
					<ul class="price-list">
						<li><strong><?php esc_html_e( 'Everything in Essential, plus:', 'ascendance' ); ?></strong></li>
						<li><?php esc_html_e( 'The weekly brief, in full', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'All Analysis, in full', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'All Dossiers, the living files', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'All Registers, trackers and ratings', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'The full searchable archive', 'ascendance' ); ?></li>
					</ul>
				</div>

				<div class="price-card">
					<div class="price-name"><?php esc_html_e( 'Enterprise', 'ascendance' ); ?></div>
					<?php $price_3 = ascendance_get_pmpro_price( 3, '$599' ); ?>
					<div class="price-amt"><?php echo esc_html($price_3); ?><span>/month</span></div>
					<div class="price-sub"><?php esc_html_e( 'For teams and institutions.', 'ascendance' ); ?></div>
					<?php echo ascendance_get_tier_cta( 'enterprise', $current_tier, 3, 'btn-outline-red', __( 'Talk to us', 'ascendance' ) ); ?>
					<ul class="price-list">
						<li><strong><?php esc_html_e( 'Everything in Professional, plus:', 'ascendance' ); ?></strong></li>
						<li><?php esc_html_e( 'Seats across your organization', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'A named analyst contact', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'Priority consideration for Ascendance Briefings', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'Register access and data export', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'Onboarding', 'ascendance' ); ?></li>
					</ul>
				</div>
			</div>

			<p class="price-note" style="margin-top:20px;"><?php esc_html_e( 'Billed monthly. Cancel anytime; access runs to the end of the paid period. Prices exclude VAT. Enterprise is arranged through a direct conversation or custom seat allocation.', 'ascendance' ); ?></p>
		</div>
	</section>

	<!-- comparison -->
	<section class="section" style="padding-top:20px;">
		<div class="wrap">
			<div class="sec-head"><span class="kicker"><?php esc_html_e( 'Compare', 'ascendance' ); ?></span><h2><?php esc_html_e( 'What each level includes.', 'ascendance' ); ?></h2></div>
			<table class="compare">
				<thead>
					<tr><th>&nbsp;</th><th><?php esc_html_e( 'Essential', 'ascendance' ); ?></th><th class="c-feat"><?php esc_html_e( 'Professional', 'ascendance' ); ?></th><th><?php esc_html_e( 'Enterprise', 'ascendance' ); ?></th></tr>
				</thead>
				<tbody>
					<tr><td><?php esc_html_e( 'Explainer library (open to everyone)', 'ascendance' ); ?></td><td class="yes">&check;</td><td class="yes c-feat">&check;</td><td class="yes">&check;</td></tr>
					<tr><td><?php esc_html_e( 'Weekly brief, opening extract', 'ascendance' ); ?></td><td class="yes">&check;</td><td class="yes c-feat">&check;</td><td class="yes">&check;</td></tr>
					<tr><td><?php esc_html_e( 'Weekly email', 'ascendance' ); ?></td><td class="yes">&check;</td><td class="yes c-feat">&check;</td><td class="yes">&check;</td></tr>
					<tr><td><?php esc_html_e( 'Weekly brief, in full', 'ascendance' ); ?></td><td class="no">&mdash;</td><td class="yes c-feat">&check;</td><td class="yes">&check;</td></tr>
					<tr><td><?php esc_html_e( 'Analysis, in full', 'ascendance' ); ?></td><td class="no">&mdash;</td><td class="yes c-feat">&check;</td><td class="yes">&check;</td></tr>
					<tr><td><?php esc_html_e( 'Dossiers, the living files', 'ascendance' ); ?></td><td class="no">&mdash;</td><td class="yes c-feat">&check;</td><td class="yes">&check;</td></tr>
					<tr><td><?php esc_html_e( 'Registers, trackers and ratings', 'ascendance' ); ?></td><td class="no">&mdash;</td><td class="yes c-feat">&check;</td><td class="yes">&check;</td></tr>
					<tr><td><?php esc_html_e( 'Full searchable archive', 'ascendance' ); ?></td><td class="no">&mdash;</td><td class="yes c-feat">&check;</td><td class="yes">&check;</td></tr>
					<tr><td><?php esc_html_e( 'Seats', 'ascendance' ); ?></td><td>1</td><td class="c-feat">1</td><td><?php esc_html_e( 'Negotiated', 'ascendance' ); ?></td></tr>
					<tr><td><?php esc_html_e( 'Named analyst contact', 'ascendance' ); ?></td><td class="no">&mdash;</td><td class="no c-feat">&mdash;</td><td class="yes">&check;</td></tr>
					<tr><td><?php esc_html_e( 'Priority consideration for Briefings', 'ascendance' ); ?></td><td class="no">&mdash;</td><td class="no c-feat">&mdash;</td><td class="yes">&check;</td></tr>
					<tr><td><?php esc_html_e( 'Register access and data export', 'ascendance' ); ?></td><td class="no">&mdash;</td><td class="no c-feat">&mdash;</td><td class="yes">&check;</td></tr>
				</tbody>
			</table>
		</div>
	</section>

	<!-- advisory block -->
	<section class="adv-band">
		<div class="wrap">
			<div class="adv-block">
				<div class="adv-block-head">
					<span class="kicker"><?php esc_html_e( 'Beyond access', 'ascendance' ); ?></span>
					<h2><?php esc_html_e( 'Advisory', 'ascendance' ); ?></h2>
				</div>
				<div class="adv-block-body">
					<p class="adv-lead"><?php esc_html_e( 'The platform tells you what is happening. An engagement tells you what it means for your asset.', 'ascendance' ); ?></p>
					<p><?php esc_html_e( 'We work only on the US-DRC Strategic Partnership. Six services, from SAR and QSP positioning through political-risk due diligence to reform tracking. Every engagement opens with a thirty-minute diagnostic call, not a proposal, and every deliverable ships with a register of what we could not verify.', 'ascendance' ); ?></p>
					<div class="adv-actions">
						<a class="btn-primary" href="<?php echo esc_url( home_url( '/services/' ) ); ?>"><?php esc_html_e( 'Explore advisory', 'ascendance' ); ?></a>
						<a class="link-arrow" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Book a diagnostic call →', 'ascendance' ); ?></a>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- reassurance / cross-links -->
	<section class="section dark">
		<div class="wrap">
			<div class="m-contact">
				<div class="lead">
					<h2><?php esc_html_e( 'Weighing access against a mandate?', 'ascendance' ); ?></h2>
					<p><?php esc_html_e( 'Access gets you the analysis. An engagement gets you the answer to your question, on your asset, on your timeline. If you are not sure which you need, start with a call.', 'ascendance' ); ?></p>
				</div>
				<div class="acts">
					<a class="btn-light" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Book a diagnostic call', 'ascendance' ); ?></a>
					<a class="btn-outline" href="<?php echo esc_url( home_url( '/faq/' ) ); ?>"><?php esc_html_e( 'Read the FAQ', 'ascendance' ); ?></a>
				</div>
			</div>
		</div>
	</section>

</main>

<?php else : ?>
<main id="primary" class="as-page-wrap">

	<!-- Page Hero Band -->
	<section class="as-page-hero">
		<div class="as-page-hero-inner">
			<p class="as-page-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<h1 class="as-page-title"><?php the_title(); ?></h1>
			<?php if ( ! empty( $desc ) ) : ?>
				<p class="as-page-desc"><?php echo esc_html( $desc ); ?></p>
			<?php endif; ?>
		</div>
	</section>

	<!-- Page Content -->
	<div class="as-page-body">
		<div class="as-page-content">

			<?php if ( has_post_thumbnail() ) : ?>
				<div class="as-page-thumb">
					<?php the_post_thumbnail( 'full' ); ?>
				</div>
			<?php endif; ?>

			<div class="as-page-entry as-prose">
				<?php
				the_content();
				wp_link_pages( array(
					'before' => '<div class="page-links">Pages:',
					'after'  => '</div>',
				) );
				?>
			</div>

			<?php
			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;
			?>

		</div>
	</div>

</main>
<?php endif; ?>

<?php
endwhile;
get_footer();
