<?php
/**
 * Footer for the Ascendance Intelligence Platform
 *
 * @package Ascendance
 */

// Dynamic Polylang translation switcher is integrated below.
?>
	</div><!-- #content -->

	<footer id="colophon" class="site-footer bg-navy-deep text-cream border-t border-brand-divider-dark py-16 mt-auto">
		<div class="container mx-auto px-6 md:px-8">
			<div class="footer-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 border-b border-brand-divider-dark pb-10">

				<!-- Brand column -->
				<div class="footer-brand flex flex-col gap-4">
					<?php
					$custom_logo_id = get_theme_mod( 'custom_logo' );
					$dark_logo_id   = get_theme_mod( 'dark_mode_logo' );
					$logo_id        = $dark_logo_id ? $dark_logo_id : $custom_logo_id;

					if ( $logo_id ) :
						$logo_url = wp_get_attachment_image_url( $logo_id, 'full' );
						?>
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="footer-custom-logo-link block">
							<img src="<?php echo esc_url( $logo_url ); ?>" class="custom-logo footer-logo" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
						</a>
					<?php else : ?>
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="footer-logo-mark flex items-center gap-1.5 no-underline hover:no-underline transition-colors duration-150">
							<span class="flex items-center select-none">
								<svg class="w-[19px] h-[22px]" viewBox="0 0 24 28" fill="none" xmlns="http://www.w3.org/2000/svg">
									<!-- Red arrow left leg -->
									<path d="M5 23 L14 6.5 L11.5 7.8 L18.5 2.5 L19.5 9.5 L17 8.2 L7.5 23 Z" fill="#BC1B1D" />
									<!-- White right leg -->
									<path d="M13.8 8.8 L19 23 h-3 L11.6 13 Z" fill="#F7F4EF" />
									<!-- White crossbar -->
									<path d="M9.5 15.5 h5.5 v2.2 h-5.5 Z" fill="#F7F4EF" />
								</svg>
							</span>
							<span class="text-white ml-0.5 leading-none font-serif text-[22px] font-medium tracking-normal">scendance</span>
						</a>
					<?php endif; ?>
					<p class="footer-brand-tagline text-cream/60 text-sm leading-relaxed max-w-[280px]">
						<?php esc_html_e( 'Premium geopolitical and strategic intelligence for decision-makers navigating a complex world.', 'ascendance' ); ?>
					</p>
					<div class="footer-socials flex gap-4">
						<a href="#" class="social-link w-8 h-8 flex items-center justify-center bg-navy border border-brand-divider-dark hover:border-brand-red hover:text-white transition-all duration-150 rounded-sm text-cream/60" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
						<a href="#" class="social-link w-8 h-8 flex items-center justify-center bg-navy border border-brand-divider-dark hover:border-brand-red hover:text-white transition-all duration-150 rounded-sm text-cream/60" aria-label="X / Twitter"><i class="fa-brands fa-x-twitter"></i></a>
						<a href="#" class="social-link w-8 h-8 flex items-center justify-center bg-navy border border-brand-divider-dark hover:border-brand-red hover:text-white transition-all duration-150 rounded-sm text-cream/60" aria-label="Substack"><i class="fa-solid fa-rss"></i></a>
					</div>
					<div class="footer-lang-switch mt-4 flex gap-4 text-xs font-bold font-sans">
						<?php
						if ( function_exists( 'pll_the_languages' ) ) {
							$languages = pll_the_languages( array( 'raw' => 1 ) );
							if ( ! empty( $languages ) ) {
								$i = 0;
								foreach ( $languages as $lang ) {
									if ( $i > 0 ) {
										echo '<span class="lang-separator text-brand-divider-dark">|</span>';
									}
									$active_class = $lang['current_lang'] ? 'active text-brand-red' : 'text-cream/60 hover:text-white transition-colors duration-150';
									echo '<a href="' . esc_url( $lang['url'] ) . '" class="lang-link ' . esc_attr( $active_class ) . '">' . esc_html( strtoupper( $lang['slug'] ) ) . '</a>';
									$i++;
								}
							}
						} else {
							// Fallback when Polylang is not active
							global $wp;
							$request_path = isset( $wp->request ) ? $wp->request : '';
							$current_url = home_url( add_query_arg( array(), $request_path ) );
							$is_french = ( strpos( $request_path, 'fr/' ) === 0 || $request_path === 'fr' );
							$en_url = $is_french ? str_replace( '/fr/', '/', home_url( $request_path ) ) : $current_url;
							if ( $request_path === 'fr' ) {
								$en_url = home_url( '/' );
							}
							$fr_url = $is_french ? $current_url : home_url( '/fr/' . $request_path );
							?>
							<a href="<?php echo esc_url( $en_url ); ?>" class="lang-link <?php echo !$is_french ? 'active text-brand-red' : 'text-cream/60 hover:text-white transition-colors duration-150'; ?>">EN</a>
							<span class="lang-separator text-brand-divider-dark">|</span>
							<a href="<?php echo esc_url( $fr_url ); ?>" class="lang-link <?php echo $is_french ? 'active text-brand-red' : 'text-cream/60 hover:text-white transition-colors duration-150'; ?>">FR</a>
							<?php
						}
						?>
					</div>
				</div>

				<!-- Platform links -->
				<div class="footer-col">
					<h4 class="text-white font-sans text-sm font-bold uppercase tracking-wider mb-5"><?php esc_html_e( 'Platform', 'ascendance' ); ?></h4>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'menu-footer',
							'menu_class'     => 'footer-links flex flex-col gap-3 list-none p-0 m-0 text-sm [&_a]:text-cream/60 hover:[&_a]:text-white [&_a]:transition-colors [&_a]:duration-150',
							'container'      => false,
							'fallback_cb'    => 'ascendance_default_footer_menu',
						)
					);
					?>
				</div>

				<!-- Intelligence links -->
				<div class="footer-col">
					<h4 class="text-white font-sans text-sm font-bold uppercase tracking-wider mb-5"><?php esc_html_e( 'Intelligence', 'ascendance' ); ?></h4>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'menu-footer-intel',
							'menu_class'     => 'footer-links flex flex-col gap-3 list-none p-0 m-0 text-sm [&_a]:text-cream/60 hover:[&_a]:text-white [&_a]:transition-colors [&_a]:duration-150',
							'container'      => false,
							'fallback_cb'    => 'ascendance_default_intel_footer_menu',
						)
					);
					?>
				</div>

				<!-- Contact column -->
				<div class="footer-col">
					<h4 class="text-white font-sans text-sm font-bold uppercase tracking-wider mb-5"><?php esc_html_e( 'Contact', 'ascendance' ); ?></h4>
					<ul class="footer-links flex flex-col gap-3 list-none p-0 m-0 text-sm [&_a]:text-cream/60 hover:[&_a]:text-white [&_a]:transition-colors [&_a]:duration-150">
						<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="flex items-center gap-2"><i class="fa-regular fa-envelope text-brand-red"></i><?php esc_html_e( 'Send a Message', 'ascendance' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/newsletter/' ) ); ?>" class="footer-subscribe-cta flex items-center gap-2"><i class="fa-regular fa-newspaper text-brand-red"></i><?php esc_html_e( 'Subscribe', 'ascendance' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>#media" class="flex items-center gap-2"><i class="fa-solid fa-id-badge text-brand-red"></i><?php esc_html_e( 'Media Kit', 'ascendance' ); ?></a></li>
					</ul>
				</div>

			</div><!-- .footer-grid -->

			<div class="footer-bottom flex flex-col md:flex-row justify-between items-center gap-4 pt-8 text-xs text-cream/60 font-sans">
				<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <strong><?php bloginfo( 'name' ); ?></strong>. <?php esc_html_e( 'All rights reserved.', 'ascendance' ); ?></p>
				<ul class="footer-bottom-links flex gap-6 list-none p-0 m-0 [&_a]:text-cream/60 hover:[&_a]:text-white [&_a]:transition-colors [&_a]:duration-150">
					<li><a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'ascendance' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>"><?php esc_html_e( 'Terms of Service', 'ascendance' ); ?></a></li>
				</ul>
			</div>

		</div><!-- .container -->
	</footer>

</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
