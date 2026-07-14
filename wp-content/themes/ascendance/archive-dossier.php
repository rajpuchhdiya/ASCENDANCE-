<?php
/**
 * The template for displaying Dossier CPT Archives
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="site-main">

	<header class="page-header-premium bg-navy-deep text-white py-16 md:py-24 border-b border-brand-divider-dark">
		<div class="container mx-auto px-6 md:px-8">
			<span class="page-header-premium-eyebrow text-xs font-mono uppercase tracking-widest text-brand-red mb-4 block"><?php esc_html_e( 'High-Density Reports', 'ascendance' ); ?></span>
			<h1 class="page-header-premium-title text-3xl md:text-5xl font-sans font-bold leading-tight mb-6"><?php esc_html_e( 'Intelligence Dossiers', 'ascendance' ); ?></h1>
			<p class="page-header-premium-desc text-base md:text-lg text-cream/80 max-w-[720px] leading-relaxed">
				<?php esc_html_e( 'Structured dossiers mapping organizations, political figures, and technological trends, featuring complete cross-referenced brief lists.', 'ascendance' ); ?>
			</p>
		</div>
	</header>

	<div class="archive-layout-wrapper py-16 md:py-24 bg-cream dark:bg-navy-deep border-b border-brand-divider-light dark:border-brand-divider-dark">
		<div class="container mx-auto px-6 md:px-8">
			<?php if ( have_posts() ) : ?>

				<div class="archive-grid-container grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
					<?php
					while ( have_posts() ) :
						the_post();
						$post_id = get_the_ID();
						$tiers = ascendance_get_post_tiers( $post_id, 'professional' );
						$pdf = get_field( 'download_pdf', $post_id );
						$summary = get_field( 'executive_summary', $post_id );
						?>
						<article id="post-<?php the_ID(); ?>" class="card archive-card bg-white dark:bg-navy-mid border-l-[3px] border-l-brand-red border-y border-r border-brand-divider-light dark:border-brand-divider-dark p-8 rounded-sm shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between">
							<div>
								<div class="archive-card-meta-row flex justify-end mb-4">
									<div class="tier-badges-group flex gap-1.5 items-center flex-wrap">
										<?php foreach ( $tiers as $t ) : ?>
											<span class="tier-badge text-[10px] font-sans font-bold uppercase tracking-wider px-2 py-0.5 rounded-sm border border-brand-divider-light dark:border-brand-divider-dark text-brand-text-muted dark:text-cream/70 bg-cream dark:bg-navy"><?php echo esc_html( ucfirst( $t ) ); ?> Tier</span>
										<?php endforeach; ?>
									</div>
								</div>

								<h3 class="archive-card-title text-lg font-sans font-bold text-brand-text-primary dark:text-white mb-3 hover:text-brand-red dark:hover:text-brand-red-light transition-colors duration-150"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

								<?php if ( ! empty( $summary ) ) : ?>
									<p class="archive-card-excerpt text-sm text-brand-text-muted dark:text-cream/70 leading-relaxed mb-4">
										<?php echo esc_html( wp_trim_words( $summary, 20, '...' ) ); ?>
									</p>
								<?php endif; ?>

								<div class="archive-card-topics text-xs text-brand-text-muted dark:text-cream/60 font-sans mb-6 [&_a]:text-brand-red hover:[&_a]:text-brand-red-light transition-colors">
									<span class="topics-label font-bold mr-1"><?php esc_html_e( 'Topics:', 'ascendance' ); ?></span>
									<?php the_terms( $post_id, 'topic', '', ', ', '' ); ?>
								</div>
							</div>

							<div class="archive-card-footer border-t border-brand-divider-light dark:border-brand-divider-dark/10 pt-4 flex justify-between items-center text-xs font-sans text-brand-text-muted dark:text-cream/60">
								<a href="<?php the_permalink(); ?>" class="font-bold text-sm text-brand-red hover:text-brand-red-light flex items-center gap-1.5"><?php esc_html_e( 'Open Dossier', 'ascendance' ); ?> <i class="fa-solid fa-arrow-right text-xs"></i></a>
								<span class="archive-card-date flex items-center gap-1"><i class="fa-regular fa-calendar"></i><?php echo get_the_date( 'M Y' ); ?></span>
							</div>
						</article>
						<?php
					endwhile;
					?>
				</div>

				<div class="archive-pagination mt-12 flex justify-center">
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
					<h2 class="text-2xl font-sans font-bold text-brand-text-primary dark:text-white"><?php esc_html_e( 'No Dossiers Found', 'ascendance' ); ?></h2>
					<p class="text-sm text-brand-text-muted dark:text-cream/70 max-w-[400px] leading-relaxed mb-4">
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
