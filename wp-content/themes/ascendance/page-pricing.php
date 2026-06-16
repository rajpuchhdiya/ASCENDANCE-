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

	<header class="page-header" style="background-color: var(--color-deep-navy); border-bottom: 1px solid var(--border-color); padding: var(--space-60) 0; text-align: center;">
		<div class="container">
			<span style="color: var(--color-red); font-family: var(--font-heading); text-transform: uppercase; font-weight: bold; font-size: var(--font-size-xs); letter-spacing: 1px;"><?php esc_html_e( 'Acquire License', 'ascendance' ); ?></span>
			<h1 class="page-title" style="margin-top: 10px; margin-bottom: 15px; color: var(--color-white);"><?php esc_html_e( 'Membership & Access Plans', 'ascendance' ); ?></h1>
			<p style="color: var(--text-secondary); max-width: 600px; margin: 0 auto; font-size: var(--font-size-sm); line-height: 1.6;">
				<?php esc_html_e( 'Choose a professional intelligence tier. Unlock forward-looking briefs, critical timelines, and high-density dossiers mapped by industry experts.', 'ascendance' ); ?>
			</p>
		</div>
	</header>

	<div class="content-wrapper" style="padding: var(--space-50) 0;">
		<div class="container">
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
						echo '<p style="text-align:center; color:var(--text-muted);">' . esc_html__( 'Pricing table shortcode is currently loading.', 'ascendance' ) . '</p>';
					}
					?>
				</div>
			</article>
		</div>
	</div>

</main>

<?php
get_footer();
