<?php
defined( 'ABSPATH' ) || exit;

/**
 * Provides shared utility helpers for settings, UI rendering, and meta handling.
 */
class GateTouch_Helpers {

    /**
     * Get post meta value with fallback
     */
    public static function get_meta( $post_id, $key, $default = '' ) {
        $meta = get_post_meta( $post_id, GATETOUCH_META_KEY, true );
        if ( ! is_array( $meta ) ) return $default;
        return $meta[ $key ] ?? $default;
    }

    /**
     * Update a single key inside the post meta array
     */
    public static function update_meta_key( $post_id, $key, $value ) {
        $meta         = get_post_meta( $post_id, GATETOUCH_META_KEY, true );
        $meta         = is_array( $meta ) ? $meta : [];
        $meta[ $key ] = $value;
        update_post_meta( $post_id, GATETOUCH_META_KEY, $meta );
    }

    /**
     * Truncate string to a character limit, preserving words
     */
    public static function truncate( $str, $limit, $suffix = '...' ) {
        if ( mb_strlen( $str ) <= $limit ) return $str;
        return mb_substr( $str, 0, $limit - mb_strlen( $suffix ) ) . $suffix;
    }

    /**
     * Clean text for use in meta tags
     */
    public static function clean_meta_text( $text ) {
        $text = wp_strip_all_tags( $text );
        $text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
        $text = preg_replace( '/\s+/', ' ', $text );
        return trim( $text );
    }

    /**
     * Safely output JSON-LD with WordPress' script tag helper.
     */
    public static function print_json_ld( $schema, $class = '' ) {
        $json = wp_json_encode( $schema );
        if ( false === $json ) {
            return;
        }

        $attributes = [ 'type' => 'application/ld+json' ];
        if ( '' !== $class ) {
            $attributes['class'] = sanitize_html_class( $class );
        }

        wp_print_inline_script_tag( $json, $attributes );
        echo "\n";
    }

    /**
     * Sanitize admin-authored .htaccess content without stripping Apache directives.
     */
    public static function sanitize_htaccess_content( $content ) {
        $content = (string) $content;
        $content = str_replace( [ "\r\n", "\r" ], "\n", $content );
        $content = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $content );
        $content = preg_replace( '/<\?(?:php|=)?|<%|%>|\?>/i', '', $content );

