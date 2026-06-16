<?php
/**
 * The header for the Ascendance Intelligence Platform
 *
 * @package Ascendance
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'ascendance' ); ?></a>

	<header id="masthead" class="site-header">
		<div class="container">
			<div class="header-inner">

				<!-- Logo -->
				<div class="site-logo">
					<?php if ( has_custom_logo() ) : ?>
						<?php the_custom_logo(); ?>
					<?php else : ?>
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="header-logo-mark">
							<?php bloginfo( 'name' ); ?>
						</a>
					<?php endif; ?>
				</div>

				<!-- Mobile toggle -->
				<button class="menu-toggle" id="menu-toggle" aria-controls="site-navigation" aria-expanded="false" aria-label="<?php esc_attr_e( 'Toggle navigation', 'ascendance' ); ?>">
					<i class="fa-solid fa-bars"></i>
				</button>

				<!-- Primary navigation -->
				<nav id="site-navigation" class="main-navigation header-nav" aria-label="<?php esc_attr_e( 'Primary Navigation', 'ascendance' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'menu-primary',
							'menu_id'        => 'primary-menu',
							'container'      => false,
							'menu_class'     => 'main-menu',
							'fallback_cb'    => 'ascendance_default_menu',
						)
					);
					?>
					<a href="<?php echo esc_url( home_url( '/newsletter/' ) ); ?>" class="btn-subscribe" id="header-subscribe-cta">
						<i class="fa-regular fa-newspaper"></i>
						<?php esc_html_e( 'Subscribe', 'ascendance' ); ?>
					</a>
				</nav>

			</div><!-- .header-inner -->
		</div><!-- .container -->
	</header>

	<?php
	// Fallback menu
	if ( ! function_exists( 'ascendance_default_menu' ) ) {
		function ascendance_default_menu() {
			$links = array(
				home_url( '/' )              => 'Home',
				home_url( '/intelligence/' ) => 'Intelligence',
				home_url( '/about/' )        => 'About',
				home_url( '/services/' )     => 'Services',
				home_url( '/industries/' )   => 'Industries',
				home_url( '/faq/' )          => 'FAQ',
				home_url( '/contact/' )      => 'Contact',
			);
			echo '<ul id="primary-menu" class="main-menu">';
			foreach ( $links as $url => $label ) {
				echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
			}
			echo '</ul>';
		}
	}
	?>

	<div id="content" class="site-content">
