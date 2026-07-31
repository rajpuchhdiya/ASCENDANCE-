<?php
/**
 * The template for displaying 404 pages (Not Found)
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="site-main bg-cream dark:bg-navy-deep min-h-[60vh] flex flex-col justify-center py-20">
	<div class="container mx-auto px-6 max-w-4xl">
		
		<div class="mb-12">
			<p class="text-sm font-mono uppercase tracking-widest text-brand-red mb-3 font-semibold">
				<?php esc_html_e( '// SYSTEM STATUS: 404', 'ascendance' ); ?>
			</p>
			<h1 class="text-4xl md:text-5xl lg:text-6xl font-sans font-bold leading-tight mb-4 text-brand-text-primary dark:text-white">
				<?php esc_html_e( 'Document Not Found', 'ascendance' ); ?>
			</h1>
			<p class="text-lg md:text-xl text-brand-text-muted dark:text-cream/80 leading-relaxed max-w-2xl">
				<?php esc_html_e( 'The intelligence brief, report, or resource you requested could not be resolved or has been archived.', 'ascendance' ); ?>
			</p>
		</div>

		<div class="bg-white dark:bg-navy-mid border-t-4 border-t-brand-red border border-brand-divider-light dark:border-brand-divider-dark p-8 md:p-12 rounded-sm shadow-sm">
			<h2 class="text-2xl font-sans font-bold text-brand-text-primary dark:text-white mb-2">
				<?php esc_html_e( 'Brief Search & Recovery', 'ascendance' ); ?>
			</h2>
			<p class="text-base text-brand-text-muted dark:text-cream/70 mb-8">
				<?php esc_html_e( 'Use the database register search below to locate alternate intelligence reports or browse our main channels.', 'ascendance' ); ?>
			</p>
			
			<form role="search" method="get" class="flex flex-col md:flex-row gap-4 mb-8 w-full max-w-2xl" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<div class="relative flex-grow">
					<div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-brand-text-muted">
						<i class="fa-solid fa-magnifying-glass"></i>
					</div>
					<input type="search" class="w-full pl-11 pr-4 py-3 bg-cream dark:bg-navy-deep border border-brand-divider-light dark:border-brand-divider-dark rounded-sm text-brand-text-primary dark:text-cream font-sans text-base outline-none focus:border-brand-red transition-colors" placeholder="<?php esc_attr_e( 'Search intelligence database...', 'ascendance' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
				</div>
				<button type="submit" class="bg-brand-red hover:bg-brand-red-light text-white font-sans font-bold text-base py-3 px-8 rounded-sm transition-colors whitespace-nowrap shadow-sm">
					<?php esc_html_e( 'SEARCH', 'ascendance' ); ?>
				</button>
			</form>

			<div class="flex flex-col sm:flex-row flex-wrap gap-4 pt-6 border-t border-brand-divider-light dark:border-brand-divider-dark">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="inline-flex justify-center items-center px-6 py-3 border border-brand-divider-light dark:border-brand-divider-dark text-brand-text-primary dark:text-cream hover:bg-cream dark:hover:bg-navy-deep text-sm font-bold font-sans uppercase tracking-wide rounded-sm transition-colors">
					<?php esc_html_e( 'Return Home', 'ascendance' ); ?>
				</a>
				<a href="<?php echo esc_url( home_url( '/briefs/' ) ); ?>" class="inline-flex justify-center items-center px-6 py-3 border border-brand-divider-light dark:border-brand-divider-dark text-brand-text-primary dark:text-cream hover:bg-cream dark:hover:bg-navy-deep text-sm font-bold font-sans uppercase tracking-wide rounded-sm transition-colors">
					<?php esc_html_e( 'Browse Briefs', 'ascendance' ); ?>
				</a>
				<?php if ( is_user_logged_in() ) : ?>
					<a href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>" class="inline-flex justify-center items-center px-6 py-3 bg-brand-red hover:bg-brand-red-light text-white text-sm font-bold font-sans uppercase tracking-wide rounded-sm transition-colors">
						<?php esc_html_e( 'Dashboard', 'ascendance' ); ?>
					</a>
				<?php else : ?>
					<a href="<?php echo esc_url( function_exists( 'pmpro_url' ) ? pmpro_url( 'levels' ) : home_url( '/membership-levels/' ) ); ?>" class="inline-flex justify-center items-center px-6 py-3 bg-brand-red hover:bg-brand-red-light text-white text-sm font-bold font-sans uppercase tracking-wide rounded-sm transition-colors">
						<?php esc_html_e( 'Subscribe', 'ascendance' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>

	</div>
</main><!-- #primary -->

<?php
get_footer();
