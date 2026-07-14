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
			<section class="page-hero bg-navy-deep text-white py-16 md:py-24 border-b border-brand-divider-dark">
				<div class="container mx-auto px-6 md:px-8">
					<div class="page-hero-inner">
						<p class="page-hero-eyebrow text-xs font-mono uppercase tracking-widest text-brand-red mb-4 block">// 
							<?php
							$categories = get_the_category();
							if ( ! empty( $categories ) ) {
								echo esc_html( strtoupper( $categories[0]->name ) );
							} else {
								echo esc_html__( 'ARTICLE', 'ascendance' );
							}
							?>
						</p>
						<h1 class="page-hero-title text-3xl md:text-5xl font-sans font-bold leading-tight mb-6"><?php the_title(); ?></h1>
						<div class="page-hero-desc text-xs font-sans text-cream/70 flex gap-6 items-center flex-wrap mt-4 [&_i]:text-brand-red [&_i]:mr-1.5">
							<span><i class="fa-regular fa-calendar"></i><?php echo get_the_date(); ?></span>
							<span><i class="fa-regular fa-user"></i><?php the_author(); ?></span>
							<span><i class="fa-regular fa-comment"></i><?php comments_number( 'No Comments', '1 Comment', '% Comments' ); ?></span>
						</div>
					</div>
				</div>
			</section>

			<div class="content-wrapper py-16 md:py-24 bg-cream dark:bg-navy-deep border-b border-brand-divider-light dark:border-brand-divider-dark">
				<div class="container mx-auto px-6 md:px-8">
					<div class="main-content max-w-[740px] mx-auto">
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="post-featured-image mb-10 max-w-full overflow-hidden border border-brand-divider-light dark:border-brand-divider-dark rounded-sm">
								<?php the_post_thumbnail( 'full' ); ?>
							</div>
						<?php endif; ?>

						<div class="entry-content text-brand-text-primary dark:text-cream leading-relaxed mb-8">
							<?php
							the_content();

							wp_link_pages(
								array(
									'before' => '<div class="page-links mt-6 text-sm">' . esc_html__( 'Pages:', 'ascendance' ),
									'after'  => '</div>',
								)
							);
							?>
						</div>

						<footer class="entry-footer mt-12 border-t border-brand-divider-light dark:border-brand-divider-dark/20 pt-6">
							<div class="flex justify-between items-center flex-wrap gap-4 text-xs font-sans text-brand-text-muted dark:text-cream/50 [&_i]:text-brand-red [&_i]:mr-1.5">
								<span class="cat-links">
									<i class="fa-solid fa-folder-open"></i>
									<?php the_category( ', ' ); ?>
								</span>
								<?php the_tags( '<span class="tags-links"><i class="fa-solid fa-tags"></i>', ', ', '</span>' ); ?>
							</div>
						</footer>

						<!-- Post Navigation -->
						<nav class="post-navigation mt-16 border-t border-brand-divider-light dark:border-brand-divider-dark/20 pt-8 flex justify-between gap-6">
							<div class="nav-previous flex-1">
								<?php
								$prev_post = get_previous_post();
								if ( ! empty( $prev_post ) ) :
									?>
									<span class="block text-[10px] font-sans font-bold uppercase tracking-wider text-brand-text-muted dark:text-cream/40 mb-1.5"><?php esc_html_e( 'Previous Post', 'ascendance' ); ?></span>
									<a href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>" class="font-sans font-bold text-sm text-brand-text-primary dark:text-cream hover:text-brand-red dark:hover:text-brand-red-light transition-colors duration-150 flex items-center gap-1.5">
										<i class="fa-solid fa-arrow-left text-xs"></i>
										<?php echo esc_html( $prev_post->post_title ); ?>
									</a>
								<?php endif; ?>
							</div>

							<div class="nav-next flex-1 text-right">
								<?php
								$next_post = get_next_post();
								if ( ! empty( $next_post ) ) :
									?>
									<span class="block text-[10px] font-sans font-bold uppercase tracking-wider text-brand-text-muted dark:text-cream/40 mb-1.5"><?php esc_html_e( 'Next Post', 'ascendance' ); ?></span>
									<a href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>" class="font-sans font-bold text-sm text-brand-text-primary dark:text-cream hover:text-brand-red dark:hover:text-brand-red-light transition-colors duration-150 flex items-center gap-1.5 justify-end">
										<?php echo esc_html( $next_post->post_title ); ?>
										<i class="fa-solid fa-arrow-right text-xs"></i>
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
