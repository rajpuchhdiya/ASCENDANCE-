<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files are included inside class methods, so render variables are local.

$llm_opts    = get_option( 'gatetouch_llms_settings', [] );
$llms_active = ( $llm_opts['enable_llms_txt'] ?? 'no' ) === 'yes';
?>
<div class="gatetouch-aeo-suite">

    <!-- ── Header ── -->
    <div class="gatetouch-card gatetouch-panel--ai" style="margin-bottom:24px;">
        <div class="gatetouch-card__header" style="justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="background:var(--riq-ai-gradient); width:40px; height:40px; border-radius:12px; display:flex; align-items:center; justify-content:center; color:#fff;"><?php echo wp_kses( GateTouch_Helpers::icon( 'brain', 20 ), GateTouch_Helpers::svg_kses_allowed() ); ?></div>
                <div>
                    <h3 style="margin:0; font-size:18px; font-weight:800; color:var(--riq-text);">AEO &amp; GEO Intelligence Center</h3>
                    <p style="margin:2px 0 0; font-size:13px; color:var(--riq-text-light);">Optimize for AI answer engines (AEO) and generative citation engines (GEO — ChatGPT, Perplexity, Google AI Overviews).</p>
                </div>
            </div>
            <div class="gatetouch-badge gatetouch-badge--primary"><?php esc_html_e( 'Active Monitoring', 'gatetouch-ai-seo' ); ?></div>
        </div>
    </div>

    <!-- ── Site-level score cards ── -->
    <div class="gatetouch-stats-grid" style="margin-bottom:24px;" id="gatetouch-signal-cards">

        <!-- AEO average -->
        <div class="gatetouch-stat-card" style="background:#fff;">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
                <div class="rk-icon-box rk-icon-box--sm rk-icon-box--blue"><?php echo wp_kses( GateTouch_Helpers::icon( 'sparkles', 16 ), GateTouch_Helpers::svg_kses_allowed() ); ?></div>
                <strong style="font-size:14px; color:var(--riq-text);"><?php esc_html_e( 'Avg AEO Score', 'gatetouch-ai-seo' ); ?></strong>
            </div>
            <div id="gatetouch-avg-aeo" style="font-size:32px; font-weight:800; color:#3b82f6; line-height:1;">—</div>
            <p style="font-size:12px; color:var(--riq-text-light); margin:6px 0 0;"><?php esc_html_e( 'Answer Engine Optimization across top 15 posts', 'gatetouch-ai-seo' ); ?></p>
        </div>

        <!-- GEO average -->
        <div class="gatetouch-stat-card" style="background:#fff;">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
                <div class="rk-icon-box rk-icon-box--sm rk-icon-box--purple"><?php echo wp_kses( GateTouch_Helpers::icon( 'globe', 16 ), GateTouch_Helpers::svg_kses_allowed() ); ?></div>
                <strong style="font-size:14px; color:var(--riq-text);"><?php esc_html_e( 'Avg GEO Score', 'gatetouch-ai-seo' ); ?></strong>
            </div>
            <div id="gatetouch-avg-geo" style="font-size:32px; font-weight:800; color:#a855f7; line-height:1;">—</div>
            <p style="font-size:12px; color:var(--riq-text-light); margin:6px 0 0;"><?php esc_html_e( 'Generative Engine Optimization across top 15 posts', 'gatetouch-ai-seo' ); ?></p>
        </div>

        <!-- llms.txt signal -->
        <div class="gatetouch-stat-card" style="background:#fff;" id="gatetouch-llms-card">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
                <div class="rk-icon-box rk-icon-box--sm rk-icon-box--green"><?php echo wp_kses( GateTouch_Helpers::icon( 'file-text', 16 ), GateTouch_Helpers::svg_kses_allowed() ); ?></div>
                <strong style="font-size:14px; color:var(--riq-text);"><?php esc_html_e( 'llms.txt Protocol', 'gatetouch-ai-seo' ); ?></strong>
            </div>
            <?php if ( $llms_active ) : ?>
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                    <span style="color:#10b981; font-weight:700; font-size:14px;"><?php esc_html_e( '✓ Published', 'gatetouch-ai-seo' ); ?></span>
                </div>
                <p style="font-size:11.5px; color:var(--riq-text-light); line-height:1.5; margin:0 0 10px;"><?php esc_html_e( 'Served for forward compatibility. No major AI engine currently uses it to pick citations, so it does not affect your score.', 'gatetouch-ai-seo' ); ?></p>
                <a href="<?php echo esc_url( home_url( '/llms.txt' ) ); ?>" target="_blank" rel="noopener" class="gatetouch-btn gatetouch-btn--ghost gatetouch-btn--sm"><?php esc_html_e( 'Preview file', 'gatetouch-ai-seo' ); ?></a>
            <?php else : ?>
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                    <span style="color:#64748b; font-weight:700; font-size:14px;"><?php esc_html_e( 'Not published', 'gatetouch-ai-seo' ); ?></span>
                </div>
                <p style="font-size:11.5px; color:var(--riq-text-light); line-height:1.5; margin:0 0 10px;"><?php esc_html_e( 'Optional. Publishing it costs nothing and prepares you if engines adopt the standard, but it will not change your score today.', 'gatetouch-ai-seo' ); ?></p>
                <button id="gatetouch-enable-llms" class="gatetouch-btn gatetouch-btn--primary gatetouch-btn--sm"><?php esc_html_e( 'Publish llms.txt', 'gatetouch-ai-seo' ); ?></button>
            <?php endif; ?>
        </div>

    </div>

    <!-- ── Site-wide GEO infrastructure ── -->
    <div class="gatetouch-card" style="margin-bottom:24px;">
        <div class="gatetouch-card__header" style="justify-content:space-between;">
            <h4 style="margin:0; font-size:15px; font-weight:700;"><?php esc_html_e( 'Site-wide AI visibility', 'gatetouch-ai-seo' ); ?></h4>
            <span style="font-size:12px; color:var(--riq-text-light);"><?php esc_html_e( 'Properties of the whole site, not of any single post', 'gatetouch-ai-seo' ); ?></span>
        </div>
        <div class="gatetouch-card__body" style="padding:20px;">
            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:16px;">
                <?php
                $gt_signal_style = [
                    'pass' => [ '#10b981', '#f0fdf4', '#bbf7d0', __( 'Good', 'gatetouch-ai-seo' ) ],
                    'warn' => [ '#f59e0b', '#fffbeb', '#fde68a', __( 'Needs attention', 'gatetouch-ai-seo' ) ],
                    'fail' => [ '#ef4444', '#fef2f2', '#fecaca', __( 'Blocking visibility', 'gatetouch-ai-seo' ) ],
                    'info' => [ '#64748b', '#f8fafc', '#e2e8f0', __( 'Optional', 'gatetouch-ai-seo' ) ],
                ];
                $gt_signal_link = [
                    'ai_crawlers' => [ admin_url( 'admin.php?page=gatetouch-settings&tab=files' ), __( 'Review crawler access →', 'gatetouch-ai-seo' ) ],
                    'entity'      => [ admin_url( 'admin.php?page=gatetouch-settings&tab=appearance' ), __( 'Set your Organization →', 'gatetouch-ai-seo' ) ],
                    'llms_txt'    => [ admin_url( 'admin.php?page=gatetouch-settings&tab=files' ), __( 'Manage llms.txt →', 'gatetouch-ai-seo' ) ],
                ];

                foreach ( GateTouch_Scoring_Engine::site_geo_signals() as $gt_key => $gt_signal ) :
                    list( $gt_c, $gt_bg, $gt_bd, $gt_word ) = $gt_signal_style[ $gt_signal['status'] ] ?? $gt_signal_style['info'];
                    ?>
                    <div style="border:1px solid <?php echo esc_attr( $gt_bd ); ?>; background:<?php echo esc_attr( $gt_bg ); ?>; border-radius:12px; padding:16px;">
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                            <span style="width:9px; height:9px; border-radius:50%; background:<?php echo esc_attr( $gt_c ); ?>; flex-shrink:0;"></span>
                            <strong style="font-size:13px; color:var(--riq-text);"><?php echo esc_html( $gt_signal['label'] ); ?></strong>
                            <span style="margin-left:auto; font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:0.04em; color:<?php echo esc_attr( $gt_c ); ?>;"><?php echo esc_html( $gt_word ); ?></span>
                        </div>
                        <p style="font-size:12px; color:var(--riq-text-light); line-height:1.55; margin:0 0 10px;"><?php echo esc_html( $gt_signal['note'] ); ?></p>
                        <?php if ( isset( $gt_signal_link[ $gt_key ] ) ) : ?>
                            <a href="<?php echo esc_url( $gt_signal_link[ $gt_key ][0] ); ?>" style="font-size:11.5px; font-weight:700; color:var(--riq-primary); text-decoration:none;"><?php echo esc_html( $gt_signal_link[ $gt_key ][1] ); ?></a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ── GEO signal breakdown ── -->
    <div class="gatetouch-card" style="margin-bottom:24px;">
        <div class="gatetouch-card__header">
            <h4 style="margin:0; font-size:15px; font-weight:700;"><?php esc_html_e( 'Per-post GEO signals', 'gatetouch-ai-seo' ); ?></h4>
            <span style="font-size:12px; color:var(--riq-text-light);"><?php esc_html_e( 'Scored on every post — no API key required', 'gatetouch-ai-seo' ); ?></span>
        </div>
        <div class="gatetouch-card__body" style="padding:20px;">
            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:16px;">
                <?php
                $gt_geo_signals = [
                    [ 'file-text', 'purple', __( 'Passage citability', 'gatetouch-ai-seo' ), 30,
                      __( 'AI engines lift self-contained blocks of roughly 134-167 words. Sections in that range score full points, plus a direct answer in the opening 60 words.', 'gatetouch-ai-seo' ),
                      __( 'Fix: split long sections under their own headings', 'gatetouch-ai-seo' ), null ],
                    [ 'chart-bar', 'indigo', __( 'Evidence density', 'gatetouch-ai-seo' ), 20,
                      __( 'Specific figures and citations to primary sources. Concrete, attributable claims get quoted; general statements do not.', 'gatetouch-ai-seo' ),
                      __( 'Fix: add real numbers and link the source', 'gatetouch-ai-seo' ), null ],
                    [ 'check-circle', 'green', __( 'Authority & freshness', 'gatetouch-ai-seo' ), 20,
                      __( 'An author bio with credentials, and a recent update date. Both weigh heavily when an engine picks between competing sources.', 'gatetouch-ai-seo' ),
                      __( 'Fix: complete author profiles, refresh stale posts', 'gatetouch-ai-seo' ), admin_url( 'profile.php' ) ],
                    [ 'link', 'indigo', __( 'Topical authority', 'gatetouch-ai-seo' ), 20,
                      __( 'Posts with three or more internal links score full points. AI citation favours sites with deep topical coverage, not isolated pages.', 'gatetouch-ai-seo' ),
                      __( 'Fix: use Link Assistant', 'gatetouch-ai-seo' ), admin_url( 'admin.php?page=gatetouch-audit&tab=links' ) ],
                    [ 'photo', 'purple', __( 'Multi-modal content', 'gatetouch-ai-seo' ), 10,
                      __( 'Pages combining text with images, tables or video are selected as sources materially more often than text-only pages.', 'gatetouch-ai-seo' ),
                      __( 'Fix: add a comparison table or diagram', 'gatetouch-ai-seo' ), null ],
                ];

                foreach ( $gt_geo_signals as list( $gt_icon, $gt_tone, $gt_label, $gt_pts, $gt_desc, $gt_fix, $gt_url ) ) :
                    ?>
                    <div style="border:1px solid #e2e8f0; border-radius:12px; padding:16px;">
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                            <span class="rk-icon-box rk-icon-box--sm rk-icon-box--<?php echo esc_attr( $gt_tone ); ?>"><?php echo wp_kses( GateTouch_Helpers::icon( $gt_icon, 14 ), GateTouch_Helpers::svg_kses_allowed() ); ?></span>
                            <strong style="font-size:13px; color:var(--riq-text);"><?php echo esc_html( $gt_label ); ?></strong>
                            <span style="margin-left:auto; font-size:11px; font-weight:700; color:#a855f7; background:#faf5ff; padding:2px 8px; border-radius:4px;"><?php echo esc_html( $gt_pts ); ?> pts</span>
                        </div>
                        <p style="font-size:12px; color:var(--riq-text-light); line-height:1.5; margin:0 0 10px;"><?php echo esc_html( $gt_desc ); ?></p>
                        <?php if ( $gt_url ) : ?>
                            <a href="<?php echo esc_url( $gt_url ); ?>" style="font-size:11px; color:var(--riq-primary); font-weight:700; background:#f8fafc; padding:4px 10px; border-radius:6px; display:inline-block; text-decoration:none;"><?php echo esc_html( $gt_fix ); ?> →</a>
                        <?php else : ?>
                            <span style="font-size:11px; color:#64748b; background:#f8fafc; padding:4px 10px; border-radius:6px; display:inline-block;"><?php echo esc_html( $gt_fix ); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ── Per-post AEO + GEO table ── -->
    <div class="gatetouch-card">
        <div class="gatetouch-card__header" style="justify-content:space-between;">
            <h4 style="margin:0; font-size:15px; font-weight:700;">Content Performance — AEO &amp; GEO</h4>
            <button id="gatetouch-reload-health" class="gatetouch-btn gatetouch-btn--ghost gatetouch-btn--sm">↻ Refresh</button>
        </div>
        <div class="gatetouch-card__body" style="padding:0;">
            <div class="gatetouch-bulk-table-container" style="overflow:hidden;">
                <table class="gatetouch-premium-table">
                    <thead>
                        <tr style="background:#f8fafc;">
                            <th><?php esc_html_e( 'Page / Post', 'gatetouch-ai-seo' ); ?></th>
                            <th style="width:160px; text-align:center;"><?php esc_html_e( 'AEO Score', 'gatetouch-ai-seo' ); ?></th>
                            <th style="width:160px; text-align:center;"><?php esc_html_e( 'GEO Score', 'gatetouch-ai-seo' ); ?></th>
                            <th style="text-align:left;"><?php esc_html_e( 'Top GEO Fix', 'gatetouch-ai-seo' ); ?></th>
                            <th style="width:100px; text-align:right;"><?php esc_html_e( 'Actions', 'gatetouch-ai-seo' ); ?></th>
                        </tr>
                    </thead>
                    <tbody id="gatetouch-aeo-results">
                        <tr>
                            <td colspan="5" style="padding:60px; text-align:center; color:var(--riq-text-light);">
                                <div class="riq-spinner" style="margin:0 auto 15px;"></div>
                                Analyzing AEO &amp; GEO signals...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php ob_start(); ?>
