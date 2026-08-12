<?php
defined( 'ABSPATH' ) || exit;

/**
 * Generates robots.txt and security.txt responses from GateTouch settings.
 */
class GateTouch_Robots {

    public function __construct() {
        add_action( 'init',              [ __CLASS__, 'register_rewrites' ] );
        // Primary: WordPress-standard hook. Fires when WP recognises the request as robots.txt.
        add_filter( 'robots_txt',        [ $this, 'generate' ], 99, 2 );
        add_filter( 'query_vars',        [ $this, 'add_query_vars' ] );
        // Fallback: for environments where WP doesn't recognise the request natively.
        add_action( 'template_redirect', [ $this, 'catch_robots' ], 1 );
        add_action( 'template_redirect', [ $this, 'serve_security_txt' ] );
    }

    /**
     * The AI crawler registry, split by what blocking a bot actually costs you.
     *
     * This distinction is the whole point: operators routinely block "AI bots"
     * as one undifferentiated group and silently remove themselves from AI
     * answers. The two groups behave completely differently.
     *
     *  - Citation crawlers fetch a page so an assistant can quote and link it in
     *    a live answer. Blocking one costs you visibility and returns nothing.
     *    These default to allowed.
     *  - Training crawlers collect corpora for model training. Blocking one has
     *    no effect on whether you are cited today. These are a genuine editorial
     *    choice, so they are surfaced but left allowed unless the site opts out.
     *
     * The pairing matters: GPTBot is OpenAI's *training* crawler, while
     * OAI-SearchBot and ChatGPT-User are what actually put you in a ChatGPT
     * answer. Blocking GPTBot alone does not remove you from ChatGPT; blocking
     * OAI-SearchBot does.
     *
     * @return array<string, array{label:string, description:string, bots:array}>
     */
    public static function ai_bots() {
        return [
            'citation' => [
                'label'       => __( 'AI search & citation crawlers', 'gatetouch-ai-seo' ),
                'description' => __( 'These fetch your pages so ChatGPT, Perplexity, Google AI Overviews and Copilot can quote and link you. Blocking one removes you from that engine\'s answers and gains you nothing in return.', 'gatetouch-ai-seo' ),
                'bots'        => [
                    'OAI-SearchBot'       => [ 'key' => 'allow_oai_searchbot', 'default' => 'yes', 'owner' => 'OpenAI',     'note' => __( 'Builds the index behind ChatGPT search. This is the one that decides whether ChatGPT can cite you.', 'gatetouch-ai-seo' ) ],
                    'ChatGPT-User'        => [ 'key' => 'allow_chatgpt_user', 'default' => 'yes', 'owner' => 'OpenAI',     'note' => __( 'Fetches a page live when a user asks ChatGPT about it.', 'gatetouch-ai-seo' ) ],
                    'PerplexityBot'       => [ 'key' => 'allow_perplexity',   'default' => 'yes', 'owner' => 'Perplexity', 'note' => __( 'Indexes pages for Perplexity answers.', 'gatetouch-ai-seo' ) ],
                    'Perplexity-User'     => [ 'key' => 'allow_perplexity_user', 'default' => 'yes', 'owner' => 'Perplexity', 'note' => __( 'Live fetch triggered by a Perplexity user.', 'gatetouch-ai-seo' ) ],
                    'ClaudeBot'           => [ 'key' => 'allow_claudebot',    'default' => 'yes', 'owner' => 'Anthropic',  'note' => __( 'Fetches pages for Claude\'s web features.', 'gatetouch-ai-seo' ) ],
                    'Google-Extended'     => [ 'key' => 'allow_google_ext',   'default' => 'yes', 'owner' => 'Google',     'note' => __( 'Controls Gemini and AI Overviews grounding. Blocking this does not affect normal Google Search ranking, but it does drop you from AI Overviews.', 'gatetouch-ai-seo' ) ],
                    'DuckAssistBot'       => [ 'key' => 'allow_duckassist',   'default' => 'yes', 'owner' => 'DuckDuckGo', 'note' => __( 'Powers DuckDuckGo\'s AI answers.', 'gatetouch-ai-seo' ) ],
                    'Amazonbot'           => [ 'key' => 'allow_amazonbot',    'default' => 'yes', 'owner' => 'Amazon',     'note' => __( 'Feeds Alexa and Rufus answers.', 'gatetouch-ai-seo' ) ],
                    'Applebot'            => [ 'key' => 'allow_applebot_search', 'default' => 'yes', 'owner' => 'Apple',   'note' => __( 'Powers Siri and Spotlight results.', 'gatetouch-ai-seo' ) ],
                    'MistralAI-User'      => [ 'key' => 'allow_mistral_user', 'default' => 'yes', 'owner' => 'Mistral',    'note' => __( 'Live fetch for Le Chat.', 'gatetouch-ai-seo' ) ],
                ],
            ],
            'training' => [
                'label'       => __( 'AI training crawlers', 'gatetouch-ai-seo' ),
                'description' => __( 'These collect content to train future models. Blocking them does not affect whether you are cited in AI answers today, so this is purely an editorial decision about your content being used as training data.', 'gatetouch-ai-seo' ),
                'bots'        => [
                    'GPTBot'              => [ 'key' => 'allow_gptbot',       'default' => 'yes', 'owner' => 'OpenAI',      'note' => __( 'Training crawler only. Blocking it does NOT remove you from ChatGPT answers — that is OAI-SearchBot above.', 'gatetouch-ai-seo' ) ],
                    'CCBot'               => [ 'key' => 'allow_ccbot',        'default' => 'yes', 'owner' => 'Common Crawl', 'note' => __( 'Open crawl corpus used by many model builders.', 'gatetouch-ai-seo' ) ],
                    'anthropic-ai'        => [ 'key' => 'allow_anthropic_ai', 'default' => 'yes', 'owner' => 'Anthropic',   'note' => __( 'Legacy training agent.', 'gatetouch-ai-seo' ) ],
                    'meta-externalagent'  => [ 'key' => 'allow_meta_ai',      'default' => 'yes', 'owner' => 'Meta',        'note' => __( 'Trains Meta AI / Llama models.', 'gatetouch-ai-seo' ) ],
                    'Applebot-Extended'   => [ 'key' => 'allow_applebot',     'default' => 'yes', 'owner' => 'Apple',       'note' => __( 'Opt-out for Apple Intelligence training. Separate from Applebot above, which is search.', 'gatetouch-ai-seo' ) ],
                    'cohere-ai'           => [ 'key' => 'allow_cohere',       'default' => 'yes', 'owner' => 'Cohere',      'note' => __( 'Trains Cohere models.', 'gatetouch-ai-seo' ) ],
                    'Bytespider'          => [ 'key' => 'allow_bytespider',   'default' => 'no',  'owner' => 'ByteDance',   'note' => __( 'Aggressive crawler with a poor rate-limit record. Blocked by default.', 'gatetouch-ai-seo' ) ],
                ],
            ],
            'seo_tools' => [
                'label'       => __( 'SEO tool crawlers', 'gatetouch-ai-seo' ),
                'description' => __( 'Third-party backlink and audit crawlers. Blocking them hides your site from competitor research tools but has no effect on search or AI visibility.', 'gatetouch-ai-seo' ),
                'bots'        => [
                    'AhrefsBot'  => [ 'key' => 'allow_ahrefs',  'default' => 'yes', 'owner' => 'Ahrefs',  'note' => '' ],
                    'SemrushBot' => [ 'key' => 'allow_semrush', 'default' => 'yes', 'owner' => 'Semrush', 'note' => '' ],
                ],
            ],
        ];
    }

