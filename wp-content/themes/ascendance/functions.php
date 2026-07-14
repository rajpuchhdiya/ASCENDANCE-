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

if ( ! defined( 'ASCENDANCE_VERSION' ) ) {
	define( 'ASCENDANCE_VERSION', time() );
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
				'menu-primary'      => esc_html__( 'Primary Header Menu', 'ascendance' ),
				'menu-footer'       => esc_html__( 'Footer Quick Links', 'ascendance' ),
				'menu-footer-intel' => esc_html__( 'Footer Intelligence Links', 'ascendance' ),
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
 * Theme font loading with local self-hosted files and preload hints for performance
 */
function ascendance_enqueue_fonts() {
	// Preload primary weight of Noto Serif used in the lead paragraph
	add_action( 'wp_head', function() {
		echo '<link rel="preload" href="' . esc_url( get_template_directory_uri() . '/assets/fonts/noto-serif-variable.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
	}, 1 );

	// Enqueue local self-hosted fonts
	wp_enqueue_style(
		'ascendance-fonts',
		get_template_directory_uri() . '/assets/css/fonts.css',
		array(),
		ASCENDANCE_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'ascendance_enqueue_fonts' );

/**
 * Preload featured images on singular templates to optimize LCP
 */
function ascendance_preload_featured_image() {
	if ( is_singular( array( 'brief', 'dossier', 'update' ) ) && has_post_thumbnail() ) {
		$thumbnail_id = get_post_thumbnail_id();
		$image_src    = wp_get_attachment_image_src( $thumbnail_id, 'full' );
		if ( $image_src ) {
			echo '<link rel="preload" href="' . esc_url( $image_src[0] ) . '" as="image">' . "\n";
		}
	}
}
add_action( 'wp_head', 'ascendance_preload_featured_image', 1 );

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
		wp_enqueue_style( 'ascendance-style', get_template_directory_uri() . '/assets/dist/css/theme.css', array( 'ascendance-fonts', 'font-awesome' ), ASCENDANCE_VERSION );
		// Enqueue compiled production JS
		wp_enqueue_script( 'ascendance-js', get_template_directory_uri() . '/assets/dist/js/main.js', array(), ASCENDANCE_VERSION, true );

		// Enqueue compiled production pages CSS
		wp_enqueue_style(
			'ascendance-pages',
			get_template_directory_uri() . '/assets/dist/css/pages.css',
			array( 'ascendance-style' ),
			ASCENDANCE_VERSION
		);

		// Enqueue compiled production pages JS
		wp_enqueue_script(
			'ascendance-pages',
			get_template_directory_uri() . '/assets/dist/js/pages.js',
			array(),
			ASCENDANCE_VERSION,
			true
		);
	} else {
		// Enqueue compiled development stylesheet from assets/dist/css/theme.css
		wp_enqueue_style( 'ascendance-style', get_template_directory_uri() . '/assets/dist/css/theme.css', array( 'ascendance-fonts', 'font-awesome' ), ASCENDANCE_VERSION );
		// Enqueue development JS
		wp_enqueue_script( 'ascendance-js', get_template_directory_uri() . '/assets/dist/js/main.js', array(), ASCENDANCE_VERSION, true );

		// Pages CSS — sitewide compiled from Vite
		wp_enqueue_style(
			'ascendance-pages',
			get_template_directory_uri() . '/assets/dist/css/pages.css',
			array( 'ascendance-style' ),
			ASCENDANCE_VERSION
		);

		// Pages JS — sitewide interactive behaviours compiled from Vite
		wp_enqueue_script(
			'ascendance-pages',
			get_template_directory_uri() . '/assets/dist/js/pages.js',
			array(),
			ASCENDANCE_VERSION,
			true
		);
	}

	wp_localize_script( 'ascendance-pages', 'ascendance_params', array(
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'ascendance_intel_filter' ),
	) );

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

/**
 * Render a tier access badge HTML string.
 *
 * @param string $tier  Tier slug: essential | professional | enterprise
 * @return string HTML badge span.
 */
function ascendance_tier_badge( string $tier ): string {
	$labels = [
		'essential'    => 'Essential',
		'professional' => 'Professional',
		'enterprise'   => 'Enterprise',
	];
	$label = $labels[ $tier ] ?? ucfirst( $tier );
	return '<span class="tier-badge ' . esc_attr( $tier ) . '">' . esc_html( $label ) . '</span>';
}

/**
 * Render an impact assessment badge HTML string.
 *
 * @param string $impact  Impact slug: low | medium | high | critical
 * @return string HTML badge span.
 */
function ascendance_impact_badge( string $impact ): string {
	$labels = [
		'low'      => 'Low',
		'medium'   => 'Medium',
		'high'     => 'High',
		'critical' => 'Critical',
	];
	$label = $labels[ $impact ] ?? ucfirst( $impact );
	return '<span class="impact-badge ' . esc_attr( $impact ) . '">' . esc_html( $label ) . '</span>';
}

/**
 * Return the post type label for a CPT slug.
 */
function ascendance_cpt_label( string $post_type ): string {
	return match ( $post_type ) {
		'brief'   => 'Brief',
		'update'  => 'Update',
		'dossier' => 'Dossier',
		default   => ucfirst( $post_type ),
	};
}

/**
 * ==============================================================================
 * SUBSCRIBER ROUTING & REDIRECTION RULES (Premium Self-Service)
 * ==============================================================================
 */

// 1. Restrict subscribers from accessing wp-admin dashboard
add_action( 'admin_init', function() {
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        return;
    }
    if ( is_user_logged_in() && ! current_user_can( 'edit_posts' ) ) {
        wp_safe_redirect( home_url( '/dashboard/' ) );
        exit;
    }
} );

