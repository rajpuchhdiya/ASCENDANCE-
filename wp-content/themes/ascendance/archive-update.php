<?php
/**
 * The template for displaying Update CPT Archives
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="site-main">

	<header class="page-header" style="background-color: var(--color-deep-navy); border-bottom: 1px solid var(--border-color); padding: var(--space-50) 0; text-align: center;">
		<div class="container">
			<span style="color: var(--color-red); font-family: var(--font-heading); text-transform: uppercase; font-weight: bold; font-size: var(--font-size-xs); letter-spacing: 1px;"><?php esc_html_e( 'Real-time Feeds', 'ascendance' ); ?></span>
			<h1 class="page-title" style="margin-top: 10px; margin-bottom: 10px; color: var(--color-white);"><?php esc_html_e( 'Intelligence Updates', 'ascendance' ); ?></h1>
			<p style="color: var(--text-secondary); max-width: 600px; margin: 0 auto; font-size: var(--font-size-sm);">
				<?php esc_html_e( 'Dynamic updates tracking volatile shifts, policy announcements, and strategic commodity market disruptions.', 'ascendance' ); ?>
			</p>
		</div>
	</header>

	<div class="content-wrapper" style="padding: var(--space-50) 0;">
		<div class="container">
			<?php if ( have_posts() ) : ?>

				<div style="position: relative; max-width: 800px; margin: 0 auto; padding-left: var(--space-30); border-left: 2px solid var(--border-color);">
					
					<?php
					while ( have_posts() ) :
						the_post();
						$post_id = get_the_ID();
						$impact = get_field( 'impact_assessment', $post_id ) ?: 'medium';
						$parent_brief_id = get_field( 'parent_brief', $post_id );

						// Impact Assessment styles
						$impact_colors = array(
							'low'      => '#00FF66',
							'medium'   => '#FFCC00',
							'high'     => '#FF6600',
							'critical' => 'var(--color-red)',
						);
						$color = isset( $impact_colors[ $impact ] ) ? $impact_colors[ $impact ] : '#FFCC00';
						?>
						<article id="post-<?php the_ID(); ?>" class="card" style="margin-bottom: var(--space-30); padding: var(--space-30); position: relative;">
							
							<!-- Timeline indicator node -->
							<div style="position: absolute; left: -41px; top: 30px; width: 18px; height: 18px; border-radius: var(--radius-sm); background-color: var(--color-deep-navy); border: 3px solid <?php echo esc_attr( $color ); ?>; box-shadow: 0 0 10px <?php echo esc_attr( $color ); ?>;"></div>

							<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: var(--space-10);">
								<div class="card-meta" style="margin-bottom: 0;">
									<span><?php echo get_the_date( 'd F Y, H:i' ); ?></span>
									<span style="color: <?php echo esc_attr( $color ); ?>; font-family: var(--font-mono); font-weight: bold; font-size: 11px; text-transform: uppercase;">Impact: <?php echo esc_html( $impact ); ?></span>
								</div>
								<span style="font-size: 10px; font-family: var(--font-heading); color: var(--text-muted); text-transform: uppercase;"><?php the_terms( $post_id, 'region', '', ', ', '' ); ?></span>
							</div>

							<h2 style="font-size: var(--font-size-sm); margin-bottom: var(--space-10); line-height: 1.3;"><a href="<?php the_permalink(); ?>" style="color: var(--color-white);"><?php the_title(); ?></a></h2>

							<div style="color: var(--text-secondary); font-size: var(--font-size-sm);">
								<?php the_excerpt(); ?>
							</div>

							<div style="border-top: 1px dashed var(--border-color); margin-top: var(--space-20); padding-top: var(--space-10); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; font-size: 11px; font-family: var(--font-heading);">
								<?php if ( ! empty( $parent_brief_id ) ) : ?>
									<span style="color: var(--text-muted);"><?php esc_html_e( 'Linked Brief: ', 'ascendance' ); ?> <a href="<?php echo esc_url( get_permalink( $parent_brief_id ) ); ?>" style="color: var(--color-white); font-weight: bold;"><?php echo esc_html( get_the_title( $parent_brief_id ) ); ?></a></span>
								<?php endif; ?>
								<a href="<?php the_permalink(); ?>" style="color: var(--color-red); font-weight: bold;"><?php esc_html_e( 'Open Update details', 'ascendance' ); ?> <i class="fa-solid fa-arrow-right"></i></a>
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
					<h2><?php esc_html_e( 'No Updates Found', 'ascendance' ); ?></h2>
					<p style="color: var(--text-secondary); max-width: 500px; margin: 1rem auto 2rem auto;">
						<?php esc_html_e( 'There are no active intelligence updates registered in this timeline.', 'ascendance' ); ?>
					</p>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary"><?php esc_html_e( 'Return Home', 'ascendance' ); ?></a>
				</div>

			<?php endif; ?>
		</div>
	</div>

</main>

<?php
get_footer();
