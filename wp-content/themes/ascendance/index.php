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

	<section class="page-hero bg-navy-deep text-white py-16 md:py-24 border-b border-brand-divider-dark">
		<div class="container mx-auto px-6 md:px-8">
			<div class="page-hero-inner">
				<p class="page-hero-eyebrow text-xs font-mono uppercase tracking-widest text-brand-red mb-4 block"><?php esc_html_e( '// PLATFORM JOURNAL', 'ascendance' ); ?></p>
				<h1 class="page-hero-title text-3xl md:text-5xl font-sans font-bold leading-tight mb-6">
					<?php
					if ( is_home() && ! is_front_page() ) :
						single_post_title();
					else :
						esc_html_e( 'Journal & Updates', 'ascendance' );
					endif;
					?>
				</h1>
				<p class="page-hero-desc text-base md:text-lg text-cream/80 max-w-[720px] leading-relaxed">
					<?php esc_html_e( 'Thoughts, write-ups, and developer highlights from the creators of Ascendance.', 'ascendance' ); ?>
				</p>
			</div>
		</div>
	</section>

	<div class="content-wrapper py-16 md:py-24 bg-cream dark:bg-navy-deep border-b border-brand-divider-light dark:border-brand-divider-dark">
		<div class="container mx-auto px-6 md:px-8">
			<?php if ( have_posts() ) : ?>

				<div class="posts-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
					<?php
					while ( have_posts() ) :
						the_post();
						?>
						<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark rounded-sm overflow-hidden shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 flex flex-col justify-between' ); ?>>
							<div class="post-thumb relative overflow-hidden aspect-[16/10] bg-navy border-b border-brand-divider-light dark:border-brand-divider-dark/20">
								<?php if ( has_post_thumbnail() ) : ?>
									<?php the_post_thumbnail( 'medium_large', array( 'class' => 'w-full h-full object-cover' ) ); ?>
								<?php else : ?>
									<div class="w-full h-full bg-navy-deep flex items-center justify-center p-6 text-center text-cream/30 border-b border-brand-divider-dark/10">
										<span class="font-mono text-[10px] uppercase tracking-widest"><?php the_title(); ?></span>
									</div>
								<?php endif; ?>
								
								<span class="post-category absolute bottom-3 left-3 text-[10px] font-sans font-bold uppercase tracking-wider bg-brand-red text-white px-2 py-0.5 rounded-sm">
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

							<div class="post-content p-6 flex-grow flex flex-col justify-between">
								<div>
									<div class="post-meta flex gap-4 text-xs text-brand-text-muted dark:text-cream/50 font-sans mb-3 [&_i]:text-brand-red [&_i]:mr-1">
										<span><i class="fa-regular fa-calendar"></i> <?php echo get_the_date(); ?></span>
										<span><i class="fa-regular fa-user"></i> <?php the_author(); ?></span>
									</div>
									<h3 class="text-base font-sans font-bold text-brand-text-primary dark:text-white mb-3 hover:text-brand-red dark:hover:text-brand-red-light transition-colors duration-150 line-clamp-2"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
									<div class="post-excerpt text-sm text-brand-text-muted dark:text-cream/70 leading-relaxed mb-4 line-clamp-3">
										<?php the_excerpt(); ?>
									</div>
								</div>
								<a href="<?php the_permalink(); ?>" class="read-more font-sans font-bold text-xs text-brand-red hover:text-brand-red-light transition-colors duration-150 flex items-center gap-1 mt-auto">
									<?php esc_html_e( 'Read More', 'ascendance' ); ?> <i class="fa-solid fa-arrow-right"></i>
								</a>
							</div>
						</article>
						<?php
					endwhile;
					?>
				</div>

				<div class="navigation-links flex justify-center mt-12">
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

				<div class="archive-empty-state text-center py-16 flex flex-col items-center gap-4">
					<i class="fa-regular fa-folder-open text-4xl text-brand-red mb-2"></i>
					<h2 class="text-2xl font-sans font-bold text-brand-text-primary dark:text-white"><?php esc_html_e( 'No Posts Found', 'ascendance' ); ?></h2>
					<p class="text-sm text-brand-text-muted dark:text-cream/70 max-w-[400px] leading-relaxed mb-4">
						<?php esc_html_e( 'It seems there are no articles published in this category or format yet. Check back soon!', 'ascendance' ); ?>
					</p>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary bg-brand-red hover:bg-brand-red-light text-white border-none py-2.5 px-5 text-sm font-bold font-sans rounded-sm transition-colors"><?php esc_html_e( 'Return Home', 'ascendance' ); ?></a>
				</div>

			<?php endif; ?>
		</div>
	</div>

</main><!-- #primary -->

<?php
get_footer();
