<?php
/**
 * Template Name: Membership Pricing
 *
 * This template displays the public membership levels and registration pricing table.
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="site-main">

	<header class="page-header-premium bg-navy-deep text-white py-16 md:py-24 border-b border-brand-divider-dark">
		<div class="container mx-auto px-6 md:px-8">
			<span class="page-header-premium-eyebrow text-xs font-mono uppercase tracking-widest text-brand-red mb-4 block"><?php esc_html_e( 'Acquire License', 'ascendance' ); ?></span>
			<h1 class="page-header-premium-title text-3xl md:text-5xl font-sans font-bold leading-tight mb-6"><?php esc_html_e( 'Membership & Access Plans', 'ascendance' ); ?></h1>
			<p class="page-header-premium-desc text-base md:text-lg text-cream/80 max-w-[720px] leading-relaxed">
				<?php esc_html_e( 'Choose a professional intelligence tier. Unlock forward-looking briefs, critical timelines, and high-density dossiers mapped by industry experts.', 'ascendance' ); ?>
			</p>
		</div>
	</header>

	<div class="archive-layout-wrapper py-16 md:py-24 bg-cream dark:bg-navy-deep border-b border-brand-divider-light dark:border-brand-divider-dark">
		<div class="container mx-auto px-6 md:px-8">
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<div class="entry-content">
					<?php
					while ( have_posts() ) :
						the_post();
						the_content();
					endwhile;

					// Output pricing grid
					if ( shortcode_exists( 'ascendance_pricing_table' ) ) {
						echo do_shortcode( '[ascendance_pricing_table]' );
					} else {
						echo '<p class="text-center text-brand-text-muted dark:text-cream/50 font-sans text-sm">' . esc_html__( 'Pricing table shortcode is currently loading.', 'ascendance' ) . '</p>';
					}
					?>
				</div>
			</article>
		</div>
	</div>

</main>

<?php
get_footer();
