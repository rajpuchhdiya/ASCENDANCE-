<?php
/**
 * The template for displaying Dossier CPT Archives
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="as-page-wrap">

	<!-- Page Hero Band -->
	<section class="as-page-hero">
		<div class="as-page-hero-inner">
			<p class="as-page-eyebrow">// <?php esc_html_e( 'High-Density Reports', 'ascendance' ); ?></p>
			<h1 class="as-page-title"><?php esc_html_e( 'Intelligence Dossiers', 'ascendance' ); ?></h1>
			<p class="as-page-desc"><?php esc_html_e( 'Structured dossiers mapping organizations, political figures, and technological trends, featuring complete cross-referenced brief lists.', 'ascendance' ); ?></p>
		</div>
	</section>

	<div class="as-page-body">
		<div class="as-page-content" style="max-width:1200px;">
			<?php if ( have_posts() ) : ?>

				<div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:24px;margin:24px 0;">
					<?php
					while ( have_posts() ) :
						the_post();
						$post_id = get_the_ID();
						$tiers   = ascendance_get_post_tiers( $post_id, 'professional' );
						$summary = get_field( 'executive_summary', $post_id );
						?>
						<article id="post-<?php the_ID(); ?>" class="as-brief-card" style="display:flex;flex-direction:column;justify-between;">
							<div>
								<div class="as-brief-card-meta">
									<span><?php echo get_the_date( 'M Y' ); ?></span>
									<div style="display:flex;gap:6px;">
										<?php foreach ( $tiers as $t ) : ?>
											<span class="as-tier-badge"><?php echo esc_html( ucfirst( $t ) ); ?> Tier</span>
										<?php endforeach; ?>
									</div>
								</div>

								<h3 class="as-brief-card-title" style="font-size:20px;"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

								<?php if ( ! empty( $summary ) ) : ?>
									<p class="as-brief-excerpt" style="font-size:14px;">
										<?php echo esc_html( wp_trim_words( $summary, 22, '...' ) ); ?>
									</p>
								<?php else : ?>
									<div class="as-brief-excerpt" style="font-size:14px;">
										<?php the_excerpt(); ?>
									</div>
								<?php endif; ?>
							</div>

							<div class="as-brief-footer" style="margin-top:auto;">
								<div class="as-brief-topics" style="font-size:12px;">
									<span style="color:var(--ink-3);margin-right:4px;"><?php esc_html_e( 'Topics:', 'ascendance' ); ?></span>
									<?php the_terms( $post_id, 'topic', '', ', ', '' ); ?>
								</div>
								<a href="<?php the_permalink(); ?>" class="as-brief-link"><?php esc_html_e( 'Open Dossier', 'ascendance' ); ?> &rarr;</a>
							</div>
						</article>
					<?php endwhile; ?>
				</div>

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

				<div style="text-align:center;padding:60px 0;">
					<h2 style="font-family:var(--font-display);font-size:24px;color:var(--ink);"><?php esc_html_e( 'No Dossiers Found', 'ascendance' ); ?></h2>
					<p style="color:var(--ink-2);margin:12px 0 24px;"><?php esc_html_e( 'There are no active intelligence dossiers registered in this index.', 'ascendance' ); ?></p>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="as-btn primary"><?php esc_html_e( 'Return Home', 'ascendance' ); ?></a>
				</div>

			<?php endif; ?>
		</div>
	</div>

</main>

<?php
get_footer();

