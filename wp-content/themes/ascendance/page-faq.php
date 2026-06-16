<?php
/**
 * Template Name: FAQ
 * Accordion FAQ with category filter.
 *
 * @package Ascendance
 */

get_header();

$faq_items = array(
	// Membership
	array( 'cat' => 'membership', 'q' => 'How do the three membership tiers differ?', 'a' => 'Essential gives you two Intelligence Briefs per week and daily Dynamic Updates for one region. Professional unlocks unlimited briefs, all regions, and read access to Strategic Dossiers. Enterprise adds REST API access, bespoke intelligence requests, a dedicated analyst relationship, and white-label export capabilities. See our Services page for the full access matrix.' ),
	array( 'cat' => 'membership', 'q' => 'Is there a free trial available?', 'a' => 'Yes — all new subscribers receive a 30-day free trial with full Essential-tier access. No credit card is required to start the trial. At the end of 30 days, you can choose to subscribe or your account reverts to the free newsletter tier.' ),
	array( 'cat' => 'membership', 'q' => 'Can I upgrade or downgrade my tier?', 'a' => 'Tier changes take effect at the start of your next billing cycle. Upgrades can be made immediately and prorated access is granted from the upgrade date. Downgrades are scheduled for the next cycle renewal.' ),
	array( 'cat' => 'membership', 'q' => 'Do you offer institutional or team pricing?', 'a' => 'Enterprise tier supports multi-seat licensing for teams of 5 or more. Contact us via the Contact page with your team size and requirements for a custom quote. Academic and non-profit discounts are also available.' ),
	// Content
	array( 'cat' => 'content', 'q' => 'How often is new intelligence published?', 'a' => 'Intelligence Briefs are published 2–4 times per week. Dynamic Updates are published as events warrant — typically 3–8 per week. Strategic Dossiers are updated monthly, with ad-hoc interim updates during significant developments.' ),
	array( 'cat' => 'content', 'q' => 'What topics and regions does Ascendance cover?', 'a' => 'We cover six strategic sectors: Geopolitics & Diplomacy, Economics & Markets, Technology & AI, Energy & Resources, Security & Defence, and Governance & Policy. Regional coverage spans all major geopolitical theatres including Indo-Pacific, Europe, Middle East, Africa, and the Americas.' ),
	array( 'cat' => 'content', 'q' => 'How does Ascendance ensure analytical quality?', 'a' => 'Every Intelligence Brief opens with a falsifiable analytical claim — a testable forward-looking statement with an explicit confidence rating. We source all major claims, disclose our methodology, and publish a corrections log for any material errors. All analysts have professional intelligence or policy backgrounds.' ),
	array( 'cat' => 'content', 'q' => 'Can I request coverage of a specific topic or region?', 'a' => 'Professional and Enterprise subscribers can submit topic or regional intelligence requests. Enterprise subscribers receive bespoke intelligence research as a dedicated service. Submit requests through the Contact page or your subscriber dashboard.' ),
	// Access
	array( 'cat' => 'access', 'q' => 'How do paywalled articles work?', 'a' => 'Each brief and dossier has a public excerpt visible to all visitors — typically 50–80 words that surface the core analytical claim. Full access to key findings, detailed analysis, and executive summaries requires a matching or higher membership tier than the brief\'s designated tier access level.' ),
	array( 'cat' => 'access', 'q' => 'Can I share content with colleagues?', 'a' => 'Individual subscriptions permit personal use only. To share access, upgrade to Enterprise tier which supports multi-seat licensing. White-label PDF exports are available for Enterprise subscribers who need to distribute intelligence internally.' ),
	array( 'cat' => 'access', 'q' => 'Is there API access for programmatic integration?', 'a' => 'Enterprise tier subscribers gain access to the Ascendance REST API. The API provides JSON endpoints for briefs, updates, dossiers, and taxonomy structures — enabling integration with your organisation\'s internal tools, dashboards, and knowledge management systems. Documentation is available in the subscriber dashboard.' ),
	// Platform
	array( 'cat' => 'platform', 'q' => 'What device and browser support is available?', 'a' => 'Ascendance is a responsive web platform that works across all modern browsers (Chrome, Firefox, Safari, Edge) and device types (desktop, tablet, mobile). A dedicated mobile-optimised experience is included in all tiers. Offline reading and native mobile apps are on the development roadmap.' ),
	array( 'cat' => 'platform', 'q' => 'How is my data protected?', 'a' => 'Subscriber data is stored with encryption at rest and in transit. We do not sell or share subscriber data with third parties. Our full privacy policy is available at the footer of every page. We are GDPR-compliant for European subscribers and follow equivalent data protection standards globally.' ),
	// Billing
	array( 'cat' => 'billing', 'q' => 'What payment methods are accepted?', 'a' => 'We accept all major credit and debit cards (Visa, Mastercard, Amex) as well as PayPal for individual subscriptions. Enterprise invoicing (net 30) is available for annual Enterprise contracts of $2,500 or above.' ),
	array( 'cat' => 'billing', 'q' => 'What is your refund policy?', 'a' => 'If you\'re unsatisfied within the first 30 days of a paid subscription, contact us for a full refund — no questions asked. After 30 days, refunds are issued on a pro-rated basis for unused subscription time, subject to a 7-day minimum hold.' ),
);
?>

