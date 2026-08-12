/**
 * GateTouch AI SEO — Enterprise Gutenberg Suite
 * High-fidelity React implementation with Sidebar and Bottom Panel integration.
 */
(function(wp, $) {
    'use strict';

    if (!wp || !wp.plugins || !wp.editPost || !wp.data || !wp.element || !wp.components) return;

    const { registerPlugin } = wp.plugins;
    const { PluginDocumentSettingPanel, PluginSidebar } = wp.editPost;
    const {
        TextControl,
        TextareaControl,
        PanelBody,
        PanelRow,
        Button,
        Dashicon,
        Spinner,
        TabPanel,
        ExternalLink,
        ProgressBar
    } = wp.components;
    const { useSelect, useDispatch } = wp.data;
    const { useState, useEffect, Fragment } = wp.element;
    const { __ } = wp.i18n;

    /**
     * Main SEO Content Component
     */
    const GateTouchSEOContent = ({ meta, updateMeta, isGenerating, handleGenerate, seoAnalysis }) => {
        const gatetouchMeta = meta[gatetouchData.meta_key] || {};

        return wp.element.createElement("div", { className: "gatetouch-seo-container", style: { padding: '5px' } },
            // AI Action Row
            wp.element.createElement("div", {
                style: {
                    marginBottom: '20px',
                    padding: '15px',
                    background: 'rgba(99, 102, 241, 0.05)',
                    borderRadius: '10px',
                    border: '1px solid rgba(99, 102, 241, 0.2)'
                }
            },
                wp.element.createElement("div", { style: { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '10px' } },
                    wp.element.createElement("span", { style: { fontSize: '12px', fontWeight: '800', color: '#6366f1', textTransform: 'uppercase' } }, "AI SEO Assistant"),
                    seoAnalysis && wp.element.createElement("span", {
                        style: {
                            background: seoAnalysis.score >= 70 ? '#10b981' : '#f59e0b',
                            color: '#fff',
                            padding: '2px 8px',
                            borderRadius: '4px',
                            fontSize: '11px',
                            fontWeight: '800'
                        }
                    }, `Score: ${seoAnalysis.score}`)
                ),
                wp.element.createElement(Button, {
                    isPrimary: true,
                    onClick: handleGenerate,
                    disabled: isGenerating,
                    style: { width: '100%', height: '40px', background: 'linear-gradient(135deg, #6366f1 0%, #a855f7 100%)', border: 'none', borderRadius: '6px', fontWeight: '700' }
                },
                    isGenerating ? wp.element.createElement(Spinner) : __("Magic AI Optimization", "gatetouch-ai-seo")
                )
            ),

            // Keyword Section
            wp.element.createElement(TextControl, {
                label: __("Focus Keyword", "gatetouch-ai-seo"),
                value: gatetouchMeta.focus_keyword || "",
                onChange: (val) => updateMeta('focus_keyword', val),
                help: __("What should this page rank for?", "gatetouch-ai-seo")
            }),

            // Snippet Preview (Google Style)
            wp.element.createElement("div", {
                className: "gatetouch-google-preview",
                style: {
                    background: '#fff',
                    border: '1px solid #e2e8f0',
                    padding: '15px',
                    borderRadius: '8px',
                    marginTop: '20px',
                    boxShadow: '0 4px 6px -1px rgba(0,0,0,0.05)'
                }
            },
                wp.element.createElement("div", { style: { color: '#1a0dab', fontSize: '18px', lineHeight: '1.2', marginBottom: '4px', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' } },
                    gatetouchMeta.meta_title || __("Insert Title...", "gatetouch-ai-seo")
                ),
                wp.element.createElement("div", { style: { color: '#006621', fontSize: '14px', marginBottom: '4px' } },
                    window.location.origin + "/..."
                ),
                wp.element.createElement("div", { style: { color: '#545454', fontSize: '13px', lineHeight: '1.4' } },
                    gatetouchMeta.meta_description || __("Please provide a meta description by editing the snippet below...", "gatetouch-ai-seo")
                )
            ),

            // SEO Fields
            wp.element.createElement("div", { style: { marginTop: '20px' } },
                wp.element.createElement(TextControl, {
                    label: __("SEO Title", "gatetouch-ai-seo"),
                    value: gatetouchMeta.meta_title || "",
                    onChange: (val) => updateMeta('meta_title', val),
                }),
                wp.element.createElement(TextareaControl, {
                    label: __("Meta Description", "gatetouch-ai-seo"),
                    value: gatetouchMeta.meta_description || "",
                    rows: 4,
                    onChange: (val) => updateMeta('meta_description', val),
                })
            ),

            // Analysis Section
            seoAnalysis && wp.element.createElement("div", { style: { marginTop: '25px', padding: '15px', background: '#f8fafc', borderRadius: '10px' } },
                wp.element.createElement("h4", { style: { fontSize: '14px', fontWeight: '800', marginBottom: '12px', color: '#1e293b' } }, "SEO Checklist"),
                seoAnalysis.checks.map((check, i) =>
                    wp.element.createElement("div", {
                        key: i,
                        style: { display: 'flex', gap: '10px', fontSize: '12px', marginBottom: '8px', alignItems: 'flex-start' }
                    },
                        wp.element.createElement(Dashicon, {
                            icon: check.status === 'pass' ? 'yes-alt' : (check.status === 'warn' ? 'warning' : 'dismiss'),
                            style: { color: check.status === 'pass' ? '#10b981' : (check.status === 'warn' ? '#f59e0b' : '#ef4444'), marginTop: '2px' }
                        }),
                        wp.element.createElement("span", { style: { color: '#475569' } }, check.message)
                    )
                )
            )
        );
    };

    /**
     * Master Panel Component (Removed Sidebar, only used for data bridging)
     */
    const GateTouchGutenbergMaster = () => {
        // We no longer render anything in the sidebar
        return null;
    };

    // Register the Master Plugin (Still needed for data store access)
    registerPlugin('gatetouch-ai-seo-suite', {
        render: GateTouchGutenbergMaster,
    });

    /**
     * NATIVE BOTTOM PANEL INJECTION
     * To ensure it appears "directly without click" and looks enterprise-grade.
     */
    const injectBottomPanel = () => {
        const editorArea = document.querySelector('.interface-interface-skeleton__content');
        if (!editorArea || document.getElementById('gatetouch-gutenberg-bottom-root')) return;

        // Create the root container
        const rootDiv = document.createElement('div');
        rootDiv.id = 'gatetouch-gutenberg-bottom-root';
        rootDiv.className = 'gatetouch-native-bottom-panel';
        rootDiv.style.borderTop = '1px solid #e2e8f0';
        rootDiv.style.background = '#fff';
        rootDiv.style.marginTop = '40px';
        rootDiv.style.padding = '40px';

        // Find the right place to inject (after the editor)
        const scrollContainer = document.querySelector('.interface-interface-skeleton__content .block-editor-writing-flow');
        if (scrollContainer) {
            scrollContainer.appendChild(rootDiv);

            // Render the UI into the bottom root
            const BottomPanelWrapper = () => {
                const { meta } = useSelect((select) => ({
                    meta: select('core/editor').getEditedPostAttribute('meta') || {},
                }));
                const { editPost } = useDispatch('core/editor');

                const [isGenerating, setIsGenerating] = useState(false);
                const [seoAnalysis, setSeoAnalysis] = useState(null);

                const updateMeta = (field, value) => {
                    const currentMeta = meta[gatetouchData.meta_key] || {};
                    const newMeta = { ...currentMeta, [field]: value };
                    editPost({ meta: { [gatetouchData.meta_key]: newMeta } });
                };

                const handleGenerate = () => {
                    setIsGenerating(true);
                    $.post(gatetouchData.ajax_url, {
                        action: 'gatetouch_generate_meta',
                        nonce: gatetouchData.nonce,
                        post_id: gatetouchData.post_id
                    }, (res) => {
                        setIsGenerating(false);
                        if (res.success) {
                            editPost({ meta: { [gatetouchData.meta_key]: res.data.meta } });
                            setSeoAnalysis(res.data.analysis);
                            // Update the Gutenberg post title so the keyword-in-title score check passes
                            if (res.data.new_post_title) {
                                try {
                                    wp.data.dispatch('core/editor').editPost({ title: res.data.new_post_title });
                                } catch(e) {}
                            }
                        }
                    });
                };

                return wp.element.createElement("div", { className: "gatetouch-bottom-ui-wrap" },
                    wp.element.createElement("h2", { style: { fontSize: '20px', fontWeight: '800', marginBottom: '25px', display: 'flex', alignItems: 'center', gap: '10px' } },
                        wp.element.createElement("span", { style: { background: 'var(--riq-ai-gradient)', WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent' } }, "GateTouch AI SEO"),
                        wp.element.createElement("span", { style: { fontSize: '10px', background: '#6366f1', color: '#fff', padding: '2px 6px', borderRadius: '4px', textTransform: 'uppercase' } }, "Optimization Engine")
                    ),
                    wp.element.createElement(GateTouchSEOContent, {
                        meta, updateMeta, isGenerating, handleGenerate, seoAnalysis
                    })
                );
            };

            wp.element.render(
                wp.element.createElement(BottomPanelWrapper),
                rootDiv
            );
        }
    };

    // Watch for editor load
    const observer = new MutationObserver((mutations) => {
        injectBottomPanel();
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

})(window.wp, window.jQuery);
