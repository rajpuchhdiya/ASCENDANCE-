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
	<section class="page-hero">
		<div class="container">
			<div class="page-hero-inner">
				<p class="page-hero-eyebrow"><?php esc_html_e( '// Get in Touch', 'ascendance' ); ?></p>
				<h1 class="page-hero-title"><?php esc_html_e( 'Contact Ascendance', 'ascendance' ); ?></h1>
				<p class="page-hero-desc"><?php esc_html_e( 'For subscription support, media enquiries, analyst comment requests, and institutional partnership discussions.', 'ascendance' ); ?></p>
			</div>
		</div>
	</section>

	<!-- ═══ CONTACT SECTION ═══════════════════════════════════ -->
	<section class="contact-section section-lg">
		<div class="container">
			<div class="contact-grid">

				<!-- Contact Form -->
				<div class="contact-form-wrapper reveal">
					<h2><?php esc_html_e( 'Send a Message', 'ascendance' ); ?></h2>

					<?php
					// Use Contact Form 7 if active, fall back to native HTML form
					if ( function_exists( 'wpcf7' ) || defined( 'WPCF7_VERSION' ) ) :
						// Replace ID with your CF7 form ID after creating it in admin
						echo do_shortcode( '[contact-form-7 id="' . esc_attr( get_option( 'ascendance_contact_form_id', '' ) ) . '" title="Contact"]' );
					else :
					?>
						<form class="contact-form-native" id="contact-form-main" novalidate>
							<div class="form-row">
								<div class="form-group">
									<label for="contact-name"><?php esc_html_e( 'Full Name', 'ascendance' ); ?></label>
									<input type="text" id="contact-name" name="name" placeholder="<?php esc_attr_e( 'Your full name', 'ascendance' ); ?>" required>
								</div>
								<div class="form-group">
									<label for="contact-email"><?php esc_html_e( 'Email Address', 'ascendance' ); ?></label>
									<input type="email" id="contact-email" name="email" placeholder="<?php esc_attr_e( 'you@example.com', 'ascendance' ); ?>" required>
								</div>
							</div>

							<div class="form-group">
								<label for="contact-org"><?php esc_html_e( 'Organisation (optional)', 'ascendance' ); ?></label>
								<input type="text" id="contact-org" name="organisation" placeholder="<?php esc_attr_e( 'Company or institution', 'ascendance' ); ?>">
							</div>

							<div class="form-group">
								<label for="contact-subject"><?php esc_html_e( 'Subject', 'ascendance' ); ?></label>
								<select id="contact-subject" name="subject">
									<option value=""><?php esc_html_e( 'Select a subject…', 'ascendance' ); ?></option>
									<option value="subscription"><?php esc_html_e( 'Subscription & Membership', 'ascendance' ); ?></option>
									<option value="media"><?php esc_html_e( 'Media & Press Enquiry', 'ascendance' ); ?></option>
									<option value="analyst"><?php esc_html_e( 'Analyst Comment Request', 'ascendance' ); ?></option>
									<option value="enterprise"><?php esc_html_e( 'Enterprise / Institutional Partnership', 'ascendance' ); ?></option>
									<option value="intelligence"><?php esc_html_e( 'Intelligence Submission or Tip', 'ascendance' ); ?></option>
									<option value="other"><?php esc_html_e( 'Other', 'ascendance' ); ?></option>
								</select>
							</div>

							<div class="form-group">
								<label for="contact-message"><?php esc_html_e( 'Message', 'ascendance' ); ?></label>
								<textarea id="contact-message" name="message" rows="6" placeholder="<?php esc_attr_e( 'Describe your enquiry in as much detail as useful…', 'ascendance' ); ?>" required></textarea>
							</div>

							<button type="submit" class="btn btn-primary" style="align-self:flex-start;" id="contact-submit-btn">
								<?php esc_html_e( 'Send Message', 'ascendance' ); ?>
								<i class="fa-solid fa-paper-plane" style="margin-left:8px;"></i>
							</button>
						</form>
					<?php endif; ?>
				</div>

				<!-- Sidebar -->
				<aside class="contact-sidebar">

					<div class="contact-info-card reveal reveal-delay-1">
						<h3><?php esc_html_e( 'Response Times', 'ascendance' ); ?></h3>
						<div class="contact-detail">
							<i class="fa-solid fa-clock"></i>
							<span><?php esc_html_e( 'Subscription support: within 1 business day', 'ascendance' ); ?></span>
						</div>
						<div class="contact-detail">
							<i class="fa-solid fa-newspaper"></i>
							<span><?php esc_html_e( 'Media enquiries: within 4 hours (business hours)', 'ascendance' ); ?></span>
						</div>
						<div class="contact-detail">
							<i class="fa-solid fa-building"></i>
							<span><?php esc_html_e( 'Enterprise proposals: within 2 business days', 'ascendance' ); ?></span>
						</div>
					</div>

					<div class="contact-info-card reveal reveal-delay-2">
						<h3><?php esc_html_e( 'Media & Press', 'ascendance' ); ?></h3>
						<div class="contact-detail">
							<i class="fa-regular fa-envelope"></i>
							<div>
								<strong style="display:block;color:var(--color-cream);"><?php esc_html_e( 'Press Desk', 'ascendance' ); ?></strong>
								<span>press@ascendance.io</span>
							</div>
						</div>
						<div class="contact-detail">
							<i class="fa-solid fa-id-badge"></i>
							<div>
								<a href="<?php echo esc_url( home_url( '/about/' ) ); ?>#media" style="color:var(--color-red);">
									<?php esc_html_e( 'Download Media Kit →', 'ascendance' ); ?>
								</a>
							</div>
						</div>
					</div>

					<div class="contact-info-card reveal reveal-delay-3">
						<h3><?php esc_html_e( 'Intelligence Submissions', 'ascendance' ); ?></h3>
						<p style="font-family:var(--font-body);font-size:0.85rem;color:rgba(247,244,239,0.55);line-height:1.6;">
							<?php esc_html_e( 'We accept intelligence tips, document submissions, and source introductions. All submissions are assessed by our editorial team. Source protection applies.', 'ascendance' ); ?>
						</p>
						<div class="contact-detail" style="margin-top:var(--space-3);">
							<i class="fa-solid fa-user-secret"></i>
							<span style="color:var(--color-red);"><?php esc_html_e( 'Secure submissions available on request', 'ascendance' ); ?></span>
						</div>
					</div>

				</aside>

			</div>
		</div>
	</section>

</main>

<?php get_footer(); ?>