    /**
     * Flat map of every known AI/tool crawler to its option key and default.
     *
     * @return array<string, array{key:string, default:string}>
     */
    public static function ai_bot_defaults() {
        $flat = [];
        foreach ( self::ai_bots() as $group_key => $group ) {
            foreach ( $group['bots'] as $bot_name => $bot ) {
                $flat[ $bot['key'] ] = [ 'bot' => $bot_name, 'group' => $group_key, 'default' => $bot['default'] ];
            }
        }
        return $flat;
    }

    /**
     * Register rewrite rules for plain-text discovery files.
     *
     * @return void
     */
    public static function register_rewrites() {
        add_rewrite_rule( '^robots\.txt$', 'index.php?robots=1', 'top' );
        add_rewrite_rule( '^security\.txt$', 'index.php?gatetouch_security_txt=1', 'top' );
        add_rewrite_rule( '^\.well-known/security\.txt$', 'index.php?gatetouch_security_txt=1', 'top' );
    }

    /**
     * Add custom query vars used by GateTouch rewrite rules.
     *
     * @param array $vars Public query vars.
     * @return array
     */
    public function add_query_vars( $vars ) {
        $vars[] = 'gatetouch_security_txt';
        return $vars;
    }

    public function generate( $output, $public ) {
        $opts = get_option( 'gatetouch_robots_settings', $this->defaults() );

        // If Robots.txt Optimization is disabled, do not override
        if ( isset( $opts['optimize'] ) && $opts['optimize'] === 'no' ) {
            return $output;
        }

        $lines = [];
        $lines[] = '# GT SEO/GEO/AEO — robots.txt';
        $lines[] = '# Generated: ' . gmdate( 'Y-m-d H:i:s' ) . ' UTC';
        $lines[] = '';

        $mode = $opts['robots_mode'] ?? 'auto';

        if ( $mode === 'custom' ) {
            if ( ! empty( $opts['robots_custom'] ) ) {
                $lines[] = trim( $opts['robots_custom'] );
                $lines[] = '';
            }
        } else {
            // Default rules
            if ( ! empty( $opts['custom_rules'] ) ) {
                // Fallback for legacy structured custom rules
                foreach ( $opts['custom_rules'] as $rule ) {
                    $lines[] = 'User-agent: ' . $rule['ua'];
                    $lines[] = ucfirst( $rule['dir'] ) . ': ' . $rule['val'];
                    $lines[] = '';
                }
            } else {
                $lines[] = 'User-agent: *';
                $lines[] = 'Disallow: /wp-admin/';
                $lines[] = 'Allow: /wp-admin/admin-ajax.php';
                if ( ! empty( $opts['block_internal_search'] ) ) {
                    $lines[] = 'Disallow: /?s=';
                    $lines[] = 'Disallow: /search/';
                }
                $lines[] = '';
            }

            // AI bots. Citation crawlers and training crawlers are governed
            // separately — see self::ai_bots() for why that distinction matters.
            $blocked_bots = [];
            foreach ( self::ai_bots() as $group ) {
                foreach ( $group['bots'] as $bot_name => $bot ) {
                    $allowed = ( $opts[ $bot['key'] ] ?? $bot['default'] ) === 'yes';
                    if ( ! $allowed ) {
                        $blocked_bots[] = $bot_name;
                    }
                }
            }

            if ( ! empty( $blocked_bots ) ) {
                $lines[] = '# Blocked AI crawlers';
                foreach ( $blocked_bots as $bot ) {
                    $lines[] = "User-agent: {$bot}";
                    $lines[] = "Disallow: /";
                    $lines[] = "";
                }
            }

            if ( class_exists( 'GateTouch_Crawl_Optimization' ) ) {
                $crawl_groups = GateTouch_Crawl_Optimization::robots_disallow_groups();
                if ( ! empty( $crawl_groups ) ) {
                    $crawl_lines = [];
                    foreach ( $crawl_groups as $agent => $paths ) {
                        if ( in_array( $agent, $blocked_bots, true ) ) {
                            continue;
                        }
                        $crawl_lines[] = 'User-agent: ' . $agent;
                        foreach ( $paths as $path ) {
                            $crawl_lines[] = 'Disallow: ' . $path;
                        }
                        $crawl_lines[] = '';
                    }
                    if ( ! empty( $crawl_lines ) ) {
                        $lines[] = '# Crawl Optimization Rules';
                        $lines   = array_merge( $lines, $crawl_lines );
                    }
                }
            }
        }

        // Sitemaps
        $sitemap_opts    = get_option( 'gatetouch_sitemap_settings', [] );
        $sitemap_raw     = $sitemap_opts['enabled'] ?? null;
        $sitemap_enabled = ( $sitemap_raw === null ) || ! in_array( $sitemap_raw, [ 'no', '0', '', false ], true );
        if ( $sitemap_enabled ) {
            $lines[] = 'Sitemap: ' . home_url( '/sitemap.xml' );
            if ( in_array( $sitemap_opts['rss_sitemap']['enabled'] ?? 'no', [ 'yes', '1' ], true ) ) {
                $lines[] = 'Sitemap: ' . home_url( '/sitemap-rss.xml' );
            }
        }

        return implode( "\n", $lines );
    }

