/* assets/js/sidebar.js */
(function(wp, $) {
    'use strict';

    if (!wp || !wp.plugins || !wp.editPost) return;

    const { registerPlugin } = wp.plugins;
    const { PluginSidebar, PluginSidebarMoreMenuItem } = wp.editPost || {};
    const el = wp.element.createElement;
    const { Fragment } = wp.element;

    const GateTouchSidebar = () => {
        try {
            const children = [];

            // Add MenuItem to the 'More' menu if supported
            if (PluginSidebarMoreMenuItem) {
                children.push(el(
                    PluginSidebarMoreMenuItem,
                    {
                        target: 'gatetouch-sidebar',
                        icon: 'admin-plugins',
                    },
                    'GateTouch AI SEO'
                ));
            }

            // The main Sidebar component
            if (PluginSidebar) {
                children.push(el(
                    PluginSidebar,
                    {
                        name: 'gatetouch-sidebar',
                        title: 'GateTouch AI SEO',
                        icon: 'admin-plugins',
                    },
                    el('div', { id: 'gatetouch-sidebar-root', className: 'gatetouch-sidebar-wrap' },
                        el('div', {
                            className: 'gatetouch-sidebar-loading',
                            style: { padding: '30px', textAlign: 'center', color: '#64748b', fontSize: '13px' }
                        },
                            el('div', { className: 'gatetouch-spinner', style: { margin: '0 auto 12px' } }),
                            'Initializing GateTouch AI Engine...'
                        )
                    )
                ));
            }

            return children.length > 0 ? el(Fragment || 'div', {}, ...children) : null;
        } catch (err) {
            console.error('GateTouch Sidebar Render Error:', err);
            return null;
        }
    };

    try {
        registerPlugin('gatetouch-ai-seo', {
            icon: 'admin-plugins', // Explicit icon for the plugin itself
            render: GateTouchSidebar,
        });
    } catch (e) {
        console.warn('GateTouch Sidebar Registration Error:', e);
    }

    // When the sidebar is opened, we populate it
    $(document).on('click', '.interface-pinned-items button[aria-label="GateTouch AI SEO"], .components-menu-item__button', function() {
        setTimeout(initializeSidebarContent, 300);
    });

    function initializeSidebarContent() {
        const root = $('#gatetouch-sidebar-root');
        if (!root.length || root.hasClass('initialized')) return;

        const hasKey = gatetouchData.has_api_key === '1';

        if (!hasKey) {
            root.addClass('initialized').html(`
                <div class="gatetouch-sidebar-locked" style="padding:20px; text-align:center;">
                    <div style="font-size:40px; margin-bottom:15px;">🔒</div>
                    <h4 style="margin:0 0 10px; font-weight:700;">AI Features Need an API Key</h4>
                    <p style="font-size:13px; color:#64748b; line-height:1.5; margin-bottom:20px;">
                        Connect your OpenAI API key to enable one-click meta generation and SEO refinements.
                    </p>
                    <a href="${gatetouchData.ajax_url.replace('admin-ajax.php', 'admin.php?page=gatetouch-settings&tab=ai')}"
                       class="gatetouch-btn gatetouch-btn--primary" style="width:100%; justify-content:center;">
                        Setup API Key →
                    </a>
                    <hr style="margin:20px 0; border:none; border-top:1px solid #e2e8f0;" />
                    <p style="font-size:11px; color:#94a3b8;">Required for AI features only. Manual SEO tools remain available.</p>
                </div>
            `);
            return;
        }

        root.addClass('initialized').html(`
            <div class="gatetouch-sidebar-header">
                <div class="gatetouch-sidebar-score-row">
                    <span class="gatetouch-sidebar-score-label">SEO Health Score</span>
                    <span class="gatetouch-sidebar-score-val" id="gatetouch_sidebar_score">0</span>
                </div>
                <div class="gatetouch-sidebar-score-bar-bg">
                    <div class="gatetouch-sidebar-score-bar-fill" id="gatetouch_sidebar_score_fill" style="width: 0%"></div>
                </div>
                <div style="display:flex; justify-content:space-between; margin-top:12px; padding-top:12px; border-top:1px solid #e2e8f0;">
                    <span style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Word Count</span>
                    <span id="gatetouch_sidebar_word_count" style="font-size:13px; font-weight:700; color:#1e293b;">0 Words</span>
                </div>
            </div>
            <div class="gatetouch-sidebar-field">
                <label class="gatetouch-sidebar-label">Focus Keyword</label>
                <input type="text" id="gatetouch_sidebar_focus_keyword" class="gatetouch-input" placeholder="e.g. best seo plugin" />
            </div>
            <div class="gatetouch-sidebar-field">
                <label class="gatetouch-sidebar-label">SEO Title</label>
                <input type="text" id="gatetouch_sidebar_meta_title" class="gatetouch-input" />
                <div class="gatetouch-sidebar-counter" id="gatetouch_sidebar_title_counter">0 / 60</div>
            </div>
            <div class="gatetouch-sidebar-field">
                <label class="gatetouch-sidebar-label">Meta Description</label>
                <textarea id="gatetouch_sidebar_meta_description" class="gatetouch-textarea" rows="4"></textarea>
                <div class="gatetouch-sidebar-counter" id="gatetouch_sidebar_desc_counter">0 / 160</div>
            </div>
            <div style="margin-top:24px;">
                <button type="button" id="gatetouch_sidebar_generate" class="gatetouch-btn gatetouch-btn--ai" style="width:100%; justify-content:center;">
                    🪄 Generate with AI
                </button>
            </div>
            <div id="gatetouch_sidebar_tips" class="gatetouch-sidebar-tips"></div>
        `);

        // Synchronize values from main meta box
        const syncValues = () => {
            $('#gatetouch_sidebar_focus_keyword').val($('#gatetouch_focus_keyword').val());
            $('#gatetouch_sidebar_meta_title').val($('#gatetouch_meta_title').val());
            $('#gatetouch_sidebar_meta_description').val($('#gatetouch_meta_description').val());

            // Sync score
            const score = $('.gatetouch-score-val').first().text();
            const color = $('.gatetouch-score-val').first().css('color');
            const wordCount = $('#gatetouch_main_word_count').text();

            $('#gatetouch_sidebar_score').text(score).css('color', color);
            $('#gatetouch_sidebar_score_fill').css({ 'width': score + '%', 'background': color });
            $('#gatetouch_sidebar_word_count').text(wordCount);

            // Sync counters
            $('#gatetouch_sidebar_title_counter').text($('#gatetouch-title-counter').text());
            $('#gatetouch_sidebar_desc_counter').text($('#gatetouch-desc-counter').text());
        };

        syncValues();

        // Listen for changes in the main meta box to update sidebar
        $(document).on('gatetouch_analysis_done', syncValues);

        // Event listeners for sidebar inputs to update main box
        $('#gatetouch_sidebar_focus_keyword').on('input', function() {
            $('#gatetouch_focus_keyword').val($(this).val()).trigger('input');
        });
        $('#gatetouch_sidebar_meta_title').on('input', function() {
            $('#gatetouch_meta_title').val($(this).val()).trigger('input');
            syncValues();
        });
        $('#gatetouch_sidebar_meta_description').on('input', function() {
            $('#gatetouch_meta_description').val($(this).val()).trigger('input');
            syncValues();
        });

        // Generate button in sidebar
        $('#gatetouch_sidebar_generate').on('click', function() {
            $('#gatetouch-btn-generate').trigger('click');
        });
    }

})(window.wp, window.jQuery);
