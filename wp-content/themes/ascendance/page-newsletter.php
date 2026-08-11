<?php
/**
 * Template Name: Newsletter
 * Newsletter subscription page with sample preview.
 *
 * @package Ascendance
 */

get_header();

// Grab the latest brief for sample preview
$sample_brief = new WP_Query( array(
	'post_type'      => 'brief',
	'posts_per_page' => 1,
	'post_status'    => 'publish',
	'orderby'        => 'date',
	'order'          => 'DESC',
) );
?>

<main id="primary" class="as-page-wrap">

	<!-- ═══ NEWSLETTER HERO ════════════════════════════════════ -->
	<section class="as-nl-hero" id="subscribe">
		<div class="as-nl-hero-inner">
			<span class="as-page-eyebrow"><?php esc_html_e( 'Free Weekly Intelligence', 'ascendance' ); ?></span>
			<h1 class="as-page-title"><?php esc_html_e( 'The Weekly Intelligence Brief', 'ascendance' ); ?></h1>
			<p class="as-page-desc" style="margin:0 auto 16px;"><?php esc_html_e( 'Every week, Ascendance distils the most significant geopolitical, economic, and technology developments into a concise, actionable intelligence brief — delivered directly to your inbox. Free for the first 30 days.', 'ascendance' ); ?></p>

			<form class="as-nl-form" id="newsletter-form-hero" novalidate>
				<input type="email" id="newsletter-email-hero" name="email" placeholder="<?php esc_attr_e( 'Your email address', 'ascendance' ); ?>" required>
				<button type="submit" class="as-btn primary"><?php esc_html_e( 'Subscribe Free', 'ascendance' ); ?></button>
			</form>
			<div id="newsletter-hero-msg" style="display:none; margin-top:15px; padding:12px; border-radius:4px; font-weight:500; font-size:14px; font-family:var(--font-ui); text-align:left;"></div>

			<div class="as-nl-benefits">
				<span><i class="fa-regular fa-calendar"></i><?php esc_html_e( 'Published every Monday', 'ascendance' ); ?></span>
				<span><i class="fa-solid fa-lock-open"></i><?php esc_html_e( 'Free 30-day trial', 'ascendance' ); ?></span>
				<span><i class="fa-solid fa-shield"></i><?php esc_html_e( 'No spam. Unsubscribe anytime.', 'ascendance' ); ?></span>
			</div>
		</div>
	</section>

	<!-- ═══ BENEFITS ══════════════════════════════════════════ -->
	<section class="as-about-section">
		<div class="as-about-wrap">
			<div style="max-width:640px; margin:0 auto 32px; text-align:center;">
				<span class="as-sec-eyebrow"><?php esc_html_e( 'What You Get', 'ascendance' ); ?></span>
				<h2 class="as-sec-title"><?php esc_html_e( 'Every Issue Includes', 'ascendance' ); ?></h2>
			</div>

			<div class="as-pillars-grid">
				<div class="as-pillar-card">
					<div class="as-pillar-icon"><i class="fa-solid fa-bullseye"></i></div>
					<h3 class="as-pillar-title"><?php esc_html_e( 'The Lead Brief', 'ascendance' ); ?></h3>
					<p class="as-pillar-desc"><?php esc_html_e( "This week's most significant development — with our analytical claim, key findings, and 90-day forecast. The brief you need to understand the story behind the headlines.", 'ascendance' ); ?></p>
				</div>
				<div class="as-pillar-card">
					<div class="as-pillar-icon"><i class="fa-solid fa-satellite-dish"></i></div>
					<h3 class="as-pillar-title"><?php esc_html_e( 'Signal / Noise Digest', 'ascendance' ); ?></h3>
					<p class="as-pillar-desc"><?php esc_html_e( "Five rapid-fire intelligence updates from the week — scored High, Medium, or Low impact. Distilled to 30 seconds each so you can process the full picture fast.", 'ascendance' ); ?></p>
				</div>
				<div class="as-pillar-card">
					<div class="as-pillar-icon"><i class="fa-solid fa-compass"></i></div>
					<h3 class="as-pillar-title"><?php esc_html_e( 'The Analyst Take', 'ascendance' ); ?></h3>
					<p class="as-pillar-desc"><?php esc_html_e( "One paragraph from our senior analysts on what they're watching for this week — a forward signal that often doesn't surface in mainstream coverage for weeks.", 'ascendance' ); ?></p>
				</div>
			</div>
		</div>
	</section>

	<!-- ═══ SAMPLE PREVIEW ════════════════════════════════════ -->
	<?php if ( $sample_brief->have_posts() ) : $sample_brief->the_post(); ?>
	<section class="as-about-section alt">
		<div class="as-about-wrap">
			<div class="as-nl-preview-grid">

				<div>
					<span class="as-sec-eyebrow"><?php esc_html_e( 'Sample Edition', 'ascendance' ); ?></span>
					<h2 class="as-sec-title"><?php esc_html_e( 'What a Typical Brief Looks Like', 'ascendance' ); ?></h2>
					<p style="font-family:var(--font-body); font-size:16.5px; line-height:1.65; color:var(--ink-2); margin:0 0 24px;"><?php esc_html_e( "Here's a look inside a recent Ascendance Intelligence Brief — the kind of structured analysis you'll receive every week.", 'ascendance' ); ?></p>
					<a href="<?php the_permalink(); ?>" class="as-btn primary" id="newsletter-sample-read">
						<?php esc_html_e( 'Read Full Brief', 'ascendance' ); ?>
					</a>
				</div>

				<div class="as-nl-preview-card">
					<div class="as-nl-preview-header">
						<span style="color:var(--red); font-weight:700;"><?php esc_html_e( 'INTELLIGENCE BRIEF', 'ascendance' ); ?></span>
						<span><?php echo get_the_date( 'j M Y' ); ?></span>
					</div>

					<div style="margin-bottom:12px;">
						<?php
						$tier_access = function_exists( 'get_field' ) ? get_field( 'tier_access' ) : get_post_meta( get_the_ID(), 'tier_access', true );
						if ( $tier_access ) echo ascendance_tier_badge( $tier_access ); // phpcs:ignore
						?>
					</div>

					<h3 class="as-nl-preview-title"><?php the_title(); ?></h3>

					<?php
					$claim = function_exists( 'get_field' ) ? get_field( 'analytical_claim' ) : get_post_meta( get_the_ID(), 'analytical_claim', true );
					if ( $claim ) : ?>
						<div class="as-nl-preview-claim">
							<strong>CLAIM //</strong>
							<?php echo esc_html( wp_trim_words( $claim, 30, '…' ) ); ?>
						</div>
					<?php endif; ?>

					<p style="font-family:var(--font-ui); font-size:13.5px; line-height:1.6; color:var(--on-ink-muted); margin:0 0 16px;">
						<?php
						$excerpt = get_field( 'public_excerpt' );
						if ( ! $excerpt ) $excerpt = get_the_excerpt();
						echo esc_html( wp_trim_words( $excerpt, 40, '…' ) );
						?>
					</p>

					<div class="as-nl-preview-footer">
						<span style="letter-spacing:0.1em; text-transform:uppercase; color:var(--on-ink-muted);">
							<?php echo get_the_terms( get_the_ID(), 'topic' ) ? esc_html( get_the_terms( get_the_ID(), 'topic' )[0]->name ) : ''; ?>
						</span>
						<span style="color:var(--red); font-weight:700;">
							<?php esc_html_e( 'Full brief available to subscribers →', 'ascendance' ); ?>
						</span>
					</div>
				</div>

			</div>
		</div>
	</section>
	<?php wp_reset_postdata(); endif; ?>

	<!-- ═══ BOTTOM SUBSCRIBE CTA ══════════════════════════════ -->
	<section class="as-nl-hero">
		<div class="as-nl-hero-inner">
			<h2 class="as-page-title" style="font-size:clamp(22px,3vw,32px);"><?php esc_html_e( 'Start Your Free 30-Day Trial', 'ascendance' ); ?></h2>
			<p class="as-page-desc" style="margin:0 auto 16px; font-size:15px;"><?php esc_html_e( 'No credit card required. Full Essential-tier access for 30 days.', 'ascendance' ); ?></p>
			<form class="as-nl-form" id="newsletter-form-bottom" novalidate>
				<input type="email" id="newsletter-email-bottom" name="email" placeholder="<?php esc_attr_e( 'Your email address', 'ascendance' ); ?>" required>
				<button type="submit" class="as-btn primary"><?php esc_html_e( 'Start Free', 'ascendance' ); ?></button>
			</form>
			<div id="newsletter-bottom-msg" style="display:none; margin-top:15px; padding:12px; border-radius:4px; font-weight:500; font-size:14px; font-family:var(--font-ui); text-align:left;"></div>
		</div>
	</section>

