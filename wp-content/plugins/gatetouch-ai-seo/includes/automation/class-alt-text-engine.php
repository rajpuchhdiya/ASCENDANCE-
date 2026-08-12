<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin bulk image scan needs a bounded attachment/meta join to find missing alt text efficiently.

/**
 * AI-Powered Alt-Text Generator (Vision AI)
 */
class GateTouch_Alt_Text_Generator {

    public function __construct() {
        if ( is_admin() ) {
            add_action( 'wp_ajax_gatetouch_generate_alt_text', [ $this, 'ajax_generate_alt' ] );
            add_action( 'wp_ajax_gatetouch_get_unoptimized_images', [ $this, 'ajax_get_unoptimized' ] );
            add_action( 'add_meta_boxes', [ $this, 'add_media_meta_box' ], 10, 2 );
        }
    }

    /**
     * Get IDs of images missing alt text
     */
    public function ajax_get_unoptimized() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'upload_files' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );

        global $wpdb;
        // Find attachments with no alt meta or empty alt meta
        $ids = $wpdb->get_col( "
            SELECT p.ID FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON (p.ID = pm.post_id AND pm.meta_key = '_wp_attachment_image_alt')
            WHERE p.post_type = 'attachment'
            AND p.post_mime_type LIKE 'image/%'
            AND (pm.meta_value IS NULL OR pm.meta_value = '')
            LIMIT 50
        " );

        wp_send_json_success( [ 'ids' => array_map( 'intval', $ids ) ] );
    }

    /**
     * AJAX handler for single image alt-text generation
     */
    public function ajax_generate_alt() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'upload_files' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );

        $attachment_id = isset( $_POST['attachment_id'] ) ? absint( wp_unslash( $_POST['attachment_id'] ) ) : 0;
        if ( ! $attachment_id ) wp_send_json_error( __( 'Invalid Attachment ID.', 'gatetouch-ai-seo' ) );

        $data = $this->generate_for_attachment( $attachment_id );

        if ( is_wp_error( $data ) ) {
            wp_send_json_error( $data->get_error_message() );
        }

        wp_send_json_success( $data );
    }

    /**
     * Core logic to get Alt-Text via Vision AI
     */
    public function generate_for_attachment( $attachment_id ) {
        $image_url = wp_get_attachment_url( $attachment_id );
        if ( ! $image_url ) return new WP_Error( 'no_url', 'No image URL found.' );

        $prompt = "Analyze this image for SEO. Provide:
1. Alt Text: Descriptive and keyword-rich (max 15 words).
2. Caption: A natural sentence describing context.
3. Filename Suggestion: SEO-friendly, lowercase, hyphenated (e.g. blue-mountain-sunset.jpg).

Respond ONLY with valid JSON: {\"alt_text\": \"...\", \"caption\": \"...\", \"filename_suggestion\": \"...\"}";

        $result = GateTouch_AI_Engine::call_vision( $image_url, $prompt );

        if ( isset( $result['error'] ) ) {
            return new WP_Error( 'ai_error', $result['error'] );
        }

        $alt_text = sanitize_text_field( $result['alt_text'] ?? '' );
        $caption  = sanitize_textarea_field( $result['caption'] ?? '' );

        if ( $alt_text ) {
            update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt_text );
        }

        if ( $caption ) {
            wp_update_post( [
                'ID'           => $attachment_id,
                'post_excerpt' => $caption, // attachment_excerpt is the caption
            ] );
        }

        return $result;
    }

    /**
     * Add a "Generate via AI" button to the Media Edit screen
     */
    public function add_media_meta_box( $screen, $post ) {
        if ( $screen === 'attachment' ) {
            add_meta_box(
                'gatetouch-ai-alt',
                'GT SEO/GEO/AEO Vision AI 2.0',
                [ $this, 'render_media_meta_box' ],
                'attachment',
                'side',
                'high'
            );
        }
    }

    public function render_media_meta_box( $post ) {
        ?>
        <div class="gatetouch-media-box">
            <p style="font-size:12px; color:#64748b; margin-bottom:12px;">Optimize your media with Vision AI. This will auto-fill Alt Text and Caption, and suggest a better Filename.</p>

            <button type="button"
                    id="gatetouch-generate-media-btn"
                    class="button button-primary"
                    data-id="<?php echo esc_attr($post->ID); ?>"
                    style="width:100%; justify-content:center; display:flex; align-items:center; gap:8px; background:#6366f1; border:none; padding:8px; cursor:pointer;">
                <span>🪄 AI Media Analysis</span>
            </button>

            <div id="gatetouch-media-status" style="margin-top:10px; font-size:11px; display:none; color:#6366f1; font-weight:700; text-align:center;">
                🧠 Analyzing Visual Context...
            </div>

            <div id="gatetouch-media-results" style="display:none; margin-top:16px;">
                <div style="padding:12px; background:#f0f9ff; border:1px solid #bae6fd; border-radius:10px;">
                    <div style="font-size:11px; font-weight:800; color:#0369a1; text-transform:uppercase; margin-bottom:8px;">AI Suggestions</div>

                    <div style="margin-bottom:8px;">
                        <label style="font-size:10px; font-weight:700; color:#64748b; display:block;">Suggested Filename</label>
                        <code id="riq-suggest-filename" style="font-size:11px;"></code>
                    </div>

                    <div style="font-size:12px; color:#0c4a6e; line-height:1.4;">
                        <strong>Alt:</strong> <span id="riq-suggest-alt"></span>
                    </div>
                </div>
            </div>
        </div>
        <?php // script block removed — moved to assets/js/admin-global.js ?>
        <?php
    }
}

new GateTouch_Alt_Text_Generator();
