<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files are included inside class methods, so render variables are local.
?>

<!-- ── Input Panel ── -->
<div class="gatetouch-card" style="margin-bottom:24px;">
    <div class="gatetouch-card__header">
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="background:var(--riq-ai-gradient); width:40px; height:40px; border-radius:12px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:20px;">🧭</div>
            <div>
                <h3 style="margin:0; font-size:17px; font-weight:800; color:var(--riq-text);"><?php esc_html_e( 'Content Brief Generator', 'gatetouch-ai-seo' ); ?></h3>
                <p style="margin:2px 0 0; font-size:13px; color:var(--riq-text-light);"><?php esc_html_e( 'Enter a keyword or topic. AI generates a complete SEO + AEO + GEO brief aligned with Google 2024/25 ranking signals.', 'gatetouch-ai-seo' ); ?></p>
            </div>
        </div>
    </div>
    <div class="gatetouch-card__body" style="padding:24px;">
        <div style="display:flex; gap:12px; align-items:flex-end;">
            <div style="flex:1;">
                <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:8px; text-transform:uppercase; letter-spacing:0.05em;"><?php esc_html_e( 'Keyword or Topic', 'gatetouch-ai-seo' ); ?></label>
                <input type="text" id="gatetouch-brief-keyword"
                    placeholder="e.g. best project management software for remote teams"
                    style="width:100%; height:46px; padding:0 16px; border:1.5px solid #cbd5e1; border-radius:10px; font-size:14px; color:#334155; box-sizing:border-box;" />
            </div>
            <button id="gatetouch-brief-generate" class="gatetouch-btn gatetouch-btn--primary" style="height:46px; padding:0 28px; font-size:14px; font-weight:700; white-space:nowrap;">
                Generate Brief
            </button>
        </div>
        <p style="font-size:12px; color:var(--riq-text-light); margin:10px 0 0;">Covers: Google E-E-A-T &bull; Helpful Content signals &bull; AI Overviews targeting &bull; GEO citation structure &bull; FAQPage schema</p>
    </div>
</div>

<!-- ── Saved briefs ── -->
<?php
require_once GATETOUCH_PATH . 'includes/class-content-brief.php';
$gt_brief_library = GateTouch_Content_Brief::library_index();
?>
<div class="gatetouch-card" id="gatetouch-brief-library-card" style="margin-bottom:24px; <?php echo empty( $gt_brief_library ) ? 'display:none;' : ''; ?>">
    <div class="gatetouch-card__header" style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
        <span><?php esc_html_e( 'Saved briefs', 'gatetouch-ai-seo' ); ?></span>
        <span style="font-size:11.5px; font-weight:600; color:var(--riq-text-light);">
            <?php
            printf(
                /* translators: %d: maximum number of stored briefs */
                esc_html__( 'Kept so you never pay to regenerate the same topic. Last %d.', 'gatetouch-ai-seo' ),
                (int) GateTouch_Content_Brief::LIBRARY_MAX
            );
            ?>
        </span>
    </div>
    <div class="gatetouch-card__body" style="padding:0;">
        <table class="gatetouch-table" style="border:none; border-radius:0;">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Keyword', 'gatetouch-ai-seo' ); ?></th>
                    <th style="width:130px;"><?php esc_html_e( 'Intent', 'gatetouch-ai-seo' ); ?></th>
                    <th style="width:130px;"><?php esc_html_e( 'Type', 'gatetouch-ai-seo' ); ?></th>
                    <th style="width:110px;"><?php esc_html_e( 'Created', 'gatetouch-ai-seo' ); ?></th>
                    <th style="width:230px;"></th>
                </tr>
            </thead>
            <tbody id="gatetouch-brief-library-body">
                <?php foreach ( $gt_brief_library as $gt_entry ) : ?>
                    <tr data-id="<?php echo esc_attr( $gt_entry['id'] ); ?>">
                        <td><strong><?php echo esc_html( $gt_entry['keyword'] ); ?></strong></td>
                        <td><?php echo esc_html( $gt_entry['intent'] ?: '—' ); ?></td>
                        <td><?php echo esc_html( $gt_entry['type'] ?: '—' ); ?></td>
                        <td style="color:var(--riq-text-light);"><?php echo esc_html( $gt_entry['ago'] ); ?></td>
                        <td style="text-align:right; white-space:nowrap;">
                            <button type="button" class="gatetouch-btn gatetouch-btn--ghost gatetouch-btn--sm gt-brief-open" data-id="<?php echo esc_attr( $gt_entry['id'] ); ?>"><?php esc_html_e( 'Open', 'gatetouch-ai-seo' ); ?></button>
                            <button type="button" class="gatetouch-btn gatetouch-btn--ghost gatetouch-btn--sm gt-brief-draft" data-id="<?php echo esc_attr( $gt_entry['id'] ); ?>"><?php esc_html_e( 'Create draft', 'gatetouch-ai-seo' ); ?></button>
                            <button type="button" class="gatetouch-btn gatetouch-btn--ghost gatetouch-btn--sm gt-brief-delete" data-id="<?php echo esc_attr( $gt_entry['id'] ); ?>" style="color:#ef4444;"><?php esc_html_e( 'Delete', 'gatetouch-ai-seo' ); ?></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── Results ── -->
