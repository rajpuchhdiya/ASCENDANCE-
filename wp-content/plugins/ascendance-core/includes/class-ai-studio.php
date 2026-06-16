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
        add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );

        // REST API
        add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
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
        add_menu_page(
            __( 'AI Studio', 'ascendance-core' ),
            __( 'AI Studio', 'ascendance-core' ),
            'edit_posts',
            'ascendance-ai-studio',
            array( $this, 'render_ai_studio_page' ),
            'dashicons-wand',
            6
        );
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
    }

    /**
     * Get monthly token cost usage in USD
     */
    public function get_monthly_cost() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ascendance_ai_usage';
        $start_of_month = date( 'Y-m-01 00:00:00' );
        
        $cost = $wpdb->get_var( $wpdb->prepare(
            "SELECT SUM(cost_usd) FROM $table_name WHERE created_at >= %s",
            $start_of_month
        ) );

        return $cost ? floatval( $cost ) : 0.0;
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
    }

    /**
     * Call Anthropic Claude API
     */
    private function call_claude( $system_prompt, $user_input, $model = 'claude-3-5-sonnet-20241022' ) {
        $api_key = getenv( 'ANTHROPIC_API_KEY' );
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
        $api_key = getenv( 'OPENAI_API_KEY' );
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
        $api_key = getenv( 'GEMINI_API_KEY' );
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
            return array(
                'text'          => "Under Ascendance's Strategic Asset Reserve framework, the Sakania-Lobito Corridor represents a vital geopolitical offset to eastern routing dependencies. The logistics consortium, led by the Africa Finance Corporation, secured concession terms that shift structural risk onto sovereign stakeholders while committing USD 450M in immediate capital expenditure.",
                'input_tokens'  => 350,
                'output_tokens' => 80,
                'cost'          => 0.0012,
                'model'         => $model . ' (Mock)',
                'provider'      => $provider
            );
        }

        // Standard draft mock article
        $title = "Sakania-Lobito Corridor Development: May 2026 Strategic Implications";
        $text = "<!-- BLOCK 1 — Lead paragraph -->
The May 2026 concession award for the Sakania-Lobito Corridor logistics consortium marks a structural pivot in the US-DRC Strategic Partnership, reorganizing regional mineral logistics and shifting infrastructure risk models. It serves as the primary Western counterweight to Chinese-financed export routes from the Katanga copperbelt.

<!-- BLOCK 2 — Key Takeaways -->
<ul>
<li><strong>Strategic Pivot:</strong> Shifts logistics routing from eastern ports to the Atlantic Corridor.</li>
<li><strong>Consortium Award:</strong> Led by the Africa Finance Corporation and US DFC partners.</li>
<li><strong>Sovereign Risk:</strong> Host states absorb operational liabilities under revised terms.</li>
<li><strong>Volume Target:</strong> Aims to reach 1.2M tonnes of mineral cargo annually by 2028.</li>
</ul>

## What is the scope of the new concession?
The logistics consortium, formally authorized in May 2026, holds a 30-year operational lease covering the railbed, dry ports, and custom facilities at Sakania. Under the terms, the consortium commits to investing USD 450M in rail rehabilitation, signaling upgrades, and rolling stock. 

Unlike the Belt and Road projects in southern DRC, the corridor's financial model depends on multilateral guarantees rather than direct state-backed loans, minimizing sovereign debt exposure for the DRC government.

## Who are the primary stakeholders?
The award integrates the Africa Finance Corporation (AFC) as the lead developer, with minority equity held by the Lobito Atlantic Railway consortium and the DRC sovereign asset fund (FPT). The United States International Development Finance Corporation (DFC) provides USD 250M in debt financing, establishing a direct interest in regional critical minerals routing.

## How does it impact cobalt and copper export logistics?
Currently, over 80% of Katanga's critical mineral exports route through Durban or Dar es Salaam, facing transit times of 20 to 30 days. The Lobito route reduces transit times to the Atlantic coast to under 8 days, lowering logistics costs by approximately 22%. This shift increases the competitiveness of US-bound critical raw materials.

## Sources
- US-DRC bilateral accord, March 2025.
- Africa Finance Corporation concession memorandum, May 2026.
- Strategic Asset Reserve intelligence assessment, Q2 2026.

=== SUGGESTED_PUBLIC_EXCERPT ===
The Sakania-Lobito Corridor concession award in May 2026 represents a structural re-engineering of Central African logistics. By linking the Katanga critical minerals hub directly to the Atlantic port of Lobito, the project shifts Western supply chain dependencies and alters regional mineral flows.