// 2. Redirect logged-in users away from custom login page
add_action( 'template_redirect', function() {
    if ( is_page( 'login' ) && is_user_logged_in() ) {
        wp_safe_redirect( home_url( '/dashboard/' ) );
        exit;
    }
} );

// 3. Redirect failed login attempts back to custom login page
add_action( 'wp_login_failed', function() {
    $referrer = wp_get_referer();
    if ( $referrer && ! strpos( $referrer, 'wp-login' ) && ! strpos( $referrer, 'wp-admin' ) ) {
        wp_safe_redirect( add_query_arg( 'login', 'failed', home_url( '/login/' ) ) );
        exit;
    }
} );

// 4. Redirect empty fields on authentication back to custom login page
add_filter( 'authenticate', function( $user, $username, $password ) {
    if ( isset( $_POST['log'] ) && ( empty( $username ) || empty( $password ) ) ) {
        wp_safe_redirect( add_query_arg( 'login', 'empty', home_url( '/login/' ) ) );
        exit;
    }
    return $user;
}, 30, 3 );

// 5. Successful login redirection for subscribers
add_filter( 'login_redirect', function( $redirect_to, $request, $user ) {
    if ( isset( $user->roles ) && is_array( $user->roles ) ) {
        if ( in_array( 'administrator', $user->roles, true ) || in_array( 'editor', $user->roles, true ) ) {
            return $redirect_to;
        } else {
            return home_url( '/dashboard/' );
        }
    }
    return $redirect_to;
}, 10, 3 );

// 6. Hide WordPress Admin Bar for subscribers
add_filter( 'show_admin_bar', function( $show ) {
    if ( is_user_logged_in() && ! current_user_can( 'edit_posts' ) ) {
        return false;
    }
    return $show;
} );

// 7. Redirect direct requests to wp-login.php (excluding logout/recovery actions) to custom login page
add_action( 'login_init', function() {
    global $pagenow;
    if ( 'wp-login.php' === $pagenow ) {
        // If it is a POST request (submitting login form or other actions), allow it to process
        if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
            return;
        }

        $action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
        if ( ! in_array( $action, array( 'logout', 'lostpassword', 'retrievepassword', 'resetpass', 'rp' ), true ) ) {
            wp_safe_redirect( home_url( '/login/' ) );
            exit;
        }
    }
} );

// 7. Global redirect for /pricing/ or /subscribe/ to PMPro levels page
add_action( 'template_redirect', function() {
    $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
    if ( preg_match( '#^/Ascendance/(pricing|subscribe)/?(\?.*)?$#i', $request_uri ) || preg_match( '#^/(pricing|subscribe)/?(\?.*)?$#i', $request_uri ) ) {
        $levels_url = function_exists( 'pmpro_url' ) ? pmpro_url( 'levels' ) : home_url( '/membership-levels/' );
        wp_safe_redirect( $levels_url );
        exit;
    }
} );

