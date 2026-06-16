<?php
/**
 * The template for displaying Brief CPT Archives
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="site-main">

	<header class="page-header" style="background-color: var(--color-deep-navy); border-bottom: 1px solid var(--border-color); padding: var(--space-50) 0; text-align: center;">
		<div class="container">
			<span style="color: var(--color-red); font-family: var(--font-heading); text-transform: uppercase; font-weight: bold; font-size: var(--font-size-xs); letter-spacing: 1px;"><?php esc_html_e( 'Intelligence Ledger', 'ascendance' ); ?></span>
			<h1 class="page-title" style="margin-top: 10px; margin-bottom: 10px; color: var(--color-white);"><?php esc_html_e( 'Intelligence Briefs', 'ascendance' ); ?></h1>
			<p style="color: var(--text-secondary); max-width: 600px; margin: 0 auto; font-size: var(--font-size-sm);">
				<?php esc_html_e( 'Weekly forward-looking analysis, strategic reports, and impact claims covering global commerce and technology.', 'ascendance' ); ?>
			</p>
		</div>
	</header>

	<div class="content-wrapper" style="padding: var(--space-50) 0;">
		<div class="container">
			<?php if ( have_posts() ) : ?>

				<div style="display: flex; flex-direction: column; gap: var(--space-30); max-width: 900px; margin: 0 auto;">
					<?php
					while ( have_posts() ) :
						the_post();
						$post_id = get_the_ID();
						$claim = get_field( 'analytical_claim', $post_id );
						$tier = get_field( 'tier_access', $post_id ) ?: 'essential';
						?>
						<article id="post-<?php the_ID(); ?>" class="card" style="padding: var(--space-30); display: flex; flex-direction: column; gap: 12px;">
							<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
								<div class="card-meta" style="margin-bottom: 0;">
									<span class="card-tag"><?php the_terms( $post_id, 'industry', '', ', ', '' ); ?></span>
									<span><?php echo get_the_date(); ?></span>
								</div>
								<span style="font-size: 11px; font-family: var(--font-heading); color: var(--color-cream); background: rgba(255,255,255,0.08); padding: 2px 8px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);"><?php echo esc_html( ucfirst( $tier ) ); ?> Tier</span>
							</div>

							<h2 style="font-size: var(--font-size-md); margin-bottom: 0; line-height: 1.3;"><a href="<?php the_permalink(); ?>" style="color: var(--color-white);"><?php the_title(); ?></a></h2>

							<?php if ( ! empty( $claim ) ) : ?>
								<div style="font-family: var(--font-mono); font-size: var(--font-size-xs); color: #00FF66; background: #030810; padding: 10px 14px; border-radius: var(--radius-sm); border: 1px solid rgba(0,255,102,0.1); margin-top: 5px;">
									<span style="color: var(--color-red); font-weight: bold; margin-right: 8px;">CLAIM //</span>
									<?php echo esc_html( $claim ); ?>
								</div>
							<?php endif; ?>

							<div style="color: var(--text-secondary); font-size: var(--font-size-sm); margin-top: 5px;">
								<?php the_excerpt(); ?>
							</div>

							<div style="margin-top: 10px;">
								<a href="<?php the_permalink(); ?>" class="btn btn-secondary" style="padding: 8px 20px; font-size: 12px;"><?php esc_html_e( 'Access Briefing', 'ascendance' ); ?> <i class="fa-solid fa-arrow-right" style="margin-left: 8px; color: var(--color-red);"></i></a>
							</div>
						</article>
						<?php
					endwhile;
					?>
				</div>

				<div class="navigation-links" style="display: flex; justify-content: center; gap: 1rem; margin-top: var(--space-40);">
					<?php
					the_posts_pagination(
						array(
							'mid_size'  => 2,
							'prev_text' => sprintf( '<i class="fa-solid fa-arrow-left"></i> %s', esc_html__( 'Previous', 'ascendance' ) ),
							'next_text' => sprintf( '%s <i class="fa-solid fa-arrow-right"></i>', esc_html__( 'Next', 'ascendance' ) ),
						)
					);
					?>
				</div>

			<?php else : ?>

				<div style="text-align: center; padding: 4rem 0;">
					<i class="fa-regular fa-folder-open" style="font-size: 3rem; color: var(--color-red); margin-bottom: 1rem;"></i>
					<h2><?php esc_html_e( 'No Briefings Found', 'ascendance' ); ?></h2>
					<p style="color: var(--text-secondary); max-width: 500px; margin: 1rem auto 2rem auto;">
						<?php esc_html_e( 'There are no active intelligence briefs registered in this index.', 'ascendance' ); ?>
					</p>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary"><?php esc_html_e( 'Return Home', 'ascendance' ); ?></a>
				</div>

			<?php endif; ?>
		</div>
	</div>

</main>

<?php
get_footer();
