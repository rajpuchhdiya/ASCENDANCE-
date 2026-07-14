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
	<section class="page-hero bg-navy-deep text-white py-16 md:py-24 border-b border-brand-divider-dark">
		<div class="container mx-auto px-6 md:px-8">
			<div class="page-hero-inner">
				<p class="page-hero-eyebrow text-xs font-mono uppercase tracking-widest text-brand-red mb-4 block"><?php esc_html_e( '// Help & Support', 'ascendance' ); ?></p>
				<h1 class="page-hero-title text-3xl md:text-5xl font-sans font-bold leading-tight mb-6"><?php esc_html_e( 'Frequently Asked Questions', 'ascendance' ); ?></h1>
				<p class="page-hero-desc text-base md:text-lg text-cream/80 max-w-[720px] leading-relaxed"><?php esc_html_e( 'Everything you need to know about Ascendance — membership, content, access, and billing.', 'ascendance' ); ?></p>
			</div>
		</div>
	</section>

	<!-- ═══ FAQ SECTION ════════════════════════════════════════ -->
	<section class="faq-section section py-20 bg-cream dark:bg-navy-deep border-b border-brand-divider-light dark:border-brand-divider-dark">
		<div class="container mx-auto px-6 md:px-8">

			<!-- Category Filter Buttons -->
			<div class="faq-categories flex flex-wrap gap-3 justify-center mb-12">
				<button class="faq-cat-btn px-5 py-2.5 bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark text-sm text-brand-text-muted dark:text-cream/70 font-sans font-bold cursor-pointer rounded-sm hover:border-brand-red transition-all duration-150 active" data-cat="all" id="faq-cat-all"><?php esc_html_e( 'All Questions', 'ascendance' ); ?></button>
				<button class="faq-cat-btn px-5 py-2.5 bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark text-sm text-brand-text-muted dark:text-cream/70 font-sans font-bold cursor-pointer rounded-sm hover:border-brand-red transition-all duration-150" data-cat="membership" id="faq-cat-membership"><?php esc_html_e( 'Membership', 'ascendance' ); ?></button>
				<button class="faq-cat-btn px-5 py-2.5 bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark text-sm text-brand-text-muted dark:text-cream/70 font-sans font-bold cursor-pointer rounded-sm hover:border-brand-red transition-all duration-150" data-cat="content" id="faq-cat-content"><?php esc_html_e( 'Content', 'ascendance' ); ?></button>
				<button class="faq-cat-btn px-5 py-2.5 bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark text-sm text-brand-text-muted dark:text-cream/70 font-sans font-bold cursor-pointer rounded-sm hover:border-brand-red transition-all duration-150" data-cat="access" id="faq-cat-access"><?php esc_html_e( 'Access', 'ascendance' ); ?></button>
				<button class="faq-cat-btn px-5 py-2.5 bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark text-sm text-brand-text-muted dark:text-cream/70 font-sans font-bold cursor-pointer rounded-sm hover:border-brand-red transition-all duration-150" data-cat="platform" id="faq-cat-platform"><?php esc_html_e( 'Platform', 'ascendance' ); ?></button>
				<button class="faq-cat-btn px-5 py-2.5 bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark text-sm text-brand-text-muted dark:text-cream/70 font-sans font-bold cursor-pointer rounded-sm hover:border-brand-red transition-all duration-150" data-cat="billing" id="faq-cat-billing"><?php esc_html_e( 'Billing', 'ascendance' ); ?></button>
			</div>

			<!-- FAQ Accordion -->
			<div class="faq-list max-w-[800px] mx-auto flex flex-col gap-4" id="faq-accordion">
				<?php foreach ( $faq_items as $i => $faq ) : ?>
					<details class="faq-item bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark rounded-sm transition-all duration-300 [&[open]]:border-brand-red [&[open]]:shadow-sm" data-cat="<?php echo esc_attr( $faq['cat'] ); ?>" id="faq-item-<?php echo esc_attr( $i + 1 ); ?>">
						<summary class="faq-question flex justify-between items-center px-6 py-5 font-sans font-bold text-base text-brand-text-primary dark:text-white cursor-pointer select-none list-none marker:hidden [&::-webkit-details-marker]:hidden">
							<?php echo esc_html( $faq['q'] ); ?>
							<span class="faq-icon text-brand-red text-xs transition-transform duration-200" aria-hidden="true"><i class="fa-solid fa-plus"></i></span>
						</summary>
						<div class="faq-answer px-6 pb-6 pt-0 text-sm text-brand-text-muted dark:text-cream/70 leading-relaxed">
							<?php echo esc_html( $faq['a'] ); ?>
						</div>
					</details>
				<?php endforeach; ?>
			</div>

			<!-- Still have questions? -->
			<div class="faq-footer text-center mt-16 max-w-[600px] mx-auto border-t border-brand-divider-light dark:border-brand-divider-dark/20 pt-10">
				<p class="faq-footer-text text-base text-brand-text-muted dark:text-cream/70 mb-4">
					<?php esc_html_e( "Didn't find what you were looking for?", 'ascendance' ); ?>
				</p>
				<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn btn-secondary border border-brand-divider-light dark:border-brand-divider-dark text-brand-text-primary dark:text-cream hover:bg-brand-divider-light dark:hover:bg-brand-divider-dark flex items-center justify-center gap-2 max-w-xs mx-auto" id="faq-contact-cta">
					<?php esc_html_e( 'Contact Support', 'ascendance' ); ?>
					<i class="fa-solid fa-arrow-right text-brand-red"></i>
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
