/**
 * GateTouch AI SEO — Elementor Integration
 * Native sidebar integration for Elementor editor.
 */
jQuery(window).on('elementor/frontend/init', function() {
    'use strict';

    if (!window.elementor) return;

    elementor.hooks.addFilter('panel/elements/region/views', function(views) {
        // Elementor uses a complex view system, we target the document settings
        return views;
    });

    // Enterprise strategy for Elementor: Add a dedicated tab or section in Document Settings
    elementor.on('panel:init', function() {
        const panel = elementor.getPanelView();
        
        // Listen for when the "Settings" (Gear icon) is opened
        elementor.channels.editor.on('section:activated', function(sectionName, editor) {
            // We can inject our UI when specific settings sections are opened
        });

        // Better approach: Use Elementor's native Control system if possible, 
        // or inject a custom panel if we want total control.
    });

    // Simple robust injection for now to ensure visibility without "Meta Boxes" button
    const injectElementorSEO = () => {
        const $panel = jQuery('#elementor-panel-elements-wrapper');
        if (!$panel.length || jQuery('#gatetouch-elementor-panel').length) return;

        $panel.prepend(`
            <div id="gatetouch-elementor-panel" class="gatetouch-card" style="margin: 10px; border: 1px solid #6366f1; border-radius: 8px; overflow: hidden; background: #fff;">
                <div style="background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); padding: 12px 15px; color: #fff; display: flex; justify-content: space-between; align-items: center;">
                    <strong style="font-size: 13px;">GateTouch AI SEO</strong>
                    <span id="gatetouch-el-score" style="background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 800;">--</span>
                </div>
                <div style="padding: 15px;">
                    <div style="margin-bottom: 15px;">
                        <label style="display:block; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; margin-bottom:5px;">Focus Keyword</label>
                        <input type="text" id="gatetouch-el-keyword" class="gatetouch-input" style="width:100%; height:32px; font-size:13px;" placeholder="Enter keyword..." />
                    </div>
                    <button id="gatetouch-el-analyze" class="gatetouch-btn gatetouch-btn--primary" style="width:100%; justify-content:center; font-size:12px; height:36px;">
                        Analyze Content
                    </button>
                    <div id="gatetouch-el-results" style="margin-top:15px; font-size:12px; color:#475569;">
                        Connect to audit engine...
                    </div>
                </div>
            </div>
        `);

        // Hook up events
        jQuery('#gatetouch-el-analyze').on('click', function() {
            const btn = jQuery(this);
            const kw = jQuery('#gatetouch-el-keyword').val();
            btn.prop('disabled', true).text('Analyzing...');

            jQuery.post(gatetouchData.ajax_url, {
                action: 'gatetouch_analyze_seo',
                nonce: gatetouchData.nonce,
                post_id: gatetouchData.post_id,
                keyword: kw,
                rendered_html: elementor.getPreviewContainer().view.$el.html() // CRITICAL: Get real Elementor content
            }, function(res) {
                btn.prop('disabled', false).text('Analyze Content');
                if (res.success) {
                    jQuery('#gatetouch-el-score').text(res.data.score);
                    let html = '<ul style="margin:0; padding:0; list-style:none;">';
                    res.data.checks.slice(0, 5).forEach(c => {
                        const color = c.status === 'pass' ? '#10b981' : (c.status === 'warn' ? '#f59e0b' : '#ef4444');
                        html += `<li style="margin-bottom:5px; display:flex; gap:5px;"><span style="color:${color}">●</span> ${c.message}</li>`;
                    });
                    html += '</ul>';
                    jQuery('#gatetouch-el-results').html(html);
                }
            });
        });
    };

    // Re-inject when panel changes
    setInterval(injectElementorSEO, 1000);
});
