<?php
/**
 * The template for displaying all single Event CPT posts
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="as-page-wrap">

	<?php
	while ( have_posts() ) :
		the_post();
		
		$post_id          = get_the_ID();
		$event_date       = get_field( 'event_date', $post_id ) ?: get_the_date( 'F j, Y // H:i T' );
		$event_location   = get_field( 'event_location', $post_id ) ?: __( 'Zoom Secured Webinar', 'ascendance' );
		$event_type       = get_field( 'event_type', $post_id ) ?: __( 'Webinar Briefing', 'ascendance' );
		$registration_url = get_field( 'registration_url', $post_id );
		$speaker          = get_field( 'event_speaker', $post_id ) ?: __( 'Ascendance Lead Analyst Desk', 'ascendance' );
		?>

		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<!-- Page Hero Section -->
			<section class="as-page-hero">
				<div class="as-page-hero-inner">
					<span class="as-page-eyebrow">// <?php echo esc_html( strtoupper( $event_type ) ); ?></span>
					<h1 class="as-page-title"><?php the_title(); ?></h1>
					<p class="as-page-desc" style="display:flex; gap:16px; align-items:center; flex-wrap:wrap; opacity: 0.8; font-size: 14px;">
						<span><i class="fa-regular fa-calendar" style="margin-right:4px;"></i><?php echo esc_html( $event_date ); ?></span>
						<span><i class="fa-solid fa-user-tie" style="margin-right:4px;"></i><?php printf( __( 'Presenter: %s', 'ascendance' ), esc_html( $speaker ) ); ?></span>
						<span><i class="fa-solid fa-location-dot" style="margin-right:4px;"></i><?php echo esc_html( $event_location ); ?></span>
					</p>
				</div>
			</section>

			<!-- Content Wrapper -->
			<section class="as-contact-section">
				<div class="as-contact-wrap">
					<div class="as-contact-grid">
						
						<!-- Main Event Description (Left Column) -->
						<div class="as-contact-form-card" style="border:none; box-shadow:none; padding:0; background:transparent;">
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="post-featured-image" style="margin-bottom:30px; border-radius:4px; overflow:hidden; border: 1px solid var(--border);">
									<?php the_post_thumbnail( 'full', array( 'style' => 'width:100%; height:auto; display:block;' ) ); ?>
								</div>
							<?php endif; ?>

							<div class="entry-content">
								<h2 style="font-family:var(--font-ui); font-size:24px; font-weight:700; margin-bottom:20px; border-bottom:1px solid var(--border); padding-bottom:12px;">
									<?php esc_html_e( 'Briefing Overview', 'ascendance' ); ?>
								</h2>
								<?php
								the_content();
								?>
							</div>
						</div>

						<!-- Sidebar Event Info & RSVP (Right Column) -->
						<aside class="as-contact-sidebar">
							<!-- Event Details Box -->
							<div class="as-contact-card" style="border-left: 3px solid var(--red);">
								<h3 class="as-contact-card-title"><?php esc_html_e( 'Event Registry Details', 'ascendance' ); ?></h3>
								
								<div class="as-contact-detail" style="align-items:flex-start;">
									<div style="width:100%;">
										<strong style="display:block; font-family:var(--font-ui); font-size:11px; text-transform:uppercase; color:var(--ink-2); margin-bottom:2px;"><?php esc_html_e( 'Schedule:', 'ascendance' ); ?></strong>
										<span style="font-family:var(--font-ui); font-size:14px;"><?php echo esc_html( $event_date ); ?></span>
									</div>
								</div>
								
								<div class="as-contact-detail" style="align-items:flex-start; margin-top:15px; border-top:1px solid var(--border); padding-top:15px;">
									<div style="width:100%;">
										<strong style="display:block; font-family:var(--font-ui); font-size:11px; text-transform:uppercase; color:var(--ink-2); margin-bottom:2px;"><?php esc_html_e( 'Access Route:', 'ascendance' ); ?></strong>
										<span style="font-family:var(--font-ui); font-size:14px;"><?php echo esc_html( $event_location ); ?></span>
									</div>
								</div>

								<div class="as-contact-detail" style="align-items:flex-start; margin-top:15px; border-top:1px solid var(--border); padding-top:15px;">
									<div style="width:100%;">
										<strong style="display:block; font-family:var(--font-ui); font-size:11px; text-transform:uppercase; color:var(--ink-2); margin-bottom:2px;"><?php esc_html_e( 'Lead Speaker:', 'ascendance' ); ?></strong>
										<span style="font-family:var(--font-ui); font-size:14px;"><?php echo esc_html( $speaker ); ?></span>
									</div>
								</div>

								<div class="as-contact-detail" style="align-items:flex-start; margin-top:15px; border-top:1px solid var(--border); padding-top:15px;">
									<div style="width:100%;">
										<strong style="display:block; font-family:var(--font-ui); font-size:11px; text-transform:uppercase; color:var(--ink-2); margin-bottom:2px;"><?php esc_html_e( 'Session Type:', 'ascendance' ); ?></strong>
										<span style="font-family:var(--font-ui); font-size:14px; font-weight:700; color:var(--red); text-transform:uppercase;"><?php echo esc_html( $event_type ); ?></span>
									</div>
								</div>
								
								<?php if ( ! empty( $registration_url ) ) : ?>
									<div style="margin-top:24px;">
										<a href="<?php echo esc_url( $registration_url ); ?>" class="as-btn primary" target="_blank" style="width:100%; justify-content:center; display:flex;">
											<?php esc_html_e( 'Join Briefing Session', 'ascendance' ); ?>
										</a>
									</div>
								<?php endif; ?>
							</div>

							<!-- RSVP / Request Invitation Box -->
							<div class="as-contact-card">
								<h3 class="as-contact-card-title"><?php esc_html_e( 'Request Invitation', 'ascendance' ); ?></h3>
								
								<form id="event-single-rsvp-form" novalidate>
									<div class="as-form-group" style="margin-bottom:15px;">
										<label for="rsvp-name"><?php esc_html_e( 'Full Name', 'ascendance' ); ?></label>
										<input type="text" id="rsvp-name" name="name" placeholder="<?php esc_attr_e( 'Your name', 'ascendance' ); ?>" required>
									</div>
									
									<div class="as-form-group" style="margin-bottom:15px;">
										<label for="rsvp-email"><?php esc_html_e( 'Email Address', 'ascendance' ); ?></label>
										<input type="email" id="rsvp-email" name="email" placeholder="<?php esc_attr_e( 'you@organisation.com', 'ascendance' ); ?>" required>
									</div>

									<div class="as-form-group" style="margin-bottom:15px;">
										<label for="rsvp-org"><?php esc_html_e( 'Organization / Firm', 'ascendance' ); ?></label>
										<input type="text" id="rsvp-org" name="organisation" placeholder="<?php esc_attr_e( 'Company or entity', 'ascendance' ); ?>" required>
									</div>

									<input type="hidden" name="event_session" value="<?php echo esc_attr( get_the_title() ); ?>">

									<div class="as-form-group" style="margin-bottom:20px;">
										<label for="rsvp-note"><?php esc_html_e( 'Access Inquiries & Questions', 'ascendance' ); ?></label>
										<textarea id="rsvp-note" name="note" rows="3" placeholder="<?php esc_attr_e( 'Ask a question or request credentials check...', 'ascendance' ); ?>"></textarea>
									</div>
									
									<div id="rsvp-form-msg" style="display:none; margin-bottom:15px; padding:12px; border-radius:4px; font-weight:500; font-size:14px; font-family:var(--font-ui);"></div>

									<button type="submit" class="as-btn primary" id="rsvp-submit-btn" style="width:100%; justify-content:center; display:flex;">
										<?php esc_html_e( 'Submit RSVP Inquiry', 'ascendance' ); ?>
									</button>
								</form>
							</div>

							<!-- Security and Access Card -->
							<div class="as-contact-card">
								<h3 class="as-contact-card-title" style="color:var(--red); font-size:11px;"><?php esc_html_e( 'Credentials & Access Protocol', 'ascendance' ); ?></h3>
								<p style="font-family:var(--font-ui); font-size:13px; line-height:1.5; color:var(--ink-2); margin:0;">
									<?php esc_html_e( 'Roundtables and briefings are encrypted. Professional and Enterprise subscribers receive secure calendar credentials automatically. Free or Guest tier accounts require manual credential auditing before invitation approval.', 'ascendance' ); ?>
								</p>
							</div>
						</aside>

					</div>
				</div>
			</section>
		</article>

		<script>
		document.addEventListener('DOMContentLoaded', function() {
			var form = document.getElementById('event-single-rsvp-form');
			if (!form) return;
			form.addEventListener('submit', function(e) {
				e.preventDefault();
				var btn = document.getElementById('rsvp-submit-btn');
				var msgDiv = document.getElementById('rsvp-form-msg');
				
				var fd = new FormData(form);
				fd.append('action', 'as_submit_rsvp');
				
				msgDiv.style.display = 'none';
				btn.disabled = true;
				btn.textContent = 'Submitting...';
				
				fetch('<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>', {
					method: 'POST',
					body: fd
				})
				.then(function(res) { return res.json(); })
				.then(function(data) {
					btn.disabled = false;
					btn.textContent = 'Submit RSVP Inquiry';
					msgDiv.style.display = 'block';
					if (data.success) {
						msgDiv.style.color = '#155724';
						msgDiv.style.backgroundColor = '#d4edda';
						msgDiv.style.border = '1px solid #c3e6cb';
						msgDiv.textContent = 'RSVP Submission Received! Our events desk will verify your credentials and send a secure briefing link to your email shortly.';
						form.reset();
					} else {
						msgDiv.style.color = '#721c24';
						msgDiv.style.backgroundColor = '#f8d7da';
						msgDiv.style.border = '1px solid #f5c6cb';
						msgDiv.textContent = 'Error: ' + (data.data || 'Failed to submit.');
					}
				})
				.catch(function(err) {
					btn.disabled = false;
					btn.textContent = 'Submit RSVP Inquiry';
					msgDiv.style.display = 'block';
					msgDiv.style.color = '#721c24';
					msgDiv.style.backgroundColor = '#f8d7da';
					msgDiv.style.border = '1px solid #f5c6cb';
					msgDiv.textContent = 'An error occurred. Please try again.';
				});
			});
		});
		</script>

		<?php
	endwhile; // End of the loop.
	?>

</main><!-- #primary -->

<?php
get_footer();
