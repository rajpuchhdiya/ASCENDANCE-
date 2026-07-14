<?php
/**
 * Template Name: About
 * Page template for the About page.
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
				<p class="page-hero-eyebrow text-xs font-mono uppercase tracking-widest text-brand-red mb-4 block"><?php esc_html_e( '// About the Platform', 'ascendance' ); ?></p>
				<h1 class="page-hero-title text-3xl md:text-5xl font-sans font-bold leading-tight mb-6"><?php esc_html_e( 'Intelligence with Editorial Independence', 'ascendance' ); ?></h1>
				<p class="page-hero-desc text-base md:text-lg text-cream/80 max-w-[720px] leading-relaxed"><?php esc_html_e( 'Ascendance is a subscription-based intelligence platform built on the principle that decision-makers deserve analysis that is rigorous, independent, and forward-looking — not reactive commentary.', 'ascendance' ); ?></p>
			</div>
		</div>
	</section>

	<!-- ═══ MISSION & STANDARDS ═══════════════════════════════ -->
	<section class="about-mission-section section py-20 bg-cream dark:bg-navy-deep border-b border-brand-divider-light dark:border-brand-divider-dark">
		<div class="container mx-auto px-6 md:px-8">
			<div class="about-grid grid grid-cols-1 lg:grid-cols-[2fr_1fr] gap-12">

				<div class="about-content flex flex-col gap-6">
					<span class="section-eyebrow text-xs uppercase tracking-widest text-brand-red font-sans font-bold block mb-1"><?php esc_html_e( 'Mission', 'ascendance' ); ?></span>
					<h2 class="section-title text-3xl md:text-4xl font-sans font-bold text-brand-text-primary dark:text-white mb-4 reveal"><?php esc_html_e( 'Why Ascendance Exists', 'ascendance' ); ?></h2>

					<p class="text-brand-text-muted dark:text-cream/80 leading-relaxed">
						<?php esc_html_e( 'The volume of geopolitical noise has exploded while the quality of actionable intelligence has not kept pace. Executives, investors, policy professionals, and analysts spend hours synthesising fragmented information from dozens of sources — only to produce risk assessments that are already stale.', 'ascendance' ); ?>
					</p>
					<p class="text-brand-text-muted dark:text-cream/80 leading-relaxed">
						<?php esc_html_e( 'Ascendance was founded to solve this. We apply structured analytical frameworks — drawn from professional intelligence methodology — to produce briefs that are falsifiable, time-bounded, and calibrated to decision-making needs rather than media news cycles.', 'ascendance' ); ?>
					</p>
					<p class="text-brand-text-muted dark:text-cream/80 leading-relaxed">
						<?php esc_html_e( 'Every brief contains a testable analytical claim, explicit confidence ratings, sourced findings, and scenario forecasts — so you can hold us accountable to the quality of our analysis over time.', 'ascendance' ); ?>
					</p>

					<div class="stats-row grid grid-cols-1 sm:grid-cols-3 gap-6 mt-8 pt-8 border-t border-brand-divider-light dark:border-brand-divider-dark/20">
						<div class="stat-item">
							<span class="stat-number block text-3xl font-sans font-bold text-brand-text-primary dark:text-white mb-2">100%</span>
							<span class="stat-label text-xs uppercase tracking-wider text-brand-text-muted dark:text-cream/60 font-sans font-medium"><?php esc_html_e( 'Editorial Independence', 'ascendance' ); ?></span>
						</div>
						<div class="stat-item">
							<span class="stat-number block text-3xl font-sans font-bold text-brand-text-primary dark:text-white mb-2">72h</span>
							<span class="stat-label text-xs uppercase tracking-wider text-brand-text-muted dark:text-cream/60 font-sans font-medium"><?php esc_html_e( 'Average Brief Turnaround', 'ascendance' ); ?></span>
						</div>
						<div class="stat-item">
							<span class="stat-number block text-3xl font-sans font-bold text-brand-text-primary dark:text-white mb-2">14+</span>
							<span class="stat-label text-xs uppercase tracking-wider text-brand-text-muted dark:text-cream/60 font-sans font-medium"><?php esc_html_e( 'Intelligence Tag Categories', 'ascendance' ); ?></span>
						</div>
					</div>
				</div>

				<aside class="about-standards bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-8 rounded-sm self-start shadow-sm">
					<h3 class="text-lg font-sans font-bold text-brand-text-primary dark:text-white mb-6 border-b border-brand-divider-light dark:border-brand-divider-dark/20 pb-4"><?php esc_html_e( 'Editorial Standards', 'ascendance' ); ?></h3>

					<?php
					$standards = array(
						array( 'label' => 'Analytical Claims',  'value' => 'Every brief leads with a falsifiable, forward-looking analytical claim. Readers can evaluate whether we got it right.' ),
						array( 'label' => 'Source Independence', 'value' => 'We accept no advertiser influence. Intelligence conclusions are never shaped by commercial relationships.' ),
						array( 'label' => 'Confidence Ratings', 'value' => 'We explicitly state our confidence level (High / Medium / Low) for every major claim, drawing on structured analytic technique.' ),
						array( 'label' => 'Timeliness Standard', 'value' => 'Briefs on breaking developments are published within 72 hours. Updates to parent briefs within 24 hours.' ),
						array( 'label' => 'Corrections Policy',  'value' => 'Material errors are corrected publicly with a change log entry. Analytical revisions are versioned and documented.' ),
					);
					foreach ( $standards as $s ) :
					?>
						<div class="about-standard-item border-b border-brand-divider-light dark:border-brand-divider-dark/10 py-4 last:border-b-0 last:pb-0">
							<span class="about-standard-label block text-xs uppercase tracking-widest text-brand-red font-sans font-bold mb-2"><?php echo esc_html( $s['label'] ); ?></span>
							<span class="about-standard-value text-sm text-brand-text-muted dark:text-cream/70 leading-relaxed"><?php echo esc_html( $s['value'] ); ?></span>
						</div>
					<?php endforeach; ?>
				</aside>

			</div>
		</div>
	</section>

	<!-- ═══ ANALYST TEAM ══════════════════════════════════════ -->
	<section class="analysts-section section py-20 bg-white dark:bg-navy border-b border-brand-divider-light dark:border-brand-divider-dark" id="team">
		<div class="container mx-auto px-6 md:px-8">
			<div class="section-header max-w-[640px] mb-12">
				<span class="section-eyebrow text-xs uppercase tracking-widest text-brand-red font-sans font-bold block mb-1"><?php esc_html_e( 'The Analysts', 'ascendance' ); ?></span>
				<h2 class="section-title text-3xl md:text-4xl font-sans font-bold text-brand-text-primary dark:text-white mb-4 reveal"><?php esc_html_e( 'Who Produces the Intelligence', 'ascendance' ); ?></h2>
			</div>

			<div class="analysts-grid grid grid-cols-1 md:grid-cols-3 gap-8">
				<?php
				$analysts = array(
					array( 'icon' => 'fa-earth-asia',    'name' => 'Dr. Amara Osei',      'title' => 'Geopolitics & Diplomacy',     'bio' => 'Former UN political affairs officer. Specialist in sub-Saharan African security dynamics and multilateral negotiation frameworks. 14 years field experience.' ),
					array( 'icon' => 'fa-microchip',     'name' => 'Dr. Yuki Tanaka',      'title' => 'Technology & AI Governance',  'bio' => 'Former semiconductor policy advisor to the Japanese Ministry of Economy. Research focus: AI regulation, chip supply chains, US-China tech competition.' ),
					array( 'icon' => 'fa-chart-line',    'name' => 'Marcus Delacroix',     'title' => 'Economics & Markets',         'bio' => 'Ex-emerging markets analyst at a tier-1 investment bank. Specialises in sovereign debt stress, energy transition finance, and FX contagion risk.' ),
				);
				foreach ( $analysts as $analyst ) :
				?>
					<div class="analyst-card bg-cream dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-8 rounded-sm shadow-sm hover:shadow-md transition-all duration-300 reveal">
						<div class="analyst-avatar w-12 h-12 flex items-center justify-center bg-brand-red text-white text-xl rounded-sm mb-6"><i class="fa-solid <?php echo esc_attr( $analyst['icon'] ); ?>"></i></div>
						<div class="analyst-name text-lg font-sans font-bold text-brand-text-primary dark:text-white mb-1"><?php echo esc_html( $analyst['name'] ); ?></div>
						<div class="analyst-title text-xs uppercase tracking-wider text-brand-red font-sans font-medium mb-4"><?php echo esc_html( $analyst['title'] ); ?></div>
						<p class="analyst-bio text-sm text-brand-text-muted dark:text-cream/70 leading-relaxed"><?php echo esc_html( $analyst['bio'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- ═══ MEDIA KIT ══════════════════════════════════════════ -->
	<section class="about-media-section section py-20 bg-cream dark:bg-navy-deep border-b border-brand-divider-light dark:border-brand-divider-dark" id="media">
		<div class="container mx-auto px-6 md:px-8">
			<span class="section-eyebrow text-xs uppercase tracking-widest text-brand-red font-sans font-bold block mb-1"><?php esc_html_e( 'Press & Media', 'ascendance' ); ?></span>
			<h2 class="section-title text-3xl md:text-4xl font-sans font-bold text-brand-text-primary dark:text-white mb-4 reveal"><?php esc_html_e( 'Media Resources', 'ascendance' ); ?></h2>
			<p class="text-brand-text-muted dark:text-cream/70 max-w-[640px] leading-relaxed mb-8">
				<?php esc_html_e( 'Journalists and media professionals can access our media kit, request analyst comment, and arrange interviews below.', 'ascendance' ); ?>
			</p>
			<div class="btn-group flex flex-wrap gap-4">
				<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn btn-primary" id="about-media-contact"><?php esc_html_e( 'Request Media Interview', 'ascendance' ); ?></a>
				<a href="#" class="btn btn-secondary border border-brand-divider-light dark:border-brand-divider-dark text-brand-text-primary dark:text-cream hover:bg-brand-divider-light dark:hover:bg-brand-divider-dark" id="about-media-kit"><?php esc_html_e( 'Download Media Kit', 'ascendance' ); ?></a>
			</div>
		</div>
	</section>

	<?php get_template_part( 'template-parts/cta-strip', null, array(
		'heading'   => __( 'Start Reading the Platform', 'ascendance' ),
		'body'      => __( 'Access the live intelligence ledger with a free subscription. No credit card required for your first 30 days.', 'ascendance' ),
		'btn_label' => __( 'Subscribe Free', 'ascendance' ),
		'btn_url'   => home_url( '/newsletter/' ),
		'btn2_label' => __( 'Browse Intelligence', 'ascendance' ),
		'btn2_url'  => home_url( '/intelligence/' ),
	) ); ?>

</main>

<?php get_footer(); ?>
