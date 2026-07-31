<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="site-main" style="padding: 80px 0; min-height: 60vh; display: flex; flex-direction: column; justify-content: center; background-color: var(--bg-main);">
	<div class="container" style="max-width: 700px; margin: 0 auto; padding: 0 24px;">
		
		<div class="card error-404-card" style="text-align: center; padding: 48px 32px; border-left: 3px solid var(--color-red); display: flex; flex-direction: column; align-items: center; gap: 24px;">
			
			<i class="fa-solid fa-triangle-exclamation" style="font-size: 48px; color: var(--color-red); margin-bottom: 8px;"></i>
			
			<h1 style="font-family: var(--font-sans); font-weight: 700; font-size: 36px; margin-bottom: 16px; color: var(--text-primary);">
				<?php esc_html_e( 'Document Not Found', 'ascendance' ); ?>
			</h1>
			
			<p style="font-size: 16px; color: var(--text-muted); line-height: 1.6; max-width: 500px; margin: 0 auto 24px;">
				<?php esc_html_e( 'The intelligence brief, report, or resource you requested could not be resolved or has been archived from our current registry.', 'ascendance' ); ?>
			</p>
			
			<div class="search-form-404" style="width: 100%; max-width: 480px; margin: 0 auto 16px;">
				<form role="search" method="get" class="search-form" style="display: flex; align-items: stretch; gap: 8px; flex-wrap: wrap; justify-content: center;" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<input type="search" class="search-field" style="flex-grow: 1; padding: 12px 16px; border: 1px solid var(--border-color); border-radius: 2px; background: var(--input-bg); color: var(--input-text); outline: none; min-width: 200px;" placeholder="<?php esc_attr_e( 'Search intelligence database...', 'ascendance' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
					<button type="submit" class="btn btn-primary" style="padding: 12px 24px; white-space: nowrap; height: auto;">
						<?php esc_html_e( 'SEARCH', 'ascendance' ); ?>
					</button>
				</form>
			</div>

			<div style="width: 100%; height: 1px; background-color: var(--border-color); margin: 16px 0; opacity: 0.5;"></div>

			<div class="error-404-links" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 16px;">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-secondary">
					<?php esc_html_e( 'Return Home', 'ascendance' ); ?>
				</a>
				
				<a href="<?php echo esc_url( home_url( '/briefs/' ) ); ?>" class="btn btn-secondary">
					<?php esc_html_e( 'Browse Briefs', 'ascendance' ); ?>
				</a>

				<?php if ( is_user_logged_in() ) : ?>
					<a href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>" class="btn btn-primary">
						<?php esc_html_e( 'Dashboard', 'ascendance' ); ?>
					</a>
				<?php else : ?>
					<a href="<?php echo esc_url( function_exists( 'pmpro_url' ) ? pmpro_url( 'levels' ) : home_url( '/membership-levels/' ) ); ?>" class="btn btn-primary">
						<?php esc_html_e( 'Subscribe', 'ascendance' ); ?>
					</a>
				<?php endif; ?>
			</div>
			
		</div>
		
	</div>
</main>

<?php
get_footer();
