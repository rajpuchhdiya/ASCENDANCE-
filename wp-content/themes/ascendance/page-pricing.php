<?php
/**
 * Template Name: Pricing & Subscribe
 *
 * @package Ascendance
 */

get_header();
?>

<main>
<!-- hero -->
<section class="price-hero">
	<div class="wrap">
		<span class="kicker"><?php esc_html_e( 'Subscribe', 'ascendance' ); ?></span>
		<h1><?php esc_html_e( 'The US-DRC Strategic Partnership, read first.', 'ascendance' ); ?></h1>
		<p><?php esc_html_e( 'One subject, covered in depth: reform, reserves, and the Lobito Corridor. Choose the access your work requires. The explainer library stays open to everyone.', 'ascendance' ); ?></p>
	</div>
</section>

<!-- tiers -->
<section class="section" id="tiers" style="padding-top:44px;">
	<div class="wrap">
		<div class="price-grid">

			<div class="price-card">
				<div class="price-name"><?php esc_html_e( 'Essential', 'ascendance' ); ?></div>
				<?php
				$level_1 = function_exists('pmpro_getLevel') ? pmpro_getLevel(1) : null;
				$price_1 = $level_1 ? '$' . intval($level_1->initial_payment) : '$150';
				?>
				<div class="price-amt"><?php echo esc_html($price_1); ?><span>/month</span></div>
				<div class="price-sub"><?php esc_html_e( 'For staying current on the partnership.', 'ascendance' ); ?></div>
				<a class="btn-outline-red" href="<?php echo esc_url( function_exists( 'pmpro_url' ) ? pmpro_url( 'checkout', '?level=1' ) : home_url( '/membership-checkout/?pmpro_level=1' ) ); ?>"><?php esc_html_e( 'Start Essential', 'ascendance' ); ?></a>
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
				<?php
				$level_2 = function_exists('pmpro_getLevel') ? pmpro_getLevel(2) : null;
				$price_2 = $level_2 ? '$' . intval($level_2->initial_payment) : '$299';
				?>
				<div class="price-amt"><?php echo esc_html($price_2); ?><span>/month</span></div>
				<div class="price-sub"><?php esc_html_e( 'For principals with live DRC exposure.', 'ascendance' ); ?></div>
				<a class="btn-primary full" href="<?php echo esc_url( function_exists( 'pmpro_url' ) ? pmpro_url( 'checkout', '?level=2' ) : home_url( '/membership-checkout/?pmpro_level=2' ) ); ?>"><?php esc_html_e( 'Start Professional', 'ascendance' ); ?></a>
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
				<?php
				$level_3 = function_exists('pmpro_getLevel') ? pmpro_getLevel(3) : null;
				$price_3 = $level_3 ? '$' . intval($level_3->initial_payment) : '$599';
				?>
				<div class="price-amt"><?php echo esc_html($price_3); ?><span>/month</span></div>
				<div class="price-sub"><?php esc_html_e( 'For teams and institutions.', 'ascendance' ); ?></div>
				<a class="btn-outline-red" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Talk to us', 'ascendance' ); ?></a>
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

		<p class="price-note"><?php esc_html_e( 'Billed monthly. Cancel anytime; access runs to the end of the paid period. Prices exclude VAT. Enterprise is arranged through a direct conversation or custom seat allocation.', 'ascendance' ); ?></p>
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

<?php
get_footer();