<main id="primary" class="site-main">

	<!-- ═══ PAGE HERO ═════════════════════════════════════════ -->
	<section class="page-hero">
		<div class="container">
			<div class="page-hero-inner">
				<p class="page-hero-eyebrow"><?php esc_html_e( '// Help & Support', 'ascendance' ); ?></p>
				<h1 class="page-hero-title"><?php esc_html_e( 'Frequently Asked Questions', 'ascendance' ); ?></h1>
				<p class="page-hero-desc"><?php esc_html_e( 'Everything you need to know about Ascendance — membership, content, access, and billing.', 'ascendance' ); ?></p>
			</div>
		</div>
	</section>

	<!-- ═══ FAQ SECTION ════════════════════════════════════════ -->
	<section class="faq-section section-lg">
		<div class="container">

			<!-- Category Filter Buttons -->
			<div class="faq-categories">
				<button class="faq-cat-btn active" data-cat="all" id="faq-cat-all"><?php esc_html_e( 'All Questions', 'ascendance' ); ?></button>
				<button class="faq-cat-btn" data-cat="membership" id="faq-cat-membership"><?php esc_html_e( 'Membership', 'ascendance' ); ?></button>
				<button class="faq-cat-btn" data-cat="content" id="faq-cat-content"><?php esc_html_e( 'Content', 'ascendance' ); ?></button>
				<button class="faq-cat-btn" data-cat="access" id="faq-cat-access"><?php esc_html_e( 'Access', 'ascendance' ); ?></button>
				<button class="faq-cat-btn" data-cat="platform" id="faq-cat-platform"><?php esc_html_e( 'Platform', 'ascendance' ); ?></button>
				<button class="faq-cat-btn" data-cat="billing" id="faq-cat-billing"><?php esc_html_e( 'Billing', 'ascendance' ); ?></button>
			</div>

			<!-- FAQ Accordion -->
			<div class="faq-list" id="faq-accordion">
				<?php foreach ( $faq_items as $i => $faq ) : ?>
					<details class="faq-item" data-cat="<?php echo esc_attr( $faq['cat'] ); ?>" id="faq-item-<?php echo esc_attr( $i + 1 ); ?>">
						<summary class="faq-question">
							<?php echo esc_html( $faq['q'] ); ?>
							<span class="faq-icon" aria-hidden="true"><i class="fa-solid fa-plus"></i></span>
						</summary>
						<div class="faq-answer">
							<?php echo esc_html( $faq['a'] ); ?>
						</div>
					</details>
				<?php endforeach; ?>
			</div>

			<!-- Still have questions? -->
			<div style="text-align:center;margin-top:var(--space-12);padding-top:var(--space-8);border-top:1px solid var(--color-divider-dark);">
				<p style="font-family:var(--font-heading);font-size:0.9rem;color:rgba(247,244,239,0.5);margin-bottom:var(--space-4);">
					<?php esc_html_e( "Didn't find what you were looking for?", 'ascendance' ); ?>
				</p>
				<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn btn-secondary" id="faq-contact-cta">
					<?php esc_html_e( 'Contact Support', 'ascendance' ); ?>
					<i class="fa-solid fa-arrow-right" style="margin-left:8px;color:var(--color-red);"></i>
				</a>
			</div>
		</div>
	</section>

	<?php get_template_part( 'template-parts/cta-strip', null, array(
		'heading'   => __( 'Ready to Start Your Free Trial?', 'ascendance' ),
		'body'      => __( '30 days free. No credit card required. Cancel at any time.', 'ascendance' ),
		'btn_label' => __( 'Start Free Trial', 'ascendance' ),
		'btn_url'   => home_url( '/newsletter/' ),
		'btn2_label' => __( 'Contact Us', 'ascendance' ),
		'btn2_url'  => home_url( '/contact/' ),
	) ); ?>

</main>

<?php get_footer(); ?>