// 6. Hook into PMPro Account Page links section
add_action( 'pmpro_member_links_bottom', function() {
    $user_id = get_current_user_id();
    $customer_id = get_user_meta( $user_id, 'pmpro_stripe_customerid', true );
    
    // Stripe Billing link (if user has active Stripe Customer ID)
    if ( ! empty( $customer_id ) ) {
        $billing_portal_url = add_query_arg( 'portal', '1', pmpro_url( 'account' ) );
        echo '<li class="pmpro_list_item"><a href="' . esc_url( $billing_portal_url ) . '">' . esc_html__( 'Manage Billing (Stripe Portal)', 'ascendance' ) . '</a></li>';
    }
    
    // Preferences Center link
    $preferences_url = home_url( '/preferences/' );
    echo '<li class="pmpro_list_item"><a href="' . esc_url( $preferences_url ) . '">' . esc_html__( 'Customize Feed & Email Preferences', 'ascendance' ) . '</a></li>';
} );

/**
 * Fallback menu for footer Platform column when no menu is assigned.
 */
if ( ! function_exists( 'ascendance_default_footer_menu' ) ) {
	function ascendance_default_footer_menu() {
		$links = array(
			home_url( '/' )            => 'Home',
			home_url( '/about/' )      => 'About',
			home_url( '/services/' )   => 'Services',
			home_url( '/industries/' ) => 'Industries',
			home_url( '/faq/' )        => 'FAQ',
		);
		echo '<ul class="footer-links">';
		foreach ( $links as $url => $label ) {
			echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
		}
		echo '</ul>';
	}
}

/**
 * Fallback menu for footer Intelligence column when no menu is assigned.
 */
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

/**
 * Customize post limits for custom post type archives (brief, update, dossier).
 */
function ascendance_customize_archive_limits( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( is_post_type_archive( array( 'brief', 'update', 'dossier' ) ) || is_tax( array( 'topic', 'region', 'intelligence_tag' ) ) ) {
		$query->set( 'posts_per_page', 10 );
	}
}
add_action( 'pre_get_posts', 'ascendance_customize_archive_limits' );

/**
 * Retrieve all tiers assigned to a post (taxonomy 'tier' has priority, fallback to ACF field 'tier_access').
 *
 * @param int $post_id Post ID.
 * @param string $default Default tier slug if none found.
 * @return array Array of tier slugs.
 */
function ascendance_get_post_tiers( int $post_id, string $default = 'essential' ): array {
	$terms = get_the_terms( $post_id, 'tier' );
	if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
		return wp_list_pluck( $terms, 'slug' );
	}
	// Fallback to ACF field
	$acf_tier = get_field( 'tier_access', $post_id );
	if ( ! empty( $acf_tier ) ) {
		return array( $acf_tier );
	}
	return array( $default );
}

/**
 * Override PMPro profile action links to keep subscribers on the frontend Edit Profile page.
 */
function ascendance_pmpro_account_profile_action_links( $pmpro_profile_action_links ) {
	$edit_profile_url = home_url( '/edit-profile/' );
	$change_password_url = home_url( '/edit-profile/?view=change-password' );

	if ( isset( $pmpro_profile_action_links['edit-profile'] ) ) {
		$pmpro_profile_action_links['edit-profile'] = sprintf(
			'<a id="pmpro_actionlink-profile" href="%s">%s</a>',
			esc_url( $edit_profile_url ),
			esc_html__( 'Edit Profile', 'paid-memberships-pro' )
		);
	}

	if ( isset( $pmpro_profile_action_links['change-password'] ) ) {
		$pmpro_profile_action_links['change-password'] = sprintf(
			'<a id="pmpro_actionlink-change-password" href="%s">%s</a>',
			esc_url( $change_password_url ),
			esc_html__( 'Change Password', 'paid-memberships-pro' )
		);
	}

	if ( isset( $pmpro_profile_action_links['logout'] ) ) {
		$pmpro_profile_action_links['logout'] = sprintf(
			'<a id="pmpro_actionlink-logout" href="%s">%s</a>',
			esc_url( wp_logout_url( home_url( '/login/' ) ) ),
			esc_html__( 'Log Out', 'paid-memberships-pro' )
		);
	}

	return $pmpro_profile_action_links;
}
add_filter( 'pmpro_account_profile_action_links', 'ascendance_pmpro_account_profile_action_links' );

/**
 * AJAX handler — Intelligence Hub filter.
 * Accepts: intel-type, topic, region, page
 * Returns JSON: { html, pagination, total }
 */
