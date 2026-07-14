<?php
/**
 * Template Name: Contact
 * Contact page with native form and sidebar info panel.
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="site-main">

	<!-- ═══ PAGE HERO ═════════════════════════════════════════ -->
	<section class="page-hero bg-navy-deep text-white py-16 md:py-24 border-b border-brand-divider-dark">
		<div class="container mx-auto px-6 md:px-8">
			<div class="page-hero-inner">
				<p class="page-hero-eyebrow text-xs font-mono uppercase tracking-widest text-brand-red mb-4 block"><?php esc_html_e( '// Get in Touch', 'ascendance' ); ?></p>
				<h1 class="page-hero-title text-3xl md:text-5xl font-sans font-bold leading-tight mb-6"><?php esc_html_e( 'Contact Ascendance', 'ascendance' ); ?></h1>
				<p class="page-hero-desc text-base md:text-lg text-cream/80 max-w-[720px] leading-relaxed"><?php esc_html_e( 'For subscription support, media enquiries, analyst comment requests, and institutional partnership discussions.', 'ascendance' ); ?></p>
			</div>
		</div>
	</section>

	<!-- ═══ CONTACT SECTION ═══════════════════════════════════ -->
	<section class="contact-section section py-20 bg-cream dark:bg-navy-deep border-b border-brand-divider-light dark:border-brand-divider-dark">
		<div class="container mx-auto px-6 md:px-8">
			<div class="contact-grid grid grid-cols-1 lg:grid-cols-[2fr_1fr] gap-12">

				<!-- Contact Form -->
				<div class="contact-form-wrapper bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-8 rounded-sm shadow-sm reveal">
					<h2 class="text-2xl font-sans font-bold text-brand-text-primary dark:text-white mb-6 border-b border-brand-divider-light dark:border-brand-divider-dark/20 pb-4"><?php esc_html_e( 'Send a Message', 'ascendance' ); ?></h2>

					<?php
					// Use Contact Form 7 if active, fall back to native HTML form
					if ( function_exists( 'wpcf7' ) || defined( 'WPCF7_VERSION' ) ) :
						// Replace ID with your CF7 form ID after creating it in admin
						echo do_shortcode( '[contact-form-7 id="' . esc_attr( get_option( 'ascendance_contact_form_id', '' ) ) . '" title="Contact"]' );
					else :
					?>
						<form class="contact-form-native flex flex-col gap-6" id="contact-form-main" novalidate>
							<div class="form-row grid grid-cols-1 md:grid-cols-2 gap-6">
								<div class="form-group flex flex-col gap-2">
									<label for="contact-name" class="font-sans text-sm font-medium text-brand-text-muted dark:text-cream/60"><?php esc_html_e( 'Full Name', 'ascendance' ); ?></label>
									<input type="text" id="contact-name" name="name" class="w-full px-4 py-3 bg-white dark:bg-navy-deep border border-brand-divider-light dark:border-brand-divider-dark rounded-sm text-brand-text-primary dark:text-cream font-serif text-sm outline-none transition-all duration-150 focus:border-brand-red focus:shadow-[0_0_15px_rgba(188,27,29,0.3)]" placeholder="<?php esc_attr_e( 'Your full name', 'ascendance' ); ?>" required>
								</div>
								<div class="form-group flex flex-col gap-2">
									<label for="contact-email" class="font-sans text-sm font-medium text-brand-text-muted dark:text-cream/60"><?php esc_html_e( 'Email Address', 'ascendance' ); ?></label>
									<input type="email" id="contact-email" name="email" class="w-full px-4 py-3 bg-white dark:bg-navy-deep border border-brand-divider-light dark:border-brand-divider-dark rounded-sm text-brand-text-primary dark:text-cream font-serif text-sm outline-none transition-all duration-150 focus:border-brand-red focus:shadow-[0_0_15px_rgba(188,27,29,0.3)]" placeholder="<?php esc_attr_e( 'you@example.com', 'ascendance' ); ?>" required>
								</div>
							</div>

							<div class="form-group flex flex-col gap-2">
								<label for="contact-org" class="font-sans text-sm font-medium text-brand-text-muted dark:text-cream/60"><?php esc_html_e( 'Organisation (optional)', 'ascendance' ); ?></label>
								<input type="text" id="contact-org" name="organisation" class="w-full px-4 py-3 bg-white dark:bg-navy-deep border border-brand-divider-light dark:border-brand-divider-dark rounded-sm text-brand-text-primary dark:text-cream font-serif text-sm outline-none transition-all duration-150 focus:border-brand-red focus:shadow-[0_0_15px_rgba(188,27,29,0.3)]" placeholder="<?php esc_attr_e( 'Company or institution', 'ascendance' ); ?>">
							</div>

							<div class="form-group flex flex-col gap-2">
								<label for="contact-subject" class="font-sans text-sm font-medium text-brand-text-muted dark:text-cream/60"><?php esc_html_e( 'Subject', 'ascendance' ); ?></label>
								<select id="contact-subject" name="subject" class="w-full px-4 py-3 bg-white dark:bg-navy-deep border border-brand-divider-light dark:border-brand-divider-dark rounded-sm text-brand-text-primary dark:text-cream font-serif text-sm outline-none transition-all duration-150 focus:border-brand-red focus:shadow-[0_0_15px_rgba(188,27,29,0.3)]">
									<option value=""><?php esc_html_e( 'Select a subject…', 'ascendance' ); ?></option>
									<option value="subscription"><?php esc_html_e( 'Subscription & Membership', 'ascendance' ); ?></option>
									<option value="media"><?php esc_html_e( 'Media & Press Enquiry', 'ascendance' ); ?></option>
									<option value="analyst"><?php esc_html_e( 'Analyst Comment Request', 'ascendance' ); ?></option>
									<option value="enterprise"><?php esc_html_e( 'Enterprise / Institutional Partnership', 'ascendance' ); ?></option>
									<option value="intelligence"><?php esc_html_e( 'Intelligence Submission or Tip', 'ascendance' ); ?></option>
									<option value="other"><?php esc_html_e( 'Other', 'ascendance' ); ?></option>
								</select>
							</div>

							<div class="form-group flex flex-col gap-2">
								<label for="contact-message" class="font-sans text-sm font-medium text-brand-text-muted dark:text-cream/60"><?php esc_html_e( 'Message', 'ascendance' ); ?></label>
								<textarea id="contact-message" name="message" rows="6" class="w-full px-4 py-3 bg-white dark:bg-navy-deep border border-brand-divider-light dark:border-brand-divider-dark rounded-sm text-brand-text-primary dark:text-cream font-serif text-sm outline-none transition-all duration-150 focus:border-brand-red focus:shadow-[0_0_15px_rgba(188,27,29,0.3)]" placeholder="<?php esc_attr_e( 'Describe your enquiry in as much detail as useful…', 'ascendance' ); ?>" required></textarea>
							</div>

							<button type="submit" class="btn btn-primary self-start flex items-center gap-2" id="contact-submit-btn">
								<?php esc_html_e( 'Send Message', 'ascendance' ); ?>
								<i class="fa-solid fa-paper-plane text-xs"></i>
							</button>
						</form>
					<?php endif; ?>
				</div>

				<!-- Sidebar -->
				<aside class="contact-sidebar flex flex-col gap-8">

					<div class="contact-info-card bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-8 rounded-sm shadow-sm reveal reveal-delay-1">
						<h3 class="text-lg font-sans font-bold text-brand-text-primary dark:text-white mb-6 border-b border-brand-divider-light dark:border-brand-divider-dark/20 pb-4"><?php esc_html_e( 'Response Times', 'ascendance' ); ?></h3>
						<div class="contact-detail flex items-start gap-4 mb-4 text-sm text-brand-text-muted dark:text-cream/70 last:mb-0">
							<i class="fa-solid fa-clock text-brand-red mt-1"></i>
							<span><?php esc_html_e( 'Subscription support: within 1 business day', 'ascendance' ); ?></span>
						</div>
						<div class="contact-detail flex items-start gap-4 mb-4 text-sm text-brand-text-muted dark:text-cream/70 last:mb-0">
							<i class="fa-solid fa-newspaper text-brand-red mt-1"></i>
							<span><?php esc_html_e( 'Media enquiries: within 4 hours (business hours)', 'ascendance' ); ?></span>
						</div>
						<div class="contact-detail flex items-start gap-4 mb-4 text-sm text-brand-text-muted dark:text-cream/70 last:mb-0">
							<i class="fa-solid fa-building text-brand-red mt-1"></i>
							<span><?php esc_html_e( 'Enterprise proposals: within 2 business days', 'ascendance' ); ?></span>
						</div>
					</div>

					<div class="contact-info-card bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-8 rounded-sm shadow-sm reveal reveal-delay-2">
						<h3 class="text-lg font-sans font-bold text-brand-text-primary dark:text-white mb-6 border-b border-brand-divider-light dark:border-brand-divider-dark/20 pb-4"><?php esc_html_e( 'Media & Press', 'ascendance' ); ?></h3>
						<div class="contact-detail flex items-start gap-4 mb-4 text-sm text-brand-text-muted dark:text-cream/70 last:mb-0">
							<i class="fa-regular fa-envelope text-brand-red mt-1"></i>
							<div>
								<strong class="block text-brand-text-primary dark:text-white font-sans"><?php esc_html_e( 'Press Desk', 'ascendance' ); ?></strong>
								<span>press@ascendance.io</span>
							</div>
						</div>
						<div class="contact-detail flex items-start gap-4 mb-4 text-sm text-brand-text-muted dark:text-cream/70 last:mb-0">
							<i class="fa-solid fa-id-badge text-brand-red mt-1"></i>
							<div>
								<a href="<?php echo esc_url( home_url( '/about/' ) ); ?>#media" class="text-brand-red hover:text-brand-red-light transition-colors duration-150">
									<?php esc_html_e( 'Download Media Kit →', 'ascendance' ); ?>
								</a>
							</div>
						</div>
					</div>

					<div class="contact-info-card bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-8 rounded-sm shadow-sm reveal reveal-delay-3">
						<h3 class="text-lg font-sans font-bold text-brand-text-primary dark:text-white mb-6 border-b border-brand-divider-light dark:border-brand-divider-dark/20 pb-4"><?php esc_html_e( 'Intelligence Submissions', 'ascendance' ); ?></h3>
						<p class="font-serif text-sm text-brand-text-muted dark:text-cream/70 leading-relaxed mb-4">
							<?php esc_html_e( 'We accept intelligence tips, document submissions, and source introductions. All submissions are assessed by our editorial team. Source protection applies.', 'ascendance' ); ?>
						</p>
						<div class="contact-detail flex items-start gap-4 mb-4 text-sm text-brand-text-muted dark:text-cream/70 last:mb-0">
							<i class="fa-solid fa-user-secret text-brand-red mt-1"></i>
							<span class="text-brand-red font-sans font-medium"><?php esc_html_e( 'Secure submissions available on request', 'ascendance' ); ?></span>
						</div>
					</div>

				</aside>

			</div>
		</div>
	</section>

</main>

<?php get_footer(); ?>
