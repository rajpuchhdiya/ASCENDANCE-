<?php

namespace WPForms\Lite\SetupWizard;

use WPForms\SetupWizard\Bridge;
use WPForms\SetupWizard\Screen as BaseScreen;

/**
 * Setup Wizard launch screen (Lite).
 *
 * Renders the Welcome screen locally instead of handing off to the SPA
 * sight-unseen: the first screen must live on the WP site (#18533). The
 * consent is persisted through the same StateManager path the SPA's
 * `/update` uses, so the mirror into `wpforms_settings` (and the consent
 * timestamp stamping) stays identical. The handoff happens on the CTA via
 * AJAX, carrying the `first_screen_completed` flag the SPA uses to skip its
 * own welcome.
 *
 * @since 2.0.0.3
 */
class Screen extends BaseScreen {

	/**
	 * Nonce action for the first-screen AJAX endpoint.
	 *
	 * @since 2.0.0.3
	 *
	 * @var string
	 */
	private const NONCE_ACTION = 'wpforms_setup_wizard_screen';

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0.3
	 */
	public function hooks(): void {

		add_action( 'wp_ajax_wpforms_setup_wizard_screen_update', [ $this, 'handle_update' ] );
	}

	/**
	 * Render the local Welcome document instead of the bridge handoff.
	 *
	 * No SPA preflight here: the local screen always renders, and
	 * reachability is checked at CTA time by `handle_update()`, which surfaces
	 * an inline error instead of a redirect.
	 *
	 * @since 2.0.0.3
	 *
	 * @return bool Whether a real launch happened (as opposed to a fallback redirect).
	 */
	public function launch(): bool {

		// A mid-wizard re-entry (Stripe OAuth return) must resume in the SPA,
		// not show the first screen again.
		if ( $this->is_resume_request() ) {
			return parent::launch();
		}

		Bridge::send_standalone_document_headers();

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render(
			'admin/setup-wizard/shell',
			[
				'view'     => $this->get_view(),
				'config'   => $this->get_js_config(),
				'exit_url' => $this->get_exit_url(),
				'assets'   => $this->get_assets(),
			],
			true
		);

		return true;
	}

	/**
	 * `wp_ajax` handler: save the Lite Connect consent and return the handoff data.
	 *
	 * @since 2.0.0.3
	 */
	public function handle_update(): void {

		if ( check_ajax_referer( self::NONCE_ACTION, 'nonce', false ) === false || ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'You are not allowed to run the Setup Wizard.', 'wpforms-lite' ) ], 403 );
		}

		if ( ! $this->bridge->is_spa_reachable() ) {
			wp_send_json_error( [ 'message' => __( 'The Setup Wizard is temporarily unavailable. Please try again in a few minutes.', 'wpforms-lite' ) ], 503 );
		}

		$is_consent_given = (bool) absint( $_POST['consent'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$state           = $this->state_manager->get_state();
		$wizard_settings = (array) ( $state['wizard_settings'] ?? [] );

		$this->state_manager->save_wizard_settings(
			array_merge( $wizard_settings, [ 'lite-connect-enabled' => $is_consent_given ] )
		);

		wp_send_json_success(
			[
				'handoff' => $this->bridge->get_handoff_data(
					$this->get_exit_url(),
					$this->get_restart_url(),
					[ 'first_screen_completed' => 1 ]
				),
			]
		);
	}

	/**
	 * Render the Welcome view.
	 *
	 * @since 2.0.0.3
	 *
	 * @return string
	 */
	private function get_view(): string {

		return (string) wpforms_render(
			'admin/setup-wizard/welcome',
			[
				'exit_url' => $this->get_exit_url(),
			],
			true
		);
	}

	/**
	 * JS configuration printed into the shell as JSON.
	 *
	 * @since 2.0.0.3
	 *
	 * @return array
	 */
	private function get_js_config(): array {

		return [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( self::NONCE_ACTION ),
			'strings'  => [
				'generic_error' => __( 'Something went wrong. Please try again.', 'wpforms-lite' ),
			],
		];
	}

	/**
	 * Compiled asset URLs for the shell template.
	 *
	 * @since 2.0.0.3
	 *
	 * @return array{css: string[], js: string[]}
	 */
	private function get_assets(): array {

		$min = wpforms_get_min_suffix();

		return [
			'css' => [ WPFORMS_PLUGIN_URL . "assets/lite/css/admin/setup-wizard{$min}.css?ver=" . WPFORMS_VERSION ],
			'js'  => [ WPFORMS_PLUGIN_URL . "assets/lite/js/admin/setup-wizard{$min}.js?ver=" . WPFORMS_VERSION ],
		];
	}
}
