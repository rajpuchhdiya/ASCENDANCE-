<?php
/**
 * Front Page — Ascendance Intelligence Platform
 *
 * Newspaper Portal Layout
 * 
 * Top Alert Ticker → Portal Hero News Grid (Featured + Sub-stories) → Portal Main Body (Category grids + Dynamic updates sidebar) → Membership Tiers → Newsletter CTA
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="site-main">

	<!-- ═══ FLASH UPDATE TICKER ════════════════════════════════ -->
	<?php
	$ticker_query = new WP_Query( array(
		'post_type'      => 'update',
		'posts_per_page' => 1,
		'post_status'    => 'publish',
		'meta_query'     => array(
			array(
				'key'     => 'impact_assessment',
				'value'   => array( 'critical', 'high' ),
				'compare' => 'IN',
			),
		),
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );
	if ( ! $ticker_query->have_posts() ) {
		$ticker_query = new WP_Query( array(
			'post_type'      => 'update',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );
	}
	if ( $ticker_query->have_posts() ) :
		while ( $ticker_query->have_posts() ) :
			$ticker_query->the_post();
			$impact = '';
			if ( function_exists( 'get_field' ) ) {
				$impact = get_field( 'impact_assessment' ) ?: '';
			} else {
				$impact = get_post_meta( get_the_ID(), 'impact_assessment', true ) ?: '';
			}
			?>
			<div class="bg-navy-deep text-cream border-y border-brand-divider-dark py-3.5 px-6 md:px-8 flex items-center justify-between gap-4 text-xs font-sans">
				<div class="flex items-center gap-3 overflow-hidden">
					<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-sm bg-brand-red text-white uppercase tracking-wider font-bold font-mono animate-pulse">
						<span class="w-1.5 h-1.5 rounded-full bg-white"></span>
						<?php esc_html_e( 'Live Intelligence Flash', 'ascendance' ); ?>
					</span>
					<?php if ( $impact ) : ?>
						<?php echo ascendance_impact_badge( $impact ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php endif; ?>
					<a href="<?php the_permalink(); ?>" class="text-cream/90 hover:text-white font-medium hover:underline truncate">
						<?php the_title(); ?>
					</a>
				</div>
				<span class="text-cream/55 flex-shrink-0"><?php echo esc_html( human_time_diff( get_the_time('U'), current_time('timestamp') ) ) . ' ' . esc_html__( 'ago', 'ascendance' ); ?></span>
			</div>
			<?php
		endwhile;
		wp_reset_postdata();
	endif;
	?>

	<!-- ═══ PORTAL HERO ═════════════════════════════════════════ -->
	<section class="portal-hero-section py-16 bg-cream dark:bg-navy-deep border-b border-brand-divider-light dark:border-brand-divider-dark">
		<div class="container mx-auto px-6 md:px-8">
			<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
				
				<!-- Main Featured Story (Col Span 8) -->
				<div class="lg:col-span-8 flex flex-col">
					<?php
					$featured_id = 0;
					$featured_query = new WP_Query( array(
						'post_type'      => array( 'brief', 'dossier' ),
						'posts_per_page' => 1,
						'post_status'    => 'publish',
						'meta_query'     => array(
							array(
								'key'     => 'featured_flag',
								'value'   => '1',
								'compare' => '=',
							),
						),
						'orderby'        => 'date',
						'order'          => 'DESC',
					) );

					if ( $featured_query->have_posts() ) {
						$featured_query->the_post();
						$featured_id = get_the_ID();
					} else {
						// Fallback to latest brief
						$fallback_query = new WP_Query( array(
							'post_type'      => 'brief',
							'posts_per_page' => 1,
							'post_status'    => 'publish',
							'orderby'        => 'date',
							'order'          => 'DESC',
						) );
						if ( $fallback_query->have_posts() ) {
							$fallback_query->the_post();
							$featured_id = get_the_ID();
						}
					}

					if ( $featured_id ) :
						$post_type = get_post_type( $featured_id );
						$permalink = get_permalink( $featured_id );
						$thumb_url = has_post_thumbnail( $featured_id ) ? get_the_post_thumbnail_url( $featured_id, 'large' ) : '';
						
						// Get terms
						$topics = wp_get_post_terms( $featured_id, 'topic' );
						$topic_name = ! empty( $topics ) && ! is_wp_error( $topics ) ? $topics[0]->name : '';
						
						$tier_access = '';
						if ( function_exists( 'get_field' ) ) {
							$tier_access = get_field( 'tier_access', $featured_id ) ?: '';
						} else {
							$tier_access = get_post_meta( $featured_id, 'tier_access', true ) ?: '';
						}
						?>
						<article class="featured-story-card group flex flex-col bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark rounded-sm shadow-sm hover:shadow-md transition-all duration-300 h-full overflow-hidden">
							<a href="<?php echo esc_url( $permalink ); ?>" class="block relative aspect-video w-full overflow-hidden bg-navy-deep">
								<?php if ( $thumb_url ) : ?>
									<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php the_title_attribute(); ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
								<?php else : ?>
									<div class="w-full h-full bg-gradient-to-br from-navy-mid to-navy-deep flex items-center justify-center">
										<div class="opacity-10 scale-90 group-hover:scale-100 transition-transform duration-500">
											<svg class="w-24 h-24 text-cream" viewBox="0 0 24 28" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M5 23 L14 6.5 L11.5 7.8 L18.5 2.5 L19.5 9.5 L17 8.2 L7.5 23 Z" fill="currentColor" />
											</svg>
										</div>
									</div>
								<?php endif; ?>
								
								<div class="absolute top-4 left-4 flex flex-wrap gap-2">
									<span class="bg-brand-red text-white text-[9px] font-sans font-bold uppercase tracking-wider px-2 py-0.5 rounded-sm">
										<?php esc_html_e( 'Featured Analysis', 'ascendance' ); ?>
									</span>
									<?php if ( $topic_name ) : ?>
										<span class="bg-navy-deep/80 text-cream text-[9px] font-sans font-bold uppercase tracking-wider px-2 py-0.5 rounded-sm backdrop-blur-xs border border-brand-divider-dark/45">
											<?php echo esc_html( $topic_name ); ?>
										</span>
									<?php endif; ?>
								</div>
							</a>
							
							<div class="p-8 flex flex-col flex-grow justify-between">
								<div>
									<div class="flex items-center gap-3 text-[10px] font-sans text-brand-text-muted dark:text-cream/55 uppercase tracking-wider font-bold mb-3">
										<span><?php echo esc_html( ascendance_cpt_label( $post_type ) ); ?></span>
										<span>•</span>
										<span><?php echo get_the_date( 'M j, Y' ); ?></span>
										<?php if ( $tier_access ) : ?>
											<span>•</span>
											<?php echo ascendance_tier_badge( $tier_access ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
										<?php endif; ?>
									</div>
									
									<h2 class="text-2xl md:text-3xl font-sans font-bold text-brand-text-primary dark:text-white leading-tight mb-4 group-hover:text-brand-red dark:group-hover:text-brand-red-light transition-colors duration-150">
										<a href="<?php echo esc_url( $permalink ); ?>"><?php the_title(); ?></a>
									</h2>
									
									<p class="text-sm md:text-base text-brand-text-muted dark:text-cream/70 leading-relaxed font-serif mb-6 line-clamp-3">
										<?php echo esc_html( get_the_excerpt() ); ?>
									</p>
								</div>
								
								<div class="border-t border-brand-divider-light dark:border-brand-divider-dark/10 pt-4 mt-auto flex items-center justify-between">
									<span class="text-xs font-sans text-brand-text-muted dark:text-cream/55">
										<?php esc_html_e( 'By Ascendance Editorial Board', 'ascendance' ); ?>
									</span>
									<a href="<?php echo esc_url( $permalink ); ?>" class="text-xs font-sans font-bold text-brand-red hover:text-brand-red-light transition-colors duration-150 flex items-center gap-1">
										<?php esc_html_e( 'Read Full Analysis', 'ascendance' ); ?>
										<i class="fa-solid fa-arrow-right"></i>
									</a>
								</div>
							</div>
						</article>
						<?php
						wp_reset_postdata();
					endif;
					?>
				</div>
				
				<!-- Sub-stories List (Col Span 4) -->
				<div class="lg:col-span-4 flex flex-col justify-between gap-6">
					<div class="border-b border-brand-divider-light dark:border-brand-divider-dark/30 pb-3 flex items-center justify-between">
						<h3 class="font-sans text-xs font-bold uppercase tracking-wider text-brand-text-primary dark:text-white"><?php esc_html_e( 'Trending Briefings', 'ascendance' ); ?></h3>
						<span class="w-1.5 h-1.5 rounded-full bg-brand-red"></span>
					</div>
					
					<div class="flex flex-col gap-6 flex-grow">
						<?php
						$sub_stories_query = new WP_Query( array(
							'post_type'      => array( 'brief', 'dossier' ),
							'posts_per_page' => 3,
							'post_status'    => 'publish',
							'post__not_in'   => $featured_id ? array( $featured_id ) : array(),
							'orderby'        => 'date',
							'order'          => 'DESC',
						) );

						if ( $sub_stories_query->have_posts() ) :
							while ( $sub_stories_query->have_posts() ) :
								$sub_stories_query->the_post();
								$sub_id = get_the_ID();
								$sub_type = get_post_type();
								$sub_topics = wp_get_post_terms( $sub_id, 'topic' );
								$sub_topic = ! empty( $sub_topics ) && ! is_wp_error( $sub_topics ) ? $sub_topics[0]->name : '';
								
								$sub_tier = '';
								if ( function_exists( 'get_field' ) ) {
									$sub_tier = get_field( 'tier_access', $sub_id ) ?: '';
								} else {
									$sub_tier = get_post_meta( $sub_id, 'tier_access', true ) ?: '';
								}
								?>
								<article class="sub-story-item group flex gap-4 py-2 border-b border-brand-divider-light dark:border-brand-divider-dark/10 last:border-0 last:pb-0">
									<div class="flex flex-col flex-grow gap-2">
										<div class="flex flex-wrap items-center gap-2 text-[9px] font-sans font-bold uppercase tracking-wider">
											<?php if ( $sub_topic ) : ?>
												<span class="text-brand-red"><?php echo esc_html( $sub_topic ); ?></span>
											<?php endif; ?>
											<span class="text-brand-text-muted dark:text-cream/55"><?php echo get_the_date( 'M j' ); ?></span>
											<?php if ( $sub_tier ) : ?>
												<?php echo ascendance_tier_badge( $sub_tier ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
											<?php endif; ?>
										</div>
										
										<h4 class="text-sm font-sans font-bold text-brand-text-primary dark:text-white leading-snug group-hover:text-brand-red dark:group-hover:text-brand-red-light transition-colors duration-150">
											<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
										</h4>
										
										<p class="text-xs text-brand-text-muted dark:text-cream/60 font-serif line-clamp-2 leading-relaxed">
											<?php echo esc_html( get_the_excerpt() ); ?>
										</p>
									</div>
								</article>
								<?php
							endwhile;
							wp_reset_postdata();
						else :
							echo '<p class="text-xs text-brand-text-muted dark:text-cream/50">' . esc_html__( 'No other active briefings.', 'ascendance' ) . '</p>';
						endif;
						?>
					</div>
					
					<a href="<?php echo esc_url( home_url( '/intelligence/' ) ); ?>" class="btn btn-secondary border border-brand-divider-light dark:border-brand-divider-dark text-brand-text-primary dark:text-cream hover:bg-brand-divider-light dark:hover:bg-brand-divider-dark text-center py-2.5 text-xs font-bold w-full" id="portal-explore-cta">
						<?php esc_html_e( 'View All Briefings', 'ascendance' ); ?>
					</a>
				</div>
				
			</div>
		</div>
	</section>

	<!-- ═══ PORTAL MAIN BODY ══════════════════════════════════ -->
	<section class="portal-body-section py-16 bg-white dark:bg-navy">
		<div class="container mx-auto px-6 md:px-8">
			<div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
				
				<!-- Left Column: Categories Feeds (Col Span 8) -->
				<div class="lg:col-span-8 flex flex-col gap-16">
					
					<!-- Category 1: Geopolitics -->
					<div class="category-block">
						<div class="category-block-header border-b border-brand-red pb-3 mb-8 flex justify-between items-center">
							<h3 class="font-sans text-lg font-bold uppercase tracking-wider text-brand-text-primary dark:text-white">
								<span class="text-brand-red mr-2 font-mono">//</span><?php esc_html_e( 'Geopolitical Insights', 'ascendance' ); ?>
							</h3>
							<?php
							$geopolitics_term = get_term_by( 'slug', 'geopolitics', 'topic' );
							$geopolitics_url = $geopolitics_term && ! is_wp_error( $geopolitics_term ) ? get_term_link( $geopolitics_term ) : home_url( '/intelligence/' );
							?>
							<a href="<?php echo esc_url( $geopolitics_url ); ?>" class="text-xs text-brand-text-muted hover:text-brand-red transition-colors duration-150 font-sans font-bold flex items-center gap-1">
								<?php esc_html_e( 'All Geopolitics', 'ascendance' ); ?>
								<i class="fa-solid fa-chevron-right text-[9px]"></i>
							</a>
						</div>
						
						<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
							<?php
							$geopolitics_query = new WP_Query( array(
								'post_type'      => array( 'brief', 'dossier' ),
								'posts_per_page' => 3,
								'post_status'    => 'publish',
								'post__not_in'   => $featured_id ? array( $featured_id ) : array(),
								'tax_query'      => array(
									array(
										'taxonomy' => 'topic',
										'field'    => 'slug',
										'terms'    => 'geopolitics',
									),
								),
								'orderby'        => 'date',
								'order'          => 'DESC',
							) );

							if ( $geopolitics_query->have_posts() ) :
								while ( $geopolitics_query->have_posts() ) :
									$geopolitics_query->the_post();
									$card_id = get_the_ID();
									$card_type = get_post_type();
									$card_thumb = has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'medium' ) : '';
									
									$card_tier = '';
									if ( function_exists( 'get_field' ) ) {
										$card_tier = get_field( 'tier_access', $card_id ) ?: '';
									} else {
										$card_tier = get_post_meta( $card_id, 'tier_access', true ) ?: '';
									}
									?>
									<article class="category-card group flex flex-col bg-cream dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark rounded-sm overflow-hidden h-full">
										<a href="<?php the_permalink(); ?>" class="block relative aspect-video overflow-hidden bg-navy-deep">
											<?php if ( $card_thumb ) : ?>
												<img src="<?php echo esc_url( $card_thumb ); ?>" alt="<?php the_title_attribute(); ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
											<?php else : ?>
												<div class="w-full h-full bg-gradient-to-br from-navy-mid to-navy-deep flex items-center justify-center">
													<div class="opacity-10 scale-90 group-hover:scale-100 transition-transform duration-500">
														<svg class="w-12 h-12 text-cream" viewBox="0 0 24 28" fill="none" xmlns="http://www.w3.org/2000/svg">
															<path d="M5 23 L14 6.5 L11.5 7.8 L18.5 2.5 L19.5 9.5 L17 8.2 L7.5 23 Z" fill="currentColor" />
														</svg>
													</div>
												</div>
											<?php endif; ?>
											
											<?php if ( $card_tier ) : ?>
												<div class="absolute top-2 left-2">
													<?php echo ascendance_tier_badge( $card_tier ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
												</div>
											<?php endif; ?>
										</a>
										
										<div class="p-5 flex flex-col flex-grow justify-between">
											<div>
												<div class="text-[9px] font-sans text-brand-text-muted dark:text-cream/55 uppercase tracking-wider font-bold mb-2">
													<?php echo esc_html( ascendance_cpt_label( $card_type ) ); ?> • <?php echo get_the_date( 'M j, Y' ); ?>
												</div>
												<h4 class="text-sm font-sans font-bold text-brand-text-primary dark:text-white leading-snug mb-3 group-hover:text-brand-red dark:group-hover:text-brand-red-light transition-colors duration-150 line-clamp-2">
													<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
												</h4>
												<p class="text-xs text-brand-text-muted dark:text-cream/70 font-serif leading-relaxed line-clamp-3 mb-4">
													<?php echo esc_html( get_the_excerpt() ); ?>
												</p>
											</div>
											<a href="<?php the_permalink(); ?>" class="text-[10px] font-sans font-bold text-brand-red hover:text-brand-red-light transition-colors duration-150 flex items-center gap-1 mt-auto">
												<?php esc_html_e( 'Access Brief', 'ascendance' ); ?>
												<i class="fa-solid fa-arrow-right"></i>
											</a>
										</div>
									</article>
									<?php
								endwhile;
								wp_reset_postdata();
							else :
								echo '<p class="text-xs text-brand-text-muted dark:text-cream/50">' . esc_html__( 'No geopolitics briefs found.', 'ascendance' ) . '</p>';
							endif;
							?>
						</div>
					</div>
					
					<!-- Category 2: Economics & Infrastructure -->
					<div class="category-block">
						<div class="category-block-header border-b border-brand-red pb-3 mb-8 flex justify-between items-center">
							<h3 class="font-sans text-lg font-bold uppercase tracking-wider text-brand-text-primary dark:text-white">
								<span class="text-brand-red mr-2 font-mono">//</span><?php esc_html_e( 'Economics & Infrastructure', 'ascendance' ); ?>
							</h3>
							<?php
							$economics_term = get_term_by( 'slug', 'economics', 'topic' );
							$economics_url = $economics_term && ! is_wp_error( $economics_term ) ? get_term_link( $economics_term ) : home_url( '/intelligence/' );
							?>
							<a href="<?php echo esc_url( $economics_url ); ?>" class="text-xs text-brand-text-muted hover:text-brand-red transition-colors duration-150 font-sans font-bold flex items-center gap-1">
								<?php esc_html_e( 'All Economics', 'ascendance' ); ?>
								<i class="fa-solid fa-chevron-right text-[9px]"></i>
							</a>
						</div>
						
						<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
							<?php
							$economics_query = new WP_Query( array(
								'post_type'      => array( 'brief', 'dossier' ),
								'posts_per_page' => 3,
								'post_status'    => 'publish',
								'post__not_in'   => $featured_id ? array( $featured_id ) : array(),
								'tax_query'      => array(
									array(
										'taxonomy' => 'topic',
										'field'    => 'slug',
										'terms'    => 'economics',
									),
								),
								'orderby'        => 'date',
								'order'          => 'DESC',
							) );

							if ( $economics_query->have_posts() ) :
								while ( $economics_query->have_posts() ) :
									$economics_query->the_post();
									$card_id = get_the_ID();
									$card_type = get_post_type();
									$card_thumb = has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'medium' ) : '';
									
									$card_tier = '';
									if ( function_exists( 'get_field' ) ) {
										$card_tier = get_field( 'tier_access', $card_id ) ?: '';
									} else {
										$card_tier = get_post_meta( $card_id, 'tier_access', true ) ?: '';
									}
									?>
									<article class="category-card group flex flex-col bg-cream dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark rounded-sm overflow-hidden h-full">
										<a href="<?php the_permalink(); ?>" class="block relative aspect-video overflow-hidden bg-navy-deep">
											<?php if ( $card_thumb ) : ?>
												<img src="<?php echo esc_url( $card_thumb ); ?>" alt="<?php the_title_attribute(); ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
											<?php else : ?>
												<div class="w-full h-full bg-gradient-to-br from-navy-mid to-navy-deep flex items-center justify-center">
													<div class="opacity-10 scale-90 group-hover:scale-100 transition-transform duration-500">
														<svg class="w-12 h-12 text-cream" viewBox="0 0 24 28" fill="none" xmlns="http://www.w3.org/2000/svg">
															<path d="M5 23 L14 6.5 L11.5 7.8 L18.5 2.5 L19.5 9.5 L17 8.2 L7.5 23 Z" fill="currentColor" />
														</svg>
													</div>
												</div>
											<?php endif; ?>
											
											<?php if ( $card_tier ) : ?>
												<div class="absolute top-2 left-2">
													<?php echo ascendance_tier_badge( $card_tier ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
												</div>
											<?php endif; ?>
										</a>
										
										<div class="p-5 flex flex-col flex-grow justify-between">
											<div>
												<div class="text-[9px] font-sans text-brand-text-muted dark:text-cream/55 uppercase tracking-wider font-bold mb-2">
													<?php echo esc_html( ascendance_cpt_label( $card_type ) ); ?> • <?php echo get_the_date( 'M j, Y' ); ?>
												</div>
												<h4 class="text-sm font-sans font-bold text-brand-text-primary dark:text-white leading-snug mb-3 group-hover:text-brand-red dark:group-hover:text-brand-red-light transition-colors duration-150 line-clamp-2">
													<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
												</h4>
												<p class="text-xs text-brand-text-muted dark:text-cream/70 font-serif leading-relaxed line-clamp-3 mb-4">
													<?php echo esc_html( get_the_excerpt() ); ?>
												</p>
											</div>
											<a href="<?php the_permalink(); ?>" class="text-[10px] font-sans font-bold text-brand-red hover:text-brand-red-light transition-colors duration-150 flex items-center gap-1 mt-auto">
												<?php esc_html_e( 'Access Brief', 'ascendance' ); ?>
												<i class="fa-solid fa-arrow-right"></i>
											</a>
										</div>
									</article>
									<?php
								endwhile;
								wp_reset_postdata();
							else :
								echo '<p class="text-xs text-brand-text-muted dark:text-cream/50">' . esc_html__( 'No economics briefs found.', 'ascendance' ) . '</p>';
							endif;
							?>
						</div>
					</div>
					
					<!-- Category 3: Technology & Security -->
					<div class="category-block">
						<div class="category-block-header border-b border-brand-red pb-3 mb-8 flex justify-between items-center">
							<h3 class="font-sans text-lg font-bold uppercase tracking-wider text-brand-text-primary dark:text-white">
								<span class="text-brand-red mr-2 font-mono">//</span><?php esc_html_e( 'Technology & Security', 'ascendance' ); ?>
							</h3>
							<?php
							$technology_term = get_term_by( 'slug', 'technology', 'topic' );
							$technology_url = $technology_term && ! is_wp_error( $technology_term ) ? get_term_link( $technology_term ) : home_url( '/intelligence/' );
							?>
							<a href="<?php echo esc_url( $technology_url ); ?>" class="text-xs text-brand-text-muted hover:text-brand-red transition-colors duration-150 font-sans font-bold flex items-center gap-1">
								<?php esc_html_e( 'All Technology', 'ascendance' ); ?>
								<i class="fa-solid fa-chevron-right text-[9px]"></i>
							</a>
						</div>
						
						<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
							<?php
							$technology_query = new WP_Query( array(
								'post_type'      => array( 'brief', 'dossier' ),
								'posts_per_page' => 3,
								'post_status'    => 'publish',
								'post__not_in'   => $featured_id ? array( $featured_id ) : array(),
								'tax_query'      => array(
									array(
										'taxonomy' => 'topic',
										'field'    => 'slug',
										'terms'    => 'technology',
									),
								),
								'orderby'        => 'date',
								'order'          => 'DESC',
							) );

							if ( $technology_query->have_posts() ) :
								while ( $technology_query->have_posts() ) :
									$technology_query->the_post();
									$card_id = get_the_ID();
									$card_type = get_post_type();
									$card_thumb = has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'medium' ) : '';
									
									$card_tier = '';
									if ( function_exists( 'get_field' ) ) {
										$card_tier = get_field( 'tier_access', $card_id ) ?: '';
									} else {
										$card_tier = get_post_meta( $card_id, 'tier_access', true ) ?: '';
									}
									?>
									<article class="category-card group flex flex-col bg-cream dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark rounded-sm overflow-hidden h-full">
										<a href="<?php the_permalink(); ?>" class="block relative aspect-video overflow-hidden bg-navy-deep">
											<?php if ( $card_thumb ) : ?>
												<img src="<?php echo esc_url( $card_thumb ); ?>" alt="<?php the_title_attribute(); ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
											<?php else : ?>
												<div class="w-full h-full bg-gradient-to-br from-navy-mid to-navy-deep flex items-center justify-center">
													<div class="opacity-10 scale-90 group-hover:scale-100 transition-transform duration-500">
														<svg class="w-12 h-12 text-cream" viewBox="0 0 24 28" fill="none" xmlns="http://www.w3.org/2000/svg">
															<path d="M5 23 L14 6.5 L11.5 7.8 L18.5 2.5 L19.5 9.5 L17 8.2 L7.5 23 Z" fill="currentColor" />
														</svg>
													</div>
												</div>
											<?php endif; ?>
											
											<?php if ( $card_tier ) : ?>
												<div class="absolute top-2 left-2">
													<?php echo ascendance_tier_badge( $card_tier ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
												</div>
											<?php endif; ?>
										</a>
										
										<div class="p-5 flex flex-col flex-grow justify-between">
											<div>
												<div class="text-[9px] font-sans text-brand-text-muted dark:text-cream/55 uppercase tracking-wider font-bold mb-2">
													<?php echo esc_html( ascendance_cpt_label( $card_type ) ); ?> • <?php echo get_the_date( 'M j, Y' ); ?>
												</div>
												<h4 class="text-sm font-sans font-bold text-brand-text-primary dark:text-white leading-snug mb-3 group-hover:text-brand-red dark:group-hover:text-brand-red-light transition-colors duration-150 line-clamp-2">
													<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
												</h4>
												<p class="text-xs text-brand-text-muted dark:text-cream/70 font-serif leading-relaxed line-clamp-3 mb-4">
													<?php echo esc_html( get_the_excerpt() ); ?>
												</p>
											</div>
											<a href="<?php the_permalink(); ?>" class="text-[10px] font-sans font-bold text-brand-red hover:text-brand-red-light transition-colors duration-150 flex items-center gap-1 mt-auto">
												<?php esc_html_e( 'Access Brief', 'ascendance' ); ?>
												<i class="fa-solid fa-arrow-right"></i>
											</a>
										</div>
									</article>
									<?php
								endwhile;
								wp_reset_postdata();
							else :
								echo '<p class="text-xs text-brand-text-muted dark:text-cream/50">' . esc_html__( 'No technology briefs found.', 'ascendance' ) . '</p>';
							endif;
							?>
						</div>
					</div>
					
				</div>
				
				<!-- Right Column: Sidebar (Col Span 4) -->
				<div class="lg:col-span-4 flex flex-col gap-10">
					
					<!-- Sidebar Block 1: Live Intelligence Ledger -->
					<div class="bg-cream dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-6 rounded-sm">
						<div class="border-b border-brand-divider-light dark:border-brand-divider-dark/20 pb-3 mb-5 flex justify-between items-center">
							<h3 class="font-sans text-sm font-bold uppercase tracking-wider text-brand-text-primary dark:text-white"><?php esc_html_e( 'Intelligence Ledger', 'ascendance' ); ?></h3>
							<span class="flex h-2 w-2 relative">
								<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-red opacity-75"></span>
								<span class="relative inline-flex rounded-full h-2 w-2 bg-brand-red"></span>
							</span>
						</div>
						
						<div class="flex flex-col gap-6 relative before:absolute before:left-3.5 before:top-2 before:bottom-2 before:w-[1px] before:bg-brand-divider-light dark:before:bg-brand-divider-dark/50">
							<?php
							$timeline_query = new WP_Query( array(
								'post_type'      => 'update',
								'posts_per_page' => 5,
								'post_status'    => 'publish',
								'orderby'        => 'date',
								'order'          => 'DESC',
							) );

							if ( $timeline_query->have_posts() ) :
								while ( $timeline_query->have_posts() ) :
									$timeline_query->the_post();
									$item_id = get_the_ID();
									
									$item_impact = '';
									if ( function_exists( 'get_field' ) ) {
										$item_impact = get_field( 'impact_assessment', $item_id ) ?: '';
									} else {
										$item_impact = get_post_meta( $item_id, 'impact_assessment', true ) ?: '';
									}
									
									// Get parent brief ID
									$parent_brief_id = get_post_meta( $item_id, 'parent_brief', true );
									$parent_title = '';
									if ( $parent_brief_id ) {
										$parent_title = get_the_title( $parent_brief_id );
									}
									?>
									<div class="relative pl-8 flex flex-col gap-1">
										<!-- Timeline Node Bullet -->
										<span class="absolute left-2 top-1.5 w-3 h-3 rounded-full border-2 border-brand-red bg-cream dark:bg-navy-mid z-10"></span>
										
										<div class="flex items-center gap-2 flex-wrap text-[9px] font-sans font-bold uppercase tracking-wider">
											<span class="text-brand-text-muted dark:text-cream/55"><?php echo get_the_date( 'M j, G:i' ); ?></span>
											<?php if ( $item_impact ) : ?>
												<?php echo ascendance_impact_badge( $item_impact ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
											<?php endif; ?>
										</div>
										
										<h4 class="text-xs font-sans font-bold text-brand-text-primary dark:text-white leading-snug hover:text-brand-red dark:hover:text-brand-red-light transition-colors duration-150">
											<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
										</h4>
										
										<?php if ( $parent_title ) : ?>
											<span class="text-[9px] font-sans text-brand-text-muted dark:text-cream/40 truncate">
												<?php esc_html_e( 'Brief:', 'ascendance' ); ?> <a href="<?php echo esc_url( get_permalink( $parent_brief_id ) ); ?>" class="hover:underline"><?php echo esc_html( $parent_title ); ?></a>
											</span>
										<?php endif; ?>
									</div>
									<?php
								endwhile;
								wp_reset_postdata();
							else :
								echo '<p class="text-xs text-brand-text-muted dark:text-cream/50 pl-8">' . esc_html__( 'No recent timeline updates.', 'ascendance' ) . '</p>';
							endif;
							?>
						</div>
					</div>
					
					<!-- Sidebar Block 2: Strategic Dossiers -->
					<div class="bg-cream dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-6 rounded-sm">
						<div class="border-b border-brand-divider-light dark:border-brand-divider-dark/20 pb-3 mb-5">
							<h3 class="font-sans text-sm font-bold uppercase tracking-wider text-brand-text-primary dark:text-white"><?php esc_html_e( 'Strategic Dossiers', 'ascendance' ); ?></h3>
						</div>
						
						<div class="flex flex-col gap-4">
							<?php
							$dossiers_query = new WP_Query( array(
								'post_type'      => 'dossier',
								'posts_per_page' => 3,
								'post_status'    => 'publish',
								'orderby'        => 'date',
								'order'          => 'DESC',
							) );

							if ( $dossiers_query->have_posts() ) :
								while ( $dossiers_query->have_posts() ) :
									$dossiers_query->the_post();
									$dossier_id = get_the_ID();
									$regions = wp_get_post_terms( $dossier_id, 'region' );
									$region_name = ! empty( $regions ) && ! is_wp_error( $regions ) ? $regions[0]->name : '';
									
									$dossier_tier = '';
									if ( function_exists( 'get_field' ) ) {
										$dossier_tier = get_field( 'tier_access', $dossier_id ) ?: '';
									} else {
										$dossier_tier = get_post_meta( $dossier_id, 'tier_access', true ) ?: '';
									}
									?>
									<a href="<?php the_permalink(); ?>" class="group block p-4 bg-white dark:bg-navy border border-brand-divider-light dark:border-brand-divider-dark/30 hover:border-brand-red dark:hover:border-brand-red-light rounded-sm transition-all duration-150">
										<div class="flex items-center justify-between gap-2 mb-1.5">
											<span class="text-[9px] font-mono uppercase tracking-widest text-brand-red font-bold">
												<?php echo esc_html( $region_name ?: '// Actor Profile' ); ?>
											</span>
											<?php if ( $dossier_tier ) : ?>
												<?php echo ascendance_tier_badge( $dossier_tier ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
											<?php endif; ?>
										</div>
										<h4 class="text-xs font-sans font-bold text-brand-text-primary dark:text-white leading-snug group-hover:text-brand-red dark:group-hover:text-brand-red-light transition-colors duration-150">
											<?php the_title(); ?>
										</h4>
									</a>
									<?php
								endwhile;
								wp_reset_postdata();
							else :
								echo '<p class="text-xs text-brand-text-muted dark:text-cream/50">' . esc_html__( 'No living dossiers.', 'ascendance' ) . '</p>';
							endif;
							?>
						</div>
					</div>
					
				</div>
				
			</div>
		</div>
	</section>

	<!-- ═══ MEMBERSHIP TIERS ══════════════════════════════════ -->
	<section class="tiers-section section py-16 bg-cream dark:bg-navy-deep border-b border-brand-divider-light dark:border-brand-divider-dark" id="pricing">
		<div class="container mx-auto px-6 md:px-8">
			<div class="section-header max-w-[640px] mx-auto text-center mb-12">
				<span class="section-eyebrow text-xs uppercase tracking-widest text-brand-red font-sans font-bold mb-3 block"><?php esc_html_e( 'Membership', 'ascendance' ); ?></span>
				<h2 class="section-title text-3xl md:text-4xl font-sans font-bold text-brand-text-primary dark:text-white mb-4 reveal"><?php esc_html_e( 'Intelligence Tiers', 'ascendance' ); ?></h2>
				<p class="section-lead text-base text-brand-text-muted dark:text-cream/70 font-sans reveal reveal-delay-1"><?php esc_html_e( 'Choose the tier calibrated to your intelligence requirements. Upgrade or downgrade at any time.', 'ascendance' ); ?></p>
			</div>

			<div class="tiers-grid grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-[1080px] mx-auto items-stretch">
				<!-- Essential -->
				<div class="tier-card bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-8 rounded-sm flex flex-col justify-between transition-all duration-300 hover:shadow-md reveal">
					<div>
						<div class="tier-name text-xs uppercase tracking-widest text-brand-text-muted dark:text-cream/60 font-sans font-bold mb-4"><?php esc_html_e( 'Essential', 'ascendance' ); ?></div>
						<div class="tier-price text-4xl md:text-5xl font-sans font-bold text-brand-text-primary dark:text-white mb-6 flex items-baseline gap-1 [&_span]:text-sm [&_span]:text-brand-text-muted [&_span]:font-normal">$150<span>/mo</span></div>
						<p class="tier-desc text-sm text-brand-text-muted dark:text-cream/70 mb-8 leading-relaxed"><?php esc_html_e( 'Foundational intelligence access for individuals and small teams building their strategic awareness.', 'ascendance' ); ?></p>
						<ul class="tier-features list-none p-0 m-0 mb-8 flex flex-col gap-3 text-sm [&_li]:flex [&_li]:items-center [&_li]:gap-2 [&_li]:text-brand-text-primary [&_li]:dark:text-cream [&_li.locked]:text-brand-text-muted/50 [&_li.locked]:dark:text-cream/40">
							<li><?php esc_html_e( '2 Intelligence Briefs per week', 'ascendance' ); ?></li>
							<li><?php esc_html_e( 'Daily Dynamic Updates', 'ascendance' ); ?></li>
							<li><?php esc_html_e( 'Regional coverage (1 region)', 'ascendance' ); ?></li>
							<li><?php esc_html_e( 'Email newsletter digest', 'ascendance' ); ?></li>
							<li class="locked"><?php esc_html_e( 'Strategic Dossiers', 'ascendance' ); ?></li>
							<li class="locked"><?php esc_html_e( 'API access', 'ascendance' ); ?></li>
						</ul>
					</div>
					<a href="<?php echo esc_url( function_exists( 'pmpro_url' ) ? pmpro_url( 'levels' ) : home_url( '/membership-levels/' ) ); ?>" class="btn btn-secondary border border-brand-divider-light dark:border-brand-divider-dark text-brand-text-primary dark:text-cream hover:bg-brand-divider-light dark:hover:bg-brand-divider-dark w-full text-center py-2.5" id="tier-essential-cta">
						<?php esc_html_e( 'Start Essential', 'ascendance' ); ?>
					</a>
				</div>

				<!-- Professional (featured) -->
				<div class="tier-card featured bg-white dark:bg-navy border-2 border-brand-red p-8 rounded-sm flex flex-col justify-between shadow-lg relative lg:-translate-y-2 reveal reveal-delay-1">
					<div>
						<div class="tier-name text-xs uppercase tracking-widest text-brand-red font-sans font-bold mb-4"><?php esc_html_e( 'Professional', 'ascendance' ); ?></div>
						<div class="tier-price text-4xl md:text-5xl font-sans font-bold text-brand-text-primary dark:text-white mb-6 flex items-baseline gap-1 [&_span]:text-sm [&_span]:text-brand-text-muted [&_span]:font-normal">$299<span>/mo</span></div>
						<p class="tier-desc text-sm text-brand-text-muted dark:text-cream/70 mb-8 leading-relaxed"><?php esc_html_e( 'Comprehensive intelligence access for analysts, consultants, and mid-market enterprise teams.', 'ascendance' ); ?></p>
						<ul class="tier-features list-none p-0 m-0 mb-8 flex flex-col gap-3 text-sm [&_li]:flex [&_li]:items-center [&_li]:gap-2 [&_li]:text-brand-text-primary [&_li]:dark:text-cream [&_li.locked]:text-brand-text-muted/50 [&_li.locked]:dark:text-cream/40">
							<li><?php esc_html_e( 'All Intelligence Briefs (unlimited)', 'ascendance' ); ?></li>
							<li><?php esc_html_e( 'Real-time Dynamic Updates', 'ascendance' ); ?></li>
							<li><?php esc_html_e( 'Global regional coverage', 'ascendance' ); ?></li>
							<li><?php esc_html_e( 'Strategic Dossiers (read access)', 'ascendance' ); ?></li>
							<li><?php esc_html_e( 'Weekly analyst call', 'ascendance' ); ?></li>
							<li class="locked"><?php esc_html_e( 'API access', 'ascendance' ); ?></li>
						</ul>
					</div>
					<a href="<?php echo esc_url( function_exists( 'pmpro_url' ) ? pmpro_url( 'levels' ) : home_url( '/membership-levels/' ) ); ?>" class="btn btn-primary w-full text-center py-2.5" id="tier-professional-cta">
						<?php esc_html_e( 'Start Professional', 'ascendance' ); ?>
					</a>
				</div>

				<!-- Enterprise -->
				<div class="tier-card bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-8 rounded-sm flex flex-col justify-between transition-all duration-300 hover:shadow-md reveal reveal-delay-2">
					<div>
						<div class="tier-name text-xs uppercase tracking-widest text-brand-text-muted dark:text-cream/60 font-sans font-bold mb-4"><?php esc_html_e( 'Enterprise', 'ascendance' ); ?></div>
						<div class="tier-price text-4xl md:text-5xl font-sans font-bold text-brand-text-primary dark:text-white mb-6 flex items-baseline gap-1"><?php esc_html_e( 'Custom', 'ascendance' ); ?></div>
						<p class="tier-desc text-sm text-brand-text-muted dark:text-cream/70 mb-8 leading-relaxed"><?php esc_html_e( 'Institutional-grade intelligence for large enterprise, government, and investment teams.', 'ascendance' ); ?></p>
						<ul class="tier-features list-none p-0 m-0 mb-8 flex flex-col gap-3 text-sm [&_li]:flex [&_li]:items-center [&_li]:gap-2 [&_li]:text-brand-text-primary [&_li]:dark:text-cream">
							<li><?php esc_html_e( 'Everything in Professional', 'ascendance' ); ?></li>
							<li><?php esc_html_e( 'Full Dossier library access', 'ascendance' ); ?></li>
							<li><?php esc_html_e( 'REST API access', 'ascendance' ); ?></li>
							<li><?php esc_html_e( 'Bespoke intelligence requests', 'ascendance' ); ?></li>
							<li><?php esc_html_e( 'Dedicated analyst relationship', 'ascendance' ); ?></li>
							<li><?php esc_html_e( 'White-label report exports', 'ascendance' ); ?></li>
						</ul>
					</div>
					<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn btn-secondary border border-brand-divider-light dark:border-brand-divider-dark text-brand-text-primary dark:text-cream hover:bg-brand-divider-light dark:hover:bg-brand-divider-dark w-full text-center py-2.5" id="tier-enterprise-cta">
						<?php esc_html_e( 'Contact for Enterprise', 'ascendance' ); ?>
					</a>
				</div>
			</div>
		</div>
	</section>

	<!-- ═══ NEWSLETTER CTA ════════════════════════════════════ -->
	<?php
	get_template_part( 'template-parts/cta-strip', null, array(
		'heading'    => __( 'Get Intelligence in Your Inbox', 'ascendance' ),
		'body'       => __( 'Subscribe to the weekly Ascendance Brief — a curated digest of the most significant geopolitical, economic, and technology developments. Free for the first 30 days.', 'ascendance' ),
		'btn_label'  => __( 'Subscribe Free', 'ascendance' ),
		'btn_url'    => home_url( '/newsletter/' ),
		'btn2_label' => __( 'View Pricing', 'ascendance' ),
		'btn2_url'   => function_exists( 'pmpro_url' ) ? pmpro_url( 'levels' ) : home_url( '/membership-levels/' ),
	) );
	?>

</main><!-- #primary -->

<?php get_footer(); ?>
