<?php
/**
 * The template for displaying Brief CPT Archives
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="as-page-wrap">

	<!-- Page Hero Band -->
	<section class="as-page-hero">
		<div class="as-page-hero-inner">
			<p class="as-page-eyebrow">// <?php esc_html_e( 'Intelligence Ledger', 'ascendance' ); ?></p>
			<h1 class="as-page-title"><?php esc_html_e( 'Intelligence Briefs', 'ascendance' ); ?></h1>
			<p class="as-page-desc"><?php esc_html_e( 'Weekly forward-looking analysis, strategic reports, and impact claims covering global commerce and technology.', 'ascendance' ); ?></p>
		</div>
	</section>

	<div class="as-page-body">
		<div class="as-page-content" style="max-width:960px;">
			<?php if ( have_posts() ) : ?>

				<div class="as-brief-list">
					<?php
					while ( have_posts() ) :
						the_post();
						$post_id = get_the_ID();
						$claim   = get_field( 'analytical_claim', $post_id );
						$tiers   = ascendance_get_post_tiers( $post_id, 'essential' );
						?>
						<article id="post-<?php the_ID(); ?>" class="as-brief-card">
							<div class="as-brief-card-meta">
								<span><?php echo get_the_date(); ?></span>
								<div style="display:flex;gap:6px;">
									<?php foreach ( $tiers as $t ) : ?>
										<span class="as-tier-badge"><?php echo esc_html( ucfirst( $t ) ); ?> Tier</span>
									<?php endforeach; ?>
								</div>
							</div>

							<h2 class="as-brief-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

							<?php if ( ! empty( $claim ) ) : ?>
								<div class="as-brief-claim">
									<span class="as-brief-claim-label">CLAIM //</span>
									<?php echo esc_html( $claim ); ?>
								</div>
							<?php endif; ?>

							<div class="as-brief-excerpt">
								<?php the_excerpt(); ?>
							</div>

							<div class="as-brief-footer">
								<div class="as-brief-topics">
									<span style="color:var(--ink-3);margin-right:4px;"><?php esc_html_e( 'Topics:', 'ascendance' ); ?></span>
									<?php the_terms( $post_id, 'topic', '', ', ', '' ); ?>
								</div>
								<a href="<?php the_permalink(); ?>" class="as-brief-link"><?php esc_html_e( 'Access Briefing', 'ascendance' ); ?> &rarr;</a>
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
					<h2 style="font-family:var(--font-display);font-size:24px;color:var(--ink);"><?php esc_html_e( 'No Briefings Found', 'ascendance' ); ?></h2>
					<p style="color:var(--ink-2);margin:12px 0 24px;"><?php esc_html_e( 'There are no active intelligence briefs registered in this index.', 'ascendance' ); ?></p>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="as-btn primary"><?php esc_html_e( 'Return Home', 'ascendance' ); ?></a>
				</div>

			<?php endif; ?>
		</div>
	</div>

</main>

<?php
get_footer();

