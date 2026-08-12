<?php

namespace WPForms\SetupWizard\Service;

/**
 * Setup Wizard install gateway.
 *
 * Resolves each SPA plugin file against `PluginCatalog` and hands it to
 * `wpforms_install_plugin()`, which downloads it from WordPress.org (or just
 * activates it when already present) and activates it. The outcome is
 * partitioned into installed/failed for the SPA.
 *
 * @since 2.0.0
 */
class PluginInstaller {

	/**
	 * Plugin catalog.
	 *
	 * @since 2.0.0
	 *
	 * @var PluginCatalog
	 */
	private $catalog;

	/**
	 * Constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$this->catalog = new PluginCatalog();
	}

	/**
	 * Install a list of plugin files and partition the outcome for the SPA.
	 *
	 * @since 2.0.0
	 *
	 * @param array $plugin_files Plugin files requested by the SPA.
	 *
	 * @return array {
	 *     Per-plugin install result keyed by the original SPA plugin file.
	 *
	 *     @type array $installed Plugin files installed (or already present) and activated.
	 *     @type array $failed    Plugin files that failed, plugin file => error message.
	 * }
	 */
	public function install( array $plugin_files ): array {

		$plugin_files = array_values( array_filter( array_map( 'wpforms_sanitize_key', $plugin_files ) ) );
		$installed    = [];
		$failed       = [];

		foreach ( $plugin_files as $plugin_file ) {
			$result = wpforms_install_plugin( $this->catalog->main_file( $plugin_file ) );

			if ( is_wp_error( $result ) ) {
				$failed[ $plugin_file ] = $result->get_error_message();

				continue;
			}

			$installed[] = $plugin_file;

			$this->suppress_activation_redirect( $plugin_file );
		}

		return [
			'installed' => $installed,
			'failed'    => $failed,
		];
	}

	/**
	 * Suppress a freshly activated cross-plugin's own "just activated" redirect.
	 *
	 * A handful of cross-plugins arm their own onboarding redirect on
	 * activation and check it on `admin_init` at a priority earlier than the
	 * wizard's own launch hook (`PHP_INT_MAX`), hijacking the request back to
	 * their own wizard instead of ours when the user navigates back to the
	 * Setup Wizard.
	 *
	 * @since 2.0.0.3
	 *
	 * @param string $plugin_file Plugin file just installed and activated.
	 */
	private function suppress_activation_redirect( string $plugin_file ): void {

		switch ( $plugin_file ) {
			case 'wp-mail-smtp/wp_mail_smtp.php':
				update_option( 'wp_mail_smtp_activation_prevent_redirect', true );
				break;

			case 'wpconsent-cookies-banner-privacy-suite/wpconsent.php':
				delete_transient( 'wpconsent_onboarding_redirect' );
				break;
		}
	}
}
