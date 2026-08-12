/* global gatetouchAdmin, jQuery */
jQuery( function ( $ ) {
    'use strict';

    const { ajax_url, nonce } = gatetouchAdmin;
    let currentPage = 1;
    let isRunning   = false;
    let totalToGen  = 0;
    let genDone     = 0;

    // ── Load Posts ───────────────────────────────────────────────
    function loadPosts( page ) {
        currentPage = page || 1;
        $( '#riq-bulk-empty' ).hide();
        $( '#riq-bulk-table' ).hide();
        $( '#riq-bulk-tbody' ).html(
            '<tr><td colspan="7" style="text-align:center;padding:20px;">Loading…</td></tr>'
        );
        $( '#riq-bulk-table' ).show();

        $.post( ajax_url, {
            action:    'gatetouch_bulk_get_posts',
            nonce,
            post_type: $( '#riq-bulk-type' ).val(),
            filter:    $( '#riq-bulk-filter' ).val(),
            search:    $( '#riq-bulk-search' ).val(),
            paged:     currentPage,
        } ).done( function ( res ) {
            if ( ! res.success ) return;
            const { posts, total, total_pages, current } = res.data;

            if ( ! posts.length ) {
                $( '#riq-bulk-tbody' ).html(
                    '<tr><td colspan="7" style="text-align:center;padding:20px;color:#9ca3af;">No posts found.</td></tr>'
                );
                renderPagination( total_pages, current );
                $( '#riq-bulk-count-label' ).text( '0 items found' );
                return;
            }

            let html = '';
            posts.forEach( post => {
                const statusBadge = post.has_meta
                    ? '<span class="gatetouch-badge gatetouch-badge--green"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Optimized</span>'
                    : '<span class="gatetouch-badge gatetouch-badge--amber"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg> Missing</span>';

                html += `<tr id="riq-row-${post.id}">
                    <td style="vertical-align: top; padding-top: 24px;"><input type="checkbox" class="riq-bulk-cb" value="${post.id}" /></td>
                    <td style="vertical-align: top;">
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            <a href="${post.edit_url}" target="_blank" rel="noopener noreferrer" style="font-weight:700; color:var(--riq-text); text-decoration:none;">${post.title}</a>
                            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                <span class="gatetouch-badge" style="background:#f1f5f9; color:#64748b; text-transform:none;">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                    ${post.word_count} words
                                </span>
                                <span class="gatetouch-badge" style="background:#f1f5f9; color:#64748b; text-transform:none;">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    ${post.modified}
                                </span>
                            </div>
                        </div>
                    </td>
                    <td style="vertical-align: top;">
                        <input type="text" class="gatetouch-bulk-inline-input riq-inline-input"
                               data-id="${post.id}" data-field="meta_title"
                               value="${post.meta_title}" placeholder="No meta title…" />
                        <div class="riq-char-bar" data-ideal-min="50" data-ideal-max="60" data-max="70">
                            <div style="width:0%;"></div>
                        </div>
                    </td>
                    <td style="vertical-align: top;">
                        <textarea class="gatetouch-bulk-inline-input riq-inline-input"
                                  data-id="${post.id}" data-field="meta_description"
                                  placeholder="No meta description…">${post.meta_description}</textarea>
                    </td>
                    <td style="vertical-align: top;">
                        <input type="text" class="gatetouch-bulk-inline-input riq-inline-input"
                               data-id="${post.id}" data-field="focus_keyword"
                               value="${post.focus_keyword}" placeholder="keyword…" />
                    </td>
                    <td style="vertical-align: top; padding-top: 24px;">${statusBadge}</td>
                    <td style="vertical-align: top; padding-top: 20px; padding-right: 16px;">
                        <div style="display:flex; flex-direction:column; gap:8px; align-items: flex-end;">
                            <button class="gatetouch-btn gatetouch-btn--ai riq-btn--sm riq-gen-one" data-id="${post.id}" style="width: 80px; height: 32px; justify-content:center; font-size:12px; font-weight:600; display:inline-flex; align-items:center; gap:4px; box-sizing: border-box; padding: 0;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                                AI
                            </button>
                            <a href="${post.edit_url}" target="_blank" rel="noopener noreferrer"
                               class="gatetouch-btn gatetouch-btn--secondary riq-btn--sm" style="width: 80px; height: 32px; justify-content:center; font-size:12px; font-weight:600; display:inline-flex; align-items:center; box-sizing: border-box; padding: 0; text-decoration: none;">Edit</a>
                        </div>
                    </td>
                </tr>`;
            } );

            $( '#riq-bulk-tbody' ).html( html );
            updateAllCharbars();
            renderPagination( total_pages, current );
            $( '#riq-bulk-count-label' ).text( total + ( total === 1 ? ' item found' : ' items found' ) );
        } );
    }

    function renderPagination( total_pages, current ) {
        let html = '';
        for ( let i = 1; i <= total_pages; i++ ) {
            html += `<button type="button" class="riq-page-btn ${i === current ? 'active' : ''}" data-page="${i}">${i}</button>`;
        }
        $( '#riq-bulk-pagination' ).html( html );
    }

    function updateAllCharbars() {
        $( '.riq-char-bar' ).each( function () {
            const $bar = $( this );
            const $input = $bar.prev( 'input, textarea' );
            if ( ! $input.length ) return;
            updateCharbar( $bar, $input.val().length );
        } );
    }

    function updateCharbar( $bar, len ) {
        const min = parseInt( $bar.data( 'ideal-min' ) ) || 50;
        const max = parseInt( $bar.data( 'ideal-max' ) ) || 60;
        const hm  = parseInt( $bar.data( 'max' ) )       || 70;
        const pct = Math.min( ( len / hm ) * 100, 100 );
        let color = '#10b981';
        if ( len === 0 )        color = '#d1d5db';
        else if ( len < min )   color = '#f59e0b';
        else if ( len > max )   color = '#ef4444';
        $bar.find( 'div' ).css( { width: pct + '%', background: color } );
    }

    // ── Events ───────────────────────────────────────────────────
    $( '#riq-bulk-load' ).on( 'click', () => loadPosts( 1 ) );

    $( '#riq-bulk-pagination' ).on( 'click', '.riq-page-btn', function () {
        loadPosts( parseInt( $( this ).data( 'page' ) ) );
    } );

    // Inline save with debounce
    let saveTimeout;
    $( '#riq-bulk-tbody' ).on( 'input', '.riq-inline-input', function () {
        const $this = $( this );
        clearTimeout( saveTimeout );

        // Update charbar
        const $nextBar = $this.next( '.riq-char-bar' );
        if ( $nextBar.length ) updateCharbar( $nextBar, $this.val().length );

        saveTimeout = setTimeout( () => {
            if ( typeof gatetouchFlash === 'function' ) gatetouchFlash( '⏳ Saving...', 'info' );

            $.post( ajax_url, {
                action: 'gatetouch_bulk_save_inline',
                nonce,
                post_id: $this.data( 'id' ),
                field:   $this.data( 'field' ),
                value:   $this.val(),
            } ).done( res => {
                if ( res.success ) {
                    $this.addClass( 'riq-saved' );
                    if ( typeof gatetouchFlash === 'function' ) gatetouchFlash( '✅ Saved', 'success' );
                    setTimeout( () => $this.removeClass( 'riq-saved' ), 1500 );
                }
            } );
        }, 800 );
    } );

    // Generate one
    $( '#riq-bulk-tbody' ).on( 'click', '.riq-gen-one', function () {
        if ( String(gatetouchAdmin.has_api_key) === '0' ) {
            if ( typeof gatetouchFlash === 'function' ) {
                gatetouchFlash('⚠️ AI service is not connected. Please configure API settings first.', 'warn');
            } else {
                alert( 'AI service is not connected. Please configure API settings first.' );
            }
            return;
        }

        const $btn    = $( this );
        const post_id = $btn.data( 'id' );
        const $row    = $( `#riq-row-${post_id}` );

        // Detect existing content
        const currentTitle = $row.find( '[data-field="meta_title"]' ).val().trim();
        const currentDesc  = $row.find( '[data-field="meta_description"]' ).val().trim();

        if ( currentTitle || currentDesc ) {
            // Show choice modal
            $( '#riq-ai-modal' ).fadeIn( 200 ).data( 'target-post', post_id );
            return;
        }

        executeGenerate( post_id, 'generate' );
    } );

    // Modal Events
    $( '.riq-modal-close' ).on( 'click', () => $( '#riq-ai-modal' ).fadeOut( 200 ) );

    $( '.gatetouch-choice-card' ).on( 'click', function() {
        const mode    = $( this ).data( 'mode' );
        const post_id = $( '#riq-ai-modal' ).fadeOut( 200 ).data( 'target-post' );
        if ( post_id ) executeGenerate( post_id, mode );
    } );

    function executeGenerate( post_id, mode ) {
        const $row = $( `#riq-row-${post_id}` );
        const $btn = $row.find( '.riq-gen-one' );

        const originalHtml = $btn.html();
        $btn.html( '<span class="riq-spinner"></span>' ).prop( 'disabled', true );

        const existing = {
            meta_title: $row.find( '[data-field="meta_title"]' ).val(),
            meta_description: $row.find( '[data-field="meta_description"]' ).val(),
        };

        $.post( ajax_url, {
            action: 'gatetouch_bulk_generate_one',
            nonce,
            post_id,
            mode,
            existing
        } )
        .done( function ( res ) {
            $btn.html( '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>' ).prop( 'disabled', false );
            if ( res.success ) {
                const d = res.data;
                const $titleInput = $row.find( '[data-field="meta_title"]' );
                const $descInput  = $row.find( '[data-field="meta_description"]' );

                $titleInput.val( d.meta_title );
                $descInput.val( d.meta_description );
                $row.find( '[data-field="focus_keyword"]' ).val( d.focus_keyword );

                // Update Previews
                $titleInput.next( '.riq-field-preview' ).text( d.meta_title_parsed );
                $descInput.next( '.riq-field-preview' ).text( d.meta_description_parsed );

                $row.find( '.gatetouch-badge' )
                    .removeClass( 'gatetouch-badge--amber' )
                    .addClass( 'gatetouch-badge--green' )
                    .html( mode === 'improve' ? '✅ Improved' : '✅ Optimized' );

                updateAllCharbars();
                if ( typeof gatetouchFlash === 'function' ) {
                    gatetouchFlash( mode === 'improve' ? '✨ Meta improved successfully!' : '✅ Meta generated successfully!', 'success' );
                }
                setTimeout( () => $btn.html( originalHtml ), 2000 );
            } else {
                if ( typeof gatetouchFlash === 'function' ) gatetouchFlash( '❌ ' + res.data, 'error' );
                $btn.html( originalHtml );
            }
        } )
        .fail( () => {
            $btn.html( originalHtml ).prop( 'disabled', false );
            if ( typeof gatetouchFlash === 'function' ) gatetouchFlash( '❌ Connection error', 'error' );
        } );
    }

    // Select all checkbox
    $( '#riq-check-all' ).on( 'change', function () {
        $( '.riq-bulk-cb' ).prop( 'checked', $( this ).is( ':checked' ) );
    } );

    // Generate all missing / selected
    $( '#riq-bulk-gen-all' ).on( 'click', function () {
        if ( gatetouchAdmin.has_api_key === '0' ) {
            if ( typeof gatetouchFlash === 'function' ) {
                gatetouchFlash('⚠️ AI service is not connected. Please configure API settings first.', 'warn');
            } else {
                alert( 'AI service is not connected. Please configure API settings first.' );
            }
            return;
        }
        if ( isRunning ) return;

        // Collect IDs — either checked boxes or all visible
        let ids = [];
        $( '.riq-bulk-cb:checked' ).each( function () { ids.push( $( this ).val() ); } );
        if ( ! ids.length ) {
            $( '.riq-bulk-cb' ).each( function () { ids.push( $( this ).val() ); } );
        }
        if ( ! ids.length ) { alert( 'Load posts first.' ); return; }

        isRunning  = true;
        totalToGen = ids.length;
        genDone    = 0;
        $( '#riq-bulk-progress' ).slideDown();
        $( '#riq-bulk-gen-all' ).prop( 'disabled', true ).html( '<span class="riq-spinner"></span> Generating...' );

        // Add Pause Button if it doesn't exist
        if ( ! $( '#riq-bulk-pause' ).length ) {
            $( '#riq-bulk-gen-all' ).after( '<button id="riq-bulk-pause" class="gatetouch-btn gatetouch-btn--secondary" style="margin-left:10px;">Pause</button>' );
        }

        const CHUNK_SIZE = 5; // Enterprise chunking
        let isPaused = false;

        $( '#riq-bulk-pause' ).on( 'click', function() {
            isPaused = ! isPaused;
            $( this ).text( isPaused ? 'Resume' : 'Pause' );
            if ( ! isPaused ) processBatch();
        } );

        function processBatch() {
            if ( isPaused ) return;
            if ( ! ids.length ) {
                isRunning = false;
                $( '#riq-bulk-gen-all' ).prop( 'disabled', false ).html( '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg> AI Optimize All' );
                $( '#riq-bulk-pause' ).remove();
                $( '#riq-bulk-status' ).text( `✅ Complete! ${genDone} posts optimized.` );
                $( '#riq-bulk-perc' ).text( '100%' );
                return;
            }

            const chunk = ids.splice( 0, CHUNK_SIZE );
            const pct   = Math.round( ( genDone / totalToGen ) * 100 );
            $( '#riq-bulk-bar' ).css( 'width', pct + '%' );
            $( '#riq-bulk-perc' ).text( pct + '%' );
            $( '#riq-bulk-status' ).text( `Processing batch: ${genDone + 1} to ${genDone + chunk.length} of ${totalToGen}...` );

            chunk.forEach( id => $( `#riq-row-${id}` ).css( 'background', '#fefce8' ) );

            $.post( ajax_url, {
                action: 'gatetouch_bulk_batch_generate',
                nonce,
                post_ids: chunk
            } )
            .done( function ( res ) {
                if ( res.success ) {
                    Object.keys( res.data ).forEach( id => {
                        genDone++;
                        const d = res.data[id];
                        const $row = $( `#riq-row-${id}` );

                        if ( d.success ) {
                            $row.css( 'background', '#f0fdf4' );
                            $row.find( '[data-field="meta_title"]' ).val( d.meta_title );
                            $row.find( '[data-field="meta_description"]' ).val( d.meta_description );
                            $row.find( '[data-field="focus_keyword"]' ).val( d.focus_keyword );
                            $row.find( '.gatetouch-badge' )
                                .removeClass( 'gatetouch-badge--amber' )
                                .addClass( 'gatetouch-badge--green' )
                                .html( '✅ Optimized' );
                        } else {
                            $row.css( 'background', '#fef2f2' );
                            $row.find( '.gatetouch-badge' ).html( '❌ Failed' );
                        }
                    } );

                    const finalPct = Math.round( ( genDone / totalToGen ) * 100 );
                    $( '#riq-bulk-bar' ).css( 'width', finalPct + '%' );
                    $( '#riq-bulk-perc' ).text( finalPct + '%' );

                    // Small delay to prevent API overloading
                    setTimeout( processBatch, 1000 );
                } else {
                    isRunning = false;
                    alert( 'Batch failed: ' + res.data );
                }
            } )
            .fail( () => {
                isRunning = false;
                alert( 'Connection error during batch processing.' );
            } );
        }

        processBatch();
    } );

    // ── Background Queue ───────────────────────────────────────
    $( '#riq-bulk-queue-all' ).on( 'click', function () {
        const ids = [];
        $( '.riq-bulk-cb:checked' ).each( function () {
            ids.push( $( this ).val() );
        } );

        if ( ! ids.length ) {
            alert( 'Please select items to queue.' );
            return;
        }

        const $btn = $( this );
        $btn.prop( 'disabled', true ).text( 'Queueing...' );

        $.post( ajax_url, {
            action: 'gatetouch_bulk_queue',
            nonce,
            post_ids: ids
        } ).done( function ( res ) {
            if ( res.success ) {
                $btn.text( '✅ Queued in Background' );
                startPollingProgress();
            } else if ( res.data && res.data.locked ) {
                $btn.prop( 'disabled', false ).text( 'Queue for AI Processing' );
                if ( res.data.settings_url ) {
                    window.location.href = res.data.settings_url;
                    return;
                }
                alert( res.data.message || 'An AI provider API key is required.' );
            } else {
                $btn.prop( 'disabled', false ).text( 'Queue for AI Processing' );
                alert( res.data || 'An error occurred. Please try again.' );
            }
        } ).fail( function() {
            $btn.prop( 'disabled', false ).text( 'Queue for AI Processing' );
        } );
    } );

    let pollingInterval = null;
    function startPollingProgress() {
        if ( pollingInterval ) return;
        $( '#riq-bulk-progress' ).fadeIn();

        pollingInterval = setInterval( function() {
            $.post( ajax_url, { action: 'gatetouch_bulk_progress', nonce } ).done( function( res ) {
                if ( res.success && res.data ) {
                    const { total, current } = res.data;
                    const pct = Math.round( ( current / total ) * 100 );
                    $( '#riq-bulk-bar' ).css( 'width', pct + '%' );
                    $( '#riq-bulk-perc' ).text( pct + '%' );
                    $( '#riq-bulk-status' ).text( `Background Processing: ${current} / ${total} (${pct}%)` );

                    if ( current >= total ) {
                        clearInterval( pollingInterval );
                        pollingInterval = null;
                        $( '#riq-bulk-status' ).text( '✅ Background Processing Complete!' );
                        setTimeout( () => {
                            $( '#riq-bulk-progress' ).fadeOut();
                            loadPosts( currentPage );
                        }, 3000 );
                    }
                } else {
                    clearInterval( pollingInterval );
                    pollingInterval = null;
                }
            });
        }, 5000 );
    }

    // Search
    let searchTimer;
    $( '#riq-bulk-search' ).on( 'input', function () {
        clearTimeout( searchTimer );
        searchTimer = setTimeout( () => loadPosts( 1 ), 500 );
    } );

    // Auto-reload on changing filters/type
    $( '#riq-bulk-type, #riq-bulk-filter' ).on( 'change', () => loadPosts( 1 ) );

    // Auto-load posts of default target type (post) on page init
    loadPosts( 1 );
} );
