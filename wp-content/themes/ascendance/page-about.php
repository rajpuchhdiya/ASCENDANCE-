<?php
/**
 * Template Name: About
 * Page template for the About page.
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="as-page-wrap">

	<!-- ═══ PAGE HERO ═════════════════════════════════════════ -->
	<section class="as-page-hero">
		<div class="as-page-hero-inner">
			<span class="as-page-eyebrow"><?php esc_html_e( '// About the Platform', 'ascendance' ); ?></span>
			<h1 class="as-page-title"><?php esc_html_e( 'Intelligence with Editorial Independence', 'ascendance' ); ?></h1>
			<p class="as-page-desc"><?php esc_html_e( 'Ascendance is a subscription-based intelligence platform built on the principle that decision-makers deserve analysis that is rigorous, independent, and forward-looking — not reactive commentary.', 'ascendance' ); ?></p>
		</div>
	</section>

	<!-- ═══ MISSION & STANDARDS ═══════════════════════════════ -->
	<section class="as-about-section">
		<div class="as-about-wrap">
			<div class="as-about-grid">

				<div class="as-about-content">
					<span class="as-sec-eyebrow"><?php esc_html_e( 'Mission', 'ascendance' ); ?></span>
					<h2 class="as-sec-title"><?php esc_html_e( 'Why Ascendance Exists', 'ascendance' ); ?></h2>

					<p><?php esc_html_e( 'The volume of geopolitical noise has exploded while the quality of actionable intelligence has not kept pace. Executives, investors, policy professionals, and analysts spend hours synthesising fragmented information from dozens of sources — only to produce risk assessments that are already stale.', 'ascendance' ); ?></p>

					<p><?php esc_html_e( 'Ascendance was founded to solve this. We apply structured analytical frameworks — drawn from professional intelligence methodology — to produce briefs that are falsifiable, time-bounded, and calibrated to decision-making needs rather than media news cycles.', 'ascendance' ); ?></p>

					<p><?php esc_html_e( 'Every brief contains a testable analytical claim, explicit confidence ratings, sourced findings, and scenario forecasts — so you can hold us accountable to the quality of our analysis over time.', 'ascendance' ); ?></p>

					<div class="as-stats-row">
						<div>
							<span class="as-stat-num">100%</span>
							<span class="as-stat-label"><?php esc_html_e( 'Editorial Independence', 'ascendance' ); ?></span>
						</div>
						<div>
							<span class="as-stat-num">72h</span>
							<span class="as-stat-label"><?php esc_html_e( 'Average Brief Turnaround', 'ascendance' ); ?></span>
						</div>
						<div>
							<span class="as-stat-num">14+</span>
							<span class="as-stat-label"><?php esc_html_e( 'Intelligence Tag Categories', 'ascendance' ); ?></span>
						</div>
					</div>
				</div>

				<aside class="as-standards-card">
					<h3 class="as-standards-title"><?php esc_html_e( 'Editorial Standards', 'ascendance' ); ?></h3>

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
						<div class="as-standard-item">
							<span class="as-standard-label"><?php echo esc_html( $s['label'] ); ?></span>
							<span class="as-standard-val"><?php echo esc_html( $s['value'] ); ?></span>
						</div>
					<?php endforeach; ?>
				</aside>

			</div>
		</div>
	</section>

	<!-- ═══ ANALYST TEAM ══════════════════════════════════════ -->
	<section class="as-about-section alt" id="team">
		<div class="as-about-wrap">
			<span class="as-sec-eyebrow"><?php esc_html_e( 'The Analysts', 'ascendance' ); ?></span>
			<h2 class="as-sec-title"><?php esc_html_e( 'Who Produces the Intelligence', 'ascendance' ); ?></h2>

			<div class="as-analysts-grid">
				<?php
				$analysts = array(
					array( 'icon' => 'fa-earth-asia',    'name' => 'Dr. Amara Osei',      'title' => 'Geopolitics & Diplomacy',     'bio' => 'Former UN political affairs officer. Specialist in sub-Saharan African security dynamics and multilateral negotiation frameworks. 14 years field experience.' ),
					array( 'icon' => 'fa-microchip',     'name' => 'Dr. Yuki Tanaka',      'title' => 'Technology & AI Governance',  'bio' => 'Former semiconductor policy advisor to the Japanese Ministry of Economy. Research focus: AI regulation, chip supply chains, US-China tech competition.' ),
					array( 'icon' => 'fa-chart-line',    'name' => 'Marcus Delacroix',     'title' => 'Economics & Markets',         'bio' => 'Ex-emerging markets analyst at a tier-1 investment bank. Specialises in sovereign debt stress, energy transition finance, and FX contagion risk.' ),
				);
				foreach ( $analysts as $analyst ) :
				?>
					<div class="as-analyst-card">
						<div class="as-analyst-icon"><i class="fa-solid <?php echo esc_attr( $analyst['icon'] ); ?>"></i></div>
						<div class="as-analyst-name"><?php echo esc_html( $analyst['name'] ); ?></div>
						<div class="as-analyst-role"><?php echo esc_html( $analyst['title'] ); ?></div>
						<p class="as-analyst-bio"><?php echo esc_html( $analyst['bio'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- ═══ MEDIA KIT ══════════════════════════════════════════ -->
	<section class="as-about-section" id="media">
		<div class="as-about-wrap">
			<span class="as-sec-eyebrow"><?php esc_html_e( 'Press & Media', 'ascendance' ); ?></span>
			<h2 class="as-sec-title"><?php esc_html_e( 'Media Resources', 'ascendance' ); ?></h2>
			<p style="font-family:var(--font-body); font-size:16.5px; line-height:1.65; color:var(--ink-2); max-width:640px; margin:0 0 20px;">
				<?php esc_html_e( 'Journalists and media professionals can access our media kit, request analyst comment, and arrange interviews below.', 'ascendance' ); ?>
			</p>
			<div class="as-media-actions">
				<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="as-btn primary" id="about-media-contact"><?php esc_html_e( 'Request Media Interview', 'ascendance' ); ?></a>
				<a href="#" class="as-btn outline" id="about-media-kit"><?php esc_html_e( 'Download Media Kit', 'ascendance' ); ?></a>
			</div>
		</div>
	</section>

	<?php get_template_part( 'template-parts/cta-strip', null, array(
		'heading'    => __( 'Start Reading the Platform', 'ascendance' ),
		'body'       => __( 'Access the live intelligence ledger with a free subscription. No credit card required for your first 30 days.', 'ascendance' ),
		'btn_label'  => __( 'Subscribe Free', 'ascendance' ),
		'btn_url'    => home_url( '/newsletter/' ),
		'btn2_label' => __( 'Browse Intelligence', 'ascendance' ),
		'btn2_url'   => home_url( '/intelligence/' ),
	) ); ?>

</main>

<?php get_footer(); ?>

