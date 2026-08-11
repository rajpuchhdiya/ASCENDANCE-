<?php
/**
 * The template for displaying 404 pages (Not Found)
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="site-main bg-cream dark:bg-navy-deep min-h-[80vh] flex flex-col items-center justify-center py-20 px-6">
	<div class="text-center max-w-3xl mx-auto w-full">
		<div class="mb-8">
			<p class="text-brand-red font-mono text-xs md:text-sm tracking-[0.2em] mb-4 uppercase flex items-center justify-center gap-3">
				<span class="w-12 h-[1px] bg-brand-red/50"></span>
				System Status: 404
				<span class="w-12 h-[1px] bg-brand-red/50"></span>
			</p>
			<h1 class="text-8xl md:text-[10rem] font-serif text-navy-deep dark:text-white font-bold leading-none mb-4 tracking-tighter opacity-90 drop-shadow-sm">
				404
			</h1>
			<h2 class="text-2xl md:text-4xl font-sans text-brand-text-primary dark:text-cream font-bold mb-6">
				Document Not Found
			</h2>
			<p class="text-brand-text-muted dark:text-cream/70 text-lg max-w-2xl mx-auto mb-12 leading-relaxed">
				<?php esc_html_e( 'The intelligence brief, report, or resource you requested could not be resolved or has been archived. Please verify the destination or use our database search below.', 'ascendance' ); ?>
			</p>
		</div>

		<!-- Search Form -->
		<div class="w-full max-w-xl mx-auto mb-16">
			<form role="search" method="get" class="search-form flex relative shadow-sm" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-brand-text-muted/60 dark:text-cream/40"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
				</div>
				<input type="search" class="w-full pl-14 pr-36 py-4 md:py-5 bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark rounded-sm focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red transition-all duration-300 text-brand-text-primary dark:text-white font-sans placeholder:text-brand-text-muted/60" placeholder="<?php esc_attr_e( 'Search intelligence database...', 'ascendance' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
				<button type="submit" class="absolute right-1 top-1 bottom-1 bg-brand-red hover:bg-brand-red-light text-white font-bold px-6 md:px-8 rounded-sm transition-colors duration-200 text-sm tracking-wide uppercase flex items-center gap-2">
					<span class="hidden md:inline">Search</span>
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="md:hidden"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
				</button>
			</form>
		</div>

		<!-- Quick Links -->
		<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 max-w-4xl mx-auto w-full">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="group flex flex-col items-center p-6 md:p-8 bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark rounded-sm hover:border-brand-red/50 dark:hover:border-brand-red/50 hover:shadow-md transition-all duration-300 text-center">
				<div class="w-12 h-12 flex items-center justify-center rounded-full bg-cream dark:bg-navy-deep mb-4 group-hover:bg-brand-red/10 transition-colors">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-brand-text-muted dark:text-cream/60 group-hover:text-brand-red transition-colors"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
				</div>
				<span class="font-sans font-bold text-base text-brand-text-primary dark:text-white mb-1"><?php esc_html_e( 'Return Home', 'ascendance' ); ?></span>
				<span class="text-sm text-brand-text-muted dark:text-cream/60"><?php esc_html_e( 'Back to main dashboard', 'ascendance' ); ?></span>
			</a>
			
			<a href="<?php echo esc_url( home_url( '/briefs/' ) ); ?>" class="group flex flex-col items-center p-6 md:p-8 bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark rounded-sm hover:border-brand-red/50 dark:hover:border-brand-red/50 hover:shadow-md transition-all duration-300 text-center">
				<div class="w-12 h-12 flex items-center justify-center rounded-full bg-cream dark:bg-navy-deep mb-4 group-hover:bg-brand-red/10 transition-colors">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-brand-text-muted dark:text-cream/60 group-hover:text-brand-red transition-colors"><path d="m6 14 1.45-2.9A2 2 0 0 1 9.24 10H20a2 2 0 0 1 1.94 2.5l-1.55 6a2 2 0 0 1-1.94 1.5H4a2 2 0 0 1-2-2V5c0-1.1.9-2 2-2h3.93a2 2 0 0 1 1.66.9l.82 1.2a2 2 0 0 0 1.66.9H18a2 2 0 0 1 2 2v2"/></svg>
				</div>
				<span class="font-sans font-bold text-base text-brand-text-primary dark:text-white mb-1"><?php esc_html_e( 'Browse Briefs', 'ascendance' ); ?></span>
				<span class="text-sm text-brand-text-muted dark:text-cream/60"><?php esc_html_e( 'View latest intelligence', 'ascendance' ); ?></span>
			</a>

			<?php if ( is_user_logged_in() ) : ?>
				<a href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>" class="group flex flex-col items-center p-6 md:p-8 bg-brand-red hover:bg-brand-red-light rounded-sm shadow-md hover:shadow-lg transition-all duration-300 text-center">
					<div class="w-12 h-12 flex items-center justify-center rounded-full bg-white/20 mb-4">
						<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/></svg>
					</div>
					<span class="font-sans font-bold text-base text-white mb-1"><?php esc_html_e( 'Secure Dashboard', 'ascendance' ); ?></span>
					<span class="text-sm text-white/80"><?php esc_html_e( 'Access your profile', 'ascendance' ); ?></span>
				</a>
			<?php else : ?>
				<a href="<?php echo esc_url( function_exists( 'pmpro_url' ) ? pmpro_url( 'levels' ) : home_url( '/membership-levels/' ) ); ?>" class="group flex flex-col items-center p-6 md:p-8 bg-brand-red hover:bg-brand-red-light rounded-sm shadow-md hover:shadow-lg transition-all duration-300 text-center">
					<div class="w-12 h-12 flex items-center justify-center rounded-full bg-white/20 mb-4">
						<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="m15.5 7.5 2.3 2.3a1 1 0 0 0 1.4 0l2.1-2.1a1 1 0 0 0 0-1.4L19 4"/><path d="m21 2-9.6 9.6"/><circle cx="7.5" cy="15.5" r="5.5"/></svg>
					</div>
					<span class="font-sans font-bold text-base text-white mb-1"><?php esc_html_e( 'Subscribe', 'ascendance' ); ?></span>
					<span class="text-sm text-white/80"><?php esc_html_e( 'Unlock full access', 'ascendance' ); ?></span>
				</a>
			<?php endif; ?>
		</div>
	</div>
</main><!-- #primary -->

<?php
get_footer();