<div id="gatetouch-brief-results" style="display:none;">

    <!-- Actions -->
    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:16px;">
        <button type="button" id="gatetouch-brief-create-post" class="gatetouch-btn gatetouch-btn--primary">
            <?php esc_html_e( 'Create draft post from this brief', 'gatetouch-ai-seo' ); ?>
        </button>
        <button type="button" id="gatetouch-brief-copy-md" class="gatetouch-btn gatetouch-btn--secondary">
            <?php esc_html_e( 'Copy as Markdown', 'gatetouch-ai-seo' ); ?>
        </button>
        <button type="button" id="gatetouch-brief-regenerate" class="gatetouch-btn gatetouch-btn--ghost">
            <?php esc_html_e( 'Regenerate', 'gatetouch-ai-seo' ); ?>
        </button>
        <span id="gatetouch-brief-cached-note" style="display:none; font-size:12px; color:var(--riq-text-light);">
            <?php esc_html_e( 'Loaded from your saved briefs — no API call was made.', 'gatetouch-ai-seo' ); ?>
        </span>
    </div>

    <!-- Signal badges -->
    <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:20px;" id="gatetouch-brief-badges"></div>

    <!-- Output tabs -->
    <div style="display:flex; gap:0; border-bottom:2px solid #e2e8f0; margin-bottom:20px;" id="gatetouch-brief-tabs">
        <button class="gatetouch-brief-tab active" data-tab="seo"     style="padding:10px 20px; border:none; background:none; font-size:13px; font-weight:700; color:#6366f1; border-bottom:2px solid #6366f1; margin-bottom:-2px; cursor:pointer;">SEO</button>
        <button class="gatetouch-brief-tab"        data-tab="aeo"     style="padding:10px 20px; border:none; background:none; font-size:13px; font-weight:700; color:#64748b; border-bottom:2px solid transparent; margin-bottom:-2px; cursor:pointer;">AEO</button>
        <button class="gatetouch-brief-tab"        data-tab="geo"     style="padding:10px 20px; border:none; background:none; font-size:13px; font-weight:700; color:#64748b; border-bottom:2px solid transparent; margin-bottom:-2px; cursor:pointer;">GEO</button>
        <button class="gatetouch-brief-tab"        data-tab="outline" style="padding:10px 20px; border:none; background:none; font-size:13px; font-weight:700; color:#64748b; border-bottom:2px solid transparent; margin-bottom:-2px; cursor:pointer;"><?php esc_html_e( 'Outline', 'gatetouch-ai-seo' ); ?></button>
        <button class="gatetouch-brief-tab"        data-tab="faqs"    style="padding:10px 20px; border:none; background:none; font-size:13px; font-weight:700; color:#64748b; border-bottom:2px solid transparent; margin-bottom:-2px; cursor:pointer;">FAQs</button>
    </div>

    <!-- SEO Tab -->
    <div class="gatetouch-brief-panel" id="gatetouch-brief-panel-seo">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
            <div class="gatetouch-card" style="margin:0;">
                <div class="gatetouch-card__body" style="padding:16px;">
                    <div style="font-size:11px; font-weight:700; color:#6366f1; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:8px;"><?php esc_html_e( 'Title Tag', 'gatetouch-ai-seo' ); ?></div>
                    <div id="rb-seo-title" style="font-size:14px; color:#1e293b; font-weight:600; line-height:1.4;"></div>
                    <div id="rb-seo-title-len" style="font-size:11px; color:#94a3b8; margin-top:4px;"></div>
                </div>
            </div>
            <div class="gatetouch-card" style="margin:0;">
                <div class="gatetouch-card__body" style="padding:16px;">
                    <div style="font-size:11px; font-weight:700; color:#6366f1; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:8px;"><?php esc_html_e( 'Meta Description', 'gatetouch-ai-seo' ); ?></div>
                    <div id="rb-seo-meta" style="font-size:13px; color:#1e293b; line-height:1.5;"></div>
                    <div id="rb-seo-meta-len" style="font-size:11px; color:#94a3b8; margin-top:4px;"></div>
                </div>
            </div>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-bottom:16px;">
            <div class="gatetouch-card" style="margin:0;">
                <div class="gatetouch-card__body" style="padding:16px;">
                    <div style="font-size:11px; font-weight:700; color:#6366f1; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:8px;"><?php esc_html_e( 'Focus Keyword', 'gatetouch-ai-seo' ); ?></div>
                    <div id="rb-seo-kw" style="font-size:14px; color:#1e293b; font-weight:700;"></div>
                </div>
            </div>
            <div class="gatetouch-card" style="margin:0;">
                <div class="gatetouch-card__body" style="padding:16px;">
                    <div style="font-size:11px; font-weight:700; color:#6366f1; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:8px;"><?php esc_html_e( 'Word Count Target', 'gatetouch-ai-seo' ); ?></div>
                    <div id="rb-seo-wc" style="font-size:14px; color:#1e293b; font-weight:700;"></div>
                </div>
            </div>
            <div class="gatetouch-card" style="margin:0;">
                <div class="gatetouch-card__body" style="padding:16px;">
                    <div style="font-size:11px; font-weight:700; color:#6366f1; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:8px;"><?php esc_html_e( 'Schema Type', 'gatetouch-ai-seo' ); ?></div>
                    <div id="rb-seo-schema" style="font-size:14px; color:#1e293b; font-weight:700;"></div>
                </div>
            </div>
        </div>
        <div class="gatetouch-card" style="margin:0 0 16px;">
            <div class="gatetouch-card__body" style="padding:16px;">
                <div style="font-size:11px; font-weight:700; color:#6366f1; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:10px;"><?php esc_html_e( 'Secondary Keywords (LSI)', 'gatetouch-ai-seo' ); ?></div>
                <div id="rb-seo-secondary" style="display:flex; gap:8px; flex-wrap:wrap;"></div>
            </div>
        </div>
        <div class="gatetouch-card" style="margin:0 0 16px;">
            <div class="gatetouch-card__body" style="padding:16px;">
                <div style="font-size:11px; font-weight:700; color:#10b981; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:10px;"><?php esc_html_e( 'E-E-A-T Action Items (Google 2024/25)', 'gatetouch-ai-seo' ); ?></div>
                <ul id="rb-seo-eeat" style="margin:0; padding-left:20px; font-size:13px; color:#334155; line-height:2;"></ul>
            </div>
        </div>
        <div class="gatetouch-card" style="margin:0;">
            <div class="gatetouch-card__body" style="padding:16px;">
                <div style="font-size:11px; font-weight:700; color:#6366f1; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:10px;"><?php esc_html_e( 'Internal Link Anchor Texts', 'gatetouch-ai-seo' ); ?></div>
                <div id="rb-seo-anchors" style="display:flex; gap:8px; flex-wrap:wrap;"></div>
            </div>
        </div>
    </div>

    <!-- AEO Tab -->
    <div class="gatetouch-brief-panel" id="gatetouch-brief-panel-aeo" style="display:none;">
        <div class="gatetouch-card" style="margin:0 0 16px; border-left:4px solid #3b82f6;">
            <div class="gatetouch-card__body" style="padding:16px;">
                <div style="font-size:11px; font-weight:700; color:#3b82f6; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:8px;"><?php esc_html_e( 'Featured Snippet Target Question', 'gatetouch-ai-seo' ); ?></div>
                <div id="rb-aeo-snippet-q" style="font-size:15px; color:#1e293b; font-weight:700;"></div>
            </div>
        </div>
        <div class="gatetouch-card" style="margin:0 0 16px; border-left:4px solid #10b981;">
            <div class="gatetouch-card__body" style="padding:16px;">
                <div style="font-size:11px; font-weight:700; color:#10b981; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:8px;"><?php esc_html_e( 'Answer Box Opening (put this at the top of your content)', 'gatetouch-ai-seo' ); ?></div>
                <div id="rb-aeo-answer" style="font-size:14px; color:#1e293b; line-height:1.6; font-style:italic;"></div>
            </div>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
            <div class="gatetouch-card" style="margin:0;">
                <div class="gatetouch-card__body" style="padding:16px;">
                    <div style="font-size:11px; font-weight:700; color:#3b82f6; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:10px;"><?php esc_html_e( 'Question Subheadings (H2/H3)', 'gatetouch-ai-seo' ); ?></div>
                    <ul id="rb-aeo-questions" style="margin:0; padding-left:18px; font-size:13px; color:#334155; line-height:2;"></ul>
                </div>
            </div>
            <div class="gatetouch-card" style="margin:0;">
                <div class="gatetouch-card__body" style="padding:16px;">
                    <div style="font-size:11px; font-weight:700; color:#3b82f6; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:10px;"><?php esc_html_e( 'Conversational Phrases to Include', 'gatetouch-ai-seo' ); ?></div>
                    <div id="rb-aeo-phrases" style="display:flex; gap:8px; flex-wrap:wrap;"></div>
                </div>
            </div>
        </div>
        <div class="gatetouch-card" style="margin:0;">
            <div class="gatetouch-card__body" style="padding:16px;">
                <div style="font-size:11px; font-weight:700; color:#3b82f6; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;"><?php esc_html_e( 'Readability Target', 'gatetouch-ai-seo' ); ?></div>
                <div id="rb-aeo-readability" style="font-size:14px; color:#1e293b; font-weight:600;"></div>
            </div>
        </div>
    </div>

    <!-- GEO Tab -->
    <div class="gatetouch-brief-panel" id="gatetouch-brief-panel-geo" style="display:none;">
        <div class="gatetouch-card" style="margin:0 0 16px; border-left:4px solid #a855f7;">
            <div class="gatetouch-card__body" style="padding:16px;">
                <div style="font-size:11px; font-weight:700; color:#a855f7; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:8px;">AI Citation Hook — Most Quotable Sentence</div>
                <div id="rb-geo-hook" style="font-size:15px; color:#1e293b; font-weight:600; line-height:1.5; font-style:italic;"></div>
                <p style="font-size:12px; color:#94a3b8; margin:8px 0 0;"><?php esc_html_e( 'Include this sentence verbatim in your content. AI systems are more likely to quote concise, authoritative statements.', 'gatetouch-ai-seo' ); ?></p>
            </div>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
            <div class="gatetouch-card" style="margin:0;">
                <div class="gatetouch-card__body" style="padding:16px;">
                    <div style="font-size:11px; font-weight:700; color:#a855f7; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:10px;"><?php esc_html_e( 'Key Entities to Define', 'gatetouch-ai-seo' ); ?></div>
                    <div id="rb-geo-entities" style="display:flex; gap:8px; flex-wrap:wrap;"></div>
                </div>
            </div>
            <div class="gatetouch-card" style="margin:0;">
                <div class="gatetouch-card__body" style="padding:16px;">
                    <div style="font-size:11px; font-weight:700; color:#a855f7; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:10px;"><?php esc_html_e( 'Topical Cluster Articles to Build', 'gatetouch-ai-seo' ); ?></div>
                    <ul id="rb-geo-cluster" style="margin:0; padding-left:18px; font-size:13px; color:#334155; line-height:2;"></ul>
                </div>
            </div>
        </div>
        <div class="gatetouch-card" style="margin:0 0 16px;">
            <div class="gatetouch-card__body" style="padding:16px;">
                <div style="font-size:11px; font-weight:700; color:#a855f7; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:10px;"><?php esc_html_e( 'Entity Definitions (include these in-content)', 'gatetouch-ai-seo' ); ?></div>
                <div id="rb-geo-definitions" style="font-size:13px; color:#334155; line-height:1.8;"></div>
            </div>
        </div>
        <div class="gatetouch-card" style="margin:0;">
            <div class="gatetouch-card__body" style="padding:16px;">
                <div style="font-size:11px; font-weight:700; color:#a855f7; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:10px;"><?php esc_html_e( 'GEO Action Checklist', 'gatetouch-ai-seo' ); ?></div>
                <ul id="rb-geo-tips" style="margin:0; padding-left:20px; font-size:13px; color:#334155; line-height:2;"></ul>
            </div>
        </div>
    </div>

    <!-- Outline Tab -->
    <div class="gatetouch-brief-panel" id="gatetouch-brief-panel-outline" style="display:none;">
        <div id="rb-outline-content"></div>
    </div>

    <!-- FAQs Tab -->
    <div class="gatetouch-brief-panel" id="gatetouch-brief-panel-faqs" style="display:none;">
        <div id="rb-faqs-content"></div>
    </div>

    <!-- Competitive Edge banner -->
    <div class="gatetouch-card" style="margin-top:20px; border-left:4px solid #f59e0b;">
        <div class="gatetouch-card__body" style="padding:16px; display:flex; align-items:center; gap:12px;">
            <span class="rk-icon-box rk-icon-box--amber"><?php echo wp_kses( GateTouch_Helpers::icon( 'trophy', 20 ), GateTouch_Helpers::svg_kses_allowed() ); ?></span>
            <div>
                <div style="font-size:11px; font-weight:700; color:#b45309; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;"><?php esc_html_e( 'Competitive Edge', 'gatetouch-ai-seo' ); ?></div>
                <div id="rb-competitive" style="font-size:13px; color:#334155; line-height:1.5;"></div>
            </div>
        </div>
    </div>

