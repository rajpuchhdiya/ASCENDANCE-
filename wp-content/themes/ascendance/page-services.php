<?php
/**
 * Template Name: Services
 * Page template for the Services/Products page.
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="as-page-wrap">

	<!-- ═══ PAGE HERO ═════════════════════════════════════════ -->
	<section class="as-page-hero">
		<div class="as-page-hero-inner">
			<span class="as-page-eyebrow"><?php esc_html_e( '// Intelligence Products', 'ascendance' ); ?></span>
			<h1 class="as-page-title"><?php esc_html_e( 'Three Products. One Platform.', 'ascendance' ); ?></h1>
			<p class="as-page-desc"><?php esc_html_e( 'Intelligence Briefs, Dynamic Updates, and Strategic Dossiers — each designed for a distinct analytical workflow and decision-making cadence.', 'ascendance' ); ?></p>
		</div>
	</section>

	<!-- ═══ SERVICES DETAIL ═══════════════════════════════════ -->
	<section class="as-services-section">
		<div class="as-services-wrap">

			<!-- 1. Intelligence Briefs -->
			<div class="as-service-item" id="briefs">
				<div class="as-service-content">
					<span class="as-service-num">01 / 03</span>
					<h2 class="as-service-title"><?php esc_html_e( 'Intelligence Briefs', 'ascendance' ); ?></h2>
					<p class="as-service-desc"><?php esc_html_e( 'Weekly structured analysis documents covering geopolitical events, economic developments, technology shifts, and security dynamics. Each brief is built around a falsifiable analytical claim supported by sourced findings.', 'ascendance' ); ?></p>
					<ul class="as-service-features">
						<li><?php esc_html_e( 'Analytical claim with explicit confidence rating', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'Key findings with primary source citations', 'ascendance' ); ?></li>
						<li><?php esc_html_e( '3–5 key takeaways for rapid consumption', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'Executive summary for non-specialist readers', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'Version-controlled — updated as events evolve', 'ascendance' ); ?></li>
					</ul>
					<div>
						<a href="<?php echo esc_url( home_url( '/briefs/' ) ); ?>" class="as-btn primary" id="services-briefs-cta"><?php esc_html_e( 'Browse Briefs', 'ascendance' ); ?></a>
					</div>
				</div>

				<div class="as-service-card">
					<span class="as-service-card-eyebrow"><?php esc_html_e( '// Brief Structure', 'ascendance' ); ?></span>
					<?php
					$brief_sections = array( 'Analytical Claim', 'Public Excerpt', 'Executive Summary', 'Key Findings', 'Key Takeaways', 'Source References', 'Related Briefs' );
					foreach ( $brief_sections as $i => $section ) :
					?>
						<div class="as-visual-row">
							<span class="as-visual-num"><?php echo str_pad( $i + 1, 2, '0', STR_PAD_LEFT ); ?></span>
							<span class="as-visual-label"><?php echo esc_html( $section ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- 2. Dynamic Updates -->
			<div class="as-service-item" id="updates">
				<div class="as-service-content">
					<span class="as-service-num">02 / 03</span>
					<h2 class="as-service-title"><?php esc_html_e( 'Dynamic Updates', 'ascendance' ); ?></h2>
					<p class="as-service-desc"><?php esc_html_e( 'Real-time intelligence dispatches linked to parent briefs. Published within 24 hours of significant developments — providing situational awareness without requiring you to monitor news feeds.', 'ascendance' ); ?></p>
					<ul class="as-service-features">
						<li><?php esc_html_e( 'Four-tier impact rating system (Low → Critical)', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'Linked to parent brief for full context', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'One-line summary optimised for scanning', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'Push notification for Critical-rated events', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'Full update content with analyst commentary', 'ascendance' ); ?></li>
					</ul>
					<div>
						<a href="<?php echo esc_url( home_url( '/updates/' ) ); ?>" class="as-btn primary" id="services-updates-cta"><?php esc_html_e( 'Browse Updates', 'ascendance' ); ?></a>
					</div>
				</div>

				<div class="as-service-card">
					<span class="as-service-card-eyebrow"><?php esc_html_e( '// Impact Scale', 'ascendance' ); ?></span>
					<?php
					$impacts = array(
						'low'      => 'Low — Minor adjustments only',
						'medium'   => 'Medium — Notable shifting variables',
						'high'     => 'High — Major trend disruption',
						'critical' => 'Critical — Dynamic realignment required',
					);
					foreach ( $impacts as $key => $label ) :
					?>
						<div class="as-visual-row">
							<?php echo ascendance_impact_badge( $key ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<span class="as-visual-label" style="margin-left:8px;"><?php echo esc_html( $label ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- 3. Strategic Dossiers -->
			<div class="as-service-item" id="dossiers">
				<div class="as-service-content">
					<span class="as-service-num">03 / 03</span>
					<h2 class="as-service-title"><?php esc_html_e( 'Strategic Dossiers', 'ascendance' ); ?></h2>
					<p class="as-service-desc"><?php esc_html_e( 'Living intelligence documents on key actors, countries, and strategic themes. Updated monthly, each dossier integrates military, economic, technological, diplomatic, and political intelligence into one coherent picture.', 'ascendance' ); ?></p>
					<ul class="as-service-features">
						<li><?php esc_html_e( 'Five-dimension strategic overview', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'Living document — updated as the situation evolves', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'Key judgements summary at top', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'Update frequency: weekly or monthly', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'Professional & Enterprise tiers only', 'ascendance' ); ?></li>
					</ul>
					<div>
						<a href="<?php echo esc_url( home_url( '/dossiers/' ) ); ?>" class="as-btn primary" id="services-dossiers-cta"><?php esc_html_e( 'Browse Dossiers', 'ascendance' ); ?></a>
					</div>
				</div>

				<div class="as-service-card">
					<span class="as-service-card-eyebrow"><?php esc_html_e( '// Dossier Dimensions', 'ascendance' ); ?></span>
					<?php
					$dims = array( 'Military & Security', 'Economic & Trade', 'Technology & Innovation', 'Diplomatic Positioning', 'Domestic Political Dynamics' );
					foreach ( $dims as $i => $dim ) :
					?>
						<div class="as-visual-row">
							<span class="as-visual-num"><?php echo esc_html( chr(65 + $i) ); ?></span>
							<span class="as-visual-label"><?php echo esc_html( $dim ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

		</div>
	</section>

	<!-- ═══ ACCESS MATRIX ══════════════════════════════════════ -->
	<section class="as-matrix-section">
		<div class="as-services-wrap">
			<div class="as-matrix-card">
				<h3 class="as-matrix-title"><?php esc_html_e( 'Tier Access Matrix', 'ascendance' ); ?></h3>
				<table class="as-matrix-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Feature', 'ascendance' ); ?></th>
							<th><?php esc_html_e( 'Essential', 'ascendance' ); ?></th>
							<th><?php esc_html_e( 'Professional', 'ascendance' ); ?></th>
							<th><?php esc_html_e( 'Enterprise', 'ascendance' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$matrix = array(
							array( 'Intelligence Briefs (2/wk)',  '✓', '✓', '✓' ),
							array( 'Intelligence Briefs (unlimited)', '—', '✓', '✓' ),
							array( 'Dynamic Updates',             '✓', '✓', '✓' ),
							array( 'Strategic Dossiers',          '—', '~', '✓' ),
							array( 'Global Regional Coverage',    '—', '✓', '✓' ),
							array( 'REST API Access',             '—', '—', '✓' ),
							array( 'Bespoke Intelligence Requests','—', '—', '✓' ),
							array( 'Dedicated Analyst',           '—', '—', '✓' ),
						);
						foreach ( $matrix as $row ) :
						?>
							<tr>
								<td><strong><?php echo esc_html( $row[0] ); ?></strong></td>
								<td><span class="<?php echo $row[1] === '✓' ? 'as-matrix-check' : 'as-matrix-cross'; ?>"><?php echo esc_html( $row[1] ); ?></span></td>
								<td><span class="<?php echo $row[2] === '✓' ? 'as-matrix-check' : 'as-matrix-cross'; ?>"><?php echo esc_html( $row[2] ); ?></span></td>
								<td><span class="<?php echo $row[3] === '✓' ? 'as-matrix-check' : 'as-matrix-cross'; ?>"><?php echo esc_html( $row[3] ); ?></span></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</section>

	<?php get_template_part( 'template-parts/cta-strip', null, array(
		'heading'   => __( 'Choose Your Intelligence Tier', 'ascendance' ),
		'body'      => __( 'Start with Essential and upgrade as your intelligence needs grow. Cancel or switch anytime.', 'ascendance' ),
		'btn_label' => __( 'Start Free Trial', 'ascendance' ),
		'btn_url'   => home_url( '/newsletter/' ),
	) ); ?>

</main>

<?php get_footer(); ?>
