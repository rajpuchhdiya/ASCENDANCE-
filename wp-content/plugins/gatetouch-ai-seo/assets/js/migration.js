/**
 * Import & Migration screen.
 *
 * Drives the batch loop so large sites never hit the PHP time limit, and shows
 * honest progress: what was imported, what was skipped and why.
 */
( function () {
    'use strict';

    if ( typeof gatetouchAdmin === 'undefined' ) {
        return;
    }

    function post( action, data ) {
        var body = new URLSearchParams();
        body.append( 'action', action );
        body.append( 'nonce', gatetouchAdmin.nonce );

        Object.keys( data || {} ).forEach( function ( key ) {
            var value = data[ key ];
            if ( Array.isArray( value ) ) {
                value.forEach( function ( item ) {
                    body.append( key + '[]', item );
                } );
            } else {
                body.append( key, value );
            }
        } );

        return fetch( gatetouchAdmin.ajax_url, {
            method: 'POST',
            credentials: 'same-origin',
            body: body
        } ).then( function ( response ) {
            return response.json();
        } );
    }

    function card( element ) {
        return element.closest( '.gatetouch-mig-card' );
    }

    function selectedTypes( root ) {
        return Array.prototype.slice
            .call( root.querySelectorAll( '.gatetouch-mig-type:checked' ) )
            .map( function ( input ) {
                return input.value;
            } );
    }

    function setProgress( root, percent, label ) {
        var wrap = root.querySelector( '.gatetouch-mig-progress' );
        var bar  = root.querySelector( '.gatetouch-mig-progress__bar span' );
        var text = root.querySelector( '.gatetouch-mig-progress__label' );

        wrap.hidden = false;
        if ( bar ) {
            bar.style.width = Math.max( 0, Math.min( 100, percent ) ) + '%';
        }
        if ( text ) {
            text.textContent = label;
        }
    }

    function showResult( root, html, tone ) {
        var box = root.querySelector( '.gatetouch-mig-result' );
        box.hidden = false;
        box.className = 'gatetouch-mig-result' + ( tone ? ' is-' + tone : '' );
        box.innerHTML = html;
    }

    function escapeHtml( value ) {
        var div = document.createElement( 'div' );
        div.textContent = value == null ? '' : String( value );
        return div.innerHTML;
    }

    function setBusy( root, busy ) {
        root.querySelectorAll( 'button' ).forEach( function ( button ) {
            button.disabled = busy;
        } );
    }

    // ── Preview ─────────────────────────────────────────────────────────────
    document.addEventListener( 'click', function ( event ) {
        var button = event.target.closest( '.gatetouch-mig-preview' );
        if ( ! button ) {
            return;
        }

        var root = card( button );
        setBusy( root, true );
        setProgress( root, 20, 'Analysing…' );

        post( 'gatetouch_migration_analyze', { source: root.dataset.source } )
            .then( function ( response ) {
                setBusy( root, false );
                setProgress( root, 100, 'Analysis complete' );

                if ( ! response.success ) {
                    showResult( root, escapeHtml( response.data || 'Analysis failed.' ), 'error' );
                    return;
                }

                var data = response.data;
                var rows = '';

                [ 'posts', 'terms', 'users' ].forEach( function ( type ) {
                    var total = data.counts[ type ] || 0;
                    if ( ! total ) {
                        return;
                    }
                    var conflicts = ( data.conflicts && data.conflicts[ type ] ) || 0;
                    rows += '<li><strong>' + total + '</strong> ' + type +
                        ( conflicts ? ' — <em>' + conflicts + ' already have data here and will be skipped</em>' : '' ) +
                        '</li>';
                } );

                if ( data.counts.redirects ) {
                    rows += '<li><strong>' + data.counts.redirects + '</strong> redirects</li>';
                }
                if ( data.settings && data.settings.length ) {
                    rows += '<li>Site templates: ' + escapeHtml( data.settings.join( ', ' ) ) + '</li>';
                }

                var examples = '';
                if ( data.examples && data.examples.length ) {
                    examples = '<p class="gatetouch-mig-result__sub">Converted samples:</p><ul class="gatetouch-mig-samples">';
                    data.examples.forEach( function ( item ) {
                        examples += '<li><code>' + escapeHtml( item.title ) + '</code></li>';
                    } );
                    examples += '</ul>';
                }

                showResult( root, '<p><strong>Preview — nothing has been written yet.</strong></p><ul>' + rows + '</ul>' + examples, 'info' );
            } )
            .catch( function () {
                setBusy( root, false );
                showResult( root, 'Analysis request failed.', 'error' );
            } );
    } );

    // ── Run ─────────────────────────────────────────────────────────────────
    document.addEventListener( 'click', function ( event ) {
        var button = event.target.closest( '.gatetouch-mig-run' );
        if ( ! button ) {
            return;
        }

        var root      = card( button );
        var types     = selectedTypes( root );
        var overwrite = root.querySelector( '.gatetouch-mig-overwrite-input' ).checked;

        if ( ! types.length ) {
            showResult( root, 'Select at least one type to import.', 'error' );
            return;
        }

        if ( overwrite && ! window.confirm( 'Overwrite mode will replace metadata you have already entered in this plugin. Continue?' ) ) {
            return;
        }

        setBusy( root, true );

        var stage    = 0;
        var offset   = 0;
        var batches  = 0;
        var maxLoops = 20000;

        function step() {
            batches++;
            if ( batches > maxLoops ) {
                setBusy( root, false );
                showResult( root, 'Stopped after too many batches. Please re-run.', 'error' );
                return;
            }

            post( 'gatetouch_migration_batch', {
                source: root.dataset.source,
                types: types,
                overwrite: overwrite ? 'true' : 'false',
                stage: stage,
                offset: offset
            } )
                .then( function ( response ) {
                    if ( ! response.success ) {
                        setBusy( root, false );
                        showResult( root, escapeHtml( response.data || 'Import failed.' ), 'error' );
                        return;
                    }

                    var data  = response.data;
                    var state = data.state || {};
                    var done  = data.done;

                    var imported = 0;
                    Object.keys( state.imported || {} ).forEach( function ( key ) {
                        imported += state.imported[ key ];
                    } );

                    if ( done ) {
                        setProgress( root, 100, 'Import complete' );
                        setBusy( root, false );
                        renderSummary( root, state );
                        return;
                    }

                    stage  = data.cursor.stage;
                    offset = data.cursor.offset;

                    // Stage count is the progress signal; record totals are unknown
                    // up front on very large sites.
                    var percent = Math.min( 95, ( stage / Math.max( 1, types.length ) ) * 100 );
                    setProgress( root, percent, 'Importing ' + ( data.stage || '' ) + ' — ' + imported + ' records so far…' );

                    step();
                } )
                .catch( function () {
                    setBusy( root, false );
                    showResult( root, 'Import request failed. Some records may already have been imported — re-running is safe.', 'error' );
                } );
        }

        step();
    } );

    function renderSummary( root, state ) {
        var rows = '';
        Object.keys( state.imported || {} ).forEach( function ( key ) {
            var imported = state.imported[ key ];
            var skipped  = ( state.skipped && state.skipped[ key ] ) || 0;
            if ( ! imported && ! skipped ) {
                return;
            }
            rows += '<tr><td>' + escapeHtml( key ) + '</td><td>' + imported + '</td><td>' + skipped + '</td></tr>';
        } );

        showResult(
            root,
            '<p><strong>Import finished.</strong></p>' +
            '<table class="gatetouch-mig-table"><thead><tr><th>Type</th><th>Imported</th><th>Skipped</th></tr></thead><tbody>' +
            rows + '</tbody></table>' +
            '<p class="gatetouch-mig-result__sub">Now click <strong>Verify</strong> to confirm everything landed.</p>',
            'success'
        );
    }

    // ── Verify ──────────────────────────────────────────────────────────────
    document.addEventListener( 'click', function ( event ) {
        var button = event.target.closest( '.gatetouch-mig-verify' );
        if ( ! button ) {
            return;
        }

        var root = card( button );
        setBusy( root, true );
        setProgress( root, 40, 'Verifying…' );

        post( 'gatetouch_migration_verify', { source: root.dataset.source } )
            .then( function ( response ) {
                setBusy( root, false );
                setProgress( root, 100, 'Verification complete' );

                if ( ! response.success ) {
                    showResult( root, escapeHtml( response.data || 'Verification failed.' ), 'error' );
                    return;
                }

                var data  = response.data;
                var rows  = '';

                Object.keys( data.types || {} ).forEach( function ( type ) {
                    var stats = data.types[ type ];
                    if ( ! stats.checked ) {
                        return;
                    }
                    rows += '<tr><td>' + escapeHtml( type ) + '</td><td>' + stats.checked + '</td><td>' +
                        stats.matched + '</td><td>' + stats.differs + '</td><td>' + stats.missing + '</td></tr>';
                } );

                var verdict = data.healthy
                    ? '<p><strong>Everything checks out.</strong> Every value present in the source is now present here.</p>'
                    : '<p><strong>' + data.totals.missing + ' value(s) did not land.</strong> See the "Missing" column below.</p>';

                var note = data.totals.differs
                    ? '<p class="gatetouch-mig-result__sub">"Differs" means this plugin already held a different value and the import left it alone — expected unless you chose overwrite.</p>'
                    : '';

                showResult(
                    root,
                    verdict +
                    '<table class="gatetouch-mig-table"><thead><tr><th>Type</th><th>Checked</th><th>Matched</th><th>Differs</th><th>Missing</th></tr></thead><tbody>' +
                    rows + '</tbody></table>' + note,
                    data.healthy ? 'success' : 'error'
                );
            } )
            .catch( function () {
                setBusy( root, false );
                showResult( root, 'Verification request failed.', 'error' );
            } );
    } );

    // ── Rollback ────────────────────────────────────────────────────────────
    var rollback = document.getElementById( 'gatetouch-mig-rollback' );
    if ( rollback ) {
        rollback.addEventListener( 'click', function () {
            if ( ! window.confirm( 'Restore site-wide settings to their state before the last import? Per-post and per-category data is not affected.' ) ) {
                return;
            }

            var status = document.getElementById( 'gatetouch-mig-rollback-status' );
            rollback.disabled = true;
            status.textContent = 'Restoring…';

            post( 'gatetouch_migration_rollback', {} )
                .then( function ( response ) {
                    rollback.disabled = false;
                    status.textContent = response.success
                        ? 'Settings restored.'
                        : ( response.data || 'Rollback failed.' );
                } )
                .catch( function () {
                    rollback.disabled = false;
                    status.textContent = 'Rollback request failed.';
                } );
        } );
    }
}() );
