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
	<section class="page-hero bg-navy-deep text-white py-16 md:py-24 border-b border-brand-divider-dark">
		<div class="container mx-auto px-6 md:px-8">
			<div class="page-hero-inner">
				<p class="page-hero-eyebrow text-xs font-mono uppercase tracking-widest text-brand-red mb-4 block"><?php esc_html_e( '// Intelligence Products', 'ascendance' ); ?></p>
				<h1 class="page-hero-title text-3xl md:text-5xl font-sans font-bold leading-tight mb-6"><?php esc_html_e( 'Three Products. One Platform.', 'ascendance' ); ?></h1>
				<p class="page-hero-desc text-base md:text-lg text-cream/80 max-w-[720px] leading-relaxed"><?php esc_html_e( 'Intelligence Briefs, Dynamic Updates, and Strategic Dossiers — each designed for a distinct analytical workflow and decision-making cadence.', 'ascendance' ); ?></p>
			</div>
		</div>
	</section>

	<!-- ═══ SERVICES DETAIL ═══════════════════════════════════ -->
	<section class="services-section section py-20 bg-cream dark:bg-navy-deep border-b border-brand-divider-light dark:border-brand-divider-dark">
		<div class="container mx-auto px-6 md:px-8">

			<!-- 1. Intelligence Briefs -->
			<div class="service-item grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-24" id="briefs">
				<div class="service-content flex flex-col gap-6">
					<div class="service-number font-mono text-xs text-brand-red font-bold tracking-widest">01 / 03</div>
					<h2 class="text-3xl font-sans font-bold text-brand-text-primary dark:text-white"><?php esc_html_e( 'Intelligence Briefs', 'ascendance' ); ?></h2>
					<p class="text-brand-text-muted dark:text-cream/80 leading-relaxed"><?php esc_html_e( 'Weekly structured analysis documents covering geopolitical events, economic developments, technology shifts, and security dynamics. Each brief is built around a falsifiable analytical claim supported by sourced findings.', 'ascendance' ); ?></p>
					<ul class="service-feature-list list-none p-0 m-0 flex flex-col gap-3 text-sm text-brand-text-muted dark:text-cream/70 [&_i]:text-brand-red [&_i]:mr-2.5">
						<li><i class="fa-solid fa-check"></i><?php esc_html_e( 'Analytical claim with explicit confidence rating', 'ascendance' ); ?></li>
						<li><i class="fa-solid fa-check"></i><?php esc_html_e( 'Key findings with primary source citations', 'ascendance' ); ?></li>
						<li><i class="fa-solid fa-check"></i><?php esc_html_e( '3–5 key takeaways for rapid consumption', 'ascendance' ); ?></li>
						<li><i class="fa-solid fa-check"></i><?php esc_html_e( 'Executive summary for non-specialist readers', 'ascendance' ); ?></li>
						<li><i class="fa-solid fa-check"></i><?php esc_html_e( 'Version-controlled — updated as events evolve', 'ascendance' ); ?></li>
					</ul>
					<div class="mt-4">
						<a href="<?php echo esc_url( home_url( '/briefs/' ) ); ?>" class="btn btn-primary" id="services-briefs-cta"><?php esc_html_e( 'Browse Briefs', 'ascendance' ); ?></a>
					</div>
				</div>

				<div class="service-visual bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-8 rounded-sm shadow-sm">
					<div class="service-detail-visual-list flex flex-col gap-4">
						<div class="service-detail-visual-eyebrow text-xs font-mono uppercase tracking-widest text-brand-red mb-4 block"><?php esc_html_e( '// Brief Structure', 'ascendance' ); ?></div>
						<?php
						$brief_sections = array( 'Analytical Claim', 'Public Excerpt', 'Executive Summary', 'Key Findings', 'Key Takeaways', 'Source References', 'Related Briefs' );
						foreach ( $brief_sections as $i => $section ) :
						?>
							<div class="service-detail-visual-row flex items-center gap-4 py-2 border-b border-brand-divider-light dark:border-brand-divider-dark/10 last:border-b-0">
								<span class="service-detail-visual-index font-mono text-xs text-brand-red font-bold"><?php echo str_pad( $i + 1, 2, '0', STR_PAD_LEFT ); ?></span>
								<span class="service-detail-visual-label text-sm text-brand-text-primary dark:text-white font-sans font-medium"><?php echo esc_html( $section ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<!-- 2. Dynamic Updates -->
			<div class="service-item grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-24" id="updates">
				<div class="service-content flex flex-col gap-6 lg:order-last">
					<div class="service-number font-mono text-xs text-brand-red font-bold tracking-widest">02 / 03</div>
					<h2 class="text-3xl font-sans font-bold text-brand-text-primary dark:text-white"><?php esc_html_e( 'Dynamic Updates', 'ascendance' ); ?></h2>
					<p class="text-brand-text-muted dark:text-cream/80 leading-relaxed"><?php esc_html_e( 'Real-time intelligence dispatches linked to parent briefs. Published within 24 hours of significant developments — providing situational awareness without requiring you to monitor news feeds.', 'ascendance' ); ?></p>
					<ul class="service-feature-list list-none p-0 m-0 flex flex-col gap-3 text-sm text-brand-text-muted dark:text-cream/70 [&_i]:text-brand-red [&_i]:mr-2.5">
						<li><i class="fa-solid fa-check"></i><?php esc_html_e( 'Four-tier impact rating system (Low → Critical)', 'ascendance' ); ?></li>
						<li><i class="fa-solid fa-check"></i><?php esc_html_e( 'Linked to parent brief for full context', 'ascendance' ); ?></li>
						<li><i class="fa-solid fa-check"></i><?php esc_html_e( 'One-line summary optimised for scanning', 'ascendance' ); ?></li>
						<li><i class="fa-solid fa-check"></i><?php esc_html_e( 'Push notification for Critical-rated events', 'ascendance' ); ?></li>
						<li><i class="fa-solid fa-check"></i><?php esc_html_e( 'Full update content with analyst commentary', 'ascendance' ); ?></li>
					</ul>
					<div class="mt-4">
						<a href="<?php echo esc_url( home_url( '/updates/' ) ); ?>" class="btn btn-primary" id="services-updates-cta"><?php esc_html_e( 'Browse Updates', 'ascendance' ); ?></a>
					</div>
				</div>

				<div class="service-visual bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-8 rounded-sm shadow-sm lg:order-first">
					<div class="service-detail-visual-list flex flex-col gap-4">
						<div class="service-detail-visual-eyebrow text-xs font-mono uppercase tracking-widest text-brand-red mb-4 block"><?php esc_html_e( '// Impact Scale', 'ascendance' ); ?></div>
						<?php
						$impacts = array(
							'low'      => 'Low — Minor adjustments only',
							'medium'   => 'Medium — Notable shifting variables',
							'high'     => 'High — Major trend disruption',
							'critical' => 'Critical — Dynamic realignment required',
						);
						foreach ( $impacts as $key => $label ) :
						?>
							<div class="service-detail-visual-row-impact flex items-center gap-4 py-2 border-b border-brand-divider-light dark:border-brand-divider-dark/10 last:border-b-0">
								<?php echo ascendance_impact_badge( $key ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								<span class="service-detail-visual-impact-desc text-sm text-brand-text-muted dark:text-cream/80 font-sans font-medium"><?php echo esc_html( $label ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<!-- 3. Strategic Dossiers -->
			<div class="service-item grid grid-cols-1 lg:grid-cols-2 gap-12 items-center" id="dossiers">
				<div class="service-content flex flex-col gap-6">
					<div class="service-number font-mono text-xs text-brand-red font-bold tracking-widest">03 / 03</div>
					<h2 class="text-3xl font-sans font-bold text-brand-text-primary dark:text-white"><?php esc_html_e( 'Strategic Dossiers', 'ascendance' ); ?></h2>
					<p class="text-brand-text-muted dark:text-cream/80 leading-relaxed"><?php esc_html_e( 'Living intelligence documents on key actors, countries, and strategic themes. Updated monthly, each dossier integrates military, economic, technological, diplomatic, and political intelligence into one coherent picture.', 'ascendance' ); ?></p>
					<ul class="service-feature-list list-none p-0 m-0 flex flex-col gap-3 text-sm text-brand-text-muted dark:text-cream/70 [&_i]:text-brand-red [&_i]:mr-2.5">
						<li><i class="fa-solid fa-check"></i><?php esc_html_e( 'Five-dimension strategic overview', 'ascendance' ); ?></li>
						<li><i class="fa-solid fa-check"></i><?php esc_html_e( 'Living document — updated as the situation evolves', 'ascendance' ); ?></li>
						<li><i class="fa-solid fa-check"></i><?php esc_html_e( 'Key judgements summary at top', 'ascendance' ); ?></li>
						<li><i class="fa-solid fa-check"></i><?php esc_html_e( 'Update frequency: weekly or monthly', 'ascendance' ); ?></li>
						<li><i class="fa-solid fa-check"></i><?php esc_html_e( 'Professional & Enterprise tiers only', 'ascendance' ); ?></li>
					</ul>
					<div class="mt-4">
						<a href="<?php echo esc_url( home_url( '/dossiers/' ) ); ?>" class="btn btn-primary" id="services-dossiers-cta"><?php esc_html_e( 'Browse Dossiers', 'ascendance' ); ?></a>
					</div>
				</div>

				<div class="service-visual bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-8 rounded-sm shadow-sm">
					<div class="service-detail-visual-list flex flex-col gap-4">
						<div class="service-detail-visual-eyebrow text-xs font-mono uppercase tracking-widest text-brand-red mb-4 block"><?php esc_html_e( '// Dossier Dimensions', 'ascendance' ); ?></div>
						<?php
						$dims = array( 'Military & Security', 'Economic & Trade', 'Technology & Innovation', 'Diplomatic Positioning', 'Domestic Political Dynamics' );
						foreach ( $dims as $i => $dim ) :
						?>
							<div class="service-detail-visual-row flex items-center gap-4 py-2 border-b border-brand-divider-light dark:border-brand-divider-dark/10 last:border-b-0">
								<span class="service-detail-visual-index font-mono text-xs text-brand-red font-bold"><?php echo esc_html( chr(65 + $i) ); ?></span>
								<span class="service-detail-visual-label text-sm text-brand-text-primary dark:text-white font-sans font-medium"><?php echo esc_html( $dim ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

		</div>
	</section>

	<!-- ═══ ACCESS MATRIX ══════════════════════════════════════ -->
	<section class="section bg-navy-mid py-20 border-b border-brand-divider-dark">
		<div class="container mx-auto px-6 md:px-8">
			<div class="access-matrix-box max-w-[800px] mx-auto">
				<div class="access-matrix-card bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-8 rounded-sm shadow-sm reveal">
					<div class="access-matrix-card-title text-lg font-sans font-bold text-brand-text-primary dark:text-white mb-6 border-b border-brand-divider-light dark:border-brand-divider-dark/20 pb-4"><?php esc_html_e( 'Tier Access Matrix', 'ascendance' ); ?></div>
					<table class="access-matrix-table w-full border-collapse text-left text-sm text-brand-text-primary dark:text-cream">
						<thead>
							<tr class="border-b border-brand-divider-light dark:border-brand-divider-dark/20">
								<th class="pb-4 font-sans font-bold uppercase text-xs tracking-wider text-brand-text-muted dark:text-cream/50"><?php esc_html_e( 'Feature', 'ascendance' ); ?></th>
								<th class="pb-4 font-sans font-bold uppercase text-xs tracking-wider text-brand-text-muted dark:text-cream/50"><?php esc_html_e( 'Essential', 'ascendance' ); ?></th>
								<th class="pb-4 font-sans font-bold uppercase text-xs tracking-wider text-brand-text-muted dark:text-cream/50"><?php esc_html_e( 'Professional', 'ascendance' ); ?></th>
								<th class="pb-4 font-sans font-bold uppercase text-xs tracking-wider text-brand-text-muted dark:text-cream/50"><?php esc_html_e( 'Enterprise', 'ascendance' ); ?></th>
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
								<tr class="border-b border-brand-divider-light dark:border-brand-divider-dark/10 last:border-b-0">
									<td class="py-4 font-medium text-brand-text-primary dark:text-cream"><?php echo esc_html( $row[0] ); ?></td>
									<td class="py-4 <?php echo $row[1] === '✓' ? 'text-brand-red font-bold' : ( $row[1] === '~' ? 'text-brand-text-primary/70 dark:text-cream/70' : 'text-brand-text-muted/40 dark:text-cream/30' ); ?>">
										<?php echo $row[1] === '✓' ? '<i class="fa-solid fa-check"></i>' : ( $row[1] === '~' ? '<i class="fa-solid fa-minus"></i>' : '<i class="fa-solid fa-xmark"></i>' ); // phpcs:ignore ?>
									</td>
									<td class="py-4 <?php echo $row[2] === '✓' ? 'text-brand-red font-bold' : ( $row[2] === '~' ? 'text-brand-text-primary/70 dark:text-cream/70' : 'text-brand-text-muted/40 dark:text-cream/30' ); ?>">
										<?php echo $row[2] === '✓' ? '<i class="fa-solid fa-check"></i>' : ( $row[2] === '~' ? '<i class="fa-solid fa-minus"></i>' : '<i class="fa-solid fa-xmark"></i>' ); // phpcs:ignore ?>
									</td>
									<td class="py-4 <?php echo $row[3] === '✓' ? 'text-brand-red font-bold' : ( $row[3] === '~' ? 'text-brand-text-primary/70 dark:text-cream/70' : 'text-brand-text-muted/40 dark:text-cream/30' ); ?>">
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
