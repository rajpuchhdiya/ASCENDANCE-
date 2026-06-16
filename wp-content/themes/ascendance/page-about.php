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
	<section class="page-hero">
		<div class="container">
			<div class="page-hero-inner">
				<p class="page-hero-eyebrow"><?php esc_html_e( '// About the Platform', 'ascendance' ); ?></p>
				<h1 class="page-hero-title"><?php esc_html_e( 'Intelligence with Editorial Independence', 'ascendance' ); ?></h1>
				<p class="page-hero-desc"><?php esc_html_e( 'Ascendance is a subscription-based intelligence platform built on the principle that decision-makers deserve analysis that is rigorous, independent, and forward-looking — not reactive commentary.', 'ascendance' ); ?></p>
			</div>
		</div>
	</section>

	<!-- ═══ MISSION & STANDARDS ═══════════════════════════════ -->
	<section class="about-mission-section section">
		<div class="container">
			<div class="about-grid">

				<div class="about-content">
					<span class="section-eyebrow"><?php esc_html_e( 'Mission', 'ascendance' ); ?></span>
					<h2 class="section-title reveal"><?php esc_html_e( 'Why Ascendance Exists', 'ascendance' ); ?></h2>

					<p style="font-family:var(--font-body);font-size:1rem;color:rgba(247,244,239,0.65);line-height:1.75;margin-bottom:var(--space-4);">
						<?php esc_html_e( 'The volume of geopolitical noise has exploded while the quality of actionable intelligence has not kept pace. Executives, investors, policy professionals, and analysts spend hours synthesising fragmented information from dozens of sources — only to produce risk assessments that are already stale.', 'ascendance' ); ?>
					</p>
					<p style="font-family:var(--font-body);font-size:1rem;color:rgba(247,244,239,0.65);line-height:1.75;margin-bottom:var(--space-4);">
						<?php esc_html_e( 'Ascendance was founded to solve this. We apply structured analytical frameworks — drawn from professional intelligence methodology — to produce briefs that are falsifiable, time-bounded, and calibrated to actual decision-making needs rather than media news cycles.', 'ascendance' ); ?>
					</p>
					<p style="font-family:var(--font-body);font-size:1rem;color:rgba(247,244,239,0.65);line-height:1.75;">
						<?php esc_html_e( 'Every brief contains a testable analytical claim, explicit confidence ratings, sourced findings, and scenario forecasts — so you can hold us accountable to the quality of our analysis over time.', 'ascendance' ); ?>
					</p>

					<div class="stats-row" style="margin-top:var(--space-8);padding-top:var(--space-6);border-top:1px solid var(--color-divider-dark);">
						<div class="stat-item">
							<span class="stat-number">100%</span>
							<span class="stat-label"><?php esc_html_e( 'Editorial Independence', 'ascendance' ); ?></span>
						</div>
						<div class="stat-item">
							<span class="stat-number">72h</span>
							<span class="stat-label"><?php esc_html_e( 'Average Brief Turnaround', 'ascendance' ); ?></span>
						</div>
						<div class="stat-item">
							<span class="stat-number">14+</span>
							<span class="stat-label"><?php esc_html_e( 'Intelligence Tag Categories', 'ascendance' ); ?></span>
						</div>
					</div>
				</div>

				<aside class="about-standards">
					<h3 style="font-family:var(--font-heading);font-size:0.78rem;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:var(--color-red);margin-bottom:var(--space-4);"><?php esc_html_e( 'Editorial Standards', 'ascendance' ); ?></h3>

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
						<div class="about-standard-item">
							<span class="about-standard-label"><?php echo esc_html( $s['label'] ); ?></span>
							<span class="about-standard-value"><?php echo esc_html( $s['value'] ); ?></span>
						</div>
					<?php endforeach; ?>
				</aside>

			</div>
		</div>
	</section>

	<!-- ═══ ANALYST TEAM ══════════════════════════════════════ -->
	<section class="analysts-section section" id="team">
		<div class="container">
			<div class="section-header">
				<span class="section-eyebrow" style="color:var(--color-red);"><?php esc_html_e( 'The Analysts', 'ascendance' ); ?></span>
				<h2 class="section-title dark reveal"><?php esc_html_e( 'Who Produces the Intelligence', 'ascendance' ); ?></h2>
			</div>

			<div class="analysts-grid">
				<?php
				$analysts = array(
					array( 'icon' => 'fa-earth-asia',    'name' => 'Dr. Amara Osei',      'title' => 'Geopolitics & Diplomacy',     'bio' => 'Former UN political affairs officer. Specialist in sub-Saharan African security dynamics and multilateral negotiation frameworks. 14 years field experience.' ),
					array( 'icon' => 'fa-microchip',     'name' => 'Dr. Yuki Tanaka',      'title' => 'Technology & AI Governance',  'bio' => 'Former semiconductor policy advisor to the Japanese Ministry of Economy. Research focus: AI regulation, chip supply chains, US-China tech competition.' ),
					array( 'icon' => 'fa-chart-line',    'name' => 'Marcus Delacroix',     'title' => 'Economics & Markets',         'bio' => 'Ex-emerging markets analyst at a tier-1 investment bank. Specialises in sovereign debt stress, energy transition finance, and FX contagion risk.' ),
				);
				foreach ( $analysts as $analyst ) :
				?>
					<div class="analyst-card reveal">
						<div class="analyst-avatar"><i class="fa-solid <?php echo esc_attr( $analyst['icon'] ); ?>"></i></div>
						<div class="analyst-name"><?php echo esc_html( $analyst['name'] ); ?></div>
						<div class="analyst-title"><?php echo esc_html( $analyst['title'] ); ?></div>
						<p class="analyst-bio"><?php echo esc_html( $analyst['bio'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- ═══ MEDIA KIT ══════════════════════════════════════════ -->
	<section class="section bg-navy" id="media" style="background:var(--color-navy-deep);padding:var(--space-12) 0;">
		<div class="container" style="text-align:center;">
			<span class="section-eyebrow"><?php esc_html_e( 'Press & Media', 'ascendance' ); ?></span>
			<h2 class="section-title reveal" style="margin-bottom:var(--space-4);"><?php esc_html_e( 'Media Resources', 'ascendance' ); ?></h2>
			<p style="font-family:var(--font-body);color:rgba(247,244,239,0.55);max-width:500px;margin:0 auto var(--space-6);line-height:1.65;font-size:0.95rem;">
				<?php esc_html_e( 'Journalists and media professionals can access our media kit, request analyst comment, and arrange interviews below.', 'ascendance' ); ?>
			</p>
			<div style="display:flex;gap:var(--space-4);justify-content:center;flex-wrap:wrap;">
				<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn btn-primary" id="about-media-contact"><?php esc_html_e( 'Request Media Interview', 'ascendance' ); ?></a>
				<a href="#" class="btn btn-secondary" id="about-media-kit"><?php esc_html_e( 'Download Media Kit', 'ascendance' ); ?></a>
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
