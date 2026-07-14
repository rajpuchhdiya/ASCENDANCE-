<?php
/**
 * The template for displaying all single Event CPT posts
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="site-main">

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
			<section class="page-hero bg-navy-deep text-white py-16 md:py-24 border-b border-brand-divider-dark">
				<div class="container mx-auto px-6 md:px-8">
					<div class="page-hero-inner">
						<p class="page-hero-eyebrow text-xs font-mono uppercase tracking-widest text-brand-red mb-4 block">// <?php echo esc_html( strtoupper( $event_type ) ); ?></p>
						<h1 class="page-hero-title text-3xl md:text-5xl font-sans font-bold leading-tight mb-6"><?php the_title(); ?></h1>
						<div class="page-hero-desc text-xs font-sans text-cream/70 flex gap-6 items-center flex-wrap mt-4 [&_i]:text-brand-red [&_i]:mr-1.5">
							<span><i class="fa-regular fa-calendar"></i><?php echo esc_html( $event_date ); ?></span>
							<span><i class="fa-solid fa-user-tie"></i><?php printf( __( 'Presenter: %s', 'ascendance' ), esc_html( $speaker ) ); ?></span>
							<span><i class="fa-solid fa-location-dot"></i><?php echo esc_html( $event_location ); ?></span>
						</div>
					</div>
				</div>
			</section>

			<!-- Content Wrapper -->
			<div class="content-wrapper py-16 md:py-24 bg-cream dark:bg-navy-deep border-b border-brand-divider-light dark:border-brand-divider-dark">
				<div class="container mx-auto px-6 md:px-8">
					<div class="contact-grid grid grid-cols-1 lg:grid-cols-[2fr_1fr] gap-12 items-start">
						
						<!-- Main Event Description (Left Column) -->
						<div class="main-content">
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="post-featured-image mb-10 max-w-full overflow-hidden border border-brand-divider-light dark:border-brand-divider-dark rounded-sm">
									<?php the_post_thumbnail( 'full' ); ?>
								</div>
							<?php endif; ?>

							<div class="entry-content text-brand-text-primary dark:text-cream leading-relaxed mb-8">
								<h2 class="text-lg font-sans font-bold text-brand-text-primary dark:text-white mb-6 border-b border-brand-divider-light dark:border-brand-divider-dark/20 pb-3">
									<?php esc_html_e( 'Briefing Overview', 'ascendance' ); ?>
								</h2>
								<?php
								the_content();
								?>
							</div>
						</div>

						<!-- Sidebar Event Info & RSVP (Right Column) -->
						<aside class="contact-sidebar flex flex-col gap-8">
							<!-- Event Details Box -->
							<div class="terminal-card bg-white dark:bg-navy-mid border-l-[3px] border-l-brand-red border-y border-r border-brand-divider-light dark:border-brand-divider-dark p-6 rounded-sm shadow-sm">
								<h3 class="text-xs font-sans font-bold uppercase tracking-wider text-brand-text-primary dark:text-white mb-6 border-b border-dashed border-brand-divider-light dark:border-brand-divider-dark/20 pb-3">
									<?php esc_html_e( 'Event Registry Details', 'ascendance' ); ?>
								</h3>
								
								<div class="flex flex-col gap-3.5 text-sm text-brand-text-muted dark:text-cream/70 line-height-1.5 font-sans">
									<div class="border-b border-brand-divider-light dark:border-brand-divider-dark/10 pb-3 last:border-b-0 last:pb-0 flex flex-col gap-1">
										<strong class="text-[10px] font-mono text-brand-text-primary dark:text-white uppercase tracking-wider"><?php esc_html_e( 'Schedule:', 'ascendance' ); ?></strong>
										<span class="font-mono text-xs text-brand-text-primary dark:text-cream"><?php echo esc_html( $event_date ); ?></span>
									</div>
									<div class="border-b border-brand-divider-light dark:border-brand-divider-dark/10 pb-3 last:border-b-0 last:pb-0 flex flex-col gap-1">
										<strong class="text-[10px] font-mono text-brand-text-primary dark:text-white uppercase tracking-wider"><?php esc_html_e( 'Access Route:', 'ascendance' ); ?></strong>
										<span class="text-brand-text-primary dark:text-cream"><?php echo esc_html( $event_location ); ?></span>
									</div>
									<div class="border-b border-brand-divider-light dark:border-brand-divider-dark/10 pb-3 last:border-b-0 last:pb-0 flex flex-col gap-1">
										<strong class="text-[10px] font-mono text-brand-text-primary dark:text-white uppercase tracking-wider"><?php esc_html_e( 'Lead Speaker:', 'ascendance' ); ?></strong>
										<span class="text-brand-text-primary dark:text-cream"><?php echo esc_html( $speaker ); ?></span>
									</div>
									<div class="border-b border-brand-divider-light dark:border-brand-divider-dark/10 pb-3 last:border-b-0 last:pb-0 flex flex-col gap-1">
										<strong class="text-[10px] font-mono text-brand-text-primary dark:text-white uppercase tracking-wider"><?php esc_html_e( 'Session Type:', 'ascendance' ); ?></strong>
										<span class="intel-card-type text-brand-red font-bold"><?php echo esc_html( $event_type ); ?></span>
									</div>
								</div>
								
								<?php if ( ! empty( $registration_url ) ) : ?>
									<div class="mt-6">
										<a href="<?php echo esc_url( $registration_url ); ?>" class="btn btn-primary w-full flex items-center justify-center gap-2" target="_blank">
											<?php esc_html_e( 'Join Briefing Session', 'ascendance' ); ?>
											<i class="fa-solid fa-arrow-up-right-from-square"></i>
										</a>
									</div>
								<?php endif; ?>
							</div>

							<!-- RSVP / Request Invitation Box -->
							<div class="contact-form-wrapper bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-6 rounded-sm shadow-sm" id="rsvp">
								<h3 class="text-xs font-sans font-bold uppercase tracking-wider text-brand-text-primary dark:text-white mb-6 border-b border-dashed border-brand-divider-light dark:border-brand-divider-dark/20 pb-3"><?php esc_html_e( 'Request Invitation', 'ascendance' ); ?></h3>
								
								<form class="contact-form-native flex flex-col gap-4" id="event-single-rsvp-form">
									<div class="form-group">
										<label for="rsvp-name" class="block text-xs font-sans font-bold text-brand-text-muted dark:text-cream/60 mb-2"><?php esc_html_e( 'Full Name', 'ascendance' ); ?></label>
										<input type="text" id="rsvp-name" name="name" class="w-full px-4 py-2.5 bg-white dark:bg-navy-deep border border-brand-divider-light dark:border-brand-divider-dark rounded-sm text-brand-text-primary dark:text-cream font-sans text-sm outline-none transition-all duration-150 focus:border-brand-red" placeholder="<?php esc_attr_e( 'Your name', 'ascendance' ); ?>" required>
									</div>
									
									<div class="form-group">
										<label for="rsvp-email" class="block text-xs font-sans font-bold text-brand-text-muted dark:text-cream/60 mb-2"><?php esc_html_e( 'Email Address', 'ascendance' ); ?></label>
										<input type="email" id="rsvp-email" name="email" class="w-full px-4 py-2.5 bg-white dark:bg-navy-deep border border-brand-divider-light dark:border-brand-divider-dark rounded-sm text-brand-text-primary dark:text-cream font-sans text-sm outline-none transition-all duration-150 focus:border-brand-red" placeholder="<?php esc_attr_e( 'you@organisation.com', 'ascendance' ); ?>" required>
									</div>

									<div class="form-group">
										<label for="rsvp-org" class="block text-xs font-sans font-bold text-brand-text-muted dark:text-cream/60 mb-2"><?php esc_html_e( 'Organization / Firm', 'ascendance' ); ?></label>
										<input type="text" id="rsvp-org" name="organisation" class="w-full px-4 py-2.5 bg-white dark:bg-navy-deep border border-brand-divider-light dark:border-brand-divider-dark rounded-sm text-brand-text-primary dark:text-cream font-sans text-sm outline-none transition-all duration-150 focus:border-brand-red" placeholder="<?php esc_attr_e( 'Company or entity', 'ascendance' ); ?>" required>
									</div>

									<input type="hidden" name="event_session" value="<?php echo esc_attr( get_the_title() ); ?>">

									<div class="form-group">
										<label for="rsvp-note" class="block text-xs font-sans font-bold text-brand-text-muted dark:text-cream/60 mb-2"><?php esc_html_e( 'Access Inquiries & Questions', 'ascendance' ); ?></label>
										<textarea id="rsvp-note" name="note" rows="3" class="w-full px-4 py-2.5 bg-white dark:bg-navy-deep border border-brand-divider-light dark:border-brand-divider-dark rounded-sm text-brand-text-primary dark:text-cream font-sans text-sm outline-none transition-all duration-150 focus:border-brand-red" placeholder="<?php esc_attr_e( 'Ask a question or request credentials check...', 'ascendance' ); ?>"></textarea>
									</div>

									<button type="submit" class="btn btn-primary w-full flex items-center justify-center gap-2" id="rsvp-submit-btn">
										<?php esc_html_e( 'Submit RSVP Inquiry', 'ascendance' ); ?>
										<i class="fa-solid fa-ticket"></i>
									</button>
								</form>
							</div>

							<!-- Security and Access Card -->
							<div class="contact-info-card bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-6 rounded-sm shadow-sm">
								<h4 class="text-[10px] font-sans font-bold uppercase tracking-widest text-brand-red mb-3"><?php esc_html_e( 'Credentials & Access Protocol', 'ascendance' ); ?></h4>
								<p class="text-xs text-brand-text-muted dark:text-cream/60 leading-relaxed">
									<?php esc_html_e( 'Roundtables and briefings are encrypted. Professional and Enterprise subscribers receive secure calendar credentials automatically. Free or Guest tier accounts require manual credential auditing before invitation approval.', 'ascendance' ); ?>
								</p>
							</div>
						</aside>

					</div>
				</div>
			</div>
		</article>

		<script>
		jQuery(document).ready(function($) {
			// Form submission simulation
			$('#event-single-rsvp-form').submit(function(e) {
				e.preventDefault();
				
				const btn = $('#rsvp-submit-btn');
				const originalHtml = btn.html();
				
				btn.attr('disabled', true).html('<i class="fa-solid fa-spinner fa-spin" style="margin-right: 8px;"></i> Submitting...');
				
				setTimeout(function() {
					btn.attr('disabled', false).html(originalHtml);
					alert('RSVP Submission Received!\nOur events desk will verify your credentials for "<?php echo esc_js( get_the_title() ); ?>" and send a secure briefing link to your email.');
					$('#event-single-rsvp-form')[0].reset();
				}, 1200);
			});
		});
		</script>

		<?php
	endwhile; // End of the loop.
	?>

</main><!-- #primary -->

<?php
get_footer();
