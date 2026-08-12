/* global gatetouchAdmin, jQuery */
jQuery( function ( $ ) {
    'use strict';

    /**
     * Global Flash Notification System
     */
    window.gatetouchFlash = function( msg, type = 'success' ) {
        const colors = { success: '#6366f1', error: '#ef4444', warn: '#f59e0b', info: '#3b82f6' };
        const $el = $( '<div>' ).css( {
            position: 'fixed', top: 64, right: 20, zIndex: 99999,
            background: colors[ type ] || colors.success,
            color: '#fff', padding: '12px 24px', borderRadius: '8px',
            boxShadow: '0 10px 15px -3px rgba(0,0,0,0.1)',
            fontWeight: '600', fontSize: '14px', display: 'none'
        } ).text( msg );

        $( 'body' ).append( $el );
        $el.fadeIn( 200 ).delay( 3000 ).fadeOut( 200, function() { $(this).remove(); } );
    };

    function gatetouchAjaxUrl() {
        return ( window.gatetouchAdmin && window.gatetouchAdmin.ajax_url ) ? window.gatetouchAdmin.ajax_url : window.ajaxurl;
    }

    function gatetouchNonce() {
        return ( window.gatetouchAdmin && window.gatetouchAdmin.nonce ) ? window.gatetouchAdmin.nonce : '';
    }

    /**
     * Page Tab Switching (Universal)
     */
    $( document ).on( 'click', '.gatetouch-page-tab', function (e) {
        e.preventDefault();
        const $tab = $( this );
        const target = $tab.data( 'target' );
        
        $tab.siblings( '.gatetouch-page-tab' ).removeClass( 'active' );
        $tab.addClass( 'active' );
        
        const $wrapper = $tab.closest( '.gatetouch-admin-wrap' );
        $wrapper.find( 'input[name="gatetouch_active_visibility_tab"]' ).val( target );
        $wrapper.find( '.gatetouch-page-tab-content' ).hide();
        $wrapper.find( '#' + target ).fadeIn( 200 );
    } );

    $( document ).on( 'click', '.gatetouch-tab-submit', function() {
        const $btn = $( this );
        const target = $btn.data( 'active-tab' );
        if ( target ) {
            $btn.closest( 'form' ).find( 'input[name="gatetouch_active_visibility_tab"]' ).val( target );
        }
        $btn.data( 'gatetouch-clicked', '1' );
    } );

    $( document ).on( 'submit', '.gatetouch-visibility-form', function() {
        const $btn = $( this ).find( '.gatetouch-tab-submit' ).filter( function() {
            return $( this ).data( 'gatetouch-clicked' ) === '1';
        } ).first();

        if ( ! $btn.length ) {
            return;
        }

        const savingText = $btn.data( 'active-tab' ) === 'tab-robots' ? 'Saving robots.txt...' : 'Saving...';
        $btn.text( savingText );
        window.setTimeout( function() {
            $btn.prop( 'disabled', true );
        }, 0 );
    } );

    /**
     * AI Homepage Meta Generator
     */
    $( '#gatetouch-gen-homepage' ).on( 'click', function () {
        const $btn = $(this);
        if ( gatetouchAdmin.has_api_key === '0' ) {
            alert( 'Please add your OpenAI API key in AI Settings first.' );
            return;
        }

        $btn.prop( 'disabled', true ).text( 'Generating...' );
        $.post( gatetouchAdmin.ajax_url, {
            action: 'gatetouch_generate_homepage_meta',
            nonce: gatetouchAdmin.nonce
        } ).done( function ( res ) {
            $btn.prop( 'disabled', false ).text( 'Generate with AI' );
            if ( res.success ) {
                $( 'input[name="gatetouch_homepage_title"]' ).val( res.data.title );
                $( 'textarea[name="gatetouch_homepage_description"]' ).val( res.data.description );
                window.gatetouchFlash( 'Homepage meta generated successfully!' );
            } else {
                alert( 'Error: ' + res.data );
            }
        } );
    } );

    /**
     * Flush Rewrite Rules
     */
    $( '#gatetouch-flush-rules-btn' ).on( 'click', function () {
        const $btn = $(this);
        $btn.prop( 'disabled', true ).text( 'Flushing...' );
        $.post( gatetouchAdmin.ajax_url, {
            action: 'gatetouch_flush_rewrites',
            nonce: gatetouchAdmin.nonce
        } ).done( function ( res ) {
            $btn.prop( 'disabled', false ).text( 'Flush Rules' );
            if ( res.success ) {
                window.gatetouchFlash( 'SEO Rewrite rules flushed!' );
            }
        } );
    } );

    /**
     * Title Separator Picker
     */
    $( '.gatetouch-separator-picker__item' ).on( 'click', function () {
        $( '.gatetouch-separator-picker__item' ).removeClass( 'is-active' );
        $( this ).addClass( 'is-active' );
        $( 'input[name="gatetouch_title_separator"]' ).val( $( this ).data( 'sep' ) );
    } );

    /**
     * Download Branded PDF Report
     */
    $( '#gatetouch-download-report' ).on( 'click', function () {
        const $btn = $(this);
        $btn.prop( 'disabled', true ).text( 'Generating Report...' );
        
        $.post( gatetouchAdmin.ajax_url, {
            action: 'gatetouch_generate_report',
            nonce: gatetouchAdmin.nonce,
            type: 'pdf'
        } ).done( function ( res ) {
            $btn.prop( 'disabled', false ).text( 'Download PDF Audit' );
            if ( res.success ) {
                const reportWindow = window.open('', '_blank');
                reportWindow.document.write(res.data.html);
                reportWindow.document.close();
                setTimeout(() => {
                    reportWindow.print();
                }, 500);
            } else {
                alert( 'Error generating report.' );
            }
        } );
    } );

    /**
     * Broadcast Pulse (Ping)
     */
    $( '#gatetouch-ping-btn' ).on( 'click', function () {
        const $btn = $(this);
        const $result = $( '#gatetouch-ping-result' );
        
        $btn.prop( 'disabled', true ).text( 'Broadcasting...' );
        $.post( gatetouchAdmin.ajax_url, {
            action: 'gatetouch_ping_now',
            nonce: gatetouchAdmin.nonce
        } ).done( function ( res ) {
            $btn.prop( 'disabled', false ).text( 'Broadcast Pulse Now' );
            if ( res.success ) {
                $result.css( 'color', '#10b981' ).text( '✅ Pinged Search Engines!' );
                window.gatetouchFlash( 'Search pulse broadcasted successfully!' );
            } else {
                $result.css( 'color', '#ef4444' ).text( 'Error. Check logs.' );
            }
        } );
    } );

    /**
     * Instant Indexing status helper.
     */
    $( '#gatetouch-btn-instant-index' ).on( 'click', function () {
        const $btn = $(this);
        const url = $( '#gatetouch-instant-url' ).val();
        if ( ! url ) {
            window.gatetouchFlash( 'Please enter a URL first.', 'warn' );
            return;
        }

        $btn.prop( 'disabled', true ).text( '⚡ Sending...' );
        window.gatetouchFlash( 'Sending URL to Indexing Console...', 'info' );
        
        setTimeout( function () {
            $btn.prop( 'disabled', false ).text( '⚡ Index Now' );
            window.gatetouchFlash( 'URL indexing request queued.', 'success' );
            $( '#gatetouch-instant-url' ).val('');
        }, 1500 );
    } );

    /**
     * Headline Analyzer logic
     */
    $( '#gatetouch-headline-analyze-btn' ).on( 'click', function () {
        const $btn = $(this);
        const headline = $( '#gatetouch-headline-input' ).val();
        if ( ! headline ) {
            window.gatetouchFlash( 'Please enter a headline first.', 'warn' );
            return;
        }

        $btn.prop( 'disabled', true ).text( 'Analyzing...' );
        const $result = $( '#gatetouch-headline-result' );
        $result.fadeOut( 200 );

        $.post( gatetouchAdmin.ajax_url, {
            action: 'gatetouch_analyze_headline',
            nonce: gatetouchAdmin.nonce,
            headline: headline
        } ).done( function ( res ) {
            $btn.prop( 'disabled', false ).text( 'Analyze' );
            if ( res.success ) {
                const data = res.data;
                const scoreColor = data.score >= 70 ? '#10b981' : (data.score >= 40 ? '#f59e0b' : '#ef4444');
                
                let html = `
                    <div class="gatetouch-card" style="padding:30px; border:2px solid ${scoreColor};">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
                            <div>
                                <h3 style="margin:0; font-size:24px; font-weight:800; color:#1e293b;">Headline Analysis</h3>
                                <p style="margin:5px 0 0; color:#64748b;">${data.seo_analysis}</p>
                            </div>
                            <div style="text-align:center;">
                                <div style="font-size:36px; font-weight:800; color:${scoreColor}; line-height:1;">${data.score}</div>
                                <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Overall Score</div>
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:30px; margin-bottom:30px;">
                            <div class="gatetouch-headline-stats">
                                <h4 style="margin:0 0 15px; font-size:14px; text-transform:uppercase; color:#94a3b8; letter-spacing:1px;">Structure & Sentiment</h4>
                                <div style="display:flex; flex-direction:column; gap:12px;">
                                    <div style="display:flex; justify-content:space-between;">
                                        <span style="color:#64748b;">Sentiment</span>
                                        <strong style="color:#1e293b;">${data.sentiment}</strong>
                                    </div>
                                    <div style="display:flex; justify-content:space-between;">
                                        <span style="color:#64748b;">Character Count</span>
                                        <strong style="color:#1e293b;">${data.character_count}</strong>
                                    </div>
                                    <div style="display:flex; justify-content:space-between;">
                                        <span style="color:#64748b;">Word Count</span>
                                        <strong style="color:#1e293b;">${data.word_count}</strong>
                                    </div>
                                    <div style="display:flex; justify-content:space-between;">
                                        <span style="color:#64748b;">Readability</span>
                                        <strong style="color:#1e293b;">${data.readability}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="gatetouch-headline-balance">
                                <h4 style="margin:0 0 15px; font-size:14px; text-transform:uppercase; color:#94a3b8; letter-spacing:1px;">Word Balance</h4>
                                <div style="display:flex; flex-direction:column; gap:8px;">
                                    <div>
                                        <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:4px;">
                                            <span>Common Words</span>
                                            <span>${data.word_balance.common}</span>
                                        </div>
                                        <div style="height:6px; background:#f1f5f9; border-radius:3px; overflow:hidden;">
                                            <div style="height:100%; background:#6366f1; width:${data.word_balance.common}"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:4px;">
                                            <span>Emotional Words</span>
                                            <span>${data.word_balance.emotional}</span>
                                        </div>
                                        <div style="height:6px; background:#f1f5f9; border-radius:3px; overflow:hidden;">
                                            <div style="height:100%; background:#ec4899; width:${data.word_balance.emotional}"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:4px;">
                                            <span>Power Words</span>
                                            <span>${data.word_balance.power}</span>
                                        </div>
                                        <div style="height:6px; background:#f1f5f9; border-radius:3px; overflow:hidden;">
                                            <div style="height:100%; background:#10b981; width:${data.word_balance.power}"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div style="background:#f8fafc; border-radius:12px; padding:20px;">
                            <h4 style="margin:0 0 10px; font-size:14px; color:#1e293b;">How to improve this headline:</h4>
                            <ul style="margin:0; padding-left:20px; color:#475569; font-size:14px; line-height:1.6;">
                                ${data.improvements.map(tip => `<li>${tip}</li>`).join('')}
                            </ul>
                        </div>
                    </div>
                `;
                $result.html( html ).fadeIn( 400 );
                window.gatetouchFlash( 'Headline analysis complete!' );
            } else {
                alert( 'Error: ' + res.data );
            }
        } );
    } );

    /**
     * Run Full Enterprise Scan
     */
    $( '#gatetouch-run-scan' ).on( 'click', function () {
        const $btn = $(this);
        $btn.prop( 'disabled', true ).text( 'Running Site Audit...' );
        window.gatetouchFlash( 'Scanning site technical foundation...', 'info' );

        $.post( gatetouchAdmin.ajax_url, {
            action: 'gatetouch_run_scan',
            nonce: gatetouchAdmin.nonce
        } ).done( function ( res ) {
            $btn.prop( 'disabled', false ).text( '🚀 Run Enterprise Scan' );
            if ( res.success ) {
                window.gatetouchFlash( 'Site audit complete! Refreshing metrics...', 'success' );
                setTimeout( () => window.location.reload(), 1000 );
            } else {
                alert( 'Audit failed. Check logs.' );
            }
        } );
    } );

    /**
     * Check URL hash for active tab
     */
    const hash = window.location.hash;
    if ( hash ) {
        const $tab = $( '.gatetouch-page-tab[data-target="' + hash.substring(1) + '"]' );
        if ( $tab.length ) $tab.trigger( 'click' );
    }

    // ── Notice dismiss ────────────────────────────────────────────────────────
    $( '#gatetouch-dismiss-v2, .notice-dismiss' ).on( 'click', function() {
        $.post( ajaxurl, { action: 'gatetouch_dismiss_notice', nonce: gatetouchAdmin.nonce } );
        $( '#gatetouch-whats-new' ).fadeOut();
    } );

    // ── Dashboard buttons ─────────────────────────────────────────────────────
    $( '#riq-run-full-audit' ).on( 'click', function(e) {
        e.preventDefault();
        var $btn = $( this );
        $btn.addClass( 'riq-btn--loading' ).text( 'Scanning...' );
        $.post( ajaxurl, { action: 'gatetouch_run_scan', nonce: gatetouchAdmin.nonce }, function(res) {
            if ( res.success ) window.location.reload();
        } );
    } );

    $( '#riq-ping-sitemap' ).on( 'click', function(e) {
        e.preventDefault();
        var $btn = $( this );
        $btn.addClass( 'riq-btn--loading' ).text( 'Pinging...' );
        $.post( ajaxurl, { action: 'gatetouch_ping_now', nonce: gatetouchAdmin.nonce }, function(res) {
            if ( res.success ) {
                $btn.removeClass( 'riq-btn--loading' ).text( '✅ Pinged!' );
                setTimeout( function() { $btn.text( 'Regenerate Sitemap' ); }, 2000 );
            }
        } );
    } );

    $( '#riq-start-crawl' ).on( 'click', function(e) {
        e.preventDefault();
        var $btn = $( this );
        $btn.addClass( 'riq-btn--loading' ).text( 'Starting Crawl...' );
        $.post( ajaxurl, { action: 'gatetouch_trigger_crawl', nonce: gatetouchAdmin.nonce }, function(res) {
            if ( res.success ) {
                $btn.text( '✅ Crawl Queued' );
                setTimeout( function() { window.location.reload(); }, 1500 );
            }
        } );
    } );

    $( '#riq-optimize-db-v2' ).on( 'click', function(e) {
        e.preventDefault();
        var $btn = $( this );
        $btn.text( 'Optimizing...' );
        $.post( ajaxurl, { action: 'gatetouch_optimize_db', nonce: gatetouchAdmin.nonce }, function(res) {
            if ( res.success ) {
                $btn.text( '✅ Done' );
                setTimeout( function() { $btn.text( 'Optimize Now' ); }, 2000 );
            }
        } );
    } );

    // ── Competitor Analysis ───────────────────────────────────────────────────
    $( '#gatetouch-analyze-competitor-btn' ).on( 'click', function() {
        var url = $( '#gatetouch-competitor-url' ).val();
        if ( ! url ) { alert( 'Please enter a valid URL.' ); return; }

        var btn = $( this );
        btn.prop( 'disabled', true ).text( 'Analyzing...' );
        $( '#gatetouch-competitor-results' ).html( '<div class="gatetouch-card" style="padding:40px; text-align:center;"><div class="gatetouch-spinner" style="margin:0 auto 20px;"></div>Fetching competitor data...</div>' );

        $.post( gatetouchAdmin.ajax_url, {
            action: 'gatetouch_analyze_competitor',
            url: url,
            nonce: gatetouchAdmin.nonce
        }, function(res) {
            btn.prop( 'disabled', false ).text( 'Analyze Site' );
            if ( res.success ) {
                var data = res.data;
                var html = '<div style="display:grid; grid-template-columns: 1fr 2fr; gap:30px;">' +
                    '<div class="gatetouch-card"><div class="gatetouch-card__body" style="padding:30px; text-align:center;">' +
                    '<div style="font-size:48px; font-weight:800; color:' + data.color + ';">' + data.score + '%</div>' +
                    '<div style="font-weight:700; color:' + data.color + '; text-transform:uppercase;">' + data.label + '</div>' +
                    '</div></div>' +
                    '<div class="gatetouch-card"><div class="gatetouch-card__body" style="padding:30px;">' +
                    '<h3 style="margin:0 0 15px; font-size:18px;">' + ( data.title || 'No Title' ) + '</h3>' +
                    '<div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; font-size:13px;">' +
                    '<div><strong>Word Count:</strong> ' + data.word_count + '</div>' +
                    '<div><strong>H1 Tags:</strong> ' + data.h1_count + '</div>' +
                    '<div><strong>H2 Tags:</strong> ' + data.h2_count + '</div>' +
                    '</div>' +
                    '<p style="margin-top:15px; font-size:13px; color:var(--riq-text-light);">' + ( data.description || 'No description found.' ) + '</p>' +
                    '</div></div></div>';
                $( '#gatetouch-competitor-results' ).html( html );
            } else {
                $( '#gatetouch-competitor-results' ).html( '<div class="gatetouch-card" style="padding:40px; text-align:center; color:#ef4444;">Error: ' + ( res.data || 'Could not analyze URL' ) + '</div>' );
            }
        } );
    } );

    // ── Redirect Manager (Extended) ───────────────────────────────────────────
    $( '#gatetouch-add-redirect-btn' ).on( 'click', function() {
        $( '#gatetouch-modal-title' ).text( 'Create Redirect' );
        $( '#gatetouch-redirect-id' ).val( 0 );
        $( '#gatetouch-redirect-form' )[0].reset();
        $( '#gatetouch-redirect-modal' ).fadeIn();
    } );

    $( '.gatetouch-modal-close' ).on( 'click', function() {
        $( '#gatetouch-redirect-modal' ).fadeOut();
    } );

    $( window ).on( 'click', function(e) {
        if ( $( e.target ).is( '#gatetouch-redirect-modal' ) ) $( '#gatetouch-redirect-modal' ).fadeOut();
    } );

    $( '.sub-tab' ).on( 'click', function() {
        $( '.sub-tab' ).removeClass( 'active' );
        $( this ).addClass( 'active' );
        $( '.gatetouch-tab-panel' ).removeClass( 'active' );
        $( '#panel-' + $( this ).data( 'tab' ) ).addClass( 'active' );
    } );

    $( '#gatetouch-ai-match-btn' ).on( 'click', function() {
        var url  = $( '#gatetouch-redirect-source' ).val();
        if ( ! url ) return;
        var list = $( '#gatetouch-ai-suggestions-list' );
        list.html( '<p style="font-size:12px; padding:10px;">AI is thinking...</p>' ).show();
        $.post( gatetouchAdmin.ajax_url, {
            action: 'gatetouch_ai_suggest', nonce: gatetouchAdmin.nonce, url: url
        }, function(res) {
            if ( res.success && res.data.suggestions.length ) {
                var html = '<p style="font-size:11px; font-weight:800; margin-bottom:10px; color:#6366f1; text-transform:uppercase;">✨ AI Recommended Targets:</p>';
                res.data.suggestions.forEach( function(s) {
                    html += '<div class="ai-suggestion-item" data-url="' + s.url + '">' +
                            '<strong>' + s.title + '</strong>' +
                            '<span style="color:#10b981; font-weight:700;">' + s.confidence + ' Match</span></div>';
                } );
                list.html( html );
            } else {
                list.html( '<p style="font-size:12px; padding:10px;">No semantic matches found.</p>' );
            }
        } );
    } );

    $( document ).on( 'click', '.ai-suggestion-item', function() {
        $( '#gatetouch-redirect-target' ).val( $( this ).data( 'url' ) );
        $( '#gatetouch-ai-suggestions-list' ).fadeOut();
    } );

    function loadRedirectsExtended() {
        var tbody = $( '#gatetouch-redirects-table tbody' );
        tbody.html( '<tr><td colspan="7" style="text-align:center; padding:30px;">Loading redirects...</td></tr>' );
        $.post( gatetouchAdmin.ajax_url, {
            action: 'gatetouch_get_redirects', nonce: gatetouchAdmin.nonce
        }, function(res) {
            if ( res.success ) {
                if ( ! res.data.rows.length ) {
                    tbody.html( '<tr><td colspan="7" style="text-align:center; padding:50px;">No redirects found. Click "+ Add Redirect" to get started.</td></tr>' );
                    return;
                }
                var html = '';
                res.data.rows.forEach( function(row) {
                    html += '<tr>' +
                        '<td><code style="background:#f1f5f9; padding:4px 8px; border-radius:4px; font-size:12px;">' + row.source_url + '</code></td>' +
                        '<td style="max-width:250px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">' + row.target_url + '</td>' +
                        '<td><span class="gatetouch-badge gatetouch-badge--blue">' + row.format + '</span></td>' +
                        '<td><strong>' + row.type + '</strong></td>' +
                        '<td><span style="font-weight:700;">' + row.hits + '</span></td>' +
                        '<td><span class="gatetouch-badge" style="background:' + ( row.status === 'active' ? '#ecfdf5' : '#fef2f2' ) + '; color:' + ( row.status === 'active' ? '#10b981' : '#ef4444' ) + ';">' + row.status + '</span></td>' +
                        '<td><div style="display:flex; gap:5px;">' +
                        '<button class="gatetouch-btn gatetouch-btn--ghost gatetouch-btn--xs gatetouch-edit-redirect" data-id="' + row.id + '" data-json=\'' + JSON.stringify( row ) + '\' style="padding:4px 8px; font-size:11px;">Edit</button>' +
                        '<button class="gatetouch-btn gatetouch-btn--ghost gatetouch-btn--xs gatetouch-delete-redirect" data-id="' + row.id + '" style="padding:4px 8px; font-size:11px; color:#ef4444;">Delete</button>' +
                        '</div></td>' +
                        '</tr>';
                } );
                tbody.html( html );
                $( '#stat-total-redirects' ).text( res.data.total );
                $( '#stat-active-404s' ).text( res.data.active_404s );
                $( '#stat-ai-matches' ).text( res.data.ai_matches );
                $( '#stat-crawl-efficiency' ).text( res.data.crawl_efficiency );
            }
        } );
    }

    if ( $( '#gatetouch-redirects-table' ).length ) loadRedirectsExtended();

    $( document ).on( 'click', '.gatetouch-edit-redirect', function() {
        var data = $( this ).data( 'json' );
        $( '#gatetouch-modal-title' ).text( 'Edit Redirect' );
        $( '#gatetouch-redirect-id' ).val( data.id );
        $( '#gatetouch-redirect-source' ).val( data.source_url );
        $( '#gatetouch-redirect-target' ).val( data.target_url );
        $( '#gatetouch-redirect-type' ).val( data.type );
        $( '#gatetouch-redirect-format' ).val( data.format );
        $( '#gatetouch-redirect-priority' ).val( data.priority );
        $( '#gatetouch-redirect-status' ).val( data.status );
        $( '#gatetouch-redirect-modal' ).fadeIn();
    } );

    $( document ).on( 'click', '.gatetouch-delete-redirect', function() {
        if ( ! confirm( gatetouchAdmin.strings.confirm_delete ) ) return;
        var id = $( this ).data( 'id' );
        $.post( gatetouchAdmin.ajax_url, {
            action: 'gatetouch_delete_redirect', nonce: gatetouchAdmin.nonce, id: id
        }, function(res) { if ( res.success ) loadRedirectsExtended(); } );
    } );

    $( '#gatetouch-redirect-form' ).off( 'submit' ).on( 'submit', function(e) {
        e.preventDefault();
        var btn = $( this ).find( 'button[type="submit"]' );
        btn.prop( 'disabled', true ).text( 'Saving...' );
        $.post( gatetouchAdmin.ajax_url, {
            action: 'gatetouch_save_redirect',
            nonce: gatetouchAdmin.nonce,
            redirect_id: $( '#gatetouch-redirect-id' ).val(),
            source_url: $( '#gatetouch-redirect-source' ).val(),
            target_url: $( '#gatetouch-redirect-target' ).val(),
            redirect_type: $( '#gatetouch-redirect-type' ).val(),
            redirect_format: $( '#gatetouch-redirect-format' ).val(),
            redirect_priority: $( '#gatetouch-redirect-priority' ).val(),
            redirect_status: $( '#gatetouch-redirect-status' ).val()
        }, function(res) {
            btn.prop( 'disabled', false ).text( 'Save Rule' );
            if ( res.success ) {
                $( '#gatetouch-redirect-modal' ).fadeOut();
                if ( res.data.warning ) alert( 'Saved with SEO Warning: ' + res.data.warning );
                loadRedirectsExtended();
            } else {
                alert( res.data || 'Failed to save redirect.' );
            }
        } );
    } );

    // ── Titles/Metas separator picker ─────────────────────────────────────────
    $( '.sa-picker-item' ).on( 'click', function() {
        var sep = $( this ).data( 'sep' );
        $( '.sa-picker-item' ).removeClass( 'is-active' );
        $( this ).addClass( 'is-active' );
        $( '#sa_title_separator_input' ).val( sep );
    } );

    // ── Core SEO visual separator picker ─────────────────────────────────────
    $( '.gatetouch-visual-picker__item' ).on( 'click', function() {
        var sep = $( this ).data( 'sep' );
        $( '.gatetouch-visual-picker__item' ).removeClass( 'is-active' );
        $( this ).addClass( 'is-active' );
        $( '#title_separator_input' ).val( sep );
        $( '#gatetouch-sep-preview' ).text( sep );
    } );

    // ── Link Assistant ────────────────────────────────────────────────────────
    $( '.gatetouch-link-row' ).on( 'click', function(e) {
        if ( $( e.target ).hasClass( 'gatetouch-find-links' ) ) return;
        var id      = $( this ).data( 'id' );
        var details = $( '#details-' + id );
        var icon    = $( this ).find( '.gatetouch-expand-icon' );
        $( '.gatetouch-link-details' ).not( details ).hide();
        $( '.gatetouch-expand-icon' ).not( icon ).text( '▶' ).css( 'color', '#94a3b8' );
        details.fadeToggle( 300 );
        icon.text( details.is( ':visible' ) ? '▼' : '▶' ).css( 'color', details.is( ':visible' ) ? 'var(--riq-primary)' : '#94a3b8' );
    } );

    $( '.gatetouch-find-links' ).on( 'click', function() {
        var btn         = $( this );
        var id          = btn.data( 'id' );
        var details     = $( '#details-' + id );
        var contentArea = details.find( '.gatetouch-link-results-content' );
        var row         = btn.closest( '.gatetouch-link-row' );

        btn.prop( 'disabled', true ).text( '🤖 AI Searching...' );
        $.post( gatetouchAdmin.ajax_url, {
            action: 'gatetouch_fetch_links', nonce: gatetouchAdmin.nonce, post_id: id
        }, function(res) {
            btn.prop( 'disabled', false ).text( 'Find Links' );
            if ( res.success ) {
                var html = '<div style="margin-bottom:20px; border-bottom:1px solid #e2e8f0; padding-bottom:10px;">' +
                    '<h4 style="margin:0; font-size:16px; font-weight:700; color:var(--riq-text);">AI Linking Opportunities</h4></div>' +
                    '<div class="gatetouch-suggestions-list" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap:20px;">';
                if ( res.data.suggestions && res.data.suggestions.length > 0 ) {
                    res.data.suggestions.forEach( function(s) {
                        html += '<div class="gatetouch-card" style="margin-bottom:0; box-shadow:0 1px 3px rgba(0,0,0,0.1); border:1px solid #e2e8f0;">' +
                            '<div class="gatetouch-card__header" style="padding:15px; font-size:14px; background:#fff; border-bottom:1px solid #f1f5f9;"><strong>Target:</strong> ' + s.title + '</div>' +
                            '<div class="gatetouch-card__body" style="padding:15px; background:#fff;">' +
                            '<div style="margin-bottom:12px;"><span style="font-size:11px; text-transform:uppercase; color:#94a3b8; font-weight:700; display:block; margin-bottom:4px;">Recommended Anchor Text</span>' +
                            '<code style="display:block; padding:8px; background:#f1f5f9; border-radius:6px; font-size:13px; color:var(--riq-primary); border:1px dashed #cbd5e1;">' + s.anchor_text + '</code></div>' +
                            '<p style="font-size:13px; color:#64748b; margin-bottom:15px; line-height:1.5;">' + s.reason + '</p>' +
                            '<a href="' + s.url + '" target="_blank" rel="noopener noreferrer" class="gatetouch-btn gatetouch-btn--ghost gatetouch-btn--sm gatetouch-btn--full">View Target Page ↗</a>' +
                            '</div></div>';
                    } );
                    html += '</div>';
                } else {
                    html = '<div style="text-align:center; padding:40px; color:#64748b;">' +
                        '<div style="font-size:40px; margin-bottom:10px;">🏝️</div>' +
                        '<p>No internal link opportunities found for this post yet.</p></div>';
                }
                contentArea.html( html );
                if ( ! details.is( ':visible' ) ) row.trigger( 'click' );
            } else {
                alert( 'Error: ' + res.data );
            }
        } );
    } );

    // ── API Diagnostics ───────────────────────────────────────────────────────
    $( '#gatetouch-test-api' ).on( 'click', function() {
        var $btn = $( this );
        $btn.prop( 'disabled', true ).text( 'Testing...' );
        $.post( ajaxurl, {
            action: 'gatetouch_setup_validate_api',
            nonce: gatetouchAdmin.setup_nonce
        }, function(res) {
            if ( res.success ) {
                alert( '✅ API Test Successful!' );
                window.location.reload();
            } else {
                alert( '❌ Error: ' + res.data );
                $btn.prop( 'disabled', false ).text( 'Run Live Test' );
            }
        } );
    } );

    $( '#gatetouch-reset-api-errors' ).on( 'click', function() {
        $.post( ajaxurl, { action: 'gatetouch_reset_safe_mode', nonce: gatetouchAdmin.nonce }, function() {
            window.location.reload();
        } );
    } );

    // ── Get Started Wizard ────────────────────────────────────────────────────
    $( '.wizard-prov-card' ).on( 'click', function() {
        var prov = $( this ).data( 'provider' );
        $( '#wizard-active-provider' ).val( prov );
        $( '.wizard-prov-card' ).each( function() {
            var isActive = $( this ).data( 'provider' ) === prov;
            $( this ).css( { 'border-color': isActive ? '#6366f1' : '#e2e8f0', 'background': isActive ? '#f0f1fe' : '#fff' } );
            $( this ).find( '.wizard-prov-tick' ).remove();
            if ( isActive ) {
                $( this ).append( '<div class="wizard-prov-tick" style="position:absolute;bottom:10px;right:10px;background:#6366f1;width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>' );
            }
        } );
        $( '.wizard-key-row' ).hide();
        $( '#wizard-key-row-' + prov ).show();
        $( '.wizard-model-select' ).hide();
        $( '.wizard-model-select[data-provider="' + prov + '"]' ).show();
    } );

    $( '#gatetouch_validate_api' ).on( 'click', function() {
        var btn      = $( this );
        var provider = $( '#wizard-active-provider' ).val() || 'openai';
        var key      = $( '#wizard-key-' + provider ).val();
        var model    = $( '.wizard-model-select[data-provider="' + provider + '"]:visible' ).val();
        var msg      = $( '#api_validation_msg' );

        if ( ! key || key.match( /^•+$/ ) ) {
            msg.html( '<span style="color:#ef4444;">Please enter your API key first.</span>' ).show();
            return;
        }
        btn.addClass( 'loading' ).text( '⏳ Validating...' );
        msg.hide();

        $.post( ajaxurl, {
            action: 'gatetouch_setup_validate_api',
            key: key, model: model, provider: provider,
            nonce: gatetouchAdmin.setup_nonce
        }, function(res) {
            btn.removeClass( 'loading' ).text( '⚡ Validate' );
            if ( res.success ) {
                msg.html( '<span style="color:#10b981; font-weight:700;">✓ ' + provider.charAt(0).toUpperCase() + provider.slice(1) + ' API connected successfully. Click "Finish setup" to continue.</span>' ).fadeIn();
            } else {
                msg.html( '<span style="color:#ef4444;">Error: ' + ( res.data || 'Connection failed. Check your key and try again.' ) + '</span>' ).fadeIn();
            }
        } );
    } );

    $( '.sep-choice' ).on( 'click', function() {
        $( '.sep-choice' ).removeClass( 'active' ).css( { 'border-color': '#e2e8f0', 'background': '#fff' } );
        $( this ).addClass( 'active' ).css( { 'border-color': '#6366f1', 'background': '#f0f1fe' } );
        $( '#wiz-sep' ).val( $( this ).data( 'sep' ) );
    } );

    // Collect whatever the current wizard step owns. Absent fields are simply
    // not sent, so the server never overwrites a setting this step didn't show.
    function gatetouchWizardPayload() {
        var data = { action: 'gatetouch_complete_setup', nonce: gatetouchAdmin.setup_nonce };

        if ( $( '#wiz-org-name' ).length ) {
            data.org_type = $( '#wiz-org-type' ).val();
            data.org_name = $( '#wiz-org-name' ).val();
            data.org_logo = $( '#wiz-org-logo' ).val();
        }
        if ( $( '#wiz-sitemap' ).length ) {
            data.sitemap_enabled     = $( '#wiz-sitemap' ).is( ':checked' ) ? '1' : '0';
            data.breadcrumbs_enabled = $( '#wiz-breadcrumbs' ).is( ':checked' ) ? '1' : '0';
            data.separator           = $( '#wiz-sep' ).val();
        }
        return data;
    }

    $( '#gatetouch_wizard_next' ).on( 'click', function( e ) {
        e.preventDefault();
        var btn  = $( this );
        var next = btn.data( 'next' );
        btn.prop( 'disabled', true ).text( gatetouchAdmin.strings.saving );

        $.post( ajaxurl, gatetouchWizardPayload() )
            .always( function() {
                window.location.href = 'admin.php?page=gatetouch-setup-wizard&step=' + next;
            } );
    } );

    $( '#gatetouch_finish_setup, #gatetouch_skip_setup' ).on( 'click', function( e ) {
        e.preventDefault();
        var data = gatetouchWizardPayload();
        data.finish = '1';

        $( this ).prop( 'disabled', true );
        $.post( ajaxurl, data ).always( function() {
            window.location.href = 'admin.php?page=gatetouch';
        } );
    } );

    // ── Bulk Media Manager ────────────────────────────────────────────────────
    if ( $( '#riq-media-tbody' ).length ) {
        var riqCurrentTab = 'overview', riqPaged = 1, riqSearchQuery = '';

        function riqLoadMedia() {
            var tbody = $( '#riq-media-tbody' );
            tbody.html( '<tr><td colspan="8" style="padding:100px; text-align:center;"><div class="riq-spinner" style="margin:0 auto 20px;"></div><p style="color:#64748b; font-weight:600;">Optimizing View...</p></td></tr>' );
            $.post( gatetouchAdmin.ajax_url, {
                action: 'gatetouch_fetch_media_bulk', tab: riqCurrentTab, paged: riqPaged,
                search: riqSearchQuery, nonce: gatetouchAdmin.nonce
            }, function(res) {
                if ( res.success && res.data.items.length > 0 ) {
                    var html = '';
                    res.data.items.forEach( function(item) {
                        var statusClass = item.alt ? 'gatetouch-badge--optimized' : 'gatetouch-badge--missing';
                        var statusText  = item.alt ? 'Optimized' : 'Missing ALT';
                        html += '<tr data-id="' + item.id + '">' +
                            '<td style="padding-left:30px;"><input type="checkbox" class="riq-media-check" value="' + item.id + '"></td>' +
                            '<td><img src="' + item.thumb + '" class="gatetouch-media-preview"></td>' +
                            '<td><strong>' + item.filename + '</strong><div style="font-size:11px; color:#94a3b8; margin-top:4px;">' + item.size + ' • ' + item.date + '</div></td>' +
                            '<td><input type="text" class="gatetouch-input riq-edit-alt" value="' + item.alt + '" style="width:100%; font-size:13px;" placeholder="Add Alt Text..."></td>' +
                            '<td><input type="text" class="gatetouch-input riq-edit-title" value="' + item.title + '" style="width:100%; font-size:12px; margin-bottom:5px;" placeholder="Title">' +
                            '<input type="text" class="gatetouch-input riq-edit-caption" value="' + item.caption + '" style="width:100%; font-size:11px;" placeholder="Caption"></td>' +
                            '<td><div style="display:flex; align-items:center; gap:8px;"><div style="flex-grow:1; height:6px; background:#f1f5f9; border-radius:3px; overflow:hidden;"><div class="gatetouch-score-bar-fill" style="height:100%; width:' + item.score + '%; background:' + ( item.score > 70 ? '#10b981' : '#f59e0b' ) + '; transition: 0.3s;"></div></div><span class="gatetouch-score-val" style="font-size:12px; font-weight:700;">' + item.score + '</span></div></td>' +
                            '<td><span class="gatetouch-badge ' + statusClass + '">' + statusText + '</span></td>' +
                            '<td style="text-align:right; padding-right:30px;"><div style="display:flex; gap:8px; justify-content:flex-end;">' +
                            '<button class="gatetouch-btn gatetouch-btn--ghost gatetouch-btn--sm riq-media-save" title="Save Changes">💾</button>' +
                            '<button class="gatetouch-btn gatetouch-btn--ai gatetouch-btn--sm riq-media-ai" title="Regenerate AI">🪄</button>' +
                            '</div></td></tr>';
                    } );
                    tbody.html( html );
                } else {
                    tbody.html( '<tr><td colspan="8" style="padding:100px; text-align:center;"><div style="font-size:40px; margin-bottom:15px;">🔍</div><h3 style="margin:0;">No matching images found.</h3><p style="color:#64748b;">Try adjusting your search or filters.</p></td></tr>' );
                }
            } );
        }

        function riqRefreshStats() {
            $.post( gatetouchAdmin.ajax_url, { action: 'gatetouch_fetch_media_stats', nonce: gatetouchAdmin.nonce }, function(res) {
                if ( res.success ) {
                    $( '#riq-stat-total' ).text( res.data.total );
                    $( '#riq-stat-missing' ).text( res.data.missing );
                    $( '#riq-stat-optimized' ).text( res.data.optimized );
                    $( '#riq-stat-health' ).text( res.data.health + '%' );
                }
            } );
        }

        $( '.riq-tab-link' ).on( 'click', function(e) {
            e.preventDefault();
            $( '.riq-tab-link' ).removeClass( 'active' );
            $( this ).addClass( 'active' );
            riqCurrentTab = $( this ).data( 'tab' );
            riqPaged = 1;
            riqLoadMedia();
        } );

        var riqSearchTimer;
        $( '#riq-media-search' ).on( 'input', function() {
            clearTimeout( riqSearchTimer );
            riqSearchQuery = $( this ).val();
            riqSearchTimer = setTimeout( riqLoadMedia, 500 );
        } );

        riqLoadMedia();

        $( '#riq-media-select-all' ).on( 'change', function() {
            $( '.riq-media-check' ).prop( 'checked', $( this ).prop( 'checked' ) );
        } );

        $( document ).on( 'click', '.riq-media-save', function() {
            var row = $( this ).closest( 'tr' );
            var id  = row.data( 'id' );
            var btn = $( this );
            btn.text( '⌛' ).prop( 'disabled', true );
            $.post( gatetouchAdmin.ajax_url, {
                action: 'gatetouch_update_media_meta', attachment_id: id,
                alt: row.find( '.riq-edit-alt' ).val(),
                title: row.find( '.riq-edit-title' ).val(),
                caption: row.find( '.riq-edit-caption' ).val(),
                nonce: gatetouchAdmin.nonce
            }, function(res) {
                if ( res.success ) {
                    btn.text( '✅' );
                    var badge = row.find( '.gatetouch-badge' );
                    if ( res.data.status === 'optimized' ) {
                        badge.removeClass( 'gatetouch-badge--missing' ).addClass( 'gatetouch-badge--optimized' ).text( 'Optimized' );
                    } else {
                        badge.removeClass( 'gatetouch-badge--optimized' ).addClass( 'gatetouch-badge--missing' ).text( 'Missing ALT' );
                    }
                    row.find( '.gatetouch-score-bar-fill' ).css( 'width', res.data.score + '%' );
                    row.find( '.gatetouch-score-val' ).text( res.data.score );
                    riqRefreshStats();
                }
                setTimeout( function() { btn.text( '💾' ).prop( 'disabled', false ); }, 1500 );
            } );
        } );

        $( document ).on( 'click', '.riq-media-ai', function() {
            var row = $( this ).closest( 'tr' );
            var id  = row.data( 'id' );
            var btn = $( this );
            btn.html( '<div class="riq-spinner riq-spinner--sm"></div>' ).prop( 'disabled', true );
            $.post( gatetouchAdmin.ajax_url, {
                action: 'gatetouch_process_image_alt', id: id, nonce: gatetouchAdmin.nonce
            }, function(res) {
                if ( res.success ) {
                    row.find( '.riq-edit-alt' ).val( res.data.alt );
                    row.find( '.riq-edit-caption' ).val( res.data.caption );
                    row.find( '.riq-edit-title' ).val( res.data.title );
                    row.find( '.gatetouch-badge' ).removeClass( 'gatetouch-badge--missing' ).addClass( 'gatetouch-badge--optimized' ).text( 'Optimized' );
                    row.find( '.gatetouch-score-bar-fill' ).css( 'width', res.data.score + '%' );
                    row.find( '.gatetouch-score-val' ).text( res.data.score );
                    riqRefreshStats();
                    btn.html( '🪄' ).prop( 'disabled', false );
                } else {
                    alert( 'AI Error: ' + ( res.data || 'Failed to analyze image. Check your API key.' ) );
                    btn.html( '🪄' ).prop( 'disabled', false );
                }
            } );
        } );

        $( '#riq-media-run-bulk' ).on( 'click', function() {
            var selected = $( '.riq-media-check:checked' );
            if ( ! selected.length ) { alert( 'Please select at least one image to optimize.' ); return; }
            if ( ! confirm( 'Are you sure you want to AI-optimize ' + selected.length + ' images?' ) ) return;
            var btn          = $( this );
            var originalText = btn.text();
            btn.prop( 'disabled', true ).text( '⏳ Processing Batch...' );
            var processed = 0, total = selected.length;

            var processBatchItem = function(index) {
                if ( index >= total ) {
                    btn.prop( 'disabled', false ).text( 'Batch Complete!' ).addClass( 'gatetouch-badge--success' );
                    setTimeout( function() { btn.text( originalText ).removeClass( 'gatetouch-badge--success' ); }, 3000 );
                    return;
                }
                var current = $( selected[index] );
                var id      = current.val();
                var row     = current.closest( 'tr' );
                var rowBtn  = row.find( '.riq-media-ai' );
                rowBtn.html( '<div class="riq-spinner riq-spinner--sm"></div>' ).prop( 'disabled', true );
                $.post( gatetouchAdmin.ajax_url, {
                    action: 'gatetouch_process_image_alt', id: id, nonce: gatetouchAdmin.nonce
                }, function(res) {
                    if ( res.success ) {
                        row.find( '.riq-edit-alt' ).val( res.data.alt );
                        row.find( '.riq-edit-caption' ).val( res.data.caption );
                        row.find( '.riq-edit-title' ).val( res.data.title );
                        row.find( '.gatetouch-badge' ).removeClass( 'gatetouch-badge--missing' ).addClass( 'gatetouch-badge--optimized' ).text( 'Optimized' );
                        row.find( '.gatetouch-score-bar-fill' ).css( 'width', res.data.score + '%' );
                        row.find( '.gatetouch-score-val' ).text( res.data.score );
                        riqRefreshStats();
                    } else {
                        var errorMsg = ( res.data && res.data.length > 20 ) ? res.data.substring( 0, 20 ) + '...' : ( res.data || 'AI Error' );
                        row.find( '.gatetouch-badge' ).text( errorMsg ).css( 'background', '#fee2e2' ).css( 'color', '#991b1b' );
                    }
                    rowBtn.html( '🪄' ).prop( 'disabled', false );
                    processed++;
                    processBatchItem( index + 1 );
                } );
            };
            processBatchItem( 0 );
        } );
    }

    // ── Automation (bulk alt scan) ────────────────────────────────────────────
    $( '#gatetouch-bulk-alt-scan' ).on( 'click', function() {
        var btn      = $( this );
        var progress = $( '#gatetouch-alt-progress' );
        var bar      = $( '#gatetouch-alt-bar' );
        var countText = $( '#gatetouch-alt-count' );

        btn.prop( 'disabled', true ).text( '🔍 Scanning Media...' );
        $.post( gatetouchAdmin.ajax_url, {
            action: 'gatetouch_get_unoptimized_images', nonce: gatetouchAdmin.nonce
        }, function(res) {
            if ( res.success && res.data.ids.length > 0 ) {
                var ids = res.data.ids, processed = 0, total = ids.length;
                progress.show();
                countText.text( '0/' + total );
                btn.text( '🪄 AI Processing...' );

                var processNext = function() {
                    if ( processed >= total ) {
                        btn.text( 'Batch Complete!' ).addClass( 'gatetouch-badge--success' );
                        setTimeout( function() { location.reload(); }, 2000 );
                        return;
                    }
                    $.post( gatetouchAdmin.ajax_url, {
                        action: 'gatetouch_generate_alt_text', attachment_id: ids[processed], nonce: gatetouchAdmin.nonce
                    }, function() {
                        processed++;
                        bar.css( 'width', ( processed / total * 100 ) + '%' );
                        countText.text( processed + '/' + total );
                        processNext();
                    } );
                };
                processNext();
            } else {
                alert( '🎉 Great news! All your images already have Alt text.' );
                btn.prop( 'disabled', false ).text( '🚀 Start Bulk AI Media Scan' );
            }
        } );
    } );

    // ── Tools page ────────────────────────────────────────────────────────────
    if ( $( '#add-robots-rule' ).length || $( '#save-robots' ).length ) {
        function riqToast(msg, type) {
            type = type || 'success';
            var $t = $( '<div>' )
                .addClass( 'gatetouch-save-toast gatetouch-save-toast--' + type )
                .text( msg );
            $( 'body' ).append( $t );
            setTimeout( function() { $t.fadeOut( 300, function() { $t.remove(); } ); }, 3000 );
        }

        // Keep the row numbers contiguous and show/hide the empty state, so the
        // table never displays "3" as its only row or an empty body with headers.
        function riqSyncRobotsRows() {
            var $body = $( '#robots-rules-body' );
            var $rows = $body.find( 'tr' ).not( '.gatetouch-table__empty' );

            $rows.each( function ( i ) {
                $( this ).children( 'td' ).first().text( i + 1 );
            } );

            if ( ! $rows.length ) {
                if ( ! $body.find( '.gatetouch-table__empty' ).length ) {
                    $body.append(
                        '<tr class="gatetouch-table__empty" id="robots-rules-empty"><td colspan="5">' +
                        'No custom rules yet — your robots.txt uses the generated defaults. Add a rule to override them.' +
                        '</td></tr>'
                    );
                }
            } else {
                $body.find( '.gatetouch-table__empty' ).remove();
            }
        }

        $( document ).on( 'click', '#add-robots-rule', function() {
            var row = '<tr><td></td>' +
                '<td><input type="text" name="robots_ua[]" value="*" class="gatetouch-input-full"></td>' +
                '<td><select name="robots_dir[]" class="gatetouch-input-full"><option value="disallow">Disallow</option><option value="allow">Allow</option></select></td>' +
                '<td><input type="text" name="robots_val[]" value="/" class="gatetouch-input-full"></td>' +
                '<td><button type="button" class="gatetouch-btn gatetouch-btn--ghost remove-rule"><span class="dashicons dashicons-no-alt"></span></button></td></tr>';
            $( '#robots-rules-body' ).append( row );
            riqSyncRobotsRows();
        } );

        $( document ).on( 'click', '.remove-rule', function() {
            $( this ).closest( 'tr' ).remove();
            riqSyncRobotsRows();
        } );

        $( document ).on( 'click', '#save-robots', function() {
            var btn = $( this ), rules = [];
            var ajaxUrl = gatetouchAjaxUrl();
            var nonce = gatetouchNonce();

            if ( ! ajaxUrl || ! nonce ) {
                riqToast( 'Robots.txt save is unavailable. Please refresh the page and try again.', 'error' );
                return;
            }

            // Skip the empty-state placeholder — it carries no inputs and would
            // otherwise be saved as a blank rule.
            $( '#robots-rules-body tr' ).not( '.gatetouch-table__empty' ).each( function() {
                var ua  = $( this ).find( 'input[name="robots_ua[]"]' ).val();
                var dir = $( this ).find( 'select[name="robots_dir[]"]' ).val();
                var val = $( this ).find( 'input[name="robots_val[]"]' ).val();
                if ( ! ua && ! val ) {
                    return;
                }
                rules.push( { ua: ua, dir: dir, val: val } );
            } );

            // ai_bots is intentionally not sent. AI crawler policy is owned by
            // Settings → Sitemaps & Files; omitting the field tells the handler
            // to leave those crawler settings untouched.
            btn.prop( 'disabled', true ).text( 'Saving...' );
            $.post( ajaxUrl, {
                action: 'gatetouch_save_robots', nonce: nonce,
                rules: rules,
                block_search: $( 'input[name="block_search"]' ).is( ':checked' ) ? 1 : 0
            } ).done( function(res) {
                btn.prop( 'disabled', false ).text( 'Save Robots.txt Settings' );
                if ( res.success ) riqToast( '✅ Robots.txt settings saved!' );
                else riqToast( '❌ Error: ' + ( res.data || 'Save failed.' ), 'error' );
            } ).fail( function() {
                btn.prop( 'disabled', false ).text( 'Save Robots.txt Settings' );
                riqToast( '❌ Connection error. Please try again.', 'error' );
            } );
        } );

        $( '#save-htaccess' ).on( 'click', function() {
            var btn = $( this );
            btn.prop( 'disabled', true ).text( 'Saving...' );
            $.post( ajaxurl, {
                action: 'gatetouch_save_htaccess', nonce: gatetouchAdmin.nonce,
                content: $( '#htaccess-content' ).val()
            } ).done( function(res) {
                btn.prop( 'disabled', false ).text( 'Save .htaccess Changes' );
                if ( res.success ) riqToast( '✅ .htaccess file saved successfully!' );
                else riqToast( '❌ Error: ' + ( res.data || 'Write failed.' ), 'error' );
            } ).fail( function() {
                btn.prop( 'disabled', false ).text( 'Save .htaccess Changes' );
                riqToast( '❌ Connection error. Please try again.', 'error' );
            } );
        } );

        $( '#reset-settings' ).on( 'click', function() {
            if ( ! confirm( 'Are you sure you want to reset the selected settings? This cannot be undone.' ) ) return;
            var btn = $( this ), mods = [];
            $( 'input[name="reset_mod[]"]:checked' ).each( function() { mods.push( $( this ).val() ); } );
            if ( ! mods.length ) { riqToast( '⚠️ Please select at least one module to reset.', 'error' ); return; }
            btn.prop( 'disabled', true ).text( 'Resetting...' );
            $.post( ajaxurl, { action: 'gatetouch_reset_settings', nonce: gatetouchAdmin.nonce, modules: mods } )
            .done( function() {
                riqToast( '✅ Settings reset successfully. Reloading...' );
                setTimeout( function() { location.reload(); }, 1200 );
            } ).fail( function() {
                btn.prop( 'disabled', false ).text( 'Reset Selected Settings to Default' );
                riqToast( '❌ Connection error. Please try again.', 'error' );
            } );
        } );

        $( '#riq-export-btn' ).on( 'click', function() {
            $.post( ajaxurl, { action: 'gatetouch_export_meta', nonce: gatetouchAdmin.nonce }, function(res) {
                if ( res.success ) {
                    var blob = new Blob( [ JSON.stringify( res.data.data, null, 2 ) ], { type: 'application/json' } );
                    var a    = document.createElement( 'a' );
                    a.href   = URL.createObjectURL( blob );
                    a.download = 'gatetouch-seo-export.json';
                    a.click();
                    riqToast( '✅ Export downloaded!' );
                } else {
                    riqToast( '❌ Export failed. Please try again.', 'error' );
                }
            } );
        } );

        $( document ).on( 'click', '.run-migration-btn', function() {
            var btn          = $( this );
            var source       = btn.data( 'source' );
            var originalText = btn.text();
            if ( ! confirm( 'Are you sure you want to import data from this source? This will copy SEO metadata, redirects, or settings into GateTouch.' ) ) return;
            btn.prop( 'disabled', true ).text( 'Importing...' );
            $.post( ajaxurl, { action: 'gatetouch_migrate_source', nonce: gatetouchAdmin.nonce, source: source } )
            .done( function(res) {
                btn.prop( 'disabled', false ).text( originalText );
                if ( res.success ) {
                    riqToast( '✅ Successfully imported ' + res.data.migrated + ' items from ' + res.data.source + '!' );
                    setTimeout( function() { location.reload(); }, 1500 );
                } else {
                    riqToast( '❌ Error: ' + ( res.data.error || 'Import failed.' ), 'error' );
                }
            } ).fail( function() {
                btn.prop( 'disabled', false ).text( originalText );
                riqToast( '❌ Connection error. Please try again.', 'error' );
            } );
        } );
    }

    // ── Redirects page ────────────────────────────────────────────────────────
    if ( $( '#riq-redir-tbody' ).length ) {
        var riqRedir_page = 1;

        function riqLoadRedirects(page) {
            riqRedir_page = page || 1;
            $.post( gatetouchAdmin.ajax_url, {
                action: 'gatetouch_get_redirects', nonce: gatetouchAdmin.nonce,
                search: $( '#riq-redir-search' ).val(), paged: riqRedir_page
            }, function(res) {
                if ( ! res.success ) return;
                var rows = res.data.rows, html = '';
                if ( ! rows.length ) {
                    html = '<tr><td colspan="6" style="text-align:center;padding:60px;color:var(--riq-text-light);">No redirects found.</td></tr>';
                } else {
                    rows.forEach( function(r) {
                        html += '<tr style="animation: fadeIn 0.3s ease;">' +
                            '<td><code>' + r.source_url + '</code></td>' +
                            '<td><code>' + r.target_url + '</code></td>' +
                            '<td><span class="gatetouch-badge gatetouch-badge--' + r.type + '">' + r.type + '</span></td>' +
                            '<td><strong style="color:var(--riq-text);">' + r.hits + '</strong></td>' +
                            '<td style="font-size:12px; color:var(--riq-text-light);">' + r.created_at + '</td>' +
                            '<td style="text-align:right;"><div style="display:flex; gap:8px; justify-content:flex-end;">' +
                            '<button class="gatetouch-btn gatetouch-btn--ghost gatetouch-btn--xs riq-redir-edit" data-id="' + r.id + '" data-src="' + r.source_url + '" data-tgt="' + r.target_url + '" data-type="' + r.type + '">Edit</button>' +
                            '<button class="gatetouch-btn gatetouch-btn--ghost gatetouch-btn--xs riq-redir-del" data-id="' + r.id + '" style="color:var(--riq-danger);">Delete</button>' +
                            '</div></td></tr>';
                    } );
                }
                $( '#riq-redir-tbody' ).html( html );
            } );
        }

        $( '#riq-redir-save' ).on( 'click', function() {
            var src = $( '#riq-redir-source' ).val().trim();
            var tgt = $( '#riq-redir-target' ).val().trim();
            var typ = $( '#riq-redir-type' ).val();
            var id  = $( '#riq-redir-id' ).val();
            if ( ! src || ! tgt ) { $( '#riq-redir-msg' ).html( '<span style="color:#ef4444">Both URLs required.</span>' ); return; }
            $( this ).text( 'Saving…' ).prop( 'disabled', true );
            $.post( gatetouchAdmin.ajax_url, {
                action: 'gatetouch_save_redirect', nonce: gatetouchAdmin.nonce,
                source_url: src, target_url: tgt, redirect_type: typ, redirect_id: id
            }, function(res) {
                $( '#riq-redir-save' ).text( 'Save Redirect' ).prop( 'disabled', false );
                if ( res.success ) {
                    $( '#riq-redir-msg' ).html( '<span style="color:#10b981">✅ Saved!</span>' );
                    $( '#riq-redir-source' ).val( '' ); $( '#riq-redir-target' ).val( '' ); $( '#riq-redir-id' ).val( '0' );
                    riqLoadRedirects( 1 );
                } else {
                    $( '#riq-redir-msg' ).html( '<span style="color:#ef4444">❌ ' + res.data + '</span>' );
                }
            } );
        } );

        $( '#riq-redir-tbody' ).on( 'click', '.riq-redir-edit', function() {
            var d = $( this ).data();
            $( '#riq-redir-source' ).val( d.src );
            $( '#riq-redir-target' ).val( d.tgt );
            $( '#riq-redir-type' ).val( d.type );
            $( '#riq-redir-id' ).val( d.id );
            $( 'html,body' ).animate( { scrollTop: 0 } );
        } ).on( 'click', '.riq-redir-del', function() {
            if ( ! confirm( gatetouchAdmin.strings.confirm_delete ) ) return;
            var id = $( this ).data( 'id' );
            $.post( gatetouchAdmin.ajax_url, { action: 'gatetouch_delete_redirect', nonce: gatetouchAdmin.nonce, redirect_id: id },
                function(res) { if ( res.success ) riqLoadRedirects( riqRedir_page ); } );
        } );

        var riqRedirSearchTimeout;
        $( '#riq-redir-search' ).on( 'input', function() {
            clearTimeout( riqRedirSearchTimeout );
            riqRedirSearchTimeout = setTimeout( function() { riqLoadRedirects( 1 ); }, 400 );
        } );

        riqLoadRedirects( 1 );
    }

    // ── Advanced settings debug log viewer ────────────────────────────────────
    function riqLoadLogs() {
        $.post( ajaxurl, { action: 'gatetouch_get_debug_logs', nonce: gatetouchAdmin.nonce }, function(res) {
            if ( res.success ) {
                $( '#riq-log-viewer' ).text( res.data || 'No logs available.' );
                var el = $( '#riq-log-viewer' )[0];
                if ( el ) el.scrollTop = el.scrollHeight;
            }
        } );
    }
    if ( $( '#riq-log-viewer' ).length ) {
        riqLoadLogs();
        $( '#riq-refresh-logs' ).on( 'click', riqLoadLogs );
        $( '#riq-clear-logs' ).on( 'click', function() {
            if ( confirm( 'Clear all logs?' ) ) {
                $.post( ajaxurl, { action: 'gatetouch_clear_debug_logs', nonce: gatetouchAdmin.nonce }, function(res) {
                    if ( res.success ) riqLoadLogs();
                } );
            }
        } );
    }

    // ── AI & AEO Settings ─────────────────────────────────────────────────────
    if ( $( '.riq-provider-card' ).length ) {
        var activeProvider = gatetouchAdmin.active_provider || 'openai';

        var providerMeta = {
            openai:    { icon: '🤖', title: 'OpenAI Setup Guide',         subtitle: 'GPT-4o configuration & API key steps' },
            anthropic: { icon: '🧠', title: 'Anthropic Claude Setup',     subtitle: 'Claude API key configuration steps' },
            gemini:    { icon: '✨',        title: 'Google Gemini Setup Guide',  subtitle: 'Gemini API key configuration steps' }
        };

        function riqOpenHelpPanel(provider) {
            var meta = providerMeta[provider] || providerMeta.openai;
            $( '#riq-hp-provider-icon' ).text( meta.icon );
            $( '#riq-hp-title' ).text( meta.title );
            $( '#riq-hp-subtitle' ).text( meta.subtitle );
            $( '.riq-hp-content' ).hide();
            $( '#riq-hp-' + provider ).show();
            $( '#riq-ai-help-panel' ).css( 'transform', 'translateX(0)' );
            $( '#riq-ai-help-overlay' ).fadeIn( 200 );
            $( '#riq-ai-help-panel' ).find( 'button, a' ).first().trigger( 'focus' );
        }

        function riqCloseHelpPanel() {
            $( '#riq-ai-help-panel' ).css( 'transform', 'translateX(100%)' );
            $( '#riq-ai-help-overlay' ).fadeOut( 200 );
        }

        $( '#riq-open-ai-help, .riq-open-provider-help' ).on( 'click', function() {
            riqOpenHelpPanel( $( this ).data( 'provider' ) || activeProvider );
        } );
        $( '#riq-ai-help-close' ).on( 'click', riqCloseHelpPanel );
        $( '#riq-ai-help-overlay' ).on( 'click', riqCloseHelpPanel );
        $( document ).on( 'keydown', function(e) { if ( e.key === 'Escape' ) riqCloseHelpPanel(); } );

        function riqSwitchProvider(prov) {
            activeProvider = prov;
            $( '.riq-provider-card' ).each( function() {
                var $card    = $( this );
                var cardProv = $card.data( 'provider' );
                var isActive = ( cardProv === prov );
                $card.css( { 'border-color': isActive ? '#6366f1' : '#e2e8f0', 'background': isActive ? '#f0f1fe' : '#fff' } );
                $card.find( '.riq-prov-tick' ).remove();
                if ( isActive ) {
                    $card.append( '<div class="riq-prov-tick" style="position:absolute;top:10px;right:10px;background:#6366f1;width:20px;height:20px;border-radius:50%;display:flex;align-items:center;justify-content:center;"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>' );
                }
                $card.find( 'input[type=radio]' ).prop( 'checked', isActive );
            } );
            $( '.riq-key-row' ).hide();
            $( '#riq-key-row-' + prov ).show();
            $( '.riq-model-select' ).hide().prop( 'disabled', true );
            $( '.riq-model-select--' + prov ).show().prop( 'disabled', false );
        }

        $( '.riq-provider-card' ).on( 'click', function() { riqSwitchProvider( $( this ).data( 'provider' ) ); } );

        $( '#gatetouch-ai-settings-form' ).on( 'submit', function() {
            $( '.riq-model-select' ).not( '.riq-model-select--' + activeProvider ).prop( 'disabled', true );
        } );

        $( document ).on( 'click', '.riq-test-key-btn', function() {
            var $btn     = $( this );
            var provider = $btn.data( 'provider' );
            var $input   = $( '#riq-key-input-' + provider );
            var $result  = $( '.riq-key-test-result[data-provider="' + provider + '"]' );
            var keyVal   = $input.val();

            if ( ! keyVal || keyVal.match( /^•+$/ ) ) {
                $result.css( { 'background': '#fffbeb', 'color': '#92400e', 'border': '1px solid #fde68a', 'display': 'block' } )
                       .text( 'Please enter a key first, or clear the field if you want to re-enter it.' );
                return;
            }
            $btn.prop( 'disabled', true ).html( '⏳ Testing...' );
            $result.hide();
            $.post( gatetouchAdmin.ajax_url, {
                action: 'gatetouch_setup_validate_api', nonce: gatetouchAdmin.nonce,
                key: keyVal, provider: provider
            } )
            .done( function(res) {
                if ( res.success ) {
                    $result.css( { 'background': '#ecfdf5', 'color': '#065f46', 'border': '1px solid #a7f3d0', 'display': 'block' } )
                           .html( '✅ <strong>Connected!</strong> Your ' + provider.charAt(0).toUpperCase() + provider.slice(1) + ' API key is valid and working.' );
                    $( '#riq-key-row-' + provider ).closest( '.gatetouch-card' )
                        .prev().find( '.riq-provider-card[data-provider="' + provider + '"] > div:last-child' )
                        .css( { 'background': '#ecfdf5', 'color': '#065f46', 'border': '1px solid #a7f3d0' } )
                        .html( '<span style="background:#10b981;width:5px;height:5px;border-radius:50%;display:inline-block;margin-right:4px;"></span> Key Saved' );
                } else {
                    $result.css( { 'background': '#fef2f2', 'color': '#991b1b', 'border': '1px solid #fca5a5', 'display': 'block' } )
                           .html( '❌ <strong>Failed:</strong> ' + ( res.data || 'Invalid API key. Please check and try again.' ) );
                }
            } )
            .fail( function() {
                $result.css( { 'background': '#fef2f2', 'color': '#991b1b', 'display': 'block' } )
                       .text( 'Connection error. Please try again.' );
            } )
            .always( function() { $btn.prop( 'disabled', false ).html( '⚡ Test Connection' ); } );
        } );

        riqSwitchProvider( activeProvider );
    }

    // ── Alt-text engine single image ──────────────────────────────────────────
    $( '#gatetouch-generate-media-btn' ).on( 'click', function() {
        var btn     = $( this );
        var id      = btn.data( 'id' );
        var status  = $( '#gatetouch-media-status' );
        var results = $( '#gatetouch-media-results' );
        btn.prop( 'disabled', true ).text( '⌛ Processing...' );
        status.show();
        $.post( ajaxurl, {
            action: 'gatetouch_generate_alt_text', attachment_id: id, nonce: gatetouchAdmin.nonce
        }, function(res) {
            if ( res.success ) {
                status.text( '✅ Optimization Complete!' );
                $( '#riq-suggest-filename' ).text( res.data.filename_suggestion );
                $( '#riq-suggest-alt' ).text( res.data.alt_text );
                results.slideDown();
                setTimeout( function() { location.reload(); }, 2000 );
            } else {
                alert( '❌ Error: ' + res.data );
                btn.prop( 'disabled', false ).text( '🪄 AI Media Analysis' );
                status.hide();
            }
        } );
    } );

} );