</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
	function handleNewsletterSubmit(formId, msgId, btnText) {
		var form = document.getElementById(formId);
		if (!form) return;
		form.addEventListener('submit', function(e) {
			e.preventDefault();
			var btn = form.querySelector('button[type="submit"]');
			var msgDiv = document.getElementById(msgId);
			var fd = new FormData(form);
			fd.append('action', 'as_submit_newsletter');
			
			msgDiv.style.display = 'none';
			btn.disabled = true;
			btn.textContent = 'Subscribing...';
			
			fetch('<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>', {
				method: 'POST',
				body: fd
			})
			.then(function(res) { return res.json(); })
			.then(function(data) {
				btn.disabled = false;
				btn.textContent = btnText;
				msgDiv.style.display = 'block';
				if (data.success) {
					msgDiv.style.color = '#155724';
					msgDiv.style.backgroundColor = '#d4edda';
					msgDiv.style.border = '1px solid #c3e6cb';
					msgDiv.textContent = 'Success! You have been subscribed. Check your email for confirmation.';
					form.reset();
				} else {
					msgDiv.style.color = '#721c24';
					msgDiv.style.backgroundColor = '#f8d7da';
					msgDiv.style.border = '1px solid #f5c6cb';
					msgDiv.textContent = 'Error: ' + (data.data || 'Failed to subscribe.');
				}
			})
			.catch(function(err) {
				btn.disabled = false;
				btn.textContent = btnText;
				msgDiv.style.display = 'block';
				msgDiv.style.color = '#721c24';
				msgDiv.style.backgroundColor = '#f8d7da';
				msgDiv.style.border = '1px solid #f5c6cb';
				msgDiv.textContent = 'An error occurred. Please try again.';
			});
		});
	}

	handleNewsletterSubmit('newsletter-form-hero', 'newsletter-hero-msg', 'Subscribe Free');
	handleNewsletterSubmit('newsletter-form-bottom', 'newsletter-bottom-msg', 'Start Free');
});
</script>

<?php get_footer(); ?>