function ascendance_intel_filter_handler() {
	check_ajax_referer( 'ascendance_intel_filter', 'nonce' );

	$type        = isset( $_POST['intel-type'] ) ? sanitize_text_field( $_POST['intel-type'] ) : 'all';
	$topic_slug  = isset( $_POST['topic'] )      ? sanitize_text_field( $_POST['topic'] )      : '';
	$region_slug = isset( $_POST['region'] )     ? sanitize_text_field( $_POST['region'] )     : '';
	$paged       = isset( $_POST['page'] )       ? absint( $_POST['page'] )                    : 1;

	$args = array(
		'post_type'      => ( $type !== 'all' ) ? array( $type ) : array( 'brief', 'update', 'dossier' ),
		'posts_per_page' => 12,
		'paged'          => $paged,
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'order'          => 'DESC',
	);

	$tax_query = array( 'relation' => 'AND' );

	if ( ! empty( $topic_slug ) ) {
		$tax_query[] = array(
			'taxonomy' => 'topic',
			'field'    => 'slug',
			'terms'    => $topic_slug,
		);
	}

	if ( ! empty( $region_slug ) ) {
		$tax_query[] = array(
			'taxonomy' => 'region',
			'field'    => 'slug',
			'terms'    => $region_slug,
		);
	}

	if ( count( $tax_query ) > 1 ) {
		$args['tax_query'] = $tax_query;
	}

	$query = new WP_Query( $args );

	ob_start();
	if ( $query->have_posts() ) :
		while ( $query->have_posts() ) :
			$query->the_post();
			get_template_part( 'template-parts/intelligence-card', null, array( 'post_id' => get_the_ID() ) );
		endwhile;
		wp_reset_postdata();
	else :
		echo '<div class="intel-no-results">';
		echo '<div class="intel-no-results-icon"><i class="fa-solid fa-filter-circle-xmark"></i></div>';
		echo '<h3 class="intel-no-results-title">' . esc_html__( 'No results found', 'ascendance' ) . '</h3>';
		echo '<p class="intel-no-results-body">' . esc_html__( 'No intelligence matches your current filter selection. Try adjusting the topic, region, or content type.', 'ascendance' ) . '</p>';
		echo '<button class="intel-no-results-reset" data-action="intel-reset" type="button"><i class="fa-solid fa-rotate-left"></i> ' . esc_html__( 'Clear Filters', 'ascendance' ) . '</button>';
		echo '</div>';
	endif;
	$cards_html = ob_get_clean();

	$pagination_html = '';
	if ( $query->max_num_pages > 1 ) {
		$pagination_html = paginate_links( array(
			'current'   => $paged,
			'total'     => $query->max_num_pages,
			'prev_text' => '<i class="fa-solid fa-arrow-left"></i>',
			'next_text' => '<i class="fa-solid fa-arrow-right"></i>',
			'type'      => 'plain',
		) );
	}

	wp_send_json_success( array(
		'html'       => $cards_html,
		'pagination' => $pagination_html,
		'total'      => $query->found_posts,
		'pages'      => $query->max_num_pages,
	) );
}
add_action( 'wp_ajax_ascendance_intel_filter',        'ascendance_intel_filter_handler' );
add_action( 'wp_ajax_nopriv_ascendance_intel_filter', 'ascendance_intel_filter_handler' );

/**
 * Inject custom CSS classes (menu-item-has-mega-menu, group) to dynamic menu items.
 */
function ascendance_nav_menu_classes( $classes, $item, $args, $depth ) {
	if ( 'menu-primary' === $args->theme_location && 0 === $depth ) {
		$title = strtolower( $item->title );
		$url = strtolower( $item->url );
		$item_classes = (array) $item->classes;

		$is_intel = ( 'intelligence' === $title || strpos( $url, '/intelligence' ) !== false || in_array( 'mega-menu-intel', $item_classes, true ) );
		$is_sectors = ( 'sectors' === $title || 'industries' === $title || strpos( $url, '/industries' ) !== false || strpos( $url, '/sectors' ) !== false || in_array( 'mega-menu-sectors', $item_classes, true ) );
		$is_membership = ( 'membership' === $title || 'services' === $title || strpos( $url, '/membership' ) !== false || strpos( $url, '/services' ) !== false || in_array( 'mega-menu-services', $item_classes, true ) );

		if ( $is_intel || $is_sectors || $is_membership ) {
			$classes[] = 'menu-item-has-mega-menu';
			$classes[] = 'group';
		}
	}
	return $classes;
}
add_filter( 'nav_menu_css_class', 'ascendance_nav_menu_classes', 10, 4 );

