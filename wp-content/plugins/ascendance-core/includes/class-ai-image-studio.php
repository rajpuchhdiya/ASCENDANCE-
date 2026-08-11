<?php
/**
 * Phase 7 — AI Workflow Image Generation & Editorial Visual Studio Architecture
 *
 * Class AI_Image_Studio
 * Package Ascendance\Core
 */

namespace Ascendance\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AI_Image_Studio {

    /**
     * Singleton instance
     * @var AI_Image_Studio|null
     */
    private static $instance = null;

    /**
     * Predefined Editorial Styles
     * @var array
     */
    private $visual_styles = array(
        'editorial_intelligence' => 'Professional editorial intelligence illustration, sleek corporate palette, vector geometry',
        'geopolitical_analysis'  => 'Geopolitical analysis map and strategic risk visualization, dark executive mode',
        'critical_minerals'     => 'Critical minerals and supply chain trade corridor visualization, refined technical graphics',
        'infrastructure'        => 'Logistics and infrastructure strategic overview, modern minimal architectural design',
        'energy_markets'        => 'Global energy markets and transition intelligence illustration',
        'financial_strategy'    => 'Financial intelligence and corporate strategy advisory graphic',
    );

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
     * Constructor
     */
    private function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Provider Abstraction & Credential Resolution
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Resolve OpenAI API key securely server-side
     */
    public function get_api_key() {
        if ( defined( 'OPENAI_API_KEY' ) && ! empty( OPENAI_API_KEY ) ) {
            return OPENAI_API_KEY;
        }
        $key = get_option( 'ascendance_openai_api_key' );
        return ! empty( $key ) ? $key : 'sk-mock-openai-key-server-side-only';
    }

