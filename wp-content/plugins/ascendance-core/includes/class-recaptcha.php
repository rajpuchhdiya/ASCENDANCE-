<?php
/**
 * Google reCAPTCHA v3 Integration
 *
 * Implements invisible, score-based bot protection on WordPress login,
 * registration, and lost-password forms using the Google reCAPTCHA v3 API.
 * No user interaction required — reCAPTCHA v3 runs silently in the background
 * and returns a score (0.0 = bot → 1.0 = human). Requests below the configured
 * threshold are rejected.
 *
 * Configuration (via .env):
 *   RECAPTCHA_SITE_KEY   — Public site key from Google reCAPTCHA Admin Console
 *   RECAPTCHA_SECRET_KEY — Secret key for server-side score verification
 *   RECAPTCHA_THRESHOLD  — Minimum score to accept (float, default: 0.5)
 *
 * Behaviour:
 *   - Skipped silently when keys are absent or WP_ENVIRONMENT_TYPE=local.
 *   - Fail-open: if Google's API is unreachable the request is allowed through.
 *   - The reCAPTCHA JS badge is shown on the login page per Google's ToS.
 *   - Action names sent to Google for score context:
 *       login, register, lostpassword
 *
 * @package Ascendance\Core
 */

namespace Ascendance\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class ReCaptcha
 */
class ReCaptcha {

    /** @var ReCaptcha|null Singleton instance */
    private static ?ReCaptcha $instance = null;

    /** @var string Google reCAPTCHA v3 public site key */
    private string $site_key;

    /** @var string Google reCAPTCHA v3 secret key */
    private string $secret_key;

    /** @var float Minimum acceptable score (0.0–1.0). Default: 0.5 */
    private float $threshold;

    /** @var bool Whether reCAPTCHA is currently active */
    private bool $active;

    /** @var string reCAPTCHA JS API endpoint */
    private const RECAPTCHA_JS = 'https://www.google.com/recaptcha/api.js';

    /** @var string reCAPTCHA server-side verification endpoint */
    private const VERIFY_URL   = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Returns the singleton instance.
     *
     * @return ReCaptcha
     */
    public static function get_instance(): ReCaptcha {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor — reads keys from env, registers all hooks.
     */
    private function __construct() {
        $this->site_key   = defined( 'RECAPTCHA_SITE_KEY' )   ? RECAPTCHA_SITE_KEY   : ( getenv( 'RECAPTCHA_SITE_KEY' )   ?: '' );
        $this->secret_key = defined( 'RECAPTCHA_SECRET_KEY' ) ? RECAPTCHA_SECRET_KEY : ( getenv( 'RECAPTCHA_SECRET_KEY' ) ?: '' );

        $raw_threshold    = defined( 'RECAPTCHA_THRESHOLD' ) ? RECAPTCHA_THRESHOLD : ( getenv( 'RECAPTCHA_THRESHOLD' ) ?: '0.5' );
        $this->threshold  = (float) $raw_threshold;

        // Clamp threshold to valid range.
        $this->threshold  = max( 0.0, min( 1.0, $this->threshold ) );

        // Determine activation: must have both keys configured.
        // reCAPTCHA v3 works on localhost too (Google whitelists it by default).
        $this->active = ( ! empty( $this->site_key ) && ! empty( $this->secret_key ) );

        if ( ! $this->active ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log(
                    '[Ascendance/ReCaptcha] Inactive — RECAPTCHA_SITE_KEY or RECAPTCHA_SECRET_KEY not configured in .env.'
                );
            }
            return;
        }

        // ── JS / Token injection ──────────────────────────────────────────────
        add_action( 'login_enqueue_scripts', [ $this, 'enqueue_recaptcha_js' ] );
        add_action( 'login_form',            [ $this, 'inject_token_field_login' ] );
        add_action( 'register_form',         [ $this, 'inject_token_field_register' ] );
        add_action( 'lostpassword_form',     [ $this, 'inject_token_field_lostpassword' ] );

        // ── Server-side verification ──────────────────────────────────────────
        add_filter( 'authenticate',          [ $this, 'verify_on_login'        ], 30, 3 );
        add_filter( 'registration_errors',   [ $this, 'verify_on_registration' ], 30, 3 );
        add_action( 'lostpassword_post',     [ $this, 'verify_on_lostpassword' ], 30, 1 );
    }

    // -------------------------------------------------------------------------
    // JS & hidden field injection
    // -------------------------------------------------------------------------

