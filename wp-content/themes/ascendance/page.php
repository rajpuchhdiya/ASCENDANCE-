<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
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
					<h1 class="page-title"><?php the_title(); ?></h1>
				</div>
			</header>

			<div class="content-wrapper">
				<div class="container">
					<div class="main-content">
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="post-featured-image" style="margin-bottom: 2.5rem;">
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

						<?php
						// If comments are open or we have at least one comment, load up the comment template.
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
