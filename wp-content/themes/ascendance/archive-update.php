<?php
/**
 * The template for displaying Update CPT Archives
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="as-page-wrap">

	<!-- Page Hero Band -->
	<section class="as-page-hero">
		<div class="as-page-hero-inner">
			<p class="as-page-eyebrow">// <?php esc_html_e( 'Real-Time Feeds', 'ascendance' ); ?></p>
			<h1 class="as-page-title"><?php esc_html_e( 'Intelligence Updates', 'ascendance' ); ?></h1>
			<p class="as-page-desc"><?php esc_html_e( 'Dynamic updates tracking volatile shifts, policy announcements, and strategic commodity market disruptions.', 'ascendance' ); ?></p>
		</div>
	</section>

	<div class="as-page-body">
		<div class="as-page-content" style="max-width:1200px;">
			<div class="as-updates-layout">

				<!-- Left Sidebar: Telemetry Status Panel -->
				<aside>
					<div class="as-sidebar-card">
						<div class="as-telemetry-badge">
							<span class="as-telemetry-dot"></span>
							<?php esc_html_e( 'Live Telemetry Active', 'ascendance' ); ?>
						</div>
						<h3 class="as-sidebar-title"><?php esc_html_e( 'Feed Overview', 'ascendance' ); ?></h3>
						<p style="font-size:13px;color:var(--ink-2);line-height:1.5;margin:0 0 16px;">
							<?php esc_html_e( 'Operational logging ledger tracking global tactical shifts and regulatory alerts.', 'ascendance' ); ?>
						</p>

						<div class="as-stats-grid">
							<div>
								<div class="as-stat-lbl"><?php esc_html_e( 'Logs Rendered', 'ascendance' ); ?></div>
								<div class="as-stat-num">
									<?php
									global $wp_query;
									echo esc_html( $wp_query->found_posts );
									?>
								</div>
							</div>
							<div>
								<div class="as-stat-lbl"><?php esc_html_e( 'Feed Type', 'ascendance' ); ?></div>
								<div style="font-family:var(--font-ui);font-size:12px;font-weight:700;color:var(--red);margin-top:6px;"><?php esc_html_e( 'Tactical CPT', 'ascendance' ); ?></div>
							</div>
						</div>
					</div>

					<!-- Severity Classification Legend -->
					<div class="as-sidebar-card">
						<h4 style="font-family:var(--font-ui);font-weight:700;font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:var(--ink-3);margin:0 0 14px;padding-bottom:8px;border-bottom:1px solid var(--hairline-2);"><?php esc_html_e( 'Severity Ledger', 'ascendance' ); ?></h4>

						<div class="as-severity-list">
							<div class="as-sev-row">
								<div><span class="as-sev-dot" style="background:var(--red);"></span><strong><?php esc_html_e( 'Critical Alert', 'ascendance' ); ?></strong></div>
								<span style="color:var(--ink-3);font-size:11px;"><?php esc_html_e( 'Immediate Action', 'ascendance' ); ?></span>
							</div>
							<div class="as-sev-row">
								<div><span class="as-sev-dot" style="background:#E67E22;"></span><strong><?php esc_html_e( 'High Severity', 'ascendance' ); ?></strong></div>
								<span style="color:var(--ink-3);font-size:11px;"><?php esc_html_e( 'Significant Shift', 'ascendance' ); ?></span>
							</div>
							<div class="as-sev-row">
								<div><span class="as-sev-dot" style="background:#2980B9;"></span><strong><?php esc_html_e( 'Medium Severity', 'ascendance' ); ?></strong></div>
								<span style="color:var(--ink-3);font-size:11px;"><?php esc_html_e( 'Tactical Log', 'ascendance' ); ?></span>
							</div>
							<div class="as-sev-row">
								<div><span class="as-sev-dot" style="background:#27AE60;"></span><strong><?php esc_html_e( 'Low Severity', 'ascendance' ); ?></strong></div>
								<span style="color:var(--ink-3);font-size:11px;"><?php esc_html_e( 'General Update', 'ascendance' ); ?></span>
							</div>
						</div>
					</div>
				</aside>

				<!-- Right Main Feed -->
				<div>
					<?php if ( have_posts() ) : ?>

						<?php
						while ( have_posts() ) :
							the_post();
							$post_id          = get_the_ID();
							$impact           = get_field( 'impact_assessment', $post_id ) ?: 'medium';
							$parent_brief_id = get_field( 'parent_brief', $post_id );
							?>
							<article id="post-<?php the_ID(); ?>" class="as-update-card sev-<?php echo esc_attr( $impact ); ?>">
								<div class="as-brief-card-meta">
									<div style="display:flex;align-items:center;gap:10px;">
										<span><?php echo get_the_date( 'd.m.Y // H:i \U\T\C' ); ?></span>
										<span class="as-sev-badge sev-<?php echo esc_attr( $impact ); ?>"><?php echo esc_html( ucfirst( $impact ) ); ?></span>
									</div>
									<span class="as-tier-badge"><?php the_terms( $post_id, 'region', '', ', ', '' ); ?></span>
								</div>

								<h2 class="as-brief-card-title" style="font-size:20px;margin-bottom:8px;"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

								<div class="as-brief-excerpt">
									<?php the_excerpt(); ?>
								</div>

								<div class="as-brief-footer">
									<?php if ( ! empty( $parent_brief_id ) ) : ?>
										<div style="font-size:12px;color:var(--ink-2);background:var(--paper-2);padding:4px 10px;border-radius:2px;border:1px solid var(--hairline);">
											<span style="color:var(--red);font-weight:700;margin-right:4px;"><?php esc_html_e( 'Linked Brief:', 'ascendance' ); ?></span>
											<a href="<?php echo esc_url( get_permalink( $parent_brief_id ) ); ?>" style="color:var(--ink);text-decoration:none;font-weight:600;"><?php echo esc_html( get_the_title( $parent_brief_id ) ); ?></a>
										</div>
									<?php else : ?>
										<div></div>
									<?php endif; ?>

									<a href="<?php the_permalink(); ?>" class="as-brief-link"><?php esc_html_e( 'Open Intel Details', 'ascendance' ); ?> &rarr;</a>
								</div>
							</article>
						<?php endwhile; ?>

						<div class="archive-pagination" style="display:flex;justify-content:center;margin-top:32px;">
							<?php
							the_posts_pagination(
								array(
									'mid_size'  => 2,
									'prev_text' => esc_html__( '&larr; Previous', 'ascendance' ),
									'next_text' => esc_html__( 'Next &rarr;', 'ascendance' ),
								)
							);
							?>
						</div>

					<?php else : ?>

						<div style="text-align:center;padding:60px 0;background:var(--card);border:1px solid var(--hairline-2);">
							<h2 style="font-family:var(--font-display);font-size:24px;color:var(--ink);"><?php esc_html_e( 'No Updates Found', 'ascendance' ); ?></h2>
							<p style="color:var(--ink-2);margin:12px 0 24px;"><?php esc_html_e( 'There are no active intelligence updates registered in this timeline.', 'ascendance' ); ?></p>
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="as-btn primary"><?php esc_html_e( 'Return Home', 'ascendance' ); ?></a>
						</div>

					<?php endif; ?>
				</div>

			</div>
		</div>
	</div>

</main>

<?php
get_footer();

