<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files are included inside class methods, so render variables are local.
// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Admin list filters use sanitized read-only GET parameters and do not change state.

if ( ! GateTouch_AI_Engine::has_api_key() ) {
    GateTouch_Helpers::page_header( __( 'Social Media AI', 'gatetouch-ai-seo' ), __( 'Transform blog posts into viral social content', 'gatetouch-ai-seo' ) );
    GateTouch_Helpers::api_key_gate( __( 'Social Media AI Generator', 'gatetouch-ai-seo' ) );
    return;
}

$gatetouch_social_ai_labels = [
    'linkedin'     => __( 'LinkedIn', 'gatetouch-ai-seo' ),
    'facebook'     => __( 'Facebook', 'gatetouch-ai-seo' ),
    'twitter'      => __( 'X (Twitter)', 'gatetouch-ai-seo' ),
    'copyLinkedIn' => __( 'Copy LinkedIn Post', 'gatetouch-ai-seo' ),
    'copyFacebook' => __( 'Copy Facebook Post', 'gatetouch-ai-seo' ),
    'copyTwitter'  => __( 'Copy X (Twitter) Post', 'gatetouch-ai-seo' ),
];
wp_localize_script( 'gatetouch-admin', 'gatetouchSocialAiLabels', $gatetouch_social_ai_labels );

