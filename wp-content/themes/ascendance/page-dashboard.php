<?php
/**
 * Template Name: Subscriber Dashboard
 *
 * This template displays the custom member portal dashboard layout.
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="site-main" style="padding: var(--space-50) 0;">
	<div class="container">
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
					echo '<div class="card" style="text-align:center; padding: 40px;">';
					echo '<h3>' . esc_html__( 'Core Plugin Missing', 'ascendance' ) . '</h3>';
					echo '<p>' . esc_html__( 'Please activate the Ascendance Core helper plugin to launch your dashboard portal.', 'ascendance' ) . '</p>';
					echo '</div>';
				}
				?>
			</div>
		</article>
	</div>
</main>

<?php
get_footer();