/**
 * Inject the mega menu panel HTML inside the menu item link markup.
 */
function ascendance_nav_menu_start_el( $item_output, $item, $depth, $args ) {
	if ( 'menu-primary' === $args->theme_location && 0 === $depth ) {
		$title = strtolower( $item->title );
		$url = strtolower( $item->url );
		$item_classes = (array) $item->classes;

		$is_intel = ( 'intelligence' === $title || strpos( $url, '/intelligence' ) !== false || in_array( 'mega-menu-intel', $item_classes, true ) );
		$is_sectors = ( 'sectors' === $title || 'industries' === $title || strpos( $url, '/industries' ) !== false || strpos( $url, '/sectors' ) !== false || in_array( 'mega-menu-sectors', $item_classes, true ) );
		$is_membership = ( 'membership' === $title || 'services' === $title || strpos( $url, '/membership' ) !== false || strpos( $url, '/services' ) !== false || in_array( 'mega-menu-services', $item_classes, true ) );

		if ( $is_intel ) {
			global $ascendance_intelligence_mega_menu;
			if ( ! empty( $ascendance_intelligence_mega_menu ) ) {
				$item_output .= $ascendance_intelligence_mega_menu;
			}
		} elseif ( $is_sectors ) {
			global $ascendance_industries_mega_menu;
			if ( ! empty( $ascendance_industries_mega_menu ) ) {
				$item_output .= $ascendance_industries_mega_menu;
			}
		} elseif ( $is_membership ) {
			global $ascendance_services_mega_menu;
			if ( ! empty( $ascendance_services_mega_menu ) ) {
				$item_output .= $ascendance_services_mega_menu;
			}
		}
	}
	return $item_output;
}
add_filter( 'walker_nav_menu_start_el', 'ascendance_nav_menu_start_el', 10, 4 );

/**
 * Helper to build mega menu HTML dynamically from WordPress nested sub-menu items.
 */
