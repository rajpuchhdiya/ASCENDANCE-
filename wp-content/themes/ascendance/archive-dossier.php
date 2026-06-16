<?php
/**
 * The template for displaying Dossier CPT Archives
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="site-main">

	<header class="page-header" style="background-color: var(--color-deep-navy); border-bottom: 1px solid var(--border-color); padding: var(--space-50) 0; text-align: center;">
		<div class="container">
			<span style="color: var(--color-red); font-family: var(--font-heading); text-transform: uppercase; font-weight: bold; font-size: var(--font-size-xs); letter-spacing: 1px;"><?php esc_html_e( 'High-Density Reports', 'ascendance' ); ?></span>
			<h1 class="page-title" style="margin-top: 10px; margin-bottom: 10px; color: var(--color-white);"><?php esc_html_e( 'Intelligence Dossiers', 'ascendance' ); ?></h1>
			<p style="color: var(--text-secondary); max-width: 600px; margin: 0 auto; font-size: var(--font-size-sm);">
				<?php esc_html_e( 'Structured dossiers mapping organizations, political figures, and technological trends, featuring complete cross-referenced brief lists.', 'ascendance' ); ?>
			</p>
		</div>
	</header>

	<div class="content-wrapper" style="padding: var(--space-50) 0;">
		<div class="container">
			<?php if ( have_posts() ) : ?>

				<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: var(--space-30);">
					<?php
					while ( have_posts() ) :
						the_post();
						$post_id = get_the_ID();
						$tier = get_field( 'tier_access', $post_id ) ?: 'professional';
						$pdf = get_field( 'download_pdf', $post_id );
						$summary = get_field( 'executive_summary', $post_id );
						?>
						<article id="post-<?php the_ID(); ?>" class="card" style="display: flex; flex-direction: column; height: 100%;">
							<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-10);">
								<span class="card-tag" style="font-size: 11px;"><?php the_terms( $post_id, 'topic', '', ', ', '' ); ?></span>
								<span style="font-size: 11px; font-family: var(--font-heading); color: var(--color-cream); background: rgba(255,255,255,0.08); padding: 2px 8px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);"><?php echo esc_html( ucfirst( $tier ) ); ?> Tier</span>
							</div>

							<h3 style="font-size: var(--font-size-sm); margin-bottom: var(--space-20); flex-grow: 0; line-height: 1.3;"><a href="<?php the_permalink(); ?>" style="color: var(--color-white);"><?php the_title(); ?></a></h3>

							<?php if ( ! empty( $summary ) ) : ?>
								<p style="color: var(--text-secondary); font-size: 13px; line-height: 1.6; margin-bottom: var(--space-30); flex-grow: 1;">
									<?php echo esc_html( wp_trim_words( $summary, 20, '...' ) ); ?>
								</p>
							<?php endif; ?>

							<div style="border-top: 1px solid var(--border-color); padding-top: var(--space-20); display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-family: var(--font-heading);">
								<a href="<?php the_permalink(); ?>" style="font-weight: bold; color: var(--color-red);"><?php esc_html_e( 'Open Dossier', 'ascendance' ); ?> <i class="fa-solid fa-arrow-right"></i></a>
								<span style="color: var(--text-muted);"><i class="fa-regular fa-calendar"></i> <?php echo get_the_date( 'M Y' ); ?></span>
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
					<h2><?php esc_html_e( 'No Dossiers Found', 'ascendance' ); ?></h2>
					<p style="color: var(--text-secondary); max-width: 500px; margin: 1rem auto 2rem auto;">
						<?php esc_html_e( 'There are no active intelligence dossiers registered in this index.', 'ascendance' ); ?>
					</p>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary"><?php esc_html_e( 'Return Home', 'ascendance' ); ?></a>
				</div>

			<?php endif; ?>
		</div>
	</div>

</main>

<?php
get_footer();
