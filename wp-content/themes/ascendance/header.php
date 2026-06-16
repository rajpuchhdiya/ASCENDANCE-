<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
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
	<a class="skip-link screen-reader-text" href="#primary" style="display:none;"><?php esc_html_e( 'Skip to content', 'ascendance' ); ?></a>

	<header id="masthead" class="site-header">
		<div class="container">
			<div class="site-logo">
				<?php
				if ( has_custom_logo() ) {
					the_custom_logo();
				} else {
					?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
					<?php
				}
				?>
			</div>

			<button class="menu-toggle" id="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
				<i class="fa-solid fa-bars"></i>
			</button>

			<nav id="site-navigation" class="main-navigation">
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
				<a href="#cta" class="header-cta"><?php esc_html_e( 'Connect', 'ascendance' ); ?></a>
			</nav>
		</div>
	</header>

	<?php
	// Fallback menu when no menu is set
	if ( ! function_exists( 'ascendance_default_menu' ) ) {
		function ascendance_default_menu() {
			echo '<ul id="primary-menu" class="main-menu">';
			echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'ascendance' ) . '</a></li>';
			echo '<li><a href="#features">' . esc_html__( 'Features', 'ascendance' ) . '</a></li>';
			echo '<li><a href="#about">' . esc_html__( 'About', 'ascendance' ) . '</a></li>';
			echo '<li><a href="#blog">' . esc_html__( 'Blog', 'ascendance' ) . '</a></li>';
			echo '</ul>';
		}
	}
	?>

	<div id="content" class="site-content">
