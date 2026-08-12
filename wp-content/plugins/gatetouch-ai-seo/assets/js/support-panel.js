/* global gatetouchSupport, jQuery */
jQuery( function ( $ ) {
    'use strict';

    var $toggle  = $( '#riq-support-toggle' );
    var $panel   = $( '#riq-support-panel' );
	var $overlay = $( '#riq-support-panel-overlay' );

	function getErrorMessage( data ) {
		if ( typeof data === 'string' ) {
			return data;
		}

		return data && data.message ? data.message : gatetouchSupport.strings.error;
	}

	function getSuccessMessage( data ) {
		var message = data && data.message ? data.message : gatetouchSupport.strings.sent;

		if ( data && data.ticket_ref ) {
			message += ' ' + gatetouchSupport.strings.ticket + ' ' + data.ticket_ref + '.';
		}

		if ( data && data.record_id ) {
			message += ' ' + gatetouchSupport.strings.recordId + ' ' + data.record_id + '.';
		}

		return message;
	}

    function openPanel() {
        $panel.addClass( 'is-open' );
        $overlay.addClass( 'is-open' );
        $toggle.attr( 'aria-expanded', 'true' );
        $panel.find( 'input, textarea, select' ).first().trigger( 'focus' );
    }

    function closePanel() {
        $panel.removeClass( 'is-open' );
        $overlay.removeClass( 'is-open' );
        $toggle.attr( 'aria-expanded', 'false' ).trigger( 'focus' );
    }

    $toggle.on( 'click', function () {
        if ( $panel.hasClass( 'is-open' ) ) {
            closePanel();
        } else {
            openPanel();
        }
    } );

    $( '#riq-sp-close' ).on( 'click', closePanel );
    $overlay.on( 'click', closePanel );

    $( document ).on( 'keydown', function ( e ) {
        if ( e.key === 'Escape' && $panel.hasClass( 'is-open' ) ) {
            closePanel();
        }
    } );

    // Contact form submission
    $( '#riq-sp-form' ).on( 'submit', function ( e ) {
        e.preventDefault();

        var $form   = $( this );
        var $btn    = $form.find( '.riq-sp-submit' );
        var $result = $( '#riq-sp-result' );

        var name    = $.trim( $( '#riq-sp-name' ).val() );
        var email   = $.trim( $( '#riq-sp-email' ).val() );
        var message = $.trim( $( '#riq-sp-message' ).val() );

        if ( ! name || ! email || ! message ) {
            $result
                .removeClass( 'riq-sp-result--success' )
                .addClass( 'riq-sp-result--error' )
                .text( 'Please fill in all required fields.' )
                .show();
            return;
        }

        $btn.prop( 'disabled', true ).html(
            '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation:spin 1s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> ' +
            gatetouchSupport.strings.sending
        );
        $result.hide();

        $.post( gatetouchSupport.ajax_url, {
            action:          'gatetouch_submit_support',
            nonce:           gatetouchSupport.nonce,
            support_name:    name,
            support_email:   email,
            support_type:    $( '#riq-sp-type' ).val(),
            support_message: message,
        } )
        .done( function ( res ) {
            if ( res.success ) {
				$result
					.removeClass( 'riq-sp-result--error' )
					.addClass( 'riq-sp-result--success' )
					.text( getSuccessMessage( res.data ) )
					.show();
				$form[0].reset();
			} else {
                $result
					.removeClass( 'riq-sp-result--success' )
					.addClass( 'riq-sp-result--error' )
					.text( getErrorMessage( res.data ) )
					.show();
			}
        } )
        .fail( function () {
            $result
                .removeClass( 'riq-sp-result--success' )
                .addClass( 'riq-sp-result--error' )
                .text( gatetouchSupport.strings.error )
                .show();
        } )
        .always( function () {
            $btn.prop( 'disabled', false ).html(
                '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg> Send Message'
            );
        } );
    } );
} );
