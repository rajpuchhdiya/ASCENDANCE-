<?php
/**
 * The header for the Ascendance Intelligence Platform
 *
 * @package Ascendance
 */

$is_logged_in = is_user_logged_in();

// Helper active class function (defines classes for navigation links)
if ( ! function_exists( 'ascendance_is_menu_active' ) ) {
	function ascendance_is_menu_active( $menu_item ) {
		switch ( $menu_item ) {
			case 'dashboard':
				return is_page( 'dashboard' );
			case 'intelligence':
				return is_page( 'intelligence' ) || 
				       is_post_type_archive( array( 'brief', 'update', 'dossier' ) ) || 
				       is_singular( array( 'brief', 'update', 'dossier' ) ) || 
				       is_tax( array( 'topic', 'region', 'intelligence_tag' ) );
			case 'services':
				return is_page( 'services' );
			case 'industries':
				return is_page( 'industries' );
			case 'about':
				return is_page( 'about' );
			case 'faq':
				return is_page( 'faq' );
			default:
				return false;
		}
	}
}

if ( ! function_exists( 'ascendance_menu_class' ) ) {
	function ascendance_menu_class( $menu_item ) {
		return ascendance_is_menu_active( $menu_item ) ? 'menu-item current-menu-item' : 'menu-item';
	}
}