    /**
     * Enqueues the reCAPTCHA v3 JS library and a small inline bootstrap script
     * that automatically binds to login form submit events.
     *
     * @return void
     */
    public function enqueue_recaptcha_js(): void {
        wp_enqueue_script(
            'google-recaptcha-v3',
            self::RECAPTCHA_JS . '?render=' . esc_attr( $this->site_key ),
            [],
            null,   // version managed by Google CDN
            true    // load in footer
        );

        // Inline script: execute reCAPTCHA on form submit and inject the token
        // into every form that has a [data-recaptcha-action] attribute.
        $inline_js = sprintf(
            '
            document.addEventListener("DOMContentLoaded", function () {
                var forms = document.querySelectorAll("form[data-recaptcha-action]");
                forms.forEach(function (form) {
                    form.addEventListener("submit", function (e) {
                        var action = form.getAttribute("data-recaptcha-action");
                        var field  = form.querySelector("input[name=recaptcha_token]");
                        if (!field) return;
                        e.preventDefault();
                        grecaptcha.ready(function () {
                            grecaptcha.execute(%s, { action: action }).then(function (token) {
                                field.value = token;
                                form.submit();
                            });
                        });
                    });
                });
            });
            ',
            wp_json_encode( $this->site_key )
        );

        wp_add_inline_script( 'google-recaptcha-v3', $inline_js );

        // Minimal CSS — position the badge so it doesn't overlap WP login elements.
        wp_add_inline_style( 'login', '
            .grecaptcha-badge { bottom: 80px !important; z-index: 9999; }
        ' );
    }

    /**
     * Injects a hidden token field + sets the data-recaptcha-action on the login form.
     * Uses output buffering to wrap the form-open tag since WordPress doesn't expose it.
     *
     * reCAPTCHA v3 injects <script> tags which need the form attribute; we use JS to
     * add the attribute from the inline bootstrap above, but we emit the hidden field here.
     *
     * @return void
     */
    public function inject_token_field_login(): void {
        $this->emit_token_field( 'login' );
        $this->emit_form_attribute_script( 'loginform', 'login' );
    }

    /**
     * Injects the token field on the registration form.
     *
     * @return void
     */
    public function inject_token_field_register(): void {
        $this->emit_token_field( 'register' );
        $this->emit_form_attribute_script( 'registerform', 'register' );
    }

    /**
     * Injects the token field on the lost-password form.
     *
     * @return void
     */
    public function inject_token_field_lostpassword(): void {
        $this->emit_token_field( 'lostpassword' );
        $this->emit_form_attribute_script( 'lostpasswordform', 'lostpassword' );
    }

    /**
     * Outputs the hidden input that will receive the reCAPTCHA token.
     *
     * @param string $action The reCAPTCHA action name for this form.
     * @return void
     */
    private function emit_token_field( string $action ): void {
        echo '<input type="hidden" name="recaptcha_token" value="" />';
        echo '<input type="hidden" name="recaptcha_action" value="' . esc_attr( $action ) . '" />';
    }

    /**
     * Emits a tiny inline script that sets data-recaptcha-action on the named form element.
     *
     * @param string $form_id  The HTML `id` attribute of the form.
     * @param string $action   The reCAPTCHA action name.
     * @return void
     */
    private function emit_form_attribute_script( string $form_id, string $action ): void {
        printf(
            '<script>document.getElementById(%s) && document.getElementById(%s).setAttribute("data-recaptcha-action", %s);</script>',
            wp_json_encode( $form_id ),
            wp_json_encode( $form_id ),
            wp_json_encode( $action )
        );
    }

    // -------------------------------------------------------------------------
    // Server-side verification hooks
    // -------------------------------------------------------------------------

    /**
     * Verifies the reCAPTCHA score during login.
     * Hooked to `authenticate` filter at priority 30 (after credential checks).
     *
     * @param \WP_User|\WP_Error|null $user      The authenticated user or error.
     * @param string                  $username  Submitted username.
     * @param string                  $password  Submitted password.
     * @return \WP_User|\WP_Error|null
     */
    public function verify_on_login( \WP_User|\WP_Error|null $user, string $username, string $password ): \WP_User|\WP_Error|null {
        if ( ! isset( $_POST['recaptcha_token'] ) ) {
            return $user;
        }

        $error = $this->verify_token(
            sanitize_text_field( wp_unslash( $_POST['recaptcha_token'] ) ),
            'login'
        );

        return $error instanceof \WP_Error ? $error : $user;
    }

    /**
     * Verifies the reCAPTCHA score during user registration.
     * Hooked to `registration_errors` filter at priority 30.
     *
     * @param \WP_Error $errors               Existing registration errors.
     * @param string    $sanitized_user_login Proposed login name.
     * @param string    $user_email           Proposed email address.
     * @return \WP_Error
     */
    public function verify_on_registration( \WP_Error $errors, string $sanitized_user_login, string $user_email ): \WP_Error {
        if ( ! isset( $_POST['recaptcha_token'] ) ) {
            return $errors;
        }

        $result = $this->verify_token(
            sanitize_text_field( wp_unslash( $_POST['recaptcha_token'] ) ),
            'register'
        );

        if ( $result instanceof \WP_Error ) {
            $errors->add( $result->get_error_code(), $result->get_error_message() );
        }

        return $errors;
    }

    /**
     * Verifies the reCAPTCHA score during a lost-password request.
     * Hooked to `lostpassword_post` action at priority 30.
     *
     * @param \WP_Error $errors Errors collected by WP's lost-password handler.
     * @return void
     */
    public function verify_on_lostpassword( \WP_Error $errors ): void {
        if ( ! isset( $_POST['recaptcha_token'] ) ) {
            return;
        }

        $result = $this->verify_token(
            sanitize_text_field( wp_unslash( $_POST['recaptcha_token'] ) ),
            'lostpassword'
        );

        if ( $result instanceof \WP_Error ) {
            $errors->add( $result->get_error_code(), $result->get_error_message() );
        }
    }

    // -------------------------------------------------------------------------
    // Token verification
    // -------------------------------------------------------------------------

    /**
     * Sends the reCAPTCHA token to Google's siteverify endpoint and checks
     * the returned score against the configured threshold.
     *
     * @param string $token   The g-recaptcha-response / recaptcha_token submitted.
     * @param string $action  The expected reCAPTCHA action name for this form.
     * @return true|\WP_Error Returns true on success, WP_Error on failure.
     */
    private function verify_token( string $token, string $action ): true|\WP_Error {
        if ( empty( $token ) ) {
            return new \WP_Error(
                'recaptcha_missing',
                __( 'reCAPTCHA token is missing. Please reload the page and try again.', 'ascendance-core' )
            );
        }

        $response = wp_remote_post(
            self::VERIFY_URL,
            [
                'timeout' => 10,
                'body'    => [
                    'secret'   => $this->secret_key,
                    'response' => $token,
                    'remoteip' => $this->get_client_ip(),
                ],
            ]
        );

        // Fail-open: if Google's API is unreachable, allow the request through.
        if ( is_wp_error( $response ) ) {
            error_log( '[Ascendance/ReCaptcha] Verification request failed: ' . $response->get_error_message() );
            return true;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        // Check that verification succeeded.
        if ( empty( $body['success'] ) ) {
            $codes = implode( ', ', $body['error-codes'] ?? [] );
            error_log( "[Ascendance/ReCaptcha] Verification failed — error codes: {$codes}" );
            return new \WP_Error(
                'recaptcha_failed',
                __( 'Security check failed. Please reload the page and try again.', 'ascendance-core' )
            );
        }

        // Verify the action matches (prevents token reuse across forms).
        if ( isset( $body['action'] ) && $body['action'] !== $action ) {
            error_log( "[Ascendance/ReCaptcha] Action mismatch — expected: {$action}, got: {$body['action']}" );
            return new \WP_Error(
                'recaptcha_action_mismatch',
                __( 'Security check failed. Please reload the page and try again.', 'ascendance-core' )
            );
        }

        // Evaluate the score.
        $score = (float) ( $body['score'] ?? 0.0 );
        error_log( "[Ascendance/ReCaptcha] Score for action '{$action}': {$score} (threshold: {$this->threshold})" );

        if ( $score < $this->threshold ) {
            return new \WP_Error(
                'recaptcha_score_low',
                sprintf(
                    /* translators: %s: score value */
                    __( 'Our automated security check flagged this request (score: %s). Please try again.', 'ascendance-core' ),
                    number_format( $score, 2 )
                )
            );
        }

        return true;
    }

    // -------------------------------------------------------------------------
    // Utilities
    // -------------------------------------------------------------------------

    /**
     * Returns the visitor's IP address, respecting common proxy headers.
     *
     * @return string
     */
    private function get_client_ip(): string {
        $headers = [
            'HTTP_CF_CONNECTING_IP',  // Cloudflare
            'HTTP_X_REAL_IP',         // Nginx reverse proxy
            'HTTP_X_FORWARDED_FOR',   // Load balancers / CDNs
            'REMOTE_ADDR',            // Direct connection
        ];

        foreach ( $headers as $header ) {
            if ( ! empty( $_SERVER[ $header ] ) ) {
                $ip = trim( explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) ) )[0] );
                if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
                    return $ip;
                }
            }
        }

        return '';
    }

    /**
     * Returns whether reCAPTCHA is currently active.
     *
     * @return bool
     */
    public function is_active(): bool {
        return $this->active;
    }

    /**
     * Returns the configured score threshold.
     *
     * @return float
     */
    public function get_threshold(): float {
        return $this->threshold;
    }
}
