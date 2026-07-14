<?php
/**
 * Template Name: Subscriber Dashboard
 *
 * This template displays the custom branded dashboard layout for subscribers.
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="site-main py-16 md:py-24 bg-cream dark:bg-navy-deep border-b border-brand-divider-light dark:border-brand-divider-dark">
	<div class="container mx-auto px-6 md:px-8">
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<div class="entry-content">
				<?php
				// Output standard content first (if editor has any notices/greetings)
				while ( have_posts() ) :
					the_post();
					the_content();
				endwhile;

				// Output the dynamic membership dashboard
				if ( shortcode_exists( 'ascendance_member_dashboard' ) ) {
					echo do_shortcode( '[ascendance_member_dashboard]' );
				} else {
					echo '<div class="card bg-white dark:bg-navy-mid border-l-[3px] border-l-brand-red border-y border-r border-brand-divider-light dark:border-brand-divider-dark p-8 rounded-sm shadow-sm text-center">';
					echo '<h3 class="text-lg font-sans font-bold text-brand-text-primary dark:text-white mb-2">' . esc_html__( 'Core Plugin Missing', 'ascendance' ) . '</h3>';
					echo '<p class="text-sm text-brand-text-muted dark:text-cream/70">' . esc_html__( 'Please activate the Ascendance Core helper plugin to launch your dashboard portal.', 'ascendance' ) . '</p>';
					echo '</div>';
				}
				?>
			</div>
		</article>
	</div>
</main>

<?php
get_footer();