// Dynamic Polylang translation switcher is integrated below.
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<script>
		(function() {
			let savedTheme = null;
			try {
				savedTheme = localStorage.getItem('theme');
			} catch (e) {
				// Local storage might be blocked in some browser settings
			}
			
			if (savedTheme === 'dark') {
				document.documentElement.setAttribute('data-theme', 'dark');
				document.documentElement.classList.add('dark-theme', 'dark');
			} else if (savedTheme === 'light') {
				document.documentElement.setAttribute('data-theme', 'light');
				document.documentElement.classList.remove('dark-theme', 'dark');
			} else {
				if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
					document.documentElement.setAttribute('data-theme', 'dark');
					document.documentElement.classList.add('dark-theme', 'dark');
				} else {
					document.documentElement.setAttribute('data-theme', 'light');
					document.documentElement.classList.remove('dark-theme', 'dark');
				}
			}
		})();
	</script>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site min-h-screen flex flex-col">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'ascendance' ); ?></a>

	<header id="masthead" class="site-header w-full h-20 flex items-center sticky top-0 z-[100] transition-all duration-200 <?php echo $is_logged_in ? 'logged-in' : 'anonymous'; ?>">
		<div class="container mx-auto px-6 md:px-8">
			<div class="header-inner flex items-center justify-between gap-8 w-full">

				<!-- Left Side: Logo (+ Tagline lockup for anonymous) -->
				<div class="header-left flex items-center">
					<div class="site-logo">
						<?php
						$custom_logo_id = get_theme_mod( 'custom_logo' );
						$dark_logo_id   = get_theme_mod( 'dark_mode_logo' );

						if ( $custom_logo_id || $dark_logo_id ) :
							$light_logo_url = $custom_logo_id ? wp_get_attachment_image_url( $custom_logo_id, 'full' ) : '';
							$dark_logo_url  = $dark_logo_id ? wp_get_attachment_image_url( $dark_logo_id, 'full' ) : '';

							if ( ! $light_logo_url && $dark_logo_url ) { $light_logo_url = $dark_logo_url; }
							if ( ! $dark_logo_url && $light_logo_url ) { $dark_logo_url = $light_logo_url; }
							?>
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="custom-logo-link block">
								<img src="<?php echo esc_url( $light_logo_url ); ?>" class="custom-logo custom-logo-light" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
								<img src="<?php echo esc_url( $dark_logo_url ); ?>" class="custom-logo custom-logo-dark" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
							</a>
						<?php else : ?>
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="header-logo-mark flex flex-col items-start gap-0.5 no-underline hover:no-underline transition-colors duration-150">
								<div class="flex items-center gap-1 text-brand-text-primary dark:text-cream">
									<span class="flex items-center select-none">
										<svg class="w-[19px] h-[22px]" viewBox="0 0 24 28" fill="none" xmlns="http://www.w3.org/2000/svg">
											<!-- Red arrow left leg -->
											<path d="M5 23 L14 6.5 L11.5 7.8 L18.5 2.5 L19.5 9.5 L17 8.2 L7.5 23 Z" fill="#BC1B1D" />
											<!-- White right leg -->
											<path d="M13.8 8.8 L19 23 h-3 L11.6 13 Z" fill="currentColor" />
											<!-- White crossbar -->
											<path d="M9.5 15.5 h5.5 v2.2 h-5.5 Z" fill="currentColor" />
										</svg>
									</span>
									<span class="text-brand-text-primary dark:text-cream ml-0.5 leading-none font-serif text-[22px] font-medium tracking-normal">scendance</span>
								</div>
								<div class="logo-tagline text-[7px] font-sans tracking-[0.16em] text-brand-text-muted dark:text-cream/50 leading-none">
									<span class="uppercase">Ascendance</span> <span class="text-brand-red font-bold font-mono mx-0.5">|</span> Geopolitical Intelligence
								</div>
							</a>
						<?php endif; ?>
					</div>
				</div>

				<!-- Primary navigation (Center) -->
				<?php
				// Resolve taxonomy terms for menu mapping
				$geopolitics_term = get_term_by( 'slug', 'geopolitics', 'topic' );
				$geopolitics_url = $geopolitics_term && ! is_wp_error( $geopolitics_term ) ? get_term_link( $geopolitics_term ) : home_url( '/intelligence/' );
				if ( is_wp_error( $geopolitics_url ) ) { $geopolitics_url = home_url( '/intelligence/' ); }

				$economics_term = get_term_by( 'slug', 'economics', 'topic' );
				$economics_url = $economics_term && ! is_wp_error( $economics_term ) ? get_term_link( $economics_term ) : home_url( '/intelligence/' );
				if ( is_wp_error( $economics_url ) ) { $economics_url = home_url( '/intelligence/' ); }

				$technology_term = get_term_by( 'slug', 'technology', 'topic' );
				$technology_url = $technology_term && ! is_wp_error( $technology_term ) ? get_term_link( $technology_term ) : home_url( '/intelligence/' );
				if ( is_wp_error( $technology_url ) ) { $technology_url = home_url( '/intelligence/' ); }

				$energy_term = get_term_by( 'slug', 'energy', 'topic' );
				$energy_url = $energy_term && ! is_wp_error( $energy_term ) ? get_term_link( $energy_term ) : home_url( '/intelligence/' );
				if ( is_wp_error( $energy_url ) ) { $energy_url = home_url( '/intelligence/' ); }

				$security_term = get_term_by( 'slug', 'security', 'topic' );
				$security_url = $security_term && ! is_wp_error( $security_term ) ? get_term_link( $security_term ) : home_url( '/intelligence/' );
				if ( is_wp_error( $security_url ) ) { $security_url = home_url( '/intelligence/' ); }

				$governance_term = get_term_by( 'slug', 'governance', 'topic' );
				$governance_url = $governance_term && ! is_wp_error( $governance_term ) ? get_term_link( $governance_term ) : home_url( '/intelligence/' );
				if ( is_wp_error( $governance_url ) ) { $governance_url = home_url( '/intelligence/' ); }

				$global_term = get_term_by( 'slug', 'global', 'region' );
				$global_url = $global_term && ! is_wp_error( $global_term ) ? get_term_link( $global_term ) : home_url( '/intelligence/' );
				if ( is_wp_error( $global_url ) ) { $global_url = home_url( '/intelligence/' ); }

				$apac_term = get_term_by( 'slug', 'asia-pacific', 'region' );
				$apac_url = $apac_term && ! is_wp_error( $apac_term ) ? get_term_link( $apac_term ) : home_url( '/intelligence/' );
				if ( is_wp_error( $apac_url ) ) { $apac_url = home_url( '/intelligence/' ); }

				$europe_term = get_term_by( 'slug', 'europe', 'region' );
				$europe_url = $europe_term && ! is_wp_error( $europe_term ) ? get_term_link( $europe_term ) : home_url( '/intelligence/' );
				if ( is_wp_error( $europe_url ) ) { $europe_url = home_url( '/intelligence/' ); }

				$americas_term = get_term_by( 'slug', 'americas', 'region' );
				$americas_url = $americas_term && ! is_wp_error( $americas_term ) ? get_term_link( $americas_term ) : home_url( '/intelligence/' );
				if ( is_wp_error( $americas_url ) ) { $americas_url = home_url( '/intelligence/' ); }

				$me_term = get_term_by( 'slug', 'middle-east', 'region' );
				$me_url = $me_term && ! is_wp_error( $me_term ) ? get_term_link( $me_term ) : home_url( '/intelligence/' );
				if ( is_wp_error( $me_url ) ) { $me_url = home_url( '/intelligence/' ); }

				global $ascendance_intelligence_mega_menu, $ascendance_services_mega_menu, $ascendance_industries_mega_menu;

				// Output buffer capture for mega menu templates to avoid duplication
				ob_start();
				?>
				<div class="mega-menu-panel hidden md:block">
					<div class="px-6 md:px-8 py-8 grid grid-cols-12 gap-8 text-left">
						<!-- Col 1: Intelligence Products (3 cols) -->
						<div class="col-span-3 border-r border-brand-divider-light dark:border-brand-divider-dark/20 pr-6">
							<h4 class="font-sans text-[10px] font-bold uppercase tracking-wider text-brand-text-muted dark:text-cream/50 mb-4"><?php esc_html_e( 'Intelligence Products', 'ascendance' ); ?></h4>
							<ul class="flex flex-col gap-2 list-none p-0 m-0">
								<li>
									<a href="<?php echo esc_url( home_url( '/briefs/' ) ); ?>" class="mega-menu-list-link flex items-center gap-3 py-1.5 px-1 rounded-sm text-xs font-sans transition-colors duration-150">
										<i class="fa-regular fa-file-lines text-[13px] w-4 flex-shrink-0 text-center"></i>
										<span><?php esc_html_e( 'Daily Briefings', 'ascendance' ); ?></span>
									</a>
								</li>
								<li>
									<a href="<?php echo esc_url( home_url( '/updates/' ) ); ?>" class="mega-menu-list-link flex items-center gap-3 py-1.5 px-1 rounded-sm text-xs font-sans transition-colors duration-150">
										<i class="fa-solid fa-earth-americas text-[13px] w-4 flex-shrink-0 text-center"></i>
										<span><?php esc_html_e( 'Global Updates', 'ascendance' ); ?></span>
									</a>
								</li>
								<li>
									<a href="<?php echo esc_url( home_url( '/dossiers/' ) ); ?>" class="mega-menu-list-link flex items-center gap-3 py-1.5 px-1 rounded-sm text-xs font-sans transition-colors duration-150">
										<i class="fa-regular fa-folder text-[13px] w-4 flex-shrink-0 text-center"></i>
										<span><?php esc_html_e( 'Country Dossiers', 'ascendance' ); ?></span>
									</a>
								</li>
								<li>
									<a href="<?php echo esc_url( home_url( '/briefs/' ) ); ?>" class="mega-menu-list-link flex items-center gap-3 py-1.5 px-1 rounded-sm text-xs font-sans transition-colors duration-150">
										<i class="fa-solid fa-chart-pie text-[13px] w-4 flex-shrink-0 text-center"></i>
										<span><?php esc_html_e( 'Strategic Forecasts', 'ascendance' ); ?></span>
									</a>
								</li>
								<li>
									<a href="<?php echo esc_url( home_url( '/briefs/' ) ); ?>" class="mega-menu-list-link flex items-center gap-3 py-1.5 px-1 rounded-sm text-xs font-sans transition-colors duration-150">
										<i class="fa-solid fa-arrow-trend-up text-[13px] w-4 flex-shrink-0 text-center"></i>
										<span><?php esc_html_e( 'Trend Analysis', 'ascendance' ); ?></span>
									</a>
								</li>
							</ul>
						</div>

						<!-- Col 2: Regions (3 cols) -->
						<div class="col-span-3 border-r border-brand-divider-light dark:border-brand-divider-dark/20 pr-6">
							<h4 class="font-sans text-[10px] font-bold uppercase tracking-wider text-brand-text-muted dark:text-cream/50 mb-4"><?php esc_html_e( 'Regional Coverage', 'ascendance' ); ?></h4>
							<ul class="flex flex-col gap-2 list-none p-0 m-0">
								<li>
									<a href="<?php echo esc_url( $apac_url ); ?>" class="mega-menu-list-link flex items-center gap-3 py-1.5 px-1 rounded-sm text-xs font-sans transition-colors duration-150">
										<i class="fa-solid fa-earth-asia text-[13px] w-4 flex-shrink-0 text-center"></i>
										<span><?php esc_html_e( 'Asia-Pacific', 'ascendance' ); ?></span>
									</a>
								</li>
								<li>
									<a href="<?php echo esc_url( $me_url ); ?>" class="mega-menu-list-link flex items-center gap-3 py-1.5 px-1 rounded-sm text-xs font-sans transition-colors duration-150">
										<i class="fa-solid fa-earth-africa text-[13px] w-4 flex-shrink-0 text-center"></i>
										<span><?php esc_html_e( 'Middle East & Africa', 'ascendance' ); ?></span>
									</a>
								</li>
								<li>
									<a href="<?php echo esc_url( $europe_url ); ?>" class="mega-menu-list-link flex items-center gap-3 py-1.5 px-1 rounded-sm text-xs font-sans transition-colors duration-150">
										<i class="fa-solid fa-landmark text-[13px] w-4 flex-shrink-0 text-center"></i>
										<span><?php esc_html_e( 'Europe', 'ascendance' ); ?></span>
									</a>
								</li>
								<li>
									<a href="<?php echo esc_url( $americas_url ); ?>" class="mega-menu-list-link flex items-center gap-3 py-1.5 px-1 rounded-sm text-xs font-sans transition-colors duration-150">
										<i class="fa-solid fa-earth-americas text-[13px] w-4 flex-shrink-0 text-center"></i>
										<span><?php esc_html_e( 'The Americas', 'ascendance' ); ?></span>
									</a>
								</li>
								<li>
									<a href="<?php echo esc_url( $global_url ); ?>" class="mega-menu-list-link flex items-center gap-3 py-1.5 px-1 rounded-sm text-xs font-sans transition-colors duration-150">
										<i class="fa-solid fa-flag text-[13px] w-4 flex-shrink-0 text-center"></i>
										<span><?php esc_html_e( 'Eurasia', 'ascendance' ); ?></span>
									</a>
								</li>
							</ul>
						</div>

						<!-- Col 3: Strategic Sectors (3 cols) -->
						<div class="col-span-3 border-r border-brand-divider-light dark:border-brand-divider-dark/20 pr-6">
							<h4 class="font-sans text-[10px] font-bold uppercase tracking-wider text-brand-text-muted dark:text-cream/50 mb-4"><?php esc_html_e( 'Strategic Sectors', 'ascendance' ); ?></h4>
							<ul class="flex flex-col gap-2 list-none p-0 m-0">
								<li>
									<a href="<?php echo esc_url( $geopolitics_url ); ?>" class="mega-menu-list-link flex items-center gap-3 py-1.5 px-1 rounded-sm text-xs font-sans transition-colors duration-150">
										<i class="fa-solid fa-shield-halved text-[13px] w-4 flex-shrink-0 text-center"></i>
										<span><?php esc_html_e( 'Geopolitics & Policy', 'ascendance' ); ?></span>
									</a>
								</li>
								<li>
									<a href="<?php echo esc_url( $economics_url ); ?>" class="mega-menu-list-link flex items-center gap-3 py-1.5 px-1 rounded-sm text-xs font-sans transition-colors duration-150">
										<i class="fa-solid fa-circle-dollar-to-slot text-[13px] w-4 flex-shrink-0 text-center"></i>
										<span><?php esc_html_e( 'Global Economics', 'ascendance' ); ?></span>
									</a>
								</li>
								<li>
									<a href="<?php echo esc_url( $technology_url ); ?>" class="mega-menu-list-link flex items-center gap-3 py-1.5 px-1 rounded-sm text-xs font-sans transition-colors duration-150">
										<i class="fa-solid fa-microchip text-[13px] w-4 flex-shrink-0 text-center"></i>
										<span><?php esc_html_e( 'Technology & Cybersecurity', 'ascendance' ); ?></span>
									</a>
								</li>
								<li>
									<a href="<?php echo esc_url( $energy_url ); ?>" class="mega-menu-list-link flex items-center gap-3 py-1.5 px-1 rounded-sm text-xs font-sans transition-colors duration-150">
										<i class="fa-solid fa-bolt text-[13px] w-4 flex-shrink-0 text-center"></i>
										<span><?php esc_html_e( 'Resource Security', 'ascendance' ); ?></span>
									</a>
								</li>
								<li>
									<a href="<?php echo esc_url( $security_url ); ?>" class="mega-menu-list-link flex items-center gap-3 py-1.5 px-1 rounded-sm text-xs font-sans transition-colors duration-150">
										<i class="fa-solid fa-award text-[13px] w-4 flex-shrink-0 text-center"></i>
										<span><?php esc_html_e( 'Defense & Military', 'ascendance' ); ?></span>
									</a>
								</li>
							</ul>
						</div>

						<!-- Col 4: Latest News (3 cols) -->
						<div class="col-span-3">
							<h4 class="font-sans text-[10px] font-bold uppercase tracking-wider text-brand-text-muted dark:text-cream/50 mb-4"><?php esc_html_e( 'Latest Intelligence', 'ascendance' ); ?></h4>
							<div class="mega-menu-latest flex flex-col gap-2">
								<?php
								$display_posts = array(
									array(
										'date'  => 'OCT 28, 2023',
										'title' => 'Taiwan Strait Stability: Assessing Recent Diplomatic Shifts',
										'link'  => home_url( '/intelligence/' ),
									),
									array(
										'date'  => 'OCT 27, 2023',
										'title' => 'Nord Stream Investigation: Critical Infrastructure Security',
										'link'  => home_url( '/intelligence/' ),
									),
									array(
										'date'  => 'OCT 26, 2023',
										'title' => 'BRICS Expansion: Impacts on Western Hegemony',
										'link'  => home_url( '/intelligence/' ),
									),
								);

								foreach ( $display_posts as $dp ) :
									?>
									<a href="<?php echo esc_url( $dp['link'] ); ?>" class="mega-menu-intel-card block p-3 rounded-sm transition-all duration-150">
										<div class="mega-menu-intel-card-date text-[9px] font-mono uppercase tracking-wider mb-1">
											<?php echo esc_html( $dp['date'] ); ?>
										</div>
										<h5 class="mega-menu-intel-card-title text-xs font-sans font-bold leading-snug m-0 transition-colors line-clamp-2">
											<?php echo esc_html( $dp['title'] ); ?>
										</h5>
									</a>
									<?php
								endforeach;
								?>
							</div>
						</div>
					</div>
				</div>
				<?php
				$ascendance_intelligence_mega_menu = ob_get_clean();

				ob_start();
				?>
				<div class="mega-menu-panel hidden md:block">
					<div class="container mx-auto px-6 md:px-8 py-8 grid grid-cols-12 gap-8 text-left">
						<!-- Col 1: Advisory & Consulting (4 cols) -->
						<div class="col-span-4 border-r border-brand-divider-light dark:border-brand-divider-dark/20 pr-6">
							<h4 class="font-sans text-[10px] font-bold uppercase tracking-wider text-brand-red mb-4"><?php esc_html_e( 'Advisory & Custom Risk', 'ascendance' ); ?></h4>
							<ul class="flex flex-col gap-4 list-none p-0 m-0">
								<li>
									<a href="<?php echo esc_url( home_url( '/services/#advisory' ) ); ?>" class="mega-menu-item block p-1.5 rounded-sm hover:bg-brand-red/[0.03] transition-colors">
										<span class="block text-xs font-sans font-bold text-brand-text-primary dark:text-white"><?php esc_html_e( 'Strategic Advisory Services', 'ascendance' ); ?></span>
										<span class="block text-[10px] text-brand-text-muted dark:text-cream/50 mt-0.5 leading-relaxed"><?php esc_html_e( 'Bespoke geopolitical risk consultations and custom strategy alignment meetings for corporate executives.', 'ascendance' ); ?></span>
									</a>
								</li>
								<li>
									<a href="<?php echo esc_url( home_url( '/services/#custom-briefs' ) ); ?>" class="mega-menu-item block p-1.5 rounded-sm hover:bg-brand-red/[0.03] transition-colors">
										<span class="block text-xs font-sans font-bold text-brand-text-primary dark:text-white"><?php esc_html_e( 'Customized Intelligence Requests', 'ascendance' ); ?></span>
										<span class="block text-[10px] text-brand-text-muted dark:text-cream/50 mt-0.5 leading-relaxed"><?php esc_html_e( 'Commission custom research briefs or dossier updates specifically tailored to your organization\'s asset locations.', 'ascendance' ); ?></span>
									</a>
								</li>
							</ul>
						</div>

						<!-- Col 2: Platform Products (4 cols) -->
						<div class="col-span-4 border-r border-brand-divider-light dark:border-brand-divider-dark/20 pr-6">
							<h4 class="font-sans text-[10px] font-bold uppercase tracking-wider text-brand-red mb-4"><?php esc_html_e( 'Platform Services', 'ascendance' ); ?></h4>
							<ul class="flex flex-col gap-4 list-none p-0 m-0">
								<li>
									<a href="<?php echo esc_url( home_url( '/services/' ) ); ?>" class="mega-menu-item block p-1.5 rounded-sm hover:bg-brand-red/[0.03] transition-colors">
										<span class="block text-xs font-sans font-bold text-brand-text-primary dark:text-white"><?php esc_html_e( 'Interactive Dashboard', 'ascendance' ); ?></span>
										<span class="block text-[10px] text-brand-text-muted dark:text-cream/50 mt-0.5 leading-relaxed"><?php esc_html_e( 'Tailored feed preferences, saved intelligence briefings, and user-defined email alert cadences.', 'ascendance' ); ?></span>
									</a>
								</li>
								<li>
									<a href="<?php echo esc_url( home_url( '/services/#api' ) ); ?>" class="mega-menu-item block p-1.5 rounded-sm hover:bg-brand-red/[0.03] transition-colors">
										<span class="block text-xs font-sans font-bold text-brand-text-primary dark:text-white"><?php esc_html_e( 'Machine-Readable REST API', 'ascendance' ); ?></span>
										<span class="block text-[10px] text-brand-text-muted dark:text-cream/50 mt-0.5 leading-relaxed"><?php esc_html_e( 'Inject direct JSON streams of brief findings and critical impact updates into corporate GRC dashboard interfaces.', 'ascendance' ); ?></span>
									</a>
								</li>
							</ul>
						</div>

						<!-- Col 3: Subscriptions (4 cols) -->
						<div class="col-span-4">
							<h4 class="font-sans text-[10px] font-bold uppercase tracking-wider text-brand-red mb-4"><?php esc_html_e( 'Membership Tiers', 'ascendance' ); ?></h4>
							<div class="flex flex-col gap-3">
								<div class="p-3.5 border border-brand-divider-light dark:border-brand-divider-dark/40 rounded-sm">
									<div class="flex items-center justify-between mb-1">
										<span class="text-xs font-sans font-bold text-brand-text-primary dark:text-white"><?php esc_html_e( 'Essential Tier', 'ascendance' ); ?></span>
										<span class="text-[10px] font-mono text-brand-red font-bold"><?php esc_html_e( 'Free Trial', 'ascendance' ); ?></span>
									</div>
									<p class="text-[10px] text-brand-text-muted dark:text-cream/50 leading-relaxed m-0"><?php esc_html_e( 'Basic intelligence briefings and alerts. Recommended for individual researchers.', 'ascendance' ); ?></p>
								</div>
								<div class="p-3.5 border border-brand-divider-light dark:border-brand-divider-dark/40 rounded-sm">
									<div class="flex items-center justify-between mb-1">
										<span class="text-xs font-sans font-bold text-brand-text-primary dark:text-white"><?php esc_html_e( 'Professional & Enterprise', 'ascendance' ); ?></span>
										<span class="text-[10px] font-mono text-brand-red font-bold"><?php esc_html_e( 'Full Access', 'ascendance' ); ?></span>
									</div>
									<p class="text-[10px] text-brand-text-muted dark:text-cream/50 leading-relaxed m-0"><?php esc_html_e( 'Unlimited intelligence, living dossiers, dedicated analyst support, and API delivery tools.', 'ascendance' ); ?></p>
								</div>
								<a href="<?php echo esc_url( function_exists( 'pmpro_url' ) ? pmpro_url( 'levels' ) : home_url( '/membership-levels/' ) ); ?>" class="btn btn-primary w-full text-center text-xs py-2 mt-1">
									<?php esc_html_e( 'Compare Plans & Rates', 'ascendance' ); ?>
								</a>
							</div>
						</div>
					</div>
				</div>
				<?php
				$ascendance_services_mega_menu = ob_get_clean();

				ob_start();
				?>
				<div class="mega-menu-panel hidden md:block">
					<div class="container mx-auto px-6 md:px-8 py-8 grid grid-cols-12 gap-8 text-left">
						<!-- Column 1: Geopolitics & Economics (4 cols) -->
						<div class="col-span-4 border-r border-brand-divider-light dark:border-brand-divider-dark/20 pr-6">
							<h4 class="font-sans text-[10px] font-bold uppercase tracking-wider text-brand-red mb-4"><?php esc_html_e( 'Diplomacy & Finance', 'ascendance' ); ?></h4>
							<ul class="flex flex-col gap-4 list-none p-0 m-0">
								<li>
									<a href="<?php echo esc_url( $geopolitics_url ); ?>" class="mega-menu-item flex items-start gap-3 p-1.5 rounded-sm hover:bg-brand-red/[0.03] transition-colors">
										<i class="fa-solid fa-earth-americas text-brand-red text-sm mt-0.5 w-5 text-center"></i>
										<div>
											<span class="block text-xs font-sans font-bold text-brand-text-primary dark:text-white"><?php esc_html_e( 'Geopolitics & Diplomacy', 'ascendance' ); ?></span>
											<span class="block text-[10px] text-brand-text-muted dark:text-cream/50 mt-0.5 leading-relaxed"><?php esc_html_e( 'Interstate relations, multilateral frameworks, and major power strategic competition.', 'ascendance' ); ?></span>
										</div>
									</a>
								</li>
								<li>
									<a href="<?php echo esc_url( $economics_url ); ?>" class="mega-menu-item flex items-start gap-3 p-1.5 rounded-sm hover:bg-brand-red/[0.03] transition-colors">
										<i class="fa-solid fa-chart-line text-brand-red text-sm mt-0.5 w-5 text-center"></i>
										<div>
											<span class="block text-xs font-sans font-bold text-brand-text-primary dark:text-white"><?php esc_html_e( 'Economics & Markets', 'ascendance' ); ?></span>
											<span class="block text-[10px] text-brand-text-muted dark:text-cream/50 mt-0.5 leading-relaxed"><?php esc_html_e( 'Trade policy, international sanctions, sovereign debt risk, and commodity market shifts.', 'ascendance' ); ?></span>
										</div>
									</a>
								</li>
							</ul>
						</div>

						<!-- Column 2: Tech & Energy (4 cols) -->
						<div class="col-span-4 border-r border-brand-divider-light dark:border-brand-divider-dark/20 pr-6">
							<h4 class="font-sans text-[10px] font-bold uppercase tracking-wider text-brand-red mb-4"><?php esc_html_e( 'Critical Assets', 'ascendance' ); ?></h4>
							<ul class="flex flex-col gap-4 list-none p-0 m-0">
								<li>
									<a href="<?php echo esc_url( $technology_url ); ?>" class="mega-menu-item flex items-start gap-3 p-1.5 rounded-sm hover:bg-brand-red/[0.03] transition-colors">
										<i class="fa-solid fa-microchip text-brand-red text-sm mt-0.5 w-5 text-center"></i>
										<div>
											<span class="block text-xs font-sans font-bold text-brand-text-primary dark:text-white"><?php esc_html_e( 'Technology & AI', 'ascendance' ); ?></span>
											<span class="block text-[10px] text-brand-text-muted dark:text-cream/50 mt-0.5 leading-relaxed"><?php esc_html_e( 'Semiconductor supply constraints, regulatory divergence, and cybersecurity risk profiles.', 'ascendance' ); ?></span>
										</div>
									</a>
								</li>
								<li>
									<a href="<?php echo esc_url( $energy_url ); ?>" class="mega-menu-item flex items-start gap-3 p-1.5 rounded-sm hover:bg-brand-red/[0.03] transition-colors">
										<i class="fa-solid fa-bolt text-brand-red text-sm mt-0.5 w-5 text-center"></i>
										<div>
											<span class="block text-xs font-sans font-bold text-brand-text-primary dark:text-white"><?php esc_html_e( 'Energy & Resources', 'ascendance' ); ?></span>
											<span class="block text-[10px] text-brand-text-muted dark:text-cream/50 mt-0.5 leading-relaxed"><?php esc_html_e( 'Sovereign energy security, critical minerals supply lines, and transition geopolitics.', 'ascendance' ); ?></span>
										</div>
									</a>
								</li>
							</ul>
						</div>

						<!-- Column 3: Defense & Governance (4 cols) -->
						<div class="col-span-4">
							<h4 class="font-sans text-[10px] font-bold uppercase tracking-wider text-brand-red mb-4"><?php esc_html_e( 'Defense & Policy', 'ascendance' ); ?></h4>
							<ul class="flex flex-col gap-4 list-none p-0 m-0">
								<li>
									<a href="<?php echo esc_url( $security_url ); ?>" class="mega-menu-item flex items-start gap-3 p-1.5 rounded-sm hover:bg-brand-red/[0.03] transition-colors">
										<i class="fa-solid fa-shield-halved text-brand-red text-sm mt-0.5 w-5 text-center"></i>
										<div>
											<span class="block text-xs font-sans font-bold text-brand-text-primary dark:text-white"><?php esc_html_e( 'Security & Defence', 'ascendance' ); ?></span>
											<span class="block text-[10px] text-brand-text-muted dark:text-cream/50 mt-0.5 leading-relaxed"><?php esc_html_e( 'Military modernization, sub-threshold hybrid warfare, and global deterrence doctrine.', 'ascendance' ); ?></span>
										</div>
									</a>
								</li>
								<li>
									<a href="<?php echo esc_url( $governance_url ); ?>" class="mega-menu-item flex items-start gap-3 p-1.5 rounded-sm hover:bg-brand-red/[0.03] transition-colors">
										<i class="fa-solid fa-scale-balanced text-brand-red text-sm mt-0.5 w-5 text-center"></i>
										<div>
											<span class="block text-xs font-sans font-bold text-brand-text-primary dark:text-white"><?php esc_html_e( 'Governance & Policy', 'ascendance' ); ?></span>
											<span class="block text-[10px] text-brand-text-muted dark:text-cream/50 mt-0.5 leading-relaxed"><?php esc_html_e( 'Regulatory compliance, policy exposure, institutional stability, and governance risks.', 'ascendance' ); ?></span>
										</div>
									</a>
								</li>
							</ul>
						</div>
					</div>
				</div>
				<?php
				$ascendance_industries_mega_menu = ob_get_clean();
				?>

				<nav id="site-navigation" class="main-navigation header-nav hidden md:flex items-center gap-8" aria-label="<?php esc_attr_e( 'Primary Navigation', 'ascendance' ); ?>">
					<?php
					if ( has_nav_menu( 'menu-primary' ) ) {
						wp_nav_menu(
							array(
								'theme_location' => 'menu-primary',
								'menu_id'        => 'primary-menu',
								'container'      => false,
								'menu_class'     => 'main-menu flex list-none gap-6',
							)
						);
					} else {
						?>
						<ul id="primary-menu" class="main-menu flex list-none gap-6">
							<li class="<?php echo esc_attr( ascendance_menu_class( 'intelligence' ) ); ?> menu-item-has-mega-menu group">
								<a href="<?php echo esc_url( home_url( '/intelligence/' ) ); ?>">
									<?php esc_html_e( 'Intelligence', 'ascendance' ); ?>
								</a>
								<?php echo $ascendance_intelligence_mega_menu; // phpcs:ignore WordPress.Security.EscapeOutput ?>
							</li>
							<li class="<?php echo esc_attr( ascendance_menu_class( 'analysis' ) ); ?> menu-item">
								<a href="<?php echo esc_url( $geopolitics_url ); ?>">
									<?php esc_html_e( 'Analysis', 'ascendance' ); ?>
								</a>
							</li>
							<li class="<?php echo esc_attr( ascendance_menu_class( 'sectors' ) ); ?> menu-item">
								<a href="<?php echo esc_url( home_url( '/industries/' ) ); ?>">
									<?php esc_html_e( 'Sectors', 'ascendance' ); ?>
								</a>
							</li>
							<li class="<?php echo esc_attr( ascendance_menu_class( 'reports' ) ); ?> menu-item">
								<a href="<?php echo esc_url( $geopolitics_url ); ?>">
									<?php esc_html_e( 'Reports', 'ascendance' ); ?>
								</a>
							</li>
							<li class="<?php echo esc_attr( ascendance_menu_class( 'events' ) ); ?> menu-item">
								<a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">
									<?php esc_html_e( 'Events', 'ascendance' ); ?>
								</a>
							</li>
							<li class="<?php echo esc_attr( ascendance_menu_class( 'membership' ) ); ?> menu-item">
								<a href="<?php echo esc_url( function_exists( 'pmpro_url' ) ? pmpro_url( 'levels' ) : home_url( '/membership-levels/' ) ); ?>">
									<?php esc_html_e( 'Membership', 'ascendance' ); ?>
								</a>
							</li>
						</ul>
						<?php
					}
					?>

					<!-- Mobile-only actions (Inside drawer) -->
					<div class="mobile-only-actions flex flex-col gap-4 mt-6">
						<div class="lang-switch-mobile flex gap-4 text-xs font-bold font-sans justify-center">
							<?php
							if ( function_exists( 'pll_the_languages' ) ) {
								$languages = pll_the_languages( array( 'raw' => 1 ) );
								if ( ! empty( $languages ) ) {
									$i = 0;
									foreach ( $languages as $lang ) {
										if ( $i > 0 ) {
											echo '<span class="lang-separator text-brand-divider-light">|</span>';
										}
										$active_class = $lang['current_lang'] ? 'active text-brand-red' : 'text-brand-text-muted';
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
								<a href="<?php echo esc_url( $en_url ); ?>" class="lang-link <?php echo !$is_french ? 'active text-brand-red' : 'text-brand-text-muted'; ?>">EN</a>
								<span class="lang-separator text-brand-divider-light">|</span>
								<a href="<?php echo esc_url( $fr_url ); ?>" class="lang-link <?php echo $is_french ? 'active text-brand-red' : 'text-brand-text-muted'; ?>">FR</a>
								<?php
							}
							?>
						</div>
						<!-- Theme Switch (Mobile) -->
						<div class="theme-switch-mobile">
							<button id="theme-toggle-mobile" class="theme-toggle-btn-mobile w-full py-2.5 bg-transparent border border-brand-divider-light dark:border-brand-divider-dark text-brand-text-muted rounded-sm cursor-pointer transition-all duration-150 hover:text-brand-text-primary hover:border-brand-text-primary mt-4 font-sans text-sm font-bold flex items-center justify-center gap-2">
								<i class="fa-solid fa-moon"></i>
								<span><?php esc_html_e( 'Dark Mode', 'ascendance' ); ?></span>
							</button>
						</div>
						<?php if ( $is_logged_in ) : ?>
							<a href="<?php echo esc_url( pmpro_url( 'account' ) ); ?>" class="mobile-action-link flex items-center gap-2 text-sm text-brand-text-muted hover:text-brand-text-primary"><i class="fa-regular fa-address-card"></i> <?php esc_html_e( 'Account', 'ascendance' ); ?></a>
							<a href="<?php echo esc_url( home_url( '/preferences/' ) ); ?>" class="mobile-action-link flex items-center gap-2 text-sm text-brand-text-muted hover:text-brand-text-primary"><i class="fa-solid fa-sliders"></i> <?php esc_html_e( 'Preferences', 'ascendance' ); ?></a>
							<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="mobile-action-link logout flex items-center gap-2 text-sm text-brand-text-muted hover:text-brand-red"><i class="fa-solid fa-right-from-bracket"></i> <?php esc_html_e( 'Logout', 'ascendance' ); ?></a>
						<?php else : ?>
							<a href="<?php echo esc_url( home_url( '/login/' ) ); ?>" class="mobile-action-link flex items-center justify-center gap-2 text-sm text-brand-text-muted hover:text-brand-text-primary"><i class="fa-solid fa-right-to-bracket"></i> <?php esc_html_e( 'Log in', 'ascendance' ); ?></a>
							<a href="<?php echo esc_url( function_exists( 'pmpro_url' ) ? pmpro_url( 'levels' ) : home_url( '/membership-levels/' ) ); ?>" class="btn btn-primary w-full text-center flex justify-center py-2.5"><?php esc_html_e( 'Subscribe', 'ascendance' ); ?></a>
						<?php endif; ?>
					</div>
				</nav>

				<!-- Right Side Actions (Desktop & Search Toggle) -->
				<div class="header-actions flex items-center gap-6 ml-auto">
					<!-- Search Toggle & Form -->
					<div class="header-search-wrap relative">
						<form role="search" method="get" class="header-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
							<div class="search-input-wrapper flex items-center border border-brand-divider-light/10 dark:border-brand-divider-dark/40 bg-white/5 dark:bg-navy rounded-sm px-2 py-1">
								<input type="search" class="search-field bg-transparent border-none outline-none text-xs text-brand-text-primary dark:text-cream placeholder-brand-text-muted/50 w-0 focus:w-36 transition-all duration-300" placeholder="<?php esc_attr_e( 'Search...', 'ascendance' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
								<button type="submit" class="search-submit bg-transparent border-none cursor-pointer text-brand-text-muted hover:text-brand-red p-1" aria-label="<?php esc_attr_e( 'Search', 'ascendance' ); ?>">
									<i class="fa-solid fa-magnifying-glass text-sm"></i>
								</button>
							</div>
						</form>
					</div>

					<!-- Theme Switch (Desktop-only) -->
					<button id="theme-toggle" class="theme-toggle-btn desktop-only flex items-center justify-center bg-transparent border-none text-brand-text-muted hover:text-brand-text-primary dark:hover:text-white p-1 cursor-pointer" aria-label="<?php esc_attr_e( 'Toggle theme mode', 'ascendance' ); ?>">
						<i class="fa-solid fa-moon text-base"></i>
					</button>

					<!-- Language Switcher (Desktop-only) -->
					<div class="lang-switch desktop-only flex gap-2 items-center text-xs font-bold font-sans">
						<?php
						if ( function_exists( 'pll_the_languages' ) ) {
							$languages = pll_the_languages( array( 'raw' => 1 ) );
							if ( ! empty( $languages ) ) {
								$i = 0;
								foreach ( $languages as $lang ) {
									if ( $i > 0 ) {
										echo '<span class="lang-separator text-brand-divider-light">|</span>';
									}
									$active_class = $lang['current_lang'] ? 'active text-brand-red' : 'text-brand-text-muted';
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
							<a href="<?php echo esc_url( $en_url ); ?>" class="lang-link <?php echo !$is_french ? 'active text-brand-red' : 'text-brand-text-muted'; ?>">EN</a>
							<span class="lang-separator text-brand-divider-light">|</span>
							<a href="<?php echo esc_url( $fr_url ); ?>" class="lang-link <?php echo $is_french ? 'active text-brand-red' : 'text-brand-text-muted'; ?>">FR</a>
							<?php
						}
						?>
					</div>

					<?php if ( $is_logged_in ) : ?>
						<!-- Account dropdown (Desktop-only) -->
						<div class="header-account-dropdown desktop-only relative">
							<button class="account-toggle flex items-center gap-1 bg-transparent border-none text-brand-text-muted hover:text-brand-text-primary dark:hover:text-white p-1 cursor-pointer" aria-label="<?php esc_attr_e( 'Account menu', 'ascendance' ); ?>">
								<i class="fa-regular fa-user text-base"></i>
							</button>
							<div class="account-dropdown-menu absolute right-0 mt-2 w-48 bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark rounded-sm shadow-md py-2 z-50">
								<ul class="account-menu list-none p-0 m-0">
									<li><a href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>" class="flex items-center gap-2 px-4 py-2 text-sm text-brand-text-muted hover:text-brand-text-primary dark:hover:text-white hover:bg-brand-red/5"><i class="fa-solid fa-gauge"></i><?php esc_html_e( 'Dashboard', 'ascendance' ); ?></a></li>
									<li><a href="<?php echo esc_url( pmpro_url( 'account' ) ); ?>" class="flex items-center gap-2 px-4 py-2 text-sm text-brand-text-muted hover:text-brand-text-primary dark:hover:text-white hover:bg-brand-red/5"><i class="fa-regular fa-address-card"></i><?php esc_html_e( 'Account', 'ascendance' ); ?></a></li>
									<li><a href="<?php echo esc_url( home_url( '/preferences/' ) ); ?>" class="flex items-center gap-2 px-4 py-2 text-sm text-brand-text-muted hover:text-brand-text-primary dark:hover:text-white hover:bg-brand-red/5"><i class="fa-solid fa-sliders"></i><?php esc_html_e( 'Preferences', 'ascendance' ); ?></a></li>
									<li class="menu-divider border-t border-brand-divider-light dark:border-brand-divider-dark my-1"></li>
									<li><a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="flex items-center gap-2 px-4 py-2 text-sm text-brand-text-muted hover:text-brand-red hover:bg-brand-red/5"><i class="fa-solid fa-right-from-bracket"></i><?php esc_html_e( 'Logout', 'ascendance' ); ?></a></li>
								</ul>
							</div>
						</div>
					<?php else : ?>
						<!-- Login Link (Desktop-only) -->
						<a href="<?php echo esc_url( home_url( '/login/' ) ); ?>" class="desktop-only text-brand-text-muted hover:text-brand-text-primary dark:hover:text-white p-1" aria-label="<?php esc_attr_e( 'Log in', 'ascendance' ); ?>">
							<i class="fa-regular fa-user text-base"></i>
						</a>
					<?php endif; ?>

					<!-- Mobile toggle -->
					<button class="menu-toggle flex items-center justify-center w-9.5 h-9.5 md:hidden bg-transparent border border-brand-divider-light dark:border-brand-divider-dark text-brand-text-muted hover:text-brand-text-primary dark:hover:text-white rounded-sm cursor-pointer" id="menu-toggle" aria-controls="site-navigation" aria-expanded="false" aria-label="<?php esc_attr_e( 'Toggle navigation', 'ascendance' ); ?>">
						<i class="fa-solid fa-bars"></i>
					</button>
				</div>

			</div><!-- .header-inner -->
		</div><!-- .container -->
	</header>

	<?php
	// Yoast Breadcrumbs shortcode or function integration (not on front page or 404 pages)
	if ( ! is_front_page() && ! is_404() ) :
		if ( shortcode_exists( 'wpseo_breadcrumb' ) ) :
			?>
			<div class="breadcrumbs-container bg-navy-deep border-b border-brand-divider-dark py-2">
				<div class="container mx-auto px-6 md:px-8">
					<?php echo do_shortcode( '[wpseo_breadcrumb]' ); ?>
				</div>
			</div>
			<?php
		elseif ( function_exists( 'yoast_breadcrumb' ) ) :
			?>
			<div class="breadcrumbs-container bg-navy-deep border-b border-brand-divider-dark py-2">
				<div class="container mx-auto px-6 md:px-8">
					<?php yoast_breadcrumb( '<p id="breadcrumbs" class="font-sans text-[0.72rem] text-cream/55 flex items-center flex-wrap gap-2 m-0">', '</p>' ); ?>
				</div>
			</div>
			<?php
		endif;
	endif;
	?>

	<div id="content" class="site-content flex-grow">
