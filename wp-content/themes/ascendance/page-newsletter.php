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
	<section class="newsletter-hero-section bg-navy-deep py-20 md:py-32 border-b border-brand-divider-dark text-white" id="subscribe">
		<div class="container mx-auto px-6 md:px-8">
			<div class="newsletter-hero-inner max-w-[720px] mx-auto text-center flex flex-col gap-6">
				<span class="section-eyebrow text-xs uppercase tracking-widest text-brand-red font-sans font-bold block mb-1"><?php esc_html_e( 'Free Weekly Intelligence', 'ascendance' ); ?></span>
				<h1 class="hero-title text-3xl md:text-5xl font-sans font-bold leading-tight mb-2">
					<?php esc_html_e( 'The Weekly Intelligence Brief', 'ascendance' ); ?>
				</h1>
				<p class="font-serif text-base text-cream/70 leading-relaxed max-w-[640px] mx-auto mb-4">
					<?php esc_html_e( 'Every week, Ascendance distils the most significant geopolitical, economic, and technology developments into a concise, actionable intelligence brief — delivered directly to your inbox. Free for the first 30 days.', 'ascendance' ); ?>
				</p>

				<form class="newsletter-form-inline flex flex-col sm:flex-row gap-4 mt-4 max-w-[540px] mx-auto w-full" id="newsletter-form-hero" novalidate>
					<input type="email" id="newsletter-email-hero" name="email" class="flex-grow px-4 py-3 bg-white dark:bg-navy border border-brand-divider-light dark:border-brand-divider-dark rounded-sm text-brand-text-primary dark:text-cream font-serif text-sm outline-none transition-all duration-150 focus:border-brand-red" placeholder="<?php esc_attr_e( 'Enter your email address', 'ascendance' ); ?>" required>
					<button type="submit" class="bg-brand-red hover:bg-brand-red-light text-white border-none rounded-sm py-3 px-6 font-sans font-bold text-sm uppercase tracking-wider cursor-pointer transition-all duration-150 active:scale-[0.98]"><?php esc_html_e( 'Subscribe Free', 'ascendance' ); ?></button>
				</form>

				<div class="newsletter-benefits flex flex-wrap justify-center gap-6 text-xs text-brand-text-muted dark:text-cream/50 mt-6 font-sans">
					<span class="newsletter-benefit flex items-center gap-2"><i class="fa-regular fa-calendar text-brand-red"></i><?php esc_html_e( 'Published every Monday', 'ascendance' ); ?></span>
					<span class="newsletter-benefit flex items-center gap-2"><i class="fa-solid fa-lock-open text-brand-red"></i><?php esc_html_e( 'Free 30-day trial', 'ascendance' ); ?></span>
					<span class="newsletter-benefit flex items-center gap-2"><i class="fa-solid fa-shield text-brand-red"></i><?php esc_html_e( 'No spam. Unsubscribe anytime.', 'ascendance' ); ?></span>
				</div>
			</div>
		</div>
	</section>

	<!-- ═══ BENEFITS ══════════════════════════════════════════ -->
	<section class="pillars-section section py-20 bg-navy border-b border-brand-divider-dark">
		<div class="container mx-auto px-6 md:px-8">
			<div class="section-header max-w-[640px] mx-auto text-center mb-12">
				<span class="section-eyebrow text-xs uppercase tracking-widest text-brand-red font-sans font-bold mb-3 block"><?php esc_html_e( 'What You Get', 'ascendance' ); ?></span>
				<h2 class="section-title text-3xl md:text-4xl font-sans font-bold text-white mb-4 reveal"><?php esc_html_e( 'Every Issue Includes', 'ascendance' ); ?></h2>
			</div>

			<div class="pillars-grid grid grid-cols-1 md:grid-cols-3 gap-8">
				<div class="pillar-card bg-navy-mid border-l-[3px] border-l-brand-red border-y border-r border-brand-divider-dark/40 p-8 rounded-sm shadow-md transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg reveal">
					<div class="pillar-icon text-3xl text-brand-red mb-6"><i class="fa-solid fa-bullseye"></i></div>
					<h3 class="text-xl font-sans font-bold text-white mb-4"><?php esc_html_e( 'The Lead Brief', 'ascendance' ); ?></h3>
					<p class="text-sm text-cream/70 leading-relaxed"><?php esc_html_e( "This week's most significant development — with our analytical claim, key findings, and 90-day forecast. The brief you need to understand the story behind the headlines.", 'ascendance' ); ?></p>
				</div>
				<div class="pillar-card bg-navy-mid border-l-[3px] border-l-brand-red border-y border-r border-brand-divider-dark/40 p-8 rounded-sm shadow-md transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg reveal reveal-delay-1">
					<div class="pillar-icon text-3xl text-brand-red mb-6"><i class="fa-solid fa-satellite-dish"></i></div>
					<h3 class="text-xl font-sans font-bold text-white mb-4"><?php esc_html_e( 'Signal / Noise Digest', 'ascendance' ); ?></h3>
					<p class="text-sm text-cream/70 leading-relaxed"><?php esc_html_e( "Five rapid-fire intelligence updates from the week — scored High, Medium, or Low impact. Distilled to 30 seconds each so you can process the full picture fast.", 'ascendance' ); ?></p>
				</div>
				<div class="pillar-card bg-navy-mid border-l-[3px] border-l-brand-red border-y border-r border-brand-divider-dark/40 p-8 rounded-sm shadow-md transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg reveal reveal-delay-2">
					<div class="pillar-icon text-3xl text-brand-red mb-6"><i class="fa-solid fa-compass"></i></div>
					<h3 class="text-xl font-sans font-bold text-white mb-4"><?php esc_html_e( 'The Analyst Take', 'ascendance' ); ?></h3>
					<p class="text-sm text-cream/70 leading-relaxed"><?php esc_html_e( "One paragraph from our senior analysts on what they're watching for this week — a forward signal that often doesn't surface in mainstream coverage for weeks.", 'ascendance' ); ?></p>
				</div>
			</div>
		</div>
	</section>

	<!-- ═══ SAMPLE PREVIEW ════════════════════════════════════ -->
	<?php if ( $sample_brief->have_posts() ) : $sample_brief->the_post(); ?>
	<section class="newsletter-preview-section py-20 bg-cream dark:bg-navy-deep border-b border-brand-divider-light dark:border-brand-divider-dark">
		<div class="container mx-auto px-6 md:px-8">
			<div class="newsletter-preview-grid grid grid-cols-1 lg:grid-cols-[1fr_1.2fr] gap-12 items-center">

				<div class="reveal">
					<span class="section-eyebrow text-xs uppercase tracking-widest text-brand-red font-sans font-bold block mb-1"><?php esc_html_e( 'Sample Edition', 'ascendance' ); ?></span>
					<h2 class="section-title text-3xl md:text-4xl font-sans font-bold text-brand-text-primary dark:text-white mb-4 reveal"><?php esc_html_e( 'What a Typical Brief Looks Like', 'ascendance' ); ?></h2>
					<p class="section-lead text-brand-text-muted dark:text-cream/80 text-base leading-relaxed mb-6"><?php esc_html_e( "Here's a look inside a recent Ascendance Intelligence Brief — the kind of structured analysis you'll receive every week.", 'ascendance' ); ?></p>
					<a href="<?php the_permalink(); ?>" class="btn btn-primary" id="newsletter-sample-read">
						<?php esc_html_e( 'Read Full Brief', 'ascendance' ); ?>
					</a>
				</div>

				<div class="newsletter-preview-card bg-navy-deep border border-brand-divider-dark/40 p-8 rounded-sm shadow-md reveal reveal-delay-1">
					<div class="newsletter-preview-header flex justify-between items-center text-[10px] font-mono tracking-widest text-brand-text-muted mb-4 border-b border-brand-divider-dark/20 pb-3">
						<span class="newsletter-preview-label text-brand-red font-bold uppercase"><?php esc_html_e( 'INTELLIGENCE BRIEF', 'ascendance' ); ?></span>
						<span class="newsletter-preview-date text-cream/40"><?php echo get_the_date( 'j M Y' ); ?></span>
					</div>

					<div class="mb-4">
						<?php
						$tier_access = function_exists( 'get_field' ) ? get_field( 'tier_access' ) : get_post_meta( get_the_ID(), 'tier_access', true );
						if ( $tier_access ) echo ascendance_tier_badge( $tier_access ); // phpcs:ignore
						?>
					</div>

					<h3 class="font-sans text-lg font-bold text-white mb-4 leading-snug"><?php the_title(); ?></h3>

					<?php
					$claim = function_exists( 'get_field' ) ? get_field( 'analytical_claim' ) : get_post_meta( get_the_ID(), 'analytical_claim', true );
					if ( $claim ) : ?>
						<div class="font-mono text-xs text-[#00FF66] bg-[#030810] p-4 rounded-sm border border-[#00FF66]/10 mb-4">
							<span class="text-brand-red font-bold mr-2">CLAIM //</span>
							<?php echo esc_html( wp_trim_words( $claim, 30, '…' ) ); ?>
						</div>
					<?php endif; ?>

					<p class="font-serif text-sm text-cream/60 leading-relaxed mb-6">
						<?php
						$excerpt = get_field( 'public_excerpt' );
						if ( ! $excerpt ) $excerpt = get_the_excerpt();
						echo esc_html( wp_trim_words( $excerpt, 40, '…' ) );
						?>
					</p>

					<div class="flex justify-between items-center mt-4 pt-4 border-t border-white/5">
						<span class="font-mono text-[10px] text-cream/30 uppercase tracking-widest">
							<?php echo get_the_terms( get_the_ID(), 'topic' ) ? esc_html( get_the_terms( get_the_ID(), 'topic' )[0]->name ) : ''; ?>
						</span>
						<span class="font-sans text-xs text-brand-red font-bold">
							<?php esc_html_e( 'Full brief available to subscribers →', 'ascendance' ); ?>
						</span>
					</div>
				</div>

			</div>
		</div>
	</section>
	<?php wp_reset_postdata(); endif; ?>

	<!-- ═══ BOTTOM SUBSCRIBE CTA ══════════════════════════════ -->
	<section class="newsletter-hero-section bg-navy-deep py-16 border-b border-brand-divider-dark text-white">
		<div class="container mx-auto px-6 md:px-8">
			<div class="newsletter-hero-inner max-w-[720px] mx-auto text-center flex flex-col gap-4">
				<h2 class="text-2xl md:text-3xl font-sans font-bold leading-tight mb-2">
					<?php esc_html_e( 'Start Your Free 30-Day Trial', 'ascendance' ); ?>
				</h2>
				<p class="font-serif text-base text-cream/60 leading-relaxed mb-4">
					<?php esc_html_e( 'No credit card required. Full Essential-tier access for 30 days.', 'ascendance' ); ?>
				</p>
				<form class="newsletter-form-inline flex flex-col sm:flex-row gap-4 max-w-[420px] mx-auto w-full" id="newsletter-form-bottom" novalidate>
					<input type="email" id="newsletter-email-bottom" name="email" class="flex-grow px-4 py-3 bg-white dark:bg-navy border border-brand-divider-light dark:border-brand-divider-dark rounded-sm text-brand-text-primary dark:text-cream font-serif text-sm outline-none transition-all duration-150 focus:border-brand-red" placeholder="<?php esc_attr_e( 'Your email address', 'ascendance' ); ?>" required>
					<button type="submit" class="bg-brand-red hover:bg-brand-red-light text-white border-none rounded-sm py-3 px-6 font-sans font-bold text-sm uppercase tracking-wider cursor-pointer transition-all duration-150 active:scale-[0.98]"><?php esc_html_e( 'Start Free', 'ascendance' ); ?></button>
				</form>
			</div>
		</div>
	</section>

</main>

<?php get_footer(); ?>
