<?php
/**
 * Login Security — Hidden Login URL
 *
 * Replicates WPS Hide Login behaviour natively inside Ascendance Core.
 * Reads WP_LOGIN_SLUG from the environment (defaults to "portal") and:
 *   - Serves wp-login.php at /portal/ (or the configured slug) by intercepting
 *     the request early in the `init` hook and requiring wp-login.php directly.
 *   - Returns 404 on any direct request to /wp-login.php by unauthenticated visitors.
 *   - Returns 404 when unauthenticated users reach /wp-admin/*.
 *   - Rewrites all internal WordPress login / registration / lost-password URLs.
 *
 * @package Ascendance\Core
 */

namespace Ascendance\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Login_Security
 */
class Login_Security {

    /** @var Login_Security|null Singleton instance */
    private static ?Login_Security $instance = null;

    /** @var string The custom login slug (e.g. "portal") */
    private string $slug;

    /**
     * Returns the singleton instance.
     *
     * @return Login_Security
     */
    public static function get_instance(): Login_Security {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor — reads slug from env, registers all hooks.
     */
    private function __construct() {
        // Read slug from env; fall back to 'portal'.
        $slug = defined( 'WP_LOGIN_SLUG' ) ? WP_LOGIN_SLUG : ( getenv( 'WP_LOGIN_SLUG' ) ?: 'portal' );
        $this->slug = trim( sanitize_title( $slug ), '/' );

        // Priority 0 — must fire before anything else in init.
        // This is the core intercept: serves wp-login.php at the custom slug.
        add_action( 'init', [ $this, 'intercept_slug_request' ], 0 );

        // Intercept direct access to wp-login.php.
        add_action( 'login_init', [ $this, 'maybe_block_login_page' ] );

        // Block unauthenticated requests to /wp-admin/*.
        add_action( 'init', [ $this, 'maybe_block_admin' ] );

        // Rewrite all URLs WordPress generates for login / logout / registration.
        add_filter( 'login_url',        [ $this, 'filter_login_url'        ], 10, 3 );
        add_filter( 'logout_url',       [ $this, 'filter_logout_url'       ], 10, 2 );
        add_filter( 'lostpassword_url', [ $this, 'filter_lostpassword_url' ], 10, 2 );
        add_filter( 'register_url',     [ $this, 'filter_register_url'     ] );
        add_filter( 'site_url',         [ $this, 'filter_site_url'         ], 10, 4 );
        add_filter( 'network_site_url', [ $this, 'filter_network_site_url' ], 10, 3 );
        add_filter( 'wp_redirect',      [ $this, 'filter_wp_redirect'      ], 10, 2 );

        // Redirect to custom slug after logout.
        add_action( 'wp_logout', [ $this, 'redirect_after_logout' ] );
    }

    // -------------------------------------------------------------------------
    // Core request interception — serves the login form at the custom slug
    // -------------------------------------------------------------------------

    /**
     * Fires at init priority 0.
     * Checks whether the current request URI matches the custom slug; if so,
     * sets the global $pagenow and requires wp-login.php directly so WordPress
     * renders the full login page (including all login_form hooks, login scripts, etc.)
     *
     * This is the approach used by WPS Hide Login — intercepting the raw URI
     * is the only reliable way to serve wp-login.php at an arbitrary path.
     *
     * @return void
     */
    public function intercept_slug_request(): void {
        // Build the expected URI path for our custom slug.
        $home_path  = untrailingslashit( parse_url( home_url(), PHP_URL_PATH ) ?? '' );
        $slug_path  = $home_path . '/' . $this->slug;

        // Get the current request path (without query string).
        $request_path = untrailingslashit(
            parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ) ?? '/'
        );

        // Only intercept when the path matches exactly.
        if ( $request_path !== $slug_path ) {
            return;
        }

        // Tell WordPress core we are on the login page.
        global $pagenow;
        $pagenow = 'wp-login.php'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

        // Set the COOKIE_DOMAIN / SITECOOKIEPATH if not yet set (wp-login.php needs these).
        if ( ! defined( 'COOKIEPATH' ) ) {
            define( 'COOKIEPATH', preg_replace( '|https?://[^/]+|i', '', get_option( 'siteurl' ) . '/' ) );
        }
        if ( ! defined( 'SITECOOKIEPATH' ) ) {
            define( 'SITECOOKIEPATH', preg_replace( '|https?://[^/]+|i', '', get_option( 'siteurl' ) . '/' ) );
        }
        if ( ! defined( 'COOKIE_DOMAIN' ) ) {
            define( 'COOKIE_DOMAIN', '' );
        }
        if ( ! defined( 'ADMIN_COOKIE_PATH' ) ) {
            define( 'ADMIN_COOKIE_PATH', SITECOOKIEPATH . 'wp-admin' );
        }

        // Require wp-login.php which handles the full login/registration/reset flow.
        // Output buffering is not needed — wp-login.php exits itself after output.
        require_once ABSPATH . 'wp-login.php';
        exit;
    }

    // -------------------------------------------------------------------------
    // Request blocking
    // -------------------------------------------------------------------------

    /**
     * Blocks direct access to wp-login.php for visitors that did NOT arrive
     * via the custom slug. Logged-in admins, WP-Cron, and CLI are allowed through.
     *
     * @return void
     */
    public function maybe_block_login_page(): void {
        // Always allow WP-Cron and CLI.
        if ( ( defined( 'DOING_CRON' ) && DOING_CRON ) || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
            return;
        }

        // Allow password-reset / 2FA links (they contain an action + key).
        $action = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : '';
        if ( in_array( $action, [ 'rp', 'resetpass', 'confirmaction', 'validate_2fa' ], true ) ) {
            if ( ! empty( $_GET['key'] ) ) {
                return;
            }
        }

        // Allow the logout action (user clicked "Log Out" — nonce embedded in URL).
        if ( 'logout' === $action && ! empty( $_GET['_wpnonce'] ) ) {
            return;
        }

        // Allow internal POST submissions that originate from any page on this site.
        // This covers both the /portal/ hidden login page AND any page embedding
        // wp_login_form() (e.g. the custom /login/ template at page-login.php).
        if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
            $referer   = wp_get_raw_referer();
            $site_url  = trailingslashit( home_url() );
            if ( $referer && str_starts_with( $referer, $site_url ) ) {
                return;
            }
        }