function ascendance_build_dynamic_mega_menu( $parent_item, $menu_items ) {
	// 1. Find all Depth 1 children (Columns)
	$columns = array();
	foreach ( $menu_items as $item ) {
		if ( (int) $item->menu_item_parent === (int) $parent_item->db_id ) {
			$columns[ $item->db_id ] = array(
				'title' => $item->title,
				'items' => array(),
			);
		}
	}

	// 2. Find all Depth 2 children (Links) for each column
	foreach ( $menu_items as $item ) {
		$parent_id = (int) $item->menu_item_parent;
		if ( isset( $columns[ $parent_id ] ) ) {
			// Extract FontAwesome classes
			$classes = (array) $item->classes;
			$fa_classes = array();
			foreach ( $classes as $class ) {
				if ( strpos( $class, 'fa' ) === 0 ) {
					$fa_classes[] = $class;
				}
			}
			$icon_class = ! empty( $fa_classes ) ? implode( ' ', $fa_classes ) : 'fa-regular fa-file-lines';

			// Fallback icon based on title if no fa- classes found
			if ( empty( $fa_classes ) ) {
				$title_lower = strtolower( $item->title );
				if ( strpos( $title_lower, 'asia' ) !== false ) {
					$icon_class = 'fa-solid fa-earth-asia';
				} elseif ( strpos( $title_lower, 'africa' ) !== false || strpos( $title_lower, 'middle' ) !== false ) {
					$icon_class = 'fa-solid fa-earth-africa';
				} elseif ( strpos( $title_lower, 'europe' ) !== false ) {
					$icon_class = 'fa-solid fa-landmark';
				} elseif ( strpos( $title_lower, 'america' ) !== false ) {
					$icon_class = 'fa-solid fa-earth-americas';
				} elseif ( strpos( $title_lower, 'global' ) !== false || strpos( $title_lower, 'eurasia' ) !== false ) {
					$icon_class = 'fa-solid fa-flag';
				} elseif ( strpos( $title_lower, 'geopolitics' ) !== false || strpos( $title_lower, 'policy' ) !== false ) {
					$icon_class = 'fa-solid fa-shield-halved';
				} elseif ( strpos( $title_lower, 'economics' ) !== false || strpos( $title_lower, 'finance' ) !== false ) {
					$icon_class = 'fa-solid fa-circle-dollar-to-slot';
				} elseif ( strpos( $title_lower, 'tech' ) !== false || strpos( $title_lower, 'cyber' ) !== false ) {
					$icon_class = 'fa-solid fa-microchip';
				} elseif ( strpos( $title_lower, 'resource' ) !== false || strpos( $title_lower, 'energy' ) !== false ) {
					$icon_class = 'fa-solid fa-bolt';
				} elseif ( strpos( $title_lower, 'defense' ) !== false || strpos( $title_lower, 'military' ) !== false ) {
					$icon_class = 'fa-solid fa-award';
				}
			}

			$columns[ $parent_id ]['items'][] = array(
				'title' => $item->title,
				'url'   => $item->url,
				'icon'  => $icon_class,
			);
		}
	}

	// 3. Render HTML
	ob_start();
	?>
	<div class="mega-menu-panel hidden md:block">
		<div class="px-6 md:px-8 py-8 grid grid-cols-12 gap-8 text-left">
			<?php
			$col_count = 0;
			foreach ( $columns as $col ) :
				$col_count++;
				if ( $col_count > 3 ) { break; } // limit to 3 columns
				?>
				<div class="col-span-3 border-r border-brand-divider-light dark:border-brand-divider-dark/20 pr-6">
					<h4 class="font-sans text-[10px] font-bold uppercase tracking-wider text-brand-text-muted dark:text-cream/50 mb-4"><?php echo esc_html( $col['title'] ); ?></h4>
					<ul class="flex flex-col gap-2 list-none p-0 m-0">
						<?php foreach ( $col['items'] as $sub_item ) : ?>
							<li>
								<a href="<?php echo esc_url( $sub_item['url'] ); ?>" class="mega-menu-list-link flex items-center gap-3 py-1.5 px-1 rounded-sm text-xs font-sans transition-colors duration-150">
									<i class="<?php echo esc_attr( $sub_item['icon'] ); ?> text-[13px] w-4 flex-shrink-0 text-center"></i>
									<span><?php echo esc_html( $sub_item['title'] ); ?></span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php
			endforeach;

			// Fill up to 3 columns to maintain 12-column grid
			for ( $i = $col_count; $i < 3; $i++ ) :
				?>
				<div class="col-span-3 border-r border-brand-divider-light dark:border-brand-divider-dark/20 pr-6"></div>
				<?php
			endfor;
			?>

			<!-- Col 4: Latest News (3 cols) - Fully Dynamic -->
			<div class="col-span-3">
				<h4 class="font-sans text-[10px] font-bold uppercase tracking-wider text-brand-text-muted dark:text-cream/50 mb-4"><?php esc_html_e( 'Latest Intelligence', 'ascendance' ); ?></h4>
				<div class="mega-menu-latest flex flex-col gap-2">
					<?php
					$latest_posts = get_posts( array(
						'post_type'      => array( 'brief', 'update', 'dossier' ),
						'posts_per_page' => 3,
						'post_status'    => 'publish',
					) );

					if ( ! empty( $latest_posts ) ) {
						foreach ( $latest_posts as $lp ) {
							$date = strtoupper( get_the_date( 'M d, Y', $lp->ID ) );
							$title = get_the_title( $lp->ID );
							$link = get_permalink( $lp->ID );
							?>
							<a href="<?php echo esc_url( $link ); ?>" class="mega-menu-intel-card block p-3 rounded-sm transition-all duration-150">
								<div class="mega-menu-intel-card-date text-[9px] font-mono uppercase tracking-wider mb-1">
									<?php echo esc_html( $date ); ?>
								</div>
								<h5 class="mega-menu-intel-card-title text-xs font-sans font-bold leading-snug m-0 transition-colors line-clamp-2">
									<?php echo esc_html( $title ); ?>
								</h5>
							</a>
							<?php
						}
					} else {
						// Fallback to static mockup posts
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
					}
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Filter menu objects to parse and construct dynamic mega menus, removing child items from standard tree.
 */
function ascendance_parse_mega_menu_subitems( $menu_items, $args ) {
	if ( 'menu-primary' !== $args->theme_location ) {
		return $menu_items;
	}

	global $ascendance_intelligence_mega_menu, $ascendance_industries_mega_menu, $ascendance_services_mega_menu;

	// 1. Locate the mega menu parent items
	$intel_parent = null;
	$sectors_parent = null;
	$membership_parent = null;

	foreach ( $menu_items as $item ) {
		if ( (int) $item->menu_item_parent === 0 ) {
			$title = strtolower( $item->title );
			$url = strtolower( $item->url );
			$item_classes = (array) $item->classes;

			if ( 'intelligence' === $title || strpos( $url, '/intelligence' ) !== false || in_array( 'mega-menu-intel', $item_classes, true ) ) {
				$intel_parent = $item;
			} elseif ( 'sectors' === $title || 'industries' === $title || strpos( $url, '/industries' ) !== false || strpos( $url, '/sectors' ) !== false || in_array( 'mega-menu-sectors', $item_classes, true ) ) {
				$sectors_parent = $item;
			} elseif ( 'membership' === $title || 'services' === $title || strpos( $url, '/membership' ) !== false || strpos( $url, '/services' ) !== false || in_array( 'mega-menu-services', $item_classes, true ) ) {
				$membership_parent = $item;
			}
		}
	}

	// 2. Identify if child items exist for these parents
	$intel_child_ids = array();
	$sectors_child_ids = array();
	$membership_child_ids = array();

	// Pass 1: Find direct children (Depth 1)
	foreach ( $menu_items as $item ) {
		$parent_id = (int) $item->menu_item_parent;
		if ( $intel_parent && $parent_id === (int) $intel_parent->db_id ) {
			$intel_child_ids[] = $item->db_id;
		} elseif ( $sectors_parent && $parent_id === (int) $sectors_parent->db_id ) {
			$sectors_child_ids[] = $item->db_id;
		} elseif ( $membership_parent && $parent_id === (int) $membership_parent->db_id ) {
			$membership_child_ids[] = $item->db_id;
		}
	}

	// Pass 2: Find children of children (Depth 2)
	$intel_grandchild_ids = array();
	$sectors_grandchild_ids = array();
	$membership_grandchild_ids = array();

	foreach ( $menu_items as $item ) {
		$parent_id = (int) $item->menu_item_parent;
		if ( in_array( $parent_id, $intel_child_ids, true ) ) {
			$intel_grandchild_ids[] = $item->db_id;
		} elseif ( in_array( $parent_id, $sectors_child_ids, true ) ) {
			$sectors_grandchild_ids[] = $item->db_id;
		} elseif ( in_array( $parent_id, $membership_child_ids, true ) ) {
			$membership_grandchild_ids[] = $item->db_id;
		}
	}

	// 3. Build dynamic mega menus if children exist, and filter them out of standard rendering list
	$items_to_remove = array();

	if ( ! empty( $intel_child_ids ) ) {
		$ascendance_intelligence_mega_menu = ascendance_build_dynamic_mega_menu( $intel_parent, $menu_items );
		$items_to_remove = array_merge( $items_to_remove, $intel_child_ids, $intel_grandchild_ids );
	}

	if ( ! empty( $sectors_child_ids ) ) {
		$ascendance_industries_mega_menu = ascendance_build_dynamic_mega_menu( $sectors_parent, $menu_items );
		$items_to_remove = array_merge( $items_to_remove, $sectors_child_ids, $sectors_grandchild_ids );
	}

	// Filter out children from the list so the walker only renders top level items
	if ( ! empty( $items_to_remove ) ) {
		$filtered_items = array();
		foreach ( $menu_items as $item ) {
			if ( ! in_array( $item->db_id, $items_to_remove, true ) ) {
				$filtered_items[] = $item;
			}
		}
		return $filtered_items;
	}

	return $menu_items;
}
add_filter( 'wp_nav_menu_objects', 'ascendance_parse_mega_menu_subitems', 10, 2 );

/**
 * Register customizer settings for light and dark mode logos.
 */
function ascendance_customize_register( $wp_customize ) {
	// Add setting for Dark Mode Logo
	$wp_customize->add_setting( 'dark_mode_logo', array(
		'sanitize_callback' => 'absint',
	) );

	// Add media upload control for Dark Mode Logo
	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'dark_mode_logo', array(
		'label'     => __( 'Dark Mode Logo', 'ascendance' ),
		'section'   => 'title_tagline',
		'mime_type' => 'image',
		'priority'  => 10,
	) ) );
}
add_action( 'customize_register', 'ascendance_customize_register' );



