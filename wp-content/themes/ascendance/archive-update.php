<?php
/**
 * The template for displaying Update CPT Archives
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="site-main">

	<section class="page-hero bg-navy-deep text-white py-16 md:py-24 border-b border-brand-divider-dark">
		<div class="container mx-auto px-6 md:px-8">
			<div class="page-hero-inner">
				<p class="page-hero-eyebrow text-xs font-mono uppercase tracking-widest text-brand-red mb-4 block"><?php esc_html_e( '// REAL-TIME FEEDS', 'ascendance' ); ?></p>
				<h1 class="page-hero-title text-3xl md:text-5xl font-sans font-bold leading-tight mb-6"><?php esc_html_e( 'Intelligence Updates', 'ascendance' ); ?></h1>
				<p class="page-hero-desc text-base md:text-lg text-cream/80 max-w-[720px] leading-relaxed">
					<?php esc_html_e( 'Dynamic updates tracking volatile shifts, policy announcements, and strategic commodity market disruptions.', 'ascendance' ); ?>
				</p>
			</div>
		</div>
	</section>

	<div class="content-wrapper py-16 md:py-24 bg-cream dark:bg-navy-deep border-b border-brand-divider-light dark:border-brand-divider-dark">
		<div class="container mx-auto px-6 md:px-8">
			
			<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
				
				<!-- Left Column: Sticky Feed Controller -->
				<div class="lg:col-span-1 lg:sticky lg:top-24 flex flex-col gap-6">
					
					<!-- Telemetry Status Panel -->
					<div class="bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-6 rounded-sm shadow-sm">
						<div class="flex items-center gap-2 mb-4">
							<span class="relative flex h-2 w-2">
								<span class="animate-pulse absolute inline-flex h-full w-full rounded-full bg-brand-red opacity-75"></span>
								<span class="relative inline-flex rounded-full h-2 w-2 bg-brand-red"></span>
							</span>
							<span class="text-xs font-mono uppercase tracking-wider text-brand-red font-bold"><?php esc_html_e( 'Live Telemetry Active', 'ascendance' ); ?></span>
						</div>
						
						<h3 class="text-lg font-sans font-bold text-brand-text-primary dark:text-white mb-2"><?php esc_html_e( 'Feed Overview', 'ascendance' ); ?></h3>
						<p class="text-xs text-brand-text-muted dark:text-cream/70 leading-relaxed mb-6">
							<?php esc_html_e( 'Operational logging ledger tracking global tactical shifts and regulatory alerts.', 'ascendance' ); ?>
						</p>
						
						<!-- Stats Grid -->
						<div class="grid grid-cols-2 gap-4 border-t border-brand-divider-light dark:border-brand-divider-dark/40 pt-4">
							<div>
								<span class="block text-[10px] font-sans font-bold text-brand-text-muted dark:text-cream/50 uppercase tracking-wide"><?php esc_html_e( 'Logs Rendered', 'ascendance' ); ?></span>
								<span class="text-2xl font-sans font-bold text-brand-text-primary dark:text-white">
									<?php 
									global $wp_query;
									echo esc_html( $wp_query->found_posts ); 
									?>
								</span>
							</div>
							<div>
								<span class="block text-[10px] font-sans font-bold text-brand-text-muted dark:text-cream/50 uppercase tracking-wide"><?php esc_html_e( 'Feed Type', 'ascendance' ); ?></span>
								<span class="text-xs font-mono font-bold text-brand-red uppercase pt-1.5 block"><?php esc_html_e( 'Tactical CPT', 'ascendance' ); ?></span>
							</div>
						</div>
					</div>
					
					<!-- Severity Classification Legend -->
					<div class="bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-6 rounded-sm shadow-sm flex flex-col gap-4">
						<h4 class="text-xs font-sans font-bold text-brand-text-primary dark:text-white uppercase tracking-wider border-b border-brand-divider-light dark:border-brand-divider-dark/40 pb-2"><?php esc_html_e( 'Severity Ledger', 'ascendance' ); ?></h4>
						
						<div class="flex flex-col gap-3">
							<div class="flex items-center justify-between text-xs">
								<div class="flex items-center gap-2">
									<span class="w-2.5 h-2.5 rounded-sm bg-brand-red"></span>
									<span class="font-sans font-bold text-brand-text-primary dark:text-cream"><?php esc_html_e( 'Critical Alert', 'ascendance' ); ?></span>
								</div>
								<span class="font-mono text-[10px] text-brand-text-muted dark:text-cream/50"><?php esc_html_e( 'Immediate Action', 'ascendance' ); ?></span>
							</div>
							<div class="flex items-center justify-between text-xs">
								<div class="flex items-center gap-2">
									<span class="w-2.5 h-2.5 rounded-sm bg-[#E67E22]"></span>
									<span class="font-sans font-bold text-brand-text-primary dark:text-cream"><?php esc_html_e( 'High Severity', 'ascendance' ); ?></span>
								</div>
								<span class="font-mono text-[10px] text-brand-text-muted dark:text-cream/50"><?php esc_html_e( 'Significant Shift', 'ascendance' ); ?></span>
							</div>
							<div class="flex items-center justify-between text-xs">
								<div class="flex items-center gap-2">
									<span class="w-2.5 h-2.5 rounded-sm bg-[#2980B9]"></span>
									<span class="font-sans font-bold text-brand-text-primary dark:text-cream"><?php esc_html_e( 'Medium Severity', 'ascendance' ); ?></span>
								</div>
								<span class="font-mono text-[10px] text-brand-text-muted dark:text-cream/50"><?php esc_html_e( 'Tactical Log', 'ascendance' ); ?></span>
							</div>
							<div class="flex items-center justify-between text-xs">
								<div class="flex items-center gap-2">
									<span class="w-2.5 h-2.5 rounded-sm bg-[#27AE60]"></span>
									<span class="font-sans font-bold text-brand-text-primary dark:text-cream"><?php esc_html_e( 'Low Severity', 'ascendance' ); ?></span>
								</div>
								<span class="font-mono text-[10px] text-brand-text-muted dark:text-cream/50"><?php esc_html_e( 'General Update', 'ascendance' ); ?></span>
							</div>
						</div>
					</div>
					
				</div>
				
				<!-- Right Column: Main Feed -->
				<div class="lg:col-span-2 flex flex-col gap-6">
					<?php if ( have_posts() ) : ?>
						
						<?php
						while ( have_posts() ) :
							the_post();
							$post_id = get_the_ID();
							$impact = get_field( 'impact_assessment', $post_id ) ?: 'medium';
							$parent_brief_id = get_field( 'parent_brief', $post_id );

							// Map impact to border styles and colors
							$impact_details = array(
								'low'      => array(
									'border' => 'border-l-[4px] border-l-[#27AE60]',
									'text'   => 'text-[#27AE60]',
									'badge'  => 'bg-[#27AE60]/10 text-[#27AE60]',
									'glow'   => 'hover:shadow-low-glow',
								),
								'medium'   => array(
									'border' => 'border-l-[4px] border-l-[#2980B9]',
									'text'   => 'text-[#2980B9]',
									'badge'  => 'bg-[#2980B9]/10 text-[#2980B9]',
									'glow'   => 'hover:shadow-medium-glow',
								),
								'high'     => array(
									'border' => 'border-l-[4px] border-l-[#E67E22]',
									'text'   => 'text-[#E67E22]',
									'badge'  => 'bg-[#E67E22]/10 text-[#E67E22]',
									'glow'   => 'hover:shadow-high-glow',
								),
								'critical' => array(
									'border' => 'border-l-[4px] border-l-brand-red',
									'text'   => 'text-brand-red',
									'badge'  => 'bg-brand-red/10 text-brand-red',
									'glow'   => 'hover:shadow-critical-glow',
								),
							);
							$style = isset( $impact_details[ $impact ] ) ? $impact_details[ $impact ] : $impact_details['medium'];
							?>
							
							<article id="post-<?php the_ID(); ?>" class="bg-white dark:bg-navy-mid border-y border-r border-brand-divider-light dark:border-brand-divider-dark <?php echo esc_attr( $style['border'] ); ?> p-6 rounded-sm shadow-sm hover:translate-x-1 <?php echo esc_attr( $style['glow'] ); ?> transition-all duration-300 relative flex flex-col gap-4">
								
								<!-- Telemetry Header -->
								<div class="flex justify-between items-center flex-wrap gap-2.5">
									<div class="flex items-center gap-3 text-[10px] font-mono font-bold tracking-wider text-brand-text-muted dark:text-cream/60">
										<span class="flex items-center gap-1.5">
											<i class="fa-regular fa-calendar text-brand-red text-xs"></i>
											<?php echo get_the_date( 'd.m.Y // H:i \U\T\C' ); ?>
										</span>
										<span class="text-brand-divider-light dark:text-brand-divider-dark">|</span>
										<span class="<?php echo esc_attr( $style['badge'] ); ?> px-2 py-0.5 rounded-sm text-[9px] uppercase tracking-widest font-sans font-bold">
											<?php echo esc_html( $impact ); ?>
										</span>
									</div>
									
									<span class="text-[10px] font-sans font-bold uppercase tracking-wider text-brand-text-muted dark:text-cream/50 bg-cream dark:bg-navy-deep px-2 py-0.5 rounded-sm border border-brand-divider-light dark:border-brand-divider-dark/20">
										<?php the_terms( $post_id, 'region', '', ', ', '' ); ?>
									</span>
								</div>
								
								<!-- Title -->
								<h2 class="text-base md:text-lg font-sans font-bold text-brand-text-primary dark:text-white leading-snug">
									<a href="<?php the_permalink(); ?>" class="hover:text-brand-red dark:hover:text-brand-red-light transition-colors"><?php the_title(); ?></a>
								</h2>
								
								<!-- Excerpt -->
								<div class="text-sm text-brand-text-muted dark:text-cream/70 leading-relaxed">
									<?php the_excerpt(); ?>
								</div>
								
								<!-- Footer Actions & Attachments -->
								<div class="border-t border-brand-divider-light dark:border-brand-divider-dark/10 pt-4 flex flex-col sm:flex-row justify-between sm:items-center gap-4 text-xs font-sans mt-2">
									
									<?php if ( ! empty( $parent_brief_id ) ) : ?>
										<div class="flex items-center gap-2 text-brand-text-muted dark:text-cream/60 bg-cream/40 dark:bg-navy-deep/40 px-3 py-1.5 rounded-sm border border-brand-divider-light/60 dark:border-brand-divider-dark/20 max-w-max">
											<i class="fa-solid fa-paperclip text-[10px] text-brand-red"></i>
											<span class="text-[10px] uppercase font-bold tracking-wider"><?php esc_html_e( 'Linked Brief:', 'ascendance' ); ?></span>
											<a href="<?php echo esc_url( get_permalink( $parent_brief_id ) ); ?>" class="font-bold text-brand-text-primary dark:text-cream hover:text-brand-red dark:hover:text-brand-red-light transition-colors line-clamp-1"><?php echo esc_html( get_the_title( $parent_brief_id ) ); ?></a>
										</div>
									<?php else : ?>
										<div class="hidden sm:block"></div>
									<?php endif; ?>
									
									<a href="<?php the_permalink(); ?>" class="font-bold text-brand-red hover:text-brand-red-light flex items-center gap-1.5 ml-auto sm:ml-0 whitespace-nowrap">
										<?php esc_html_e( 'Open Intel Details', 'ascendance' ); ?> 
										<i class="fa-solid fa-arrow-right text-[10px]"></i>
									</a>
								</div>
								
							</article>
							
						<?php
						endwhile;
						?>
						
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
						
						<div class="archive-empty-state text-center py-16 bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark rounded-sm shadow-sm flex flex-col items-center gap-4">
							<i class="fa-regular fa-folder-open text-4xl text-brand-red mb-2"></i>
							<h2 class="text-2xl font-sans font-bold text-brand-text-primary dark:text-white"><?php esc_html_e( 'No Updates Found', 'ascendance' ); ?></h2>
							<p class="text-sm text-brand-text-muted dark:text-cream/70 max-w-[400px] leading-relaxed mb-4">
								<?php esc_html_e( 'There are no active intelligence updates registered in this timeline.', 'ascendance' ); ?>
							</p>
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary"><?php esc_html_e( 'Return Home', 'ascendance' ); ?></a>
						</div>
						
					<?php endif; ?>
				</div>
				
			</div>
			
		</div>
	</div>

</main>

<?php
get_footer();