    /**
     * Fallback for environments where WP doesn't natively recognise the robots.txt request.
     * WP 5.7+ handles robots.txt via is_robots() → do_robots → robots_txt filter.
     * This only fires when that chain doesn't activate (e.g. plain-permalink setups).
     *
     * @return void
     */
    public function catch_robots() {
        // If WP already recognised this as a robots request, its do_robots() will
        // call our generate() via the robots_txt filter. Don't double-handle it.
        if ( is_robots() ) return;

        $uri  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
        $path = strtok( $uri, '?' );

        if ( ! preg_match( '/robots\.txt$/i', $path ) ) return;

        nocache_headers();
        header( 'Content-Type: text/plain; charset=UTF-8' );
        header( 'X-Robots-Tag: noindex, follow' );

        $public = ( '1' === get_option( 'blog_public' ) );
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- robots.txt is plain text, not HTML.
        echo $this->generate( '', $public );
        exit;
    }

    private function defaults() {
        return [
            'robots_mode'      => 'auto',
            'robots_custom'    => '',
            'crawl_delay'      => 1,
            'optimize'         => 'yes',
            'allow_gptbot'     => 'yes',
            'allow_claudebot'  => 'yes',
            'allow_perplexity' => 'yes',
            'allow_google_ext' => 'yes',
            'allow_ccbot'      => 'yes',
            'allow_meta_ai'    => 'yes',
            'allow_applebot'   => 'yes',
            'allow_bytespider' => 'no',
            'allow_ahrefs'     => 'yes',
            'allow_semrush'    => 'yes',
        ];
    }