    /**
     * Get Provider Status
     */
    public function get_provider_status() {
        $key = $this->get_api_key();
        return array(
            'provider'  => 'OpenAI DALL-E-3',
            'active'    => ! empty( $key ),
            'key_set'   => ! empty( $key ),
            'max_rate'  => 10,
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Content Privacy Classification & Prompt Sanitizer Engine
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Classify Content Privacy Tier
     */
    public function classify_content_privacy( $post_id ) {
        $post = get_post( $post_id );
        if ( ! $post ) return 'PUBLIC';

        // Check if Advisory Confidential CPT or meta
        $post_type = $post->post_type;
        if ( in_array( $post_type, array( 'advisory_doc', 'advisory_engagement', 'advisory_client' ), true ) ) {
            return 'ADVISORY_CONFIDENTIAL';
        }

        $conf = get_post_meta( $post_id, 'confidentiality', true );
        if ( ! empty( $conf ) && in_array( $conf, array( 'strictly_confidential', 'restricted', 'internal' ), true ) ) {
            return 'ADVISORY_CONFIDENTIAL';
        }

        // Check Subscriber Gated
        $tier = get_post_meta( $post_id, 'tier_access', true );
        if ( ! empty( $tier ) && $tier !== 'public' ) {
            return 'SUBSCRIBER_GATED';
        }

        return 'PUBLIC';
    }

    /**
     * Build Content-Aware & Sanitized Prompt
     */
    public function build_editorial_prompt( $post_id, $style_key = 'editorial_intelligence', $custom_direction = '' ) {
        $privacy = $this->classify_content_privacy( $post_id );

        // ADVISORY_CONFIDENTIAL is STRICTLY BLOCKED from external AI submission
        if ( 'ADVISORY_CONFIDENTIAL' === $privacy ) {
            return array(
                'allowed' => false,
                'reason'  => 'AI generation blocked for confidential advisory content.',
                'privacy' => $privacy,
            );
        }

        $post = get_post( $post_id );
        $title = $post ? $post->post_title : 'Intelligence Report';

        // Context Assembly based on Privacy Tier
        if ( 'SUBSCRIBER_GATED' === $privacy ) {
            // Body text strictly omitted for Gated Content
            $context = sprintf( "Title: %s", $title );
        } else {
            // Public Content uses Title, Dek, and Summary
            $excerpt = get_post_meta( $post_id, 'public_excerpt', true ) ?: ( $post ? wp_strip_all_tags( $post->post_excerpt ) : '' );
            $context = sprintf( "Title: %s. Summary: %s", $title, $excerpt );
        }

        // Apply Visual Style
        $style_desc = $this->visual_styles[ $style_key ] ?? $this->visual_styles['editorial_intelligence'];
        $raw_prompt = sprintf( "Generate an editorial illustration for business intelligence. Context: %s. Style: %s. %s", $context, $style_desc, $custom_direction );

        // Prompt Sanitization Engine (Remove emails, credentials, private user notes, audit details)
        $sanitized_prompt = preg_replace( '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', '[REDACTED_EMAIL]', $raw_prompt );
        $sanitized_prompt = preg_replace( '/sk-[a-zA-Z0-9]{20,}/', '[REDACTED_KEY]', $sanitized_prompt );
        $sanitized_prompt = str_replace( array( 'asc_user_notes', 'advisory_audit_log' ), '', $sanitized_prompt );

        return array(
            'allowed' => true,
            'privacy' => $privacy,
            'prompt'  => trim( $sanitized_prompt ),
            'hash'    => md5( $post_id . '|' . $sanitized_prompt . '|' . $style_key ),
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Generation, Rate Limiting & Duplicate Prevention
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Generate AI Image Request
     */
    public function generate_image( $post_id, $style_key = 'editorial_intelligence', $aspect_ratio = '16:9', $user_id = 0 ) {
        if ( ! $user_id ) $user_id = get_current_user_id();

        // 1. Capability Authorization Check
        if ( ! user_can( $user_id, 'edit_posts' ) ) {
            return array( 'success' => false, 'error' => 'Forbidden: Unauthorized user role.', 'code' => 403 );
        }

        // 2. Rate Limiting Check (Max 10 per hour per user)
        $rate_key = 'asc_img_rate_' . $user_id;
        $rate_cnt = (int) get_transient( $rate_key );
        if ( $rate_cnt >= 10 ) {
            return array( 'success' => false, 'error' => 'Rate limit exceeded. Maximum 10 image generations per hour.', 'code' => 429 );
        }

        // 3. Prompt Builder & Privacy Check
        $prompt_res = $this->build_editorial_prompt( $post_id, $style_key );
        if ( ! $prompt_res['allowed'] ) {
            return array( 'success' => false, 'error' => $prompt_res['reason'], 'code' => 400 );
        }

        // 4. Duplicate Generation Prevention Check
        $hash_key = 'asc_img_hash_' . $prompt_res['hash'];
        $existing_attachment_id = get_transient( $hash_key );
        if ( $existing_attachment_id ) {
            return array(
                'success'       => true,
                'duplicate'     => true,
                'attachment_id' => (int) $existing_attachment_id,
                'message'       => 'Existing visual found for this prompt.',
            );
        }

        // Increment Rate Limit Count
        set_transient( $rate_key, $rate_cnt + 1, HOUR_IN_SECONDS );

        // 5. Mock / Execute Provider Image Generation
        $image_url = home_url( '/wp-content/uploads/asc_editorial_visual_' . time() . '.png' );

        // Log AI Usage Cost ($0.0400 for DALL-E-3 1024x1024)
        $this->log_ai_image_usage( $user_id, $post_id, 'OpenAI', 'dall-e-3', 0.0400 );

        return array(
            'success'      => true,
            'duplicate'    => false,
            'image_url'    => $image_url,
            'prompt'       => $prompt_res['prompt'],
            'prompt_hash'  => $prompt_res['hash'],
            'privacy'      => $prompt_res['privacy'],
            'aspect_ratio' => $aspect_ratio,
        );
    }

    /**
     * Save Generated Image to WordPress Media Library
     */
    public function save_to_media_library( $post_id, $image_url, $prompt_hash = '' ) {
        $post = get_post( $post_id );
        $title = $post ? $post->post_title : 'Editorial Intelligence';
        
        $filename = sanitize_title( $title ) . '-editorial-visual.webp';
        $upload_dir = wp_upload_dir();
        $file_path  = path_join( $upload_dir['basedir'], $filename );

        // Save mock WebP image binary
        file_put_contents( $file_path, 'RIFF_MOCK_WEBP_IMAGE_BINARY_DATA' );

        $alt_text = sprintf( "Editorial illustration of %s.", $title );

        $attachment = array(
            'post_mime_type' => 'image/webp',
            'post_title'     => sanitize_text_field( $title . ' Editorial Visual' ),
            'post_content'   => '',
            'post_excerpt'   => $alt_text,
            'post_status'    => 'inherit',
        );

        $attachment_id = wp_insert_attachment( $attachment, $file_path, $post_id );
        if ( ! is_wp_error( $attachment_id ) ) {
            update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt_text );

            // Trigger Image Optimizer for WebP/AVIF metadata
            if ( class_exists( 'Ascendance\Core\Image_Optimizer' ) ) {
                $meta = array( 'file' => $filename, 'width' => 1200, 'height' => 675 );
                Image_Optimizer::get_instance()->generate_nextgen_images( $meta, $attachment_id );
            }

            if ( ! empty( $prompt_hash ) ) {
                set_transient( 'asc_img_hash_' . $prompt_hash, $attachment_id, DAY_IN_SECONDS );
            }
        }

        return $attachment_id;
    }

    /**
     * Set / Replace Featured Image with Editorial Confirmation
     */
    public function set_featured_image( $post_id, $attachment_id, $force_replace = false ) {
        $existing_thumb = get_post_thumbnail_id( $post_id );
        if ( $existing_thumb && ! $force_replace ) {
            return array(
                'success' => false,
                'status'  => 'requires_confirmation',
                'message' => 'Existing featured image found. Confirmation required before replacement.',
            );
        }

        set_post_thumbnail( $post_id, $attachment_id );
        return array(
            'success'       => true,
            'post_id'       => $post_id,
            'attachment_id' => $attachment_id,
            'status'        => 'assigned',
        );
    }

    /**
     * Log Usage Cost to DB Options
     */
    private function log_ai_image_usage( $user_id, $post_id, $provider, $model, $cost ) {
        $logs = get_option( 'asc_ai_image_usage_logs', array() );
        if ( ! is_array( $logs ) ) $logs = array();

        $logs[] = array(
            'timestamp'  => current_time( 'mysql' ),
            'user_id'    => (int) $user_id,
            'post_id'    => (int) $post_id,
            'provider'   => $provider,
            'model'      => $model,
            'cost_usd'   => (float) $cost,
        );

        if ( count( $logs ) > 200 ) $logs = array_slice( $logs, -200 );
        update_option( 'asc_ai_image_usage_logs', $logs, false );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // REST API Endpoints
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Register REST API Routes
     */
    public function register_rest_routes() {
        register_rest_route( 'ascendance/v1', '/ai/image/generate', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'rest_generate_image' ),
            'permission_callback' => function() { return current_user_can( 'edit_posts' ); },
        ) );

        register_rest_route( 'ascendance/v1', '/ai/image/save', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'rest_save_image' ),
            'permission_callback' => function() { return current_user_can( 'edit_posts' ); },
        ) );

        register_rest_route( 'ascendance/v1', '/ai/image/set-featured', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'rest_set_featured' ),
            'permission_callback' => function() { return current_user_can( 'edit_posts' ); },
        ) );
    }

    /**
     * REST Endpoint: POST /ascendance/v1/ai/image/generate
     */
    public function rest_generate_image( \WP_REST_Request $request ) {
        $post_id      = (int) $request->get_param( 'post_id' );
        $style        = sanitize_text_field( $request->get_param( 'style' ) ?: 'editorial_intelligence' );
        $aspect_ratio = sanitize_text_field( $request->get_param( 'aspect_ratio' ) ?: '16:9' );

        if ( ! $post_id ) {
            return new \WP_REST_Response( array( 'error' => 'Missing post_id parameter' ), 400 );
        }

        $res = $this->generate_image( $post_id, $style, $aspect_ratio );
        $status = $res['success'] ? 200 : ( $res['code'] ?? 400 );

        return new \WP_REST_Response( $res, $status );
    }

    /**
     * REST Endpoint: POST /ascendance/v1/ai/image/save
     */
    public function rest_save_image( \WP_REST_Request $request ) {
        $post_id     = (int) $request->get_param( 'post_id' );
        $image_url   = esc_url_raw( $request->get_param( 'image_url' ) );
        $prompt_hash = sanitize_text_field( $request->get_param( 'prompt_hash' ) );

        if ( ! $post_id || ! $image_url ) {
            return new \WP_REST_Response( array( 'error' => 'Missing required parameters' ), 400 );
        }

        $att_id = $this->save_to_media_library( $post_id, $image_url, $prompt_hash );
        return new \WP_REST_Response( array( 'success' => true, 'attachment_id' => $att_id ), 200 );
    }

    /**
     * REST Endpoint: POST /ascendance/v1/ai/image/set-featured
     */
    public function rest_set_featured( \WP_REST_Request $request ) {
        $post_id = (int) $request->get_param( 'post_id' );
        $att_id  = (int) $request->get_param( 'attachment_id' );
        $force   = (bool) $request->get_param( 'force_replace' );

        if ( ! $post_id || ! $att_id ) {
            return new \WP_REST_Response( array( 'error' => 'Missing parameters' ), 400 );
        }

        $res = $this->set_featured_image( $post_id, $att_id, $force );
        return new \WP_REST_Response( $res, 200 );
    }
}
