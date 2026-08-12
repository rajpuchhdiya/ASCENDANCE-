/* global gatetouchData, wp, jQuery */
jQuery( function ( $ ) {
    'use strict';

    const { ajax_url, nonce, post_id, strings, has_api_key } = gatetouchData;
    let autoSaveTimeout, analysisTimeout;

    // ── Portal Manager (Fixes Clipping) ──────────────────────────
    const PortalManager = {
        init: function() {
            // Move any portal elements to the body to avoid overflow:hidden clipping
            $('.gatetouch-portal').each(function() {
                const $portal = $(this);
                if (!$portal.parent().is('body')) {
                    $portal.appendTo('body');
                }
            });
        },
        
        toggle: function($trigger, $portal, options = {}) {
            if ($portal.is(':visible')) {
                $portal.fadeOut(150);
                $trigger.removeClass('is-active');
            } else {
                this.position($trigger, $portal, options);
                $portal.fadeIn(150);
                $trigger.addClass('is-active');
            }
        },

        position: function($trigger, $portal, options = {}) {
            const offset = $trigger.offset();
            const triggerHeight = $trigger.outerHeight();
            const triggerWidth = $trigger.outerWidth();
            const portalWidth = $portal.outerWidth();
            
            // Default position: below and right-aligned
            let top = offset.top + triggerHeight + (options.marginTop || 10);
            let left = offset.left + triggerWidth - portalWidth;
            
            // Viewport safety
            if (left < 10) left = 10;
            if (top + $portal.outerHeight() > $(window).height() + $(window).scrollTop()) {
                top = offset.top - $portal.outerHeight() - 10;
            }

            $portal.css({
                top: top,
                left: left,
                position: 'absolute' // Use absolute relative to body
            });
        }
    };


    // ── Utilities ────────────────────────────────────────────────
    function setLoading( active, msg, $btn ) {
        const $s = $( '#gatetouch-ai-status' );
        const $allButtons = $( '.gatetouch-btn' );
        
        if ( active ) {
            $s.show();
            $( '#gatetouch-status-text' ).text( msg || strings.generating );
            $allButtons.prop( 'disabled', true );
            
            if ( $btn && $btn.length ) {
                $btn.data( 'original-html', $btn.html() );
                $btn.html( `<span class="gatetouch-spinner-inline"></span> ${msg || 'Processing...'}` );
                $btn.addClass( 'is-loading' );
            }
        } else {
            $s.hide();
            $allButtons.prop( 'disabled', false );
            
            $( '.gatetouch-btn.is-loading' ).each( function() {
                const $b = $( this );
                if ( $b.data( 'original-html' ) ) {
                    $b.html( $b.data( 'original-html' ) );
                }
                $b.removeClass( 'is-loading' );
            } );
        }
    }

    function flash( msg, type ) {
        const colors = { success: '#10b981', error: '#ef4444', warn: '#f59e0b' };
        const $el = $( '<div>' ).css( {
            position: 'fixed', top: 64, right: 20, zIndex: 999999,
            background: colors[ type ] || colors.success,
            color: '#fff', padding: '10px 18px', borderRadius: 8,
            fontWeight: 700, fontSize: 13, boxShadow: '0 4px 12px rgba(0,0,0,.15)',
            maxWidth: 340, transition: 'opacity .4s'
        } ).text( msg ).appendTo( 'body' );
        setTimeout( () => $el.css( 'opacity', 0 ), 2800 );
        setTimeout( () => $el.remove(), 3200 );
    }

    function updateCounter( $input, $counter, $bar, idealMin, idealMax, hardMax ) {
        const len = $input.val().length;
        const pct = Math.min( ( len / hardMax ) * 100, 100 );
        let cls = 'neutral', bg = '#d1d5db';
        if      ( len === 0 )                        { cls = 'neutral'; bg = '#d1d5db'; }
        else if ( len < idealMin )                   { cls = 'warn';    bg = '#f59e0b'; }
        else if ( len >= idealMin && len <= idealMax ) { cls = 'good'; bg = '#10b981'; }
        else                                         { cls = 'over';    bg = '#ef4444'; }
        $counter.text( `${len} / ${idealMax}` )
                .removeClass( 'gatetouch-counter--neutral gatetouch-counter--good gatetouch-counter--warn gatetouch-counter--over' )
                .addClass( 'gatetouch-counter--' + cls );
        $bar.css( { width: pct + '%', background: bg } );
    }

    function populateMeta( meta ) {
        if ( meta.meta_title )          $( '#gatetouch_meta_title' ).val( meta.meta_title ).trigger( 'input' );
        if ( meta.meta_description )    $( '#gatetouch_meta_description' ).val( meta.meta_description ).trigger( 'input' );
        if ( meta.focus_keyword )       $( '#gatetouch_focus_keyword' ).val( meta.focus_keyword );
        if ( meta.additional_keywords ) $( '#gatetouch_additional_keywords' ).val( meta.additional_keywords );
        if ( meta.og_title )            $( '[name="gatetouch_og_title"]' ).val( meta.og_title );
        if ( meta.og_description )      $( '[name="gatetouch_og_description"]' ).val( meta.og_description );
        if ( meta.twitter_title )       $( '[name="gatetouch_twitter_title"]' ).val( meta.twitter_title );
        if ( meta.twitter_description ) $( '[name="gatetouch_twitter_description"]' ).val( meta.twitter_description );
        if ( meta.schema_type )         $( '#gatetouch_schema_type' ).val( meta.schema_type );
        if ( meta.custom_schema )       $( '#gatetouch_custom_schema' ).val( meta.custom_schema );
        
        // Handle AI Insights
        if ( meta.search_intent ) {
            // Update intent tags and explanations in the existing DOM
            $('.gatetouch-intent-tag').text(meta.search_intent + ' Intent');
            $('.gatetouch-intent-tag').next('span').text(meta.intent_explanation || '');
            
            if ( meta.missing_topics ) {
                const topicHtml = meta.missing_topics.map( t => `<span class="gatetouch-topic-tag" style="background:#fef2f2; color:#ef4444; border:1px solid #fee2e2; padding:8px 14px; border-radius:8px; font-weight:700; font-size:13px;">${t}</span>` ).join('');
                $('.gatetouch-topic-tags').html(topicHtml);
            }
            
            $('.gatetouch-ai-insights-card').removeClass('is-empty');
        }
    }

    function updateSocialImagePreview( imageUrl ) {
        const $fbPreview = $('#gatetouch-fb-img-preview');
        const $fbPlaceholder = $('#gatetouch-fb-img-placeholder');

        if ( imageUrl ) {
            $fbPreview.attr('src', imageUrl).show();
            $fbPlaceholder.hide();
        } else {
            $fbPreview.hide();
            $fbPlaceholder.show();
        }
    }

    function renderAnalysis( data ) {
        if ( ! data || ! data.checks ) return;
        const c = data.color || '#9ca3af';

        // Update Gauge / Badges
        $( '#gatetouch_seo_score_label' ).text( data.label || 'Not Analyzed' ).css( 'color', data.color || '#9ca3af' );
        $( '#gatetouch_seo_score_badge' ).text( data.score || 0 ).css( 'background', data.color || '#9ca3af' );
        $( '#gatetouch_ai_score_badge' ).text( data.ai_score || 0 ).css( 'background', data.ai_color || '#6366f1' );
        $( '#gatetouch-ai-card-score-badge' ).text( 'AI Health: ' + (data.ai_score || 0) + '%' );
        $( '#gatetouch_main_word_count' ).html( `${data.word_count.toLocaleString()} <span style="font-weight:400; font-size:12px; color:var(--riq-text-mid);">Words</span>` );

        // Update Social Preview Fallback
        const $socialImage = $('#gatetouch_og_image');
        const isUsingFallback = $socialImage.attr('data-using-fallback') === '1';
        if ( isUsingFallback && data.fallback_og_image ) {
            $socialImage.val(data.fallback_og_image).attr('data-fallback-image', data.fallback_og_image);
        }
        updateSocialImagePreview($socialImage.val() || data.fallback_og_image || '');

        // Group checks by category
        const cats = {
            cognitive:   { label: 'Cognitive SEO (AI-First)', icon: '🧠', target: '#gatetouch-sub-tab-analysis-ai' },
            basic:       { label: 'Basic SEO', icon: '📄', target: '#gatetouch-sub-tab-analysis-seo' },
            content:     { label: 'Content & Cornerstone', icon: '🏛', target: '#gatetouch-sub-tab-analysis-seo' },
            keywords:    { label: 'Secondary Keywords', icon: '🔑', target: '#gatetouch-sub-tab-analysis-seo' },
            readability: { label: 'Readability', icon: '📖', target: '#gatetouch-sub-tab-analysis-seo' }
        };

        let seoHtml = '<div class="gatetouch-analysis-grid">';
        let aiHtml  = '<div class="gatetouch-analysis-grid">';
        
        for ( const [key, cat] of Object.entries( cats ) ) {
            const checks = data.checks.filter( ch => ch.category === key );
            if ( ! checks.length ) continue;

            let sectionHtml = `
                <div class="gatetouch-analysis-section">
                    <div class="gatetouch-analysis-section-head">
                        <span>${cat.icon} ${cat.label}</span>
                    </div>
                    <div class="gatetouch-analysis-checklist">
            `;

            checks.forEach( ch => {
                sectionHtml += `
                    <div class="gatetouch-check-item gatetouch-check--${ch.status}" data-key="${ch.key || ''}">
                        <span class="gatetouch-check-icon"></span>
                        <span class="gatetouch-check-text">${ch.message}</span>
                        ${ch.key ? '<span class="gatetouch-check-help" title="View Expert Guidance">?</span>' : ''}
                    </div>
                `;
            } );

            sectionHtml += '</div></div>';
            
            if ( key === 'cognitive' ) aiHtml += sectionHtml;
            else seoHtml += sectionHtml;
        }

        seoHtml += '</div>';
        aiHtml  += '</div>';

        // Update the sub-tab panels safely
        const $seoPanel = $( '#gatetouch-sub-tab-analysis-seo' );
        const $aiPanel  = $( '#gatetouch-sub-tab-analysis-ai' );

        // Direct Replacement of grids to avoid duplicate renders
        $seoPanel.find('.gatetouch-analysis-grid').replaceWith( seoHtml );
        $aiPanel.find('#gatetouch-ai-analysis-grid').replaceWith( aiHtml.replace('gatetouch-analysis-grid', 'gatetouch-analysis-grid" id="gatetouch-ai-analysis-grid') );
        
        // Sync the technical checklist into the AI panel too
        $aiPanel.find('#gatetouch-ai-tech-checklist').replaceWith( seoHtml.replace('gatetouch-analysis-grid', 'gatetouch-analysis-grid" id="gatetouch-ai-tech-checklist') );
        
        // Ensure panels are visible if no grid was found initially
        if (!$seoPanel.find('.gatetouch-analysis-grid').length) $seoPanel.prepend(seoHtml);
    }

    // Event Delegation (Fixes Gutenberg Re-render Bugs)
    $( document ).on( 'click', '.gatetouch-tab-btn', function ( e ) {
        e.preventDefault();
        const tab = $( this ).data( 'tab' );
        if ( ! tab ) return;

        $('#gatetouch_active_tab').val(tab);
        $(this).addClass('active').siblings().removeClass('active');
        $('.gatetouch-tab-panel').removeClass('active');
        $('#gatetouch-tab-' + tab).addClass('active');
    } );

    $( document ).on( 'click', '.gatetouch-manual-analyze', function ( e ) {
        e.preventDefault();
        runAnalysis();
    } );

    $( document ).on( 'click', '.gatetouch-sub-tab-btn', function (e) {
        e.preventDefault();
        const id = $( this ).data( 'subtab' );
        const $panel = $(this).closest('.gatetouch-tab-panel');
        
        // Determine which subtab type we are saving
        const stateKey = $panel.attr('id') === 'gatetouch-tab-analysis' ? '#gatetouch_active_subtab_analysis' : '#gatetouch_active_subtab_schema';
        $(stateKey).val(id);
        
        $( this ).addClass( 'active' ).siblings().removeClass('active');
        $panel.find('.gatetouch-sub-tab-panel').removeClass( 'active' );
        $( '#gatetouch-sub-tab-' + id ).addClass( 'active' );
    } );
    // ── Guidance Modal Manager ──────────────────────────────────
    const GuidanceManager = {
        show: function(key) {
            if (!key) return;
            
            const $overlay = this.getOverlay();
            $overlay.fadeIn(200).css('display', 'flex');
            
            this.setLoading(true);
            
            $.post(ajax_url, { action: 'gatetouch_get_guidance', nonce, key })
                .done((res) => {
                    this.setLoading(false);
                    if (res.success) {
                        this.render(res.data);
                    } else {
                        flash('Failed to load guidance', 'error');
                        $overlay.fadeOut(150);
                    }
                })
                .fail(() => {
                    this.setLoading(false);
                    flash('Network error', 'error');
                });
        },
        
        getOverlay: function() {
            let $overlay = $('.gatetouch-guidance-overlay');
            if (!$overlay.length) {
                $overlay = $(`
                    <div class="gatetouch-guidance-overlay">
                        <div class="gatetouch-guidance-modal">
                            <div class="gatetouch-guidance-head">
                                <h3 id="riq-guidance-title">Expert Guidance</h3>
                                <button type="button" class="gatetouch-guidance-close">✕</button>
                            </div>
                            <div class="gatetouch-guidance-body">
                                <div id="riq-guidance-content"></div>
                            </div>
                        </div>
                    </div>
                `).appendTo('body');
                
                $overlay.on('click', function(e) {
                    if ($(e.target).hasClass('gatetouch-guidance-overlay')) {
                        $(this).fadeOut(150);
                    }
                });
                
                $overlay.find('.gatetouch-guidance-close').on('click', function() {
                    $overlay.fadeOut(150);
                });
            }
            return $overlay;
        },
        
        setLoading: function(active) {
            const $body = $('#riq-guidance-content');
            if (active) {
                $body.html('<div style="text-align:center; padding:40px;"><div class="gatetouch-spinner" style="margin:0 auto 15px;"></div><p style="color:#64748b; font-size:13px;">Fetching Expert Advice...</p></div>');
            }
        },
        
        render: function(data) {
            $('#riq-guidance-title').text(data.title || 'Expert Guidance');
            
            let html = `
                <div class="gatetouch-guidance-section">
                    <h4>The Problem</h4>
                    <div class="gatetouch-guidance-explanation">${data.explanation}</div>
                </div>
                
                <div class="gatetouch-guidance-section">
                    <h4>Why it matters (SEO Impact)</h4>
                    <div class="gatetouch-guidance-impact">
                        <ul>
                            ${data.seo_impact.map(i => `<li>${i}</li>`).join('')}
                        </ul>
                    </div>
                </div>
                
                <div class="gatetouch-guidance-section">
                    <h4>How to Fix it</h4>
                    <div class="gatetouch-guidance-fix">
                        <p>${data.fix_beginner}</p>
                    </div>
                </div>
            `;
            
            if (data.best_practices) {
                html += `
                    <div class="gatetouch-guidance-section">
                        <h4>Expert Best Practices</h4>
                        <p style="font-size:14px; color:var(--riq-text-mid); line-height:1.6; margin:0;">${data.best_practices}</p>
                    </div>
                `;
            }
            
            $('#riq-guidance-content').html(html);
        }
    };

    $(document).on('click', '.gatetouch-check-help', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const key = $(this).closest('.gatetouch-check-item').data('key');
        GuidanceManager.show(key);
    });

    $(document).on('click', '#gatetouch-link-how-to-improve', function(e) {
        e.preventDefault();
        $('.gatetouch-tab-btn[data-tab="analysis"]').trigger('click');
        $('.gatetouch-sub-tab-btn[data-subtab="analysis-seo"]').trigger('click');
        
        // Smooth scroll to the analysis grid
        $('html, body').animate({
            scrollTop: $('#gatetouch-tab-analysis').offset().top - 150
        }, 500);
    });

    // ── AI Content Handlers ─────────────────────────────────────
    $( document ).on( 'click', '#gatetouch-btn-generate', function () {
        const $btn = $( this );
        if ( has_api_key === '0' ) { flash( strings.no_api_key, 'warn' ); return; }
        setLoading( true, 'Generating Meta...', $btn );
        $.post( ajax_url, { action: 'gatetouch_generate_meta', nonce, post_id } )
            .done( function ( res ) {
                setLoading( false );
                if ( res.success ) {
                    populateMeta( res.data.meta );
                    renderAnalysis( res.data.analysis );
                    // Reflect the new post title in the classic editor and Gutenberg
                    if ( res.data.new_post_title ) {
                        // Classic editor
                        var $titleInput = $( '#title' );
                        if ( $titleInput.length ) {
                            $titleInput.val( res.data.new_post_title ).trigger( 'change' );
                            $( '#title-prompt-text' ).hide();
                        }
                        // Gutenberg (dispatches to the block editor store)
                        if ( typeof wp !== 'undefined' && wp.data && wp.data.dispatch ) {
                            try {
                                wp.data.dispatch( 'core/editor' ).editPost( { title: res.data.new_post_title } );
                            } catch(e) {}
                        }
                    }
                    flash( strings.done, 'success' );
                } else flash( '❌ ' + res.data, 'error' );
            } )
            .fail( () => setLoading( false ) );
    } );

    $( document ).on( 'click', '#gatetouch-btn-improve', function () {
        const $btn = $( this );
        if ( has_api_key === '0' ) { flash( strings.no_api_key, 'warn' ); return; }
        setLoading( true, 'Improving Meta...', $btn );
        $.post( ajax_url, { action: 'gatetouch_improve_meta', nonce, post_id } )
            .done( function ( res ) {
                setLoading( false );
                if ( res.success ) {
                    populateMeta( res.data );
                    flash( strings.done, 'success' );
                } else flash( '❌ ' + res.data, 'error' );
            } )
            .fail( () => setLoading( false ) );
    } );

    $( document ).on( 'click', '#gatetouch-btn-smart-schema', function () {
        const $btn = $( this );
        setLoading( true, 'Detecting Schema...', $btn );
        $.post( ajax_url, { action: 'gatetouch_smart_schema', nonce, post_id } )
            .done( function ( res ) {
                setLoading( false );
                if ( res.success ) {
                    populateMeta( res.data );
                    flash( 'Schema Detected ✓', 'success' );
                } else flash( '❌ ' + res.data, 'error' );
            } )
            .fail( () => setLoading( false ) );
    } );

    $( document ).on( 'click', '#gatetouch-btn-ai-points', function () {
        const $btn = $( this );
        setLoading( true, 'Extracting Points...', $btn );
        $.post( ajax_url, { action: 'gatetouch_ai_points', nonce, post_id } )
            .done( function ( res ) {
                setLoading( false );
                if ( res.success ) {
                    const points = Array.isArray(res.data.points) ? res.data.points.map(p => "• " + p).join("\n") : res.data.points;
                    $( '#gatetouch_key_points' ).val( points ).trigger( 'input' );
                    flash( 'Points Generated ✓', 'success' );
                } else flash( '❌ ' + res.data, 'error' );
            } )
            .fail( () => setLoading( false ) );
    } );

    $( document ).on( 'click', '#gatetouch-btn-faq', function () {
        const $btn = $( this );
        setLoading( true, 'Extracting FAQs...', $btn );
        $.post( ajax_url, { action: 'gatetouch_generate_faq', nonce, post_id } )
            .done( function ( res ) {
                setLoading( false );
                if ( res.success ) {
                    let html = '';
                    res.data.faqs.forEach( f => {
                        html += `
                            <div class="gatetouch-faq-item">
                                <span class="gatetouch-faq-remove">✕</span>
                                <input type="text" name="gatetouch_faq_q[]" value="${f.question}" class="gatetouch-input" style="margin-bottom:8px; font-weight:600;" />
                                <textarea name="gatetouch_faq_a[]" rows="2" class="gatetouch-textarea">${f.answer}</textarea>
                            </div>
                        `;
                    } );
                    $( '#gatetouch-faq-list' ).html( html );
                    flash( 'FAQs Extracted ✓', 'success' );
                } else flash( '❌ ' + res.data, 'error' );
            } )
            .fail( () => setLoading( false ) );
    } );

    $( document ).on( 'click', '#gatetouch-btn-ai-social', function () {
        const $btn = $( this );
        setLoading( true, 'Generating Social Kit...', $btn );
        $.post( ajax_url, { action: 'gatetouch_ai_social', nonce, post_id } )
            .done( function ( res ) {
                setLoading( false );
                if ( res.success ) {
                    let kit = "";
                    if (res.data.linkedin) kit += "--- LINKEDIN ---\n" + res.data.linkedin + "\n\n";
                    if (res.data.facebook) kit += "--- FACEBOOK ---\n" + res.data.facebook + "\n\n";
                    if (res.data.twitter)  kit += "--- X (TWITTER) ---\n" + res.data.twitter + "\n";
                    
                    $( '#gatetouch_social_posts' ).val( kit ).trigger( 'input' );
                    flash( 'Social Kit Ready ✓', 'success' );
                } else flash( '❌ ' + res.data, 'error' );
            } )
            .fail( () => setLoading( false ) );
    } );

    $( document ).on( 'click', '#gatetouch-faq-add', function() {
        const html = `
            <div class="gatetouch-faq-item">
                <span class="gatetouch-faq-remove">✕</span>
                <input type="text" name="gatetouch_faq_q[]" placeholder="Question" class="gatetouch-input" style="margin-bottom:8px; font-weight:600;" />
                <textarea name="gatetouch_faq_a[]" rows="2" placeholder="Answer" class="gatetouch-textarea"></textarea>
            </div>
        `;
        $( '#gatetouch-faq-list' ).append( html );
    } );

    $( document ).on( 'click', '.gatetouch-faq-remove', function() {
        if ( confirm( strings.confirm_del ) ) {
            $( this ).closest( '.gatetouch-faq-item' ).remove();
        }
    } );

    $( document ).on( 'click', '#gatetouch-og-media-btn', function(e) {
        e.preventDefault();
        const frame = wp.media({
            title: 'Select Social Share Image',
            multiple: false,
            library: { type: 'image' }
        });
        frame.on('select', function() {
            const attachment = frame.state().get('selection').first().toJSON();
            $('#gatetouch_og_image').val(attachment.url).trigger('input');
        });
        frame.open();
    } );


    // ── Field Auto-Save & Analysis ────────────────────────────────
    function syncAndAnalyze( field, value ) {
        clearTimeout( autoSaveTimeout );
        autoSaveTimeout = setTimeout( () => {
            $.post( ajax_url, { action: 'gatetouch_save_meta_ajax', nonce, post_id, field, value } )
                .done( () => runAnalysis() );
        }, 800 );
    }

    $(document).on('input', '#gatetouch_meta_title', function() {
        const val = $(this).val();
        updateCounter($(this), $('#gatetouch-title-counter'), $('#gatetouch-title-bar'), 50, 60, 70);
        $('#gatetouch-serp-title').text(val || 'Your meta title will appear here…');
        syncAndAnalyze('meta_title', val);
    });

    $(document).on('input', '#gatetouch_meta_description', function() {
        const val = $(this).val();
        updateCounter($(this), $('#gatetouch-desc-counter'), $('#gatetouch-desc-bar'), 145, 160, 180);
        $('#gatetouch-serp-desc').text(val || 'Your meta description will appear here…');
        syncAndAnalyze('meta_description', val);
    });

    $(document).on('input', '#gatetouch_focus_keyword', function() {
        syncAndAnalyze('focus_keyword', $(this).val());
    });

    $(document).on('input', '#gatetouch_additional_keywords', function() {
        syncAndAnalyze('additional_keywords', $(this).val());
    });

    $(document).on('input', '#gatetouch_og_image', function() {
        const val = $(this).val();
        $(this).attr('data-using-fallback', '0');
        updateSocialImagePreview(val);
        syncAndAnalyze('og_image', val);
    });

    // ── Checkbox Auto-Save ──────────────────────────────────────
    $(document).on('change', 'input[name^="gatetouch_"]', function() {
        if ($(this).attr('type') === 'checkbox') {
            const field = $(this).attr('name').replace('gatetouch_', '');
            const value = $(this).is(':checked') ? '1' : '';
            syncAndAnalyze(field, value);
        }
    });

    // ── Analyze (Automated & Reactive) ──────────────────────────
    function runAnalysis() {
        const kw = $( '#gatetouch_focus_keyword' ).val() || '';

        clearTimeout( analysisTimeout );
        analysisTimeout = setTimeout( () => {
            let content = '', title = '', featured_image_id = 0;
            if ( typeof wp !== 'undefined' && wp.data && wp.data.select( 'core/editor' ) ) {
                const editor = wp.data.select( 'core/editor' );
                content = editor.getEditedPostAttribute( 'content' ) || '';
                title = editor.getEditedPostAttribute( 'title' );
                featured_image_id = parseInt( editor.getEditedPostAttribute( 'featured_media' ), 10 ) || 0;
            }

            if ( ! title ) {
                title = $( '#title' ).val() || $( 'input[name="post_title"]' ).val() || '';
            }

            if ( ! content ) {
                content = $( '#content' ).val() || '';
            }

            if ( ! featured_image_id ) {
                featured_image_id = parseInt( $( '#_thumbnail_id' ).val(), 10 ) || 0;
            }

            setLoading( true, strings.analyzing );
            const payload = {
                action: 'gatetouch_analyze_seo',
                nonce, post_id,
                keyword: kw
            };

            if ( content ) payload.content = content;
            if ( title ) payload.title = title;
            if ( featured_image_id > 0 ) payload.featured_image_id = featured_image_id;

            $.post( ajax_url, payload )
            .done( function ( res ) {
                setLoading( false );
                if ( res.success ) renderAnalysis( res.data );
            } )
            .fail( () => setLoading( false ) );
        }, 1200 );
    }

    // ── Initialization ──────────────────────────────────────────
    function init() {
        PortalManager.init();
        
        // Initial Counters
        $('#gatetouch_meta_title').trigger('input');
        $('#gatetouch_meta_description').trigger('input');
        
        // Ensure all fields are visible by default
        $('.gatetouch-field--keyword, .gatetouch-field--title, .gatetouch-field--desc, .gatetouch-field--preview').show();

        runAnalysis();
    }

    // Run on load
    init();
    
    // Ensure portals stay in body periodically
    setInterval(function() {
        PortalManager.init();
    }, 2000);

    // Watch for Gutenberg changes
    if ( typeof wp !== 'undefined' && wp.data && wp.data.subscribe ) {
        let lastContent = '', lastTitle = '', lastFeat = 0;
        wp.data.subscribe( () => {
            const editor = wp.data.select( 'core/editor' );
            if ( ! editor ) return;
            const currentContent = editor.getEditedPostAttribute( 'content' );
            const currentTitle   = editor.getEditedPostAttribute( 'title' );
            const currentFeat    = editor.getEditedPostAttribute( 'featured_media' );

            if ( currentContent !== lastContent || currentTitle !== lastTitle || currentFeat !== lastFeat ) {
                lastContent = currentContent; lastTitle = currentTitle; lastFeat = currentFeat;
                runAnalysis();
            }
        } );
    }

} );
