<?php
/**
 * The template for displaying 404 pages (Not Found)
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="site-main">

	<section class="page-hero bg-navy-deep text-white py-16 md:py-24 border-b border-brand-divider-dark">
		<div class="container mx-auto px-6 md:px-8">
			<div class="page-hero-inner">
				<p class="page-hero-eyebrow text-xs font-mono uppercase tracking-widest text-brand-red mb-4 block"><?php esc_html_e( '// SYSTEM STATUS: 404', 'ascendance' ); ?></p>
				<h1 class="page-hero-title text-3xl md:text-5xl font-sans font-bold leading-tight mb-6"><?php esc_html_e( 'Document Not Found', 'ascendance' ); ?></h1>
				<p class="page-hero-desc text-base md:text-lg text-cream/80 max-w-[720px] leading-relaxed">
					<?php esc_html_e( 'The intelligence brief, report, or resource you requested could not be resolved or has been archived.', 'ascendance' ); ?>
				</p>
			</div>
		</div>
	</section>

	<div class="content-wrapper py-16 md:py-24 bg-cream dark:bg-navy-deep border-b border-brand-divider-light dark:border-brand-divider-dark">
		<div class="container mx-auto max-w-[700px] px-6">
			<div class="card error-404-card bg-white dark:bg-navy-mid border-l-[3px] border-l-brand-red border-y border-r border-brand-divider-light dark:border-brand-divider-dark p-8 md:p-12 rounded-sm shadow-md text-center flex flex-col items-center gap-6">
				<i class="fa-solid fa-triangle-exclamation text-5xl text-brand-red mb-2"></i>
				<h2 class="text-xl font-sans font-bold text-brand-text-primary dark:text-white"><?php esc_html_e( 'Brief Search & Recovery', 'ascendance' ); ?></h2>
				<p class="text-sm text-brand-text-muted dark:text-cream/70 leading-relaxed max-w-[500px]">
					<?php esc_html_e( 'Use the database register search below to locate alternate intelligence reports or browse our main channels.', 'ascendance' ); ?>
				</p>
				
				<div class="search-form-404 w-full max-w-[480px]">
					<form role="search" method="get" class="search-form flex items-center gap-0 border border-brand-divider-light dark:border-brand-divider-dark rounded-sm overflow-hidden bg-white dark:bg-navy-deep" action="<?php echo esc_url( home_url( '/' ) ); ?>">
						<input type="search" class="search-field flex-grow px-4 py-3 bg-transparent text-brand-text-primary dark:text-cream font-serif text-sm outline-none border-none" placeholder="<?php esc_attr_e( 'Search intelligence database...', 'ascendance' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
						<button type="submit" class="btn btn-primary bg-brand-red hover:bg-brand-red-light text-white border-none py-3 px-6 cursor-pointer transition-colors duration-150 flex items-center justify-center"><i class="fa-solid fa-magnifying-glass"></i></button>
					</form>
				</div>

				<div class="error-404-links flex flex-wrap justify-center gap-4 mt-4">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-secondary border border-brand-divider-light dark:border-brand-divider-dark text-brand-text-primary dark:text-cream hover:bg-brand-divider-light dark:hover:bg-brand-divider-dark py-2.5 px-5 text-sm font-bold font-sans rounded-sm transition-colors"><?php esc_html_e( 'Return Home', 'ascendance' ); ?></a>
					<a href="<?php echo esc_url( home_url( '/briefs/' ) ); ?>" class="btn btn-secondary border border-brand-divider-light dark:border-brand-divider-dark text-brand-text-primary dark:text-cream hover:bg-brand-divider-light dark:hover:bg-brand-divider-dark py-2.5 px-5 text-sm font-bold font-sans rounded-sm transition-colors"><?php esc_html_e( 'Browse Briefs', 'ascendance' ); ?></a>
					<?php if ( is_user_logged_in() ) : ?>
						<a href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>" class="btn btn-primary bg-brand-red hover:bg-brand-red-light text-white border-none py-2.5 px-5 text-sm font-bold font-sans rounded-sm transition-colors"><?php esc_html_e( 'Dashboard', 'ascendance' ); ?></a>
					<?php else : ?>
						<a href="<?php echo esc_url( function_exists( 'pmpro_url' ) ? pmpro_url( 'levels' ) : home_url( '/membership-levels/' ) ); ?>" class="btn btn-primary bg-brand-red hover:bg-brand-red-light text-white border-none py-2.5 px-5 text-sm font-bold font-sans rounded-sm transition-colors"><?php esc_html_e( 'Subscribe', 'ascendance' ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

</main><!-- #primary -->

<?php
get_footer();
