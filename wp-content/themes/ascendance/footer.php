<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Ascendance
 */

?>
	</div><!-- #content -->

	<footer id="colophon" class="site-footer">
		<div class="container">
			<div class="footer-grid">
				<div class="footer-brand">
					<h3><?php bloginfo( 'name' ); ?></h3>
					<p><?php esc_html_e( 'Ascend to the next tier of digital experiences with clean aesthetics, interactive designs, and performance-first development.', 'ascendance' ); ?></p>
					<div class="footer-socials">
						<a href="#" class="social-link" aria-label="Twitter"><i class="fa-brands fa-twitter"></i></a>
						<a href="#" class="social-link" aria-label="GitHub"><i class="fa-brands fa-github"></i></a>
						<a href="#" class="social-link" aria-label="LinkedIn"><i class="fa-brands fa-linkedin"></i></a>
						<a href="#" class="social-link" aria-label="Dribbble"><i class="fa-brands fa-dribbble"></i></a>
					</div>
				</div>

				<div class="footer-col">
					<h4><?php esc_html_e( 'Quick Links', 'ascendance' ); ?></h4>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'menu-footer',
							'menu_class'     => 'footer-links',
							'container'      => false,
							'fallback_cb'    => 'ascendance_default_footer_menu',
						)
					);
					?>
				</div>

				<div class="footer-col">
					<h4><?php esc_html_e( 'Contact', 'ascendance' ); ?></h4>
					<ul class="footer-links">
						<li><a href="mailto:raj@example.com"><i class="fa-regular fa-envelope" style="margin-right: 8px; color: var(--accent-purple);"></i> raj@example.com</a></li>
						<li><span style="color: var(--text-secondary); font-size: 0.95rem;"><i class="fa-solid fa-location-dot" style="margin-right: 8px; color: var(--accent-purple);"></i> Mumbai, India</span></li>
						<li><span style="color: var(--text-secondary); font-size: 0.95rem;"><i class="fa-solid fa-code" style="margin-right: 8px; color: var(--accent-purple);"></i> Author: Raj</span></li>
					</ul>
				</div>
			</div>

			<div class="footer-bottom">
				<p>&copy; <?php echo date( 'Y' ); ?> <strong><?php bloginfo( 'name' ); ?></strong>. All rights reserved. Created with passion by <strong>Raj</strong>.</p>
				<ul class="footer-bottom-links">
					<li><a href="#"><?php esc_html_e( 'Privacy Policy', 'ascendance' ); ?></a></li>
					<li><a href="#"><?php esc_html_e( 'Terms of Service', 'ascendance' ); ?></a></li>
				</ul>
			</div>
		</div>
	</footer>
</div><!-- #page -->

<?php
if ( ! function_exists( 'ascendance_default_footer_menu' ) ) {
	function ascendance_default_footer_menu() {
		echo '<ul class="footer-links">';
		echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'ascendance' ) . '</a></li>';
		echo '<li><a href="#features">' . esc_html__( 'Features', 'ascendance' ) . '</a></li>';
		echo '<li><a href="#about">' . esc_html__( 'About', 'ascendance' ) . '</a></li>';
		echo '<li><a href="#blog">' . esc_html__( 'Blog', 'ascendance' ) . '</a></li>';
		echo '</ul>';
	}
}
?>

<?php wp_footer(); ?>

</body>
</html>
