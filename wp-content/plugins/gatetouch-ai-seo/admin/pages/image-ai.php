<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files are included inside class methods, so render variables are local.

if ( ! GateTouch_AI_Engine::has_api_key() ) {
    GateTouch_Helpers::page_header( __( 'AI Image Tools', 'gatetouch-ai-seo' ), __( 'Generate alt text and AI images at scale', 'gatetouch-ai-seo' ) );
    GateTouch_Helpers::api_key_gate( __( 'AI Image Tools', 'gatetouch-ai-seo' ) );
    return;
}

$inline_js = <<<'GATEJS'
jQuery(document).ready(function($) {
    $('#gatetouch-gen-image-btn').on('click', function() {
        const btn = $(this);
        const prompt = $('#gatetouch-image-prompt').val();
        const result = $('#gatetouch-gen-image-result');
        const img = result.find('img');
        const placeholder = result.find('.riq-placeholder-icon');

        if (!prompt) {
            alert('Please enter a prompt.');
            return;
        }

        btn.prop('disabled', true).text('🎨 Painting...');
        img.hide();
        placeholder.show().text('⌛');

        $.post(gatetouchAdmin.ajax_url, {
            action: 'gatetouch_ai_image',
            nonce: gatetouchAdmin.nonce,
            prompt: prompt
        }, function(res) {
            btn.prop('disabled', false).text('Generate Image');
            if (res.success) {
                img.attr('src', res.data.url).show();
                placeholder.hide();
                window.gatetouchFlash('Image generated successfully!');
            } else {
                alert('Error: ' + res.data);
                placeholder.text('🖼️');
            }
        });
    });
});
GATEJS;
wp_add_inline_script( 'gatetouch-admin', $inline_js );
?>

<div class="gatetouch-image-tools-wrap">

    <!-- DALL-E Section (Compact) -->
    <div class="gatetouch-card" style="margin-bottom: 30px; border: 1px solid #e2e8f0; background: #fff;">
        <div class="gatetouch-card__header" style="border-bottom: 1px solid #f1f5f9;">
            <div style="display:flex; align-items:center; gap:12px;">
                <span class="rk-icon-box rk-icon-box--purple"><?php echo wp_kses( GateTouch_Helpers::icon( 'palette', 20 ), GateTouch_Helpers::svg_kses_allowed() ); ?></span>
                <h3 style="margin:0; font-size:16px; font-weight:800;"><?php esc_html_e( 'AI Featured Image Generator', 'gatetouch-ai-seo' ); ?></h3>
            </div>
        </div>
        <div class="gatetouch-card__body">
            <div style="display:flex; gap:20px; align-items: flex-start;">
                <div style="flex: 1;">
                    <p style="margin:0 0 15px; font-size:13px; color:var(--riq-text-light);"><?php esc_html_e( 'Create professional featured images from text prompts using DALL-E 3.', 'gatetouch-ai-seo' ); ?></p>
                    <div style="display:flex; gap:10px;">
                        <input type="text" id="gatetouch-image-prompt" class="gatetouch-input" style="flex:1;" placeholder="e.g. A futuristic enterprise office with robots, cinematic lighting...">
                        <button id="gatetouch-gen-image-btn" class="gatetouch-btn gatetouch-btn--ai"><?php esc_html_e( 'Generate Image', 'gatetouch-ai-seo' ); ?></button>
                    </div>
                </div>
                <div id="gatetouch-gen-image-result" style="width:120px; height:80px; background:#f8fafc; border-radius:10px; display:flex; align-items:center; justify-content:center; border:1px dashed #cbd5e1; overflow:hidden;">
                    <img src="" style="max-width:100%; max-height:100%; display:none;">
                    <span class="riq-placeholder-icon" style="color:#94a3b8;"><?php echo wp_kses( GateTouch_Helpers::icon( 'photo', 28 ), GateTouch_Helpers::svg_kses_allowed() ); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- BULK MEDIA MANAGER INTEGRATION -->
    <?php
    // Signals to the panel that it is embedded, so it renders a section heading
    // instead of a second page-level <h1>.
    $gt_media_embedded = true;
    include GATETOUCH_PATH . 'admin/pages/bulk-media-manager.php';
    unset( $gt_media_embedded );
    ?>

</div>
