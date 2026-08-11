<?php
/**
 * Template Name: Contact
 * Contact page with native form and sidebar info panel.
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="as-page-wrap">

	<!-- ═══ PAGE HERO ═════════════════════════════════════════ -->
	<section class="as-page-hero">
		<div class="as-page-hero-inner">
			<span class="as-page-eyebrow"><?php esc_html_e( '// Get in Touch', 'ascendance' ); ?></span>
			<h1 class="as-page-title"><?php esc_html_e( 'Contact Ascendance', 'ascendance' ); ?></h1>
			<p class="as-page-desc"><?php esc_html_e( 'For subscription support, media enquiries, analyst comment requests, and institutional partnership discussions.', 'ascendance' ); ?></p>
		</div>
	</section>

	<!-- ═══ CONTACT SECTION ═══════════════════════════════════ -->
	<section class="as-contact-section">
		<div class="as-contact-wrap">
			<div class="as-contact-grid">

				<!-- Contact Form -->
				<div class="as-contact-form-card">
					<h2 class="as-contact-form-title"><?php esc_html_e( 'Send a Message', 'ascendance' ); ?></h2>

					<?php
					// Use Contact Form 7 if active, fall back to native form
					if ( function_exists( 'wpcf7' ) || defined( 'WPCF7_VERSION' ) ) :
						echo do_shortcode( '[contact-form-7 id="' . esc_attr( get_option( 'ascendance_contact_form_id', '' ) ) . '" title="Contact"]' );
					else :
					?>
						<form id="contact-form-main" novalidate>
							<div class="as-form-row">
								<div class="as-form-group">
									<label for="contact-name"><?php esc_html_e( 'Full Name', 'ascendance' ); ?></label>
									<input type="text" id="contact-name" name="name" placeholder="<?php esc_attr_e( 'Your full name', 'ascendance' ); ?>" required>
								</div>
								<div class="as-form-group">
									<label for="contact-email"><?php esc_html_e( 'Email Address', 'ascendance' ); ?></label>
									<input type="email" id="contact-email" name="email" placeholder="<?php esc_attr_e( 'you@example.com', 'ascendance' ); ?>" required>
								</div>
							</div>

							<div class="as-form-group">
								<label for="contact-org"><?php esc_html_e( 'Organisation (optional)', 'ascendance' ); ?></label>
								<input type="text" id="contact-org" name="organisation" placeholder="<?php esc_attr_e( 'Company or institution', 'ascendance' ); ?>">
							</div>

							<div class="as-form-group">
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

							<div class="as-form-group">
								<label for="contact-message"><?php esc_html_e( 'Message', 'ascendance' ); ?></label>
								<textarea id="contact-message" name="message" rows="6" placeholder="<?php esc_attr_e( 'Describe your enquiry in as much detail as useful…', 'ascendance' ); ?>" required></textarea>
							</div>

							<div id="contact-form-msg" style="display:none; margin-bottom:15px; padding:12px; border-radius:4px; font-weight:500; font-size:14px;"></div>
							<button type="submit" class="as-btn primary" id="contact-submit-btn">
								<?php esc_html_e( 'Send Message', 'ascendance' ); ?>
							</button>
						</form>
					<?php endif; ?>
				</div>

				<!-- Sidebar -->
				<aside class="as-contact-sidebar">

					<div class="as-contact-card">
						<h3 class="as-contact-card-title"><?php esc_html_e( 'Response Times', 'ascendance' ); ?></h3>
						<div class="as-contact-detail">
							<i class="fa-solid fa-clock"></i>
							<span><?php esc_html_e( 'Subscription support: within 1 business day', 'ascendance' ); ?></span>
						</div>
						<div class="as-contact-detail">
							<i class="fa-solid fa-newspaper"></i>
							<span><?php esc_html_e( 'Media enquiries: within 4 hours (business hours)', 'ascendance' ); ?></span>
						</div>
						<div class="as-contact-detail">
							<i class="fa-solid fa-building"></i>
							<span><?php esc_html_e( 'Enterprise proposals: within 2 business days', 'ascendance' ); ?></span>
						</div>
					</div>

					<div class="as-contact-card">
						<h3 class="as-contact-card-title"><?php esc_html_e( 'Media & Press', 'ascendance' ); ?></h3>
						<div class="as-contact-detail">
							<i class="fa-regular fa-envelope"></i>
							<div>
								<strong style="display:block; color:var(--ink); font-family:var(--font-ui);"><?php esc_html_e( 'Press Desk', 'ascendance' ); ?></strong>
								<span>press@ascendance.io</span>
							</div>
						</div>
						<div class="as-contact-detail">
							<i class="fa-solid fa-id-badge"></i>
							<div>
								<a href="<?php echo esc_url( home_url( '/about/' ) ); ?>#media" style="color:var(--red); font-weight:600; text-decoration:none;">
									<?php esc_html_e( 'Download Media Kit →', 'ascendance' ); ?>
								</a>
							</div>
						</div>
					</div>

					<div class="as-contact-card">
						<h3 class="as-contact-card-title"><?php esc_html_e( 'Intelligence Submissions', 'ascendance' ); ?></h3>
						<p style="font-family:var(--font-ui); font-size:13px; line-height:1.5; color:var(--ink-2); margin-bottom:12px;">
							<?php esc_html_e( 'We accept intelligence tips, document submissions, and source introductions. All submissions are assessed by our editorial team. Source protection applies.', 'ascendance' ); ?>
						</p>
						<div class="as-contact-detail">
							<i class="fa-solid fa-user-secret"></i>
							<span style="color:var(--red); font-weight:600;"><?php esc_html_e( 'Secure submissions available on request', 'ascendance' ); ?></span>
						</div>
					</div>

				</aside>

			</div>
		</div>
	</section>

</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('contact-form-main');
    if (!form) return;
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var btn = document.getElementById('contact-submit-btn');
        var msgDiv = document.getElementById('contact-form-msg');
        var fd = new FormData(form);
        fd.append('action', 'as_submit_contact');
        
        msgDiv.style.display = 'none';
        btn.disabled = true;
        btn.textContent = 'Sending...';

        fetch('<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>', {
            method: 'POST',
            body: fd
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.textContent = 'Send Message';
            msgDiv.style.display = 'block';
            if (data.success) {
                msgDiv.style.color = '#155724';
                msgDiv.style.backgroundColor = '#d4edda';
                msgDiv.style.border = '1px solid #c3e6cb';
                msgDiv.textContent = 'Your message has been sent successfully. We will get back to you shortly.';
                form.reset();
            } else {
                msgDiv.style.color = '#721c24';
                msgDiv.style.backgroundColor = '#f8d7da';
                msgDiv.style.border = '1px solid #f5c6cb';
                msgDiv.textContent = 'Error: ' + (data.data || 'Failed to send.');
            }
        })
        .catch(function(err) {
            btn.disabled = false;
            btn.textContent = 'Send Message';
            msgDiv.style.display = 'block';
            msgDiv.style.color = '#721c24';
            msgDiv.style.backgroundColor = '#f8d7da';
            msgDiv.style.border = '1px solid #f5c6cb';
            msgDiv.textContent = 'An error occurred. Please try again.';
        });
    });
});
</script>

<?php get_footer(); ?>