        return '' === $content ? '' : rtrim( $content ) . "\n";
    }

    /**
     * Get semantic color for a score
     */
    public static function get_score_color( $score ) {
        if ( $score >= 80 ) return '#10b981'; // Green
        if ( $score >= 50 ) return '#f59e0b'; // Amber
        return '#ef4444'; // Red
    }

    /**
     * Render a toggle switch
     */
    public static function toggle( $name, $value, $label = '' ) {
        $checked = ( $value === 'yes' || $value === '1' || $value === true );
        echo '<div style="display:flex; align-items:center; gap:12px;">';
        echo '<label class="gatetouch-toggle">';
        echo '<input type="checkbox" name="' . esc_attr( $name ) . '" value="yes" ' . checked( true, $checked, false ) . '>';
        echo '<span class="gatetouch-toggle__slider"></span>';
        echo '</label>';
        if ( $label ) {
            echo '<span class="gatetouch-toggle__label" style="font-size:14px; font-weight:500; color:var(--riq-text);">' . esc_html( $label ) . '</span>';
        }
        echo '</div>';
    }

    /**
     * Render a settings page header
     */
    public static function page_header( $title, $subtitle = '' ) {
        ?>
        <div class="gatetouch-admin-header">
            <div class="gatetouch-admin-header__left">
                <div style="background: linear-gradient(135deg, #6366f1, #a855f7); color:#fff; width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:24px; box-shadow: 0 8px 16px rgba(99, 102, 241, 0.2);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                    </svg>
                </div>
                <div>
                    <h1 style="display:flex; align-items:center; gap:12px; margin:0; font-size:24px; font-weight:800; color:#1e293b;">
                        <?php echo esc_html( $title ); ?>
                        <span class="gatetouch-version-badge" style="background:var(--riq-ai-gradient); color:white; font-size:11px; padding:4px 12px; border-radius:40px; text-transform:uppercase; font-weight:700;">V<?php echo esc_html( GATETOUCH_VERSION ); ?></span>
                    </h1>
                    <?php if ( $subtitle ) : ?>
                        <p style="margin:4px 0 0; color:#64748b; font-size:14px;"><?php echo esc_html( $subtitle ); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="gatetouch-admin-header__right">
                <div style="display:flex; gap:10px;">
                    <a href="<?php echo esc_url( home_url( '/sitemap_index.xml' ) ); ?>" target="_blank" rel="noopener noreferrer" class="gatetouch-btn gatetouch-btn--ghost" style="background:#fff; border:1px solid #e2e8f0; color:#475569;">
                       <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:5px;"><path d="M3 3v18h18"/><path d="M18 9l-6 6-3-3-3 3"/></svg>
                       <?php esc_html_e( 'Sitemap', 'gatetouch-ai-seo' ); ?>
                    </a>
                    <a href="<?php echo esc_url( home_url( '/llms.txt' ) ); ?>" target="_blank" rel="noopener noreferrer" class="gatetouch-btn gatetouch-btn--ghost" style="background:#fff; border:1px solid #e2e8f0; color:#475569;">
                       <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:5px;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                       <?php esc_html_e( 'LLMs.txt', 'gatetouch-ai-seo' ); ?>
                    </a>
                    <a href="<?php echo esc_url( home_url( '/robots.txt' ) ); ?>" target="_blank" rel="noopener noreferrer" class="gatetouch-btn gatetouch-btn--ghost" style="background:#fff; border:1px solid #e2e8f0; color:#475569;">
                       <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:5px;"><rect x="3" y="11" width="18" height="10" rx="2"/><circle cx="12" cy="5" r="2"/><path d="M12 7v4"/></svg>
                       <?php esc_html_e( 'Robots.txt', 'gatetouch-ai-seo' ); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render admin notice
     */
    public static function notice( $message, $type = 'success', $dismissible = true ) {
        $classes = [
            'success' => 'gatetouch-notice--success',
            'warn'    => 'gatetouch-notice--warn',
            'error'   => 'gatetouch-notice--error',
            'info'    => 'gatetouch-notice--info',
        ];
        $cls     = $classes[ $type ] ?? 'gatetouch-notice--info';
        // Strip duplicate leading emoji if present, since CSS ::before already renders it
        $message = preg_replace( '/^(✅|❌|⚠️|ℹ️)\s*/u', '', $message );
        
        echo '<div class="gatetouch-notice ' . esc_attr( $cls ) . '" role="alert">' . wp_kses_post( $message );
        if ( $dismissible ) {
            echo '<button type="button" class="gatetouch-notice__dismiss" aria-label="' . esc_attr__( 'Dismiss', 'gatetouch-ai-seo' ) . '">✕</button>';
        }
        echo '</div>';
    }

    /**
     * Get all public post types as array for dropdowns
     */
    public static function get_post_types_options() {
        $types   = get_post_types( [ 'public' => true ], 'objects' );
        $options = [];
        foreach ( $types as $type ) {
            $options[ $type->name ] = $type->labels->singular_name;
        }
        return $options;
    }

    /**
     * Check if current screen is a GateTouch admin page
     */
    public static function is_gatetouch_page( $hook ) {
        return ( strpos( $hook, 'gatetouch' ) !== false || in_array( $hook, [ 'post.php', 'post-new.php' ] ) );
    }

    /**
     * Format bytes to human readable
     */
    public static function format_bytes( $bytes ) {
        $units = [ 'B', 'KB', 'MB', 'GB' ];
        for ( $i = 0; $bytes > 1024 && $i < count( $units ) - 1; $i++ ) {
            $bytes /= 1024;
        }
        return round( $bytes, 2 ) . ' ' . $units[ $i ];
    }

    /**
     * Return an inline SVG for a Tabler icon (https://tabler.io/icons).
     * All paths are static/hardcoded — no user input is involved.
     *
     * @param string $name  Icon slug matching the internal map.
     * @param int    $size  Width/height in pixels.
     * @param array  $attrs Extra HTML attributes for the <svg> element.
     * @return string Safe SVG markup.
     */
    public static function icon( $name, $size = 16, $attrs = [] ) {
        static $paths = null;
        if ( null === $paths ) {
            $paths = [
                // navigation
                'search'            => '<circle cx="10" cy="10" r="7"/><line x1="21" y1="21" x2="15" y2="15"/>',
                'brain'             => '<path d="M15.5 13a3.5 3.5 0 0 0-3.5 3.5v1a3.5 3.5 0 0 0 7 0v-1.8"/><path d="M8.5 13a3.5 3.5 0 0 1 3.5 3.5v1a3.5 3.5 0 0 1-7 0v-1.8"/><path d="M17.5 16a3.5 3.5 0 0 0 0-7h-.5"/><path d="M19 9.3V6.5a3.5 3.5 0 0 0-7 0"/><path d="M6.5 16a3.5 3.5 0 0 1 0-7H7"/><path d="M5 9.3V6.5a3.5 3.5 0 0 1 7 0v10"/>',
                'link'              => '<path d="M10 14a3.5 3.5 0 0 0 5 0l4-4a3.5 3.5 0 0 0-5-5l-2 2"/><path d="M14 10a3.5 3.5 0 0 0-5 0l-4 4a3.5 3.5 0 0 0 5 5l2-2"/>',
                'bolt'              => '<polyline points="13 3 13 10 19 10 11 21 11 14 5 14 13 3"/>',
                'arrows-right-left' => '<path d="M21 7H3"/><path d="M18 10l3-3-3-3"/><path d="M6 20l-3-3 3-3"/><path d="M3 17h18"/>',
                'sitemap'           => '<rect x="3" y="15" width="6" height="6" rx="2"/><rect x="15" y="15" width="6" height="6" rx="2"/><rect x="9" y="3" width="6" height="6" rx="2"/><path d="M6 15v-1a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v1"/><line x1="12" y1="9" x2="12" y2="12"/>',
                'target'            => '<circle cx="12" cy="12" r="1"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="9"/>',
                'robot'             => '<rect x="3" y="8" width="18" height="12" rx="3"/><circle cx="12" cy="3" r="1"/><line x1="12" y1="4" x2="12" y2="8"/><line x1="9" y1="13" x2="9.01" y2="13"/><line x1="15" y1="13" x2="15.01" y2="13"/><path d="M9.5 17a3.5 3.5 0 0 0 5 0"/>',
                'settings-2'        => '<circle cx="12" cy="12" r="3"/><path d="M19.875 6.27a2.225 2.225 0 0 1 1.125 1.948v7.284c0 .809-.443 1.555-1.158 1.948l-6.75 4.27a2.269 2.269 0 0 1-2.184 0l-6.75-4.27a2.225 2.225 0 0 1-1.158-1.948V8.218c0-.809.443-1.554 1.158-1.947l6.75-4.27a2.269 2.269 0 0 1 2.184 0l6.75 4.27z"/>',
                'plug-connected'    => '<path d="M7 12l5 5-1.5 1.5a3.536 3.536 0 1 1-5-5L7 12z"/><path d="M17 12l-5-5 1.5-1.5a3.536 3.536 0 1 1 5 5L17 12z"/><line x1="3" y1="21" x2="7" y2="17"/><line x1="15" y1="9" x2="9" y2="15"/>',
                'shield-check'      => '<path d="M12 3a12 12 0 0 0 8.5 3 12 12 0 0 1-8.5 18A12 12 0 0 1 3.5 6 12 12 0 0 0 12 3"/><path d="M9 12l2 2 4-4"/>',
                'building'          => '<polyline points="3 21 21 21"/><rect x="9" y="3" width="6" height="18"/><polyline points="9 3 3 3 3 21"/><polyline points="15 3 21 3 21 21"/>',
                'rocket'            => '<path d="M4 13a8 8 0 0 1 7 7 6 6 0 0 0 3-5 9 9 0 0 0 6-8 3 3 0 0 0-3-3 9 9 0 0 0-8 6 6 6 0 0 0-5 3"/><path d="M7 14a6 6 0 0 0-3 6 6 6 0 0 0 6-3"/><circle cx="15" cy="9" r="1"/>',
                'compass'           => '<circle cx="12" cy="12" r="9"/><polyline points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/>',
                'stack-2'           => '<path d="M12 4l8 4-8 4-8-4z"/><path d="M4 12l8 4 8-4"/><path d="M4 16l8 4 8-4"/>',
                'share'             => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>',
                'photo'             => '<line x1="15" y1="8" x2="15.01" y2="8"/><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 16l5-5c.928-.893 2.072-.893 3 0l5 5"/><path d="M14 14l1-1c.928-.893 2.072-.893 3 0l3 3"/>',
                // misc / status
                'check'             => '<path d="M5 12l5 5 10-10"/>',
                'check-circle'      => '<circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/>',
                'x'                 => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
                'alert-triangle'    => '<path d="M12 9v4"/><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636-2.87L13.637 3.59a1.914 1.914 0 0 0-3.274 0z"/><path d="M12 16v.01"/>',
                'alert-octagon'     => '<path d="M8.7 3h6.6a1 1 0 0 1 .7.3l4.7 4.7a1 1 0 0 1 .3.7v6.6a1 1 0 0 1-.3.7l-4.7 4.7a1 1 0 0 1-.7.3H8.7a1 1 0 0 1-.7-.3L3.3 15.7a1 1 0 0 1-.3-.7V8.7a1 1 0 0 1 .3-.7L8 3.3a1 1 0 0 1 .7-.3z"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
                'info-circle'       => '<circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
                'chart-bar'         => '<rect x="3" y="12" width="6" height="8" rx="1"/><rect x="9" y="8" width="6" height="12" rx="1"/><rect x="15" y="4" width="6" height="16" rx="1"/>',
                'sparkles'          => '<path d="M16 18a2 2 0 0 1 2 2 2 2 0 0 1 2-2 2 2 0 0 1-2-2 2 2 0 0 1-2 2zm0-12a2 2 0 0 1 2 2 2 2 0 0 1 2-2 2 2 0 0 1-2-2 2 2 0 0 1-2 2z"/><path d="M9 12a4 4 0 0 1 4 4 4 4 0 0 1 4-4 4 4 0 0 1-4-4 4 4 0 0 1-4 4z"/>',
                // content & media
                'pencil'            => '<path d="M4 20h4l10.5-10.5a1.5 1.5 0 0 0-4-4L4 16v4"/><line x1="13.5" y1="6.5" x2="17.5" y2="10.5"/>',
                'file-text'         => '<path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2z"/><line x1="9" y1="9" x2="10" y2="9"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="15" y2="17"/>',
                'device-mobile'     => '<rect x="7" y="4" width="10" height="16" rx="1"/><line x1="11" y1="5" x2="13" y2="5"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
                'palette'           => '<path d="M12 21a9 9 0 1 1 0-18c4.97 0 9 3.582 9 8 0 1.06-.474 2.078-1.318 2.828-.844.75-1.989 1.172-3.182 1.172H15a2 2 0 0 0-1 3.75A1.3 1.3 0 0 1 13 21"/><circle cx="7.5" cy="10.5" r="1"/><circle cx="12" cy="7.5" r="1"/><circle cx="16.5" cy="10.5" r="1"/>',
                // seo & web
                'key'               => '<circle cx="8" cy="15" r="4"/><line x1="11" y1="12" x2="20" y2="3"/><line x1="19" y1="5" x2="21" y2="7"/>',
                'globe'             => '<circle cx="12" cy="12" r="9"/><line x1="3.6" y1="9" x2="20.4" y2="9"/><line x1="3.6" y1="15" x2="20.4" y2="15"/><path d="M11.5 3a17 17 0 0 0 0 18"/><path d="M12.5 3a17 17 0 0 1 0 18"/>',
                'map'               => '<polyline points="3 7 9 4 15 7 21 4 21 17 15 20 9 17 3 20 3 7"/><line x1="9" y1="4" x2="9" y2="17"/><line x1="15" y1="7" x2="15" y2="20"/>',
                'tag'               => '<path d="M11 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4"/><path d="M13 3l8 8-4 4-8-8V3h4"/>',
                'puzzle'            => '<path d="M4 7h3a1 1 0 0 0 1-1V4a1 1 0 0 1 2 0v2a1 1 0 0 0 1 1h3a1 1 0 0 1 0 2h-2a1 1 0 0 0-1 1v3a1 1 0 0 1-2 0v-3a1 1 0 0 0-1-1H4a1 1 0 0 1 0-2z"/>',
                'arrows-shuffle'    => '<path d="M18 4l3 3-3 3"/><path d="M18 20l3-3-3-3"/><path d="M3 7h3a5 5 0 0 1 5 5 5 5 0 0 0 5 5h5"/><path d="M3 17h3a5 5 0 0 0 5-5 5 5 0 0 1 5-5h5"/>',
                'bulb'              => '<path d="M9 16a5 5 0 1 1 6 0v1a1 1 0 0 1-1 1H10a1 1 0 0 1-1-1v-1z"/><path d="M9 18h6"/><path d="M9 21h6"/>',
                'tool'              => '<path d="M7 10H3V6l4-3 4 3v4H7zM7 10v4"/><path d="M10 14H6"/><path d="M16.5 6.5a4.5 4.5 0 0 1-6 0M14 9l6 6-3 3-6-6"/>',
                'briefcase'         => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="12.01"/>',
                'trophy'            => '<path d="M8 21l4-8 4 8"/><path d="M5 3h14a1 1 0 0 1 1 1v3a7 7 0 0 1-7 7 7 7 0 0 1-7-7V4a1 1 0 0 1 1-1z"/><path d="M5 6H3a2 2 0 0 0-2 2v1a4 4 0 0 0 4 4"/><path d="M19 6h2a2 2 0 0 1 2 2v1a4 4 0 0 1-4 4"/>',
                'refresh'           => '<path d="M20 11A8 8 0 0 0 4.5 9M4 5v4h4"/><path d="M4 13a8 8 0 0 0 15.5 2m.5 4v-4h-4"/>',
            ];
        }

        if ( ! isset( $paths[ $name ] ) ) {
            return '';
        }

        $extra = '';
        foreach ( $attrs as $k => $v ) {
            $extra .= ' ' . esc_attr( $k ) . '="' . esc_attr( $v ) . '"';
        }

        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"%2$s>%3$s</svg>',
            (int) $size,
            $extra,
            $paths[ $name ]
        );

        return wp_kses( $svg, self::svg_kses_allowed() );
    }

    /**
     * Return a branded logo container for an AI provider.
     * All output is static/hardcoded SVG — no user input.
     *
     * @param string $slug  Provider slug: openai | anthropic | gemini.
     * @param int    $size  Container size in pixels.
     * @return string Safe HTML with inline SVG logo.
     */
    public static function provider_logo( $slug, $size = 40 ) {
        $s  = (int) $size;
        $r  = (int) round( $s * 0.28 );   // border-radius
        $is = (int) round( $s * 0.62 );   // inner svg size

        switch ( $slug ) {
            case 'openai':
                $bg  = '#000';
                $svg = '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="white" width="' . $is . '" height="' . $is . '"><path d="M22.282 9.821a5.985 5.985 0 0 0-.516-4.911 6.046 6.046 0 0 0-6.51-2.9A6.065 6.065 0 0 0 4.981 4.18a5.985 5.985 0 0 0-3.998 2.9 6.046 6.046 0 0 0 .743 7.097 5.98 5.98 0 0 0 .511 4.911 6.051 6.051 0 0 0 6.515 2.9A5.985 5.985 0 0 0 13.26 24a6.056 6.056 0 0 0 5.772-4.206 5.99 5.99 0 0 0 3.997-2.9 6.056 6.056 0 0 0-.747-7.073zM13.26 22.43a4.476 4.476 0 0 1-2.876-1.04l.141-.08 4.779-2.758a.775.775 0 0 0 .393-.681v-6.737l2.02 1.168a.071.071 0 0 1 .038.052v5.583a4.504 4.504 0 0 1-4.495 4.493zM3.6 18.304a4.47 4.47 0 0 1-.535-3.014l.142.085 4.783 2.759a.771.771 0 0 0 .78 0l5.843-3.369v2.332a.08.08 0 0 1-.033.062L9.74 19.95a4.5 4.5 0 0 1-6.14-1.646zM2.34 7.896a4.485 4.485 0 0 1 2.366-1.973V11.6a.766.766 0 0 0 .388.676l5.815 3.355-2.02 1.168a.076.076 0 0 1-.071 0l-4.83-2.786A4.504 4.504 0 0 1 2.34 7.872zm16.597 3.855L13.1 8.364l2.015-1.164a.076.076 0 0 1 .071 0l4.83 2.791a4.494 4.494 0 0 1-.676 8.105v-5.678a.79.79 0 0 0-.407-.666zm2.01-3.023l-.141-.085-4.774-2.782a.776.776 0 0 0-.785 0L9.409 9.23V6.897a.066.066 0 0 1 .028-.061l4.83-2.787a4.5 4.5 0 0 1 6.68 4.66zm-12.64 4.135l-2.02-1.164a.08.08 0 0 1-.038-.057V6.074a4.5 4.5 0 0 1 7.376-3.453l-.142.08L8.704 5.46a.775.775 0 0 0-.393.681zm1.097-2.365l2.602-1.5 2.607 1.5v2.999l-2.597 1.5-2.607-1.5z"/></svg>';
                break;
            case 'anthropic':
                $bg  = '#cc785c';
                $svg = '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="white" width="' . $is . '" height="' . $is . '"><path d="M13.827 3.38 20.03 20.62H17.02l-1.52-4.41H8.498l-1.527 4.41H3.969L10.173 3.38zm-3.534 9.886h3.416L12 6.964z"/></svg>';
                break;
            case 'gemini':
                $bg  = '#fff';
                $svg = '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width="' . $is . '" height="' . $is . '"><defs><linearGradient id="rk-gem" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#4285f4"/><stop offset="45%" stop-color="#9b72ff"/><stop offset="100%" stop-color="#d96570"/></linearGradient></defs><path d="M12 2C12 7.48 7.48 12 2 12c5.48 0 10 4.52 10 10 0-5.48 4.52-10 10-10-5.48 0-10-4.52-10-10z" fill="url(#rk-gem)"/></svg>';
                break;
            default:
                return '';
        }

        $allowed = array_merge(
            self::svg_kses_allowed(),
            [ 'div' => [ 'style' => true ] ]
        );

        return wp_kses(
            sprintf(
                '<div style="width:%1$dpx;height:%1$dpx;background:%2$s;border-radius:%3$dpx;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.15);">%4$s</div>',
                $s,
                esc_attr( $bg ),
                $r,
                $svg
            ),
            $allowed
        );
    }

    /**
     * Kses allowlist for inline SVG icons produced by this class.
     * All SVG content is hardcoded — this satisfies WP.org escaping policy.
     */
    public static function svg_kses_allowed() {
        $common = [ 'fill' => true, 'stroke' => true, 'stroke-width' => true ];
        return [
            'svg'            => array_merge( $common, [
                'xmlns'            => true,
                'width'            => true,
                'height'           => true,
                'viewbox'          => true,
                'stroke-linecap'   => true,
                'stroke-linejoin'  => true,
                'class'            => true,
                'aria-hidden'      => true,
                'focusable'        => true,
                'role'             => true,
            ] ),
            'path'           => array_merge( $common, [ 'd' => true ] ),
            'circle'         => array_merge( $common, [ 'cx' => true, 'cy' => true, 'r' => true ] ),
            'line'           => array_merge( $common, [ 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true ] ),
            'polyline'       => array_merge( $common, [ 'points' => true ] ),
            'rect'           => array_merge( $common, [ 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true ] ),
            'defs'           => [],
            'lineargradient' => [ 'id' => true, 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true ],
            'stop'           => [ 'offset' => true, 'stop-color' => true, 'stop-opacity' => true ],
        ];
    }

    /**
     * Sanitize an array whose values are binary flags ('0','1','yes','no').
     *
     * Anything outside that set collapses to '0', so this must only ever be
     * registered for options that are genuinely flags-only. Applying it to a
     * mixed-shape option silently destroys every text, URL, number and nested
     * value in it — see sanitize_schema_settings() and sanitize_robots_settings().
     */
    public static function sanitize_flags_array( $value ) {
        if ( ! is_array( $value ) ) {
            return [];
        }
        $allowed = [ '0', '1', 'yes', 'no', '' ];
        $clean   = [];
        foreach ( $value as $k => $v ) {
            $clean[ sanitize_key( $k ) ] = in_array( (string) $v, $allowed, true ) ? (string) $v : '0';
        }
        return $clean;
    }

    /**
     * Sanitize the structured-data option.
     *
     * Mixed shape: Organization identity fields (free text, URL, email, date)
     * alongside binary output flags. Each is sanitized by its own type, and
     * unknown keys fall back to plain text rather than being discarded.
     */
    public static function sanitize_schema_settings( $value ) {
        if ( ! is_array( $value ) ) {
            return [];
        }

        $org_types = [ 'Organization', 'Person', 'LocalBusiness', 'NewsMediaOrganization', 'EducationalOrganization', 'GovernmentOrganization', 'NGO', 'OnlineStore' ];
        $flags     = [ 'enabled', 'website_schema', 'breadcrumb_schema', 'author_schema', 'faq_automation', 'item_list_schema', 'speakable_schema', 'nlp_optimization', 'entity_mapping', 'auto_schema' ];

        $clean = [];
        foreach ( $value as $k => $v ) {
            $key = sanitize_key( $k );

            if ( in_array( $key, $flags, true ) ) {
                $clean[ $key ] = empty( $v ) || '0' === (string) $v || 'no' === (string) $v ? '0' : '1';
                continue;
            }

            switch ( $key ) {
                case 'org_type':
                    $clean[ $key ] = in_array( (string) $v, $org_types, true ) ? (string) $v : 'Organization';
                    break;
                case 'org_logo':
                    $clean[ $key ] = esc_url_raw( (string) $v );
                    break;
                case 'org_email':
                    $clean[ $key ] = sanitize_email( (string) $v );
                    break;
                case 'org_description':
                    $clean[ $key ] = sanitize_textarea_field( (string) $v );
                    break;
                default:
                    $clean[ $key ] = is_array( $v )
                        ? array_map( 'sanitize_text_field', $v )
                        : sanitize_text_field( (string) $v );
            }
        }

        return $clean;
    }

    /**
     * Sanitize the robots.txt option.
     *
     * Mixed shape: the custom rule table (a list of ua/dir/val rows), the raw
     * expert-mode textarea, a crawl delay, and one yes/no flag per AI crawler.
     */
    public static function sanitize_robots_settings( $value ) {
        if ( ! is_array( $value ) ) {
            return [];
        }

        $clean = [];
        foreach ( $value as $k => $v ) {
            $key = sanitize_key( $k );

            switch ( true ) {
                case 'custom_rules' === $key:
                    $rules = [];
                    foreach ( (array) $v as $rule ) {
                        if ( ! is_array( $rule ) ) {
                            continue;
                        }
                        $dir     = sanitize_key( $rule['dir'] ?? '' );
                        $rules[] = [
                            'ua'  => sanitize_text_field( $rule['ua'] ?? '' ),
                            'dir' => in_array( $dir, [ 'allow', 'disallow' ], true ) ? $dir : 'disallow',
                            'val' => sanitize_text_field( $rule['val'] ?? '' ),
                        ];
                    }
                    $clean[ $key ] = $rules;
                    break;

                case 'robots_custom' === $key:
                    $clean[ $key ] = sanitize_textarea_field( (string) $v );
                    break;

                case 'robots_mode' === $key:
                    $clean[ $key ] = in_array( (string) $v, [ 'auto', 'custom' ], true ) ? (string) $v : 'auto';
                    break;

                case 'crawl_delay' === $key:
                    $clean[ $key ] = absint( $v );
                    break;

                case 'ai_bots' === $key:
                    // Legacy key, kept so older stored values survive a save.
                    $clean[ $key ] = is_array( $v ) ? array_map( 'sanitize_text_field', $v ) : [];
                    break;

                case 0 === strpos( $key, 'allow_' ):
                    $clean[ $key ] = 'no' === (string) $v ? 'no' : 'yes';
                    break;

                default:
                    $clean[ $key ] = is_array( $v )
                        ? array_map( 'sanitize_text_field', $v )
                        : ( in_array( (string) $v, [ '0', '1', 'yes', 'no', '' ], true )
                            ? (string) $v
                            : sanitize_text_field( (string) $v ) );
            }
        }

        return $clean;
    }

    /**
     * Sanitize the Search Appearance settings tree.
     *
     * The shape is nested and heterogeneous — templates, robots flags, schema
     * settings and WooCommerce toggles — so each group is handled explicitly
     * rather than mapped through one generic sanitiser.
     *
     * Template fields keep their #variable# syntax, so they are sanitised as text
     * rather than escaped: escaping happens at output time.
     *
     * @param array $value Raw $_POST['sa'] tree.
     * @return array Clean settings, ready to merge into the stored option.
     */
    public static function sanitize_search_appearance( $value ) {
        if ( ! is_array( $value ) ) {
            return [];
        }

        $clean = [];

        // ── Global ──────────────────────────────────────────────────────────
        if ( isset( $value['global'] ) && is_array( $value['global'] ) ) {
            $global = $value['global'];

            $separator = sanitize_text_field( $global['title_separator'] ?? '|' );

            $clean['global'] = [
                'title_separator'  => '' === $separator ? '|' : $separator,
                'og_default_image' => esc_url_raw( $global['og_default_image'] ?? '' ),
                'twitter_card'     => in_array( $global['twitter_card'] ?? '', [ 'summary', 'summary_large_image' ], true )
                    ? $global['twitter_card']
                    : 'summary_large_image',
                'twitter_site'     => sanitize_text_field( $global['twitter_site'] ?? '' ),
                'facebook_app_id'  => sanitize_text_field( $global['facebook_app_id'] ?? '' ),
                'homepage_noindex' => empty( $global['homepage_noindex'] ) ? '' : '1',
            ];

            // The homepage template is posted as its own row for layout reasons.
            if ( isset( $value['global']['homepage'] ) && is_array( $value['global']['homepage'] ) ) {
                $clean['global']['homepage_title'] = sanitize_text_field( $value['global']['homepage']['title'] ?? '' );
                $clean['global']['homepage_desc']  = sanitize_textarea_field( $value['global']['homepage']['desc'] ?? '' );
            }
        }

        // ── Template groups ─────────────────────────────────────────────────
        foreach ( [ 'content_types', 'taxonomies', 'archives', 'post_type_archives' ] as $group ) {
            if ( ! isset( $value[ $group ] ) || ! is_array( $value[ $group ] ) ) {
                continue;
            }

            foreach ( $value[ $group ] as $key => $row ) {
                if ( ! is_array( $row ) ) {
                    continue;
                }

                $key = sanitize_key( $key );

                $entry = [
                    'title' => sanitize_text_field( $row['title'] ?? '' ),
                    'desc'  => sanitize_textarea_field( $row['desc'] ?? '' ),
                ];

                foreach ( [ 'noindex', 'nofollow', 'noarchive', 'noimageindex', 'nosnippet', 'noindex_empty', 'noindex_single_author' ] as $flag ) {
                    // Checkboxes only post when checked, so an absent key means off.
                    $entry[ $flag ] = empty( $row[ $flag ] ) ? '' : '1';
                }

                if ( isset( $row['schema_type'] ) ) {
                    $entry['schema_type'] = sanitize_text_field( $row['schema_type'] );
                }
                if ( isset( $row['og_image'] ) ) {
                    $entry['og_image'] = esc_url_raw( $row['og_image'] );
                }

                $clean[ $group ][ $key ] = $entry;
            }
        }

        // ── Advanced ────────────────────────────────────────────────────────
        if ( isset( $value['advanced'] ) && is_array( $value['advanced'] ) ) {
            $advanced = $value['advanced'];

            $max_image = $advanced['max_image_preview'] ?? 'large';

            $clean['advanced'] = [
                'max_snippet'        => self::sanitize_preview_limit( $advanced['max_snippet'] ?? '-1' ),
                'max_video_preview'  => self::sanitize_preview_limit( $advanced['max_video_preview'] ?? '-1' ),
                'max_image_preview'  => in_array( $max_image, [ 'large', 'standard', 'none' ], true ) ? $max_image : 'large',
                'use_meta_keywords'  => empty( $advanced['use_meta_keywords'] ) ? '' : '1',
                'no_paged_canonical' => empty( $advanced['no_paged_canonical'] ) ? '' : '1',
                'noindex_paged'      => empty( $advanced['noindex_paged'] ) ? '' : '1',
                'crawl_cleanup_rss'  => empty( $advanced['crawl_cleanup_rss'] ) ? '' : '1',
            ];
        }

        // ── Image SEO ───────────────────────────────────────────────────────
        if ( isset( $value['image_seo'] ) && is_array( $value['image_seo'] ) ) {
            $clean['image_seo'] = [
                'redirect_attachments' => empty( $value['image_seo']['redirect_attachments'] ) ? '' : '1',
            ];
        }

        // ── WooCommerce ─────────────────────────────────────────────────────
        if ( isset( $value['woocommerce'] ) && is_array( $value['woocommerce'] ) ) {
            $woo = $value['woocommerce'];

            $clean['woocommerce'] = [
                'default_brand' => sanitize_text_field( $woo['default_brand'] ?? '' ),
            ];

            foreach ( [ 'replace_woo_schema', 'review_schema', 'noindex_cart', 'noindex_filtered', 'remove_generator' ] as $flag ) {
                $clean['woocommerce'][ $flag ] = empty( $woo[ $flag ] ) ? '0' : '1';
            }
        }

        // ── Schema settings live in their own option ────────────────────────
        if ( isset( $value['schema'] ) && is_array( $value['schema'] ) ) {
            self::save_schema_settings( $value['schema'] );
        }

        return $clean;
    }

    /**
     * A robots preview limit is either -1 (no limit) or a non-negative integer.
     */
    private static function sanitize_preview_limit( $value ) {
        $value = trim( (string) $value );

        if ( '-1' === $value ) {
            return '-1';
        }

        return (string) max( 0, (int) $value );
    }

    /**
     * Persist the schema half of the Search Appearance screen.
     *
     * Schema lives in gatetouch_schema_settings because other modules read it
     * from there; this keeps the settings screen a single form regardless.
     */
    private static function save_schema_settings( array $raw ) {
        $existing = get_option( 'gatetouch_schema_settings', [] );
        $existing = is_array( $existing ) ? $existing : [];

        $org_types = [ 'Organization', 'Person', 'NewsMediaOrganization', 'EducationalOrganization', 'GovernmentOrganization', 'NGO', 'OnlineStore' ];
        $org_type  = $raw['org_type'] ?? 'Organization';

        $clean = [
            'org_type'          => in_array( $org_type, $org_types, true ) ? $org_type : 'Organization',
            'org_name'          => sanitize_text_field( $raw['org_name'] ?? '' ),
            'org_logo'          => esc_url_raw( $raw['org_logo'] ?? '' ),
            'org_description'   => sanitize_textarea_field( $raw['org_description'] ?? '' ),
            'org_phone'         => sanitize_text_field( $raw['org_phone'] ?? '' ),
            'org_email'         => sanitize_email( $raw['org_email'] ?? '' ),
            'org_founding_date' => sanitize_text_field( $raw['org_founding_date'] ?? '' ),
            'org_vat_id'        => sanitize_text_field( $raw['org_vat_id'] ?? '' ),
        ];

        foreach ( [ 'enabled', 'website_schema', 'breadcrumb_schema', 'author_schema', 'faq_automation', 'item_list_schema', 'speakable_schema' ] as $flag ) {
            $clean[ $flag ] = empty( $raw[ $flag ] ) ? '0' : '1';
        }

        update_option( 'gatetouch_schema_settings', array_merge( $existing, $clean ) );
    }

    /**
     * Sanitize homepage meta (title + description + noindex flag).
     */
    public static function sanitize_homepage_meta( $value ) {
        if ( ! is_array( $value ) ) {
            return [];
        }
        return [
            'title'       => sanitize_text_field( $value['title'] ?? '' ),
            'description' => sanitize_textarea_field( $value['description'] ?? '' ),
            'noindex'     => ! empty( $value['noindex'] ) ? '1' : '0',
        ];
    }

    /**
     * Sanitize breadcrumb settings array.
     */
    public static function sanitize_breadcrumb_settings( $value ) {
        if ( ! is_array( $value ) ) {
            return [];
        }
        return [
            'enabled'        => ! empty( $value['enabled'] ) ? '1' : '0',
            'placement'      => in_array( (string) ( $value['placement'] ?? 'content' ), [ 'manual', 'content', 'body_open' ], true ) ? (string) $value['placement'] : 'content',
            'separator'      => sanitize_text_field( $value['separator'] ?? '›' ),
            'home_label'     => sanitize_text_field( $value['home_label'] ?? 'Home' ),
            'prefix'         => sanitize_text_field( $value['prefix'] ?? '' ),
            'show_blog'      => ! empty( $value['show_blog'] ) ? '1' : '0',
            'show_current'   => ! empty( $value['show_current'] ) ? '1' : '0',
            'link_current'   => ! empty( $value['link_current'] ) ? '1' : '0',
            'archive_format' => sanitize_text_field( $value['archive_format'] ?? 'Archives for #taxonomy#' ),
            'search_format'  => sanitize_text_field( $value['search_format'] ?? 'Search for "#search_query#"' ),
            'error_format'   => sanitize_text_field( $value['error_format'] ?? '404 Error: Page not found' ),
        ];
    }

    /**
     * Sanitize social media settings array (nested structure with URLs).
     */
    public static function sanitize_social_settings( $value ) {
        if ( ! is_array( $value ) ) {
            return [];
        }
        $profiles = [];
        if ( ! empty( $value['profiles'] ) && is_array( $value['profiles'] ) ) {
            $profiles = array_map( 'esc_url_raw', $value['profiles'] );
        }
        return [
            'profiles' => $profiles,
            'facebook' => [
                'enabled'     => ! empty( $value['facebook']['enabled'] ) ? '1' : '',
                'default_img' => esc_url_raw( $value['facebook']['default_img'] ?? '' ),
                'app_id'      => sanitize_text_field( $value['facebook']['app_id'] ?? '' ),
                'object_type' => sanitize_text_field( $value['facebook']['object_type'] ?? 'article' ),
            ],
            'twitter' => [
                'enabled'     => ! empty( $value['twitter']['enabled'] ) ? '1' : '',
                'card_type'   => sanitize_text_field( $value['twitter']['card_type'] ?? 'summary_large_image' ),
                'site_handle' => sanitize_text_field( $value['twitter']['site_handle'] ?? '' ),
                'default_img' => esc_url_raw( $value['twitter']['default_img'] ?? '' ),
            ],
            'pinterest' => [
                'verify_code' => sanitize_text_field( $value['pinterest']['verify_code'] ?? '' ),
            ],
            // Flat-key compat used by business.php tab.
            'facebook_enabled' => ! empty( $value['facebook_enabled'] ) ? '1' : '0',
            'twitter_enabled'  => ! empty( $value['twitter_enabled'] ) ? '1' : '0',
            'twitter_card'     => sanitize_text_field( $value['twitter_card'] ?? 'summary_large_image' ),
        ];
    }

    /**
     * Sanitize analytics / webmaster settings array.
     */
    public static function sanitize_webmaster_settings( $value ) {
        if ( ! is_array( $value ) ) {
            return [];
        }
        return [
            'google_search_console' => sanitize_text_field( $value['google_search_console'] ?? '' ),
            'google_analytics'      => sanitize_text_field( $value['google_analytics'] ?? '' ),
            'bing_webmaster'        => sanitize_text_field( $value['bing_webmaster'] ?? '' ),
            'pinterest_verify'      => sanitize_text_field( $value['pinterest_verify'] ?? '' ),
            'meta_pixel'            => sanitize_text_field( $value['meta_pixel'] ?? '' ),
            'custom_header'         => wp_kses_post( $value['custom_header'] ?? '' ),
            'custom_footer'         => wp_kses_post( $value['custom_footer'] ?? '' ),
            'custom_body'           => wp_kses_post( $value['custom_body'] ?? '' ),
        ];
    }

    /**
     * Sanitize LLMs.txt settings array.
     */
    public static function sanitize_llms_settings( $value ) {
        if ( ! is_array( $value ) ) {
            return [];
        }
        $cpts = [];
        if ( ! empty( $value['llms_include_cpts'] ) && is_array( $value['llms_include_cpts'] ) ) {
            $cpts = array_map( 'sanitize_key', $value['llms_include_cpts'] );
        }
        return [
            'enable_llms_txt'      => in_array( $value['enable_llms_txt'] ?? '', [ 'yes', '1' ], true ) ? 'yes' : 'no',
            'enable_llms_full_txt' => in_array( $value['enable_llms_full_txt'] ?? '', [ 'yes', '1' ], true ) ? 'yes' : 'no',
            'site_description'     => sanitize_textarea_field( $value['site_description'] ?? '' ),
            'llms_max_posts'       => absint( $value['llms_max_posts'] ?? 20 ),
            'llms_full_max_pages'  => absint( $value['llms_full_max_pages'] ?? 5 ),
            'llms_custom_intro'    => sanitize_textarea_field( $value['llms_custom_intro'] ?? '' ),
            'llms_include_cpts'    => $cpts,
            'enabled'              => in_array( $value['enabled'] ?? '', [ 'yes', '1' ], true ) ? 'yes' : 'no',
        ];
    }

    /**
     * Sanitize security.txt settings array.
     */
    public static function sanitize_security_txt_settings( $value ) {
        if ( ! is_array( $value ) ) {
            return [];
        }
        return [
            'enable'              => ! empty( $value['enable'] ) ? '1' : '',
            'contact'             => sanitize_text_field( $value['contact'] ?? '' ),
            'expires'             => sanitize_text_field( $value['expires'] ?? '' ),
            'policy'              => esc_url_raw( $value['policy'] ?? '' ),
            'preferred_languages' => sanitize_text_field( $value['preferred_languages'] ?? 'en' ),
        ];
    }

    /**
     * Sanitize sitemap settings array.
     */
    public static function sanitize_sitemap_settings( $value ) {
        if ( ! is_array( $value ) ) {
            return [];
        }
        $clean = [];
        foreach ( $value as $k => $v ) {
            $key = sanitize_key( $k );
            if ( is_array( $v ) ) {
                $clean[ $key ] = array_map( 'sanitize_key', $v );
            } elseif ( 'sitemap_priority' === $key ) {
                // A decimal between 0.0 and 1.0 — absint() would floor "0.8" to 0.
                $priority      = is_numeric( $v ) ? (float) $v : 0.8;
                $clean[ $key ] = (string) min( 1.0, max( 0.0, $priority ) );
            } elseif ( is_numeric( $v ) ) {
                $clean[ $key ] = absint( $v );
            } else {
                $clean[ $key ] = in_array( (string) $v, [ 'yes', 'no', '0', '1', '' ], true )
                    ? (string) $v
                    : sanitize_text_field( (string) $v );
            }
        }
        return $clean;
    }

    /**
     * Render an Intelligent SEO Audit Card
     */
    public static function render_audit_card( $issue ) {
        $priority_cls = 'gatetouch-priority--' . strtolower( $issue['priority'] ?? 'medium' );
        $type         = $issue['type'] ?? 'info';
        $icon_name    = $type === 'error' ? 'alert-octagon' : ( $type === 'warning' ? 'alert-triangle' : 'info-circle' );
        $icon_color   = $type === 'error' ? 'red' : ( $type === 'warning' ? 'amber' : 'blue' );
        $uniqid = !empty($issue['id']) ? $issue['id'] : uniqid();
        ?>
        <div class="gatetouch-intelligent-card" id="riq-issue-<?php echo esc_attr($uniqid); ?>">
            <div class="gatetouch-intelligent-card__header">
                <div class="gatetouch-intelligent-card__title-wrap">
                    <span class="rk-icon-box rk-icon-box--sm rk-icon-box--<?php echo esc_attr( $icon_color ); ?>"><?php echo wp_kses( self::icon( $icon_name, 16 ), self::svg_kses_allowed() ); ?></span>
                    <h3 style="margin:0; font-size:18px; font-weight:800;"><?php echo esc_html($issue['title']); ?></h3>
                    <span class="gatetouch-intelligent-card__priority <?php echo esc_attr( $priority_cls ); ?>"><?php echo esc_html($issue['priority'] ?? 'Medium'); ?></span>
                </div>
                <div style="display:flex; gap:10px;">
                    <button class="gatetouch-btn gatetouch-btn--ghost gatetouch-btn--sm riq-recheck-btn" data-id="<?php echo esc_attr($uniqid); ?>"><?php esc_html_e( 'Recheck', 'gatetouch-ai-seo' ); ?></button>
                </div>
            </div>

            <div class="gatetouch-intelligent-card__body">
                <div class="gatetouch-intelligent-card__explanation">
                    <?php echo wp_kses_post($issue['explanation']); ?>
                </div>

                <div class="gatetouch-intelligent-card__impact-grid">
                    <div class="gatetouch-impact-section">
                        <h4 style="display:flex;align-items:center;gap:6px;"><?php echo wp_kses( self::icon( 'chart-bar', 15 ), self::svg_kses_allowed() ); ?></h4>
                        <ul class="gatetouch-impact-list">
                            <?php foreach ((array)$issue['seo_impact'] as $impact) : ?>
                                <li><?php echo esc_html($impact); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="gatetouch-impact-section">
                        <h4 style="display:flex;align-items:center;gap:6px;"><?php echo wp_kses( self::icon( 'briefcase', 15 ), self::svg_kses_allowed() ); ?></h4>
                        <p style="font-size:13px; color:#475569; margin:0; line-height:1.6;"><?php echo esc_html($issue['owner_impact'] ?? ''); ?></p>
                    </div>
                </div>

                <!-- Mode Toggles -->
                <div class="gatetouch-mode-toggle-wrap">
                    <button class="gatetouch-mode-btn active" data-mode="owner" data-target="riq-owner-<?php echo esc_attr($uniqid); ?>"><?php esc_html_e( 'Owner Guide', 'gatetouch-ai-seo' ); ?></button>
                    <button class="gatetouch-mode-btn" data-mode="dev" data-target="riq-dev-<?php echo esc_attr($uniqid); ?>"><?php esc_html_e( 'Developer Guide', 'gatetouch-ai-seo' ); ?></button>
                </div>

                <!-- Owner Mode Content -->
                <div id="riq-owner-<?php echo esc_attr($uniqid); ?>" class="gatetouch-mode-content active">
                    <div style="display:flex; gap:20px;">
                        <span class="rk-icon-box rk-icon-box--amber"><?php echo wp_kses( self::icon( 'bulb', 20 ), self::svg_kses_allowed() ); ?></span>
                        <div>
                            <strong style="display:block; margin-bottom:5px; font-size:14px;"><?php esc_html_e( 'How to Fix (Beginner Friendly)', 'gatetouch-ai-seo' ); ?></strong>
                            <p style="font-size:13px; color:#475569; margin:0; line-height:1.6;"><?php echo esc_html($issue['fix_beginner'] ?? ''); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Developer Mode Content -->
                <div id="riq-dev-<?php echo esc_attr($uniqid); ?>" class="gatetouch-mode-content">
                    <div style="display:flex; gap:20px; margin-bottom:15px;">
                        <span class="rk-icon-box rk-icon-box--slate"><?php echo wp_kses( self::icon( 'tool', 20 ), self::svg_kses_allowed() ); ?></span>
                        <div>
                            <strong style="display:block; margin-bottom:5px; font-size:14px;"><?php esc_html_e( 'Technical Implementation', 'gatetouch-ai-seo' ); ?></strong>
                            <p style="font-size:13px; color:#475569; margin:0; line-height:1.6;"><?php echo esc_html($issue['fix_developer'] ?? ''); ?></p>
                        </div>
                    </div>
                    <?php if (!empty($issue['code_example'])) : ?>
                        <div style="margin-top:15px;">
                            <strong style="font-size:11px; text-transform:uppercase; color:var(--riq-text-light);"><?php esc_html_e( 'Code Example:', 'gatetouch-ai-seo' ); ?></strong>
                            <pre class="gatetouch-code-block"><?php echo esc_html($issue['code_example']); ?></pre>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($issue['dev_details'])) : ?>
                        <div style="margin-top:15px; padding-top:15px; border-top:1px solid #e2e8f0;">
                            <strong style="font-size:11px; text-transform:uppercase; color:var(--riq-text-light);"><?php esc_html_e( 'Detected Details:', 'gatetouch-ai-seo' ); ?></strong>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:8px;">
                                <?php foreach ($issue['dev_details'] as $key => $val) : ?>
                                    <div style="font-size:12px;"><span style="color:var(--riq-text-light);"><?php echo esc_html($key); ?>:</span> <strong><?php echo esc_html($val); ?></strong></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="gatetouch-action-footer">
                <?php if (!empty($issue['learn_more'])) : ?>
                    <a href="<?php echo esc_url($issue['learn_more']); ?>" class="gatetouch-learn-more">Learn More ↗</a>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>
                
                <div style="display:flex; gap:12px;">
                    <?php if (!empty($issue['action_btn'])) : ?>
                        <a href="<?php echo esc_url($issue['action_btn']['link']); ?>" class="gatetouch-btn gatetouch-btn--primary"><?php echo esc_html($issue['action_btn']['text']); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render a full-page API key gate for features that require an active AI connection.
     *
     * Usage:
     *   if ( ! GateTouch_AI_Engine::has_api_key() ) {
     *       GateTouch_Helpers::api_key_gate( 'Bulk AI Optimizer' );
     *       return;
     *   }
     */
    public static function api_key_gate( $feature_name = '' ) {
        $settings_url  = esc_url( admin_url( 'admin.php?page=gatetouch-settings&tab=ai' ) );
        $provider      = get_option( 'gatetouch_ai_provider', 'openai' );
        $provider_info = [
            'openai'    => [ 'icon' => '🤖', 'name' => 'OpenAI',        'link' => 'https://platform.openai.com/api-keys',       'hint' => 'Get your key at platform.openai.com' ],
            'anthropic' => [ 'icon' => '🧠', 'name' => 'Anthropic',     'link' => 'https://console.anthropic.com/settings/keys', 'hint' => 'Get your key at console.anthropic.com' ],
            'gemini'    => [ 'icon' => '✨', 'name' => 'Google Gemini', 'link' => 'https://aistudio.google.com/app/apikey',      'hint' => 'Get your free key at aistudio.google.com' ],
        ];
        $prov = $provider_info[ $provider ] ?? $provider_info['openai'];

        $errors = (int) get_option( 'gatetouch_api_error_count', 0 );
        $is_safe_mode = $errors >= 5;

        // Determine the specific reason
        $key_saved = (bool) GateTouch_AI_Engine::get_key( $provider );
        if ( $is_safe_mode ) {
            $reason  = __( 'AI requests are paused — your API key returned 5+ consecutive errors.', 'gatetouch-ai-seo' );
            $sub     = __( 'Check your API key and billing, then reset safe mode in AI Settings.', 'gatetouch-ai-seo' );
            $icon_name = 'alert-octagon';
            $color     = '#ef4444';
        } elseif ( ! $key_saved ) {
            $reason    = sprintf(
                /* translators: %s: provider name */
                __( 'No %s API key found. Add your key in AI Settings to enable this feature.', 'gatetouch-ai-seo' ),
                $prov['name']
            );
            $sub       = esc_html( $prov['hint'] ) . ' — it\'s free to sign up.';
            $icon_name = 'key';
            $color     = '#f59e0b';
        } else {
            $reason    = sprintf(
                /* translators: %s: provider name */
                __( 'Your %s API key appears invalid. Please test and re-save it in AI Settings.', 'gatetouch-ai-seo' ),
                $prov['name']
            );
            $sub       = __( 'Check your billing credits and ensure the key hasn\'t been revoked.', 'gatetouch-ai-seo' );
            $icon_name = 'alert-triangle';
            $color     = '#f59e0b';
        }
        ?>
        <div style="max-width:600px; margin:60px auto; text-align:center; font-family:'Inter',-apple-system,sans-serif;">

            <!-- Icon -->
            <div style="width:80px; height:80px; background:<?php echo esc_attr( $color ); ?>20; border:2px solid <?php echo esc_attr( $color ); ?>40; border-radius:24px; display:flex; align-items:center; justify-content:center; color:<?php echo esc_attr( $color ); ?>; margin:0 auto 28px;">
                <?php echo wp_kses( self::icon( $icon_name, 40 ), self::svg_kses_allowed() ); ?>
            </div>

            <?php if ( $feature_name ) : ?>
            <div style="display:inline-flex; align-items:center; gap:6px; background:#f0f1fe; color:#4338ca; padding:6px 14px; border-radius:20px; font-size:12px; font-weight:700; margin-bottom:16px;">
                <?php echo wp_kses( self::provider_logo( $provider, 20 ), self::svg_kses_allowed() ); ?>
                <?php
                /* translators: %s: feature name */
                printf( esc_html__( '%s requires an AI connection', 'gatetouch-ai-seo' ), esc_html( $feature_name ) );
                ?>
            </div>
            <?php endif; ?>

            <h2 style="font-size:24px; font-weight:900; color:#1e293b; margin:0 0 12px;">
                <?php esc_html_e( 'API Key Required', 'gatetouch-ai-seo' ); ?>
            </h2>

            <p style="font-size:15px; color:#64748b; line-height:1.7; margin:0 0 8px; max-width:460px; margin-left:auto; margin-right:auto;">
                <?php echo esc_html( $reason ); ?>
            </p>
            <p style="font-size:13px; color:#94a3b8; margin:0 0 32px;">
                <?php echo esc_html( $sub ); ?>
            </p>

            <!-- Provider badge -->
            <div style="display:inline-flex; align-items:center; gap:10px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:12px 20px; margin-bottom:28px;">
                <?php echo wp_kses( self::provider_logo( $provider, 32 ), self::svg_kses_allowed() ); ?>
                <div style="text-align:left;">
                    <div style="font-weight:800; font-size:13px; color:#0f172a;"><?php echo esc_html( $prov['name'] ); ?></div>
                    <div style="font-size:11px; color:#64748b;"><?php esc_html_e( 'Current provider', 'gatetouch-ai-seo' ); ?></div>
                </div>
            </div>

            <!-- Action buttons -->
            <div style="display:flex; justify-content:center; gap:12px; flex-wrap:wrap;">
                <a href="<?php echo esc_url( $settings_url ); ?>"
                   style="display:inline-flex; align-items:center; gap:8px; background:linear-gradient(135deg,#6366f1,#a855f7); color:#fff; border:none; padding:13px 26px; border-radius:12px; font-size:14px; font-weight:800; text-decoration:none; box-shadow:0 8px 20px rgba(99,102,241,0.3);">
                    <?php echo wp_kses( self::icon( 'settings-2', 16 ), self::svg_kses_allowed() ); ?>
                </a>
                <a href="<?php echo esc_url( $prov['link'] ); ?>" target="_blank" rel="noopener"
                   style="display:inline-flex; align-items:center; gap:8px; background:#fff; color:#475569; border:1.5px solid #e2e8f0; padding:13px 22px; border-radius:12px; font-size:13px; font-weight:600; text-decoration:none;">
                    <?php echo wp_kses( self::provider_logo( $provider, 18 ), self::svg_kses_allowed() ); ?>
                    <?php
                    /* translators: %s: provider name */
                    printf( esc_html__( 'Get %s Key →', 'gatetouch-ai-seo' ), esc_html( $prov['name'] ) );
                    ?>
                </a>
            </div>

            <?php if ( $is_safe_mode ) : ?>
            <p style="margin-top:20px; font-size:12px; color:#94a3b8;">
                <?php esc_html_e( 'To exit safe mode: go to AI Settings and click "Reset Safe Mode".', 'gatetouch-ai-seo' ); ?>
            </p>
            <?php endif; ?>
        </div>
        <?php
    }
}
