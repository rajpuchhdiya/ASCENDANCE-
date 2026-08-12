<?php
defined( 'ABSPATH' ) || exit;

/**
 * GateTouch Help & Support Floating Panel
 *
 * Injects a floating "?" button and slide-in support panel on all GateTouch admin pages.
 * Users can contact support, request services, and access documentation.
 */
class GateTouch_Support_Panel {

    public function __construct() {
        add_action( 'admin_footer',          [ $this, 'render_panel' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'wp_ajax_gatetouch_submit_support', [ $this, 'ajax_submit_support' ] );
    }

    public function enqueue_assets( $hook ) {
        if ( ! GateTouch_Helpers::is_gatetouch_page( $hook ) ) return;

        wp_enqueue_style(
            'gatetouch-support-panel',
            GATETOUCH_URL . 'assets/css/support-panel.css',
            [],
            GATETOUCH_VERSION
        );
        wp_enqueue_script(
            'gatetouch-support-panel',
            GATETOUCH_URL . 'assets/js/support-panel.js',
            [ 'jquery' ],
            GATETOUCH_VERSION,
            true
        );
        wp_localize_script( 'gatetouch-support-panel', 'gatetouchSupport', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'gatetouch_ajax' ),
            'site_url' => home_url(),
			'strings'  => [
				'sending'  => __( 'Sending...', 'gatetouch-ai-seo' ),
				'sent'     => __( 'Message sent! We\'ll reply within 24 hours.', 'gatetouch-ai-seo' ),
				'error'    => __( 'Could not send. Please email us directly.', 'gatetouch-ai-seo' ),
				'ticket'   => __( 'Ticket:', 'gatetouch-ai-seo' ),
				'recordId' => __( 'Record ID:', 'gatetouch-ai-seo' ),
			],
		] );
	}

    public function render_panel() {
        $screen = get_current_screen();
        if ( ! $screen ) return;
        if ( strpos( $screen->id, 'gatetouch' ) === false && ! in_array( $screen->base, [ 'post', 'page' ], true ) ) return;

        $name  = '';
        $email = get_option( 'admin_email', '' );
        ?>
        <!-- GT SEO/GEO/AEO Support Panel -->
        <div id="riq-support-panel-overlay" class="riq-sp-overlay"></div>

        <div id="riq-support-panel" class="riq-sp-panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'GT SEO/GEO/AEO Support', 'gatetouch-ai-seo' ); ?>">

            <div class="riq-sp-header">
                <div class="riq-sp-logo">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                </div>
                <div>
                    <div class="riq-sp-title"><?php esc_html_e( 'GT SEO/GEO/AEO Support', 'gatetouch-ai-seo' ); ?></div>
                    <div class="riq-sp-subtitle"><?php esc_html_e( 'We typically reply within 24 hours', 'gatetouch-ai-seo' ); ?></div>
                </div>
                <button id="riq-sp-close" class="riq-sp-close" aria-label="<?php esc_attr_e( 'Close support panel', 'gatetouch-ai-seo' ); ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <div class="riq-sp-body">

                <!-- Quick Links -->
                <div class="riq-sp-section">
                    <div class="riq-sp-section-title"><?php esc_html_e( 'Quick Actions', 'gatetouch-ai-seo' ); ?></div>
                    <div class="riq-sp-quick-links">
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=gatetouch-help' ) ); ?>" class="riq-sp-quick-link">
                            <span class="riq-sp-ql-icon">📖</span>
                            <span><?php esc_html_e( 'Documentation', 'gatetouch-ai-seo' ); ?></span>
                        </a>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=gatetouch-audit' ) ); ?>" class="riq-sp-quick-link">
                            <span class="riq-sp-ql-icon">🔍</span>
                            <span><?php esc_html_e( 'Run SEO Audit', 'gatetouch-ai-seo' ); ?></span>
                        </a>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=gatetouch-settings&tab=ai' ) ); ?>" class="riq-sp-quick-link">
                            <span class="riq-sp-ql-icon">🤖</span>
                            <span><?php esc_html_e( 'AI Settings', 'gatetouch-ai-seo' ); ?></span>
                        </a>
                        <a href="https://wordpress.org/support/plugin/gatetouch-ai-seo/" target="_blank" rel="noopener" class="riq-sp-quick-link">
                            <span class="riq-sp-ql-icon">💬</span>
                            <span><?php esc_html_e( 'Community Forum', 'gatetouch-ai-seo' ); ?></span>
                        </a>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="riq-sp-section">
                    <div class="riq-sp-section-title"><?php esc_html_e( 'Send a Message', 'gatetouch-ai-seo' ); ?></div>
                    <form id="riq-sp-form" class="riq-sp-form" novalidate>
                        <div class="riq-sp-field">
                            <input type="text" id="riq-sp-name" name="support_name"
                                   placeholder="<?php esc_attr_e( 'Your Name', 'gatetouch-ai-seo' ); ?>"
                                   value="<?php echo esc_attr( $name ); ?>"
                                   required />
                        </div>
                        <div class="riq-sp-field">
                            <input type="email" id="riq-sp-email" name="support_email"
                                   placeholder="<?php esc_attr_e( 'Your Email', 'gatetouch-ai-seo' ); ?>"
                                   value="<?php echo esc_attr( $email ); ?>"
                                   required />
                        </div>
                        <div class="riq-sp-field">
                            <select id="riq-sp-type" name="support_type">
                                <option value="support"><?php esc_html_e( 'Technical Support', 'gatetouch-ai-seo' ); ?></option>
                                <option value="seo_audit"><?php esc_html_e( 'Request SEO Audit', 'gatetouch-ai-seo' ); ?></option>
                                <option value="managed_seo"><?php esc_html_e( 'Managed SEO Inquiry', 'gatetouch-ai-seo' ); ?></option>
                                <option value="content"><?php esc_html_e( 'Content Writing Service', 'gatetouch-ai-seo' ); ?></option>
                                <option value="other"><?php esc_html_e( 'Other', 'gatetouch-ai-seo' ); ?></option>
                            </select>
                        </div>
                        <div class="riq-sp-field">
                            <textarea id="riq-sp-message" name="support_message" rows="4"
                                      placeholder="<?php esc_attr_e( 'Describe your issue or what you need help with...', 'gatetouch-ai-seo' ); ?>"
                                      required></textarea>
                        </div>
                        <div id="riq-sp-result" class="riq-sp-result" style="display:none;"></div>
                        <button type="submit" class="riq-sp-submit">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                            <?php esc_html_e( 'Send Message', 'gatetouch-ai-seo' ); ?>
                        </button>
                    </form>
                </div>

            </div><!-- /.riq-sp-body -->

            <div class="riq-sp-footer">
                <span><?php esc_html_e( 'GT SEO/GEO/AEO', 'gatetouch-ai-seo' ); ?> v<?php echo esc_html( GATETOUCH_VERSION ); ?></span>
            </div>
        </div>

        <!-- Floating Help Button -->
        <button id="riq-support-toggle" class="riq-sp-toggle" aria-label="<?php esc_attr_e( 'Open GT SEO/GEO/AEO Support', 'gatetouch-ai-seo' ); ?>" title="<?php esc_attr_e( 'Need help? Click to open GT SEO/GEO/AEO Support', 'gatetouch-ai-seo' ); ?>">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <span class="riq-sp-toggle-label"><?php esc_html_e( 'Help', 'gatetouch-ai-seo' ); ?></span>
        </button>
        <?php
    }

	public function ajax_submit_support() {
		check_ajax_referer( 'gatetouch_ajax', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );
		}

		$name    = sanitize_text_field( wp_unslash( $_POST['support_name']    ?? '' ) );
		$email   = sanitize_email( wp_unslash( $_POST['support_email']   ?? '' ) );
        $type    = sanitize_key( wp_unslash( $_POST['support_type']    ?? 'support' ) );
        $message = sanitize_textarea_field( wp_unslash( $_POST['support_message'] ?? '' ) );

        if ( ! $name || ! is_email( $email ) || ! $message ) {
            wp_send_json_error( __( 'Please fill in all required fields.', 'gatetouch-ai-seo' ) );
        }

        $type_labels = [
            'support'     => 'Technical Support',
            'seo_audit'   => 'SEO Audit Request',
            'managed_seo' => 'Managed SEO Inquiry',
            'content'     => 'Content Writing Service',
            'other'       => 'Other',
        ];

		$subject = '[GT SEO/GEO/AEO Support] ' . ( $type_labels[ $type ] ?? 'Support Request' ) . ' from ' . get_bloginfo( 'name' );

			$recipient = apply_filters( 'gatetouch_support_email', 'parbat@gatetouch.com' );
			$body      = sprintf(
				"Name: %s\nEmail: %s\nRequest type: %s\nSite: %s\nPlugin version: %s\nWordPress: %s\nPHP: %s\n\nMessage:\n%s",
				$name,
				$email,
				$type_labels[ $type ] ?? 'Support Request',
				home_url(),
				GATETOUCH_VERSION,
				get_bloginfo( 'version' ),
				PHP_VERSION,
				$message
			);
			$headers   = [
				'Reply-To: ' . $name . ' <' . $email . '>',
			];

			if ( ! wp_mail( $recipient, $subject, $body, $headers ) ) {
				wp_send_json_error( __( 'Could not send the support request. Please use the WordPress.org support forum.', 'gatetouch-ai-seo' ) );
			}

			wp_send_json_success( [
				'message' => __( 'Support request sent.', 'gatetouch-ai-seo' ),
			] );
		}
	}