    public function serve_security_txt() {
        $uri               = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
        $request_path      = strtok( $uri, '?' );
        $security_path     = wp_parse_url( home_url( '/security.txt' ), PHP_URL_PATH );
        $well_known_path   = wp_parse_url( home_url( '/.well-known/security.txt' ), PHP_URL_PATH );
        $valid_paths       = array_filter( [ $security_path, $well_known_path, '/security.txt', '/.well-known/security.txt' ] );
        $security_txt_file = (bool) get_query_var( 'gatetouch_security_txt' );

        if ( ! $security_txt_file && ! in_array( $request_path, $valid_paths, true ) ) return;

        $opts = get_option( 'gatetouch_security_txt_settings', [] );
        if ( empty( $opts['enable'] ) ) {
            status_header( 404 );
            exit;
        }

        nocache_headers();
        header( 'Content-Type: text/plain; charset=UTF-8' );

        $lines = [ '# security.txt — https://securitytxt.org' ];
        if ( ! empty( $opts['contact'] ) )             $lines[] = 'Contact: ' . $opts['contact'];
        if ( ! empty( $opts['expires'] ) )             $lines[] = 'Expires: ' . $opts['expires'];
        if ( ! empty( $opts['policy'] ) )              $lines[] = 'Policy: ' . $opts['policy'];
        if ( ! empty( $opts['preferred_languages'] ) ) $lines[] = 'Preferred-Languages: ' . $opts['preferred_languages'];
        $lines[] = 'Canonical: ' . home_url( '/.well-known/security.txt' );

        echo esc_html( implode( "\n", $lines ) );
        exit;
    }
}
