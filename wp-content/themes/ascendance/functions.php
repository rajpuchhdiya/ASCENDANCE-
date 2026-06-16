<?php
/**
 * Ascendance functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Ascendance
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'ascendance_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 */
	function ascendance_setup() {
		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and WordPress will
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );

		// This theme uses wp_nav_menu() in two locations.
		register_nav_menus(
			array(
				'menu-primary' => esc_html__( 'Primary Header Menu', 'ascendance' ),
				'menu-footer'  => esc_html__( 'Footer Menu', 'ascendance' ),
			)
		);

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			)
		);

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 250,
				'width'       => 250,
				'flex-width'  => true,
				'flex-height' => true,
			)
		);
	}
endif;
add_action( 'after_setup_theme', 'ascendance_setup' );

/**
 * Theme font loading with preconnect hints for performance (Section 3.6)
 */
function ascendance_enqueue_fonts() {
	// Preconnect hints
	add_action( 'wp_head', function() {
		echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
		echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
		echo '<link rel="preconnect" href="https://fonts.cdnfonts.com">' . "\n";
	}, 1 );

	// Google Fonts: Noto Serif (variable) + JetBrains Mono + Barlow fallback
	wp_enqueue_style(
		'ascendance-google-fonts',
		'https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,300..900;1,300..900&family=JetBrains+Mono:wght@400;500;700&family=Barlow:wght@400;500;600;700&display=swap',
		array(),
		null
	);

	// Cooper Hewitt from cdnfonts
	wp_enqueue_style(
		'ascendance-cooper-hewitt',
		'https://fonts.cdnfonts.com/css/cooper-hewitt',
		array(),
		null
	);
}
add_action( 'wp_enqueue_scripts', 'ascendance_enqueue_fonts' );

/**
 * Enqueue scripts and styles.
 */
function ascendance_scripts() {
	// Enqueue FontAwesome for icons
	wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0' );

	// Check environment and load either compiled or raw development assets
	$env = defined( 'WP_ENVIRONMENT_TYPE' ) ? WP_ENVIRONMENT_TYPE : 'production';

	if ( in_array( $env, array( 'staging', 'production' ), true ) ) {
		// Enqueue compiled production stylesheet
		wp_enqueue_style( 'ascendance-style', get_template_directory_uri() . '/assets/dist/css/theme.css', array( 'ascendance-google-fonts', 'ascendance-cooper-hewitt', 'font-awesome' ), '2.0.0' );
		// Enqueue compiled production JS
		wp_enqueue_script( 'ascendance-js', get_template_directory_uri() . '/assets/dist/js/main.js', array(), '2.0.0', true );
	} else {
		// Enqueue unminified development stylesheet
		wp_enqueue_style( 'ascendance-style', get_stylesheet_uri(), array( 'ascendance-google-fonts', 'ascendance-cooper-hewitt', 'font-awesome' ), '2.0.0' );
		// Enqueue development JS
		wp_enqueue_script( 'ascendance-js', get_template_directory_uri() . '/assets/js/main.js', array(), '2.0.0', true );
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'ascendance_scripts' );

/**
 * Filter the excerpt length.
 */
function ascendance_custom_excerpt_length( $length ) {
	return 20; // 20 words
}
add_filter( 'excerpt_length', 'ascendance_custom_excerpt_length' );

/**
 * Filter the excerpt "Read More" suffix.
 */
function ascendance_custom_excerpt_more( $more ) {
	return '...';
}
add_filter( 'excerpt_more', 'ascendance_custom_excerpt_more' );
