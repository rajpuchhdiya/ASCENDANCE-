<?php
/**
 * The template for displaying 404 pages (Not Found)
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="site-main relative overflow-hidden bg-cream dark:bg-navy-deep min-h-[80vh] flex items-center justify-center">

	<!-- Decorative Elements -->
	<div class="absolute inset-0 pointer-events-none overflow-hidden z-0 flex items-center justify-center">
		<!-- Large glowing 404 -->
		<div class="text-[25vw] font-bold font-sans text-brand-red/5 dark:text-brand-red/5 leading-none select-none" aria-hidden="true">404</div>
		
		<!-- Glassmorphism Orbs -->
		<div class="absolute top-1/4 left-1/4 w-64 h-64 bg-brand-red/10 rounded-full blur-3xl mix-blend-multiply dark:mix-blend-lighten animate-pulse"></div>
		<div class="absolute bottom-1/4 right-1/4 w-80 h-80 bg-navy-light/10 dark:bg-brand-red/5 rounded-full blur-3xl mix-blend-multiply dark:mix-blend-lighten animate-pulse" style="animation-delay: 2s;"></div>
	</div>

	<div class="container mx-auto px-6 md:px-8 relative z-10 py-16 md:py-24">
		<div class="max-w-3xl mx-auto">
			
			<div class="text-center mb-12">
				<p class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-brand-red/10 text-brand-red text-xs font-mono uppercase tracking-widest mb-6">
					<span class="w-2 h-2 rounded-full bg-brand-red animate-pulse"></span>
					<?php esc_html_e( 'System Status: 404', 'ascendance' ); ?>
				</p>
				
				<h1 class="text-4xl md:text-6xl lg:text-7xl font-sans font-extrabold leading-tight mb-6 text-brand-text-primary dark:text-white tracking-tight">
					<?php esc_html_e( 'Document Not Found', 'ascendance' ); ?>
				</h1>
				
				<p class="text-lg md:text-xl text-brand-text-muted dark:text-cream/80 max-w-2xl mx-auto leading-relaxed">
					<?php esc_html_e( 'The intelligence brief, report, or resource you requested could not be resolved or has been archived from our current registry.', 'ascendance' ); ?>
				</p>
			</div>

			<div class="bg-white/80 dark:bg-navy-mid/80 backdrop-blur-lg border border-brand-divider-light dark:border-brand-divider-dark p-8 md:p-10 rounded-2xl shadow-xl">
				
				<div class="flex items-center gap-4 mb-6 pb-6 border-b border-brand-divider-light dark:border-brand-divider-dark">
					<div class="w-12 h-12 rounded-full bg-brand-red/10 flex items-center justify-center text-brand-red text-xl flex-shrink-0">
						<i class="fa-solid fa-satellite-dish"></i>
					</div>
					<div>
						<h2 class="text-xl font-sans font-bold text-brand-text-primary dark:text-white mb-1"><?php esc_html_e( 'Brief Search & Recovery', 'ascendance' ); ?></h2>
						<p class="text-sm text-brand-text-muted dark:text-cream/70">
							<?php esc_html_e( 'Query the database register to locate alternate intelligence reports.', 'ascendance' ); ?>
						</p>
					</div>
				</div>

				<div class="mb-8">
					<form role="search" method="get" class="group relative flex items-center w-full" action="<?php echo esc_url( home_url( '/' ) ); ?>">
						<div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-brand-text-muted dark:text-cream/50 group-focus-within:text-brand-red transition-colors">
							<i class="fa-solid fa-magnifying-glass"></i>
						</div>
						<input type="search" class="w-full pl-12 pr-32 py-4 bg-cream dark:bg-navy-deep border-2 border-transparent focus:border-brand-red/30 rounded-xl text-brand-text-primary dark:text-cream font-serif text-base outline-none transition-all duration-300 shadow-inner" placeholder="<?php esc_attr_e( 'Enter search term...', 'ascendance' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
						<div class="absolute right-2 top-1/2 -translate-y-1/2">
							<button type="submit" class="bg-brand-red hover:bg-brand-red-light text-white font-sans font-bold text-sm py-2 px-6 rounded-lg transition-all duration-300 shadow-md hover:shadow-lg hover:-translate-y-0.5">
								<?php esc_html_e( 'Search', 'ascendance' ); ?>
							</button>
						</div>
					</form>
				</div>

				<div class="flex flex-wrap items-center justify-center gap-4">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="group flex items-center gap-2 py-3 px-6 rounded-lg bg-cream dark:bg-navy-deep text-brand-text-primary dark:text-cream hover:text-brand-red dark:hover:text-brand-red font-sans font-bold text-sm transition-all duration-300 border border-transparent hover:border-brand-red/20">
						<i class="fa-solid fa-home text-brand-text-muted group-hover:text-brand-red transition-colors"></i>
						<?php esc_html_e( 'Return Home', 'ascendance' ); ?>
					</a>
					
					<a href="<?php echo esc_url( home_url( '/briefs/' ) ); ?>" class="group flex items-center gap-2 py-3 px-6 rounded-lg bg-cream dark:bg-navy-deep text-brand-text-primary dark:text-cream hover:text-brand-red dark:hover:text-brand-red font-sans font-bold text-sm transition-all duration-300 border border-transparent hover:border-brand-red/20">
						<i class="fa-solid fa-folder-open text-brand-text-muted group-hover:text-brand-red transition-colors"></i>
						<?php esc_html_e( 'Browse Briefs', 'ascendance' ); ?>
					</a>

					<?php if ( is_user_logged_in() ) : ?>
						<a href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>" class="group flex items-center gap-2 py-3 px-6 rounded-lg bg-cream dark:bg-navy-deep text-brand-text-primary dark:text-cream hover:text-brand-red dark:hover:text-brand-red font-sans font-bold text-sm transition-all duration-300 border border-transparent hover:border-brand-red/20">
							<i class="fa-solid fa-chart-line text-brand-text-muted group-hover:text-brand-red transition-colors"></i>
							<?php esc_html_e( 'Dashboard', 'ascendance' ); ?>
						</a>
					<?php else : ?>
						<a href="<?php echo esc_url( function_exists( 'pmpro_url' ) ? pmpro_url( 'levels' ) : home_url( '/membership-levels/' ) ); ?>" class="group flex items-center gap-2 py-3 px-6 rounded-lg bg-brand-red text-white hover:bg-brand-red-light font-sans font-bold text-sm transition-all duration-300 shadow-md hover:shadow-lg border border-transparent">
							<i class="fa-solid fa-user-plus"></i>
							<?php esc_html_e( 'Subscribe', 'ascendance' ); ?>
						</a>
					<?php endif; ?>
				</div>
				
			</div>
			
		</div>
	</div>

</main><!-- #primary -->

<?php
get_footer();

