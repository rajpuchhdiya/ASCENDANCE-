<?php
/**
 * The template for displaying search results pages
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="site-main">

	<!-- Search Page Header -->
	<header class="page-header bg-navy-deep text-white py-16 md:py-24 border-b border-brand-divider-dark text-center">
		<div class="container mx-auto px-6 md:px-8 max-w-[700px]">
			<span class="text-xs font-mono uppercase tracking-widest text-brand-red mb-4 block"><?php esc_html_e( 'Search Engine', 'ascendance' ); ?></span>
			<h1 class="page-title text-3xl md:text-5xl font-sans font-bold leading-tight mb-6">
				<?php
				/* translators: %s: search query. */
				printf( esc_html__( 'Results for: %s', 'ascendance' ), '<span class="text-brand-red">' . esc_html( get_search_query() ) . '</span>' );
				?>
			</h1>
			
			<!-- Re-search bar form -->
			<form role="search" method="get" class="search-form flex items-center gap-0 max-w-[500px] mx-auto border border-brand-divider-dark rounded-sm overflow-hidden bg-white/5" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<input type="search" class="search-field flex-grow px-4 py-3 bg-transparent text-white font-serif text-sm outline-none border-none" placeholder="<?php echo esc_attr_x( 'Search briefings, dossiers...', 'placeholder', 'ascendance' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
				<button type="submit" class="search-submit bg-brand-red hover:bg-brand-red-light text-white border-none py-3 px-6 font-sans font-bold text-sm uppercase tracking-wider cursor-pointer transition-colors duration-150">
					<i class="fa-solid fa-magnifying-glass"></i>
				</button>
			</form>
		</div>
	</header>

	<div class="content-wrapper py-16 md:py-24 bg-cream dark:bg-navy-deep border-b border-brand-divider-light dark:border-brand-divider-dark">
		<div class="container mx-auto px-6 md:px-8">
			<?php if ( have_posts() ) : ?>

				<div class="flex flex-col gap-8 max-w-[900px] mx-auto">
					<?php
					while ( have_posts() ) :
						the_post();
						$post_id = get_the_ID();
						$post_type = get_post_type();
						$claim = get_field( 'analytical_claim', $post_id );
						$tier = get_field( 'tier_access', $post_id ) ?: 'essential';
						
						// Permission access check
						$user_has_access = class_exists( 'Ascendance\Core\Paywall' ) && Ascendance\Core\Paywall::get_instance()->user_has_access( $post_id );
						?>
						<article id="post-<?php the_ID(); ?>" class="card bg-white dark:bg-navy-mid border-y border-r border-brand-divider-light dark:border-brand-divider-dark p-8 rounded-sm shadow-sm hover:shadow-md transition-all duration-300 flex flex-col gap-4 <?php echo $user_has_access ? 'border-l-[3px] border-l-[#27AE60]' : 'border-l-[3px] border-l-brand-red'; ?>">
							<div class="flex justify-between items-center flex-wrap gap-2.5">
								<div class="card-meta text-xs text-brand-text-muted dark:text-cream/50 font-sans flex items-center gap-3">
									<span class="font-mono text-[10px] text-brand-red uppercase font-bold border border-brand-red/20 px-1.5 py-0.5 rounded-sm bg-brand-red/5"><?php echo esc_html( ucfirst( $post_type ) ); ?></span>
									<span class="card-tag text-brand-red font-bold"><?php the_terms( $post_id, 'topic', '', ', ', '' ); ?></span>
									<span><?php echo get_the_date(); ?></span>
								</div>
								
								<!-- Dynamic Lock State Indicator -->
								<div>
									<?php if ( $user_has_access ) : ?>
										<span class="text-[10px] font-sans font-bold uppercase tracking-wider px-2 py-0.5 rounded-sm border border-[#27AE60]/20 text-[#27AE60] bg-[#27AE60]/10 flex items-center gap-1">
											<i class="fa-solid fa-lock-open"></i> <?php esc_html_e( 'Access Granted', 'ascendance' ); ?>
										</span>
									<?php else : ?>
										<span class="text-[10px] font-sans font-bold uppercase tracking-wider px-2 py-0.5 rounded-sm border border-brand-red/20 text-brand-red bg-brand-red/10 flex items-center gap-1">
											<i class="fa-solid fa-lock"></i> <?php echo esc_html( ucfirst( $tier ) ); ?> <?php esc_html_e( 'Required', 'ascendance' ); ?>
										</span>
									<?php endif; ?>
								</div>
							</div>

							<h2 class="text-lg font-sans font-bold text-brand-text-primary dark:text-white leading-snug mb-1 hover:text-brand-red transition-colors">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h2>

							<?php if ( ! empty( $claim ) ) : ?>
								<div class="font-mono text-xs text-[#00FF66] bg-[#030810] p-4 rounded-sm border border-[#00FF66]/10">
									<span class="text-brand-red font-bold mr-2">CLAIM //</span>
									<?php echo esc_html( $claim ); ?>
								</div>
							<?php endif; ?>

							<div class="text-sm text-brand-text-muted dark:text-cream/70 leading-relaxed">
								<?php the_excerpt(); ?>
							</div>

							<div class="mt-2.5">
								<?php if ( $user_has_access ) : ?>
									<a href="<?php the_permalink(); ?>" class="btn border border-[#27AE60] text-[#27AE60] hover:bg-[#27AE60] hover:text-white px-4 py-2 text-xs font-bold font-sans rounded-sm transition-colors duration-150 flex items-center gap-1.5 max-w-max">
										<?php esc_html_e( 'Read Analysis', 'ascendance' ); ?> 
										<i class="fa-solid fa-arrow-right"></i>
									</a>
								<?php else : ?>
									<a href="<?php the_permalink(); ?>" class="btn btn-primary bg-brand-red hover:bg-brand-red-light text-white text-xs font-bold font-sans px-4 py-2 rounded-sm border-none transition-colors duration-150 flex items-center gap-1.5 max-w-max">
										<i class="fa-solid fa-lock"></i>
										<?php esc_html_e( 'Unlock Analytical Report', 'ascendance' ); ?>
									</a>
								<?php endif; ?>
							</div>
						</article>
						<?php
					endwhile;
					?>
				</div>

				<!-- Pagination -->
				<div class="navigation-links mt-12 flex justify-center">
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

				<!-- No Results State -->
				<div class="archive-empty-state text-center py-16 flex flex-col items-center gap-4">
					<i class="fa-solid fa-magnifying-glass-minus text-4xl text-brand-red mb-2"></i>
					<h2 class="text-2xl font-sans font-bold text-brand-text-primary dark:text-white"><?php esc_html_e( 'No Intelligence Found', 'ascendance' ); ?></h2>
					<p class="text-sm text-brand-text-muted dark:text-cream/70 max-w-[500px] leading-relaxed mb-4">
						<?php esc_html_e( 'We could not locate any intelligence reports, briefs, or dossiers matching your query. Verify the spelling or try alternative keywords.', 'ascendance' ); ?>
					</p>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-secondary border border-brand-divider-light dark:border-brand-divider-dark text-brand-text-primary dark:text-cream hover:bg-brand-divider-light dark:hover:bg-brand-divider-dark px-6 py-3 text-sm font-bold font-sans rounded-sm transition-colors duration-150"><?php esc_html_e( 'Return to Home Feed', 'ascendance' ); ?></a>
				</div>

			<?php endif; ?>
		</div>
	</div>

</main>

<?php
get_footer();
