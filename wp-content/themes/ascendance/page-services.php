<?php
/**
 * Template Name: Services
 * Page template for the Services/Products page.
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
				<p class="page-hero-eyebrow"><?php esc_html_e( '// Intelligence Products', 'ascendance' ); ?></p>
				<h1 class="page-hero-title"><?php esc_html_e( 'Three Products. One Platform.', 'ascendance' ); ?></h1>
				<p class="page-hero-desc"><?php esc_html_e( 'Intelligence Briefs, Dynamic Updates, and Strategic Dossiers — each designed for a distinct analytical workflow and decision-making cadence.', 'ascendance' ); ?></p>
			</div>
		</div>
	</section>

	<!-- ═══ SERVICES DETAIL ═══════════════════════════════════ -->
	<section class="services-section section-lg">
		<div class="container">

			<!-- 1. Intelligence Briefs -->
			<div class="service-item" id="briefs">
				<div class="service-content">
					<div class="service-number">01 / 03</div>
					<h2><?php esc_html_e( 'Intelligence Briefs', 'ascendance' ); ?></h2>
					<p><?php esc_html_e( 'Weekly structured analysis documents covering geopolitical events, economic developments, technology shifts, and security dynamics. Each brief is built around a falsifiable analytical claim supported by sourced findings.', 'ascendance' ); ?></p>
					<ul class="service-feature-list">
						<li><i class="fa-solid fa-check"></i><?php esc_html_e( 'Analytical claim with explicit confidence rating', 'ascendance' ); ?></li>
						<li><i class="fa-solid fa-check"></i><?php esc_html_e( 'Key findings with primary source citations', 'ascendance' ); ?></li>
						<li><i class="fa-solid fa-check"></i><?php esc_html_e( '3–5 key takeaways for rapid consumption', 'ascendance' ); ?></li>
						<li><i class="fa-solid fa-check"></i><?php esc_html_e( 'Executive summary for non-specialist readers', 'ascendance' ); ?></li>
						<li><i class="fa-solid fa-check"></i><?php esc_html_e( 'Version-controlled — updated as events evolve', 'ascendance' ); ?></li>
					</ul>
					<div style="margin-top:var(--space-6);">
						<a href="<?php echo esc_url( home_url( '/briefs/' ) ); ?>" class="btn btn-primary" id="services-briefs-cta"><?php esc_html_e( 'Browse Briefs', 'ascendance' ); ?></a>
					</div>
				</div>

				<div class="service-visual">
					<div style="position:relative;z-index:1;">
						<div style="font-family:var(--font-mono);font-size:0.65rem;color:var(--color-red);letter-spacing:2px;text-transform:uppercase;margin-bottom:var(--space-4);"><?php esc_html_e( '// Brief Structure', 'ascendance' ); ?></div>
						<?php
						$brief_sections = array( 'Analytical Claim', 'Public Excerpt', 'Executive Summary', 'Key Findings', 'Key Takeaways', 'Source References', 'Related Briefs' );
						foreach ( $brief_sections as $i => $section ) :
						?>
							<div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid rgba(255,255,255,0.04);">
								<span style="font-family:var(--font-mono);font-size:0.6rem;color:rgba(188,27,29,0.5);width:20px;"><?php echo str_pad( $i + 1, 2, '0', STR_PAD_LEFT ); ?></span>
								<span style="font-family:var(--font-heading);font-size:0.82rem;color:rgba(247,244,239,0.75);"><?php echo esc_html( $section ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<!-- 2. Dynamic Updates -->
			<div class="service-item reverse" id="updates">
				<div class="service-content">
					<div class="service-number">02 / 03</div>
					<h2><?php esc_html_e( 'Dynamic Updates', 'ascendance' ); ?></h2>
					<p><?php esc_html_e( 'Real-time intelligence dispatches linked to parent briefs. Published within 24 hours of significant developments — providing situational awareness without requiring you to monitor news feeds.', 'ascendance' ); ?></p>
					<ul class="service-feature-list">
						<li><i class="fa-solid fa-check"></i><?php esc_html_e( 'Four-tier impact rating system (Low → Critical)', 'ascendance' ); ?></li>
						<li><i class="fa-solid fa-check"></i><?php esc_html_e( 'Linked to parent brief for full context', 'ascendance' ); ?></li>
						<li><i class="fa-solid fa-check"></i><?php esc_html_e( 'One-line summary optimised for scanning', 'ascendance' ); ?></li>
						<li><i class="fa-solid fa-check"></i><?php esc_html_e( 'Push notification for Critical-rated events', 'ascendance' ); ?></li>
						<li><i class="fa-solid fa-check"></i><?php esc_html_e( 'Full update content with analyst commentary', 'ascendance' ); ?></li>
					</ul>
					<div style="margin-top:var(--space-6);">
						<a href="<?php echo esc_url( home_url( '/updates/' ) ); ?>" class="btn btn-primary" id="services-updates-cta"><?php esc_html_e( 'Browse Updates', 'ascendance' ); ?></a>
					</div>
				</div>

				<div class="service-visual">
					<div style="position:relative;z-index:1;">
						<div style="font-family:var(--font-mono);font-size:0.65rem;color:var(--color-red);letter-spacing:2px;text-transform:uppercase;margin-bottom:var(--space-4);"><?php esc_html_e( '// Impact Scale', 'ascendance' ); ?></div>
						<?php
						$impacts = array(
							'low'      => 'Low — Minor adjustments only',
							'medium'   => 'Medium — Notable shifting variables',
							'high'     => 'High — Major trend disruption',
							'critical' => 'Critical — Dynamic realignment required',
						);
						foreach ( $impacts as $key => $label ) :
						?>
							<div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid rgba(255,255,255,0.04);">
								<?php echo ascendance_impact_badge( $key ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								<span style="font-family:var(--font-heading);font-size:0.8rem;color:rgba(247,244,239,0.65);"><?php echo esc_html( $label ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<!-- 3. Strategic Dossiers -->
			<div class="service-item" id="dossiers">
				<div class="service-content">
					<div class="service-number">03 / 03</div>
					<h2><?php esc_html_e( 'Strategic Dossiers', 'ascendance' ); ?></h2>
					<p><?php esc_html_e( 'Living intelligence documents on key actors, countries, and strategic themes. Updated monthly, each dossier integrates military, economic, technological, diplomatic, and political intelligence into one coherent picture.', 'ascendance' ); ?></p>
					<ul class="service-feature-list">
						<li><i class="fa-solid fa-check"></i><?php esc_html_e( 'Five-dimension strategic overview', 'ascendance' ); ?></li>
						<li><i class="fa-solid fa-check"></i><?php esc_html_e( 'Living document — updated as the situation evolves', 'ascendance' ); ?></li>
						<li><i class="fa-solid fa-check"></i><?php esc_html_e( 'Key judgements summary at top', 'ascendance' ); ?></li>
						<li><i class="fa-solid fa-check"></i><?php esc_html_e( 'Update frequency: weekly or monthly', 'ascendance' ); ?></li>
						<li><i class="fa-solid fa-check"></i><?php esc_html_e( 'Professional & Enterprise tiers only', 'ascendance' ); ?></li>
					</ul>
					<div style="margin-top:var(--space-6);">
						<a href="<?php echo esc_url( home_url( '/dossiers/' ) ); ?>" class="btn btn-primary" id="services-dossiers-cta"><?php esc_html_e( 'Browse Dossiers', 'ascendance' ); ?></a>
					</div>
				</div>

				<div class="service-visual">
					<div style="position:relative;z-index:1;">
						<div style="font-family:var(--font-mono);font-size:0.65rem;color:var(--color-red);letter-spacing:2px;text-transform:uppercase;margin-bottom:var(--space-4);"><?php esc_html_e( '// Dossier Dimensions', 'ascendance' ); ?></div>
						<?php
						$dims = array( 'Military & Security', 'Economic & Trade', 'Technology & Innovation', 'Diplomatic Positioning', 'Domestic Political Dynamics' );
						foreach ( $dims as $i => $dim ) :
						?>
							<div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid rgba(255,255,255,0.04);">
								<span style="font-family:var(--font-mono);font-size:0.6rem;color:rgba(188,27,29,0.5);width:20px;"><?php echo esc_html( chr(65 + $i) ); ?></span>
								<span style="font-family:var(--font-heading);font-size:0.82rem;color:rgba(247,244,239,0.75);"><?php echo esc_html( $dim ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

		</div>
	</section>

	<!-- ═══ ACCESS MATRIX ══════════════════════════════════════ -->
	<section class="section bg-navy-mid">
		<div class="container">
			<div style="max-width:760px;margin:0 auto;">
				<div class="access-matrix reveal">
					<div class="access-matrix-title"><?php esc_html_e( 'Tier Access Matrix', 'ascendance' ); ?></div>
					<table class="access-table">
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
									<td><?php echo esc_html( $row[0] ); ?></td>
									<td class="<?php echo $row[1] === '✓' ? 'check' : ( $row[1] === '~' ? 'partial' : 'cross' ); ?>">
										<?php echo $row[1] === '✓' ? '<i class="fa-solid fa-check"></i>' : ( $row[1] === '~' ? '<i class="fa-solid fa-minus"></i>' : '<i class="fa-solid fa-xmark"></i>' ); // phpcs:ignore ?>
									</td>
									<td class="<?php echo $row[2] === '✓' ? 'check' : ( $row[2] === '~' ? 'partial' : 'cross' ); ?>">
										<?php echo $row[2] === '✓' ? '<i class="fa-solid fa-check"></i>' : ( $row[2] === '~' ? '<i class="fa-solid fa-minus"></i>' : '<i class="fa-solid fa-xmark"></i>' ); // phpcs:ignore ?>
									</td>
									<td class="<?php echo $row[3] === '✓' ? 'check' : ( $row[3] === '~' ? 'partial' : 'cross' ); ?>">
										<?php echo $row[3] === '✓' ? '<i class="fa-solid fa-check"></i>' : ( $row[3] === '~' ? '<i class="fa-solid fa-minus"></i>' : '<i class="fa-solid fa-xmark"></i>' ); // phpcs:ignore ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
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
