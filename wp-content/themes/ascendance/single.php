<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="site-main">

	<?php
	while ( have_posts() ) :
		the_post();
		?>

		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="page-header">
				<div class="container">
					<span class="hero-tagline" style="margin-bottom: 1rem;">
						<?php
						$categories = get_the_category();
						if ( ! empty( $categories ) ) {
							echo esc_html( $categories[0]->name );
						} else {
							echo esc_html__( 'Article', 'ascendance' );
						}
						?>
					</span>
					
					<h1 class="page-title"><?php the_title(); ?></h1>
					
					<div class="meta-info">
						<span><i class="fa-regular fa-calendar"></i> <?php echo get_the_date(); ?></span>
						<span><i class="fa-regular fa-user"></i> <?php the_author(); ?></span>
						<span><i class="fa-regular fa-comment"></i> <?php comments_number( 'No Comments', '1 Comment', '% Comments' ); ?></span>
					</div>
				</div>
			</header>

			<div class="content-wrapper">
				<div class="container">
					<div class="main-content">
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="post-featured-image">
								<?php the_post_thumbnail( 'full' ); ?>
							</div>
						<?php endif; ?>

						<div class="entry-content">
							<?php
							the_content();

							wp_link_pages(
								array(
									'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'ascendance' ),
									'after'  => '</div>',
								)
							);
							?>
						</div>

						<footer class="entry-footer" style="margin-top: 3rem; border-top: 1px solid var(--border-light); padding-top: 1.5rem;">
							<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
								<span class="cat-links">
									<i class="fa-solid fa-folder-open" style="color: var(--color-red); margin-right: 8px;"></i>
									<?php the_category( ', ' ); ?>
								</span>
								<?php the_tags( '<span class="tags-links"><i class="fa-solid fa-tags" style="color: var(--color-red); margin-right: 8px;"></i>', ', ', '</span>' ); ?>
							</div>
						</footer>

						<!-- Post Navigation -->
						<nav class="post-navigation" style="margin-top: 4rem; border-top: 1px solid var(--border-light); padding-top: 2rem; display: flex; justify-content: space-between; gap: 1.5rem;">
							<div class="nav-previous" style="flex: 1;">
								<?php
								$prev_post = get_previous_post();
								if ( ! empty( $prev_post ) ) :
									?>
									<span style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); display: block; margin-bottom: 0.3rem;"><?php esc_html_e( 'Previous Post', 'ascendance' ); ?></span>
									<a href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>" style="font-weight: 700; color: var(--text-primary); font-family: var(--font-heading); line-height: 1.3;">
										<i class="fa-solid fa-arrow-left" style="margin-right: 6px; font-size: 0.8rem; color: var(--color-red);"></i>
										<?php echo esc_html( $prev_post->post_title ); ?>
									</a>
								<?php endif; ?>
							</div>

							<div class="nav-next" style="flex: 1; text-align: right;">
								<?php
								$next_post = get_next_post();
								if ( ! empty( $next_post ) ) :
									?>
									<span style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); display: block; margin-bottom: 0.3rem;"><?php esc_html_e( 'Next Post', 'ascendance' ); ?></span>
									<a href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>" style="font-weight: 700; color: var(--text-primary); font-family: var(--font-heading); line-height: 1.3;">
										<?php echo esc_html( $next_post->post_title ); ?>
										<i class="fa-solid fa-arrow-right" style="margin-left: 6px; font-size: 0.8rem; color: var(--color-red);"></i>
									</a>
								<?php endif; ?>
							</div>
						</nav>

						<!-- Comments Area -->
						<?php
						if ( comments_open() || get_comments_number() ) :
							comments_template();
						endif;
						?>

					</div>
				</div>
			</div>
		</article>

		<?php
	endwhile; // End of the loop.
	?>

</main><!-- #primary -->

<?php
get_footer();