jQuery(document).ready(function($) {

    function scoreBar(score, color) {
        return '<div style="display:flex;align-items:center;gap:8px;justify-content:center;">' +
            '<div style="width:80px;height:6px;background:#f1f5f9;border-radius:3px;overflow:hidden;">' +
            '<div style="height:100%;width:' + score + '%;background:' + color + ';"></div></div>' +
            '<span style="font-weight:700;color:' + color + ';font-size:13px;">' + score + '%</span>' +
            '</div>';
    }

    function loadHealth() {
        $('#gatetouch-aeo-results').html(
            '<tr><td colspan="5" style="padding:40px;text-align:center;color:var(--riq-text-light);">' +
            '<div class="riq-spinner" style="margin:0 auto 12px;"></div>Analyzing AEO &amp; GEO signals...</td></tr>'
        );

        $.post(gatetouchAdmin.ajax_url, {
            action: 'gatetouch_fetch_aeo_health',
            nonce:  gatetouchAdmin.nonce
        }, function(res) {
            if (!res.success) return;

            var d = res.data;

            // Update average cards
            $('#gatetouch-avg-aeo').text(d.avg_aeo + '%');
            $('#gatetouch-avg-geo').text(d.avg_geo + '%');

            // Render rows
            var html = '';
            if (!d.rows || !d.rows.length) {
                html = '<tr><td colspan="5" style="padding:40px;text-align:center;color:var(--riq-text-light);"><?php esc_html_e( 'No published posts found.', 'gatetouch-ai-seo' ); ?></td></tr>';
            } else {
                d.rows.forEach(function(r) {
                    var geoTip = r.geo_tip
                        ? '<span style="font-size:12px;color:#64748b;">' + r.geo_tip + '</span>'
                        : '<span style="font-size:12px;color:#10b981;">✓ No issues found</span>';

                    html += '<tr>' +
                        '<td><strong>' + r.title + '</strong>' +
                            '<div style="font-size:11px;color:var(--riq-text-light);">' + r.url + '</div></td>' +
                        '<td style="text-align:center;">' + scoreBar(r.aeo_score, r.aeo_color) + '</td>' +
                        '<td style="text-align:center;">' + scoreBar(r.geo_score, r.geo_color) + '</td>' +
                        '<td>' + geoTip + '</td>' +
                        '<td style="text-align:right;">' +
                            '<a href="' + r.edit_url + '" class="gatetouch-btn gatetouch-btn--ghost gatetouch-btn--sm">Edit</a>' +
                        '</td>' +
                    '</tr>';
                });
            }
            $('#gatetouch-aeo-results').html(html);
        });
    }

    loadHealth();
    $('#gatetouch-reload-health').on('click', loadHealth);

    // Enable llms.txt quick-fix
    $(document).on('click', '#gatetouch-enable-llms', function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<span class="riq-spinner"></span>');
        $.post(gatetouchAdmin.ajax_url, {
            action: 'gatetouch_fix_geo_signal',
            signal: 'llms_txt',
            nonce:  gatetouchAdmin.nonce
        }, function(res) {
            if (res.success) {
                if (typeof gatetouchFlash === 'function') gatetouchFlash('llms.txt enabled — GEO scores updated', 'success');
                // Reload page to reflect new state in signal cards
                location.reload();
            } else {
                btn.prop('disabled', false).text('Enable llms.txt');
                if (typeof gatetouchFlash === 'function') gatetouchFlash(res.data || 'Error enabling llms.txt', 'error');
            }
        });
    });

    // llms.txt sync button (when already active)
    $(document).on('click', '#gatetouch-fix-llms', function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<span class="riq-spinner"></span>');
        $.post(gatetouchAdmin.ajax_url, {
            action: 'gatetouch_flush_rewrites',
            nonce:  gatetouchAdmin.nonce
        }, function(res) {
            btn.prop('disabled', false).text('Sync Rules');
            if (res.success) {
                if (typeof gatetouchFlash === 'function') gatetouchFlash('llms.txt synchronised', 'success');
                window.open('<?php echo esc_js( home_url( '/llms.txt' ) ); ?>', '_blank');
            }
        });
    });

});
<?php wp_add_inline_script( 'gatetouch-admin', ob_get_clean() ); ?>
