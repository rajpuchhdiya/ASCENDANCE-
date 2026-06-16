<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="site-main">

	<header class="page-header">
		<div class="container">
			<?php
			if ( is_home() && ! is_front_page() ) :
				?>
				<h1 class="page-title"><?php single_post_title(); ?></h1>
				<?php
			else :
				?>
				<h1 class="page-title"><?php esc_html_e( 'Journal & Updates', 'ascendance' ); ?></h1>
				<?php
			endif;
			?>
			<p style="color: var(--text-secondary); margin-top: 10px;">
				<?php esc_html_e( 'Thoughts, write-ups, and developer highlights from the creators of Ascendance.', 'ascendance' ); ?>
			</p>
		</div>
	</header>

	<div class="content-wrapper">
		<div class="container">
			<?php if ( have_posts() ) : ?>

				<div class="posts-grid" style="margin-bottom: 4rem;">
					<?php
					while ( have_posts() ) :
						the_post();
						?>
						<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card' ); ?>>
							<div class="post-thumb">
								<?php if ( has_post_thumbnail() ) : ?>
									<?php the_post_thumbnail( 'medium_large' ); ?>
								<?php else : ?>
									<img src="https://placehold.co/600x400/080710/ffffff?text=<?php echo urlencode( get_the_title() ); ?>" alt="<?php the_title_attribute(); ?>">
								<?php endif; ?>
								
								<span class="post-category">
									<?php
									$categories = get_the_category();
									if ( ! empty( $categories ) ) {
										echo esc_html( $categories[0]->name );
									} else {
										echo esc_html__( 'Article', 'ascendance' );
									}
									?>
								</span>
							</div>

							<div class="post-content">
								<div class="post-meta">
									<span><i class="fa-regular fa-calendar"></i> <?php echo get_the_date(); ?></span>
									<span><i class="fa-regular fa-user"></i> <?php the_author(); ?></span>
								</div>
								<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<div class="post-excerpt">
									<?php the_excerpt(); ?>
								</div>
								<a href="<?php the_permalink(); ?>" class="read-more">
									<?php esc_html_e( 'Read More', 'ascendance' ); ?> <i class="fa-solid fa-arrow-right"></i>
								</a>
							</div>
						</article>
						<?php
					endwhile;
					?>
				</div>

				<div class="navigation-links" style="display: flex; justify-content: center; gap: 1rem;">
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
					<i class="fa-regular fa-folder-open" style="font-size: 3rem; color: var(--accent-purple); margin-bottom: 1rem;"></i>
					<h2><?php esc_html_e( 'No Posts Found', 'ascendance' ); ?></h2>
					<p style="color: var(--text-secondary); max-width: 500px; margin: 1rem auto 2rem auto;">
						<?php esc_html_e( 'It seems there are no articles published in this category or format yet. Check back soon!', 'ascendance' ); ?>
					</p>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary"><?php esc_html_e( 'Return Home', 'ascendance' ); ?></a>
				</div>

			<?php endif; ?>
		</div>
	</div>

</main><!-- #primary -->

<?php
get_footer();