ob_start();
?>
jQuery(document).ready(function($) {
    const labels = window.gatetouchSocialAiLabels || {};

    $('.gatetouch-social-row').on('click', function(e) {
        if ($(e.target).hasClass('gatetouch-gen-social')) return;

        const id = $(this).data('id');
        const details = $('#details-' + id);
        const icon = $(this).find('.gatetouch-expand-icon');

        $('.gatetouch-social-details').not(details).hide();
        $('.gatetouch-expand-icon').not(icon).text('▶').css('color', '#94a3b8');

        details.fadeToggle(300);
        icon.text(details.is(':visible') ? '▼' : '▶').css('color', details.is(':visible') ? 'var(--riq-primary)' : '#94a3b8');
    });

    $(document).on('click', '.gatetouch-social-tab-btn', function() {
        const wrap = $(this).closest('.gatetouch-social-tabs-wrap');
        const platform = $(this).data('platform');

        wrap.find('.gatetouch-social-tab-btn').removeClass('active');
        $(this).addClass('active');

        wrap.find('.gatetouch-platform-content').hide();
        wrap.find('.gatetouch-platform-content[data-platform="'+platform+'"]').fadeIn();
    });

    $('.gatetouch-gen-social').on('click', function() {
        const btn = $(this);
        const id = btn.data('id');
        const details = $('#details-' + id);
        const row = btn.closest('.gatetouch-social-row');

        btn.prop('disabled', true).text('🤖 AI Generating...');

        $.post(gatetouchAdmin.ajax_url, {
            action: 'gatetouch_ai_social',
            nonce: gatetouchAdmin.nonce,
            post_id: id
        }, function(res) {
            btn.prop('disabled', false).text('Re-generate').removeClass('gatetouch-btn--ai').addClass('gatetouch-btn--ghost');
            if (res.success) {
                const data = res.data;
                const html = `
                    <div style="padding:30px; border-left:4px solid var(--riq-primary);">
                        <div class="gatetouch-social-tabs-wrap">
                            <div class="gatetouch-social-tabs" style="display:flex; gap:10px; margin-bottom:20px; border-bottom:1px solid #e2e8f0; padding-bottom:10px;">
                                <button class="gatetouch-social-tab-btn active" data-platform="linkedin">${labels.linkedin}</button>
                                <button class="gatetouch-social-tab-btn" data-platform="facebook">${labels.facebook}</button>
                                <button class="gatetouch-social-tab-btn" data-platform="twitter">${labels.twitter}</button>
                            </div>

                            <div class="gatetouch-platform-content" data-platform="linkedin">
                                <textarea class="gatetouch-textarea" style="height:150px; background:#fff;">${data.linkedin}</textarea>
                                <button class="gatetouch-btn gatetouch-btn--ghost gatetouch-btn--sm gatetouch-copy-btn" style="margin-top:10px;">${labels.copyLinkedIn}</button>
                            </div>
                            <div class="gatetouch-platform-content" data-platform="facebook" style="display:none;">
                                <textarea class="gatetouch-textarea" style="height:150px; background:#fff;">${data.facebook}</textarea>
                                <button class="gatetouch-btn gatetouch-btn--ghost gatetouch-btn--sm gatetouch-copy-btn" style="margin-top:10px;">${labels.copyFacebook}</button>
                            </div>
                            <div class="gatetouch-platform-content" data-platform="twitter" style="display:none;">
                                <textarea class="gatetouch-textarea" style="height:150px; background:#fff;">${data.twitter}</textarea>
                                <button class="gatetouch-btn gatetouch-btn--ghost gatetouch-btn--sm gatetouch-copy-btn" style="margin-top:10px;">${labels.copyTwitter}</button>
                            </div>
                        </div>
                    </div>
                `;
                details.find('td').html(html);

                if (!details.is(':visible')) {
                    row.trigger('click');
                }

                window.gatetouchFlash('🎉 Social posts generated and saved!');
            } else {
                alert('Error: ' + res.data);
            }
        });
    });

    $(document).on('click', '.gatetouch-copy-btn', function() {
        const txt = $(this).siblings('textarea');
        txt.select();
        document.execCommand('copy');

        const originalText = $(this).text();
        $(this).text('✅ Copied!').addClass('gatetouch-btn--success');
        setTimeout(() => {
            $(this).text(originalText).removeClass('gatetouch-btn--success');
        }, 2000);
    });
});
<?php
wp_add_inline_script( 'gatetouch-admin', ob_get_clean() );
?>
<div class="gatetouch-card">
    <div class="gatetouch-card__header">
        <div style="display:flex; align-items:center; gap:12px;">
            <span class="rk-icon-box rk-icon-box--indigo"><?php echo wp_kses( GateTouch_Helpers::icon( 'device-mobile', 20 ), GateTouch_Helpers::svg_kses_allowed() ); ?></span>
            <div>
                <h3 style="margin:0; font-size:18px; font-weight:800;"><?php esc_html_e( 'Social Media AI Generator', 'gatetouch-ai-seo' ); ?></h3>
                <p style="margin:2px 0 0; font-size:13px; color:var(--riq-text-light); font-weight:400;"><?php esc_html_e( 'Transform your blog posts into viral social media content.', 'gatetouch-ai-seo' ); ?></p>
            </div>
        </div>
    </div>
    <div class="gatetouch-card__body">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <div class="gatetouch-alert gatetouch-alert--info" style="margin:0; flex:1;">
                Select a post below to generate or manage optimized captions for LinkedIn, Facebook, and X (Twitter).
            </div>
            <div style="margin-left:20px;">
                <form method="get" action="" style="display:flex; gap:10px; align-items:center;">
                    <input type="hidden" name="page" value="gatetouch-content-ai">
                    <input type="hidden" name="tab" value="social">
                    <select name="riq_post_type" class="gatetouch-select" onchange="this.form.submit()" style="height:42px; border-radius:10px; min-width:150px;">
                        <?php 
                        $filter = isset($_GET['riq_post_type']) ? sanitize_key($_GET['riq_post_type']) : 'all';
                        ?>
                        <option value="all" <?php selected($filter, 'all'); ?>>All Content</option>
                        <option value="post" <?php selected($filter, 'post'); ?>>Posts Only</option>
                        <option value="page" <?php selected($filter, 'page'); ?>>Pages Only</option>
                    </select>
                </form>
            </div>
        </div>

        <table class="wp-list-table widefat fixed striped posts" style="border:none; box-shadow:none;">
            <thead>
                <tr>
                    <th style="padding-left:20px;"><?php esc_html_e( 'Post Title', 'gatetouch-ai-seo' ); ?></th>
                    <th><?php esc_html_e( 'Post Type', 'gatetouch-ai-seo' ); ?></th>
                    <th style="width:200px; text-align:right; padding-right:20px;"><?php esc_html_e( 'Actions', 'gatetouch-ai-seo' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $pt_filter = ($filter === 'all') ? ['post', 'page'] : [$filter];
                $posts = get_posts(['post_type' => $pt_filter, 'numberposts' => 20]);
                foreach ($posts as $p) :
                    $meta = get_post_meta($p->ID, GATETOUCH_META_KEY, true) ?: [];
                    $social = $meta['social_ai'] ?? null;
                    $has_content = !empty($social['linkedin']) || !empty($social['facebook']) || !empty($social['twitter']);
                ?>
                <tr class="gatetouch-social-row" data-id="<?php echo esc_attr( $p->ID ); ?>">
                    <td style="padding-left:20px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <span class="gatetouch-expand-icon" style="cursor:pointer; color:#94a3b8;">▶</span>
                            <strong><?php echo esc_html($p->post_title); ?></strong>
                        </div>
                    </td>
                    <td><span class="gatetouch-badge gatetouch-badge--ghost"><?php echo esc_html(get_post_type($p->ID)); ?></span></td>
                    <td style="text-align:right; padding-right:20px;">
                        <button class="gatetouch-btn <?php echo esc_attr( $has_content ? 'gatetouch-btn--ghost' : 'gatetouch-btn--ai' ); ?> gatetouch-btn--sm gatetouch-gen-social" data-id="<?php echo esc_attr( $p->ID ); ?>">
                            <?php echo esc_html( $has_content ? __( 'Re-generate', 'gatetouch-ai-seo' ) : __( 'Generate Social Posts', 'gatetouch-ai-seo' ) ); ?>
                        </button>
                    </td>
                </tr>
                <tr class="gatetouch-social-details" id="details-<?php echo esc_attr( $p->ID ); ?>" style="display:none; background:#f8fafc;">
                    <td colspan="3" style="padding:0; border-top:none;">
                        <div style="padding:30px; border-left:4px solid var(--riq-primary);">
                            <?php if ($has_content) : ?>
                                <div class="gatetouch-social-tabs-wrap">
                                    <div class="gatetouch-social-tabs" style="display:flex; gap:10px; margin-bottom:20px; border-bottom:1px solid #e2e8f0; padding-bottom:10px;">
                                        <button class="gatetouch-social-tab-btn active" data-platform="linkedin"><?php esc_html_e( 'LinkedIn', 'gatetouch-ai-seo' ); ?></button>
                                        <button class="gatetouch-social-tab-btn" data-platform="facebook"><?php esc_html_e( 'Facebook', 'gatetouch-ai-seo' ); ?></button>
                                        <button class="gatetouch-social-tab-btn" data-platform="twitter"><?php esc_html_e( 'X (Twitter)', 'gatetouch-ai-seo' ); ?></button>
                                    </div>
                                    
                                    <div class="gatetouch-platform-content" data-platform="linkedin">
                                        <textarea class="gatetouch-textarea" style="height:150px; background:#fff;"><?php echo esc_textarea($social['linkedin']); ?></textarea>
                                        <button class="gatetouch-btn gatetouch-btn--ghost gatetouch-btn--sm gatetouch-copy-btn" style="margin-top:10px;"><?php esc_html_e( 'Copy LinkedIn Post', 'gatetouch-ai-seo' ); ?></button>
                                    </div>
                                    <div class="gatetouch-platform-content" data-platform="facebook" style="display:none;">
                                        <textarea class="gatetouch-textarea" style="height:150px; background:#fff;"><?php echo esc_textarea($social['facebook']); ?></textarea>
                                        <button class="gatetouch-btn gatetouch-btn--ghost gatetouch-btn--sm gatetouch-copy-btn" style="margin-top:10px;"><?php esc_html_e( 'Copy Facebook Post', 'gatetouch-ai-seo' ); ?></button>
                                    </div>
                                    <div class="gatetouch-platform-content" data-platform="twitter" style="display:none;">
                                        <textarea class="gatetouch-textarea" style="height:150px; background:#fff;"><?php echo esc_textarea($social['twitter']); ?></textarea>
                                        <button class="gatetouch-btn gatetouch-btn--ghost gatetouch-btn--sm gatetouch-copy-btn" style="margin-top:10px;"><?php esc_html_e( 'Copy X (Twitter) Post', 'gatetouch-ai-seo' ); ?></button>
                                    </div>
                                </div>
                            <?php else : ?>
                                <div style="text-align:center; padding:20px; color:#64748b;">
                                    <p><?php esc_html_e( 'No social posts generated yet. Click the button above to start.', 'gatetouch-ai-seo' ); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
