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

<main id="primary" class="site-main">

	<!-- ═══ NEWSLETTER HERO ════════════════════════════════════ -->
	<section class="newsletter-hero-section" id="subscribe">
		<div class="container">
			<div class="newsletter-hero-inner">
				<span class="section-eyebrow"><?php esc_html_e( 'Free Weekly Intelligence', 'ascendance' ); ?></span>
				<h1 class="hero-title" style="font-size:clamp(2rem,4vw,3rem);margin-bottom:var(--space-4);">
					<?php esc_html_e( 'The Weekly Intelligence Brief', 'ascendance' ); ?>
				</h1>
				<p style="font-family:var(--font-body);font-size:1.05rem;color:rgba(247,244,239,0.6);line-height:1.7;margin-bottom:0;">
					<?php esc_html_e( 'Every week, Ascendance distils the most significant geopolitical, economic, and technology developments into a concise, actionable intelligence brief — delivered directly to your inbox. Free for the first 30 days.', 'ascendance' ); ?>
				</p>

				<form class="newsletter-form-inline" id="newsletter-form-hero" novalidate>
					<input type="email" id="newsletter-email-hero" name="email" placeholder="<?php esc_attr_e( 'Enter your email address', 'ascendance' ); ?>" required>
					<button type="submit"><?php esc_html_e( 'Subscribe Free', 'ascendance' ); ?></button>
				</form>

				<div class="newsletter-benefits">
					<span class="newsletter-benefit"><i class="fa-regular fa-calendar"></i><?php esc_html_e( 'Published every Monday', 'ascendance' ); ?></span>
					<span class="newsletter-benefit"><i class="fa-solid fa-lock-open"></i><?php esc_html_e( 'Free 30-day trial', 'ascendance' ); ?></span>
					<span class="newsletter-benefit"><i class="fa-solid fa-shield"></i><?php esc_html_e( 'No spam. Unsubscribe anytime.', 'ascendance' ); ?></span>
				</div>
			</div>
		</div>
	</section>

	<!-- ═══ BENEFITS ══════════════════════════════════════════ -->
	<section class="pillars-section section" style="background:var(--color-navy-mid);">
		<div class="container">
			<div class="section-header" style="text-align:center;max-width:520px;margin:0 auto var(--space-8);">
				<span class="section-eyebrow"><?php esc_html_e( 'What You Get', 'ascendance' ); ?></span>
				<h2 class="section-title reveal"><?php esc_html_e( 'Every Issue Includes', 'ascendance' ); ?></h2>
			</div>

			<div class="pillars-grid">
				<div class="pillar-card reveal">
					<div class="pillar-icon"><i class="fa-solid fa-bullseye"></i></div>
					<h3><?php esc_html_e( 'The Lead Brief', 'ascendance' ); ?></h3>
					<p><?php esc_html_e( "This week's most significant development — with our analytical claim, key findings, and 90-day forecast. The brief you need to understand the story behind the headlines.", 'ascendance' ); ?></p>
				</div>
				<div class="pillar-card reveal reveal-delay-1">
					<div class="pillar-icon"><i class="fa-solid fa-satellite-dish"></i></div>
					<h3><?php esc_html_e( 'Signal / Noise Digest', 'ascendance' ); ?></h3>
					<p><?php esc_html_e( "Five rapid-fire intelligence updates from the week — scored High, Medium, or Low impact. Distilled to 30 seconds each so you can process the full picture fast.", 'ascendance' ); ?></p>
				</div>
				<div class="pillar-card reveal reveal-delay-2">
					<div class="pillar-icon"><i class="fa-solid fa-compass"></i></div>
					<h3><?php esc_html_e( 'The Analyst Take', 'ascendance' ); ?></h3>
					<p><?php esc_html_e( "One paragraph from our senior analysts on what they\'re watching for this week — a forward signal that often doesn't surface in mainstream coverage for weeks.", 'ascendance' ); ?></p>
				</div>
			</div>
		</div>
	</section>

	<!-- ═══ SAMPLE PREVIEW ════════════════════════════════════ -->
	<?php if ( $sample_brief->have_posts() ) : $sample_brief->the_post(); ?>
	<section class="newsletter-preview-section">
		<div class="container">
			<div class="newsletter-preview-grid">

				<div class="reveal">
					<span class="section-eyebrow" style="color:var(--color-red);"><?php esc_html_e( 'Sample Edition', 'ascendance' ); ?></span>
					<h2 class="section-title dark reveal"><?php esc_html_e( 'What a Typical Brief Looks Like', 'ascendance' ); ?></h2>
					<p class="section-lead dark"><?php esc_html_e( "Here's a look inside a recent Ascendance Intelligence Brief — the kind of structured analysis you'll receive every week.", 'ascendance' ); ?></p>
					<a href="<?php the_permalink(); ?>" class="btn btn-primary" id="newsletter-sample-read" style="margin-top:var(--space-4);">
						<?php esc_html_e( 'Read Full Brief', 'ascendance' ); ?>
					</a>
				</div>

				<div class="newsletter-preview-card reveal reveal-delay-1">
					<div class="newsletter-preview-header">
						<span class="newsletter-preview-label"><?php esc_html_e( 'INTELLIGENCE BRIEF', 'ascendance' ); ?></span>
						<span class="newsletter-preview-date"><?php echo get_the_date( 'j M Y' ); ?></span>
					</div>

					<?php
					$tier_access = function_exists( 'get_field' ) ? get_field( 'tier_access' ) : get_post_meta( get_the_ID(), 'tier_access', true );
					if ( $tier_access ) echo ascendance_tier_badge( $tier_access ); // phpcs:ignore
					?>

					<h3 style="font-family:var(--font-heading);font-size:1rem;color:var(--color-white);margin:var(--space-3) 0 var(--space-2);line-height:1.35;"><?php the_title(); ?></h3>

					<?php
					$claim = function_exists( 'get_field' ) ? get_field( 'analytical_claim' ) : get_post_meta( get_the_ID(), 'analytical_claim', true );
					if ( $claim ) : ?>
						<div style="font-family:var(--font-mono);font-size:0.72rem;color:#00FF66;background:#030810;padding:10px 14px;border-radius:2px;border:1px solid rgba(0,255,102,0.1);margin-bottom:var(--space-3);">
							<span style="color:var(--color-red);font-weight:bold;margin-right:6px;">CLAIM //</span>
							<?php echo esc_html( wp_trim_words( $claim, 30, '…' ) ); ?>
						</div>
					<?php endif; ?>

					<p style="font-family:var(--font-body);font-size:0.82rem;color:rgba(247,244,239,0.55);line-height:1.6;margin:0;">
						<?php
						$excerpt = get_field( 'public_excerpt' );
						if ( ! $excerpt ) $excerpt = get_the_excerpt();
						echo esc_html( wp_trim_words( $excerpt, 40, '…' ) );
						?>
					</p>

					<div style="display:flex;justify-content:space-between;align-items:center;margin-top:var(--space-4);padding-top:var(--space-3);border-top:1px solid rgba(255,255,255,0.06);">
						<span style="font-family:var(--font-mono);font-size:0.65rem;color:rgba(247,244,239,0.3);text-transform:uppercase;letter-spacing:1px;">
							<?php echo get_the_terms( get_the_ID(), 'topic' ) ? esc_html( get_the_terms( get_the_ID(), 'topic' )[0]->name ) : ''; ?>
						</span>
						<span style="font-family:var(--font-heading);font-size:0.72rem;color:var(--color-red);">
							<?php esc_html_e( 'Full brief available to subscribers →', 'ascendance' ); ?>
						</span>
					</div>
				</div>

			</div>
		</div>
	</section>
	<?php wp_reset_postdata(); endif; ?>

	<!-- ═══ BOTTOM SUBSCRIBE CTA ══════════════════════════════ -->
	<section class="newsletter-hero-section" style="padding:64px 0;background:var(--color-navy-deep);">
		<div class="container">
			<div class="newsletter-hero-inner">
				<h2 style="font-family:var(--font-heading);font-size:1.8rem;color:var(--color-white);margin-bottom:var(--space-4);">
					<?php esc_html_e( 'Start Your Free 30-Day Trial', 'ascendance' ); ?>
				</h2>
				<p style="font-family:var(--font-body);font-size:0.95rem;color:rgba(247,244,239,0.55);margin-bottom:0;">
					<?php esc_html_e( 'No credit card required. Full Essential-tier access for 30 days.', 'ascendance' ); ?>
				</p>
				<form class="newsletter-form-inline" id="newsletter-form-bottom" novalidate style="max-width:420px;">
					<input type="email" id="newsletter-email-bottom" name="email" placeholder="<?php esc_attr_e( 'Your email address', 'ascendance' ); ?>" required>
					<button type="submit"><?php esc_html_e( 'Start Free', 'ascendance' ); ?></button>
				</form>
			</div>
		</div>
	</section>

</main>

<?php get_footer(); ?>