</div>

<!-- Loading state -->
<div id="gatetouch-brief-loading" style="display:none; text-align:center; padding:60px 20px;">
    <div class="riq-spinner" style="margin:0 auto 16px; width:32px; height:32px;"></div>
    <div style="font-size:15px; font-weight:600; color:var(--riq-text);"><?php esc_html_e( 'Generating your SEO + AEO + GEO content brief...', 'gatetouch-ai-seo' ); ?></div>
    <div style="font-size:13px; color:var(--riq-text-light); margin-top:6px;"><?php esc_html_e( 'Analysing keyword intent, entity requirements, and AI citation patterns', 'gatetouch-ai-seo' ); ?></div>
</div>

<?php ob_start(); ?>
jQuery(document).ready(function($) {

    function tag(text, color) {
        return '<span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;background:' + color + '20;color:' + color + ';border:1px solid ' + color + '40;">' + text + '</span>';
    }

    function listItems(arr) {
        if (!arr || !arr.length) return '<li style="color:#94a3b8"><?php esc_html_e( 'None specified', 'gatetouch-ai-seo' ); ?></li>';
        return arr.map(function(i) { return '<li>' + $('<div>').text(i).html() + '</li>'; }).join('');
    }

    function safeText(val) {
        return $('<div>').text(val || '—').html();
    }

    // Tab switching
    $(document).on('click', '.gatetouch-brief-tab', function() {
        var tab = $(this).data('tab');
        $('.gatetouch-brief-tab').css({ color: '#64748b', 'border-bottom-color': 'transparent' });
        $(this).css({ color: '#6366f1', 'border-bottom-color': '#6366f1' });
        $('.gatetouch-brief-panel').hide();
        $('#gatetouch-brief-panel-' + tab).show();
    });

    // Generate
    // Currently displayed brief — its id and its Markdown rendering.
    var gtCurrentBriefId = '';
    var gtCurrentMarkdown = '';

    function flash(msg, type) {
        if (typeof gatetouchFlash === 'function') { gatetouchFlash(msg, type || 'success'); }
        else if (window.gatetouchFlash) { window.gatetouchFlash(msg, type || 'success'); }
    }

    // Accepts the full payload: { brief, markdown, id, cached, library }
    function applyPayload(data) {
        gtCurrentBriefId  = data.id || '';
        gtCurrentMarkdown = data.markdown || '';
        $('#gatetouch-brief-cached-note').toggle(!!data.cached);
        if (data.library) { renderLibrary(data.library); }
        renderBrief(data.brief || {});
    }

    function renderLibrary(rows) {
        var $body = $('#gatetouch-brief-library-body');
        if (!$body.length) { return; }
        if (!rows || !rows.length) {
            $('#gatetouch-brief-library-card').hide();
            $body.empty();
            return;
        }
        var html = '';
        rows.forEach(function(r) {
            var id = $('<div>').text(r.id).html();
            html += '<tr data-id="' + id + '">' +
                '<td><strong>' + safeText(r.keyword) + '</strong></td>' +
                '<td>' + safeText(r.intent || '—') + '</td>' +
                '<td>' + safeText(r.type || '—') + '</td>' +
                '<td style="color:var(--riq-text-light);">' + safeText(r.ago) + '</td>' +
                '<td style="text-align:right; white-space:nowrap;">' +
                    '<button type="button" class="gatetouch-btn gatetouch-btn--ghost gatetouch-btn--sm gt-brief-open" data-id="' + id + '">Open</button> ' +
                    '<button type="button" class="gatetouch-btn gatetouch-btn--ghost gatetouch-btn--sm gt-brief-draft" data-id="' + id + '">Create draft</button> ' +
                    '<button type="button" class="gatetouch-btn gatetouch-btn--ghost gatetouch-btn--sm gt-brief-delete" data-id="' + id + '" style="color:#ef4444;">Delete</button>' +
                '</td></tr>';
        });
        $body.html(html);
        $('#gatetouch-brief-library-card').show();
    }

    function runGenerate(force) {
        var keyword = $('#gatetouch-brief-keyword').val().trim();
        if (keyword.length < 2) {
            $('#gatetouch-brief-keyword').css('border-color', '#ef4444').focus();
            return;
        }
        $('#gatetouch-brief-keyword').css('border-color', '#cbd5e1');

        $('#gatetouch-brief-results').hide();
        $('#gatetouch-brief-loading').show();
        $('#gatetouch-brief-generate').prop('disabled', true).text('Generating...');

        $.post(gatetouchAdmin.ajax_url, {
            action:  'gatetouch_generate_brief',
            nonce:   gatetouchAdmin.nonce,
            keyword: keyword,
            force:   force ? 1 : 0
        })
        .done(function(res) {
            if (!res.success) { flash(res.data || 'Generation failed.', 'error'); return; }
            applyPayload(res.data);
        })
        .fail(function() { flash('Server error. Please try again.', 'error'); })
        .always(function() {
            $('#gatetouch-brief-loading').hide();
            $('#gatetouch-brief-generate').prop('disabled', false).text('Generate Brief');
        });
    }

    $('#gatetouch-brief-generate').on('click', function() { runGenerate(false); });

    $('#gatetouch-brief-regenerate').on('click', function() {
        if (!window.confirm('Generate a fresh brief for this keyword? This makes a new API call and replaces the saved one.')) { return; }
        runGenerate(true);
    });

    // ── Copy as Markdown ──────────────────────────────────────────────────
    $('#gatetouch-brief-copy-md').on('click', function() {
        var btn = $(this);
        if (!gtCurrentMarkdown) { flash('Nothing to copy yet.', 'error'); return; }

        var done = function() {
            var original = btn.text();
            btn.text('Copied ✓');
            setTimeout(function() { btn.text(original); }, 1800);
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(gtCurrentMarkdown).then(done).catch(function() { fallbackCopy(gtCurrentMarkdown, done); });
        } else {
            fallbackCopy(gtCurrentMarkdown, done);
        }
    });

    // execCommand path for plain-HTTP admins, where the async clipboard API is blocked.
    function fallbackCopy(text, done) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.setAttribute('readonly', '');
        ta.style.position = 'fixed';
        ta.style.top = '-1000px';
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); done(); }
        catch (e) { flash('Could not copy automatically — select the text manually.', 'error'); }
        document.body.removeChild(ta);
    }

    // ── Create draft post ─────────────────────────────────────────────────
    function createDraft(id, btn) {
        if (!id) { flash('Generate or open a brief first.', 'error'); return; }
        var original = btn.text();
        btn.prop('disabled', true).text('Creating...');

        $.post(gatetouchAdmin.ajax_url, {
            action: 'gatetouch_brief_create_post',
            nonce:  gatetouchAdmin.nonce,
            id:     id
        })
        .done(function(res) {
            if (!res.success) { flash(res.data || 'Could not create the draft.', 'error'); return; }
            flash('Draft created — opening the editor.', 'success');
            if (res.data && res.data.edit_url) { window.location.href = res.data.edit_url; }
        })
        .fail(function() { flash('Server error. Please try again.', 'error'); })
        .always(function() { btn.prop('disabled', false).text(original); });
    }

    $('#gatetouch-brief-create-post').on('click', function() { createDraft(gtCurrentBriefId, $(this)); });
    $(document).on('click', '.gt-brief-draft', function() { createDraft($(this).data('id'), $(this)); });

    // ── Library actions ───────────────────────────────────────────────────
    $(document).on('click', '.gt-brief-open', function() {
        var id = $(this).data('id');
        $('#gatetouch-brief-results').hide();
        $('#gatetouch-brief-loading').show();

        $.post(gatetouchAdmin.ajax_url, { action: 'gatetouch_brief_library', nonce: gatetouchAdmin.nonce, id: id })
        .done(function(res) {
            if (!res.success) { flash(res.data || 'Could not load that brief.', 'error'); return; }
            $('#gatetouch-brief-keyword').val((res.data.brief && res.data.brief.keyword) || '');
            applyPayload(res.data);
        })
        .fail(function() { flash('Server error. Please try again.', 'error'); })
        .always(function() { $('#gatetouch-brief-loading').hide(); });
    });

    $(document).on('click', '.gt-brief-delete', function() {
        if (!window.confirm('Delete this saved brief?')) { return; }
        var id = $(this).data('id');
        $.post(gatetouchAdmin.ajax_url, { action: 'gatetouch_brief_delete', nonce: gatetouchAdmin.nonce, id: id })
        .done(function(res) {
            if (res.success) {
                renderLibrary(res.data.library);
                if (id === gtCurrentBriefId) { $('#gatetouch-brief-results').hide(); gtCurrentBriefId = ''; }
            }
        });
    });

    // Enter key in keyword field
    $('#gatetouch-brief-keyword').on('keydown', function(e) {
        if (e.key === 'Enter') $('#gatetouch-brief-generate').trigger('click');
    });

    function renderBrief(d) {
        var seo = d.seo || {};
        var aeo = d.aeo || {};
        var geo = d.geo || {};

        // Badges
        var badges = '';
        if (d.search_intent) badges += tag(d.search_intent + ' Intent', '#6366f1');
        if (d.content_type)  badges += ' ' + tag(d.content_type, '#10b981');
        if (seo.word_count_target) badges += ' ' + tag(seo.word_count_target + ' words', '#f59e0b');
        if (seo.schema_type) badges += ' ' + tag(seo.schema_type + ' Schema', '#a855f7');
        $('#gatetouch-brief-badges').html(badges);

        // SEO tab
        var title = seo.title_tag || '';
        $('#rb-seo-title').text(title);
        $('#rb-seo-title-len').text(title.length + ' chars' + (title.length >= 50 && title.length <= 60 ? ' ✓' : ' (aim for 50-60)'));

        var meta = seo.meta_description || '';
        $('#rb-seo-meta').text(meta);
        $('#rb-seo-meta-len').text(meta.length + ' chars' + (meta.length >= 145 && meta.length <= 160 ? ' ✓' : ' (aim for 145-160)'));

        $('#rb-seo-kw').text(seo.focus_keyword || '—');
        $('#rb-seo-wc').text(seo.word_count_target || '—');
        $('#rb-seo-schema').text(seo.schema_type || '—');

        var secKw = (seo.secondary_keywords || []).map(function(k) { return tag(k, '#6366f1'); }).join(' ');
        $('#rb-seo-secondary').html(secKw || '<span style="color:#94a3b8">None</span>');

        $('#rb-seo-eeat').html(listItems(seo.eeat_tips));

        var anchors = (seo.internal_link_anchors || []).map(function(a) { return tag(a, '#0891b2'); }).join(' ');
        $('#rb-seo-anchors').html(anchors || '<span style="color:#94a3b8">None</span>');

        // AEO tab
        $('#rb-aeo-snippet-q').text(aeo.featured_snippet_target || '—');
        $('#rb-aeo-answer').text(aeo.answer_box_opening || '—');
        $('#rb-aeo-questions').html(listItems(aeo.question_subheadings));
        $('#rb-aeo-readability').text(aeo.readability_target || '—');
        var phrases = (aeo.conversational_phrases || []).map(function(p) { return tag(p, '#3b82f6'); }).join(' ');
        $('#rb-aeo-phrases').html(phrases || '<span style="color:#94a3b8">None</span>');

        // GEO tab
        $('#rb-geo-hook').text(geo.citation_hook || '—');
        var entities = (geo.key_entities || []).map(function(e) { return tag(e, '#a855f7'); }).join(' ');
        $('#rb-geo-entities').html(entities || '<span style="color:#94a3b8">None</span>');
        $('#rb-geo-cluster').html(listItems(geo.topical_cluster_ideas));
        $('#rb-geo-tips').html(listItems(geo.geo_tips));

        var defs = '';
        (geo.entity_definitions || []).forEach(function(item) {
            defs += '<div style="padding:8px 0; border-bottom:1px solid #f1f5f9;">' +
                '<strong>' + safeText(item.entity) + ':</strong> ' + safeText(item.definition) + '</div>';
        });
        $('#rb-geo-definitions').html(defs || '<span style="color:#94a3b8">None</span>');

        // Outline tab
        var outlineHtml = '';
        (d.content_outline || []).forEach(function(sec, i) {
            outlineHtml += '<div class="gatetouch-card" style="margin:0 0 12px;">' +
                '<div class="gatetouch-card__body" style="padding:14px 18px; display:flex; gap:14px; align-items:flex-start;">' +
                '<div style="width:28px; height:28px; background:linear-gradient(135deg,#6366f1,#a855f7); color:#fff; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; flex-shrink:0;">' + (i + 1) + '</div>' +
                '<div><div style="font-weight:700; color:#1e293b; font-size:14px;">' + safeText(sec.section) + '</div>' +
                '<div style="font-size:13px; color:#64748b; margin-top:4px;">' + safeText(sec.notes) + '</div></div>' +
                '</div></div>';
        });
        $('#rb-outline-content').html(outlineHtml || '<p style="color:#94a3b8"><?php esc_html_e( 'No outline generated.', 'gatetouch-ai-seo' ); ?></p>');

        // FAQs tab
        var faqHtml = '';
        (aeo.faqs || []).forEach(function(faq, i) {
            faqHtml += '<div class="gatetouch-card" style="margin:0 0 12px;">' +
                '<div class="gatetouch-card__body" style="padding:16px;">' +
                '<div style="font-weight:700; color:#1e293b; font-size:14px; margin-bottom:8px;">Q' + (i+1) + ': ' + safeText(faq.question) + '</div>' +
                '<div style="font-size:13px; color:#334155; line-height:1.6;">' + safeText(faq.answer) + '</div>' +
                '</div></div>';
        });
        $('#rb-faqs-content').html(faqHtml || '<p style="color:#94a3b8"><?php esc_html_e( 'No FAQs generated.', 'gatetouch-ai-seo' ); ?></p>');

        // Competitive edge
        $('#rb-competitive').text(d.competitive_edge || '—');

        // Show results, reset to SEO tab
        $('.gatetouch-brief-tab[data-tab="seo"]').trigger('click');
        $('#gatetouch-brief-results').show();

        // Scroll to results
        $('html, body').animate({ scrollTop: $('#gatetouch-brief-results').offset().top - 80 }, 300);
    }
});
<?php wp_add_inline_script( 'gatetouch-admin', ob_get_clean() ); ?>
