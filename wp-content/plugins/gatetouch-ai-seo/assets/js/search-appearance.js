/**
 * Settings → Search Appearance
 *
 * Variable insertion into the last-focused template field, live character
 * counters, and the title separator picker.
 */
( function () {
    'use strict';

    var LIMITS = {
        title: { min: 50, max: 60 },
        desc:  { min: 145, max: 160 }
    };

    var lastField = null;

    function markTarget( field ) {
        var previous = document.querySelector( '.gatetouch-sa-input.is-var-target' );
        if ( previous ) {
            previous.classList.remove( 'is-var-target' );
        }
        if ( field ) {
            field.classList.add( 'is-var-target' );
        }
    }

    /**
     * Insert text at the caret of the target field, or append if none is focused.
     */
    function insertVariable( variable ) {
        var field = lastField || document.querySelector( '[data-gt-var-target]' );
        if ( ! field ) {
            return;
        }

        var start = typeof field.selectionStart === 'number' ? field.selectionStart : field.value.length;
        var end   = typeof field.selectionEnd === 'number' ? field.selectionEnd : field.value.length;

        field.value = field.value.slice( 0, start ) + variable + field.value.slice( end );

        var caret = start + variable.length;
        field.focus();
        if ( field.setSelectionRange ) {
            field.setSelectionRange( caret, caret );
        }

        field.dispatchEvent( new Event( 'input', { bubbles: true } ) );
    }

    function updateCounter( field ) {
        var kind = field.getAttribute( 'data-gt-counter' );
        if ( ! kind || ! LIMITS[ kind ] ) {
            return;
        }

        var counter = field.parentNode.querySelector( '.gatetouch-sa-counter' );
        if ( ! counter ) {
            return;
        }

        // Rough approximation: variables expand to real text at render time, so
        // the raw template length is only a guide.
        var length = field.value.length;
        var limit  = LIMITS[ kind ];

        counter.textContent = length ? length + ' / ' + limit.max : '';
        counter.className = 'gatetouch-sa-counter' +
            ( length > limit.max ? ' is-over' : ( length >= limit.min ? ' is-good' : '' ) );
    }

    function init() {
        var fields = document.querySelectorAll( '[data-gt-var-target]' );

        Array.prototype.forEach.call( fields, function ( field ) {
            field.addEventListener( 'focus', function () {
                lastField = field;
                markTarget( field );
            } );
            field.addEventListener( 'input', function () {
                updateCounter( field );
            } );
            updateCounter( field );
        } );

        // Keep the caret position current while a field stays focused.
        document.addEventListener( 'selectionchange', function () {
            var active = document.activeElement;
            if ( active && active.hasAttribute && active.hasAttribute( 'data-gt-var-target' ) ) {
                lastField = active;
            }
        } );

        Array.prototype.forEach.call( document.querySelectorAll( '.gatetouch-sa-var' ), function ( button ) {
            // mousedown fires before the field loses focus, preserving the caret.
            button.addEventListener( 'mousedown', function ( event ) {
                event.preventDefault();
            } );
            button.addEventListener( 'click', function () {
                insertVariable( button.getAttribute( 'data-var' ) );
            } );
        } );

        // Media library pickers for the logo and default social image.
        Array.prototype.forEach.call( document.querySelectorAll( '.gatetouch-upload-btn' ), function ( button ) {
            button.addEventListener( 'click', function () {
                var target = document.querySelector( button.getAttribute( 'data-target' ) );
                if ( ! target || ! window.wp || ! window.wp.media ) {
                    return;
                }

                var frame = window.wp.media( {
                    title: button.getAttribute( 'data-title' ) || 'Select image',
                    multiple: false,
                    library: { type: 'image' }
                } );

                frame.on( 'select', function () {
                    var attachment = frame.state().get( 'selection' ).first().toJSON();
                    target.value = attachment.url;
                    target.dispatchEvent( new Event( 'input', { bubbles: true } ) );
                } );

                frame.open();
            } );
        } );

        // Separator picker.
        var picker = document.getElementById( 'gatetouch-sa-sep' );
        if ( picker ) {
            var input = document.getElementById( 'gatetouch-sa-sep-input' );
            Array.prototype.forEach.call( picker.querySelectorAll( '.gatetouch-visual-picker__item' ), function ( item ) {
                item.addEventListener( 'click', function () {
                    Array.prototype.forEach.call( picker.querySelectorAll( '.gatetouch-visual-picker__item' ), function ( other ) {
                        other.classList.remove( 'is-active' );
                    } );
                    item.classList.add( 'is-active' );
                    if ( input ) {
                        input.value = item.getAttribute( 'data-sep' );
                    }
                } );
            } );
        }
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }
}() );
