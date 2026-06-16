<?php
/**
 * Footer for the Ascendance Intelligence Platform
 *
 * @package Ascendance
 */
?>
	</div><!-- #content -->

	<footer id="colophon" class="site-footer">
		<div class="container">
			<div class="footer-grid">

				<!-- Brand column -->
				<div class="footer-brand">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="footer-logo-mark">
						<?php bloginfo( 'name' ); ?>
					</a>
					<p class="footer-brand-tagline">
						<?php esc_html_e( 'Premium geopolitical and strategic intelligence for decision-makers navigating a complex world.', 'ascendance' ); ?>
					</p>
					<div class="footer-socials">
						<a href="#" class="social-link" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
						<a href="#" class="social-link" aria-label="X / Twitter"><i class="fa-brands fa-x-twitter"></i></a>
						<a href="#" class="social-link" aria-label="Substack"><i class="fa-solid fa-rss"></i></a>
					</div>
				</div>

				<!-- Platform links -->
				<div class="footer-col">
					<h4><?php esc_html_e( 'Platform', 'ascendance' ); ?></h4>
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

				<!-- Intelligence links -->
				<div class="footer-col">
					<h4><?php esc_html_e( 'Intelligence', 'ascendance' ); ?></h4>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'menu-footer-intel',
							'menu_class'     => 'footer-links',
							'container'      => false,
							'fallback_cb'    => 'ascendance_default_intel_footer_menu',
						)
					);
					?>
				</div>

				<!-- Contact column -->
				<div class="footer-col">
					<h4><?php esc_html_e( 'Contact', 'ascendance' ); ?></h4>
					<ul class="footer-links">
						<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><i class="fa-regular fa-envelope" style="margin-right:6px;color:var(--color-red);"></i><?php esc_html_e( 'Send a Message', 'ascendance' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/newsletter/' ) ); ?>"><i class="fa-regular fa-newspaper" style="margin-right:6px;color:var(--color-red);"></i><?php esc_html_e( 'Subscribe', 'ascendance' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>#media"><i class="fa-solid fa-id-badge" style="margin-right:6px;color:var(--color-red);"></i><?php esc_html_e( 'Media Kit', 'ascendance' ); ?></a></li>
					</ul>
				</div>

			</div><!-- .footer-grid -->

			<div class="footer-bottom">
				<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <strong><?php bloginfo( 'name' ); ?></strong>. <?php esc_html_e( 'All rights reserved.', 'ascendance' ); ?></p>
				<ul class="footer-bottom-links">
					<li><a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'ascendance' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>"><?php esc_html_e( 'Terms of Service', 'ascendance' ); ?></a></li>
				</ul>
			</div>

		</div><!-- .container -->
	</footer>

</div><!-- #page -->

<?php
if ( ! function_exists( 'ascendance_default_footer_menu' ) ) {
	function ascendance_default_footer_menu() {
		$links = array(
			home_url( '/' )              => 'Home',
			home_url( '/about/' )        => 'About',
			home_url( '/services/' )     => 'Services',
			home_url( '/industries/' )   => 'Industries',
			home_url( '/faq/' )          => 'FAQ',
		);
		echo '<ul class="footer-links">';
		foreach ( $links as $url => $label ) {
			echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
		}
		echo '</ul>';
	}
}

if ( ! function_exists( 'ascendance_default_intel_footer_menu' ) ) {
	function ascendance_default_intel_footer_menu() {
		$links = array(
			home_url( '/intelligence/' ) => 'Intelligence Hub',
			home_url( '/briefs/' )       => 'Intelligence Briefs',
			home_url( '/updates/' )      => 'Dynamic Updates',
			home_url( '/dossiers/' )     => 'Dossiers',
			home_url( '/newsletter/' )   => 'Newsletter',
		);
		echo '<ul class="footer-links">';
		foreach ( $links as $url => $label ) {
			echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
		}
		echo '</ul>';
	}
}
?>

<?php wp_footer(); ?>
</body>
</html>
