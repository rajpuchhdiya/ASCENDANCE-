( function( wp ) {
    const { registerPlugin } = wp.plugins;
    const { PluginSidebar } = wp.editPost || {};
    const { PanelBody, Button, Spinner, TextControl, TextareaControl } = wp.components;
    const { createElement: el, useState } = wp.element;
    const { select, dispatch } = wp.data;

    function Sidebar() {
        const [loading, setLoading] = useState( false );
        const [result, setResult] = useState( null );

        const runGenerate = async () => {
            setLoading( true );
            const post = select( 'core/editor' ).getCurrentPost();
            const content = post && post.content ? post.content.raw || post.content : '';
            const post_id = post && post.id ? post.id : 0;

            try {
                const res = await fetch( AscendanceAIStudio.restUrl + 'ascendance/v1/ai/seo', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': AscendanceAIStudio.nonce
                    },
                    body: JSON.stringify({ post_id: post_id, content: content, provider: 'openai' })
                } );

                const json = await res.json();
                if ( res.ok ) {
                    setResult( json );
                } else {
                    setResult( { error: json.error || 'Unknown error' } );
                }
            } catch ( e ) {
                setResult( { error: e.message } );
            } finally {
                setLoading( false );
            }
        };

        const applyResult = async () => {
            if ( ! result ) return;
            const post = select( 'core/editor' ).getCurrentPost();
            const post_id = post && post.id ? post.id : 0;

            try {
                await fetch( AscendanceAIStudio.restUrl + 'ascendance/v1/ai/seo-apply', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': AscendanceAIStudio.nonce
                    },
                    body: JSON.stringify({ post_id: post_id, title: result.title, meta_description: result.meta_description })
                } );

                // Update editor state immediately
                dispatch( 'core/editor' ).editPost( { title: result.title || '', excerpt: result.meta_description || '' } );
                alert( 'SEO title and meta applied to draft.' );
            } catch ( e ) {
                alert( 'Failed to apply SEO: ' + e.message );
            }
        };

        return el( PluginSidebar, { name: 'ascendance-ai-seo', title: 'AI SEO' },
            el( PanelBody, { title: 'Generate SEO', initialOpen: true },
                el( 'div', { style: { marginBottom: '12px' } },
                    el( Button, { isPrimary: true, onClick: runGenerate, disabled: loading }, loading ? el( Spinner, {} ) : 'Generate SEO' ),
                    ' ',
                    el( Button, { isSecondary: true, onClick: applyResult, disabled: !result || loading }, 'Apply to Draft' )
                ),
                result ? (
                    result.error ? el( 'div', { style: { color: 'red' } }, 'Error: ' + result.error ) : el( 'div', {},
                        el( TextControl, { label: 'Title', value: result.title || '' } ),
                        el( TextareaControl, { label: 'Meta description', value: result.meta_description || '' } )
                    )
                ) : el( 'div', { style: { color: '#777' } }, 'No generated result yet.' )
            )
        );
    }

    registerPlugin( 'ascendance-ai-seo-sidebar', { render: Sidebar } );
} )( window.wp );
