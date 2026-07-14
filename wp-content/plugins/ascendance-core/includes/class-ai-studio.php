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

        if ( 'brevo' === $provider ) {
            if ( defined( 'ASCENDANCE_NEWSLETTER_API_KEY' ) && ! empty( ASCENDANCE_NEWSLETTER_API_KEY ) ) {
                return trim( ASCENDANCE_NEWSLETTER_API_KEY );
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

        if ( 'brevo' === $provider ) {
            if ( defined( 'ASCENDANCE_NEWSLETTER_API_KEY' ) && ! empty( ASCENDANCE_NEWSLETTER_API_KEY ) ) {
                return __( 'Constant (ASCENDANCE_NEWSLETTER_API_KEY)', 'ascendance-core' );
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
    private function call_claude( $system_prompt, $user_input, $model = 'claude-3-5-sonnet-20241022' ) {
        $api_key = $this->get_api_key( 'anthropic' );
        if ( ! $api_key || strpos( $api_key, 'sk-ant-api03-' ) === 0 && strlen($api_key) < 20 ) {
            return $this->get_mock_response( 'anthropic', $model, $user_input );
        }

        $body = array(
            'model'      => $model,
            'max_tokens' => 4000,
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

        // Calculate Cost
        $cost_usd = ( $input_tokens * 0.000003 ) + ( $output_tokens * 0.000015 );

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
     * Get mock responses for local testing
     */
    private function get_mock_response( $provider, $model, $prompt ) {
        // Wait a small moment to simulate API latency
        usleep( 500000 );

        // If it's a section regeneration request
        if ( strpos( $prompt, 'REGENERATE_SECTION' ) !== false || strpos( $prompt, 'Instruction:' ) !== false ) {
            $instruction = 'Regenerate';
            if ( preg_match( '/Instruction:\s*(.*)/i', $prompt, $matches ) ) {
                $instruction = trim( $matches[1] );
            }

            $refined_text = "Under Ascendance's Strategic Asset Reserve framework, the Sakania-Lobito Corridor represents a vital geopolitical offset to eastern routing dependencies. The logistics consortium, led by the Africa Finance Corporation, secured concession terms that shift structural risk onto sovereign stakeholders while committing USD 450M in immediate capital expenditure.";
            
            if ( strpos( strtolower( $instruction ), 'shorten' ) !== false ) {
                $refined_text = "Under Ascendance's Strategic Asset Reserve framework, the Lobito Corridor represents a vital geopolitical offset to eastern routing dependencies, securing USD 450M in immediate capital investments while shifting sovereign risk.";
            } elseif ( strpos( strtolower( $instruction ), 'expand' ) !== false ) {
                $refined_text = "Under Ascendance's Strategic Asset Reserve framework, the Sakania-Lobito Corridor represents a vital geopolitical offset to eastern routing dependencies. The logistics consortium, led by the Africa Finance Corporation, secured concession terms that shift structural risk onto sovereign stakeholders while committing USD 450M in immediate capital expenditure. This investment will expand regional trade flow capacity to over 1.2M tonnes annually and lower overall logistics costs.";
            } elseif ( strpos( strtolower( $instruction ), 'cautious' ) !== false ) {
                $refined_text = "Under Ascendance's Strategic Asset Reserve framework, the Sakania-Lobito Corridor represents an analytical offset to eastern routing dependencies. The logistics consortium, led by the Africa Finance Corporation, has negotiated concession terms that shift structural risk onto sovereign stakeholders while proposing USD 450M in initial capital expenditure.";
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
        $slug = sanitize_title( $topic . '-' . ( $first_kw ?: 'brief' ) );
        $title = sprintf( "%s Analysis: Strategic Implications for %s", $topic, $regions );
        
        // Construct structured mock response
        $text = "<div class=\"seo-metadata-block\" style=\"border: 1px dashed rgba(255,255,255,0.1); padding: 15px; margin-bottom: 20px; border-radius: 2px; background: rgba(255,255,255,0.02);\">\n" .
                "  <p><strong>Meta Title:</strong> " . esc_html( substr( $title, 0, 70 ) ) . "</p>\n" .
                "  <p><strong>Meta Description:</strong> Strategic intelligence briefing exploring " . esc_html( strtolower( $topic ) ) . " trends across " . esc_html( $regions ) . " with focus on " . esc_html( $keywords ) . ".</p>\n" .
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
                "<h1>" . esc_html( strtoupper( $type ) ) . " &bull; " . esc_html( $title ) . "</h1>\n\n" .
                "<h2>Introduction</h2>\n" .
                "<p>This " . esc_html( strtolower( $type ) ) . " assesses the geopolitical landscape of " . esc_html( $topic ) . " across " . esc_html( $regions ) . ". Under a " . esc_html( $tone ) . " framework, recent developments suggest significant structural changes that shift regional operational risk models. The primary focus of this analysis centres on " . esc_html( $keywords ) . " and the surrounding infrastructure corridors.</p>\n\n" .
                "<p><em>Briefing Summary:</em> " . esc_html( substr( $notes, 0, 400 ) ) . ( strlen( $notes ) > 400 ? '...' : '' ) . "</p>\n\n" .
                "<h2>What is the current status of " . esc_html( $topic ) . " in " . esc_html( $regions ) . "?</h2>\n" .
                "<p>Recent regional surveys demonstrate a direct correlation between policy execution and logistics corridors. Key stakeholders have committed substantial capital expenditure to rehabilitate existing infrastructure networks. Unlike previous state-backed loan models, the current funding structure relies heavily on multilateral credit guarantees, reducing direct sovereign debt exposure.</p>\n\n" .
                "<h3>Consortium terms and financial guarantees</h3>\n" .
                "<p>The consortium partners have agreed to concession terms that allocate operational liabilities to local state entities while maintaining private management control. This structure aims to balance developmental objectives with commercial feasibility. Special instructions noted for this draft: <em>" . esc_html( $custom_prompt ) . "</em>.</p>\n\n" .
                "<h2>Key strategic challenges and " . esc_html( $first_kw ) . " routing risks</h2>\n" .
                "<p>Logistical congestion at regional ports continues to present significant bottlenecks. Transit times through the corridor are projected to decline by approximately 40% once the digital customs and signaling upgrades are fully integrated. However, security concerns along border crossings remain a persistent variable in cost modeling.</p>\n\n" .
                "<h3>Regional trade flow dependencies</h3>\n" .
                "<p>Export volumes are expected to rise to 1.2M tonnes annually within the next three fiscal years. To support this growth, developers are prioritizing the expansion of dry port facilities and customs clearance zones at major transit junctions.</p>\n\n" .
                "<h2>Conclusion</h2>\n" .
                "<p>In conclusion, the strategic realignment of " . esc_html( $topic ) . " routing across " . esc_html( $regions ) . " represents a key pivot in regional supply chains. By establishing direct logistics pipelines, stakeholders are altering long-term trade flow dynamics and geopolitical dependencies.</p>\n\n" .
                "<h2>FAQ Section</h2>\n" .
                "<p><strong>Q: What is the primary focus keyword for this report?</strong><br>A: The primary keyword is '" . esc_html( $first_kw ) . "', which guides the search engine relevance scoring.</p>\n" .
                "<p><strong>Q: How does this impact long-term operational risk?</strong><br>A: By shifting transit liabilities onto sovereign entities, the consortium isolates private investment from localized operational volatility.</p>";

        return array(
            'text'          => $text,
            'input_tokens'  => 1200,
            'output_tokens' => 800,
            'cost'          => 0.0068,
            'model'         => $model . ' (Mock Dynamic)',
            'provider'      => $provider
        );
    }

    /**
     * Handle Generate request from REST API
     */
    public function handle_generate_request( $request ) {
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
            $system_prompt = "You are an analytical writer for Ascendance Strategies, a Paris-based strategic intelligence advisory firm focused on the US-DRC Strategic Partnership, critical minerals supply chains, and the Sakania-Lobito Corridor. Your readers are institutional subscribers: government bodies, investors, multilaterals, and corporates active in central Africa.\n\nVOICE:\n- Measured, institutional, evidence-led.\n- Short, declarative sentences. One claim per paragraph.\n- Name actors explicitly.\n- Return the article in HTML block markup.\n- Provide fully detailed, exhaustive, and in-depth coverage of the topic. Avoid short, simple, or brief summaries. Elaborate on historical context, regulatory backgrounds, financial details, and strategic implications with granular detail.";
        }

        // Append strict SEO, Intelligence Metadata, and Content Structure instructions to force complete layout
        $system_prompt .= "\n\nCRITICAL FORMATTING INSTRUCTION:\n" .
            "You MUST structure the response to include the following sections exactly, using HTML block markup. " .
            "The output must contain:\n" .
            "1. SEO METADATA SECTION (encapsulate in a <div class=\"seo-metadata-block\" style=\"border: 1px dashed rgba(255,255,255,0.1); padding: 15px; margin-bottom: 20px; border-radius: 2px; background: rgba(255,255,255,0.02);\">\n" .
            "   - Meta Title (max 70 chars)\n" .
            "   - Meta Description (max 155 chars)\n" .
            "   - Meta Keywords\n" .
            "   - Focus Keyword\n" .
            "   - SEO Slug / URL\n" .
            "   - Suggested Internal Linking Opportunities (list 2-3 links related to Ascendance Strategies Briefs/Dossiers)\n" .
            "   - Suggested External References (list 2-3 authoritative external source references)\n" .
            "2. INTELLIGENCE METADATA SECTION (encapsulate in a <div class=\"intelligence-metadata-block\" style=\"border: 1px dashed rgba(16,185,129,0.2); padding: 15px; margin-bottom: 20px; border-radius: 2px; background: rgba(16,185,129,0.02);\">\n" .
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
            "   - H2 Headings and H3 Headings (where appropriate) dividing the main body analysis\n" .
            "   - Conclusion\n" .
            "   - FAQ Section (containing 2-3 relevant questions and answers)\n\n" .
            "Strictly follow this structure. Do not skip any of these required elements.";

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

            // Fetch new total and percent
            $new_cost = $this->get_monthly_cost();
            $result['monthly_cost'] = $new_cost;
            $result['monthly_percent'] = min( 100, round( ( $new_cost / $cap ) * 100, 2 ) );

            if ( isset( $result['text'] ) ) {
                $result['text'] = trim( $result['text'] );
                $result['text'] = preg_replace( '/^`{3,}(?:html)?\s*/i', '', $result['text'] );
                $result['text'] = preg_replace( '/`{3,}\s*$/', '', $result['text'] );
                $result['text'] = trim( $result['text'] );
            }

            return new \WP_REST_Response( $result, 200 );

        } catch ( \Exception $e ) {
            return new \WP_REST_Response( array( 'error' => $e->getMessage() ), 500 );
        }
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

        $system_prompt = "You are an editor for Ascendance Strategies. Rewrite or refine the provided block of text according to the instruction, maintaining the measured, institutional tone. Return ONLY the rewritten text, with no introduction or outro.";
        
        $user_input = "REGENERATE_SECTION\n" .
                      "Original Block:\n$block_content\n\n" .
                      "Instruction: $instruction\n\n" .
                      "Surrounding Context:\n$context";

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

            // Fetch new total and percent
            $new_cost = $this->get_monthly_cost();
            $result['monthly_cost'] = $new_cost;
            $result['monthly_percent'] = min( 100, round( ( $new_cost / $cap ) * 100, 2 ) );

            if ( isset( $result['text'] ) ) {
                $result['text'] = trim( $result['text'] );
                $result['text'] = preg_replace( '/^`{3,}(?:html)?\s*/i', '', $result['text'] );
                $result['text'] = preg_replace( '/`{3,}\s*$/', '', $result['text'] );
                $result['text'] = trim( $result['text'] );
            }

            return new \WP_REST_Response( $result, 200 );

        } catch ( \Exception $e ) {
            return new \WP_REST_Response( array( 'error' => $e->getMessage() ), 500 );
        }
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
                    $result = $this->call_claude( $system_prompt, $user_input, 'claude-3-5-sonnet-20241022' );
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
        $params = $request->get_json_params();

        $type = sanitize_text_field( $params['type'] ?? 'brief' );
        $topic = sanitize_text_field( $params['topic'] ?? '' );
        $regions = array_map( 'sanitize_text_field', $params['regions'] ?? array() );
        $keywords = sanitize_text_field( $params['keywords'] ?? '' );
        $tone = sanitize_text_field( $params['tone'] ?? 'institutional' );
        $tier = sanitize_text_field( ! empty( $params['tier'] ) ? $params['tier'] : $default_tier );
        $content = $params['content'] ?? ''; // Keep raw HTML content

        if ( empty( $content ) ) {
            return new \WP_REST_Response( array( 'error' => __( 'No generated content provided.', 'ascendance-core' ) ), 400 );
        }

        // Clean up markdown code block wrappers if present (e.g. ```html ... ```)
        $content = trim( $content );
        $content = preg_replace( '/^`{3,}(?:html)?\s*/i', '', $content );
        $content = preg_replace( '/`{3,}\s*$/', '', $content );
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

        // 2. Parse and extract SEO Metadata
        $meta_title = '';
        $meta_desc = '';
        $meta_keywords = '';
        $focus_keyword = '';
        $seo_slug = '';
        $internal_links = '';
        $external_references = '';

        // Extract <div class="seo-metadata-block">...</div>
        $seo_block = '';
        if ( preg_match( '/<div[^>]*class=["\']seo-metadata-block["\'][^>]*>(.*?)<\/div>/is', $content, $matches ) ) {
            $seo_block = $matches[1];
            // Remove the block from the post content body
            $content = preg_replace( '/<div[^>]*class=["\']seo-metadata-block["\'][^>]*>(.*?)<\/div>/is', '', $content );
        }

        if ( ! empty( $seo_block ) ) {
            // Strip tags for simple string extraction, decode HTML entities
            $seo_plain = html_entity_decode( strip_tags( $seo_block ), ENT_QUOTES, 'UTF-8' );

            // Parse Meta Title
            if ( preg_match( '/(?:Meta\s+)?Title\s*:\s*([^\r\n]+)/i', $seo_plain, $m ) ) {
                $meta_title = sanitize_text_field( trim( $m[1] ) );
            }
            // Parse Meta Description
            if ( preg_match( '/(?:Meta\s+)?Description\s*:\s*([^\r\n]+)/i', $seo_plain, $m ) ) {
                $meta_desc = sanitize_text_field( trim( $m[1] ) );
            }
            // Parse Meta Keywords
            if ( preg_match( '/(?:Meta\s+)?Keywords\s*:\s*([^\r\n]+)/i', $seo_plain, $m ) ) {
                $meta_keywords = sanitize_text_field( trim( $m[1] ) );
            }
            // Parse Focus Keyword
            if ( preg_match( '/Focus\s+Keyword\s*:\s*([^\r\n]+)/i', $seo_plain, $m ) ) {
                $focus_keyword = sanitize_text_field( trim( $m[1] ) );
            }
            // Parse SEO Slug / URL
            if ( preg_match( '/(?:SEO\s+)?Slug(?:\s*\/|\s+URL)?\s*:\s*([^\r\n]+)/i', $seo_plain, $m ) ) {
                $seo_slug = sanitize_title( trim( $m[1] ) );
            }

            // Parse list items or HTML for internal/external links
            if ( preg_match( '/Suggested\s+Internal\s+Linking\s+Opportunities\s*:\s*(.*?)(?=Suggested\s+External|$)/is', $seo_block, $m ) ) {
                $internal_links = trim( $m[1] );
            }
            if ( preg_match( '/Suggested\s+External\s+References\s*:\s*(.*)/is', $seo_block, $m ) ) {
                $external_references = trim( $m[1] );
            }
        }

        // 2b. Parse and extract Intelligence Metadata
        $subhead = '';
        $analytical_claim = '';
        $public_excerpt = '';
        $executive_summary = '';
        $key_findings = '';
        $key_takeaways = array();
        $sources_list = array();

        $intel_block = '';
        if ( preg_match( '/<div[^>]*class=["\']intelligence-metadata-block["\'][^>]*>(.*?)<\/div>/is', $content, $matches ) ) {
            $intel_block = $matches[1];
            // Remove the block from the post content body
            $content = preg_replace( '/<div[^>]*class=["\']intelligence-metadata-block["\'][^>]*>(.*?)<\/div>/is', '', $content );
        }

        if ( ! empty( $intel_block ) ) {
            // Extract Subhead
            if ( preg_match( '/Subhead\s*:\s*(?:<\/strong>)?\s*([^\r\n<]+)/i', $intel_block, $m ) ) {
                $subhead = sanitize_text_field( trim( $m[1] ) );
            }
            // Extract Analytical Claim
            if ( preg_match( '/Analytical\s+Claim\s*:\s*(?:<\/strong>)?\s*([^<]+)/i', $intel_block, $m ) ) {
                $analytical_claim = sanitize_textarea_field( trim( $m[1] ) );
            }
            // Extract Public Excerpt
            if ( preg_match( '/Public\s+Excerpt\s*:\s*(?:<\/strong>)?\s*([^<]+)/i', $intel_block, $m ) ) {
                $public_excerpt = sanitize_textarea_field( trim( $m[1] ) );
            }
            // Extract Executive Summary
            if ( preg_match( '/Executive\s+Summary\s*:\s*(?:<\/strong>)?\s*([^<]+)/i', $intel_block, $m ) ) {
                $executive_summary = sanitize_textarea_field( trim( $m[1] ) );
            }
            // Extract Key Findings
            if ( preg_match( '/Key\s+Findings\s*:\s*(?:<\/strong>)?\s*(.*?)(?=<[^>]+>Key\s+Takeaways|<[^>]+>Sources|$)/is', $intel_block, $m ) ) {
                $key_findings = wp_kses_post( trim( $m[1] ) );
            }
            // Extract Key Takeaways
            if ( preg_match( '/Key\s+Takeaways\s*:\s*(?:<\/strong>)?\s*(.*?)(?=<[^>]+>Sources|$)/is', $intel_block, $m ) ) {
                $takeaways_block = trim( strip_tags( $m[1] ) );
                preg_match_all( '/(?:-\s*|\d+\.\s*)([^\r\n]+)/', $takeaways_block, $t_matches );
                if ( ! empty( $t_matches[1] ) ) {
                    foreach ( $t_matches[1] as $item ) {
                        $key_takeaways[] = array( 'takeaway' => sanitize_text_field( trim( $item ) ) );
                    }
                }
            }
            // Extract Sources
            if ( preg_match( '/Sources\s*:\s*(?:<\/strong>)?\s*(.*)/is', $intel_block, $m ) ) {
                $sources_block = trim( strip_tags( $m[1] ) );
                // Line-by-line parsing of sources
                $lines = explode( "\n", $sources_block );
                foreach ( $lines as $line ) {
                    if ( empty( trim( $line ) ) ) continue;
                    $src_name = '';
                    $src_url = '';
                    $src_date = '';
                    
                    if ( preg_match( '/Source\s*:\s*([^|]+)/i', $line, $sm ) ) {
                        $src_name = sanitize_text_field( trim( $sm[1] ) );
                    }
                    if ( preg_match( '/URL\s*:\s*([^|]+)/i', $line, $su ) ) {
                        $src_url = esc_url_raw( trim( $su[1] ) );
                    }
                    if ( preg_match( '/Date\s*:\s*([0-9-]{10})/i', $line, $sd ) ) {
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
        }
        // 2c. Parse and extract Suggested blocks if they exist (and strip them from content)
        if ( preg_match( '/=== SUGGESTED_PUBLIC_EXCERPT ===\s*(.*?)(?=== SUGGESTED_KEY_TAKEAWAYS ===|=== SUGGESTED_IMAGE_PROMPTS ===|$)/is', $content, $m ) ) {
            $suggested_excerpt = trim( strip_tags( html_entity_decode( $m[1], ENT_QUOTES, 'UTF-8' ) ) );
            if ( empty( $public_excerpt ) ) {
                $public_excerpt = $suggested_excerpt;
            }
        }
        
        if ( preg_match( '/=== SUGGESTED_KEY_TAKEAWAYS ===\s*(.*?)(?=== SUGGESTED_IMAGE_PROMPTS ===|$)/is', $content, $m ) ) {
            $suggested_takeaways = trim( strip_tags( html_entity_decode( $m[1], ENT_QUOTES, 'UTF-8' ) ) );
            if ( empty( $key_takeaways ) ) {
                $lines = explode( "\n", $suggested_takeaways );
                foreach ( $lines as $line ) {
                    $cleaned_line = trim( preg_replace( '/^[–\-\*\x{2013}\x{2014}\d\.\s]+/u', '', $line ) );
                    if ( ! empty( $cleaned_line ) ) {
                        $key_takeaways[] = array( 'takeaway' => $cleaned_line );
                    }
                }
            }
        }

        // Clean up: strip the Suggested blocks from the post content body completely
        $content = preg_replace( '/=== SUGGESTED_PUBLIC_EXCERPT ===.*$/is', '', $content );
        $content = preg_replace( '/=== SUGGESTED_KEY_TAKEAWAYS ===.*$/is', '', $content );
        $content = preg_replace( '/=== SUGGESTED_IMAGE_PROMPTS ===.*$/is', '', $content );

        // Balance any unclosed tags caused by stripping
        $content = force_balance_tags( trim( $content ) );

        // 3. Extract and strip the first <h1> title from the content to use as the WP post title
        $post_title = '';
        if ( preg_match( '/<h1[^>]*>(.*?)<\/h1>/is', $content, $matches ) ) {
            $post_title = trim( strip_tags( html_entity_decode( $matches[1], ENT_QUOTES, 'UTF-8' ) ) );
            // Strip any system/heading labels like "Main H1 Title:", "H1 Title:", "H1:", etc.
            $post_title = preg_replace( '/^(?:Main\s+)?H1\s+(?:Title\s*)?:\s*/i', '', $post_title );
            $post_title = preg_replace( '/^Title\s*:\s*/i', '', $post_title );
            $post_title = trim( $post_title );
            // Strip the H1 block from the post content body to avoid duplicate H1 display
            $content = preg_replace( '/<h1[^>]*>.*?<\/h1>/is', '', $content );
        }

        // Fallbacks for post title
        if ( empty( $post_title ) ) {
            if ( ! empty( $meta_title ) ) {
                $post_title = $meta_title;
            } else {
                // Find first H2
                if ( preg_match( '/<h2[^>]*>(.*?)<\/h2>/is', $content, $matches ) ) {
                    $post_title = trim( strip_tags( html_entity_decode( $matches[1], ENT_QUOTES, 'UTF-8' ) ) );
                } else {
                    $post_title = __( 'AI Compiled Intel Draft', 'ascendance-core' ) . ' - ' . date_i18n( 'Y-m-d H:i' );
                }
            }
        }

        // Clean up remaining content
        $content = trim( $content );

        // 4. Construct post insertion array
        $post_arr = array(
            'post_title'   => $post_title,
            'post_content' => $content,
            'post_status'  => 'draft',
            'post_type'    => $post_type,
            'post_author'  => get_current_user_id(),
        );

        if ( ! empty( $seo_slug ) ) {
            $post_arr['post_name'] = $seo_slug;
        }

        $post_id = wp_insert_post( $post_arr );

        if ( is_wp_error( $post_id ) ) {
            return new \WP_REST_Response( array( 'error' => $post_id->get_error_message() ), 500 );
        }

        // 5. Update SEO plugins meta keys (Yoast and Rank Math)
        if ( ! empty( $meta_title ) ) {
            update_post_meta( $post_id, '_yoast_wpseo_title', $meta_title );
            update_post_meta( $post_id, '_rank_math_title', $meta_title );
        }
        if ( ! empty( $meta_desc ) ) {
            update_post_meta( $post_id, '_yoast_wpseo_metadesc', $meta_desc );
            update_post_meta( $post_id, '_rank_math_description', $meta_desc );
        }
        if ( ! empty( $focus_keyword ) ) {
            update_post_meta( $post_id, '_yoast_wpseo_focuskw', $focus_keyword );
            update_post_meta( $post_id, '_rank_math_focus_keyword', $focus_keyword );
        }
        if ( ! empty( $meta_keywords ) ) {
            update_post_meta( $post_id, '_yoast_wpseo_metakeywords', $meta_keywords );
        }

        // 6. Save target parameters to postmeta for tracking
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

        // 7. Associate Topic Term (if CPT supports it)
        if ( ! empty( $topic ) && taxonomy_exists( 'topic' ) && is_object_in_taxonomy( $post_type, 'topic' ) ) {
            wp_set_object_terms( $post_id, $topic, 'topic' );
        }

        // 8. Associate Region Terms (if CPT supports it)
        if ( ! empty( $regions ) && taxonomy_exists( 'region' ) && is_object_in_taxonomy( $post_type, 'region' ) ) {
            wp_set_object_terms( $post_id, $regions, 'region' );
        }

        // 9. Associate Tier Term (if CPT supports it)
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

        // 10. Update ACF custom postmeta field for Tier Access Level
        update_post_meta( $post_id, 'tier_access', $tier );
        // Also save the ACF reference key (for brief & dossier fields)
        if ( $post_type === 'dossier' ) {
            update_post_meta( $post_id, '_tier_access', 'field_dossier_tier_access' );
        } else {
            update_post_meta( $post_id, '_tier_access', 'field_brief_tier_access' );
        }

        // 11. Populate ACF Metadata if post type is Brief or Dossier
        if ( in_array( $post_type, array( 'brief', 'dossier' ), true ) ) {
            $prefix = $post_type === 'dossier' ? 'dossier' : 'brief';
            
            // Subhead
            if ( ! empty( $subhead ) ) {
                update_post_meta( $post_id, 'subhead', $subhead );
                update_post_meta( $post_id, '_subhead', 'field_' . $prefix . '_subhead' );
            }
            
            // Analytical Claim
            if ( ! empty( $analytical_claim ) ) {
                update_post_meta( $post_id, 'analytical_claim', $analytical_claim );
                update_post_meta( $post_id, '_analytical_claim', 'field_' . $prefix . '_analytical_claim' );
            }
            
            // Public Excerpt
            if ( ! empty( $public_excerpt ) ) {
                update_post_meta( $post_id, 'public_excerpt', $public_excerpt );
                update_post_meta( $post_id, '_public_excerpt', 'field_' . $prefix . '_public_excerpt' );
            }
            
            // Executive Summary
            if ( ! empty( $executive_summary ) ) {
                update_post_meta( $post_id, 'executive_summary', $executive_summary );
                update_post_meta( $post_id, '_executive_summary', 'field_' . $prefix . '_executive_summary' );
            }
            
            // Key Findings
            if ( ! empty( $key_findings ) ) {
                update_post_meta( $post_id, 'key_findings', $key_findings );
                update_post_meta( $post_id, '_key_findings', 'field_' . $prefix . '_key_findings' );
            }
            
            // Key Takeaways (repeater)
            if ( ! empty( $key_takeaways ) ) {
                update_post_meta( $post_id, 'key_takeaways', count( $key_takeaways ) );
                update_post_meta( $post_id, '_key_takeaways', 'field_' . $prefix . '_key_takeaways' );
                foreach ( $key_takeaways as $index => $item ) {
                    update_post_meta( $post_id, 'key_takeaways_' . $index . '_takeaway', $item['takeaway'] );
                    update_post_meta( $post_id, '_key_takeaways_' . $index . '_takeaway', 'field_' . $prefix . '_takeaway_text' );
                }
            }
            
            // Sources (repeater)
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
            
            // Brief Version
            update_post_meta( $post_id, 'brief_version', 1 );
            update_post_meta( $post_id, '_brief_version', 'field_' . $prefix . '_brief_version' );
            
            // AI Generated
            update_post_meta( $post_id, 'ai_generated', 1 );
            update_post_meta( $post_id, '_ai_generated', 'field_' . $prefix . '_ai_generated' );
            
            // Featured Post
            update_post_meta( $post_id, 'featured_flag', 0 );
            update_post_meta( $post_id, '_featured_flag', 'field_' . $prefix . '_featured_flag' );

            // Parse and populate related briefs (internal links auto-linking)
            $related_ids = array();
            if ( ! empty( $internal_links ) ) {
                if ( preg_match_all( '/href=["\'](?:[^"\']*?\/(?:brief|dossier|intel|update)\/)?([a-zA-Z0-9-_]+)\/?["\']/i', $internal_links, $url_matches ) ) {
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
                update_post_meta( $post_id, 'related_briefs', $related_ids );
                update_post_meta( $post_id, '_related_briefs', 'field_' . $prefix . '_related_briefs' );
            }
        }

        // 12. If post type is Update CPT
        if ( $post_type === 'update' ) {
            // Key Update
            update_post_meta( $post_id, 'key_update', $content );
            update_post_meta( $post_id, '_key_update', 'field_update_key_update' );

            // One Line Summary
            if ( ! empty( $meta_desc ) ) {
                update_post_meta( $post_id, 'one_line_summary', substr( $meta_desc, 0, 160 ) );
                update_post_meta( $post_id, '_one_line_summary', 'field_update_one_line_summary' );
            }

            // Update Date
            update_post_meta( $post_id, 'update_date', date( 'Y-m-d' ) );
            update_post_meta( $post_id, '_update_date', 'field_update_date' );
        }

        // 13. Auto-generate and assign Featured Image
        $this->set_featured_image_from_topic( $post_id, $topic );

        // 9. Get Edit Link
        $edit_link = get_edit_post_link( $post_id, 'raw' );

        return new \WP_REST_Response( array(
            'success'   => true,
            'post_id'   => $post_id,
            'edit_link' => $edit_link
        ), 200 );
    }

    /**
     * Generate or assign a beautiful featured image for the post based on topic.
     */
    private function set_featured_image_from_topic( $post_id, $topic ) {
        $image_url = '';
        $api_key = $this->get_api_key( 'openai' );

        // 1. Try to generate a custom image using OpenAI DALL-E API if a valid key exists
        if ( $api_key && strlen( $api_key ) >= 15 ) {
            $prompt = sprintf( "A beautiful, premium, realistic documentary-style photograph illustrating: %s. High quality, professional photography, bright cinematic lighting, clean composition, no text.", $topic );
            
            $response = wp_remote_post( 'https://api.openai.com/v1/images/generations', array(
                'timeout' => 30,
                'headers' => array(
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $api_key
                ),
                'body' => wp_json_encode( array(
                    'model'  => 'dall-e-2',
                    'prompt' => $prompt,
                    'n'      => 1,
                    'size'   => '1024x1024'
                ) )
            ) );

            if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
                $body = json_decode( wp_remote_retrieve_body( $response ), true );
                if ( ! empty( $body['data'][0]['url'] ) ) {
                    $image_url = $body['data'][0]['url'];
                }
            }
        }

        // 2. If OpenAI generation is missing or failed, fall back to high-quality topic-matched Unsplash image
        if ( empty( $image_url ) ) {
            $normalized_topic = strtolower( $topic );
            if ( strpos( $normalized_topic, 'solar' ) !== false || strpos( $normalized_topic, 'energy' ) !== false || strpos( $normalized_topic, 'power' ) !== false ) {
                $image_url = 'https://images.unsplash.com/photo-1509391366360-2e959784a276?auto=format&fit=crop&w=1200&q=80';
            } elseif ( strpos( $normalized_topic, 'mineral' ) !== false || strpos( $normalized_topic, 'cobalt' ) !== false || strpos( $normalized_topic, 'copper' ) !== false || strpos( $normalized_topic, 'mining' ) !== false ) {
                $image_url = 'https://images.unsplash.com/photo-1533106497176-45ae19e68ba2?auto=format&fit=crop&w=1200&q=80';
            } elseif ( strpos( $normalized_topic, 'rail' ) !== false || strpos( $normalized_topic, 'infrastructure' ) !== false || strpos( $normalized_topic, 'corridor' ) !== false || strpos( $normalized_topic, 'lobito' ) !== false ) {
                $image_url = 'https://images.unsplash.com/photo-1474487548417-781cb71495f3?auto=format&fit=crop&w=1200&q=80';
            } elseif ( strpos( $normalized_topic, 'geopolitics' ) !== false || strpos( $normalized_topic, 'partnership' ) !== false || strpos( $normalized_topic, 'china' ) !== false || strpos( $normalized_topic, 'strategic' ) !== false ) {
                $image_url = 'https://images.unsplash.com/photo-1526470608268-f674ce90ebd4?auto=format&fit=crop&w=1200&q=80';
            } else {
                $image_url = 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=1200&q=80';
            }
        }

        // 3. Sideload the image into the WordPress media library and set it as featured
        if ( ! empty( $image_url ) ) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';

            // Download the image to a temp file
            $temp_file = download_url( $image_url );

            if ( ! is_wp_error( $temp_file ) ) {
                $file_array = array(
                    'name'     => sanitize_title( $topic ) . '-featured.jpg',
                    'tmp_name' => $temp_file
                );

                // Sideload the file into WP Media Library
                $attachment_id = media_handle_sideload( $file_array, $post_id, $topic );

                if ( ! is_wp_error( $attachment_id ) ) {
                    // Set as featured image
                    set_post_thumbnail( $post_id, $attachment_id );
                } else {
                    @unlink( $temp_file );
                }
            }
        }
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
                    $result = $this->call_claude( $system_prompt, $user_input, 'claude-3-5-sonnet-20241022' );
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
            $system_prompt = "You are an analytical writer for Ascendance Strategies, a Paris-based strategic intelligence advisory firm focused on the US-DRC Strategic Partnership, critical minerals supply chains, and the Sakania-Lobito Corridor. Your readers are institutional subscribers: government bodies, investors, multilaterals, and corporates active in central Africa.\n\nVOICE:\n- Measured, institutional, evidence-led. Closer to a Financial Times long-read than a blog post.\n- Short, declarative sentences. Avoid headline-style cleverness in body text.\n- Name actors explicitly every time. No pronouns where an entity name fits.\n- Use precise dates (\"In May 2026\") not relative time (\"recently\").\n- One claim per paragraph. State claim, then evidence, then implication.\n- Provide fully detailed, exhaustive, and in-depth coverage of the topic. Avoid short, simple, or brief summaries. Elaborate on historical context, regulatory backgrounds, financial details, and strategic implications with granular detail.\n\nSTRUCTURE for an Intelligence Brief:\n1. Open with a 40-80 word definitional paragraph that fully answers the article's title as a question. This is the citable paragraph.\n2. A \"Key takeaways\" block of 3-5 bullets.\n3. H2 section headings phrased as questions a reader might actually ask.\n4. End with a \"Sources\" block listing the evidence base.\n\nWHAT TO AVOID:\n- No \"In conclusion\" or \"In summary\" sign-offs.\n- No marketing copy, no calls to action, no \"Subscribe to learn more\".\n- Do not invent statistics, dates, or named entities. If you don't know something, write [VERIFY] in brackets where it should go.\n- Do not use the words: leverage, synergy, robust, ecosystem, holistic, game-changer, paradigm.\n\nOUTPUT:\n- Return the article in HTML format.\n- After the article body, output three additional sections:\n  * === SUGGESTED_PUBLIC_EXCERPT ===\n  * === SUGGESTED_KEY_TAKEAWAYS ===\n  * === SUGGESTED_IMAGE_PROMPTS ===";
        }

        // Fetch terms for selection
        $topics = get_terms( array( 'taxonomy' => 'topic', 'hide_empty' => false ) );
        $regions = get_terms( array( 'taxonomy' => 'region', 'hide_empty' => false ) );
        $tiers = get_terms( array( 'taxonomy' => 'tier', 'hide_empty' => false ) );

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

                .ascendance-ai-studio-inner {
                    background: #070B13;
                    padding: 30px;
                    border-radius: 2px;
                    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 10px 10px -5px rgba(0, 0, 0, 0.4);
                    border: 1px solid rgba(255, 255, 255, 0.08);
                    color: #FFFFFF;
                    font-family: 'Inter', sans-serif;
                    margin-right: 20px;
                    margin-top: 25px !important;
                }
                .studio-container {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 30px;
                    margin-top: 20px;
                }
                @media (max-width: 900px) {
                    .studio-container {
                        grid-template-columns: 1fr;
                    }
                }
                .studio-card {
                    background: linear-gradient(135deg, #0D1527 0%, #070B13 100%);
                    border: 1px solid rgba(255, 255, 255, 0.08);
                    border-radius: 2px;
                    padding: 24px;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
                    color: #F7F4EF;
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
                    border-radius: 2px;
                    min-height: 400px;
                    max-height: 600px;
                    overflow-y: auto;
                    white-space: pre-wrap;
                    box-shadow: inset 0 2px 8px rgba(0,0,0,0.8);
                    text-shadow: 0 0 4px rgba(16, 185, 129, 0.15);
                }
                .draft-toolbar {
                    display: flex;
                    gap: 10px;
                    margin-bottom: 16px;
                    justify-content: flex-end;
                }
                .draft-toolbar button {
                    background: rgba(255, 255, 255, 0.05);
                    color: #10B981;
                    border: 1px solid rgba(16, 185, 129, 0.3);
                    padding: 6px 12px;
                    border-radius: 2px;
                    cursor: pointer;
                    font-family: 'JetBrains Mono', monospace;
                    font-size: 11px;
                    transition: all 0.2s ease;
                }
                .draft-toolbar button:hover {
                    background: rgba(16, 185, 129, 0.1);
                    border-color: #10B981;
                    box-shadow: 0 0 8px rgba(16, 185, 129, 0.2);
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
                                    if ( 'active' === $gateways['anthropic']['status']['status'] ) {
                                        echo '<option value="anthropic">' . esc_html__( 'Anthropic Claude (Recommended)', 'ascendance-core' ) . '</option>';
                                        $has_options = true;
                                    }
                                    if ( 'active' === $gateways['openai']['status']['status'] ) {
                                        echo '<option value="openai">' . esc_html__( 'OpenAI GPT-4o (Alt)', 'ascendance-core' ) . '</option>';
                                        $has_options = true;
                                    }
                                    if ( 'active' === $gateways['gemini']['status']['status'] ) {
                                        echo '<option value="gemini">' . esc_html__( 'Google Gemini 1.5 Pro (Alt)', 'ascendance-core' ) . '</option>';
                                        $has_options = true;
                                    }
                                    if ( ! $has_options ) {
                                        echo '<option value="" disabled selected>' . esc_html__( 'No Active Providers Configured', 'ascendance-core' ) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div>
                                <label for="article_topic"><?php esc_html_e( 'Primary Focus / Industry', 'ascendance-core' ); ?></label>
                                <select id="article_topic" class="studio-field">
                                    <?php foreach($topics as $topic) : ?>
                                        <option value="<?php echo esc_attr($topic->name); ?>"><?php echo esc_html($topic->name); ?></option>
                                    <?php endforeach; ?>
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
                            <?php foreach($regions as $region) : ?>
                                <label class="studio-checkbox-label">
                                    <input type="checkbox" class="region-checkbox" value="<?php echo esc_attr($region->name); ?>" />
                                    <?php echo esc_html($region->name); ?>
                                </label>
                            <?php endforeach; ?>
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
                        
                        <div class="draft-toolbar" id="draft_actions" style="display: none;">
                            <button id="shorten_btn"><i class="dashicons dashicons-editor-justify"></i> Shorten</button>
                            <button id="expand_btn"><i class="dashicons dashicons-editor-paragraph"></i> Expand</button>
                            <button id="cautious_btn"><i class="dashicons dashicons-shield"></i> Cautious Tone</button>
                            <button id="push_to_wp_btn" style="background:#BC1B1D; border-color:#BC1B1D; color:#FFF;"><i class="dashicons dashicons-admin-post"></i> Push to WP Draft</button>
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
                let lastResultText = '';
                
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

                    // UI Changes
                    $('#btn_spinner').show();
                    $('#generate_btn').attr('disabled', true);
                    $('#draft_output').html('<span class="terminal-success">&gt; Contacting security API channels...</span>\n<span class="terminal-success">&gt; Initiating intelligence compilation sequence...</span>');

                    $.ajax({
                        url: '/Ascendance/wp-json/ascendance/v1/ai-studio/generate',
                        method: 'POST',
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
                            $('#btn_spinner').hide();
                            $('#generate_btn').attr('disabled', false);
                            
                            if (response.text) {
                                lastResultText = response.text;
                                $('#draft_output').html(response.text);
                                $('#output_model_info').text('Model: ' + response.model);
                                $('#output_cost_info').text('Cost: $' + response.cost.toFixed(4));
                                $('#draft_actions').show();

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
                                $('#draft_output').html('<span class="terminal-error">&gt; Error parsing compilation payload.</span>');
                            }
                        },
                        error: function(xhr) {
                            $('#btn_spinner').hide();
                            $('#generate_btn').attr('disabled', false);
                            let errMsg = 'API Connection Exception.';
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errMsg = xhr.responseJSON.error;
                            }
                            $('#draft_output').html('<span class="terminal-error">&gt; ERROR SEQUENCE INITIATED:</span>\n<span class="terminal-error">&gt; ' + errMsg + '</span>');
                        }
                    });
                });

                // Helper for section adjustments
                function adjustSection(instruction) {
                    if(!lastResultText) return;
                    
                    const aiProvider = $('#ai_provider').val();
                    
                    $('#draft_output').html('<span class="terminal-warning">&gt; Gating section parameters...</span>\n<span class="terminal-warning">&gt; Running refinement filters...</span>');
                    
                    $.ajax({
                        url: '/Ascendance/wp-json/ascendance/v1/ai-studio/regenerate-section',
                        method: 'POST',
                        headers: {
                            'X-WP-Nonce': '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>'
                        },
                        contentType: 'application/json',
                        data: JSON.stringify({
                            content: lastResultText,
                            instruction: instruction,
                            provider: aiProvider,
                            context: ''
                        }),
                        success: function(response) {
                            if (response.text) {
                                lastResultText = response.text;
                                $('#draft_output').html(response.text);
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
                                $('#draft_output').html('<span class="terminal-error">&gt; Error refining section.</span>');
                            }
                        },
                        error: function(xhr) {
                            let errMsg = 'Refinement Exception.';
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errMsg = xhr.responseJSON.error;
                            }
                            $('#draft_output').html('<span class="terminal-error">&gt; ERROR IN REFINEMENT:</span>\n<span class="terminal-error">&gt; ' + errMsg + '</span>');
                        }
                    });
                }

                $('#shorten_btn').click(() => adjustSection('Shorten this text significantly'));
                $('#expand_btn').click(() => adjustSection('Expand details and explain background'));
                $('#cautious_btn').click(() => adjustSection('Refine tone to be extremely objective and cautious'));

                // Push to WP Draft programmatically
                $('#push_to_wp_btn').click(function() {
                    if(!lastResultText) return;

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
                        url: '/Ascendance/wp-json/ascendance/v1/ai-studio/push-draft',
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
                            content: lastResultText
                        }),
                        success: function(response) {
                            if (response.success && response.edit_link) {
                                $('#draft_output').html(
                                    $('#draft_output').html() + 
                                    '\n<span class="terminal-success">&gt; Draft record created successfully. [ID: ' + response.post_id + ']</span>' +
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