=== SUGGESTED_KEY_TAKEAWAYS ===
- Concession award alters Central African logistics routes
- Shift to Atlantic ports reduces transit times by 60%
- USD 250M US DFC funding establishes direct geopolitical interest

=== SUGGESTED_IMAGE_PROMPTS ===
- Panoramic shot, Port of Lobito docks, container ships, dawn, 16:9 aspect ratio.
- Railway line through Katanga highlands, freight train carrying mineral shipping containers, wide-angle lens.";

        return array(
            'text'          => $text,
            'input_tokens'  => 1200,
            'output_tokens' => 600,
            'cost'          => 0.0054,
            'model'         => $model . ' (Mock)',
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

        // 1. Enforce monthly cost limit
        $cap = floatval( get_option( 'ascendance_ai_monthly_cap', 100.00 ) );
        $current_cost = $this->get_monthly_cost();
        if ( $current_cost >= $cap ) {
            return new \WP_REST_Response( array( 'error' => __( 'AI Studio monthly budget cap reached. Please raise limit in settings.', 'ascendance-core' ) ), 403 );
        }

        // 2. Fetch system prompt
        $system_prompt = get_option( 'ascendance_ai_system_prompt' );
        if ( ! $system_prompt ) {
            // Load default system prompt
            $system_prompt = "You are an analytical writer for Ascendance Strategies, a Paris-based strategic intelligence advisory firm focused on the US-DRC Strategic Partnership, critical minerals supply chains, and the Sakania-Lobito Corridor. Your readers are institutional subscribers: government bodies, investors, multilaterals, and corporates active in central Africa.\n\nVOICE:\n- Measured, institutional, evidence-led.\n- Short, declarative sentences. One claim per paragraph.\n- Name actors explicitly.\n- Return the article in HTML block markup.";
        }

        if ( ! empty( $custom_prompt ) ) {
            $system_prompt .= "\n\nADDITIONAL INSTRUCTION:\n" . $custom_prompt;
        }

        // Build user prompt
        $user_input = "Format: " . strtoupper( $type ) . "\n" .
                      "Primary Topic: " . $topic . "\n" .
                      "Target Regions: " . implode( ', ', $regions ) . "\n\n" .
                      "Source Notes / Outline:\n" . $notes;

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

            return new \WP_REST_Response( $result, 200 );

        } catch ( \Exception $e ) {
            return new \WP_REST_Response( array( 'error' => $e->getMessage() ), 500 );
        }
    }

    /**
     * Render the AI Studio page in WP admin
     */
    public function render_ai_studio_page() {
        // Enforce stylesheet and script loading if needed, or render in-page styling
        $current_cost = $this->get_monthly_cost();
        $cap = floatval( get_option( 'ascendance_ai_monthly_cap', 100.00 ) );
        $percent = min( 100, round( ( $current_cost / $cap ) * 100 ) );
        
        // Save prompt if updated
        if ( isset($_POST['save_ai_settings']) && check_admin_referer('ascendance_ai_settings_action', 'ascendance_ai_settings_nonce') ) {
            update_option('ascendance_ai_monthly_cap', sanitize_text_field($_POST['ai_monthly_cap']));
            update_option('ascendance_ai_system_prompt', wp_kses_post($_POST['ai_system_prompt']));
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('AI Settings saved successfully.', 'ascendance-core') . '</p></div>';
            // Refresh variables
            $cap = floatval($_POST['ai_monthly_cap']);
            $percent = min( 100, round( ( $current_cost / $cap ) * 100 ) );
        }

        $system_prompt = get_option( 'ascendance_ai_system_prompt' );
        if ( ! $system_prompt ) {
            $system_prompt = "You are an analytical writer for Ascendance Strategies, a Paris-based strategic intelligence advisory firm focused on the US-DRC Strategic Partnership, critical minerals supply chains, and the Sakania-Lobito Corridor. Your readers are institutional subscribers: government bodies, investors, multilaterals, and corporates active in central Africa.\n\nVOICE:\n- Measured, institutional, evidence-led. Closer to a Financial Times long-read than a blog post.\n- Short, declarative sentences. Avoid headline-style cleverness in body text.\n- Name actors explicitly every time. No pronouns where an entity name fits.\n- Use precise dates (\"In May 2026\") not relative time (\"recently\").\n- One claim per paragraph. State claim, then evidence, then implication.\n\nSTRUCTURE for an Intelligence Brief:\n1. Open with a 40-80 word definitional paragraph that fully answers the article's title as a question. This is the citable paragraph.\n2. A \"Key takeaways\" block of 3-5 bullets.\n3. H2 section headings phrased as questions a reader might actually ask.\n4. End with a \"Sources\" block listing the evidence base.\n\nWHAT TO AVOID:\n- No \"In conclusion\" or \"In summary\" sign-offs.\n- No marketing copy, no calls to action, no \"Subscribe to learn more\".\n- Do not invent statistics, dates, or named entities. If you don't know something, write [VERIFY] in brackets where it should go.\n- Do not use the words: leverage, synergy, robust, ecosystem, holistic, game-changer, paradigm.\n\nOUTPUT:\n- Return the article in HTML format.\n- After the article body, output three additional sections:\n  * === SUGGESTED_PUBLIC_EXCERPT ===\n  * === SUGGESTED_KEY_TAKEAWAYS ===\n  * === SUGGESTED_IMAGE_PROMPTS ===";
        }

        // Fetch terms for selection
        $topics = get_terms( array( 'taxonomy' => 'industry', 'hide_empty' => false ) ); // using industry as topics per CPT_Taxonomy class
        $regions = get_terms( array( 'taxonomy' => 'region', 'hide_empty' => false ) );

        ?>
        <div class="wrap ascendance-ai-studio-wrap" style="max-width: 1200px; margin-top: 20px;">
            <style>
                .studio-container {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 30px;
                    margin-top: 20px;
                }
                .studio-card {
                    background: #0F1E35;
                    border: 1px solid rgba(247, 244, 239, 0.1);
                    border-radius: 8px;
                    padding: 24px;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
                    color: #F7F4EF;
                }
                .studio-card h3 {
                    color: #FFFFFF;
                    border-bottom: 1px solid rgba(247, 244, 239, 0.1);
                    padding-bottom: 12px;
                    margin-top: 0;
                    margin-bottom: 20px;
                    font-family: "Cooper Hewitt", sans-serif;
                }
                .studio-card label {
                    display: block;
                    font-weight: bold;
                    margin-bottom: 8px;
                    color: rgba(247, 244, 239, 0.8);
                }
                .studio-field {
                    width: 100%;
                    background: #0A1628;
                    border: 1px solid rgba(247, 244, 239, 0.1);
                    border-radius: 4px;
                    color: #FFFFFF;
                    padding: 10px;
                    margin-bottom: 20px;
                    font-family: inherit;
                }
                .studio-field:focus {
                    border-color: #BC1B1D;
                    outline: none;
                }
                .studio-checkbox-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 8px;
                    margin-bottom: 20px;
                }
                .studio-checkbox-label {
                    font-weight: normal !important;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                .studio-btn {
                    background: #BC1B1D;
                    color: #FFFFFF;
                    border: none;
                    border-radius: 4px;
                    padding: 12px 24px;
                    font-weight: bold;
                    cursor: pointer;
                    font-family: "Cooper Hewitt", sans-serif;
                    transition: background 0.2s;
                }
                .studio-btn:hover {
                    background: #9E1416;
                }
                .studio-btn:disabled {
                    background: #555;
                    cursor: not-allowed;
                }
                .usage-bar-outer {
                    width: 100%;
                    background: #0A1628;
                    border-radius: 4px;
                    height: 12px;
                    margin-top: 8px;
                    overflow: hidden;
                    border: 1px solid rgba(247, 244, 239, 0.1);
                }
                .usage-bar-inner {
                    height: 100%;
                    background: #BC1B1D;
                    width: 0%;
                    transition: width 0.3s;
                }
                .draft-container {
                    background: #030810;
                    border: 1px solid #BC1B1D;
                    color: #00FF66;
                    font-family: "JetBrains Mono", monospace;
                    padding: 20px;
                    border-radius: 4px;
                    min-height: 400px;
                    max-height: 600px;
                    overflow-y: auto;
                    white-space: pre-wrap;
                    box-shadow: 0 0 15px rgba(188, 27, 29, 0.2);
                }
                .draft-toolbar {
                    display: flex;
                    gap: 10px;
                    margin-bottom: 12px;
                    justify-content: flex-end;
                }
                .draft-toolbar button {
                    background: rgba(255, 255, 255, 0.1);
                    color: #00FF66;
                    border: 1px solid #00FF66;
                    padding: 4px 10px;
                    border-radius: 2px;
                    cursor: pointer;
                    font-size: 11px;
                }
                .draft-toolbar button:hover {
                    background: rgba(0, 255, 102, 0.1);
                }
            </style>

            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #BC1B1D; padding-bottom: 15px; margin-bottom: 25px;">
                <h1 style="margin: 0; font-family: 'Cooper Hewitt', sans-serif; font-weight: bold; color: #0F1E35;">
                    <i class="dashicons dashicons-wand" style="font-size: 28px; width: 28px; height: 28px; margin-right: 10px; color: #BC1B1D;"></i>
                    ASCENDANCE &middot; AI Studio
                </h1>
                <div style="font-family: 'JetBrains Mono', monospace; text-align: right; font-size: 12px;">
                    <div><?php esc_html_e( 'System Status: Active', 'ascendance-core' ); ?></div>
                    <div style="color: #BC1B1D; font-weight: bold;"><?php printf( __( 'Usage: $%s / $%s', 'ascendance-core' ), number_format( $current_cost, 2 ), number_format( $cap, 2 ) ); ?></div>
                </div>
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
                                    <option value="anthropic">Anthropic Claude (Recommended)</option>
                                    <option value="openai">OpenAI GPT-4o (Alt)</option>
                                    <option value="gemini">Google Gemini 1.5 Pro (Alt)</option>
                                </select>
                            </div>
                        </div>

                        <label for="article_topic"><?php esc_html_e( 'Primary Focus / Industry', 'ascendance-core' ); ?></label>
                        <select id="article_topic" class="studio-field">
                            <?php foreach($topics as $topic) : ?>
                                <option value="<?php echo esc_attr($topic->name); ?>"><?php echo esc_html($topic->name); ?></option>
                            <?php endforeach; ?>
                        </select>

                        <label><?php esc_html_e( 'Geographic Regions', 'ascendance-core' ); ?></label>
                        <div class="studio-checkbox-grid">
                            <?php foreach($regions as $region) : ?>
                                <label class="studio-checkbox-label">
                                    <input type="checkbox" class="region-checkbox" value="<?php echo esc_attr($region->name); ?>" />
                                    <?php echo esc_html($region->name); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <label for="source_notes"><?php esc_html_e( 'Source Material & Briefing Notes', 'ascendance-core' ); ?></label>
                        <textarea id="source_notes" class="studio-field" style="height: 180px;" placeholder="Paste raw stakeholder briefings, press releases, reports, or topic outlines here. AI will extract claims and compile the analysis based on these inputs..."></textarea>

                        <label for="custom_prompt"><?php esc_html_e( 'Special Writing Instructions (Optional)', 'ascendance-core' ); ?></label>
                        <input type="text" id="custom_prompt" class="studio-field" placeholder="e.g. Focus on geopolitical impact, make it highly cautious, or reference LAR." />

                        <button id="generate_btn" class="studio-btn" style="width: 100%;" <?php disabled( $current_cost >= $cap ); ?>>
                            <span id="btn_text"><i class="dashicons dashicons-admin-generic" style="margin-top: -3px; animation: spin 2s linear infinite; display: none;" id="btn_spinner"></i> <?php esc_html_e( 'COMPILE INTELLIGENCE DRAFT', 'ascendance-core' ); ?></span>
                        </button>

                        <div style="margin-top: 25px; border-top: 1px dashed rgba(247, 244, 239, 0.1); padding-top: 15px;">
                            <div style="display:flex; justify-content:space-between; font-size: 11px;">
                                <span><?php esc_html_e( 'MONTHLY BUDGET CAP PROGRESS', 'ascendance-core' ); ?></span>
                                <span><?php echo $percent; ?>%</span>
                            </div>
                            <div class="usage-bar-outer">
                                <div class="usage-bar-inner" style="width: <?php echo $percent; ?>%;"></div>
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
            </div>

            <!-- Settings accordion -->
            <div class="studio-card" style="margin-top: 30px; background: #0A1628;">
                <h3><i class="dashicons dashicons-admin-settings" style="color: #BC1B1D;"></i> AI SYSTEM PROMPT ENGINE SETTINGS</h3>
                <form method="post" action="">
                    <?php wp_nonce_field('ascendance_ai_settings_action', 'ascendance_ai_settings_nonce'); ?>
                    
                    <div style="display: grid; grid-template-columns: 1fr 4fr; gap: 20px;">
                        <div>
                            <label for="ai_monthly_cap"><?php esc_html_e( 'Monthly Cap ($)', 'ascendance-core' ); ?></label>
                            <input type="number" step="10" name="ai_monthly_cap" id="ai_monthly_cap" class="studio-field" value="<?php echo esc_attr( $cap ); ?>" required />
                        </div>
                        <div>
                            <label for="ai_system_prompt"><?php esc_html_e( 'System Writing Prompt', 'ascendance-core' ); ?></label>
                            <textarea name="ai_system_prompt" id="ai_system_prompt" class="studio-field" style="height: 200px; font-family: monospace; font-size:11px;"><?php echo esc_textarea($system_prompt); ?></textarea>
                        </div>
                    </div>
                    <input type="submit" name="save_ai_settings" class="studio-btn" value="Save System Prompt Settings" />
                </form>
            </div>
        </div>

        <script>
            jQuery(document).ready(function($) {
                let lastResultText = '';
                
                $('#generate_btn').click(function() {
                    const articleType = $('#article_type').val();
                    const aiProvider = $('#ai_provider').val();
                    const topic = $('#article_topic').val();
                    const notes = $('#source_notes').val();
                    const customPrompt = $('#custom_prompt').val();
                    
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
                    $('#draft_output').html('> Contacting security API channels...\n> Initiating intelligence compilation sequence...');

                    $.ajax({
                        url: '/Ascendance/wp-json/ascendance/v1/ai-studio/generate',
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            type: articleType,
                            provider: aiProvider,
                            topic: topic,
                            regions: regions,
                            notes: notes,
                            custom_prompt: customPrompt
                        }),
                        success: function(response) {
                            $('#btn_spinner').hide();
                            $('#generate_btn').attr('disabled', false);
                            
                            if (response.text) {
                                lastResultText = response.text;
                                $('#draft_output').text(response.text);
                                $('#output_model_info').text('Model: ' + response.model);
                                $('#output_cost_info').text('Cost: $' + response.cost.toFixed(4));
                                $('#draft_actions').show();
                            } else {
                                $('#draft_output').text('> Error parsing compilation payload.');
                            }
                        },
                        error: function(xhr) {
                            $('#btn_spinner').hide();
                            $('#generate_btn').attr('disabled', false);
                            let errMsg = 'API Connection Exception.';
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errMsg = xhr.responseJSON.error;
                            }
                            $('#draft_output').html('> ERROR SEQUENCE INITIATED:\n> ' + errMsg);
                        }
                    });
                });

                // Helper for section adjustments
                function adjustSection(instruction) {
                    if(!lastResultText) return;
                    
                    $('#draft_output').html('> Gating section parameters...\n> Running refinement filters...');
                    
                    $.ajax({
                        url: '/Ascendance/wp-json/ascendance/v1/ai-studio/regenerate-section',
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            content: lastResultText,
                            instruction: instruction,
                            provider: $('#ai_provider').val(),
                            context: $('#source_notes').val()
                        }),
                        success: function(response) {
                            if (response.text) {
                                lastResultText = response.text;
                                $('#draft_output').text(response.text);
                                $('#output_model_info').text('Model: ' + response.model);
                                $('#output_cost_info').text('Cost: $' + response.cost.toFixed(4));
                            }
                        },
                        error: function() {
                            $('#draft_output').text('> Refinement routine failed.');
                        }
                    });
                }

                $('#shorten_btn').click(() => adjustSection('Shorten this text significantly'));
                $('#expand_btn').click(() => adjustSection('Expand details and explain background'));
                $('#cautious_btn').click(() => adjustSection('Refine tone to be extremely objective and cautious'));

                // Push to WP Draft programmatically
                $('#push_to_wp_btn').click(function() {
                    if(!lastResultText) return;
                    
                    // Call a quick REST endpoint or open the editor with prefilled content
                    // To keep it simple, we can construct a form post to post-new.php or let them copy it.
                    // Let's implement programmatic post creation via ajax or construct draft creation!
                    // Let's create a quick ajax handler or redirect to post-new.php with query args.
                    // For WordPress Gutenberg, redirecting is standard, but creating a post via ajax is much cooler.
                    // Let's redirect to wp-admin/post-new.php?post_type=brief&ai_import=1 and handle it via localstorage or cookie.
                    // Or let's just trigger a draft save via REST API!
                    // Let's write a small script that handles it. For now, let's copy to clipboard and alert.
                    navigator.clipboard.writeText(lastResultText);
                    alert('Draft content copied to clipboard! You can now create a new Brief/Update and paste the content.');
                });
            });
        </script>
        <?php
    }
}
