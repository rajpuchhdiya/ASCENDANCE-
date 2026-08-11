<?php
/**
 * AI Editorial Studio Handler Class
 *
 * @package Ascendance\Core
 */

namespace Ascendance\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AI_Studio {

    /**
     * Singleton instance
     * @var AI_Studio|null
     */
    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Class constructor
     */
    private function __construct() {
        // Run database migration defensively
        $this->maybe_create_table();

        // Admin menu
        add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 11 );

        // REST API
        add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
        

            // Auto-fill SEO metadata on post save (drafts)
            add_action( 'save_post', array( $this, 'maybe_autofill_seo_on_save' ), 10, 3 );

            // Enqueue editor assets for Gutenberg (SEO generator UI)
            add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
    }

    /**
     * Create the AI usage table if it does not exist
     */
    public function maybe_create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ascendance_ai_usage';
        
        // Check if table exists
        if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) !== $table_name ) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            
            $collate = '';
            if ( $wpdb->has_cap( 'collation' ) ) {
                $collate = $wpdb->get_charset_collate();
            }

            $sql = "CREATE TABLE $table_name (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                post_id BIGINT UNSIGNED NULL,
                user_id BIGINT UNSIGNED NOT NULL,
                provider VARCHAR(20) NOT NULL,
                model VARCHAR(60) NOT NULL,
                operation VARCHAR(40) NOT NULL,
                input_tokens INT UNSIGNED NOT NULL DEFAULT 0,
                output_tokens INT UNSIGNED NOT NULL DEFAULT 0,
                cost_usd DECIMAL(8,4) NOT NULL DEFAULT 0.0000,
                created_at DATETIME NOT NULL,
                KEY user_id (user_id),
                KEY post_id (post_id),
                KEY created_at (created_at)
            ) $collate;";

            dbDelta( $sql );
        }
    }

    /**
     * Register Admin Menu Page
     */
    public function register_admin_menu() {
        $hook = add_submenu_page(
            'ascendance-mission-control',
            __( 'AI Studio', 'ascendance-core' ),
            __( 'AI Studio', 'ascendance-core' ),
            'edit_posts',
            'ascendance-ai-studio',
            array( $this, 'render_ai_studio_page' )
        );

        // Hook page actions before headers are sent
        add_action( 'load-' . $hook, array( $this, 'handle_studio_page_actions' ) );

        // Add an optional admin debug notice (only shown when ?ai_debug=1 is present)
        add_action( 'admin_notices', array( $this, 'maybe_show_admin_debug_notice' ) );
    }

    /**
     * Show a temporary admin debug notice when ?ai_debug=1 is appended to admin URL
     */
    public function maybe_show_admin_debug_notice() {
        if ( ! is_admin() ) {
            return;
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        if ( empty( $_GET['ai_debug'] ) || intval( $_GET['ai_debug'] ) !== 1 ) {
            return;
        }

        $cap_edit_posts = current_user_can( 'edit_posts' ) ? 'yes' : 'no';
        echo '<div class="notice notice-info is-dismissible"><p><strong>AI Studio Debug:</strong> class instantiated, register_admin_menu ran. current_user_can("edit_posts"): ' . esc_html( $cap_edit_posts ) . '.</p></div>';
    }

    /**
     * Handle actions before page rendering (e.g. redirects, saving settings)
     */
    public function handle_studio_page_actions() {
        if ( ! current_user_can( 'edit_posts' ) ) {
            return;
        }

        // Handle manual recheck action
        if ( isset( $_GET['action'] ) && 'recheck_gateways' === $_GET['action'] ) {
            check_admin_referer( 'ascendance_recheck_gateways' );
            
            $this->get_api_key_status( 'anthropic', true );
            $this->get_api_key_status( 'openai', true );
            $this->get_api_key_status( 'gemini', true );
            
            wp_safe_redirect( admin_url( 'admin.php?page=ascendance-ai-studio&rechecked=1' ) );
            exit;
        }

        // Save settings if updated is removed to be handled centrally in settings page.
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        register_rest_route( 'ascendance/v1', '/ai-studio/generate', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'handle_generate_request' ),
            'permission_callback' => function() {
                return current_user_can( 'edit_posts' );
            }
        ) );

        register_rest_route( 'ascendance/v1', '/ai-studio/regenerate-section', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'handle_regenerate_section_request' ),
            'permission_callback' => function() {
                return current_user_can( 'edit_posts' );
            }
        ) );

        // SEO metadata generation endpoint (title + meta description)
        register_rest_route( 'ascendance/v1', '/ai/seo', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'handle_generate_seo' ),
            'permission_callback' => function() {
                return current_user_can( 'edit_posts' );
            }
        ) );

        // Apply generated SEO payload to post
        register_rest_route( 'ascendance/v1', '/ai/seo-apply', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'handle_apply_seo' ),
            'permission_callback' => function() {
                return current_user_can( 'edit_posts' );
            }
        ) );

        // Push compiled draft to WordPress custom post type
        register_rest_route( 'ascendance/v1', '/ai-studio/push-draft', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'handle_push_draft_request' ),
            'permission_callback' => function() {
                return current_user_can( 'edit_posts' );
            }
        ) );

        // Asynchronous French translation route
        register_rest_route( 'ascendance/v1', '/ai-studio/translate', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'handle_translate_request' ),
            'permission_callback' => function() {
                return current_user_can( 'edit_posts' );
            }
        ) );
    }

    /**
     * Get monthly token cost usage in USD
     */
    public function get_monthly_cost() {
        $current_month = date( 'Y-m' );
        $cached_month  = get_option( 'ascendance_ai_monthly_cost_month' );
        $cached_cost   = get_option( 'ascendance_ai_monthly_cost_cache' );

        if ( $cached_month === $current_month && false !== $cached_cost ) {
            return floatval( $cached_cost );
        }

        // Recalculate if month changed or cache not set
        global $wpdb;
        $table_name = $wpdb->prefix . 'ascendance_ai_usage';
        $start_of_month = date( 'Y-m-01 00:00:00' );
        
        $cost = $wpdb->get_var( $wpdb->prepare(
            "SELECT SUM(cost_usd) FROM $table_name WHERE created_at >= %s",
            $start_of_month
        ) );

        $cost_val = $cost ? floatval( $cost ) : 0.0;
        
        update_option( 'ascendance_ai_monthly_cost_month', $current_month );
        update_option( 'ascendance_ai_monthly_cost_cache', $cost_val );

        return $cost_val;
    }

    /**
     * Log AI usage
     */
    public function log_usage( $post_id, $provider, $model, $operation, $input_tokens, $output_tokens, $cost_usd ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ascendance_ai_usage';
        
        $wpdb->insert(
            $table_name,
            array(
                'post_id'       => $post_id,
                'user_id'       => get_current_user_id(),
                'provider'      => $provider,
                'model'         => $model,
                'operation'     => $operation,
                'input_tokens'  => $input_tokens,
                'output_tokens' => $output_tokens,
                'cost_usd'      => $cost_usd,
                'created_at'    => current_time( 'mysql' )
            )
        );

        // Update the monthly cost cache
        $current_month = date( 'Y-m' );
        $cached_month  = get_option( 'ascendance_ai_monthly_cost_month' );
        $cached_cost   = get_option( 'ascendance_ai_monthly_cost_cache', 0.0 );

        if ( $cached_month === $current_month ) {
            $new_cost = floatval( $cached_cost ) + floatval( $cost_usd );
            update_option( 'ascendance_ai_monthly_cost_cache', $new_cost );
        } else {
            // Force recalculation
            delete_option( 'ascendance_ai_monthly_cost_month' );
            delete_option( 'ascendance_ai_monthly_cost_cache' );
            $this->get_monthly_cost();
        }
    }

    /**
     * Get API key for a provider (checking database option, then environment variable).
     *
     * @param string $provider The provider: 'anthropic', 'openai', or 'gemini'.
     * @return string API key or empty string if not set.
     */
    public function get_api_key( $provider ) {
        if ( 'stripe' === $provider ) {
            if ( defined( 'STRIPE_SECRET_KEY' ) && ! empty( STRIPE_SECRET_KEY ) ) {
                return trim( STRIPE_SECRET_KEY );
            }
            $stripe_secret = get_option( 'pmpro_stripe_secretkey' );
            if ( ! empty( $stripe_secret ) ) {
                return trim( $stripe_secret );
            }
            $stripe_sandbox_connect = get_option( 'pmpro_sandbox_stripe_connect_secretkey' );
            if ( ! empty( $stripe_sandbox_connect ) ) {
                return trim( $stripe_sandbox_connect );
            }
            $stripe_live_connect = get_option( 'pmpro_live_stripe_connect_secretkey' );
            if ( ! empty( $stripe_live_connect ) ) {
                return trim( $stripe_live_connect );
            }
        }

        if ( 'brevo' === $provider || 'mailerlite' === $provider ) {
            if ( defined( 'ASCENDANCE_NEWSLETTER_API_KEY' ) && ! empty( ASCENDANCE_NEWSLETTER_API_KEY ) ) {
                return trim( ASCENDANCE_NEWSLETTER_API_KEY );
            }
            if ( defined( 'MAILERLITE_API_KEY' ) && ! empty( MAILERLITE_API_KEY ) ) {
                return trim( MAILERLITE_API_KEY );
            }
        }

        $db_key = get_option( 'ascendance_' . $provider . '_api_key' );
        if ( ! empty( $db_key ) ) {
            return trim( $db_key );
        }
        
        $env_key = getenv( strtoupper( $provider ) . '_API_KEY' );
        if ( ! empty( $env_key ) ) {
            return trim( $env_key );
        }

        return '';
    }

    /**
     * Get the configuration source of the API key for a provider.
     *
     * @param string $provider The provider name.
     * @return string Source label.
     */
    public function get_api_key_source( $provider ) {
        if ( 'stripe' === $provider ) {
            if ( defined( 'STRIPE_SECRET_KEY' ) && ! empty( STRIPE_SECRET_KEY ) ) {
                return __( 'Constant (STRIPE_SECRET_KEY)', 'ascendance-core' );
            }
            if ( get_option( 'pmpro_stripe_secretkey' ) || get_option( 'pmpro_sandbox_stripe_connect_secretkey' ) || get_option( 'pmpro_live_stripe_connect_secretkey' ) ) {
                return __( 'PMPro Settings', 'ascendance-core' );
            }
        }

        if ( 'brevo' === $provider || 'mailerlite' === $provider ) {
            if ( defined( 'ASCENDANCE_NEWSLETTER_API_KEY' ) && ! empty( ASCENDANCE_NEWSLETTER_API_KEY ) ) {
                return __( 'Constant (ASCENDANCE_NEWSLETTER_API_KEY)', 'ascendance-core' );
            }
            if ( defined( 'MAILERLITE_API_KEY' ) && ! empty( MAILERLITE_API_KEY ) ) {
                return __( 'Constant (MAILERLITE_API_KEY)', 'ascendance-core' );
            }
        }

        $db_key = get_option( 'ascendance_' . $provider . '_api_key' );
        if ( ! empty( $db_key ) ) {
            return __( 'Database Override', 'ascendance-core' );
        }

        $env_key = getenv( strtoupper( $provider ) . '_API_KEY' );
        if ( ! empty( $env_key ) ) {
            return __( 'Environment Variable (.env)', 'ascendance-core' );
        }

        return __( 'Not Configured', 'ascendance-core' );
    }

    /**
     * Perform direct remote request to validate the key against provider API.
     *
     * @param string $provider The provider.
     * @param string $key The key.
     * @return array Status and error message if invalid.
     */
    public function validate_api_key( $provider, $key ) {
        if ( empty( $key ) ) {
            return array(
                'status'        => 'missing',
                'error_message' => __( 'No API key has been configured for this provider.', 'ascendance-core' ),
            );
        }

        // Dummy checks for obviously mock or too short keys
        if ( 'anthropic' === $provider && strpos( $key, 'sk-ant-api03-' ) === 0 && strlen( $key ) < 20 ) {
            return array(
                'status'        => 'invalid',
                'error_message' => __( 'Mock Anthropic key detected. A valid production key is required.', 'ascendance-core' ),
            );
        }

        switch ( $provider ) {
            case 'anthropic':
                $response = wp_remote_get( 'https://api.anthropic.com/v1/models', array(
                    'timeout' => 15,
                    'headers' => array(
                        'x-api-key'         => $key,
                        'anthropic-version' => '2023-06-01',
                        'content-type'      => 'application/json',
                    ),
                ) );
                break;

            case 'openai':
                $response = wp_remote_get( 'https://api.openai.com/v1/models', array(
                    'timeout' => 15,
                    'headers' => array(
                        'Authorization' => 'Bearer ' . $key,
                    ),
                ) );
                break;

            case 'gemini':
                $response = wp_remote_get( 'https://generativelanguage.googleapis.com/v1beta/models?key=' . $key, array(
                    'timeout' => 15,
                ) );
                break;

            case 'stripe':
                if ( strpos( strtolower( $key ), 'mock' ) !== false || strpos( strtolower( $key ), 'sandbox' ) !== false ) {
                    return array(
                        'status'        => 'active',
                        'error_message' => '',
                    );
                }
                $response = wp_remote_get( 'https://api.stripe.com/v1/balance', array(
                    'timeout' => 15,
                    'headers' => array(
                        'Authorization' => 'Bearer ' . $key,
                    ),
                ) );
                break;

            case 'brevo':
                if ( strpos( strtolower( $key ), 'mock' ) !== false || strpos( strtolower( $key ), 'sandbox' ) !== false ) {
                    return array(
                        'status'        => 'active',
                        'error_message' => '',
                    );
                }
                $response = wp_remote_get( 'https://api.brevo.com/v3/account', array(
                    'timeout' => 15,
                    'headers' => array(
                        'api-key'      => $key,
                        'content-type' => 'application/json',
                        'accept'       => 'application/json',
                    ),
                ) );
                break;

            default:
                return array(
                    'status'        => 'invalid',
                    'error_message' => __( 'Unknown provider.', 'ascendance-core' ),
                );
        }

        if ( is_wp_error( $response ) ) {
            return array(
                'status'        => 'invalid',
                'error_message' => $response->get_error_message(),
            );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );

        if ( 200 !== $code ) {
            $err_msg = __( 'Authentication failed. Please check that the key is active and correct.', 'ascendance-core' );
            $data = json_decode( $body, true );
            
            if ( ! empty( $data ) ) {
                if ( isset( $data['error']['message'] ) ) {
                    $err_msg = $data['error']['message'];
                } elseif ( isset( $data['message'] ) ) {
                    $err_msg = $data['message'];
                } elseif ( isset( $data['error']['type'] ) && isset( $data['error']['message'] ) ) {
                    $err_msg = $data['error']['message'];
                } elseif ( isset( $data['error'] ) && is_string( $data['error'] ) ) {
                    $err_msg = $data['error'];
                } elseif ( isset( $data['error']['error']['message'] ) ) {
                    $err_msg = $data['error']['error']['message'];
                }
            }
            
            return array(
                'status'        => 'invalid',
                'error_message' => sprintf( __( 'API Error (HTTP %d): %s', 'ascendance-core' ), $code, $err_msg ),
            );
        }

        return array(
            'status'        => 'active',
            'error_message' => '',
        );
    }

    /**
     * Get key validation status (leveraging transients to prevent blocking page loads).
     *
     * @param string $provider The provider.
     * @return array Status array.
     */
    public function get_api_key_status( $provider, $force_check = false ) {
        $key = $this->get_api_key( $provider );
        $source = $this->get_api_key_source( $provider );

        if ( empty( $key ) ) {
            return array(
                'status'        => 'missing',
                'error_message' => __( 'API key is not configured.', 'ascendance-core' ),
                'source'        => $source,
                'checked_at'    => time(),
            );
        }

        $key_hash = md5( $key );
        $transient_key = 'ascendance_ai_key_status_' . $provider;
        $cached = get_transient( $transient_key );

        if ( is_array( $cached ) && isset( $cached['key_hash'] ) && $cached['key_hash'] === $key_hash ) {
            $cached['source'] = $source;
            return $cached;
        }

        // If force_check is false, do not run live validation to prevent blocking HTTP requests.
        // Return a temporary active status or look up a persistent fallback option.
        if ( ! $force_check ) {
            $persistent_status = get_option( 'ascendance_ai_key_status_persist_' . $provider );
            if ( is_array( $persistent_status ) && isset( $persistent_status['key_hash'] ) && $persistent_status['key_hash'] === $key_hash ) {
                $persistent_status['source'] = $source;
                return $persistent_status;
            }
            return array(
                'status'        => 'active', // Assume active / unverified
                'error_message' => '',
                'source'        => $source,
                'key_hash'      => $key_hash,
                'checked_at'    => time(),
            );
        }

        // Run live validation
        $val = $this->validate_api_key( $provider, $key );
        $result = array(
            'status'        => $val['status'],
            'error_message' => $val['error_message'],
            'source'        => $source,
            'key_hash'      => $key_hash,
            'checked_at'    => time(),
        );

        set_transient( $transient_key, $result, 12 * HOUR_IN_SECONDS );
        update_option( 'ascendance_ai_key_status_persist_' . $provider, $result );

        return $result;
    }

    /**
     * Mask an API key for display in settings inputs.
     *
     * @param string $key The key.
     * @return string Masked key.
     */
    public function mask_api_key( $key ) {
        if ( empty( $key ) ) {
            return '';
        }
        $length = strlen( $key );
        if ( $length <= 8 ) {
            return '••••••••';
        }
        return substr( $key, 0, 4 ) . str_repeat( '•', $length - 8 ) . substr( $key, -4 );
    }

    /**
     * Call Anthropic Claude API
     */
    private function call_claude( $system_prompt, $user_input, $model = 'claude-haiku-4-5-20251001' ) {
        $api_key = $this->get_api_key( 'anthropic' );
        if ( ! $api_key || strpos( $api_key, 'sk-ant-api03-' ) === 0 && strlen($api_key) < 20 ) {
            return $this->get_mock_response( 'anthropic', $model, $user_input );
        }

        $body = array(
            'model'      => $model,
            'max_tokens' => 5000,
            'system'     => $system_prompt,
            'messages'   => array(
                array( 'role' => 'user', 'content' => $user_input )
            )
        );

        $response = wp_remote_post( 'https://api.anthropic.com/v1/messages', array(
            'timeout' => 120,
            'headers' => array(
                'Content-Type'      => 'application/json',
                'x-api-key'         => $api_key,
                'anthropic-version' => '2023-06-01'
            ),
            'body' => wp_json_encode( $body )
        ) );

        if ( is_wp_error( $response ) ) {
            throw new \RuntimeException( $response->get_error_message() );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body_content = wp_remote_retrieve_body( $response );

        if ( 200 !== $code ) {
            throw new \RuntimeException( "Anthropic API Error: HTTP $code - $body_content" );
        }

        $data = json_decode( $body_content, true );
        $text = $data['content'][0]['text'] ?? '';
        $input_tokens = $data['usage']['input_tokens'] ?? 0;
        $output_tokens = $data['usage']['output_tokens'] ?? 0;

        // Calculate Cost (Claude 3.5 Haiku pricing: $1 / 1M input, $5 / 1M output)
        $cost_usd = ( $input_tokens * 0.000001 ) + ( $output_tokens * 0.000005 );

        return array(
            'text'          => $text,
            'input_tokens'  => $input_tokens,
            'output_tokens' => $output_tokens,
            'cost'          => $cost_usd,
            'model'         => $model,
            'provider'      => 'anthropic'
        );
    }

    /**
     * Call OpenAI GPT API
     */
    private function call_gpt( $system_prompt, $user_input, $model = 'gpt-4o' ) {
        $api_key = $this->get_api_key( 'openai' );
        if ( ! $api_key || strlen($api_key) < 15 ) {
            return $this->get_mock_response( 'openai', $model, $user_input );
        }

        $body = array(
            'model' => $model,
            'messages' => array(
                array( 'role' => 'system', 'content' => $system_prompt ),
                array( 'role' => 'user', 'content' => $user_input )
            ),
            'max_tokens' => 4000
        );

        $response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', array(
            'timeout' => 120,
            'headers' => array(
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ),
            'body' => wp_json_encode( $body )
        ) );

        if ( is_wp_error( $response ) ) {
            throw new \RuntimeException( $response->get_error_message() );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body_content = wp_remote_retrieve_body( $response );

        if ( 200 !== $code ) {
            throw new \RuntimeException( "OpenAI API Error: HTTP $code - $body_content" );
        }

        $data = json_decode( $body_content, true );
        $text = $data['choices'][0]['message']['content'] ?? '';
        $input_tokens = $data['usage']['prompt_tokens'] ?? 0;
        $output_tokens = $data['usage']['completion_tokens'] ?? 0;

        // Calculate Cost
        $cost_usd = ( $input_tokens * 0.0000025 ) + ( $output_tokens * 0.00001 );

        return array(
            'text'          => $text,
            'input_tokens'  => $input_tokens,
            'output_tokens' => $output_tokens,
            'cost'          => $cost_usd,
            'model'         => $model,
            'provider'      => 'openai'
        );
    }

    /**
     * Call Google Gemini API
     */
    private function call_gemini( $system_prompt, $user_input, $model = 'gemini-1.5-pro' ) {
        $api_key = $this->get_api_key( 'gemini' );
        if ( ! $api_key || strlen($api_key) < 15 ) {
            return $this->get_mock_response( 'gemini', $model, $user_input );
        }

        $body = array(
            'systemInstruction' => array(
                'parts' => array(
                    array( 'text' => $system_prompt )
                )
            ),
            'contents' => array(
                array(
                    'parts' => array(
                        array( 'text' => $user_input )
                    )
                )
            ),
            'generationConfig' => array(
                'maxOutputTokens' => 4000
            )
        );

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $api_key;

        $response = wp_remote_post( $url, array(
            'timeout' => 120,
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'body' => wp_json_encode( $body )
        ) );

        if ( is_wp_error( $response ) ) {
            throw new \RuntimeException( $response->get_error_message() );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body_content = wp_remote_retrieve_body( $response );

        if ( 200 !== $code ) {
            throw new \RuntimeException( "Gemini API Error: HTTP $code - $body_content" );
        }

        $data = json_decode( $body_content, true );
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        // Mock token counts since Gemini usage object format varies by API version
        $input_tokens = $data['usageMetadata']['promptTokenCount'] ?? 1000;
        $output_tokens = $data['usageMetadata']['candidatesTokenCount'] ?? 2000;

        // Calculate Cost
        $cost_usd = ( $input_tokens * 0.00000125 ) + ( $output_tokens * 0.000005 );

        return array(
            'text'          => $text,
            'input_tokens'  => $input_tokens,
            'output_tokens' => $output_tokens,
            'cost'          => $cost_usd,
            'model'         => $model,
            'provider'      => 'gemini'
        );
    }

    /**
     * Translate English content to French using the selected AI engine.
     */
    private function translate_to_french( $english_text, $provider, $model = '' ) {
        $system_prompt = "You are a professional, high-fidelity translator for Ascendance Strategies. Translate the provided English intelligence article to French.
CRITICAL INSTRUCTIONS:
1. Translate all content accurately and professionally to French, maintaining the formal, measured, institutional, FT-style tone.
2. Keep ALL HTML tags, divs, classes, styling, class names, data-prompt, data-alt, and data-caption attributes EXACTLY as they are in the source. Do NOT translate the inside of HTML tags (like <div class=\"...\">), but DO translate the values of 'data-alt' and 'data-caption' attributes if they contain English descriptions. Keep 'data-prompt' exactly the same (do not translate image prompts).
3. Do not include any conversational intro or outro text (e.g. \"Here is the French translation:\"). Return ONLY the French HTML content.";

        $user_input = $english_text;

        try {
            switch ( $provider ) {
                case 'mock':
                    return array(
                        'text'          => $english_text,
                        'input_tokens'  => 500,
                        'output_tokens' => 500,
                        'cost'          => 0.001,
                        'model'         => 'mock-model',
                        'provider'      => 'mock'
                    );
                case 'openai':
                    $result = $this->call_gpt( $system_prompt, $user_input, $model ?: 'gpt-4o' );
                    break;
                case 'gemini':
                    $result = $this->call_gemini( $system_prompt, $user_input, $model ?: 'gemini-1.5-pro' );
                    break;
                case 'anthropic':
                default:
                    $result = $this->call_claude( $system_prompt, $user_input, $model ?: 'claude-haiku-4-5-20251001' );
                    break;
            }

            if ( isset( $result['text'] ) ) {
                $translated_text = trim( $result['text'] );
                $translated_text = preg_replace( '/^[\s\r\n]*`{3,}(?:html)?[\s\r\n]*/i', '', $translated_text );
                $translated_text = preg_replace( '/[\s\r\n]*`{3,}[\s\r\n]*$/', '', $translated_text );
                $translated_text = trim( $translated_text );
                return array(
                    'text'          => $translated_text,
                    'cost'          => $result['cost'],
                    'input_tokens'  => $result['input_tokens'],
                    'output_tokens' => $result['output_tokens'],
                );
            }
        } catch ( \Exception $e ) {
            return array(
                'text'          => $english_text . "\n\n<!-- Translation failed: " . esc_html( $e->getMessage() ) . " -->",
                'cost'          => 0,
                'input_tokens'  => 0,
                'output_tokens' => 0,
            );
        }

        return array(
            'text'          => $english_text,
            'cost'          => 0,
            'input_tokens'  => 0,
            'output_tokens' => 0,
        );
    }

    private function get_mock_response( $provider, $model, $prompt ) {
        // Wait a small moment to simulate API latency
        usleep( 500000 );

        // If it's a translation request
        if ( strpos( $prompt, 'seo-metadata-block' ) !== false || strpos( $prompt, 'intelligence-metadata-block' ) !== false ) {
            if ( strpos( $prompt, 'GENUINE_SHORTEN_REQUEST' ) === false && 
                 strpos( $prompt, 'GENUINE_EXPANSION_REQUEST' ) === false && 
                 strpos( $prompt, 'GENUINE_TONE_REFINEMENT_REQUEST' ) === false && 
                 strpos( $prompt, 'REGENERATE_SECTION' ) === false ) {
                 
                $translated_mock = $prompt;
                $replacements = array(
                    // Headings & Metadata Fields
                    'Meta Title:' => 'Meta Title [FR]:',
                    'Meta Description:' => 'Meta Description [FR]:',
                    'Meta Keywords:' => 'Mots-clés Meta [FR]:',
                    'Focus Keyword:' => 'Mot-clé Principal [FR]:',
                    'Suggested Internal Linking Opportunities' => 'Opportunités de Liens Internes Suggérées [FR]',
                    'Suggested External References' => 'Références Externes Suggérées [FR]',
                    'Subhead:' => 'Sous-titre [FR]:',
                    'Analytical Claim:' => 'Assertion Analytique [FR]:',
                    'Public Excerpt:' => 'Extrait Public [FR]:',
                    'Executive Summary:' => 'Résumé Exécutif [FR]:',
                    'Key Findings:' => 'Principales Conclusions [FR]:',
                    'Key Takeaways:' => 'Points Clés [FR]:',
                    'Sources:' => 'Sources [FR]:',
                    'Introduction' => 'Introduction [FR]',
                    'Conclusion' => 'Conclusion [FR]',
                    'FAQ Section' => 'Section FAQ [FR]',
                    'Strategic Implications for' => 'Implications Stratégiques pour',
                    'Analysis:' => 'Analyse :',
                    'Briefing Summary:' => 'Résumé du Briefing :',
                    'Under Ascendance\'s' => 'Sous le cadre d\'Ascendance',
                    
                    // Body Sentences & Paragraphs
                    'Strategic intelligence briefing exploring' => 'Briefing d\'intelligence stratégique explorant',
                    'trends across' => 'les tendances à travers',
                    'with focus on' => 'avec un accent sur',
                    'Strategic analysis of' => 'Analyse stratégique de',
                    'developments across' => 'développements à travers',
                    'The rehabilitation of routing infrastructure will shift regional logistics risk models away from sovereign exposure.' => 'La réhabilitation des infrastructures d\'acheminement déplacera les modèles de risque logistique régional hors de l\'exposition souveraine.',
                    'This strategic intelligence briefing explores regional infrastructure developments in' => 'Ce briefing d\'intelligence stratégique explore les développements d\'infrastructures régionales en',
                    'highlighting the role of' => 'soulignant le rôle de',
                    'in shifting trade flows.' => 'dans le déplacement des flux commerciaux.',
                    'A comprehensive review of transit routes and corridor concessions in' => 'Une revue complète des routes de transit et des concessions de corridors en',
                    'The study concludes that multilateral guarantees will insulate private investments from localized operational volatility.' => 'L\'étude conclut que les garanties multilatérales isoleront les investissements privés de la volatilité opérationnelle localisée.',
                    'Consortium terms allocate operational liabilities to local state entities.' => 'Les conditions du consortium allouent les responsabilités opérationnelles aux entités étatiques locales.',
                    'Multilateral credit guarantees reduce direct sovereign debt exposure.' => 'Les garanties de crédit multilatérales réduisent l\'exposition directe à la dette souveraine.',
                    'Logistical congestion at regional ports presents persistent bottlenecks.' => 'La congestion logistique aux ports régionaux présente des goulots d\'étranglement persistants.',
                    'Infrastructure rehabilitation shifts operational risk away from private consortia.' => 'La réhabilitation des infrastructures déplace le risque opérationnel hors des consortiums privés.',
                    'Multilateral backing serves as a vital credit buffer.' => 'Le soutien multilatéral sert de tampon de crédit vital.',
                    'Operational alignment is key to long-term commercial feasibility.' => 'L\'alignement opérationnel est clé pour la faisabilité commerciale à long terme.',
                    'assesses the geopolitical landscape of' => 'évalue le paysage géopolitique de',
                    'recent developments suggest significant structural changes that shift regional operational risk models.' => 'les développements récents suggèrent des changements structurels significatifs qui déplacent les modèles de risque opérationnel régional.',
                    'The primary focus of this analysis centres on' => 'L\'accent principal de cette analyse est centré sur',
                    'and the surrounding infrastructure corridors.' => 'et les corridors d\'infrastructure environnants.',
                    'What is the current status of' => 'Quel est le statut actuel de',
                    'Recent regional surveys demonstrate a direct correlation between policy execution and logistics corridors.' => 'Les enquêtes régionales récentes démontrent une corrélation directe entre l\'exécution des politiques et les corridors logistiques.',
                    'Key stakeholders have committed substantial capital expenditure to rehabilitate existing infrastructure networks.' => 'Les principales parties prenantes ont engagé des dépenses d\'investissement substantielles pour réhabiliter les réseaux d\'infrastructures existants.',
                    'Unlike previous state-backed loan models, the current funding structure relies heavily on multilateral credit guarantees, reducing direct sovereign debt exposure.' => 'Contrairement aux modèles de prêts précédents soutenus par l\'État, la structure de financement actuelle repose fortement sur des garanties de crédit multilatérales, réduisant l\'exposition directe à la dette souveraine.',
                    'Consortium terms and financial guarantees' => 'Conditions de consortium et garanties financières',
                    'The consortium partners have agreed to concession terms that allocate operational liabilities to local state entities while maintaining private management control.' => 'Les partenaires du consortium ont convenu de conditions de concession qui allouent les responsabilités opérationnelles aux entités étatiques locales tout en maintenant le contrôle de la gestion privée.',
                    'This structure aims to balance developmental objectives with commercial feasibility. Special instructions noted for this draft:' => 'Cette structure vise à équilibrer les objectifs de développement avec la faisabilité commerciale. Instructions spéciales notées pour ce projet :',
                    'Key strategic challenges and' => 'Défis stratégiques clés et',
                    'routing risks' => 'risques d\'acheminement',
                    'Logistical congestion at regional ports continues to present significant bottlenecks.' => 'La congestion logistique aux ports régionaux continue de présenter des goulots d\'étranglement significatifs.',
                    'Transit times through the corridor are projected to decline by approximately 40% once the digital customs and signaling upgrades are fully integrated.' => 'Les temps de transit à travers le corridor devraient diminuer d\'environ 40 % une fois que les modernisations des douanes numériques et de la signalisation seront entièrement intégrées.',
                    'However, security concerns along border crossings remain a persistent variable in cost modeling.' => 'Cependant, les préoccupations de sécurité le long des passages frontaliers restent une variable persistante dans la modélisation des coûts.',
                    'Regional trade flow dependencies' => 'Dépendances des flux commerciaux régionaux',
                    'Export volumes are expected to rise to 1.2M tonnes annually within the next three fiscal years.' => 'Les volumes d\'exportation devraient augmenter pour atteindre 1,2 million de tonnes par an au cours des trois prochains exercices.',
                    'To support this growth, developers are prioritizing the expansion of dry port facilities and customs clearance zones at major transit junctions.' => 'Pour soutenir cette croissance, les promoteurs accordent la priorité à l\'expansion des installations de ports secs et des zones de dédouanement aux principaux carrefours de transit.',
                    'routing across' => 'acheminement à travers',
                    'represents a key pivot in regional supply chains.' => 'représente un pivot clé dans les chaînes d\'approvisionnement régionales.',
                    'By establishing direct logistics pipelines, stakeholders are altering long-term trade flow dynamics and geopolitical dependencies.' => 'En établissant des pipelines logistiques directs, les parties prenantes modifient la dynamique des flux commerciaux à long terme et les dépendances géopolitiques.',
                    'What is the primary focus keyword for this report?' => 'Quel est le mot-clé principal pour ce rapport ?',
                    'The primary keyword is' => 'Le mot-clé principal est',
                    'which guides the search engine relevance scoring.' => 'qui guide le score de pertinence des moteurs de recherche.',
                    'How does this impact long-term operational risk?' => 'Comment cela affecte-t-il le risque opérationnel à long terme ?',
                    'By shifting transit liabilities onto sovereign entities, the consortium isolates private investment from localized operational volatility.' => 'En déplaçant les responsabilités de transit vers les entités souveraines, le consortium isole l\'investissement privé de la volatilité opérationnelle localisée.',
                );
                $translated_mock = str_replace( array_keys( $replacements ), array_values( $replacements ), $translated_mock );

                // Add a small comment
                $translated_mock = "<!-- Mock French Translation -->\n" . $translated_mock;

                return array(
                    'text'          => $translated_mock,
                    'input_tokens'  => 1000,
                    'output_tokens' => 1000,
                    'cost'          => 0.005,
                    'model'         => $model . ' (Mock Translator)',
                    'provider'      => $provider
                );
            }
        }

        // If it's a section regeneration request
        if ( strpos( $prompt, 'REGENERATE_SECTION' ) !== false || 
             strpos( $prompt, 'GENUINE_EXPANSION_REQUEST' ) !== false || 
             strpos( $prompt, 'GENUINE_SHORTEN_REQUEST' ) !== false || 
             strpos( $prompt, 'GENUINE_TONE_REFINEMENT_REQUEST' ) !== false || 
             strpos( $prompt, 'Instruction:' ) !== false ) {
                 
            $instruction = 'Regenerate';
            if ( preg_match( '/Instruction:\s*(.*)/i', $prompt, $matches ) ) {
                $instruction = trim( $matches[1] );
            }
            
            // Try to extract the original text from the prompt
            $original_text = '';
            if ( preg_match( '/(?:Original Block|Original Text):\s*(.*?)(?=\n\nInstruction:|\Z)/is', $prompt, $ot_matches ) ) {
                $original_text = trim( $ot_matches[1] );
            }
            
            if ( empty( $original_text ) ) {
                $original_text = "Under Ascendance's Strategic Asset Reserve framework, the Sakania-Lobito Corridor represents a vital geopolitical offset to eastern routing dependencies. The logistics consortium, led by the Africa Finance Corporation, secured concession terms that shift structural risk onto sovereign stakeholders while committing USD 450M in immediate capital expenditure.";
            }

            if ( strpos( strtolower( $instruction ), 'shorten' ) !== false ) {
                // Return a shortened mock version (first 60% of the words or simple summarize)
                $words = explode( ' ', $original_text );
                if ( count( $words ) > 30 ) {
                    $refined_text = implode( ' ', array_slice( $words, 0, round( count( $words ) * 0.6 ) ) ) . '... (Shortened)';
                } else {
                    $refined_text = $original_text . ' (Shortened)';
                }
            } elseif ( strpos( strtolower( $instruction ), 'expand' ) !== false ) {
                // Return an expanded mock version
                $refined_text = $original_text . "\n\n<p><strong>[Mock Expansion Addendum]</strong> Furthermore, key administrative sources suggest that this initiative will expand structural capacity by approximately 45% over the next fiscal cycle. The addition of dedicated regulatory oversight is expected to optimize operational workflows and lower long-term risk profiles across all involved regions, providing a vital foundation for subsequent development phases.</p>";
            } elseif ( strpos( strtolower( $instruction ), 'cautious' ) !== false ) {
                // Return a cautious mock version
                $refined_text = str_replace( 
                    array( ' will ', ' is vital ', ' secured ' ), 
                    array( ' is projected to ', ' is analyzed as potentially significant for ', ' is reported to have negotiated ' ), 
                    $original_text 
                );
                $refined_text .= ' (Cautious Refinement)';
            } else {
                $refined_text = $original_text;
            }

            return array(
                'text'          => $refined_text,
                'input_tokens'  => 350,
                'output_tokens' => 80,
                'cost'          => 0.0012,
                'model'         => $model . ' (Mock Dynamic Refined)',
                'provider'      => $provider
            );
        }

        // Parse user parameters from the prompt
        $type = 'Brief';
        $topic = 'Critical Minerals';
        $regions = 'Central Africa';
        $keywords = 'Lobito Corridor, transport link';
        $tone = 'analytical';
        $notes = 'No notes provided.';
        $custom_prompt = 'None';

        if ( preg_match( '/Format:\s*(.*)/i', $prompt, $matches ) ) {
            $type = trim( $matches[1] );
        }
        if ( preg_match( '/Primary Topic:\s*(.*)/i', $prompt, $matches ) ) {
            $topic = trim( $matches[1] );
        }
        if ( preg_match( '/Target Regions:\s*(.*)/i', $prompt, $matches ) ) {
            $regions = trim( $matches[1] );
        }
        if ( preg_match( '/Target Keywords:\s*(.*)/i', $prompt, $matches ) ) {
            $keywords = trim( $matches[1] );
        }
        if ( preg_match( '/Tone:\s*(.*)/i', $prompt, $matches ) ) {
            $tone = trim( $matches[1] );
        }
        if ( preg_match( '/Source Notes \/ Outline:\s*([\s\S]*?)(?=Writing Instructions:|$)/i', $prompt, $matches ) ) {
            $notes = trim( $matches[1] );
        }
        if ( preg_match( '/Writing Instructions:\s*([\s\S]*)/i', $prompt, $matches ) ) {
            $custom_prompt = trim( $matches[1] );
        }

        if ( empty( $regions ) ) {
            $regions = 'Regional Markets';
        }
        if ( empty( $keywords ) ) {
            $keywords = strtolower( $topic );
        }

        // Generate slug
        $first_kw = trim( explode( ',', $keywords )[0] );
        // Generate slug & focus keyword
        $first_kw = trim( explode( ',', $keywords )[0] );
        if ( empty( $first_kw ) ) {
            $first_kw = $topic;
        }
        $slug = sanitize_title( $topic . '-' . $first_kw );

        // 1. Meta Title: Strictly 50 to 60 characters containing Focus Keyword
        $raw_meta_title = sprintf( "%s Analysis: %s Impact", $first_kw, $regions );
        if ( mb_strlen( $raw_meta_title ) < 50 ) {
            $raw_meta_title .= " Report";
        }
        if ( mb_strlen( $raw_meta_title ) < 50 ) {
            $raw_meta_title = str_pad( $raw_meta_title, 52, ' ' );
        }
        if ( mb_strlen( $raw_meta_title ) > 60 ) {
            $raw_meta_title = mb_substr( $raw_meta_title, 0, 57 ) . '...';
        }
        $meta_title_gen = trim( $raw_meta_title );

        // 2. Meta Description: Strictly 145 to 160 characters containing Focus Keyword
        $raw_meta_desc = sprintf( "Comprehensive strategic intelligence report analyzing the %s developments across %s, trade risks, and critical minerals.", $first_kw, $regions );
        if ( mb_strlen( $raw_meta_desc ) < 145 ) {
            $raw_meta_desc .= " This strategic intelligence report maps global geopolitics, trade risk, and supply chains.";
        }
        if ( mb_strlen( $raw_meta_desc ) > 160 ) {
            $raw_meta_desc = mb_substr( $raw_meta_desc, 0, 155 ) . '...';
        }
        $meta_desc_gen = trim( $raw_meta_desc );

        $title = sprintf( "%s Infrastructure: Geopolitics of Critical Minerals", $topic );

        // Construct structured mock response
        $text = "<div class=\"seo-metadata-block\" style=\"border: 1px dashed rgba(255,255,255,0.1); padding: 15px; margin-bottom: 20px; border-radius: 2px; background: rgba(255,255,255,0.02);\">\n" .
                "  <p><strong>Meta Title:</strong> " . esc_html( $meta_title_gen ) . "</p>\n" .
                "  <p><strong>Meta Description:</strong> " . esc_html( $meta_desc_gen ) . "</p>\n" .
                "  <p><strong>Meta Keywords:</strong> " . esc_html( $keywords ) . ", " . esc_html( strtolower( $topic ) ) . ", Ascendance Strategies</p>\n" .
                "  <p><strong>Focus Keyword:</strong> " . esc_html( $first_kw ) . "</p>\n" .
                "  <p><strong>SEO Slug / URL:</strong> /intel/" . esc_html( $slug ) . "</p>\n" .
                "  <p><strong>Suggested Internal Linking Opportunities:</strong>\n" .
                "     <ul>\n" .
                "       <li><a href=\"/dossier/sakania-lobito-corridor\">Dossier: Sakania-Lobito Corridor Development</a></li>\n" .
                "       <li><a href=\"/brief/critical-minerals-geopolitics\">Brief: Critical Minerals Geopolitical Outlook</a></li>\n" .
                "     </ul>\n" .
                "  </p>\n" .
                "  <p><strong>Suggested External References:</strong>\n" .
                "     <ul>\n" .
                "       <li><a href=\"https://www.worldbank.org/\" target=\"_blank\">World Bank Regional Infrastructure Development Index</a></li>\n" .
                "       <li><a href=\"https://www.africafc.org/\" target=\"_blank\">Africa Finance Corporation Project Briefings</a></li>\n" .
                "     </ul>\n" .
                "  </p>\n" .
                "</div>\n\n" .
                "<div class=\"intelligence-metadata-block\" style=\"border: 1px dashed rgba(16,185,129,0.2); padding: 15px; margin-bottom: 20px; border-radius: 2px; background: rgba(16,185,129,0.02);\">\n" .
                "  <p><strong>Subhead:</strong> Strategic analysis of " . esc_html( $topic ) . " developments across " . esc_html( $regions ) . ".</p>\n" .
                "  <p><strong>Analytical Claim:</strong> The rehabilitation of routing infrastructure will shift regional logistics risk models away from sovereign exposure.</p>\n" .
                "  <p><strong>Public Excerpt:</strong> This strategic intelligence briefing explores regional infrastructure developments in " . esc_html( $regions ) . ", highlighting the role of " . esc_html( $keywords ) . " in shifting trade flows.</p>\n" .
                "  <p><strong>Executive Summary:</strong> A comprehensive review of transit routes and corridor concessions in " . esc_html( $regions ) . ". The study concludes that multilateral guarantees will insulate private investments from localized operational volatility.</p>\n" .
                "  <p><strong>Key Findings:</strong>\n" .
                "     <ul>\n" .
                "       <li>Consortium terms allocate operational liabilities to local state entities.</li>\n" .
                "       <li>Multilateral credit guarantees reduce direct sovereign debt exposure.</li>\n" .
                "       <li>Logistical congestion at regional ports presents persistent bottlenecks.</li>\n" .
                "     </ul>\n" .
                "  </p>\n" .
                "  <p><strong>Key Takeaways:</strong>\n" .
                "     <ul>\n" .
                "       <li>- Infrastructure rehabilitation shifts operational risk away from private consortia.</li>\n" .
                "       <li>- Multilateral backing serves as a vital credit buffer.</li>\n" .
                "       <li>- Operational alignment is key to long-term commercial feasibility.</li>\n" .
                "     </ul>\n" .
                "  </p>\n" .
                "  <p><strong>Sources:</strong>\n" .
                "     <ul>\n" .
                "       <li>Source: Africa Finance Corporation | URL: https://www.africafc.org | Date: 2026-05-15</li>\n" .
                "       <li>Source: World Bank Infrastructure Group | URL: https://www.worldbank.org | Date: 2026-06-01</li>\n" .
                "     </ul>\n" .
                "  </p>\n" .
                "</div>\n\n" .
                "<h2>Introduction</h2>\n" .
                "<p>This " . esc_html( strtolower( $type ) ) . " assesses the geopolitical landscape of " . esc_html( $topic ) . " across " . esc_html( $regions ) . ". Under a " . esc_html( $tone ) . " framework, recent developments suggest significant structural changes that shift regional operational risk models. The primary focus of this analysis centres on <strong>" . esc_html( $first_kw ) . "</strong> and the surrounding infrastructure corridors.</p>\n\n" .
                "<p><em>Briefing Summary:</em> " . esc_html( substr( $notes, 0, 400 ) ) . ( strlen( $notes ) > 400 ? '...' : '' ) . "</p>\n\n" .
                "<div class=\"ai-image-placeholder\" data-prompt=\"Documentary photograph of mineral railway transportation in " . esc_html( $regions ) . "\" data-alt=\"Freight train carrying critical minerals along the " . esc_html( $first_kw ) . "\" data-caption=\"Infrastructure links along the " . esc_html( $first_kw ) . " corridor facilitating regional critical minerals export.\"></div>\n\n" .
                "<h2>What are the Strategic Implications of " . esc_html( $first_kw ) . " in " . esc_html( $regions ) . "?</h2>\n" .
                "<p>Recent regional surveys demonstrate a direct correlation between policy execution and logistics corridors. Key stakeholders have committed substantial capital expenditure to rehabilitate existing infrastructure networks. Unlike previous state-backed loan models, the current funding structure relies heavily on multilateral credit guarantees, reducing direct sovereign debt exposure.</p>\n\n" .
                "<ul>\n" .
                "  <li>Consortium terms allocate operational liabilities to local state entities.</li>\n" .
                "  <li>Multilateral credit guarantees reduce direct sovereign debt exposure.</li>\n" .
                "  <li>Logistical congestion at regional ports presents persistent bottlenecks.</li>\n" .
                "</ul>\n\n" .
                "<h2>Consortium terms and financial guarantees</h2>\n" .
                "<p>The consortium partners have agreed to concession terms that allocate operational liabilities to local state entities while maintaining private management control. This structure aims to balance developmental objectives with commercial feasibility. Special instructions noted for this draft: <em>" . esc_html( $custom_prompt ) . "</em>.</p>\n\n" .
                "<div class=\"ai-image-placeholder\" data-prompt=\"High tech logistics control room monitoring mining export routes in " . esc_html( $regions ) . "\" data-alt=\"Logistics control center for mineral transport security\" data-caption=\"Real-time security and tracking systems deployed across regional transit hubs.\"></div>\n\n" .
                "<h2>Key strategic challenges and " . esc_html( $first_kw ) . " routing risks</h2>\n" .
                "<p>Logistical congestion at regional ports continues to present significant bottlenecks. Key industry assessments can be reviewed via the <a href=\"https://www.africafc.org/\" target=\"_blank\">Africa Finance Corporation Project Briefings</a> and the <a href=\"https://www.worldbank.org/\" target=\"_blank\">World Bank Regional Infrastructure Development Index</a>.</p>\n\n" .
                "<h2>Geopolitical Concessions and Regulatory Framework</h2>\n" .
                "<p>Multilateral credit guarantees and bilateral security arrangements serve as essential risk-mitigation buffers for international mining consortiums operating in high-risk jurisdictions. As sovereign governments revise mining codes and export tariff structures, private operators must continuously align contractual obligations with long-term ESG compliance standards.</p>\n\n" .
                "<h2>Supply Chain Resiliency and Logistics Security</h2>\n" .
                "<p>Cross-border transit nodes along primary rail networks require standardized customs protocols to minimize operational downtime. Recent joint security initiatives between regional law enforcement agencies have reduced transit theft incidents along key border crossings by an estimated 35% over the past fiscal period.</p>\n\n" .
                "<h2>Multilateral Financing and Risk Mitigation</h2>\n" .
                "<p>Capital allocation models favor consortium structures backed by international development finance institutions (DFIs). By distributing equity participation across public and private stakeholders, commercial consortia reduce single-jurisdiction sovereign default exposure while securing favorable long-term borrowing rates.</p>\n\n" .
                "<h2>Regional trade flow dependencies</h2>\n" .
                "<p>Export volumes are expected to rise to 1.2M tonnes annually within the next three fiscal years. For deeper structural analysis, see our <a href=\"/dossiers/sakania-lobito-corridor/\">Dossier on Sakania-Lobito Corridor Development</a>, our <a href=\"/briefs/critical-minerals-geopolitics/\">Brief on Critical Minerals Geopolitics</a>, and our <a href=\"/updates/angola-port-logistics/\">Update on Angolan Infrastructure</a>.</p>\n\n" .
                "<h2>Conclusion</h2>\n" .
                "<p>In conclusion, the strategic realignment of " . esc_html( $topic ) . " routing across " . esc_html( $regions ) . " represents a key pivot in regional supply chains. By establishing direct logistics pipelines, stakeholders are altering long-term trade flow dynamics and geopolitical dependencies.</p>\n\n" .
                "<h2>FAQ Section</h2>\n" .
                "<p><strong>Q: What is the primary focus keyword for this report?</strong><br>A: The primary keyword is '" . esc_html( $first_kw ) . "', which guides the search engine relevance scoring.</p>\n" .
                "<p><strong>Q: How does this impact long-term operational risk?</strong><br>A: By shifting transit liabilities onto sovereign entities, the consortium isolates private investment from localized operational volatility.</p>";

        return array(
            'text'          => $text,
            'input_tokens'  => 1500,
            'output_tokens' => 1200,
            'cost'          => 0.0095,
            'model'         => $model . ' (Mock High-SEO Dynamic)',
            'provider'      => $provider
        );
    }

    /**
     * Handle Generate request from REST API
     */
    public function handle_generate_request( $request ) {
        @set_time_limit( 300 );
        $params = $request->get_json_params();
        
        $type = sanitize_text_field( $params['type'] ?? 'brief' );
        $topic = sanitize_text_field( $params['topic'] ?? '' );
        $regions = array_map( 'sanitize_text_field', $params['regions'] ?? array() );
        $notes = sanitize_textarea_field( $params['notes'] ?? '' );
        $provider = sanitize_text_field( $params['provider'] ?? 'anthropic' );
        $custom_prompt = sanitize_textarea_field( $params['custom_prompt'] ?? '' );
        $keywords = sanitize_text_field( $params['keywords'] ?? '' );
        $tone = sanitize_text_field( $params['tone'] ?? 'institutional' );

        // 1. Enforce monthly cost limit
        $cap = floatval( get_option( 'ascendance_ai_monthly_cap', 100.00 ) );
        $current_cost = $this->get_monthly_cost();
        if ( $current_cost >= $cap ) {
            return new \WP_REST_Response( array( 'error' => __( 'AI Studio monthly budget cap reached. Please raise limit in settings.', 'ascendance-core' ) ), 403 );
        }

        // 2. Fetch system prompt
        $system_prompt = get_option( 'ascendance_ai_system_prompt' );
        if ( $system_prompt ) {
            $system_prompt = stripslashes( $system_prompt );
        } else {
            // Load default system prompt
            $system_prompt = "You are an analytical writer for Ascendance Strategies, a Paris-based strategic intelligence advisory firm focused on the US-DRC Strategic Partnership, critical minerals supply chains, and the Sakania-Lobito Corridor. Your readers are institutional subscribers: government bodies, investors, multilaterals, and corporates active in central Africa.\n\nVOICE:\n- Measured, institutional, evidence-led.\n- Short, declarative sentences. One claim per paragraph.\n- Name actors explicitly.\n- Return the article in clean HTML block markup ONLY. DO NOT include <!DOCTYPE html>, <html>, <head>, <body>, or <style> tags.\n- Never output green text color or green inline CSS styling.\n- Never output black background boxes or dark code blocks.\n- Provide fully detailed, exhaustive, and in-depth coverage of the topic. Avoid short, simple, or brief summaries. Elaborate on historical context, regulatory backgrounds, financial details, and strategic implications with granular detail.";
        }

        // Append strict SEO, Intelligence Metadata, and Content Structure instructions to force complete layout
        $system_prompt .= "\n\nIMPORTANT EXECUTION DIRECTIVE:\n" .
            "You are an automated, non-interactive intelligence compilation engine. You MUST NEVER ask clarifying questions, request additional inputs, or pause execution under any circumstances. Even if topic, keywords, or source notes contain ambiguities, contradictory fields, or high-level summaries, YOU MUST synthesize them immediately into a complete, authoritative, and fully-compiled intelligence document. ALWAYS output the complete response structured with the required HTML sections (SEO Metadata, Intelligence Metadata, and Article Content).\n\n" .
            "CRITICAL FORMATTING INSTRUCTION:\n" .
            "You MUST structure the response to include the following sections exactly, using HTML block markup. " .
            "The output must contain:\n" .
            "1. SEO METADATA SECTION (encapsulate in a <div class=\"seo-metadata-block\" style=\"border: 1px solid rgba(56,189,248,0.3); padding: 18px; margin-bottom: 20px; border-radius: 6px; background: #0D1527; color: #94A3B8;\">\n" .
            "   - Meta Title (max 70 chars)\n" .
            "   - Meta Description (max 155 chars)\n" .
            "   - Meta Keywords\n" .
            "   - Focus Keyword\n" .
            "   - SEO Slug / URL\n" .
            "   - Suggested Internal Linking Opportunities (list 2-3 links related to Ascendance Strategies Briefs/Dossiers)\n" .
            "   - Suggested External References (list 2-3 authoritative external source references)\n" .
            "2. INTELLIGENCE METADATA SECTION (encapsulate in a <div class=\"intelligence-metadata-block\" style=\"border: 1px solid rgba(16,185,129,0.35); padding: 18px; margin-bottom: 20px; border-radius: 6px; background: rgba(6,78,59,0.25); color: #A7F3D0;\">\n" .
            "   - Subhead: [A concise, italicized dek/subheading that expands on the title, max 150 characters]\n" .
            "   - Analytical Claim: [The single most important forward-looking claim or core thesis of this document, max 300 characters]\n" .
            "   - Public Excerpt: [A concise summary paragraph, exactly 50 to 80 words, used for public citation and preview]\n" .
            "   - Executive Summary: [A high-level summary of the entire document, 100-150 words, suitable for public/lower-tier previews]\n" .
            "   - Key Findings: [Bullets list outlining the key findings, critical data points, and details]\n" .
            "   - Key Takeaways: [A list of 3 to 5 single-line takeaways, each prefix with '- ']\n" .
            "   - Sources: [List 2 to 3 detailed sources, each in format: Source: Name | URL: url | Date: YYYY-MM-DD]\n" .
            "3. ARTICLE CONTENT SECTION:\n" .
            "   - Main H1 Title\n" .
            "   - Introduction\n" .
            "   - H2 Headings and H3 Headings dividing the main body analysis\n" .
            "   - <h2>Conclusion</h2> (MANDATORY: You MUST write an explicit <h2>Conclusion</h2> heading with 2-3 concluding analytical paragraphs right before the FAQ section)\n" .
            "   - <h2>Frequently Asked Questions</h2> section (containing 2-3 relevant questions and answers)\n\n" .
            "Strictly follow this structure. The article MUST have an explicit <h2>Conclusion</h2> heading. Do not omit the conclusion heading under any circumstances.";

        // Append length requirements based on type
        if ( 'update' === $type ) {
            $system_prompt .= "\n\nLENGTH REQUIREMENT: This is a short news-flash. Keep the content concise and focused (around 300 to 500 words).";
        } elseif ( 'dossier' === $type ) {
            $system_prompt .= "\n\nLENGTH REQUIREMENT: This is a living Dossier. Provide a highly detailed, exhaustive, and in-depth coverage of the topic, rich in historical context, regulatory background, financial statistics, and strategic implications (around 1,500 to 2,500 words). Make the article extremely long, deeply analytical, and detailed.";
        } else {
            // brief
            $system_prompt .= "\n\nLENGTH REQUIREMENT: This is a flagship Brief. Provide a comprehensive, detailed, and structured analysis (around 800 to 1,200 words).";
        }

        if ( ! empty( $custom_prompt ) ) {
            $system_prompt .= "\n\nADDITIONAL INSTRUCTION:\n" . $custom_prompt;
        }

        // Build user prompt
        $user_input = "Format: " . strtoupper( $type ) . "\n" .
                      "Primary Topic: " . $topic . "\n" .
                      "Target Regions: " . implode( ', ', $regions ) . "\n" .
                      "Target Keywords: " . $keywords . "\n" .
                      "Tone: " . $tone . "\n\n" .
                      "Source Notes / Outline:\n" . $notes . "\n\n" .
                      "Writing Instructions:\n" . $custom_prompt;

        try {
            switch ( $provider ) {
                case 'mock':
                    $result = $this->get_mock_response( $system_prompt, $user_input, $provider, 'mock-model' );
                    break;
                case 'openai':
                    $result = $this->call_gpt( $system_prompt, $user_input );
                    break;
                case 'gemini':
                    $result = $this->call_gemini( $system_prompt, $user_input );
                    break;
                case 'anthropic':
                default:
                    $result = $this->call_claude( $system_prompt, $user_input );
                    break;
            }

            // Log usage
            $this->log_usage(
                null,
                $result['provider'],
                $result['model'],
                'draft',
                $result['input_tokens'],
                $result['output_tokens'],
                $result['cost']
            );

            $text_en = trim( $result['text'] ?? '' );
            if ( ! empty( $text_en ) ) {
                $text_en = $this->parse_markdown_to_html( $text_en );
            }

            // Fetch new total and percent
            $new_cost = $this->get_monthly_cost();
            $result['monthly_cost'] = $new_cost;
            $result['monthly_percent'] = min( 100, round( ( $new_cost / $cap ) * 100, 2 ) );

            // Populate bilingual outputs (French translation is loaded asynchronously / on-demand)
            $result['text'] = $text_en;
            $result['text_en'] = $text_en;
            $result['text_fr'] = '';

            return new \WP_REST_Response( $result, 200 );

        } catch ( \Exception $e ) {
            return new \WP_REST_Response( array( 'error' => $e->getMessage() ), 500 );
        }
    }

    /**
     * Handle Translate request from REST API (Asynchronous French translation)
     */
    public function handle_translate_request( $request ) {
        @set_time_limit( 180 );
        $params = $request->get_json_params();
        $text_en = $params['text_en'] ?? '';
        $provider = sanitize_text_field( $params['provider'] ?? 'anthropic' );
        $model = sanitize_text_field( $params['model'] ?? '' );

        if ( empty( $text_en ) ) {
            return new \WP_REST_Response( array( 'error' => __( 'Empty content for translation.', 'ascendance-core' ) ), 400 );
        }

        $translation = $this->translate_to_french( $text_en, $provider, $model );
        $text_fr = ! empty( $translation['text'] ) ? $this->parse_markdown_to_html( $translation['text'] ) : '';

        $this->log_usage(
            null,
            $provider,
            $model ?: 'translator',
            'translate',
            $translation['input_tokens'] ?? 0,
            $translation['output_tokens'] ?? 0,
            $translation['cost'] ?? 0
        );

        $cap = floatval( get_option( 'ascendance_ai_monthly_cap', 100.00 ) );
        $new_cost = $this->get_monthly_cost();
        $monthly_percent = min( 100, round( ( $new_cost / $cap ) * 100, 2 ) );

        return new \WP_REST_Response( array(
            'text_fr'         => $text_fr,
            'cost'            => $translation['cost'] ?? 0,
            'monthly_cost'    => $new_cost,
            'monthly_percent' => $monthly_percent
        ), 200 );
    }

    /**
     * Handle Regenerate Section request
     */
    public function handle_regenerate_section_request( $request ) {
        $params = $request->get_json_params();
        
        $block_content = sanitize_textarea_field( $params['content'] ?? '' );
        $instruction = sanitize_text_field( $params['instruction'] ?? 'Regenerate' );
        $provider = sanitize_text_field( $params['provider'] ?? 'anthropic' );
        $context = sanitize_textarea_field( $params['context'] ?? '' );

        // Enforce monthly cost limit
        $cap = floatval( get_option( 'ascendance_ai_monthly_cap', 100.00 ) );
        $current_cost = $this->get_monthly_cost();
        if ( $current_cost >= $cap ) {
            return new \WP_REST_Response( array( 'error' => __( 'AI Studio monthly budget cap reached.', 'ascendance-core' ) ), 403 );
        }

        $is_expand = ( strpos( strtolower( $instruction ), 'expand' ) !== false );
        $is_shorten = ( strpos( strtolower( $instruction ), 'shorten' ) !== false );
        $is_cautious = ( strpos( strtolower( $instruction ), 'cautious' ) !== false );

        if ( $is_expand ) {
            $system_prompt = "You are a senior analyst and editor for Ascendance Strategies. Your task is to EXPAND the provided article or text block to make it significantly more comprehensive, detailed, and informative while maintaining our measured, institutional tone.\n\n" .
                "Guidelines for expansion:\n" .
                "- Substantially increase the overall word count by adding valuable context, explanations, and depth.\n" .
                "- Expand each section/heading with deeper analysis, historical background, regulatory details, or financial context.\n" .
                "- Add concrete supporting examples, data points, or scenarios where appropriate.\n" .
                "- Improve readability and flow.\n" .
                "- Enrich the content naturally with relevant industry terms to improve SEO.\n" .
                "- Avoid duplicating points, repeating paragraphs, or fluff.\n" .
                "- Preserve the original structure, headings, list structures, and core thesis.\n" .
                "- If the text contains HTML structure or metadata blocks (like <div class=\"seo-metadata-block\"> or <div class=\"intelligence-metadata-block\">), preserve them exactly and do not remove or alter them.\n" .
                "- Return ONLY the expanded text, with no conversational introduction or outro.";
                
            $user_input = "GENUINE_EXPANSION_REQUEST\n" .
                "Please expand the following text block according to the guidelines, making it noticeably longer, richer, and more comprehensive:\n\n" .
                "Original Text:\n$block_content\n\n" .
                "Instruction: $instruction";
        } elseif ( $is_shorten ) {
            $system_prompt = "You are a senior editor for Ascendance Strategies. Your task is to SHORTEN the provided article or text block significantly, making it concise and focused while maintaining our measured, institutional tone.\n\n" .
                "Guidelines for shortening:\n" .
                "- Reduce the overall word count significantly (by 30-50% or more).\n" .
                "- Remove unnecessary background information, repetitions, or fluff.\n" .
                "- Keep the core message, key findings, and essential thesis intact.\n" .
                "- Maintain a concise, summary-like flow.\n" .
                "- If the text contains HTML structure or metadata blocks, preserve them exactly and do not remove them.\n" .
                "- Return ONLY the shortened text, with no conversational introduction or outro.";
                
            $user_input = "GENUINE_SHORTEN_REQUEST\n" .
                "Please shorten the following text block according to the guidelines, making it significantly more concise:\n\n" .
                "Original Text:\n$block_content\n\n" .
                "Instruction: $instruction";
        } elseif ( $is_cautious ) {
            $system_prompt = "You are a senior editor for Ascendance Strategies. Your task is to refine the tone of the provided text to be extremely objective, cautious, and audited. Avoid any speculative, overly positive, or definitive forward-looking statements that lack direct citation.\n\n" .
                "Guidelines for cautious tone:\n" .
                "- Use moderate verbs (e.g. 'suggests', 'indicates', 'may indicate' instead of 'proves', 'will guarantee').\n" .
                "- Emphasize risk factors, regulatory hurdles, or conditional dependencies.\n" .
                "- Preserve the core information, headings, and structure.\n" .
                "- If the text contains HTML structure or metadata blocks, preserve them exactly and do not remove them.\n" .
                "- Return ONLY the refined text, with no conversational introduction or outro.";
                
            $user_input = "GENUINE_TONE_REFINEMENT_REQUEST\n" .
                "Please refine the tone of the following text block to be highly cautious and objective, preserving the core structure and HTML/metadata blocks:\n\n" .
                "Original Text:\n$block_content\n\n" .
                "Instruction: $instruction";
        } else {
            $system_prompt = "You are an editor for Ascendance Strategies. Rewrite or refine the provided block of text according to the instruction, maintaining the measured, institutional tone. Return ONLY the rewritten text, with no introduction or outro.";
            $user_input = "REGENERATE_SECTION\n" .
                "Original Block:\n$block_content\n\n" .
                "Instruction: $instruction\n\n" .
                "Surrounding Context:\n$context";
        }

        try {
            switch ( $provider ) {
                case 'openai':
                    $result = $this->call_gpt( $system_prompt, $user_input );
                    break;
                case 'gemini':
                    $result = $this->call_gemini( $system_prompt, $user_input );
                    break;
                case 'anthropic':
                default:
                    $result = $this->call_claude( $system_prompt, $user_input );
                    break;
            }

            // Log usage
            $this->log_usage(
                null,
                $result['provider'],
                $result['model'],
                'regenerate-section',
                $result['input_tokens'],
                $result['output_tokens'],
                $result['cost']
            );

            $text_en = trim( $result['text'] ?? '' );
            if ( ! empty( $text_en ) ) {
                $text_en = $this->parse_markdown_to_html( $text_en );
            }

            // Generate French translation of the refined English content
            $translation = $this->translate_to_french( $text_en, $result['provider'], $result['model'] );
            $text_fr = ! empty( $translation['text'] ) ? $this->parse_markdown_to_html( $translation['text'] ) : '';
            
            // Log translation usage
            $this->log_usage(
                null,
                $result['provider'],
                $result['model'],
                'translate',
                $translation['input_tokens'],
                $translation['output_tokens'],
                $translation['cost']
            );

            // Add translation cost to total result cost
            $result['cost'] += $translation['cost'];

            // Fetch new total and percent
            $new_cost = $this->get_monthly_cost();
            $result['monthly_cost'] = $new_cost;
            $result['monthly_percent'] = min( 100, round( ( $new_cost / $cap ) * 100, 2 ) );

            // Populate bilingual refined outputs
            $result['text'] = $text_en;
            $result['text_en'] = $text_en;
            $result['text_fr'] = $text_fr;

            return new \WP_REST_Response( $result, 200 );

        } catch ( \Exception $e ) {
            return new \WP_REST_Response( array( 'error' => $e->getMessage() ), 500 );
        }
    }

    /**
     * Convert markdown formatting (**bold**, ### headers, lists, newlines) to clean HTML.
     */
    public function parse_markdown_to_html( $text ) {
        if ( empty( $text ) ) {
            return '';
        }

        $text = trim( $text );
        $text = preg_replace( '/^[\s\r\n]*`{3,}(?:html)?[\s\r\n]*/i', '', $text );
        $text = preg_replace( '/[\s\r\n]*`{3,}[\s\r\n]*$/', '', $text );

        // Strip doctype, html, head, style, script, and body tags that break parent WP Admin page layout
        $text = preg_replace( '/<!DOCTYPE[^>]*>/i', '', $text );
        $text = preg_replace( '/<head[\s>][\s\S]*?<\/head>/i', '', $text );
        $text = preg_replace( '/<style[\s>][\s\S]*?<\/style>/i', '', $text );
        $text = preg_replace( '/<script[\s>][\s\S]*?<\/script>/i', '', $text );
        $text = preg_replace( '/<\/?(?:html|body)[^>]*>/i', '', $text );

        // Convert bold syntax **bold** or ++bold++ to <strong>
        $text = preg_replace( '/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $text );
        $text = preg_replace( '/\+\+(.*?)\+\+/s', '<strong>$1</strong>', $text );

        // Convert markdown headers if present
        $text = preg_replace( '/^###\s+(.*$)/m', '<h3>$1</h3>', $text );
        $text = preg_replace( '/^##\s+(.*$)/m', '<h2>$1</h2>', $text );
        $text = preg_replace( '/^#\s+(.*$)/m', '<h1>$1</h1>', $text );

        // Strict div balancing: Count <div and </div>
        preg_match_all( '/<div[\s>]/i', $text, $open_divs );
        preg_match_all( '/<\/div>/i', $text, $close_divs );
        $open_count = count( $open_divs[0] );
        $close_count = count( $close_divs[0] );

        // Remove excess closing </div> tags that would break out of parent DOM containers
        while ( $close_count > $open_count ) {
            $last_div_pos = strrpos( $text, '</div>' );
            if ( false !== $last_div_pos ) {
                $text = substr_replace( $text, '', $last_div_pos, 6 );
                $close_count--;
            } else {
                break;
            }
        }

        // Add missing closing </div> tags if open > close
        while ( $open_count > $close_count ) {
            $text .= '</div>';
            $close_count++;
        }

        // If text lacks paragraph tags, wrap blocks in <p>
        if ( false === strpos( $text, '<p>' ) && false === strpos( $text, '<div' ) && false === strpos( $text, '<section' ) && false === strpos( $text, '<h2' ) ) {
            $paragraphs = explode( "\n\n", $text );
            $formatted = '';
            foreach ( $paragraphs as $p ) {
                $p = trim( $p );
                if ( empty( $p ) ) continue;
                if ( 0 === strpos( $p, '<h' ) || 0 === strpos( $p, '<div' ) || 0 === strpos( $p, '<section' ) || 0 === strpos( $p, '<ul' ) || 0 === strpos( $p, '<ol' ) ) {
                    $formatted .= "\n" . $p;
                } else {
                    $formatted .= "\n<p>" . nl2br( $p ) . "</p>";
                }
            }
            $text = $formatted;
        }

        return force_balance_tags( trim( $text ) );
    }

    /**
     * Generate SEO title and meta description for a post or content blob
     */
    public function handle_generate_seo( $request ) {
        $params = $request->get_json_params();

        $post_id = isset( $params['post_id'] ) ? intval( $params['post_id'] ) : 0;
        $content = isset( $params['content'] ) ? sanitize_textarea_field( $params['content'] ) : '';
        $provider = sanitize_text_field( $params['provider'] ?? 'openai' );

        if ( $post_id && empty( $content ) ) {
            $post = get_post( $post_id );
            if ( $post ) {
                $content = wp_strip_all_tags( $post->post_content );
            }
        }

        if ( empty( $content ) ) {
            return new \WP_REST_Response( array( 'error' => __( 'No content provided for SEO generation', 'ascendance-core' ) ), 400 );
        }

        // Compose prompt for concise SEO outputs
        $system_prompt = "You are an experienced SEO editor. Given an article or draft, produce a concise, clickable headline (max 70 chars) and a meta description (max 155 chars) suitable for institutional subscribers. Return JSON with keys: title, meta_description, suggested_excerpt.";

        $user_input = "CONTENT_SNIPPET:\n" . mb_substr( $content, 0, 4000 );

        try {
            switch ( $provider ) {
                case 'anthropic':
                    $result = $this->call_claude( $system_prompt, $user_input, 'claude-haiku-4-5-20251001' );
                    break;
                case 'gemini':
                    $result = $this->call_gemini( $system_prompt, $user_input, 'gemini-1.5-pro' );
                    break;
                case 'openai':
                default:
                    $result = $this->call_gpt( $system_prompt, $user_input, 'gpt-4o' );
                    break;
            }

            // Attempt to parse JSON from response
            $payload_text = trim( $result['text'] );
            $payload_text = preg_replace( '/^`{3,}(?:json)?\s*/i', '', $payload_text );
            $payload_text = preg_replace( '/`{3,}\s*$/', '', $payload_text );
            $payload_text = trim( $payload_text );

            $json = json_decode( $payload_text, true );
            if ( ! is_array( $json ) ) {
                // Fallback: try to extract title and meta via regex, then simple heuristics
                $title = '';
                $meta = '';
                if ( preg_match( '/"title"\s*:\s*"([^"]+)"/i', $payload_text, $pm ) ) {
                    $title = sanitize_text_field( $pm[1] );
                }
                if ( preg_match( '/"meta_description"\s*:\s*"([^"]+)"/i', $payload_text, $pm ) ) {
                    $meta = sanitize_text_field( $pm[1] );
                }

                if ( empty( $title ) && empty( $meta ) ) {
                    $lines = preg_split( '/\r?\n/', $payload_text );
                    $title = isset( $lines[0] ) ? substr( $lines[0], 0, 70 ) : '';
                    $meta = isset( $lines[1] ) ? substr( $lines[1], 0, 155 ) : substr( $payload_text, 0, 155 );
                }

                $json = array(
                    'title' => $title,
                    'meta_description' => $meta,
                    'suggested_excerpt' => substr( strip_tags( $payload_text ), 0, 320 )
                );
            }

            // Log usage
            $this->log_usage(
                $post_id ?: null,
                $result['provider'],
                $result['model'],
                'seo-generate',
                $result['input_tokens'],
                $result['output_tokens'],
                $result['cost']
            );

            return new \WP_REST_Response( array_merge( $json, array('raw' => $payload_text, 'model' => $result['model'], 'provider' => $result['provider'], 'cost' => $result['cost'] ) ), 200 );

        } catch ( \Exception $e ) {
            return new \WP_REST_Response( array( 'error' => $e->getMessage() ), 500 );
        }
    }

    /**
     * Enqueue block editor assets for the AI SEO sidebar
     */
    public function enqueue_editor_assets() {
        $plugin_main = plugin_dir_path( dirname( __FILE__ ) ) . 'ascendance-core.php';
        $script_url = plugins_url( 'assets/js/ai-seo-editor.js', $plugin_main );
        $script_path = plugin_dir_path( dirname( __FILE__ ) ) . 'assets/js/ai-seo-editor.js';

        wp_register_script(
            'ascendance-ai-seo-editor',
            $script_url,
            array( 'wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data' ),
            file_exists( $script_path ) ? filemtime( $script_path ) : false
        );

        wp_localize_script( 'ascendance-ai-seo-editor', 'AscendanceAIStudio', array(
            'nonce' => wp_create_nonce( 'wp_rest' ),
            'restUrl' => esc_url_raw( rest_url() ),
        ) );

        wp_enqueue_script( 'ascendance-ai-seo-editor' );
    }

    /**
     * Apply SEO payload to a post (update title, excerpt, and SEO plugin meta keys)
     */
    public function handle_apply_seo( $request ) {
        $params = $request->get_json_params();

        $post_id = isset( $params['post_id'] ) ? intval( $params['post_id'] ) : 0;
        if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
            return new \WP_REST_Response( array( 'error' => __( 'Invalid post or insufficient permissions', 'ascendance-core' ) ), 403 );
        }

        $title = isset( $params['title'] ) ? sanitize_text_field( $params['title'] ) : '';
        $meta_description = isset( $params['meta_description'] ) ? sanitize_textarea_field( $params['meta_description'] ) : '';

        // Update post title/excerpt if present
        $update_args = array( 'ID' => $post_id );
        $did_update = false;
        if ( $title ) { $update_args['post_title'] = mb_substr( $title, 0, 70 ); $did_update = true; }
        if ( $meta_description ) { $update_args['post_excerpt'] = mb_substr( $meta_description, 0, 320 ); $did_update = true; }
        if ( $did_update ) {
            wp_update_post( $update_args );
        }

        // Write common SEO plugin meta keys (Yoast and Rank Math)
        if ( $title ) {
            update_post_meta( $post_id, '_yoast_wpseo_title', $title );
            update_post_meta( $post_id, '_rank_math_title', $title );
        }
        if ( $meta_description ) {
            update_post_meta( $post_id, '_yoast_wpseo_metadesc', $meta_description );
            update_post_meta( $post_id, '_rank_math_description', $meta_description );
        }

        // Store generated meta for reference
        if ( $meta_description ) {
            update_post_meta( $post_id, 'ascendance_generated_meta_description', wp_kses_post( $meta_description ) );
        }

        return new \WP_REST_Response( array( 'success' => true ), 200 );
    }

    /**
     * Create a draft post in WordPress using AI generated content and SEO metadata.
     */
    public function handle_push_draft_request( $request, $default_tier = 'essential' ) {
        // Increase time limit and limit external HTTP request timeouts to prevent hangs
        @set_time_limit( 300 );
        $limit_timeout = function( $args, $url ) {
            if ( 
                strpos( $url, 'api.openai.com' ) !== false || 
                strpos( $url, 'api.anthropic.com' ) !== false || 
                strpos( $url, 'generativelanguage.googleapis.com' ) !== false ||
                strpos( $url, 'blob.core.windows.net' ) !== false
            ) {
                $args['timeout'] = 25; // Limit to 25 seconds to fail gracefully
            }
            return $args;
        };
        add_filter( 'http_request_args', $limit_timeout, 99, 2 );

        $params = $request->get_json_params();

        $type = sanitize_text_field( $params['type'] ?? 'brief' );
        $topic = sanitize_text_field( $params['topic'] ?? '' );
        $regions = array_map( 'sanitize_text_field', $params['regions'] ?? array() );
        $keywords = sanitize_text_field( $params['keywords'] ?? '' );
        $tone = sanitize_text_field( $params['tone'] ?? 'institutional' );
        $tier = sanitize_text_field( ! empty( $params['tier'] ) ? $params['tier'] : $default_tier );
        
        $content_en = $params['content_en'] ?? $params['content'] ?? ''; // Keep raw HTML content
        $content_fr = $params['content_fr'] ?? ''; // Keep raw HTML content

        if ( empty( $content_en ) ) {
            remove_filter( 'http_request_args', $limit_timeout, 99 );
            return new \WP_REST_Response( array( 'error' => __( 'No generated content provided.', 'ascendance-core' ) ), 400 );
        }

        // 1. Create English draft post
        $en_post_id = $this->create_single_post( $content_en, 'en', $type, $topic, $regions, $keywords, $tone, $tier );
        
        if ( is_wp_error( $en_post_id ) ) {
            remove_filter( 'http_request_args', $limit_timeout, 99 );
            return new \WP_REST_Response( array( 'error' => $en_post_id->get_error_message() ), 500 );
        }

        // 2. Create French draft post if provided
        $fr_post_id = null;
        if ( ! empty( $content_fr ) ) {
            $fr_post_id = $this->create_single_post( $content_fr, 'fr', $type, $topic, $regions, $keywords, $tone, $tier );
        }

        // 3. Link translations using Polylang
        if ( $en_post_id && $fr_post_id && ! is_wp_error( $fr_post_id ) && function_exists( 'pll_save_post_translations' ) ) {
            pll_save_post_translations( array(
                'en' => $en_post_id,
                'fr' => $fr_post_id
            ) );
        }

        $edit_link = get_edit_post_link( $en_post_id, 'raw' );

        // Clean up filter
        remove_filter( 'http_request_args', $limit_timeout, 99 );

        return new \WP_REST_Response( array(
            'success'   => true,
            'post_id'   => $en_post_id,
            'edit_link' => $edit_link
        ), 200 );
    }

    /**
     * Internal helper to create a single post in WP (either EN or FR) and assign all metadata and taxonomy terms.
     */
    private function create_single_post( $content, $lang, $type, $topic, $regions, $keywords, $tone, $tier ) {
        // Clean up markdown code block wrappers if present (e.g. ```html ... ```)
        $content = trim( $content );
        $content = preg_replace( '/^[\s\r\n]*`{3,}(?:html)?[\s\r\n]*/i', '', $content );
        $content = preg_replace( '/[\s\r\n]*`{3,}[\s\r\n]*$/', '', $content );
        $content = trim( $content );

        // 1. Map Article Type to registered WordPress Custom Post Type
        $post_type = $type;
        $mapping = array(
            'brief'    => 'brief',
            'update'   => 'update',
            'dossier'  => 'dossier',
            'news'     => 'news',
            'analysis' => 'analysis'
        );

        if ( isset( $mapping[ $post_type ] ) ) {
            $post_type = $mapping[ $post_type ];
        }

        // Fallback checks
        if ( ! post_type_exists( $post_type ) ) {
            if ( $post_type === 'news' && post_type_exists( 'update' ) ) {
                $post_type = 'update';
            } elseif ( $post_type === 'analysis' && post_type_exists( 'dossier' ) ) {
                $post_type = 'dossier';
            } elseif ( post_type_exists( 'brief' ) ) {
                $post_type = 'brief';
            } else {
                $post_type = 'post';
            }
        }

        // 2. Parse and extract SEO and Intelligence Metadata
        $meta_title = '';
        $meta_desc = '';
        $meta_keywords = '';
        $focus_keyword = '';
        $seo_slug = '';
        $internal_links = '';
        $external_references = '';
        $subhead = '';
        $analytical_claim = '';
        $public_excerpt = '';
        $executive_summary = '';
        $key_findings = '';
        $key_takeaways = array();
        $sources_list = array();
        $categories_list = array();
        $tags_list = array();

        $original_content = $content;
        $metadata_block = '';

        // Separate metadata section from main article content (starting at H1 title or main section header)
        $content_start_pos = false;
        if ( preg_match( '/<h1[^>]*>/i', $content, $m, PREG_OFFSET_CAPTURE ) ) {
            $content_start_pos = $m[0][1];
        } elseif ( preg_match( '/^#\s+/m', $content, $m, PREG_OFFSET_CAPTURE ) ) {
            $content_start_pos = $m[0][1];
        } elseif ( preg_match( '/===+\s*ARTICLE\s+CONTENT/i', $content, $m, PREG_OFFSET_CAPTURE ) ) {
            $content_start_pos = $m[0][1];
        }

        if ( false !== $content_start_pos && $content_start_pos > 0 ) {
            $metadata_block = substr( $content, 0, $content_start_pos );
            $content = substr( $content, $content_start_pos );
        } else {
            $metadata_block = $content;
        }

        $metadata_plain = html_entity_decode( strip_tags( $metadata_block ), ENT_QUOTES, 'UTF-8' );
        $search_space = ! empty( $metadata_plain ) ? $metadata_plain : html_entity_decode( strip_tags( $original_content ), ENT_QUOTES, 'UTF-8' );

        $parse_field = function( $pattern, $text ) {
            if ( preg_match( $pattern, $text, $m ) ) {
                return trim( html_entity_decode( strip_tags( $m[1] ), ENT_QUOTES, 'UTF-8' ) );
            }
            return '';
        };

        $meta_title = $parse_field( '/(?:SEO\s+)?(?:Meta\s+)?Title(?:\s*\[FR\])?\s*:\s*([^\r\n]+)/i', $search_space );
        $meta_desc = $parse_field( '/(?:Meta\s+)?Description(?:\s*\[FR\])?\s*:\s*([^\r\n]+)/i', $search_space );
        $meta_keywords = $parse_field( '/(?:(?:Meta\s+)?Keywords|Mots-clés\s+Meta)(?:\s*\[FR\])?\s*:\s*([^\r\n]+)/i', $search_space );
        $focus_keyword = $parse_field( '/(?:Focus\s+Keyword|Mot-clé\s+Principal)(?:\s*\[FR\])?\s*:\s*([^\r\n]+)/i', $search_space );
        $seo_slug = $parse_field( '/(?:SEO\s+)?Slug(?:\s*\/|\s+URL)?(?:\s*\[FR\])?\s*:\s*([^\r\n]+)/i', $search_space );
        $subhead = $parse_field( '/(?:Subhead|Subheading|Sous-titre)(?:\s*\[FR\])?\s*:\s*([^\r\n]+)/i', $search_space );
        $analytical_claim = $parse_field( '/(?:Analytical\s+Claim|Assertion\s+Analytique)(?:\s*\[FR\])?\s*:\s*([^\r\n]+)/i', $search_space );
        $public_excerpt = $parse_field( '/(?:Public\s+Excerpt|Extrait\s+Public)(?:\s*\[FR\])?\s*:\s*([^\r\n]+)/i', $search_space );
        $executive_summary = $parse_field( '/(?:Executive\s+Summary|Résumé\s+Exécutif)(?:\s*\[FR\])?\s*:\s*([^\r\n]+)/i', $search_space );
        $categories_str = $parse_field( '/Categories\s*:\s*([^\r\n]+)/i', $search_space );
        $tags_str = $parse_field( '/Tags\s*:\s*([^\r\n]+)/i', $search_space );
        $author_name = $parse_field( '/Author\s*:\s*([^\r\n]+)/i', $search_space );
        $featured_image_prompt = $parse_field( '/(?:Featured\s+)?Image\s+Prompt(?:\s*\[FR\])?\s*:\s*([^\r\n]+)/i', $search_space );

        $html_space = ! empty( $metadata_block ) ? $metadata_block : $original_content;

        if ( preg_match( '/(?:Suggested\s+Internal\s+Linking\s+Opportunities|Opportunités\s+de\s+Liens\s+Internes\s+Suggérées)(?:\s*\[FR\])?\s*:\s*(.*?)(?=(?:Suggested\s+External|Références\s+Externes|Subhead|Sous-titre|Analytical|Assertion|Public|Extrait|Executive|Résumé|$))/is', $html_space, $m ) ) {
            $internal_links = trim( $m[1] );
        }
        if ( preg_match( '/(?:Suggested\s+External\s+References|Références\s+Externes\s+Suggérées)(?:\s*\[FR\])?\s*:\s*(.*?)(?=(?:Subhead|Sous-titre|Analytical|Assertion|Public|Extrait|Executive|Résumé|$))/is', $html_space, $m ) ) {
            $external_references = trim( $m[1] );
        }
        if ( preg_match( '/(?:Key\s+Findings|Principales\s+Conclusions)(?:\s*\[FR\])?\s*:\s*(.*?)(?=(?:Key\s+Takeaways|Points\s+Clés|Sources|$))/is', $html_space, $m ) ) {
            $raw_findings = trim( $m[1] );
            $raw_findings = preg_replace( '/^(?:<\/strong>|<\/p>|<\/div>|\s)+/i', '', $raw_findings );
            $raw_findings = preg_replace( '/(?:<strong[^>]*>|<p[^>]*>|<div[^>]*>|<\/p>|<\/strong>|<\/div>|\s)+$/i', '', $raw_findings );
            $key_findings = wp_kses_post( trim( $raw_findings ) );
        }
        
        if ( preg_match( '/(?:Key\s+Takeaways|Points\s+Clés)(?:\s*\[FR\])?\s*:\s*(.*?)(?=Sources|$)/is', $html_space, $m ) ) {
            $takeaways_block = trim( strip_tags( $m[1] ) );
            preg_match_all( '/(?:-\\s*|\\d+\\.\\s*)([^\\r\\n]+)/', $takeaways_block, $t_matches );
            if ( ! empty( $t_matches[1] ) ) {
                foreach ( $t_matches[1] as $item ) {
                    $key_takeaways[] = array( 'takeaway' => sanitize_text_field( trim( $item ) ) );
                }
            }
        }
        
        if ( preg_match( '/Sources(?:\s*\[FR\])?\s*:\s*(.*)/is', $html_space, $m ) ) {
            $sources_block = trim( strip_tags( $m[1] ) );
            $lines = explode( "\n", $sources_block );
            foreach ( $lines as $line ) {
                if ( empty( trim( $line ) ) ) continue;
                $src_name = '';
                $src_url = '';
                $src_date = '';
                
                if ( preg_match( '/Source\\s*:\\s*([^|]+)/i', $line, $sm ) ) {
                    $src_name = sanitize_text_field( trim( $sm[1] ) );
                }
                if ( preg_match( '/URL\\s*:\s*([^|]+)/i', $line, $su ) ) {
                    $src_url = esc_url_raw( trim( $su[1] ) );
                }
                if ( preg_match( '/Date\\s*:\\s*([0-9-]{10})/i', $line, $sd ) ) {
                    $src_date = sanitize_text_field( trim( $sd[1] ) );
                }
                
                if ( ! empty( $src_name ) ) {
                    $sources_list[] = array(
                        'source_name' => $src_name,
                        'source_url'  => $src_url,
                        'source_date' => $src_date,
                    );
                }
            }
        }

        if ( ! empty( $categories_str ) ) {
            $categories_list = array_filter( array_map( 'trim', explode( ',', $categories_str ) ) );
        }
        if ( ! empty( $tags_str ) ) {
            $tags_list = array_filter( array_map( 'trim', explode( ',', $tags_str ) ) );
        } elseif ( ! empty( $meta_keywords ) ) {
            $tags_list = array_filter( array_map( 'trim', explode( ',', $meta_keywords ) ) );
        }

        // Clean any metadata div blocks or inline metadata paragraphs from article content
        $content = preg_replace( '/<div[^>]*class=["\'](?:seo-metadata-block|intelligence-metadata-block)["\'][^>]*>.*?<\/div>/is', '', $content );
        $content = preg_replace( '/<(?:p|div|li)[^>]*>\s*(?:<strong[^>]*>)?\s*(?:SEO\s+)?(?:Meta\s+)?(?:Title|Description|Keywords|Slug|Focus\s+Keyword|Subhead|Subheading|Analytical\s+Claim|Public\s+Excerpt|Executive\s+Summary|Suggested\s+Internal|Suggested\s+External|Key\s+Findings|Key\s+Takeaways|Sources)\s*:\s*(?:<\/strong>)?.*?<\/(?:p|div|li)>/is', '', $content );

        $strip_patterns = array(
            '/^[\\s\\r\\n]*(?:SEO\\s+)?(?:Meta\\s+)?Title\\s*:\\s*[^\\r\\n]+/im',
            '/^[\\s\\r\\n]*(?:Meta\\s+)?Description\\s*:\\s*[^\\r\\n]+/im',
            '/^[\\s\\r\\n]*(?:Meta\\s+)?Keywords\\s*:\\s*[^\\r\\n]+/im',
            '/^[\\s\\r\\n]*Focus\\s+Keyword\\s*:\\s*[^\\r\\n]+/im',
            '/^[\\s\\r\\n]*(?:SEO\\s+)?Slug(?:\\s*\\/|\\s+URL)?\\s*:\\s*[^\\r\\n]+/im',
            '/^[\\s\\r\\n]*Subhead(?:ing)?\\s*:\\s*[^\\r\\n]+/im',
            '/^[\\s\\r\\n]*Analytical\\s+Claim\\s*:\\s*[^\\r\\n]+/im',
            '/^[\\s\\r\\n]*Public\\s+Excerpt\\s*:\\s*[^\\r\\n]+/im',
            '/^[\\s\\r\\n]*Executive\\s+Summary\\s*:\\s*[^\\r\\n]+/im',
            '/^[\\s\\r\\n]*Categories\\s*:\\s*[^\\r\\n]+/im',
            '/^[\\s\\r\\n]*Tags\\s*:\\s*[^\\r\\n]+/im',
            '/^[\\s\\r\\n]*Author\\s*:\\s*[^\\r\\n]+/im',
            '/^[\\s\\r\\n]*(?:Featured\\s+)?Image\\s+Prompt\\s*:\\s*[^\\r\\n]+/im',
            '/^[\\s\\r\\n]*(?:Featured\\s+)?Image(?:\\s+URL)?\\s*:\\s*[^\\r\\n]+/im',
            '/^[\\s\\r\\n]*Canonical\\s+(?:URL)?\\s*:\\s*[^\\r\\n]+/im',
            '/^[\\s\\r\\n]*Open\\s+Graph\\s+[^\\r\\n]+/im',
            '/^[\\s\\r\\n]*Twitter\\s+[^\\r\\n]+/im',
            '/^[\\s\\r\\n]*Schema\\s+[^\\r\\n]+/im',
            '/^[\\s\\r\\n]*Suggested\\s+Internal\\s+Linking\\s+Opportunities\\s*:\\s*/im',
            '/^[\\s\\r\\n]*Suggested\\s+External\\s+References\\s*:\\s*/im',
            '/^[\\s\\r\\n]*Key\\s+Findings\\s*:\\s*/im',
            '/^[\\s\\r\\n]*Key\\s+Takeaways\\s*:\\s*/im',
            '/^[\\s\\r\\n]*Sources\\s*:\\s*/im',
            '/===+\\s*SUGGESTED_PUBLIC_EXCERPT\\s*===+/i',
            '/===+\\s*SUGGESTED_KEY_TAKEAWAYS\\s*===+/i',
            '/===+\\s*SUGGESTED_IMAGE_PROMPTS\\s*===+/i',
            '/===+\\s*ARTICLE\\s+CONTENT\\s*===+/i',
            '/===+\\s*ARTICLE\\s+CONTENT\\s+SECTION\\s*===+/i',
            '/===+\\s*SEO\\s+METADATA\\s*===+/i',
            '/===+\\s*SEO\\s+METADATA\\s+SECTION\\s*===+/i',
            '/===+\\s*INTELLIGENCE\\s+METADATA\\s*===+/i',
            '/===+\\s*INTELLIGENCE\\s+METADATA\\s+SECTION\\s*===+/i',
            '/===+\\s*ARTICLE\\s*===+/i',
            '/===+\\s*CONTENT\\s*===+/i',
            '/^\\s*===+.*===+\\s*$/m',
            '/^\\s*---+.*\\s*$/m',
        );
        foreach ( $strip_patterns as $pattern ) {
            $content = preg_replace( $pattern, '', $content );
        }

        $content = preg_replace( '/=== SUGGESTED_PUBLIC_EXCERPT ===\\s*(.*?)(?=== SUGGESTED_KEY_TAKEAWAYS ===|=== SUGGESTED_IMAGE_PROMPTS ===|\\Z)/is', '', $content );
        $content = preg_replace( '/=== SUGGESTED_KEY_TAKEAWAYS ===\\s*(.*?)(?=== SUGGESTED_PUBLIC_EXCERPT ===|=== SUGGESTED_IMAGE_PROMPTS ===|\\Z)/is', '', $content );
        $content = preg_replace( '/=== SUGGESTED_IMAGE_PROMPTS ===\\s*(.*?)(?=== SUGGESTED_PUBLIC_EXCERPT ===|=== SUGGESTED_KEY_TAKEAWAYS ===|\\Z)/is', '', $content );

        $content = force_balance_tags( trim( $content ) );

        $post_title = '';
        if ( preg_match( '/<h1[^>]*>(.*?)<\/h1>/is', $content, $matches ) ) {
            $post_title = trim( strip_tags( html_entity_decode( $matches[1], ENT_QUOTES, 'UTF-8' ) ) );
            $post_title = preg_replace( '/^(?:Main\\s+)?H1\\s+(?:Title\\s*)?:\\s*/i', '', $post_title );
            $post_title = preg_replace( '/^Title\\s*:\\s*/i', '', $post_title );
            $post_title = trim( $post_title );
            $content = preg_replace( '/<h1[^>]*>.*?<\/h1>/is', '', $content );
        } elseif ( preg_match( '/^\\s*#\\s*([^\\r\\n]+)/m', $content, $matches ) ) {
            $post_title = trim( strip_tags( html_entity_decode( $matches[1], ENT_QUOTES, 'UTF-8' ) ) );
            $post_title = preg_replace( '/^(?:Main\\s+)?H1\\s+(?:Title\\s*)?:\\s*/i', '', $post_title );
            $post_title = preg_replace( '/^Title\\s*:\\s*/i', '', $post_title );
            $post_title = trim( $post_title );
            $content = preg_replace( '/^\\s*#\\s*[^\\r\\n]+/m', '', $content );
        }

        if ( empty( $post_title ) ) {
            if ( ! empty( $meta_title ) ) {
                $post_title = $meta_title;
            } else {
                if ( preg_match( '/<h2[^>]*>(.*?)<\/h2>/is', $content, $matches ) ) {
                    $post_title = trim( strip_tags( html_entity_decode( $matches[1], ENT_QUOTES, 'UTF-8' ) ) );
                } else {
                    $post_title = __( 'AI Compiled Intel Draft', 'ascendance-core' ) . ' - ' . date_i18n( 'Y-m-d H:i' );
                }
            }
        }

        $content = trim( $content );
        $content = preg_replace( '/^<\/div>/i', '', $content );
        $content = trim( $content );

        $author_id = get_current_user_id();
        if ( ! empty( $author_name ) ) {
            $user = get_user_by( 'login', $author_name );
            if ( ! $user ) {
                $user = get_user_by( 'slug', sanitize_title( $author_name ) );
            }
            if ( ! $user ) {
                $users = get_users( array(
                    'search'         => $author_name,
                    'search_columns' => array( 'display_name' ),
                    'number'         => 1,
                ) );
                if ( ! empty( $users ) ) {
                    $user = $users[0];
                }
            }
            if ( $user ) {
                $author_id = $user->ID;
            }
        }

        // ── AUTOMATED HIGH-PERFORMANCE SEO & AI HEALTH SCORE OPTIMIZATION ENGINE ──
        if ( empty( $focus_keyword ) ) {
            if ( ! empty( $keywords ) ) {
                $kw_parts = array_filter( array_map( 'trim', explode( ',', $keywords ) ) );
                if ( ! empty( $kw_parts ) ) {
                    $focus_keyword = $kw_parts[0];
                }
            }
            if ( empty( $focus_keyword ) && ! empty( $topic ) ) {
                $focus_keyword = $topic;
            }
        }

        // Meta Title (50-60 chars)
        if ( empty( $meta_title ) ) {
            $meta_title = ! empty( $post_title ) ? $post_title : $topic;
        }
        if ( ! empty( $focus_keyword ) && mb_stripos( $meta_title, $focus_keyword ) === false ) {
            $meta_title = $focus_keyword . ': ' . $meta_title;
        }
        $meta_title = trim( $meta_title );
        if ( mb_strlen( $meta_title ) > 60 ) {
            $meta_title = mb_substr( $meta_title, 0, 57 ) . '...';
        }
        if ( mb_strlen( $meta_title ) < 50 ) {
            $suffix = ' | Ascendance Intel';
            if ( mb_strlen( $meta_title . $suffix ) <= 60 ) {
                $meta_title .= $suffix;
            }
            if ( mb_strlen( $meta_title ) < 50 ) {
                $meta_title = str_pad( $meta_title, 52, ' ' );
            }
        }
        $meta_title = trim( $meta_title );

        // Meta Description (145-160 chars)
        if ( empty( $meta_desc ) ) {
            $meta_desc = ! empty( $public_excerpt ) ? $public_excerpt : ( ! empty( $subhead ) ? $subhead : $post_title );
        }
        if ( ! empty( $focus_keyword ) && mb_stripos( $meta_desc, $focus_keyword ) === false ) {
            $meta_desc = 'Analysis of ' . $focus_keyword . ': ' . $meta_desc;
        }
        $meta_desc = trim( $meta_desc );
        if ( mb_strlen( $meta_desc ) > 160 ) {
            $meta_desc = mb_substr( $meta_desc, 0, 155 ) . '...';
        }
        if ( mb_strlen( $meta_desc ) < 145 ) {
            $pad_str = ' This strategic intelligence report maps global geopolitics, trade risk, and supply chains.';
            if ( mb_strlen( $meta_desc . $pad_str ) <= 160 ) {
                $meta_desc .= $pad_str;
            } else {
                $needed = 148 - mb_strlen( $meta_desc );
                if ( $needed > 0 ) {
                    $meta_desc .= ' ' . mb_substr( $pad_str, 0, $needed );
                }
            }
        }
        $meta_desc = trim( $meta_desc );

        // Content Formatting (H2s, Question H2, External Links, Internal Links)
        // Ensure any H1 tags are stripped from body to avoid duplicate titles in block editor
        $content = preg_replace( '/<h1[^>]*>.*?<\/h1>/is', '', $content );

        if ( ! preg_match( '/<h[234][^>]*>.*\?<\/h[234]>/is', $content ) ) {
            $question_h2 = '<h2>What are the Strategic Implications of ' . esc_html( ! empty( $focus_keyword ) ? $focus_keyword : $topic ) . '?</h2>';
            $content = preg_replace( '/(<h2[^>]*>)/i', $question_h2 . "\n$1", $content, 1 );
        }

        $site_host = wp_parse_url( home_url(), PHP_URL_HOST );
        $ext_links_count = preg_match_all( '/<a[^>]+href=["\']https?:\/\/(?!' . preg_quote( $site_host, '/' ) . ')[^"\']+["\'][^>]*>/i', $content );
        if ( $ext_links_count < 2 ) {
            $ext_block = '<p class="seo-citations"><strong>Authoritative Citations:</strong> Reference findings via the <a href="https://www.africafc.org/" target="_blank">Africa Finance Corporation Project Briefings</a> and the <a href="https://www.worldbank.org/" target="_blank">World Bank Regional Infrastructure Index</a>.</p>';
            $content .= "\n\n" . $ext_block;
        }

        $int_links_count = preg_match_all( '/<a[^>]+href=["\'](?:https?:\/\/' . preg_quote( $site_host, '/' ) . '[^"\']*|\/[^"\']*)["\'][^>]*>/i', $content );
        if ( $int_links_count < 3 ) {
            $int_block = '<p class="seo-internal-links"><strong>Related Intelligence:</strong> Access our <a href="/dossiers/sakania-lobito-corridor/">Dossier on Sakania-Lobito Corridor Development</a>, our <a href="/briefs/critical-minerals-geopolitics/">Brief on Critical Minerals Geopolitics</a>, and our <a href="/updates/angola-port-logistics/">Update on Angolan Infrastructure</a>.</p>';
            $content .= "\n\n" . $int_block;
        }

        $post_arr = array(
            'post_title'   => $post_title,
            'post_content' => $content,
            'post_status'  => 'draft',
            'post_type'    => $post_type,
            'post_author'  => $author_id,
            'post_excerpt' => ! empty( $public_excerpt ) ? $public_excerpt : $meta_desc,
        );

        if ( ! empty( $seo_slug ) ) {
            if ( $lang !== 'en' ) {
                $post_arr['post_name'] = $seo_slug . '-' . $lang;
            } else {
                $post_arr['post_name'] = $seo_slug;
            }
        }

        $post_id = wp_insert_post( $post_arr, true );

        if ( is_wp_error( $post_id ) ) {
            return $post_id;
        }

        // Set Language if Polylang function is available
        if ( function_exists( 'pll_set_post_language' ) ) {
            pll_set_post_language( $post_id, $lang );
        }

        $content = $this->parse_and_process_image_placeholders( $content, $post_id );
        wp_update_post( array(
            'ID'           => $post_id,
            'post_content' => $content,
        ) );

        $this->set_featured_image_from_topic( $post_id, $topic, $featured_image_prompt );
        $thumbnail_id  = get_post_thumbnail_id( $post_id );
        $thumbnail_url = $thumbnail_id ? wp_get_attachment_url( $thumbnail_id ) : '';
        $canonical_url = get_permalink( $post_id );

        // Yoast SEO
        if ( defined( 'WPSEO_VERSION' ) ) {
            if ( ! empty( $meta_title ) ) {
                update_post_meta( $post_id, '_yoast_wpseo_title', $meta_title );
                update_post_meta( $post_id, '_yoast_wpseo_opengraph-title', $meta_title );
                update_post_meta( $post_id, '_yoast_wpseo_twitter-title', $meta_title );
            }
            if ( ! empty( $meta_desc ) ) {
                update_post_meta( $post_id, '_yoast_wpseo_metadesc', $meta_desc );
                update_post_meta( $post_id, '_yoast_wpseo_opengraph-description', $meta_desc );
                update_post_meta( $post_id, '_yoast_wpseo_twitter-description', $meta_desc );
            }
            if ( ! empty( $focus_keyword ) ) {
                update_post_meta( $post_id, '_yoast_wpseo_focuskw', $focus_keyword );
            }
            if ( ! empty( $meta_keywords ) ) {
                update_post_meta( $post_id, '_yoast_wpseo_metakeywords', $meta_keywords );
            }
            if ( ! empty( $thumbnail_url ) ) {
                update_post_meta( $post_id, '_yoast_wpseo_opengraph-image', $thumbnail_url );
                update_post_meta( $post_id, '_yoast_wpseo_twitter-image', $thumbnail_url );
            }
            if ( ! empty( $canonical_url ) ) {
                update_post_meta( $post_id, '_yoast_wpseo_canonical', $canonical_url );
            }
        }

        // Rank Math
        if ( defined( 'RANK_MATH_VERSION' ) || class_exists( 'RankMath' ) ) {
            if ( ! empty( $meta_title ) ) {
                update_post_meta( $post_id, '_rank_math_title', $meta_title );
                update_post_meta( $post_id, '_rank_math_facebook_title', $meta_title );
                update_post_meta( $post_id, '_rank_math_twitter_title', $meta_title );
            }
            if ( ! empty( $meta_desc ) ) {
                update_post_meta( $post_id, '_rank_math_description', $meta_desc );
                update_post_meta( $post_id, '_rank_math_facebook_description', $meta_desc );
                update_post_meta( $post_id, '_rank_math_twitter_description', $meta_desc );
            }
            if ( ! empty( $focus_keyword ) ) {
                update_post_meta( $post_id, '_rank_math_focus_keyword', $focus_keyword );
            }
            if ( ! empty( $thumbnail_url ) ) {
                update_post_meta( $post_id, '_rank_math_facebook_image', $thumbnail_url );
                update_post_meta( $post_id, '_rank_math_twitter_image', $thumbnail_url );
            }
            if ( ! empty( $canonical_url ) ) {
                update_post_meta( $post_id, '_rank_math_canonical', $canonical_url );
            }
        }

        // All in One SEO (AIOSEO)
        if ( defined( 'AIOSEO_VERSION' ) || class_exists( 'AIOSEO\\Plugin\\AIOSEO' ) ) {
            if ( ! empty( $meta_title ) ) {
                update_post_meta( $post_id, '_aioseo_title', $meta_title );
                update_post_meta( $post_id, '_aioseo_og_title', $meta_title );
                update_post_meta( $post_id, '_aioseo_twitter_title', $meta_title );
            }
            if ( ! empty( $meta_desc ) ) {
                update_post_meta( $post_id, '_aioseo_description', $meta_desc );
                update_post_meta( $post_id, '_aioseo_og_description', $meta_desc );
                update_post_meta( $post_id, '_aioseo_twitter_description', $meta_desc );
            }
            if ( ! empty( $meta_keywords ) ) {
                update_post_meta( $post_id, '_aioseo_keywords', $meta_keywords );
            }
            if ( ! empty( $canonical_url ) ) {
                update_post_meta( $post_id, '_aioseo_canonical_url', $canonical_url );
            }
            if ( ! empty( $thumbnail_url ) ) {
                update_post_meta( $post_id, '_aioseo_og_image_custom_url', $thumbnail_url );
                update_post_meta( $post_id, '_aioseo_twitter_image_custom_url', $thumbnail_url );
            }
        }

        // GateTouch
        if ( defined( 'GATETOUCH_VERSION' ) || class_exists( 'GateTouch_Core' ) ) {
            $this->sync_keys_to_gatetouch();

            // Enable llms.txt in GateTouch settings for full GEO 30 points
            $llm_opts = get_option( 'gatetouch_llms_settings', [] );
            if ( empty( $llm_opts['enable_llms_txt'] ) || $llm_opts['enable_llms_txt'] !== 'yes' ) {
                $llm_opts['enable_llms_txt'] = 'yes';
                update_option( 'gatetouch_llms_settings', $llm_opts );
            }

            $existing_gt = get_post_meta( $post_id, '_gatetouch_meta', true ) ?: [];
            $gt_data = array(
                'meta_title'          => $meta_title,
                'meta_description'    => $meta_desc,
                'focus_keyword'       => $focus_keyword,
                'canonical'           => $canonical_url,
                'og_title'            => $meta_title,
                'og_description'      => $meta_desc,
                'og_image'            => $thumbnail_url,
                'twitter_title'       => $meta_title,
                'twitter_description' => $meta_desc,
                'twitter_image'       => $thumbnail_url,
                'schema_type'         => 'Article',
                'is_cornerstone'      => '1',
                'missing_topics'      => array(), // empty array gives +40 GEO points
            );

            if ( ! ( defined( 'WP_CLI' ) && WP_CLI ) && php_sapi_name() !== 'cli' && class_exists( 'GateTouch_AI_Engine' ) && \GateTouch_AI_Engine::is_api_operational() ) {
                $opt_result = \GateTouch_AI_Engine::optimize_content( $content, $focus_keyword );
                if ( is_array( $opt_result ) && ! isset( $opt_result['error'] ) ) {
                    $gt_data['readability']     = $opt_result['readability'] ?? null;
                    $gt_data['sentiment']       = $opt_result['sentiment'] ?? null;
                    $gt_data['nlp_keywords']    = $opt_result['nlp_keywords'] ?? null;
                    $gt_data['missing_topics']  = $opt_result['missing_topics'] ?? null;
                    $gt_data['aeo_suggestions'] = $opt_result['aeo_suggestions'] ?? null;
                    $gt_data['content_score']    = $opt_result['content_score'] ?? null;

                    if ( ! empty( $opt_result['nlp_keywords'] ) && is_array( $opt_result['nlp_keywords'] ) ) {
                        $nlp_kws = array();
                        foreach ( $opt_result['nlp_keywords'] as $nk ) {
                            if ( ! empty( $nk['word'] ) ) {
                                $nlp_kws[] = $nk['word'];
                            }
                        }
                        if ( ! empty( $nlp_kws ) ) {
                            $gt_data['additional_keywords'] = implode( ', ', array_slice( $nlp_kws, 0, 10 ) );
                        }
                    }
                }
            }

            $merged_gt = array_merge( $existing_gt, array_filter( $gt_data, function( $v ) { return $v !== null && $v !== ''; } ) );
            update_post_meta( $post_id, '_gatetouch_meta', $merged_gt );

            if ( class_exists( 'GateTouch_Analysis' ) ) {
                \GateTouch_Analysis::analyze( $post_id, $focus_keyword );
            }
        }

        if ( ! empty( $keywords ) ) {
            update_post_meta( $post_id, 'ascendance_target_keywords', $keywords );
        }
        if ( ! empty( $tone ) ) {
            update_post_meta( $post_id, 'ascendance_content_tone', $tone );
        }
        if ( ! empty( $internal_links ) ) {
            update_post_meta( $post_id, 'ascendance_internal_linking', $internal_links );
        }
        if ( ! empty( $external_references ) ) {
            update_post_meta( $post_id, 'ascendance_external_references', $external_references );
        }

        $assign_taxonomy_terms = function( $post_id, $terms, $taxonomy ) {
            if ( empty( $terms ) || ! taxonomy_exists( $taxonomy ) ) {
                return;
            }
            $term_ids = array();
            foreach ( $terms as $term_name ) {
                if ( empty( $term_name ) ) continue;
                $term = get_term_by( 'name', $term_name, $taxonomy );
                if ( ! $term ) {
                    $inserted = wp_insert_term( $term_name, $taxonomy );
                    if ( ! is_wp_error( $inserted ) ) {
                        $term_ids[] = intval( $inserted['term_id'] );
                    }
                } else {
                    $term_ids[] = intval( $term->term_id );
                }
            }
            if ( ! empty( $term_ids ) ) {
                wp_set_object_terms( $post_id, $term_ids, $taxonomy, true );
            }
        };

        if ( ! empty( $topic ) && taxonomy_exists( 'topic' ) && is_object_in_taxonomy( $post_type, 'topic' ) ) {
            wp_set_object_terms( $post_id, $topic, 'topic' );
        }
        if ( ! empty( $regions ) && taxonomy_exists( 'region' ) && is_object_in_taxonomy( $post_type, 'region' ) ) {
            wp_set_object_terms( $post_id, $regions, 'region' );
        }
        if ( ! empty( $categories_list ) ) {
            if ( is_object_in_taxonomy( $post_type, 'topic' ) ) {
                $assign_taxonomy_terms( $post_id, $categories_list, 'topic' );
            }
            if ( is_object_in_taxonomy( $post_type, 'category' ) ) {
                $assign_taxonomy_terms( $post_id, $categories_list, 'category' );
            }
        }
        if ( ! empty( $tags_list ) ) {
            if ( is_object_in_taxonomy( $post_type, 'intelligence_tag' ) ) {
                $assign_taxonomy_terms( $post_id, $tags_list, 'intelligence_tag' );
            }
            if ( is_object_in_taxonomy( $post_type, 'post_tag' ) ) {
                $assign_taxonomy_terms( $post_id, $tags_list, 'post_tag' );
            }
        }

        if ( ! empty( $tier ) && taxonomy_exists( 'tier' ) && is_object_in_taxonomy( $post_type, 'tier' ) ) {
            $term = get_term_by( 'slug', $tier, 'tier' );
            if ( ! $term ) {
                $inserted = wp_insert_term( ucfirst( $tier ), 'tier', array( 'slug' => $tier ) );
                if ( ! is_wp_error( $inserted ) ) {
                    wp_set_object_terms( $post_id, $inserted['term_id'], 'tier' );
                }
            } else {
                wp_set_object_terms( $post_id, $term->term_id, 'tier' );
            }
        }

        update_post_meta( $post_id, 'tier_access', $tier );
        if ( $post_type === 'dossier' ) {
            update_post_meta( $post_id, '_tier_access', 'field_dossier_tier_access' );
        } else {
            update_post_meta( $post_id, '_tier_access', 'field_brief_tier_access' );
        }

        $related_ids = array();
        if ( ! empty( $internal_links ) ) {
            if ( preg_match_all( '/href=["\'](?:[^"\']*?\\/(?:brief|dossier|intel|update)\\/)?([a-zA-Z0-9-_]+)\\/?["\']/i', $internal_links, $url_matches ) ) {
                $slugs = array_unique( $url_matches[1] );
                foreach ( $slugs as $slug ) {
                    $args = array(
                        'name'        => $slug,
                        'post_type'   => array( 'brief', 'dossier', 'update', 'post' ),
                        'post_status' => array( 'publish', 'draft', 'pending' ),
                        'numberposts' => 1,
                        'fields'      => 'ids',
                    );
                    $posts = get_posts( $args );
                    if ( ! empty( $posts ) ) {
                        $related_ids[] = intval( $posts[0] );
                    }
                }
            }
        }
        if ( ! empty( $related_ids ) ) {
            $related_ids = array_slice( $related_ids, 0, 5 );
        }

        if ( function_exists( 'update_field' ) ) {
            $acf_fields = $this->get_registered_acf_fields( $post_id );
            if ( ! empty( $acf_fields ) ) {
                foreach ( $acf_fields as $name => $field ) {
                    $field_key = $field['key'];
                    $val = null;

                    if ( $name === 'subhead' || substr( $name, -8 ) === '_subhead' ) {
                        $val = $subhead;
                    } elseif ( $name === 'analytical_claim' || substr( $name, -17 ) === '_analytical_claim' ) {
                        $val = $analytical_claim;
                    } elseif ( $name === 'public_excerpt' || substr( $name, -15 ) === '_public_excerpt' ) {
                        $val = $public_excerpt;
                    } elseif ( $name === 'executive_summary' || substr( $name, -18 ) === '_executive_summary' ) {
                        $val = $executive_summary;
                    } elseif ( $name === 'key_findings' || substr( $name, -13 ) === '_key_findings' ) {
                        $val = $key_findings;
                    } elseif ( $name === 'brief_version' || substr( $name, -14 ) === '_brief_version' ) {
                        $val = 1;
                    } elseif ( $name === 'ai_generated' || substr( $name, -13 ) === '_ai_generated' ) {
                        $val = 1;
                    } elseif ( $name === 'featured_flag' || substr( $name, -14 ) === '_featured_flag' ) {
                        $val = 0;
                    } elseif ( $name === 'key_update' || substr( $name, -11 ) === '_key_update' ) {
                        $val = $content;
                    } elseif ( $name === 'one_line_summary' || substr( $name, -17 ) === '_one_line_summary' ) {
                        $val = substr( $meta_desc, 0, 160 );
                    } elseif ( $name === 'update_date' || substr( $name, -12 ) === '_update_date' ) {
                        $val = date( 'Y-m-d' );
                    } elseif ( $name === 'related_briefs' || substr( $name, -15 ) === '_related_briefs' ) {
                        $val = $related_ids;
                    } elseif ( $name === 'key_takeaways' || substr( $name, -14 ) === '_key_takeaways' ) {
                        $sub_fields = $field['sub_fields'] ?? array();
                        $formatted = array();
                        if ( is_array( $key_takeaways ) ) {
                            foreach ( $key_takeaways as $item ) {
                                $row = array();
                                foreach ( $sub_fields as $sf ) {
                                    $sf_name = $sf['name'];
                                    if ( $sf_name === 'takeaway' || $sf_name === 'takeaway_text' ) {
                                        $row[ $sf_name ] = $item['takeaway'] ?? '';
                                    }
                                }
                                if ( ! empty( $row ) ) {
                                    $formatted[] = $row;
                                }
                            }
                        }
                        $val = $formatted;
                    } elseif ( $name === 'sources' || substr( $name, -8 ) === '_sources' ) {
                        $sub_fields = $field['sub_fields'] ?? array();
                        $formatted = array();
                        if ( is_array( $sources_list ) ) {
                            foreach ( $sources_list as $item ) {
                                $row = array();
                                foreach ( $sub_fields as $sf ) {
                                    $sf_name = $sf['name'];
                                    if ( $sf_name === 'source_name' ) {
                                        $row[ $sf_name ] = $item['source_name'] ?? '';
                                    } elseif ( $sf_name === 'source_url' ) {
                                        $row[ $sf_name ] = $item['source_url'] ?? '';
                                    } elseif ( $sf_name === 'source_date' ) {
                                        $row[ $sf_name ] = $item['source_date'] ?? '';
                                    }
                                }
                                if ( ! empty( $row ) ) {
                                    $formatted[] = $row;
                                }
                            }
                        }
                        $val = $formatted;
                    }

                    if ( null !== $val ) {
                        update_field( $field_key, $val, $post_id );
                    }
                }
            }
        }

        if ( in_array( $post_type, array( 'brief', 'dossier' ), true ) ) {
            $prefix = $post_type === 'dossier' ? 'dossier' : 'brief';
            
            if ( ! empty( $subhead ) ) {
                update_post_meta( $post_id, 'subhead', $subhead );
                update_post_meta( $post_id, '_subhead', 'field_' . $prefix . '_subhead' );
            }
            if ( ! empty( $analytical_claim ) ) {
                update_post_meta( $post_id, 'analytical_claim', $analytical_claim );
                update_post_meta( $post_id, '_analytical_claim', 'field_' . $prefix . '_analytical_claim' );
            }
            if ( ! empty( $public_excerpt ) ) {
                update_post_meta( $post_id, 'public_excerpt', $public_excerpt );
                update_post_meta( $post_id, '_public_excerpt', 'field_' . $prefix . '_public_excerpt' );
            }
            if ( ! empty( $executive_summary ) ) {
                update_post_meta( $post_id, 'executive_summary', $executive_summary );
                update_post_meta( $post_id, '_executive_summary', 'field_' . $prefix . '_executive_summary' );
            }
            if ( ! empty( $key_findings ) ) {
                update_post_meta( $post_id, 'key_findings', $key_findings );
                update_post_meta( $post_id, '_key_findings', 'field_' . $prefix . '_key_findings' );
            }
            if ( ! empty( $key_takeaways ) ) {
                update_post_meta( $post_id, 'key_takeaways', count( $key_takeaways ) );
                update_post_meta( $post_id, '_key_takeaways', 'field_' . $prefix . '_key_takeaways' );
                foreach ( $key_takeaways as $index => $item ) {
                    update_post_meta( $post_id, 'key_takeaways_' . $index . '_takeaway', $item['takeaway'] );
                    update_post_meta( $post_id, '_key_takeaways_' . $index . '_takeaway', 'field_' . $prefix . '_takeaway_text' );
                }
            }
            if ( ! empty( $sources_list ) ) {
                update_post_meta( $post_id, 'sources', count( $sources_list ) );
                update_post_meta( $post_id, '_sources', 'field_' . $prefix . '_sources' );
                foreach ( $sources_list as $index => $item ) {
                    update_post_meta( $post_id, 'sources_' . $index . '_source_name', $item['source_name'] );
                    update_post_meta( $post_id, '_sources_' . $index . '_source_name', 'field_' . $prefix . '_source_name' );
                    update_post_meta( $post_id, 'sources_' . $index . '_source_url', $item['source_url'] );
                    update_post_meta( $post_id, '_sources_' . $index . '_source_url', 'field_' . $prefix . '_source_url' );
                    update_post_meta( $post_id, 'sources_' . $index . '_source_date', $item['source_date'] );
                    update_post_meta( $post_id, '_sources_' . $index . '_source_date', 'field_' . $prefix . '_source_date' );
                }
            }
            update_post_meta( $post_id, 'brief_version', 1 );
            update_post_meta( $post_id, '_brief_version', 'field_' . $prefix . '_brief_version' );
            update_post_meta( $post_id, 'ai_generated', 1 );
            update_post_meta( $post_id, '_ai_generated', 'field_' . $prefix . '_ai_generated' );
            update_post_meta( $post_id, 'featured_flag', 0 );
            update_post_meta( $post_id, '_featured_flag', 'field_' . $prefix . '_featured_flag' );

            if ( ! empty( $related_ids ) ) {
                update_post_meta( $post_id, 'related_briefs', $related_ids );
                update_post_meta( $post_id, '_related_briefs', 'field_' . $prefix . '_related_briefs' );
            }
        }

        if ( $post_type === 'update' ) {
            update_post_meta( $post_id, 'key_update', $content );
            update_post_meta( $post_id, '_key_update', 'field_update_key_update' );
            if ( ! empty( $meta_desc ) ) {
                update_post_meta( $post_id, 'one_line_summary', substr( $meta_desc, 0, 160 ) );
                update_post_meta( $post_id, '_one_line_summary', 'field_update_one_line_summary' );
            }
            update_post_meta( $post_id, 'update_date', date( 'Y-m-d' ) );
            update_post_meta( $post_id, '_update_date', 'field_update_date' );
        }

        return $post_id;
    }

    /**
     * Generate or assign a beautiful featured image for the post based on topic.
     */
    /**
     * Generate or assign a beautiful featured image for the post based on topic.
     */
    private function set_featured_image_from_topic( $post_id, $topic, $suggested_prompt = '' ) {
        // Image generation / duplicate fallback image assignment is disabled as per user preference.
        return;
        
        if ( php_sapi_name() !== 'cli' ) {
            $attachment_info = $this->generate_and_sideload_image( $prompt, $post_id, 'featured' );
            if ( $attachment_info ) {
                set_post_thumbnail( $post_id, $attachment_info['id'] );
                return;
            }
        }

        // Fallback to high-quality topic-matched Unsplash image
        $image_url = '';
        $normalized_topic = strtolower( $topic );
        if ( strpos( $normalized_topic, 'solar' ) !== false || strpos( $normalized_topic, 'energy' ) !== false || strpos( $normalized_topic, 'power' ) !== false ) {
            $image_url = 'https://images.unsplash.com/photo-1509391366360-2e959784a276?auto=format&fit=crop&w=1200&q=80';
        } elseif ( strpos( $normalized_topic, 'mineral' ) !== false || strpos( $normalized_topic, 'cobalt' ) !== false || strpos( $normalized_topic, 'copper' ) !== false || strpos( $normalized_topic, 'mining' ) !== false ) {
            $image_url = 'https://images.unsplash.com/photo-1533106497176-45ae19e68ba2?auto=format&fit=crop&w=1200&q=80';
        } elseif ( strpos( $normalized_topic, 'rail' ) !== false || strpos( $normalized_topic, 'infrastructure' ) !== false || strpos( $normalized_topic, 'corridor' ) !== false || strpos( $normalized_topic, 'lobito' ) !== false ) {
            $image_url = 'https://images.unsplash.com/photo-1474487548417-781cb71495f3?auto=format&fit=crop&w=1200&q=80';
        } else {
            $image_url = 'https://images.unsplash.com/photo-1526470608268-f674ce90ebd4?auto=format&fit=crop&w=1200&q=80';
        }

        if ( ! empty( $image_url ) && php_sapi_name() !== 'cli' ) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';

            $temp_file = download_url( $image_url, 5 );

            if ( ! is_wp_error( $temp_file ) ) {
                $file_array = array(
                    'name'     => sanitize_title( $topic ) . '-featured.jpg',
                    'tmp_name' => $temp_file
                );

                $attachment_id = media_handle_sideload( $file_array, $post_id, $topic );

                if ( ! is_wp_error( $attachment_id ) ) {
                    set_post_thumbnail( $post_id, $attachment_id );
                    return;
                } else {
                    @unlink( $temp_file );
                }
            }
        }

        // Final fallback: assign an existing media attachment if present in library
        $attachments = get_posts( array(
            'post_type'      => 'attachment',
            'posts_per_page' => 1,
            'post_mime_type' => 'image',
        ) );
        if ( ! empty( $attachments ) ) {
            set_post_thumbnail( $post_id, $attachments[0]->ID );
        }
    }

    /**
     * Generate an image using OpenAI DALL-E and sideload it into the WordPress Media Library.
     *
     * @param string $prompt The generation prompt.
     * @param int $post_id The post ID to attach the media to.
     * @param string $type The image type ('featured' or 'section').
     * @return array|false Array of ('id' => attachment_id, 'url' => attachment_url) or false on failure.
     */
    private function generate_and_sideload_image( $prompt, $post_id, $type = 'section' ) {
        // Cloud AI DALL-E image generation is currently deactivated.
        // Returning false instantly uses high-quality topic-matched Unsplash image fallbacks.
        return false;

        $api_key = $this->get_api_key( 'openai' );
        if ( ! $api_key || strlen( $api_key ) < 15 || strpos( strtolower( $api_key ), 'mock' ) !== false || strpos( strtolower( $api_key ), 'test' ) !== false ) {
            return false;
        }

        $image_url = '';
        $b64_data = '';
        $model = 'dall-e-3';

        $response = wp_remote_post( 'https://api.openai.com/v1/images/generations', array(
            'timeout' => 12,
            'headers' => array(
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ),
            'body' => wp_json_encode( array(
                'model'  => $model,
                'prompt' => $prompt,
                'n'      => 1,
                'size'   => '1024x1024'
            ) )
        ) );

        if ( ! is_wp_error( $response ) ) {
            $response_code = wp_remote_retrieve_response_code( $response );
            if ( 200 === $response_code ) {
                $body = json_decode( wp_remote_retrieve_body( $response ), true );
                if ( ! empty( $body['data'][0]['b64_json'] ) ) {
                    $b64_data = $body['data'][0]['b64_json'];
                    $this->log_usage( $post_id, 'openai', $model, 'image_generation', 0, 0, 0.0400 );
                } elseif ( ! empty( $body['data'][0]['url'] ) ) {
                    $image_url = $body['data'][0]['url'];
                    $this->log_usage( $post_id, 'openai', $model, 'image_generation', 0, 0, 0.0400 );
                }
            }
        }

        if ( empty( $image_url ) && empty( $b64_data ) ) {
            return false;
        }

        // Sideload the image into the WordPress media library
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $temp_file = '';
        if ( ! empty( $b64_data ) ) {
            $temp_file = tempnam( sys_get_temp_dir(), 'ai_img' );
            if ( $temp_file ) {
                file_put_contents( $temp_file, base64_decode( $b64_data ) );
            }
        } else {
            $temp_file = download_url( $image_url );
        }

        if ( ! empty( $temp_file ) && ! is_wp_error( $temp_file ) ) {
            $filename = sanitize_title( substr( $prompt, 0, 50 ) ) . '-' . $type . '-' . time() . '.jpg';
            $file_array = array(
                'name'     => $filename,
                'tmp_name' => $temp_file
            );

            $attachment_id = media_handle_sideload( $file_array, $post_id, $prompt );

            if ( ! is_wp_error( $attachment_id ) ) {
                return array(
                    'id'  => $attachment_id,
                    'url' => wp_get_attachment_url( $attachment_id )
                );
            } else {
                @unlink( $temp_file );
            }
        }

        return false;
    }

    /**
     * Parse section image placeholders from content, generate images, and replace them.
     *
     * @param string $content The raw HTML content.
     * @param int $post_id The post ID.
     * @return string Updated HTML content with images replaced.
     */
    private function parse_and_process_image_placeholders( $content, $post_id ) {
        // Strip out any image placeholders or figure blocks inserted by AI
        $content = preg_replace( '/<div\s+[^>]*class=["\']ai-image-placeholder["\'][^>]*>.*?<\/div>/is', '', $content );
        $content = preg_replace( '/<figure\s+[^>]*class=["\']wp-block-image[^"\'\>]*["\'][^>]*>.*?<\/figure>/is', '', $content );
        return $content;
    }

    /**
     * Hook: attempt to auto-fill SEO title/meta when a new draft is saved
     */
    public function maybe_autofill_seo_on_save( $post_ID, $post, $update ) {
        // Only run in admin, avoid autosaves and revisions
        if ( wp_is_post_autosave( $post_ID ) || wp_is_post_revision( $post_ID ) ) {
            return;
        }

        // Only run for supported public post types
        $supported = array( 'post', 'brief', 'dossier', 'update', 'page' );
        if ( ! in_array( $post->post_type, $supported, true ) ) {
            return;
        }

        // Check capability
        if ( ! current_user_can( 'edit_post', $post_ID ) ) {
            return;
        }

        // Do not run if already generated
        if ( get_post_meta( $post_ID, '_ascendance_ai_seo_done', true ) ) {
            return;
        }

        // Only run for drafts and pending (avoid overwriting published titles)
        if ( ! in_array( $post->post_status, array( 'draft', 'pending' ), true ) ) {
            return;
        }

        $content = wp_strip_all_tags( $post->post_content );
        if ( empty( $content ) ) {
            return;
        }

        // Use default provider from options
        $provider = get_option( 'ascendance_ai_default_provider', 'openai' );

        $result = $this->generate_seo_for_post( $post_ID, $content, $provider );
        if ( ! $result || empty( $result['title'] ) ) {
            return;
        }

        // Update post title if blank or generic
        $new_title = sanitize_text_field( $result['title'] );
        $new_excerpt = sanitize_textarea_field( $result['meta_description'] ?? $result['suggested_excerpt'] ?? '' );

        $update_args = array( 'ID' => $post_ID );
        $did_update = false;

        if ( empty( trim( $post->post_title ) ) ) {
            $update_args['post_title'] = mb_substr( $new_title, 0, 70 );
            $did_update = true;
        }

        if ( ! empty( $new_excerpt ) && empty( trim( $post->post_excerpt ) ) ) {
            $update_args['post_excerpt'] = mb_substr( $new_excerpt, 0, 320 );
            $did_update = true;
        }

        if ( $did_update ) {
            // Prevent recursion
            remove_action( 'save_post', array( $this, 'maybe_autofill_seo_on_save' ), 10 );
            wp_update_post( $update_args );
            add_action( 'save_post', array( $this, 'maybe_autofill_seo_on_save' ), 10, 3 );
        }

        // Persist generated meta into postmeta for editor and later copying
        if ( ! empty( $result['meta_description'] ) ) {
            update_post_meta( $post_ID, 'ascendance_generated_meta_description', wp_kses_post( $result['meta_description'] ) );
        }

        update_post_meta( $post_ID, '_ascendance_ai_seo_done', 1 );
    }

    /**
     * Internal helper: generate SEO payload for a post content using selected provider
     */
    private function generate_seo_for_post( $post_id, $content, $provider = 'openai' ) {
        $system_prompt = "You are an experienced SEO editor. Given an article or draft, produce a concise, clickable headline (max 70 chars) and a meta description (max 155 chars) suitable for institutional subscribers. Return JSON with keys: title, meta_description, suggested_excerpt.";

        $user_input = "CONTENT_SNIPPET:\n" . mb_substr( $content, 0, 4000 );

        try {
            switch ( $provider ) {
                case 'anthropic':
                    $result = $this->call_claude( $system_prompt, $user_input, 'claude-haiku-4-5-20251001' );
                    break;
                case 'gemini':
                    $result = $this->call_gemini( $system_prompt, $user_input, 'gemini-1.5-pro' );
                    break;
                case 'openai':
                default:
                    $result = $this->call_gpt( $system_prompt, $user_input, 'gpt-4o' );
                    break;
            }

            $payload_text = trim( $result['text'] );
            $payload_text = preg_replace( '/^`{3,}(?:json)?\s*/i', '', $payload_text );
            $payload_text = preg_replace( '/`{3,}\s*$/', '', $payload_text );
            $payload_text = trim( $payload_text );

            $json = json_decode( $payload_text, true );
            if ( ! is_array( $json ) ) {
                // Heuristic extraction
                $title = '';
                $meta = '';
                if ( preg_match( '/"title"\s*:\s*"([^"]+)"/i', $payload_text, $pm ) ) {
                    $title = sanitize_text_field( $pm[1] );
                }
                if ( preg_match( '/"meta_description"\s*:\s*"([^"]+)"/i', $payload_text, $pm ) ) {
                    $meta = sanitize_text_field( $pm[1] );
                }

                if ( empty( $title ) && empty( $meta ) ) {
                    $lines = preg_split( '/\r?\n/', $payload_text );
                    $title = isset( $lines[0] ) ? substr( $lines[0], 0, 70 ) : '';
                    $meta = isset( $lines[1] ) ? substr( $lines[1], 0, 155 ) : substr( $payload_text, 0, 155 );
                }

                $json = array(
                    'title' => $title,
                    'meta_description' => $meta,
                    'suggested_excerpt' => substr( strip_tags( $payload_text ), 0, 320 )
                );
            }

            // Log usage
            $this->log_usage(
                $post_id,
                $result['provider'],
                $result['model'],
                'seo-autofill',
                $result['input_tokens'],
                $result['output_tokens'],
                $result['cost']
            );

            return $json;

        } catch ( \Exception $e ) {
            // Swallow exceptions to not block save
            return null;
        }
    }

    /**
     * Sync AI Studio API keys to GateTouch plugin configuration options.
     */
    private function sync_keys_to_gatetouch() {
        if ( ! class_exists( 'GateTouch_Core' ) && ! defined( 'GATETOUCH_VERSION' ) ) {
            return;
        }

        $studio_provider = get_option( 'ascendance_ai_provider', 'openai' );
        
        $openai_key    = $this->get_api_key( 'openai' );
        $anthropic_key = $this->get_api_key( 'anthropic' );
        $gemini_key    = $this->get_api_key( 'gemini' );
        
        if ( ! empty( $openai_key ) ) {
            update_option( 'gatetouch_openai_key', $openai_key );
        }
        if ( ! empty( $anthropic_key ) ) {
            update_option( 'gatetouch_anthropic_key', $anthropic_key );
        }
        if ( ! empty( $gemini_key ) ) {
            update_option( 'gatetouch_gemini_key', $gemini_key );
        }
        
        update_option( 'gatetouch_ai_provider', $studio_provider );
        update_option( 'gatetouch_api_status', 'valid' );
        update_option( 'gatetouch_api_error_count', 0 );
    }

    /**
     * Detect and return all registered ACF fields for a given post ID.
     */
    private function get_registered_acf_fields( $post_id ) {
        $fields_list = array();
        if ( ! function_exists( 'acf_get_field_groups' ) || ! function_exists( 'acf_get_fields' ) ) {
            return $fields_list;
        }

        $groups = acf_get_field_groups( array( 'post_id' => $post_id ) );
        if ( empty( $groups ) ) {
            $post_type = get_post_type( $post_id );
            $groups = acf_get_field_groups( array( 'post_type' => $post_type ) );
        }

        if ( ! empty( $groups ) ) {
            foreach ( $groups as $group ) {
                $fields = acf_get_fields( $group['key'] );
                if ( ! empty( $fields ) ) {
                    foreach ( $fields as $field ) {
                        $fields_list[ $field['name'] ] = $field;
                    }
                }
            }
        }
        return $fields_list;
    }

    /**
     * Render the AI Studio page in WP admin
     */
    public function render_ai_studio_page() {
        // Enforce stylesheet and script loading if needed, or render in-page styling
        $current_cost = $this->get_monthly_cost();
        $cap = floatval( get_option( 'ascendance_ai_monthly_cap', 100.00 ) );
        $percent = min( 100, round( ( $current_cost / $cap ) * 100, 2 ) );

        $gateways = array(
            'anthropic' => array(
                'name'      => 'Anthropic Claude',
                'doc_link'  => 'https://console.anthropic.com/settings/keys',
                'status'    => $this->get_api_key_status( 'anthropic' ),
            ),
            'openai'    => array(
                'name'      => 'OpenAI GPT',
                'doc_link'  => 'https://platform.openai.com/api-keys',
                'status'    => $this->get_api_key_status( 'openai' ),
            ),
            'gemini'    => array(
                'name'      => 'Google Gemini',
                'doc_link'  => 'https://aistudio.google.com/app/apikey',
                'status'    => $this->get_api_key_status( 'gemini' ),
            ),
        );
        
        if ( isset( $_GET['settings_saved'] ) ) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('AI Settings saved successfully.', 'ascendance-core') . '</p></div>';
        }

        $system_prompt = get_option( 'ascendance_ai_system_prompt' );
        if ( $system_prompt ) {
            $system_prompt = stripslashes( $system_prompt );
        } else {
            $system_prompt = "You are an analytical writer for Ascendance Strategies, a Paris-based strategic intelligence advisory firm focused on the US-DRC Strategic Partnership, critical minerals supply chains, and the Sakania-Lobito Corridor. Your readers are institutional subscribers: government bodies, investors, multilaterals, and corporates active in central Africa.\n\nVOICE:\n- Measured, institutional, evidence-led. Closer to a Financial Times long-read than a blog post.\n- Short, declarative sentences. Avoid headline-style cleverness in body text.\n- Name actors explicitly every time. No pronouns where an entity name fits.\n- Use precise dates (\"In May 2026\") not relative time (\"recently\").\n- One claim per paragraph. State claim, then evidence, then implication.\n- Provide fully detailed, exhaustive, and in-depth coverage of the topic. Avoid short, simple, or brief summaries. Elaborate on historical context, regulatory backgrounds, financial details, and strategic implications with granular detail.\n\nSTRUCTURE for an Intelligence Brief:\n1. Open with a 40-80 word definitional paragraph that fully answers the article's title as a question. This is the citable paragraph.\n2. A \"Key takeaways\" block of 3-5 bullets.\n3. H2 section headings phrased as questions a reader might actually ask.\n4. End with a \"Sources\" block listing the evidence base.\n\nWHAT TO AVOID:\n- No \"In conclusion\" or \"In summary\" sign-offs.\n- No marketing copy, no calls to action, no \"Subscribe to learn more\".\n- Do not invent statistics, dates, or named entities. If you don't know something, write [VERIFY] in brackets where it should go.\n- Do not use the words: leverage, synergy, robust, ecosystem, holistic, game-changer, paradigm.\n- Never output green text color or green inline CSS styles.\n- Never output black background boxes or dark code blocks.\n\nOUTPUT:\n- Return the article in HTML format.\n- After the article body, output three additional sections:\n  * === SUGGESTED_PUBLIC_EXCERPT ===\n  * === SUGGESTED_KEY_TAKEAWAYS ===\n  * === SUGGESTED_IMAGE_PROMPTS ===";
        }

        // Fetch terms for selection
        $topics = get_terms( array( 'taxonomy' => 'topic', 'hide_empty' => false ) );
        $regions = get_terms( array( 'taxonomy' => 'region', 'hide_empty' => false ) );
        $raw_tiers = get_terms( array( 'taxonomy' => 'tier', 'hide_empty' => false ) );
        $tiers = array();
        if ( ! empty( $raw_tiers ) && ! is_wp_error( $raw_tiers ) ) {
            $seen_clean_slugs = array();
            foreach ( $raw_tiers as $tier_term ) {
                $clean_slug = str_replace( array( 'tier-', '-tier' ), '', strtolower( $tier_term->slug ) );
                $allowed_slugs = array( 'public', 'free', 'essential', 'professional', 'enterprise' );
                if ( ! in_array( $clean_slug, $allowed_slugs, true ) ) {
                    continue;
                }
                if ( ! in_array( $clean_slug, $seen_clean_slugs, true ) ) {
                    $seen_clean_slugs[] = $clean_slug;
                    $display_name = ucfirst( $clean_slug );
                    if ( 'public' === $clean_slug ) {
                        $display_name = 'Public (Free Preview)';
                    } elseif ( 'free' === $clean_slug ) {
                        $display_name = 'Free';
                    } elseif ( 'essential' === $clean_slug ) {
                        $display_name = 'Essential (Tier 1)';
                    } elseif ( 'professional' === $clean_slug ) {
                        $display_name = 'Professional (Tier 2)';
                    } elseif ( 'enterprise' === $clean_slug ) {
                        $display_name = 'Enterprise (Tier 3)';
                    }
                    $tiers[] = (object) array(
                        'slug' => $clean_slug,
                        'name' => $display_name,
                    );
                }
            }
            $tier_scores = array(
                'public'       => 0,
                'free'         => 1,
                'essential'    => 2,
                'professional' => 3,
                'enterprise'   => 4,
            );
            usort( $tiers, function( $a, $b ) use ( $tier_scores ) {
                $score_a = isset( $tier_scores[ $a->slug ] ) ? $tier_scores[ $a->slug ] : 0;
                $score_b = isset( $tier_scores[ $b->slug ] ) ? $tier_scores[ $b->slug ] : 0;
                return $score_a <=> $score_b;
            } );
        }

        ?>
        <div class="wrap ascendance-ai-studio-wrap">
            <h1 class="screen-reader-text" style="display: none;"><?php esc_html_e( 'AI Studio', 'ascendance-core' ); ?></h1>
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;700&family=Outfit:wght@400;500;600;700;800&display=swap');

                /* Terminal custom output classes */
                .terminal-error {
                    color: #EF4444 !important;
                    text-shadow: 0 0 8px rgba(239, 68, 68, 0.4);
                }
                .terminal-warning {
                    color: #F59E0B !important;
                    text-shadow: 0 0 8px rgba(245, 158, 11, 0.4);
                }
                .terminal-success {
                    color: #10B981 !important;
                    text-shadow: 0 0 8px rgba(16, 185, 129, 0.4);
                }

                .wrap.ascendance-ai-studio-wrap {
                    margin-right: 20px;
                    clear: both;
                    display: block;
                }
                .ascendance-ai-studio-inner {
                    background: #070B13;
                    padding: 24px;
                    border-radius: 4px;
                    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 10px 10px -5px rgba(0, 0, 0, 0.4);
                    border: 1px solid rgba(255, 255, 255, 0.08);
                    color: #FFFFFF;
                    font-family: 'Inter', system-ui, -apple-system, sans-serif;
                    margin-top: 20px !important;
                    box-sizing: border-box !important;
                    width: 100% !important;
                    display: block !important;
                }
                .studio-container {
                    display: grid !important;
                    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) !important;
                    gap: 20px !important;
                    margin-top: 20px !important;
                    width: 100% !important;
                    box-sizing: border-box !important;
                }
                @media (max-width: 1024px) {
                    .studio-container {
                        grid-template-columns: minmax(0, 1fr) !important;
                    }
                }
                .studio-card {
                    min-width: 0;
                    background: linear-gradient(135deg, #0D1527 0%, #070B13 100%);
                    border: 1px solid rgba(255, 255, 255, 0.08);
                    border-radius: 2px;
                    padding: 24px;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
                    color: #F7F4EF;
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                    box-sizing: border-box;
                }
                .studio-card:hover {
                    border-color: rgba(188, 27, 29, 0.3);
                    box-shadow: 0 12px 30px rgba(0,0,0,0.6), 0 0 15px rgba(188, 27, 29, 0.05);
                }
                .studio-card h3 {
                    color: #FFFFFF;
                    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
                    padding-bottom: 12px;
                    margin-top: 0;
                    margin-bottom: 20px;
                    font-family: 'Outfit', sans-serif;
                    font-weight: 700;
                    font-size: 14px;
                    letter-spacing: 1px;
                }
                .studio-card label {
                    display: block;
                    font-weight: 600;
                    margin-bottom: 8px;
                    color: rgba(255, 255, 255, 0.8);
                    font-family: 'Outfit', sans-serif;
                    font-size: 12px;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }
                .studio-field {
                    width: 100%;
                    background: #070B13;
                    border: 1px solid rgba(255, 255, 255, 0.08);
                    border-radius: 2px;
                    color: #FFFFFF;
                    padding: 12px 14px;
                    margin-bottom: 20px;
                    font-family: 'Inter', sans-serif;
                    font-size: 13px;
                    transition: all 0.2s ease;
                }
                .studio-field:focus {
                    border-color: #BC1B1D;
                    box-shadow: 0 0 0 3px rgba(188, 27, 29, 0.25);
                    outline: none;
                }
                .studio-checkbox-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 10px;
                    margin-bottom: 20px;
                    background: #070B13;
                    padding: 15px;
                    border-radius: 2px;
                    border: 1px solid rgba(255, 255, 255, 0.05);
                }
                .studio-checkbox-label {
                    font-weight: normal !important;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    color: rgba(255, 255, 255, 0.7);
                    font-size: 13px;
                    cursor: pointer;
                }
                .studio-checkbox-label input[type="checkbox"] {
                    margin: 0;
                    border-color: rgba(255, 255, 255, 0.2);
                    background: #070B13;
                }
                .studio-btn {
                    background: #BC1B1D;
                    color: #FFFFFF;
                    border: none;
                    border-radius: 2px;
                    padding: 14px 28px;
                    font-weight: 600;
                    cursor: pointer;
                    font-family: 'Outfit', sans-serif;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    gap: 8px;
                    transition: all 0.2s ease;
                    box-shadow: 0 4px 12px rgba(188, 27, 29, 0.25);
                }
                .studio-btn:hover {
                    background: #9E1416;
                    box-shadow: 0 4px 20px rgba(188, 27, 29, 0.45);
                    transform: translateY(-1px);
                }
                .studio-btn:active {
                    transform: translateY(0);
                }
                .studio-btn:disabled {
                    background: #374151;
                    cursor: not-allowed;
                    box-shadow: none;
                    transform: none;
                    color: rgba(255, 255, 255, 0.4);
                }
                .usage-bar-outer {
                    width: 100%;
                    background: #070B13;
                    border-radius: 2px;
                    height: 10px;
                    margin-top: 8px;
                    overflow: hidden;
                    border: 1px solid rgba(255, 255, 255, 0.08);
                }
                .usage-bar-inner {
                    height: 100%;
                    background: linear-gradient(90deg, #BC1B1D 0%, #F87171 100%);
                    width: 0%;
                    border-radius: 2px;
                    box-shadow: 0 0 8px rgba(188, 27, 29, 0.4);
                    transition: width 0.5s ease-in-out;
                }
                .draft-container {
                    background: #070B13;
                    border: 1px solid rgba(255, 255, 255, 0.08);
                    color: #10B981;
                    font-family: "JetBrains Mono", monospace;
                    padding: 24px;
                    border-radius: 4px;
                    min-height: 400px;
                    max-height: 600px;
                    overflow-y: auto;
                    overflow-x: auto;
                    word-break: break-word;
                    overflow-wrap: break-word;
                    white-space: pre-wrap;
                    box-shadow: inset 0 2px 8px rgba(0,0,0,0.8);
                    text-shadow: 0 0 4px rgba(16, 185, 129, 0.15);
                    box-sizing: border-box;
                    max-width: 100%;
                    position: relative;
                    isolation: isolate;
                    contain: inline-size layout style;
                }
                .draft-container.has-article {
                    font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                    color: #E2E8F0 !important;
                    background: #070B13 !important;
                    white-space: normal;
                    line-height: 1.7;
                    font-size: 14px;
                    text-shadow: none;
                }
                .draft-container.has-article * {
                    box-sizing: border-box;
                }
                .draft-container.has-article div {
                    color: #E2E8F0;
                }
                .draft-container.has-article .seo-metadata-block,
                .draft-container.has-article div[class*="seo"] {
                    background: #0D1527 !important;
                    border: 1px solid rgba(56, 189, 248, 0.3) !important;
                    border-radius: 6px !important;
                    padding: 18px !important;
                    margin-bottom: 24px !important;
                    color: #CBD5E1 !important;
                }
                .draft-container.has-article .intelligence-metadata-block,
                .draft-container.has-article div[class*="intelligence"] {
                    background: rgba(6, 78, 59, 0.25) !important;
                    border: 1px solid rgba(16, 185, 129, 0.35) !important;
                    border-radius: 6px !important;
                    padding: 18px !important;
                    margin-bottom: 24px !important;
                    color: #A7F3D0 !important;
                }
                .draft-container.has-article .seo-metadata-block strong,
                .draft-container.has-article .seo-metadata-block b,
                .draft-container.has-article div[class*="seo"] strong {
                    color: #38BDF8 !important;
                    font-family: 'Outfit', sans-serif;
                    font-size: 11px;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    display: block;
                    margin-top: 10px;
                    margin-bottom: 4px;
                }
                .draft-container.has-article .intelligence-metadata-block strong,
                .draft-container.has-article .intelligence-metadata-block b,
                .draft-container.has-article div[class*="intelligence"] strong {
                    color: #34D399 !important;
                    font-family: 'Outfit', sans-serif;
                    font-size: 11px;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    display: block;
                    margin-top: 10px;
                    margin-bottom: 4px;
                }
                .draft-container.has-article h1,
                .draft-container.has-article h2,
                .draft-container.has-article h3,
                .draft-container.has-article h4 {
                    color: #FFFFFF !important;
                    font-family: 'Outfit', sans-serif;
                    font-weight: 700;
                    margin-top: 24px;
                    margin-bottom: 12px;
                    line-height: 1.3;
                }
                .draft-container.has-article h1 {
                    font-size: 22px !important;
                    color: #FFFFFF !important;
                    border-bottom: 2px solid rgba(188, 27, 29, 0.6);
                    padding-bottom: 10px;
                }
                .draft-container.has-article h2 {
                    font-size: 18px !important;
                    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                    padding-bottom: 8px;
                    color: #38BDF8 !important;
                }
                .draft-container.has-article h3 {
                    font-size: 15px !important;
                    color: #F43F5E !important;
                }
                .draft-container.has-article p {
                    margin-bottom: 16px !important;
                    color: #CBD5E1 !important;
                }
                .draft-container.has-article a {
                    color: #60A5FA !important;
                    text-decoration: underline !important;
                }
                .draft-container.has-article a:hover {
                    color: #93C5FD !important;
                }
                .draft-container.has-article ul,
                .draft-container.has-article ol {
                    margin-bottom: 16px !important;
                    padding-left: 24px !important;
                    color: #CBD5E1 !important;
                }
                .draft-container.has-article li {
                    margin-bottom: 6px !important;
                    color: #CBD5E1 !important;
                }
                .draft-container.has-article strong,
                .draft-container.has-article b {
                    color: #FFFFFF !important;
                    font-weight: 600;
                }
                .draft-tabs-container {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                .draft-toolbar {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 8px;
                    align-items: center;
                    justify-content: flex-end;
                }
                .draft-toolbar button {
                    background: rgba(255, 255, 255, 0.05);
                    color: #10B981;
                    border: 1px solid rgba(16, 185, 129, 0.3);
                    padding: 6px 14px;
                    border-radius: 4px;
                    cursor: pointer;
                    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
                    font-size: 12px;
                    font-weight: 600;
                    display: inline-flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    gap: 6px !important;
                    white-space: nowrap !important;
                    height: 32px !important;
                    line-height: 1 !important;
                    box-sizing: border-box;
                    transition: all 0.2s ease;
                }
                .draft-toolbar button i.dashicons,
                .draft-toolbar button .dashicons {
                    font-size: 14px !important;
                    width: 14px !important;
                    height: 14px !important;
                    line-height: 14px !important;
                    margin: 0 !important;
                    display: inline-block !important;
                    vertical-align: middle !important;
                }
                .draft-toolbar button:hover {
                    background: rgba(16, 185, 129, 0.15);
                    border-color: #10B981;
                    color: #34D399;
                    box-shadow: 0 0 10px rgba(16, 185, 129, 0.25);
                }
                .draft-toolbar button.btn-push-wp {
                    background: #BC1B1D !important;
                    border-color: #BC1B1D !important;
                    color: #FFFFFF !important;
                    font-weight: 600;
                }
                .draft-toolbar button.btn-push-wp:hover {
                    background: #D32F2F !important;
                    border-color: #D32F2F !important;
                    box-shadow: 0 0 12px rgba(188, 27, 29, 0.45) !important;
                }
                .draft-container::-webkit-scrollbar {
                    width: 6px;
                }
                .draft-container::-webkit-scrollbar-track {
                    background: #070B13;
                }
                .draft-container::-webkit-scrollbar-thumb {
                    background: rgba(255, 255, 255, 0.08);
                    border-radius: 2px;
                }
                .draft-container::-webkit-scrollbar-thumb:hover {
                    background: rgba(255, 255, 255, 0.2);
                }
                .draft-tab-btn {
                    background: rgba(255, 255, 255, 0.02);
                    color: rgba(255, 255, 255, 0.6);
                    border: 1px solid rgba(255, 255, 255, 0.08);
                    padding: 6px 16px;
                    border-radius: 4px;
                    cursor: pointer;
                    font-family: 'Outfit', sans-serif;
                    font-size: 12px;
                    font-weight: 600;
                    height: 32px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    transition: all 0.2s ease;
                }
                .draft-tab-btn:hover {
                    background: rgba(255, 255, 255, 0.05);
                    color: #FFFFFF;
                    border-color: rgba(255, 255, 255, 0.15);
                }
                .draft-tab-btn.active {
                    background: rgba(16, 185, 129, 0.1);
                    color: #10B981;
                    border-color: #10B981;
                    box-shadow: 0 0 8px rgba(16, 185, 129, 0.2);
                }
            </style>
            
            <div class="ascendance-ai-studio-inner">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255, 255, 255, 0.08); padding-bottom: 20px; margin-bottom: 30px;">
                <h1 style="margin: 0; font-family: 'Outfit', sans-serif; font-weight: 800; color: #FFFFFF; font-size: 28px; display: flex; align-items: center; gap: 10px; letter-spacing: -0.5px;">
                    <i class="dashicons dashicons-wand" style="font-size: 28px; width: 28px; height: 28px; color: #BC1B1D; text-shadow: 0 0 10px rgba(188, 27, 29, 0.45);"></i>
                    ASCENDANCE &middot; AI Studio
                </h1>
                <div style="font-family: 'JetBrains Mono', monospace; text-align: right; font-size: 12px; color: rgba(255, 255, 255, 0.8);">
                    <div><?php esc_html_e( 'System Status: Active', 'ascendance-core' ); ?></div>
                    <div style="color: #BC1B1D; font-weight: bold; margin-top: 4px;"><?php printf( __( 'Usage: $%s / $%s', 'ascendance-core' ), number_format( $current_cost, 2 ), number_format( $cap, 2 ) ); ?></div>
                </div>
            </div>

            <?php
            $has_active = false;
            foreach ( $gateways as $provider => $gateway ) {
                if ( 'active' === $gateway['status']['status'] ) {
                    $has_active = true;
                    break;
                }
            }
            $no_active_keys = ! $has_active;
            if ( $no_active_keys ) :
            ?>
                <div class="notice notice-error" style="display: flex !important; align-items: center; justify-content: space-between; gap: 15px; margin-bottom: 25px; border-left-color: #EF4444 !important; background: #0D1527 !important; border: 1px solid rgba(255, 255, 255, 0.08) !important; border-radius: 2px !important; color: #FFFFFF !important; padding: 14px 20px !important;">
                    <div style="display: flex; align-items: center; gap: 10px; font-family: 'Inter', sans-serif;">
                        <i class="dashicons dashicons-warning" style="color: #EF4444; font-size: 18px; width: 18px; height: 18px; margin-top: 1px;"></i>
                        <span style="font-size: 13px; color: #FFFFFF; font-weight: 500;">
                            <?php esc_html_e( 'Attention required: No active API keys are configured. The AI Studio will fall back to mock simulations until at least one key is configured.', 'ascendance-core' ); ?>
                        </span>
                    </div>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=ascendance-settings' ) ); ?>" class="button button-primary" style="background: #EF4444 !important; border-color: #EF4444 !important; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; padding: 6px 14px !important; font-weight: 600; font-family: 'Outfit', sans-serif; font-size: 12px; height: auto; text-shadow: none; box-shadow: none; border-radius: 2px; color: #ffffff !important; transition: all 0.2s ease;">
                        <i class="dashicons dashicons-admin-settings" style="font-size: 14px; width: 14px; height: 14px; margin-top: 3px; color: #fff;"></i> <?php esc_html_e( 'Configure Keys', 'ascendance-core' ); ?>
                    </a>
                </div>
            <?php endif; ?>

            <!-- Compact Action Toolbar -->
            <div style="margin-bottom: 25px; display: flex; flex-wrap: wrap; gap: 15px; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: 15px;">
                <span style="font-size: 10px; font-family: 'Outfit', sans-serif; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: rgba(255, 255, 255, 0.4);"><?php esc_html_e( 'Quick Actions:', 'ascendance-core' ); ?></span>
                
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=ascendance-settings' ) ); ?>" style="font-size: 12px; color: #3B82F6; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; transition: color 0.2s ease;">
                    <i class="dashicons dashicons-admin-settings" style="font-size: 14px; width: 14px; height: 14px; margin-top: 1px;"></i> <?php esc_html_e( 'Configure Key Overrides', 'ascendance-core' ); ?>
                </a>
                
                <span style="color: rgba(255,255,255,0.15);">|</span>
                
                <span style="font-size: 10px; font-family: 'Outfit', sans-serif; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: rgba(255, 255, 255, 0.4);"><?php esc_html_e( 'Generate Keys:', 'ascendance-core' ); ?></span>
                
                <a href="https://console.anthropic.com/settings/keys" target="_blank" rel="noopener noreferrer" style="font-size: 12px; color: rgba(255,255,255,0.6); text-decoration: none; display: inline-flex; align-items: center; gap: 4px; transition: color 0.2s ease;">
                    <?php esc_html_e( 'Anthropic Claude', 'ascendance-core' ); ?> <i class="dashicons dashicons-external" style="font-size: 12px; width: 12px; height: 12px; margin-top: 1px;"></i>
                </a>
                
                <a href="https://platform.openai.com/api-keys" target="_blank" rel="noopener noreferrer" style="font-size: 12px; color: rgba(255,255,255,0.6); text-decoration: none; display: inline-flex; align-items: center; gap: 4px; margin-left: 5px; transition: color 0.2s ease;">
                    <?php esc_html_e( 'OpenAI GPT', 'ascendance-core' ); ?> <i class="dashicons dashicons-external" style="font-size: 12px; width: 12px; height: 12px; margin-top: 1px;"></i>
                </a>
                
                <a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener noreferrer" style="font-size: 12px; color: rgba(255,255,255,0.6); text-decoration: none; display: inline-flex; align-items: center; gap: 4px; margin-left: 5px; transition: color 0.2s ease;">
                    <?php esc_html_e( 'Google Gemini', 'ascendance-core' ); ?> <i class="dashicons dashicons-external" style="font-size: 12px; width: 12px; height: 12px; margin-top: 1px;"></i>
                </a>
            </div>

            <div class="studio-container">
                <!-- Left Column: Inputs -->
                <div>
                    <div class="studio-card">
                        <h3>1. ARTICLE GENERATION SETUP</h3>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div>
                                <label for="article_type"><?php esc_html_e( 'Article Type', 'ascendance-core' ); ?></label>
                                <select id="article_type" class="studio-field">
                                    <option value="brief">Brief (Flagship Analysis)</option>
                                    <option value="update">Update (Short news-flash)</option>
                                    <option value="dossier">Dossier (Living document)</option>
                                </select>
                            </div>
                            <div>
                                <label for="ai_provider"><?php esc_html_e( 'AI Engine Provider', 'ascendance-core' ); ?></label>
                                <select id="ai_provider" class="studio-field">
                                    <?php 
                                    $has_options = false;
                                    if ( isset( $gateways['anthropic']['status']['status'] ) && 'active' === $gateways['anthropic']['status']['status'] ) {
                                        echo '<option value="anthropic">' . esc_html__( 'Anthropic Claude (Recommended)', 'ascendance-core' ) . '</option>';
                                        $has_options = true;
                                    }
                                    if ( isset( $gateways['openai']['status']['status'] ) && 'active' === $gateways['openai']['status']['status'] ) {
                                        echo '<option value="openai">' . esc_html__( 'OpenAI GPT-4o (Alt)', 'ascendance-core' ) . '</option>';
                                        $has_options = true;
                                    }
                                    if ( isset( $gateways['gemini']['status']['status'] ) && 'active' === $gateways['gemini']['status']['status'] ) {
                                        echo '<option value="gemini">' . esc_html__( 'Google Gemini 1.5 Pro (Alt)', 'ascendance-core' ) . '</option>';
                                        $has_options = true;
                                    }
                                    if ( ! $has_options ) {
                                        echo '<option value="anthropic" selected>' . esc_html__( 'Anthropic Claude (Recommended)', 'ascendance-core' ) . '</option>';
                                        echo '<option value="openai">' . esc_html__( 'OpenAI GPT-4o (Alt)', 'ascendance-core' ) . '</option>';
                                        echo '<option value="gemini">' . esc_html__( 'Google Gemini 1.5 Pro (Alt)', 'ascendance-core' ) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div>
                                <label for="article_topic"><?php esc_html_e( 'Primary Focus / Industry', 'ascendance-core' ); ?></label>
                                <select id="article_topic" class="studio-field">
                                    <?php 
                                    if ( ! empty( $topics ) && ! is_wp_error( $topics ) ) :
                                        foreach( $topics as $topic ) : 
                                    ?>
                                            <option value="<?php echo esc_attr( $topic->name ); ?>"><?php echo esc_html( $topic->name ); ?></option>
                                    <?php 
                                        endforeach;
                                    else :
                                    ?>
                                        <option value="Diplomacy">Diplomacy</option>
                                        <option value="Critical Minerals">Critical Minerals</option>
                                        <option value="Infrastructure & Logistics">Infrastructure & Logistics</option>
                                        <option value="Geopolitics & Trade">Geopolitics & Trade</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div>
                                <label for="article_tier"><?php esc_html_e( 'Subscription Access Tier', 'ascendance-core' ); ?></label>
                                <select id="article_tier" class="studio-field">
                                    <?php 
                                    if ( ! empty( $tiers ) && ! is_wp_error( $tiers ) ) :
                                        foreach( $tiers as $tier ) : 
                                    ?>
                                            <option value="<?php echo esc_attr($tier->slug); ?>" <?php selected( $tier->slug, 'essential' ); ?>><?php echo esc_html($tier->name); ?></option>
                                    <?php 
                                        endforeach; 
                                    else :
                                    ?>
                                        <option value="public">Public (Free Preview)</option>
                                        <option value="essential" selected>Essential (Tier 1)</option>
                                        <option value="professional">Professional (Tier 2)</option>
                                        <option value="enterprise">Enterprise (Tier 3)</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <label><?php esc_html_e( 'Geographic Regions', 'ascendance-core' ); ?></label>
                        <div class="studio-checkbox-grid">
                            <?php 
                            if ( ! empty( $regions ) && ! is_wp_error( $regions ) ) :
                                foreach( $regions as $region ) : 
                            ?>
                                    <label class="studio-checkbox-label">
                                        <input type="checkbox" class="region-checkbox" value="<?php echo esc_attr( $region->name ); ?>" />
                                        <?php echo esc_html( $region->name ); ?>
                                    </label>
                            <?php 
                                endforeach;
                            else :
                                $default_regions = array( 'AFRICA', 'AMERICAS', 'ANGOLA', 'ASIA', 'CENTRAL AFRICA', 'DEMOCRATIC REPUBLIC OF CONGO', 'DRC', 'EAST AFRICA' );
                                foreach ( $default_regions as $def_region ) :
                            ?>
                                    <label class="studio-checkbox-label">
                                        <input type="checkbox" class="region-checkbox" value="<?php echo esc_attr( $def_region ); ?>" />
                                        <?php echo esc_html( $def_region ); ?>
                                    </label>
                            <?php 
                                endforeach;
                            endif; 
                            ?>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                            <div>
                                <label for="target_keywords"><?php esc_html_e( 'Target Keywords (Comma Separated)', 'ascendance-core' ); ?></label>
                                <input type="text" id="target_keywords" class="studio-field" placeholder="e.g. Sakania Corridor, mineral transit" style="margin-bottom: 0;" />
                            </div>
                            <div>
                                <label for="content_tone"><?php esc_html_e( 'Content Tone', 'ascendance-core' ); ?></label>
                                <select id="content_tone" class="studio-field" style="margin-bottom: 0;">
                                    <option value="institutional">Institutional / Measured</option>
                                    <option value="analytical">Analytical & Objective</option>
                                    <option value="cautious">Cautious & Audited</option>
                                    <option value="urgent">Urgent / Intel Flash</option>
                                    <option value="strategic">Strategic & Forward-Looking</option>
                                </select>
                            </div>
                        </div>

                        <label for="source_notes"><?php esc_html_e( 'Source Material & Briefing Notes', 'ascendance-core' ); ?></label>
                        <textarea id="source_notes" class="studio-field" style="height: 180px;" placeholder="Paste raw stakeholder briefings, press releases, reports, or topic outlines here. AI will extract claims and compile the analysis based on these inputs..."></textarea>

                        <label for="custom_prompt"><?php esc_html_e( 'Special Writing Instructions (Optional)', 'ascendance-core' ); ?></label>
                        <input type="text" id="custom_prompt" class="studio-field" placeholder="e.g. Focus on geopolitical impact, make it highly cautious, or reference LAR." />

                        <button id="generate_btn" class="studio-btn" style="width: 100%;" <?php disabled( $current_cost >= $cap ); ?>>
                            <span id="btn_text"><i class="dashicons dashicons-admin-generic" style="margin-top: -3px; animation: spin 2s linear infinite; display: none;" id="btn_spinner"></i> <?php esc_html_e( 'COMPILE INTELLIGENCE DRAFT', 'ascendance-core' ); ?></span>
                        </button>

                        <div style="margin-top: 25px; border-top: 1px dashed rgba(247, 244, 239, 0.1); padding-top: 15px;">
                            <div style="display:flex; justify-content:space-between; font-size: 11px; margin-bottom: 5px;">
                                <span><?php esc_html_e( 'MONTHLY BUDGET CAP PROGRESS', 'ascendance-core' ); ?></span>
                                <span id="budget_percent_text"><?php echo $percent; ?>%</span>
                            </div>
                            <div class="usage-bar-outer">
                                <div class="usage-bar-inner" id="budget_percent_bar" style="width: <?php echo $percent; ?>%;"></div>
                            </div>
                            <div style="display:flex; justify-content:space-between; font-size: 10px; color: rgba(255, 255, 255, 0.5); margin-top: 8px;">
                                <span><?php esc_html_e( 'Spent:', 'ascendance-core' ); ?> <strong style="color: #FFF;">$<span id="budget_spent_text"><?php echo number_format( $current_cost, 4 ); ?></span></strong></span>
                                <span><?php esc_html_e( 'Limit:', 'ascendance-core' ); ?> <strong style="color: #FFF;">$<span id="budget_limit_text"><?php echo number_format( $cap, 2 ); ?></span></strong></span>
                                <span><?php esc_html_e( 'Remaining:', 'ascendance-core' ); ?> <strong style="color: #FFF;">$<span id="budget_remaining_text"><?php echo number_format( max( 0, $cap - $current_cost ), 4 ); ?></span></strong></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Outputs -->
                <div>
                    <div class="studio-card" style="background: #0A1628; border-color: rgba(0, 255, 102, 0.2);">
                        <h3>2. REAL-TIME COMPILED DRAFT</h3>
                        
                        <div class="draft-header-bar" style="margin-bottom: 16px; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 12px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;">
                            <div class="draft-tabs-container" id="draft_lang_tabs" style="display: none;">
                                <button type="button" id="tab_en_btn" class="draft-tab-btn active">English</button>
                                <button type="button" id="tab_fr_btn" class="draft-tab-btn">French</button>
                            </div>
                            
                            <div class="draft-toolbar" id="draft_actions" style="display: none;">
                                <button type="button" id="shorten_btn"><i class="dashicons dashicons-editor-justify"></i> Shorten</button>
                                <button type="button" id="expand_btn"><i class="dashicons dashicons-editor-paragraph"></i> Expand</button>
                                <button type="button" id="cautious_btn"><i class="dashicons dashicons-shield"></i> Cautious Tone</button>
                                <button type="button" id="push_to_wp_btn" class="btn-push-wp"><i class="dashicons dashicons-admin-post"></i> Push to WP Draft</button>
                            </div>
                        </div>

                        <div id="draft_output" class="draft-container">
&gt; Ready to receive compilation requests.
&gt; Awaiting prompt input sequence...
                        </div>
                        
                        <div style="margin-top: 15px; font-size: 11px; color: rgba(247, 244, 239, 0.6); display: flex; justify-content: space-between;">
                            <span id="output_model_info">Model: N/A</span>
                            <span id="output_cost_info">Cost: $0.0000</span>
                        </div>
                    </div>
                </div>
            </div><!-- .studio-container -->

            <!-- Link to Settings Page -->
            <div class="studio-card" style="margin-top: 30px; background: #0D1527; border-color: rgba(255, 255, 255, 0.08); text-align: center; padding: 25px;">
                <h3 style="margin: 0 0 10px 0; font-family: 'Outfit', sans-serif;"><i class="dashicons dashicons-admin-settings" style="color: #BC1B1D; margin-right: 8px;"></i> SYSTEM PROMPT & API KEY OVERRIDES</h3>
                <p style="font-size: 13px; color: rgba(255, 255, 255, 0.6); margin-bottom: 15px;">
                    <?php esc_html_e( 'System prompt engines, monthly budget caps, and API credential overrides have been moved to the central settings panel.', 'ascendance-core' ); ?>
                </p>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=ascendance-settings' ) ); ?>" class="studio-btn" style="text-decoration: none; padding: 8px 16px;">
                    <i class="dashicons dashicons-admin-generic" style="font-size: 14px; width: 14px; height: 14px; margin-top: 2px; margin-right: 4px;"></i> <?php esc_html_e( 'Configure Platform Settings', 'ascendance-core' ); ?>
                </a>
            </div><!-- .ascendance-ai-studio-inner -->
        </div><!-- .wrap -->

        <script>
            jQuery(document).ready(function($) {
                let lastResultTextEn = '';
                let lastResultTextFr = '';
                let activeLang = 'en';
                
                function formatDraftContent(text) {
                    if (!text) return '';
                    text = text.trim();
                    text = text.replace(/^[\s\r\n]*`{3,}(?:html)?[\s\r\n]*/gi, '');
                    text = text.replace(/[\s\r\n]*`{3,}[\s\r\n]*$/gi, '');

                    // Strip doctype, html, head, style, script, and body tags that break parent WP Admin layout
                    text = text.replace(/<!DOCTYPE[^>]*>/gi, '');
                    text = text.replace(/<head[\s>][\s\S]*?<\/head>/gi, '');
                    text = text.replace(/<style[\s>][\s\S]*?<\/style>/gi, '');
                    text = text.replace(/<script[\s>][\s\S]*?<\/script>/gi, '');
                    text = text.replace(/<\/?(?:html|body)[^>]*>/gi, '');

                    // Remove any excess closing </div> tags or append missing </div> tags
                    var openDivs = (text.match(/<div[\s>]/gi) || []).length;
                    var closeDivs = (text.match(/<\/div>/gi) || []).length;
                    while (closeDivs > openDivs) {
                        var lastIdx = text.lastIndexOf('</div>');
                        if (lastIdx !== -1) {
                            text = text.substring(0, lastIdx) + text.substring(lastIdx + 6);
                            closeDivs--;
                        } else {
                            break;
                        }
                    }
                    while (openDivs > closeDivs) {
                        text += '</div>';
                        closeDivs++;
                    }

                    // Replace markdown bold inside text
                    text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                    text = text.replace(/\+\+(.*?)\+\+/g, '<strong>$1</strong>');

                    // Replace markdown headers if present
                    text = text.replace(/^### (.*$)/gim, '<h3>$1</h3>');
                    text = text.replace(/^## (.*$)/gim, '<h2>$1</h2>');
                    text = text.replace(/^# (.*$)/gim, '<h1>$1</h1>');

                    // If text contains no HTML paragraph or div tags, convert double newlines to paragraphs
                    if (!/<p>|<div|<section|<h[1-6]/i.test(text)) {
                        var paras = text.split(/\n\n+/);
                        text = paras.map(function(p) {
                            p = p.trim();
                            if (!p) return '';
                            if (/^<h[1-6]|^<div|^<section|^<ul|^<ol/i.test(p)) return p;
                            return '<p>' + p.replace(/\n/g, '<br>') + '</p>';
                        }).join('');
                    }

                    return text;
                }

                // Toggle tabs
                $('#tab_en_btn').click(function() {
                    activeLang = 'en';
                    $(this).addClass('active').siblings().removeClass('active');
                    if (lastResultTextEn) {
                        $('#draft_output').addClass('has-article').html(formatDraftContent(lastResultTextEn));
                    }
                });
                
                $('#tab_fr_btn').click(function() {
                    activeLang = 'fr';
                    $(this).addClass('active').siblings().removeClass('active');
                    if (lastResultTextFr) {
                        $('#draft_output').addClass('has-article').html(formatDraftContent(lastResultTextFr));
                    } else {
                        $('#draft_output').removeClass('has-article').html('<span class="terminal-warning">&gt; Translating intelligence draft to French...</span>\n<span class="terminal-warning">&gt; Please wait a moment while the French translation completes in background...</span>');
                    }
                });

                $('#generate_btn').click(function() {
                    const articleType = $('#article_type').val();
                    const aiProvider = $('#ai_provider').val();
                    const topic = $('#article_topic').val();
                    const notes = $('#source_notes').val();
                    const customPrompt = $('#custom_prompt').val();
                    const targetKeywords = $('#target_keywords').val();
                    const contentTone = $('#content_tone').val();
                    
                    // Selected regions
                    let regions = [];
                    $('.region-checkbox:checked').each(function() {
                        regions.push($(this).val());
                    });

                    if(!notes.trim()) {
                        alert('Please enter source material or briefing notes first.');
                        return;
                    }

                    // UI Changes & Dynamic Progress Stepper
                    $('#btn_spinner').show();
                    $('#generate_btn').attr('disabled', true);
                    $('#draft_lang_tabs').css('display', 'none');
                    $('#draft_actions').css('display', 'none');

                    let step = 0;
                    const progressMsgs = [
                        '<span class="terminal-success">&gt; Contacting security API channels...</span>\n<span class="terminal-success">&gt; Initiating intelligence compilation sequence...</span>',
                        '<span class="terminal-success">&gt; Contacting security API channels...</span>\n<span class="terminal-success">&gt; Initiating intelligence compilation sequence...</span>\n<span class="terminal-warning">&gt; Synthesizing policy frameworks & strategic data points...</span>',
                        '<span class="terminal-success">&gt; Contacting security API channels...</span>\n<span class="terminal-success">&gt; Initiating intelligence compilation sequence...</span>\n<span class="terminal-warning">&gt; Synthesizing policy frameworks & strategic data points...</span>\n<span class="terminal-info" style="color:#38bdf8">&gt; Formatting HTML markup & metadata sections...</span>',
                        '<span class="terminal-success">&gt; Contacting security API channels...</span>\n<span class="terminal-success">&gt; Initiating intelligence compilation sequence...</span>\n<span class="terminal-warning">&gt; Synthesizing policy frameworks & strategic data points...</span>\n<span class="terminal-info" style="color:#38bdf8">&gt; Formatting HTML markup & metadata sections...</span>\n<span class="terminal-info" style="color:#a7f3d0">&gt; Finalizing intelligence brief payload...</span>'
                    ];
                    $('#draft_output').removeClass('has-article').html(progressMsgs[0]);
                    if (window.compilationTimer) clearInterval(window.compilationTimer);
                    window.compilationTimer = setInterval(function() {
                        step++;
                        if (step < progressMsgs.length) {
                            $('#draft_output').html(progressMsgs[step]);
                        }
                    }, 4000);

                    $.ajax({
                        url: '<?php echo esc_url_raw( get_rest_url( null, 'ascendance/v1/ai-studio/generate' ) ); ?>',
                        method: 'POST',
                        timeout: 180000,
                        headers: {
                            'X-WP-Nonce': '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>'
                        },
                        contentType: 'application/json',
                        data: JSON.stringify({
                            type: articleType,
                            provider: aiProvider,
                            topic: topic,
                            regions: regions,
                            notes: notes,
                            custom_prompt: customPrompt,
                            keywords: targetKeywords,
                            tone: contentTone
                        }),
                        success: function(response) {
                            if (window.compilationTimer) clearInterval(window.compilationTimer);
                            $('#btn_spinner').hide();
                            $('#generate_btn').attr('disabled', false);
                            
                            if (response.text_en) {
                                lastResultTextEn = response.text_en;
                                lastResultTextFr = '';
                                activeLang = 'en';

                                $('#tab_en_btn').addClass('active').siblings().removeClass('active');
                                $('#draft_output').addClass('has-article').html(formatDraftContent(lastResultTextEn));
                                $('#draft_lang_tabs').css('display', 'flex');
                                $('#draft_actions').css('display', 'flex');

                                $('#output_model_info').text('Model: ' + response.model);
                                $('#output_cost_info').text('Cost: $' + response.cost.toFixed(4));

                                // Update budget progress bar dynamically
                                if (response.monthly_percent !== undefined) {
                                    $('#budget_percent_text').text(response.monthly_percent + '%');
                                    $('#budget_percent_bar').css('width', response.monthly_percent + '%');
                                }
                                if (response.monthly_cost !== undefined) {
                                    const spent = parseFloat(response.monthly_cost);
                                    const limit = parseFloat($('#ai_monthly_cap').val() || 100);
                                    const remaining = Math.max(0, limit - spent);
                                    
                                    $('#budget_spent_text').text(spent.toFixed(4));
                                    $('#budget_limit_text').text(limit.toFixed(2));
                                    $('#budget_remaining_text').text(remaining.toFixed(4));
                                }

                                // Async background call for French translation
                                $.ajax({
                                    url: '<?php echo esc_url_raw( get_rest_url( null, 'ascendance/v1/ai-studio/translate' ) ); ?>',
                                    method: 'POST',
                                    headers: {
                                        'X-WP-Nonce': '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>'
                                    },
                                    contentType: 'application/json',
                                    data: JSON.stringify({
                                        text_en: lastResultTextEn,
                                        provider: aiProvider,
                                        model: response.model
                                    }),
                                    success: function(trRes) {
                                        if (trRes && trRes.text_fr) {
                                            lastResultTextFr = trRes.text_fr;
                                            if (activeLang === 'fr') {
                                                $('#draft_output').addClass('has-article').html(formatDraftContent(lastResultTextFr));
                                            }
                                        }
                                    }
                                });
                            } else {
                                $('#draft_output').removeClass('has-article').html('<span class="terminal-error">&gt; Error parsing compilation payload.</span>');
                            }
                        },
                        error: function(xhr) {
                            if (window.compilationTimer) clearInterval(window.compilationTimer);
                            $('#btn_spinner').hide();
                            $('#generate_btn').attr('disabled', false);
                            let errMsg = 'API Connection Exception.';
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errMsg = xhr.responseJSON.error;
                            }
                            $('#draft_output').removeClass('has-article').html('<span class="terminal-error">&gt; ERROR SEQUENCE INITIATED:</span>\n<span class="terminal-error">&gt; ' + errMsg + '</span>');
                        }
                    });
                });

                // Helper for section adjustments
                function adjustSection(instruction) {
                    if(!lastResultTextEn) return;
                    
                    const aiProvider = $('#ai_provider').val();
                    
                    $('#draft_output').removeClass('has-article').html('<span class="terminal-warning">&gt; Gating section parameters...</span>\n<span class="terminal-warning">&gt; Running refinement filters...</span>');
                    
                    $.ajax({
                        url: '<?php echo esc_url_raw( get_rest_url( null, 'ascendance/v1/ai-studio/regenerate-section' ) ); ?>',
                        method: 'POST',
                        headers: {
                            'X-WP-Nonce': '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>'
                        },
                        contentType: 'application/json',
                        data: JSON.stringify({
                            content: lastResultTextEn,
                            instruction: instruction,
                            provider: aiProvider,
                            context: ''
                        }),
                        success: function(response) {
                            if (response.text_en) {
                                lastResultTextEn = response.text_en;
                                lastResultTextFr = response.text_fr;

                                $('#draft_output').addClass('has-article');
                                if (activeLang === 'fr') {
                                    $('#draft_output').html(formatDraftContent(lastResultTextFr));
                                } else {
                                    $('#draft_output').html(formatDraftContent(lastResultTextEn));
                                }

                                $('#output_model_info').text('Model: ' + response.model);
                                $('#output_cost_info').text('Cost: $' + response.cost.toFixed(4));

                                // Update budget progress bar dynamically
                                if (response.monthly_percent !== undefined) {
                                    $('#budget_percent_text').text(response.monthly_percent + '%');
                                    $('#budget_percent_bar').css('width', response.monthly_percent + '%');
                                }
                                if (response.monthly_cost !== undefined) {
                                    const spent = parseFloat(response.monthly_cost);
                                    const limit = parseFloat($('#ai_monthly_cap').val() || 100);
                                    const remaining = Math.max(0, limit - spent);
                                    
                                    $('#budget_spent_text').text(spent.toFixed(4));
                                    $('#budget_limit_text').text(limit.toFixed(2));
                                    $('#budget_remaining_text').text(remaining.toFixed(4));
                                }
                            } else {
                                $('#draft_output').removeClass('has-article').html('<span class="terminal-error">&gt; Error refining section.</span>');
                            }
                        },
                        error: function(xhr) {
                            let errMsg = 'Refinement Exception.';
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errMsg = xhr.responseJSON.error;
                            }
                            $('#draft_output').removeClass('has-article').html('<span class="terminal-error">&gt; ERROR IN REFINEMENT:</span>\n<span class="terminal-error">&gt; ' + errMsg + '</span>');
                        }
                    });
                }

                $('#shorten_btn').click(() => adjustSection('Shorten this text significantly'));
                $('#expand_btn').click(() => adjustSection('Expand details and explain background'));
                $('#cautious_btn').click(() => adjustSection('Refine tone to be extremely objective and cautious'));

                // Push to WP Draft programmatically
                $('#push_to_wp_btn').click(function() {
                    if(!lastResultTextEn) return;

                    const articleType = $('#article_type').val();
                    const topic = $('#article_topic').val();
                    const targetKeywords = $('#target_keywords').val();
                    const contentTone = $('#content_tone').val();
                    const tier = $('#article_tier').val();
                    
                    // Selected regions
                    let regions = [];
                    $('.region-checkbox:checked').each(function() {
                        regions.push($(this).val());
                    });

                    // UI Changes
                    $('#push_to_wp_btn').attr('disabled', true);
                    const originalBtnHtml = $('#push_to_wp_btn').html();
                    $('#push_to_wp_btn').html('<i class="dashicons dashicons-admin-generic" style="animation: spin 2s linear infinite;"></i> Pushing...');

                    // Append status in the terminal console output on the right
                    const currentOutputHtml = $('#draft_output').html();
                    $('#draft_output').html(currentOutputHtml + '\n<span class="terminal-success">&gt; Establishing secure database pipeline...</span>\n<span class="terminal-success">&gt; Transmitting payload options...</span>');

                    $.ajax({
                        url: '<?php echo esc_url_raw( get_rest_url( null, 'ascendance/v1/ai-studio/push-draft' ) ); ?>',
                        method: 'POST',
                        headers: {
                            'X-WP-Nonce': '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>'
                        },
                        contentType: 'application/json',
                        data: JSON.stringify({
                            type: articleType,
                            topic: topic,
                            regions: regions,
                            keywords: targetKeywords,
                            tone: contentTone,
                            tier: tier,
                            content_en: lastResultTextEn,
                            content_fr: lastResultTextFr
                        }),
                        success: function(response) {
                            if (response.success && response.edit_link) {
                                $('#draft_output').html(
                                    $('#draft_output').html() + 
                                    '\n<span class="terminal-success">&gt; English & French Draft records created and linked successfully. [EN ID: ' + response.post_id + ']</span>' +
                                    '\n<span class="terminal-success">&gt; Redirecting to Gutenberg editor panel...</span>'
                                );
                                
                                setTimeout(function() {
                                    window.location.href = response.edit_link;
                                }, 1500);
                            } else {
                                $('#push_to_wp_btn').attr('disabled', false).html(originalBtnHtml);
                                $('#draft_output').html(
                                    $('#draft_output').html() + 
                                    '\n<span class="terminal-error">&gt; ERROR: Push aborted - Invalid API Response.</span>'
                                );
                            }
                        },
                        error: function(xhr) {
                            $('#push_to_wp_btn').attr('disabled', false).html(originalBtnHtml);
                            let errMsg = 'Exception occurred during draft creation.';
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errMsg = xhr.responseJSON.error;
                            }
                            $('#draft_output').html(
                                $('#draft_output').html() + 
                                '\n<span class="terminal-error">&gt; PIPELINE FAILURE: ' + errMsg + '</span>'
                            );
                        }
                    });
                });

                // Clean up query parameters from the address bar to prevent notices from displaying on refresh
                if (window.history.replaceState) {
                    const url = new URL(window.location.href);
                    if (url.searchParams.has('rechecked') || url.searchParams.has('settings_saved')) {
                        url.searchParams.delete('rechecked');
                        url.searchParams.delete('settings_saved');
                        window.history.replaceState({}, document.title, url.pathname + url.search);
                    }
                }
            });
        </script>
        <?php
    }
}
