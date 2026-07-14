<?php
/**
 * The template for displaying all single Update CPT posts
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="site-main register-editorial py-16 md:py-24 bg-cream dark:bg-navy-deep border-b border-brand-divider-light dark:border-brand-divider-dark">

	<?php
	while ( have_posts() ) :
		the_post();
		
		$post_id = get_the_ID();
		$parent_brief_id = get_field( 'parent_brief', $post_id );
		$impact = get_field( 'impact_assessment', $post_id ) ?: 'medium';
		$key_update = get_field( 'key_update', $post_id );
		$user_has_access = class_exists( 'Ascendance\Core\Paywall' ) && Ascendance\Core\Paywall::get_instance()->user_has_access();

		// Sibling updates timeline query
		$siblings_query = null;
		if ( ! empty( $parent_brief_id ) ) {
			$siblings_query = new WP_Query( array(
				'post_type'      => 'update',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'meta_query'     => array(
					array(
						'key'     => 'parent_brief',
						'value'     => $parent_brief_id,
						'compare' => '=',
					),
				),
				'orderby'        => 'date',
				'order'          => 'ASC',
			) );
		}
		?>

		<article id="post-<?php the_ID(); ?>" <?php post_class( 'container mx-auto px-6 md:px-8' ); ?>>
			<!-- Update Header -->
			<header class="editorial-header max-w-[800px] mx-auto mb-12 text-center flex flex-col items-center gap-4">
				<div class="flex justify-center items-center gap-2.5 flex-wrap">
					<span class="paywall-badge text-[10px] font-mono tracking-widest uppercase bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark text-brand-text-primary dark:text-cream px-2 py-0.5 rounded-sm"><?php esc_html_e( 'Real-time Update', 'ascendance' ); ?></span>
					<?php echo ascendance_impact_badge( $impact ); ?>
				</div>

				<h1 class="text-3xl md:text-5xl font-sans font-bold leading-tight text-brand-text-primary dark:text-white mb-2"><?php the_title(); ?></h1>
				
				<div class="flex justify-center items-center gap-6 text-xs text-brand-text-muted dark:text-cream/50 font-sans border-y border-brand-divider-light dark:border-brand-divider-dark/20 py-3.5 w-full">
					<span><i class="fa-regular fa-clock text-brand-red mr-1.5"></i> <?php echo get_the_date(); ?></span>
					<span><i class="fa-solid fa-earth-americas text-brand-red mr-1.5"></i> <?php the_terms( $post_id, 'region', '', ', ', '' ); ?></span>
				</div>
			</header>

			<div class="entry-content max-w-[740px] mx-auto">
				
				<!-- Parent Brief Connection Banner -->
				<?php if ( ! empty( $parent_brief_id ) ) : ?>
					<div class="parent-brief-connection bg-white dark:bg-navy-mid border-l-[3px] border-l-brand-red border-y border-r border-brand-divider-light dark:border-brand-divider-dark p-6 rounded-sm shadow-sm mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
						<div class="connection-content flex flex-col gap-1">
							<span class="connection-eyebrow text-[10px] font-mono tracking-widest text-brand-text-muted uppercase"><?php esc_html_e( 'Linked Intelligence Briefing', 'ascendance' ); ?></span>
							<strong class="connection-title text-sm font-sans font-bold text-brand-text-primary dark:text-white"><?php echo esc_html( get_the_title( $parent_brief_id ) ); ?></strong>
						</div>
						<a href="<?php echo esc_url( get_permalink( $parent_brief_id ) ); ?>" class="btn btn-secondary border border-brand-divider-light dark:border-brand-divider-dark text-brand-text-primary dark:text-cream hover:bg-brand-divider-light dark:hover:bg-brand-divider-dark py-2 px-4 text-xs font-bold font-sans rounded-sm transition-colors duration-150"><?php esc_html_e( 'Read Parent Brief', 'ascendance' ); ?></a>
					</div>
				<?php endif; ?>

				<!-- Gated content area wrapper for search engine crawlers (Google NewsArticle paywall guidelines) -->
				<?php if ( $user_has_access ) : ?>
					<div class="paywall-gated-content">
				<?php endif; ?>

				<!-- Main Content (Subject to paywall filtering) -->
				<div class="main-body-content text-brand-text-primary dark:text-cream leading-relaxed text-sm md:text-base mb-8">
					<?php the_content(); ?>
				</div>

				<!-- Key Update Specific Content (Gated) -->
				<?php if ( ! empty( $key_update ) && $user_has_access ) : ?>
					<div class="critical-adjustments-ledger mt-10 border-t border-brand-divider-light dark:border-brand-divider-dark/20 pt-8">
						<h3 class="text-base font-sans font-bold text-brand-text-primary dark:text-white mb-4 flex items-center gap-2">
							<i class="fa-solid fa-triangle-exclamation text-brand-red"></i>
							<?php esc_html_e( 'Critical Adjustments Ledger', 'ascendance' ); ?>
						</h3>
						<div class="text-sm text-brand-text-muted dark:text-cream/70 leading-relaxed">
							<?php echo wp_kses_post( $key_update ); ?>
						</div>
					</div>
				<?php endif; ?>

				<!-- Timeline siblings (Chronological Navigation, Gated) -->
				<?php if ( $siblings_query && $siblings_query->have_posts() && $siblings_query->post_count > 1 && $user_has_access ) : ?>
					<div class="timeline-siblings-section mt-12 border-t border-brand-divider-light dark:border-brand-divider-dark/20 pt-10">
						<h3 class="text-base font-sans font-bold text-brand-text-primary dark:text-white mb-6 flex items-center gap-2">
							<i class="fa-solid fa-list-ol text-brand-red"></i>
							<?php esc_html_e( 'Timeline Developments Ledger', 'ascendance' ); ?>
						</h3>
						
						<div class="sibling-nodes flex flex-col gap-4 relative before:content-[''] before:absolute before:left-3 before:top-2 before:bottom-2 before:w-0.5 before:bg-brand-divider-light dark:before:bg-brand-divider-dark/20 pl-8">
							<?php 
							while ( $siblings_query->have_posts() ) :
								$siblings_query->the_post();
								$sib_id = get_the_ID();
								$is_current = ( $sib_id === $post_id );
								$sib_impact = get_field( 'impact_assessment', $sib_id ) ?: 'medium';
								?>
								<div class="sibling-node-item relative">
									<!-- Dot indicator -->
									<div class="absolute -left-[27px] top-1.5 w-2.5 h-2.5 rounded-sm border-2 border-brand-red <?php echo $is_current ? 'bg-brand-red' : 'bg-white dark:bg-navy-mid'; ?>"></div>
									
									<div class="font-sans text-sm font-medium leading-relaxed">
										<?php if ( $is_current ) : ?>
											<span class="text-brand-text-primary dark:text-white font-bold"><?php the_title(); ?></span>
											<span class="font-mono text-[9px] text-brand-red uppercase font-bold ml-1.5">[<?php esc_html_e( 'CURRENT NODE', 'ascendance' ); ?>]</span>
										<?php else : ?>
											<a href="<?php the_permalink(); ?>" class="text-brand-text-muted hover:text-brand-red transition-colors"><?php the_title(); ?></a>
										<?php endif; ?>
										<span class="text-xs text-brand-text-muted dark:text-cream/50 ml-3"><?php echo get_the_date( 'M Y' ); ?></span>
									</div>
								</div>
							<?php 
							endwhile; 
							wp_reset_postdata();
							?>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( $user_has_access ) : ?>
					</div>
				<?php endif; ?>

			</div>

			<!-- Footer tags and comment thread -->
			<footer class="editorial-footer max-w-[740px] mx-auto border-t border-brand-divider-light dark:border-brand-divider-dark/20 pt-6 mt-12">
				<div class="flex justify-between items-center flex-wrap gap-4 text-xs font-sans text-brand-text-muted dark:text-cream/50 mb-8 [&_a]:text-brand-red hover:[&_a]:text-brand-red-light">
					<span>
						<?php esc_html_e( 'Regions Tracked: ', 'ascendance' ); ?>
						<strong><?php the_terms( $post_id, 'region', '', ', ', '' ); ?></strong>
					</span>
					<span>
						<?php esc_html_e( 'Tags: ', 'ascendance' ); ?>
						<strong><?php the_terms( $post_id, 'intelligence_tag', '', ', ', '' ); ?></strong>
					</span>
				</div>
				
				<?php if ( comments_open() || get_comments_number() ) : ?>
					<div class="comments-thread mt-8 border-t border-brand-divider-light dark:border-brand-divider-dark/20 pt-8">
						<?php comments_template(); ?>
					</div>
				<?php endif; ?>
			</footer>
		</article>

	<?php endwhile; ?>

</main>

<?php
get_footer();