        // All other direct visits to wp-login.php → 404.
        $this->send_404();
    }

    /**
     * Blocks unauthenticated direct requests to /wp-admin/* (except admin-ajax.php).
     *
     * @return void
     */
    public function maybe_block_admin(): void {
        if ( ! is_admin() ) {
            return;
        }

        // Allow admin-ajax.php (used by front-end plugins).
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            return;
        }

        // Allow logged-in users.
        if ( is_user_logged_in() ) {
            return;
        }

        // Allow CLI and cron.
        if ( ( defined( 'DOING_CRON' ) && DOING_CRON ) || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
            return;
        }

        // Unauthenticated /wp-admin/ → 404.
        $this->send_404();
    }

    // -------------------------------------------------------------------------
    // URL filters
    // -------------------------------------------------------------------------

    /**
     * Replaces wp-login.php in login URLs with the custom slug.
     *
     * @param string $login_url    The generated login URL.
     * @param string $redirect     Redirect destination after login.
     * @param bool   $force_reauth Whether to force reauthorisation.
     * @return string
     */
    public function filter_login_url( string $login_url, string $redirect, bool $force_reauth ): string {
        return $this->swap_login_url( $login_url, $redirect ? [ 'redirect_to' => $redirect ] : [] );
    }

    /**
     * Replaces wp-login.php in logout URLs with the custom slug.
     *
     * @param string $logout_url The generated logout URL.
     * @param string $redirect   Redirect destination after logout.
     * @return string
     */
    public function filter_logout_url( string $logout_url, string $redirect ): string {
        return $this->swap_login_url( $logout_url );
    }

    /**
     * Replaces wp-login.php in lost-password URLs.
     *
     * @param string $lostpassword_url The generated URL.
     * @param string $redirect         Redirect after reset.
     * @return string
     */
    public function filter_lostpassword_url( string $lostpassword_url, string $redirect ): string {
        return $this->swap_login_url( $lostpassword_url );
    }

    /**
     * Replaces wp-login.php in registration URLs.
     *
     * @param string $register_url The generated URL.
     * @return string
     */
    public function filter_register_url( string $register_url ): string {
        return $this->swap_login_url( $register_url );
    }

    /**
     * Intercepts site_url() calls that reference wp-login.php.
     *
     * @param string      $url     Generated URL.
     * @param string      $path    Requested path.
     * @param string|null $scheme  URL scheme.
     * @param int|null    $blog_id Blog ID.
     * @return string
     */
    public function filter_site_url( string $url, string $path, ?string $scheme, ?int $blog_id ): string {
        if ( str_contains( $path, 'wp-login.php' ) ) {
            return $this->swap_login_url( $url );
        }
        return $url;
    }

    /**
     * Intercepts network_site_url() calls that reference wp-login.php.
     *
     * @param string      $url    Generated URL.
     * @param string      $path   Requested path.
     * @param string|null $scheme URL scheme.
     * @return string
     */
    public function filter_network_site_url( string $url, string $path, ?string $scheme ): string {
        if ( str_contains( $path, 'wp-login.php' ) ) {
            return $this->swap_login_url( $url );
        }
        return $url;
    }

    /**
     * Prevents wp_redirect() from ever sending the user to wp-login.php directly.
     *
     * @param string $location Destination URL.
     * @param int    $status   HTTP status code.
     * @return string
     */
    public function filter_wp_redirect( string $location, int $status ): string {
        if ( str_contains( $location, 'wp-login.php' ) ) {
            return $this->swap_login_url( $location );
        }
        return $location;
    }

    /**
     * After logout, redirect to the custom login slug (not wp-login.php).
     *
     * @return void
     */
    public function redirect_after_logout(): void {
        wp_safe_redirect( home_url( $this->slug ) );
        exit;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Swaps wp-login.php in a URL for the custom slug, preserving query args.
     *
     * @param string $url        The original URL containing wp-login.php.
     * @param array  $extra_args Additional query args to add.
     * @return string
     */
    private function swap_login_url( string $url, array $extra_args = [] ): string {
        $parsed   = wp_parse_url( $url );
        $new_path = trailingslashit( home_url( $this->slug ) );

        $args = [];
        if ( ! empty( $parsed['query'] ) ) {
            parse_str( $parsed['query'], $args );
        }
        $args = array_merge( $args, $extra_args );

        // Strip 'action=login' — that's the default, no need to carry it.
        if ( isset( $args['action'] ) && 'login' === $args['action'] ) {
            unset( $args['action'] );
        }

        if ( ! empty( $args ) ) {
            $new_path = add_query_arg( $args, $new_path );
        }

        return $new_path;
    }

    /**
     * Sends a 404 response and terminates execution.
     *
     * @return never
     */
    private function send_404(): never {
        global $wp_query;
        if ( isset( $wp_query ) ) {
            $wp_query->set_404();
            status_header( 404 );
            nocache_headers();
            include get_query_template( '404' );
            die();
        }
        header( 'HTTP/1.1 404 Not Found' );
        die( '404 Not Found' );
    }

    /**
     * Returns the current custom login slug.
     *
     * @return string
     */
    public function get_slug(): string {
        return $this->slug;
    }
}
