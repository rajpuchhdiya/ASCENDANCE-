<?php
defined( 'ABSPATH' ) || exit;

/**
 * Routes AI requests across providers and sanitizes AI-generated SEO output.
 */
class GateTouch_AI_Engine {

    const PROVIDERS = [
        'openai'    => 'OpenAI (GPT-4o)',
        'anthropic' => 'Anthropic (Claude)',
        'gemini'    => 'Google Gemini',
    ];

    /**
     * Get the active provider slug.
     */
    public static function get_provider() {
        return get_option( 'gatetouch_ai_provider', 'openai' );
    }

    /**
     * Provider-agnostic call. Routes to the correct backend.
     */
    public static function call(
        $system,
        $user,
        $model    = '',
        float $temp        = 0.7,
        int   $max_tokens  = 1200,
        int   $retries     = 2
    ) {
        if ( ! self::is_api_operational() ) {
            return [ 'error' => __( 'AI API is not configured or is in Safe Mode due to repeated failures.', 'gatetouch-ai-seo' ) ];
        }

        $provider = self::get_provider();

        switch ( $provider ) {
            case 'anthropic':
                return self::call_anthropic( $system, $user, $model, $temp, $max_tokens, $retries );
            case 'gemini':
                return self::call_gemini( $system, $user, $model, $temp, $max_tokens, $retries );
            default:
                return self::call_openai( $system, $user, $model, $temp, $max_tokens, $retries );
        }
    }

    // ── OpenAI ────────────────────────────────────────────────────────────────

    private static function call_openai( $system, $user, $model, $temp, $max_tokens, $retries ) {
        $api_key = self::get_key( 'openai' );
        if ( ! $api_key ) return [ 'error' => __( 'OpenAI API key not configured.', 'gatetouch-ai-seo' ) ];

        $model   = $model ?: get_option( 'gatetouch_ai_model', 'gpt-4o' );
        $attempt = 0;
        $last_error = '';

        while ( $attempt <= $retries ) {
            $response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', [
                'timeout'   => 60,
                'sslverify' => true,
                'headers'   => [
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type'  => 'application/json',
                ],
                'body' => wp_json_encode( [
                    'model'           => $model,
                    'messages'        => [
                        [ 'role' => 'system', 'content' => $system ],
                        [ 'role' => 'user',   'content' => $user ],
                    ],
                    'temperature'     => $temp,
                    'max_tokens'      => $max_tokens,
                    'response_format' => [ 'type' => 'json_object' ],
                ] ),
            ] );

            if ( is_wp_error( $response ) ) {
                $last_error = $response->get_error_message();
                GateTouch_Logger::error( 'OpenAI Connection Error: ' . $last_error );
                $attempt++;
                continue;
            }

            $code = wp_remote_retrieve_response_code( $response );
            $body = json_decode( wp_remote_retrieve_body( $response ), true );

            if ( $code === 429 ) {
                $last_error = 'OpenAI rate limit reached or quota exceeded.';
                self::log_api_failure( $last_error );
                break;
            }
            if ( $code === 401 ) {
                self::mark_api_invalid();
                return [ 'error' => 'Invalid OpenAI API key.' ];
            }
            if ( $code !== 200 ) {
                $attempt++;
                continue;
            }

            $usage = $body['usage'] ?? [];
            if ( ! empty( $usage ) ) {
                self::track_usage( $usage, $model );
            }

            $content = $body['choices'][0]['message']['content'] ?? null;
            if ( ! $content ) {
                $last_error = 'Empty response from OpenAI.';
                $attempt++;
                continue;
            }

            $parsed = json_decode( $content, true );
            if ( json_last_error() !== JSON_ERROR_NONE ) {
                $last_error = 'AI returned invalid JSON.';
                GateTouch_Logger::error( 'OpenAI invalid JSON: ' . $content );
                $attempt++;
                continue;
            }

            return self::validate_ai_response( $parsed );
        }

        return [ 'error' => $last_error ?: 'AI generation failed after retries.' ];
    }

    // ── Anthropic / Claude ────────────────────────────────────────────────────

    private static function call_anthropic( $system, $user, $model, $temp, $max_tokens, $retries ) {
        $api_key = self::get_key( 'anthropic' );
        if ( ! $api_key ) return [ 'error' => __( 'Anthropic API key not configured.', 'gatetouch-ai-seo' ) ];

        if ( ! $model || strpos( $model, 'gpt' ) !== false ) {
            $saved = get_option( 'gatetouch_ai_model', '' );
            $model = ( $saved && strpos( $saved, 'gpt' ) === false ) ? $saved : 'claude-sonnet-4-6';
        }

        $attempt    = 0;
        $last_error = '';

        while ( $attempt <= $retries ) {
            $response = wp_remote_post( 'https://api.anthropic.com/v1/messages', [
                'timeout'   => 60,
                'sslverify' => true,
                'headers'   => [
                    'x-api-key'         => $api_key,
                    'anthropic-version' => '2023-06-01',
                    'Content-Type'      => 'application/json',
                ],
                'body' => wp_json_encode( [
                    'model'      => $model,
                    'max_tokens' => $max_tokens,
                    'system'     => $system . ' You MUST respond with a valid JSON object only. No markdown, no explanations.',
                    'messages'   => [
                        [ 'role' => 'user', 'content' => $user ],
                    ],
                    'temperature' => $temp,
                ] ),
            ] );

            if ( is_wp_error( $response ) ) {
                $last_error = $response->get_error_message();
                GateTouch_Logger::error( 'Anthropic Connection Error: ' . $last_error );
                $attempt++;
                continue;
            }

            $code = wp_remote_retrieve_response_code( $response );
            $body = json_decode( wp_remote_retrieve_body( $response ), true );

            if ( $code === 429 ) {
                $last_error = 'Anthropic rate limit reached.';
                self::log_api_failure( $last_error );
                break;
            }
            if ( $code === 401 ) {
                self::mark_api_invalid();
                return [ 'error' => 'Invalid Anthropic API key.' ];
            }
            if ( $code !== 200 ) {
                $attempt++;
                continue;
            }

            $content = $body['content'][0]['text'] ?? null;
            if ( ! $content ) {
                $last_error = 'Empty response from Anthropic.';
                $attempt++;
                continue;
            }

            // Strip markdown code fences if present
            $content = preg_replace( '/^```json\s*|\s*```$/is', '', trim( $content ) );

            $parsed = json_decode( $content, true );
            if ( json_last_error() !== JSON_ERROR_NONE ) {
                $last_error = 'Anthropic returned invalid JSON.';
                GateTouch_Logger::error( 'Anthropic invalid JSON: ' . $content );
                $attempt++;
                continue;
            }

            // Track approximate usage
            $usage = [
                'total_tokens' => ( $body['usage']['input_tokens'] ?? 0 ) + ( $body['usage']['output_tokens'] ?? 0 ),
            ];
            self::track_usage( $usage, $model );

            return self::validate_ai_response( $parsed );
        }

        return [ 'error' => $last_error ?: 'Anthropic AI generation failed after retries.' ];
    }

    // ── Google Gemini ─────────────────────────────────────────────────────────

    private static function call_gemini( $system, $user, $model, $temp, $max_tokens, $retries ) {
        $api_key = self::get_key( 'gemini' );
        if ( ! $api_key ) return [ 'error' => __( 'Google Gemini API key not configured.', 'gatetouch-ai-seo' ) ];

        if ( ! $model || strpos( $model, 'gpt' ) !== false ) {
            $saved = get_option( 'gatetouch_ai_model', '' );
            $model = ( $saved && strpos( $saved, 'gemini' ) !== false ) ? $saved : 'gemini-1.5-pro';
        }

        $attempt    = 0;
        $last_error = '';

        while ( $attempt <= $retries ) {
            $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $api_key;

            $response = wp_remote_post( $endpoint, [
                'timeout'   => 60,
                'sslverify' => true,
                'headers'   => [ 'Content-Type' => 'application/json' ],
                'body'      => wp_json_encode( [
                    'system_instruction' => [
                        'parts' => [ [ 'text' => $system . ' Respond ONLY with a valid JSON object.' ] ],
                    ],
                    'contents' => [
                        [ 'role' => 'user', 'parts' => [ [ 'text' => $user ] ] ],
                    ],
                    'generationConfig' => [
                        'temperature'     => $temp,
                        'maxOutputTokens' => $max_tokens,
                        'responseMimeType' => 'application/json',
                    ],
                ] ),
            ] );

            if ( is_wp_error( $response ) ) {
                $last_error = $response->get_error_message();
                GateTouch_Logger::error( 'Gemini Connection Error: ' . $last_error );
                $attempt++;
                continue;
            }

            $code = wp_remote_retrieve_response_code( $response );
            $body = json_decode( wp_remote_retrieve_body( $response ), true );

            if ( $code === 429 ) {
                $last_error = 'Google Gemini rate limit reached.';
                self::log_api_failure( $last_error );
                break;
            }
            if ( $code === 401 || $code === 403 ) {
                self::mark_api_invalid();
                return [ 'error' => 'Invalid Google Gemini API key.' ];
            }
            if ( $code !== 200 ) {
                $attempt++;
                continue;
            }

            $content = $body['candidates'][0]['content']['parts'][0]['text'] ?? null;
            if ( ! $content ) {
                $last_error = 'Empty response from Gemini.';
                $attempt++;
                continue;
            }

            $content = preg_replace( '/^```json\s*|\s*```$/is', '', trim( $content ) );

            $parsed = json_decode( $content, true );
            if ( json_last_error() !== JSON_ERROR_NONE ) {
                $last_error = 'Gemini returned invalid JSON.';
                $attempt++;
                continue;
            }

            $usage_meta = $body['usageMetadata'] ?? [];
            self::track_usage( [
                'total_tokens' => ( $usage_meta['promptTokenCount'] ?? 0 ) + ( $usage_meta['candidatesTokenCount'] ?? 0 ),
            ], $model );

            return self::validate_ai_response( $parsed );
        }

        return [ 'error' => $last_error ?: 'Gemini AI generation failed after retries.' ];
    }

    /**
     * AI VALIDATION LAYER: Sanitize and validate AI output before using it.
     */
    private static function validate_ai_response( $data ) {
        $clean = [];

        foreach ( $data as $key => $value ) {
            $key = sanitize_key( $key );

            if ( is_string( $value ) ) {
                $value = preg_replace( '/^```json\s*|```$/i', '', $value );
                $value = trim( $value );

                if ( 'meta_title' === $key ) {
                    $value = mb_substr( $value, 0, 60 );
                }
                if ( 'meta_description' === $key ) {
                    $value = mb_substr( $value, 0, 160 );
                }

                $clean[ $key ] = sanitize_text_field( $value );
            } elseif ( is_array( $value ) ) {
                $clean[ $key ] = self::recursive_sanitize( $value );
            } else {
                $clean[ $key ] = $value;
            }
        }

        return $clean;
    }

    private static function recursive_sanitize( $array ) {
        foreach ( $array as $k => $v ) {
            if ( is_array( $v ) ) {
                $array[ $k ] = self::recursive_sanitize( $v );
            } elseif ( is_string( $v ) ) {
                $array[ $k ] = sanitize_text_field( $v );
            }
        }
        return $array;
    }

    private static function normalize_text( $text ) {
        $text = html_entity_decode( wp_strip_all_tags( (string) $text ), ENT_QUOTES, get_bloginfo( 'charset' ) );
        $text = function_exists( 'remove_accents' ) ? remove_accents( $text ) : $text;
        $text = mb_strtolower( $text );
        $text = preg_replace( '/[^\p{L}\p{N}]+/u', ' ', $text );
        return trim( preg_replace( '/\s+/u', ' ', $text ) );
    }

    private static function keyword_matches( $haystack, $keyword ) {
        $keyword_norm = self::normalize_text( $keyword );
        if ( $keyword_norm === '' ) {
            return false;
        }

        $haystack_norm = ' ' . self::normalize_text( $haystack ) . ' ';
        if ( preg_match( '/\s' . preg_quote( $keyword_norm, '/' ) . '\s/u', $haystack_norm ) ) {
            return true;
        }

        $terms = array_values( array_filter( array_unique( preg_split( '/\s+/u', $keyword_norm, -1, PREG_SPLIT_NO_EMPTY ) ), function( $term ) {
            return mb_strlen( $term ) > 2 || is_numeric( $term ) || in_array( $term, [ 'ai', 'ui', 'ux' ], true );
        } ) );

        if ( empty( $terms ) ) {
            return false;
        }

        $matched = 0;
        foreach ( $terms as $term ) {
            if ( preg_match( '/\s' . preg_quote( $term, '/' ) . '\s/u', $haystack_norm ) ) {
                $matched++;
            }
        }

        return ( $matched / count( $terms ) ) >= ( count( $terms ) <= 2 ? 1 : 0.8 );
    }

    private static function derive_focus_keyword( WP_Post $post, $content = '' ) {
        $title = wp_strip_all_tags( $post->post_title );
        $title = preg_replace( '/[:|–—-].*$/u', '', $title );
        $title = self::normalize_text( $title );

        $stopwords = [
            'a', 'an', 'and', 'are', 'as', 'at', 'be', 'but', 'by', 'for', 'from', 'how',
            'in', 'into', 'is', 'of', 'on', 'or', 'the', 'to', 'vs', 'with', 'without',
            'top', 'best', 'ultimate', 'complete', 'guide', 'hidden', 'costs',
        ];

        $terms = preg_split( '/\s+/u', $title, -1, PREG_SPLIT_NO_EMPTY );
        $terms = array_values( array_filter( $terms, function( $term ) use ( $stopwords ) {
            return ! in_array( $term, $stopwords, true ) && ( mb_strlen( $term ) > 2 || is_numeric( $term ) || in_array( $term, [ 'ai', 'ui', 'ux' ], true ) );
        } ) );

        if ( count( $terms ) < 2 && $content !== '' ) {
            $content_terms = preg_split( '/\s+/u', self::normalize_text( $content ), -1, PREG_SPLIT_NO_EMPTY );
            foreach ( $content_terms as $term ) {
                if ( ! in_array( $term, $stopwords, true ) && mb_strlen( $term ) > 3 ) {
                    $terms[] = $term;
                }
                if ( count( $terms ) >= 4 ) {
                    break;
                }
            }
        }

        $terms = array_slice( array_unique( $terms ), 0, 6 );
        return trim( implode( ' ', $terms ) );
    }

    private static function fallback_secondary_keywords( $focus_keyword, WP_Post $post ) {
        $base = trim( $focus_keyword );
        if ( $base === '' ) {
            return [];
        }

        $year = gmdate( 'Y' );
        $post_type = get_post_type_object( $post->post_type );
        $type_label = $post_type ? strtolower( $post_type->labels->singular_name ) : 'content';

        return array_values( array_unique( [
            $base . ' guide',
            'best ' . $base,
            $base . ' ' . $year,
            $base . ' ' . $type_label,
        ] ) );
    }

    /**
     * Generate full SEO meta for a single post.
     */
    public static function generate_meta( $post_id ) {
        $post = get_post( $post_id );
        if ( ! $post ) return [ 'error' => 'Post not found.' ];

        $content = $post->post_content;
        if ( empty( $content ) || trim( wp_strip_all_tags( $content ) ) === '' ) {
            $content = GateTouch_Analysis::capture_rendered_template( $post_id );
        }

        $content   = GateTouch_Helpers::clean_meta_text( $content );
        $content   = substr( $content, 0, 8000 );
        $post_type = get_post_type_object( $post->post_type );
        $pt_label  = $post_type ? $post_type->labels->singular_name : 'Post';

        preg_match_all( '/<h([1-6])[^>]*>(.*?)<\/h\1>/is', $post->post_content, $headings );
        $h_structure = '';
        if ( ! empty( $headings[1] ) ) {
            foreach ( $headings[1] as $i => $level ) {
                $h_structure .= "H{$level}: " . wp_strip_all_tags( $headings[2][ $i ] ) . "\n";
            }
        }

        $cats = wp_get_post_categories( $post_id, [ 'fields' => 'names' ] );
        $tags = wp_get_post_tags( $post_id, [ 'fields' => 'names' ] );

        $current_year = gmdate( 'Y' );

        $system = "You are a senior SEO strategist and keyword research expert with deep expertise in Google Search trends, AEO (Answer Engine Optimization), and GEO (Generative Engine Optimization). You think like a professional who has access to Google Trends, SEMrush, and Ahrefs data. You understand: commercial intent vs informational intent, trending search modifiers, long-tail keyword opportunities, semantic clusters, and how to pick keywords that have HIGH search volume but are still winnable for a new page. IMPORTANT: Respond ONLY with a valid JSON object. No markdown, no explanations, no code fences.";

        $title_derived_keyword = self::derive_focus_keyword( $post, $content );

        $user = "Analyze this {$pt_label} thoroughly and generate market-trend-aware, high-performance SEO metadata and keyword strategy.

=== PAGE DATA ===
Post Title: {$post->post_title}
Title-Derived Keyword Candidate: {$title_derived_keyword}
Post Type: {$pt_label}
Categories: " . implode( ', ', (array) $cats ) . '
Tags: ' . implode( ', ', (array) $tags ) . "
Heading Structure:
{$h_structure}

Full Content (HTML stripped):
{$content}

=== KEYWORD STRATEGY RULES ===

FOCUS KEYWORD — Pick the single BEST keyword for this page by applying all 4 filters:
  (a) RELEVANCE: Must directly match the core topic of the content above.
  (a2) TITLE ALIGNMENT: If the Post Title contains a clear search phrase, use that title-derived phrase or a very close variant unless the content strongly contradicts it.
  (b) SEARCH VOLUME: Choose a keyword that gets meaningful monthly searches (think: would this appear in SEMrush/Ahrefs with volume?).
  (c) TREND: Prefer keywords that are growing in {$current_year} — consider \"best\", \"how to\", \"[topic] for beginners\", \"[topic] {$current_year}\", or AI/automation variants if relevant.
  (d) WINNABLE: Prefer 2–4 word phrases over single broad terms — they convert better and rank faster.
  Example of a GOOD focus keyword: \"best WordPress SEO plugin\" or \"AI SEO tools {$current_year}\"
  Example of a BAD focus keyword: \"SEO\" (too broad) or \"plugin\" (no intent).

SECONDARY KEYWORDS — Choose exactly 4, each serving a different purpose:
  [0] A close synonym or variant of the focus keyword (e.g. \"WordPress SEO optimization\")
  [1] A long-tail question keyword starting with \"how to\", \"what is\", or \"best way to\" (4-6 words)
  [2] A trending modifier keyword: add \"best\", \"top\", \"free\", \"AI-powered\", or \"{$current_year}\" to the topic
  [3] A buyer-intent keyword: includes \"buy\", \"plugin\", \"tool\", \"software\", \"service\", or \"compare\"
  All 4 must be realistically searched AND present or clearly implied by the content.

=== OUTPUT RULES ===

meta_title:
  • MUST contain the exact focus_keyword
  • MUST be between 50 and 60 characters — count each character carefully, aim for 57
  • Include a power word (Best, Top, Expert, Ultimate, Proven, Complete, Fast)
  • Format: [Power Word] [Focus Keyword]: [Benefit] — or — [Focus Keyword] | [Power Word] [Short Benefit]

meta_description:
  • MUST contain the focus_keyword in the first 20 words
  • MUST be between 145 and 160 characters — count each character carefully, aim for 155
  • Structure: [Hook with keyword] + [Value proposition] + [CTA]
  • Example structure: \"Discover the best [keyword] to [benefit]. [What user gets]. [Action phrase] today.\"

post_title_suggestion:
  • A natural, click-worthy WordPress post title containing the focus_keyword
  • Different from meta_title — more conversational, suits the blog/CMS context
  • Can be longer than 60 chars

slug_suggestion:
  • Lowercase, hyphenated, focus_keyword based
  • Remove stop words (a, an, the, for, to, in, with)
  • Max 5-6 words

og_title / twitter_title: Include focus_keyword, 55–70 chars, social-friendly hook
og_description / twitter_description: Include focus_keyword, 120–150 chars, shareable value statement

schema_type: Choose the most specific applicable type: Article, HowTo, FAQPage, Product, Review, BreadcrumbList, WebPage, BlogPosting, Course, Recipe, Event, NewsArticle

image_alt_suggestion: Natural sentence describing an ideal featured image, includes focus_keyword

missing_topics: 2 high-value subtopics NOT covered in the content that searchers of this keyword would expect
improvement_tips: 2 specific, actionable content improvements to increase rankings for this keyword

Return JSON with EXACTLY these keys:
{
  \"search_intent\": \"string (Informational | Commercial | Transactional | Navigational)\",
  \"intent_explanation\": \"string\",
  \"intent_match_score\": integer,
  \"focus_keyword\": \"string\",
  \"meta_title\": \"string\",
  \"meta_description\": \"string\",
  \"post_title_suggestion\": \"string\",
  \"secondary_keywords\": [\"string\", \"string\", \"string\", \"string\"],
  \"slug_suggestion\": \"string\",
  \"og_title\": \"string\",
  \"og_description\": \"string\",
  \"twitter_title\": \"string\",
  \"twitter_description\": \"string\",
  \"schema_type\": \"string\",
  \"image_alt_suggestion\": \"string\",
  \"missing_topics\": [\"string\", \"string\"],
  \"improvement_tips\": [\"string\", \"string\"]
}";

        $result = self::call( $system, $user );

        // Enforce length requirements — AI frequently under-shoots despite prompt instructions.
        if ( ! isset( $result['error'] ) ) {
            $kw = trim( (string) ( $result['focus_keyword'] ?? '' ) );
            $source_context = $post->post_title . ' ' . $content;

            if ( $kw === '' || ! self::keyword_matches( $source_context, $kw ) ) {
                $kw = $title_derived_keyword ?: $kw;
                if ( $kw !== '' ) {
                    $result['focus_keyword'] = $kw;
                }
            }

            if ( empty( $result['secondary_keywords'] ) || ! is_array( $result['secondary_keywords'] ) ) {
                $result['secondary_keywords'] = self::fallback_secondary_keywords( $kw, $post );
            }

            if ( empty( $result['post_title_suggestion'] ) || ( $kw && ! self::keyword_matches( $result['post_title_suggestion'], $kw ) ) ) {
                $result['post_title_suggestion'] = self::keyword_matches( $post->post_title, $kw )
                    ? $post->post_title
                    : ucwords( $kw ) . ': Expert Guide';
            }

            if ( ! empty( $result['meta_title'] ) ) {
                $result['meta_title'] = self::enforce_title_length( $result['meta_title'], $kw );
            }
            if ( ! empty( $result['meta_description'] ) ) {
                $result['meta_description'] = self::enforce_desc_length( $result['meta_description'] );
            }
        }

        return $result;
    }

    /**
     * Pad/trim meta title to 50–60 chars while keeping the focus keyword present.
     */
    private static function enforce_title_length( $title, $kw ) {
        $title = trim( $title );
        $len   = mb_strlen( $title );

        // Trim if over 60
        if ( $len > 60 ) {
            $title = mb_substr( $title, 0, 57 ) . '...';
            return $title;
        }

        // Pad if under 50 — append useful phrases until we hit the zone
        $pads = [
            ' – Learn More',
            ' | Expert Guide',
            ' – Best Practices',
            ' | Pro Tips',
            ' – Full Guide',
            ' | Top Strategies',
            ' – Complete Overview',
        ];
        foreach ( $pads as $pad ) {
            if ( $len >= 50 ) break;
            $candidate = $title . $pad;
            if ( mb_strlen( $candidate ) <= 60 ) {
                $title = $candidate;
                $len   = mb_strlen( $title );
            }
        }

        // Last-resort: append chars from the keyword if still short
        if ( $len < 50 && $kw ) {
            $extra = ' – ' . ucwords( $kw );
            $candidate = mb_substr( $title . $extra, 0, 60 );
            if ( mb_strlen( $candidate ) >= 50 ) {
                $title = $candidate;
            }
        }

        return $title;
    }

    /**
     * Pad/trim meta description to 145–160 chars while keeping the focus keyword present.
     */
    private static function enforce_desc_length( $desc ) {
        $desc = trim( $desc );

        // Pad if under 145 — append CTA sentences until we land in zone
        $ctas = [
            ' Discover how to boost results and drive more organic traffic today.',
            ' Start optimizing now and see measurable ranking improvements in days.',
            ' Learn proven strategies to grow your online presence and visibility.',
            ' Get started today and take your site performance to the next level.',
            ' Find out what works and apply expert tactics with confidence.',
        ];

        foreach ( $ctas as $cta ) {
            if ( mb_strlen( $desc ) >= 145 ) break;
            $base      = rtrim( $desc, '.,!? ' );
            $candidate = $base . $cta;
            if ( mb_strlen( $candidate ) <= 160 ) {
                $desc = $candidate;
            } else {
                // Fit as many words of the CTA as possible without exceeding 160
                $available = 160 - mb_strlen( $base ) - 1; // -1 for trailing period
                $chunk     = mb_substr( $cta, 0, max( 0, $available ) );
                $last_sp   = mb_strrpos( $chunk, ' ' );
                if ( $last_sp !== false && $last_sp > 0 ) {
                    $chunk = mb_substr( $chunk, 0, $last_sp );
                }
                $desc = $base . rtrim( $chunk ) . '.';
                break;
            }
        }

        // Final hard trim — must never exceed 160 chars
        if ( mb_strlen( $desc ) > 160 ) {
            $desc = mb_substr( $desc, 0, 159 );
            $last = mb_strrpos( $desc, ' ' );
            if ( $last > 140 ) {
                $desc = mb_substr( $desc, 0, $last );
            }
            $desc = rtrim( $desc, ' .,!?' ) . '.';
        }

        return trim( $desc );
    }

    /**
     * Improve / rewrite existing meta.
     */
    public static function improve_meta( $post_id, $existing ) {
        $post    = get_post( $post_id );
        $content = $post->post_content;
        if ( empty( $content ) || trim( wp_strip_all_tags( $content ) ) === '' ) {
            $content = GateTouch_Analysis::capture_rendered_template( $post_id );
        }
        $content = GateTouch_Helpers::clean_meta_text( $content );
        $content = substr( $content, 0, 2000 );

        $system = 'You are an expert SEO copywriter specializing in SEO, AEO, and GEO optimization. Rewrite and improve existing meta tags to be more compelling, keyword-optimized, and authoritative. Respond ONLY with valid JSON.';
        $user   = "Post: {$post->post_title}
Current meta_title: {$existing['meta_title']}
Current meta_description: {$existing['meta_description']}
Content snippet: {$content}

STRICT SEO, AEO, AND GEO REQUIREMENTS:
1. meta_title: MUST be strictly between 50 and 60 characters long. Do not write less than 50 or more than 60 characters. Aim for 55 characters.
2. meta_description: MUST be strictly between 145 and 160 characters long. Do not write less than 145 or more than 160 characters. Aim for 152 characters.

Return JSON with EXACTLY these keys:
{
  \"meta_title\": \"string (MUST be strictly between 50 and 60 characters)\",
  \"meta_description\": \"string (MUST be strictly between 145 and 160 characters)\",
  \"improvement_reason\": \"what you changed and why in 1 sentence\"
}";

        return self::call( $system, $user );
    }

    /**
     * Generate advanced schema based on full page analysis.
     */
    public static function generate_advanced_schema( $post_id ) {
        $post    = get_post( $post_id );
        $content = $post->post_content;
        if ( empty( $content ) || trim( wp_strip_all_tags( $content ) ) === '' ) {
            $content = GateTouch_Analysis::capture_rendered_template( $post_id );
        }
        $content = GateTouch_Helpers::clean_meta_text( $content );
        $content = substr( $content, 0, 5000 );

        $system = 'You are a structured data expert. Detect the most appropriate schema type (Article, Product, FAQPage, etc.) AND scan for video embeds (YouTube/Vimeo). If videos are found, include a VideoObject in the JSON-LD or as part of the @graph. Respond ONLY with valid JSON.';
        $user   = "Generate Advanced Schema for:
Title: {$post->post_title}
Content: {$content}

Return JSON with EXACTLY these keys:
{
  \"detected_type\": \"Type\",
  \"has_video\": true/false,
  \"schema_json\": { \"@context\": \"https://schema.org\", \"@type\": \"...\", ... }
}";

        return self::call( $system, $user );
    }

    /**
     * Generate FAQ pairs from post content.
     */
    public static function generate_faq( $post_id ) {
        $post    = get_post( $post_id );
        $content = $post->post_content;
        if ( empty( $content ) || trim( wp_strip_all_tags( $content ) ) === '' ) {
            $content = GateTouch_Analysis::capture_rendered_template( $post_id );
        }
        $content = GateTouch_Helpers::clean_meta_text( $content );
        $content = substr( $content, 0, 5000 );

        $system = 'You are an SEO, AEO, and GEO expert. Extract FAQ question-answer pairs optimised for FAQPage schema markup AND for AI citation (ChatGPT, Perplexity, Google AI Overviews). Each question should match how a real user asks an AI assistant. Each answer should be concise (2-3 sentences), authoritative, and contain the key named entities from the content so AI systems can cite it accurately. Respond ONLY with valid JSON.';
        $user   = "Extract 4-6 FAQ pairs from this content. Prioritise questions that:
1. Start with Who/What/Why/How/When — formats AI answer engines prefer
2. Cover the most important named entities in the content
3. Have concise, self-contained answers (no \"as mentioned above\")

Post Title: {$post->post_title}
Content: {$content}

Return JSON:
{
  \"faqs\": [
    {\"question\": \"...\", \"answer\": \"...\"},
    {\"question\": \"...\", \"answer\": \"...\"}
  ]
}";

        return self::call( $system, $user );
    }

    /**
     * Generate homepage meta.
     */
    public static function generate_homepage_meta() {
        $site_name = get_bloginfo( 'name' );
        $tagline   = get_bloginfo( 'description' );
        $pages     = get_pages( [ 'number' => 10 ] );
        $pg_list   = implode( ', ', wp_list_pluck( $pages, 'post_title' ) );

        $system = 'You are an SEO, AEO, and GEO expert strategist. Generate homepage meta tags. Respond ONLY with valid JSON.';
        $user   = "Website: {$site_name}
Tagline: {$tagline}
Key Pages: {$pg_list}

STRICT SEO, AEO, AND GEO REQUIREMENTS:
1. meta_title: MUST be strictly between 50 and 60 characters long. Do not write less than 50 or more than 60 characters. Aim for 55 characters.
2. meta_description: MUST be strictly between 145 and 160 characters long. Do not write less than 145 or more than 160 characters. Aim for 152 characters.

Return JSON with EXACTLY these keys:
{
  \"meta_title\": \"string (MUST be strictly between 50 and 60 characters)\",
  \"meta_description\": \"string (MUST be strictly between 145 and 160 characters)\",
  \"og_title\": \"engaging OG title\",
  \"og_description\": \"conversational OG description\",
  \"focus_keyword\": \"primary keyword\",
  \"schema_type\": \"WebSite or LocalBusiness\"
}";

        return self::call( $system, $user );
    }

    /**
     * Batch generate for multiple post IDs (used in bulk).
     */
    public static function batch_generate( $post_id ) {
        return self::generate_meta( $post_id );
    }

    /**
     * Vision AI call for Image Analysis (OpenAI only).
     */
    public static function call_vision( $image_url, $prompt ) {
        $api_key = self::get_key( 'openai' );
        if ( ! $api_key ) return [ 'error' => 'Vision AI requires an OpenAI API key (GPT-4o Vision). Add your OpenAI key in GT SEO/GEO/AEO → AI Settings even if you use a different provider for text generation.' ];

        $final_url = $image_url;

        if ( strpos( $image_url, 'localhost' ) !== false || strpos( $image_url, '127.0.0.1' ) !== false ) {
            $response = wp_remote_get( $image_url, [ 'sslverify' => true ] );
            if ( ! is_wp_error( $response ) ) {
                $type      = wp_remote_retrieve_header( $response, 'content-type' );
                $data      = wp_remote_retrieve_body( $response );
                $base64    = base64_encode( $data ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
                $final_url = "data:{$type};base64,{$base64}";
            }
        }

        $response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', [
            'timeout'   => 60,
            'sslverify' => true,
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ],
            'body' => wp_json_encode( [
                'model'    => 'gpt-4o',
                'messages' => [
                    [
                        'role'    => 'user',
                        'content' => [
                            [ 'type' => 'text',      'text'      => $prompt ],
                            [ 'type' => 'image_url', 'image_url' => [ 'url' => $final_url ] ],
                        ],
                    ],
                ],
                'max_tokens'      => 300,
                'response_format' => [ 'type' => 'json_object' ],
            ] ),
        ] );

        if ( is_wp_error( $response ) ) return [ 'error' => $response->get_error_message() ];
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        $content = $body['choices'][0]['message']['content'] ?? null;
        if ( ! $content ) {
            return [ 'error' => $body['error']['message'] ?? 'No content returned from AI.' ];
        }

        $parsed = json_decode( $content, true );
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return [ 'error' => 'AI returned invalid JSON format.' ];
        }

        return $parsed;
    }

    /**
     * Extract key entities for Internal Linking.
     */
    public static function extract_entities( $content ) {
        $system = 'You are an entity extraction engine. Identify the 5 most important topical entities/keywords in the content. Respond ONLY with valid JSON.';
        $user   = 'Extract 5 entities from: ' . "\n" . substr( $content, 0, 3000 );
        $user  .= "\nReturn JSON: {\"entities\": [\"entity1\", \"entity2\", ...]}";

        return self::call( $system, $user );
    }

    /**
     * Generate Key Points summary from content.
     */
    public static function generate_key_points( $post_id ) {
        $post    = get_post( $post_id );
        $content = $post->post_content;
        if ( empty( $content ) || trim( wp_strip_all_tags( $content ) ) === '' ) {
            $content = GateTouch_Analysis::capture_rendered_template( $post_id );
        }
        $content = GateTouch_Helpers::clean_meta_text( $content );
        $content = substr( $content, 0, 8000 );

        $system = 'You are an expert content strategist. Extract the most important "Key Takeaways" or "Key Points" from the content. Format them as a punchy, bulleted list. Respond ONLY with valid JSON.';
        $user   = "Post Title: {$post->post_title}\nContent: {$content}\n\nReturn JSON: {\"title\": \"Key Takeaways\", \"points\": [\"Point 1\", \"Point 2\", \"Point 3\"]}";

        return self::call( $system, $user );
    }

    /**
     * Generate Social Media Posts.
     */
    public static function generate_social_posts( $post_id ) {
        $post    = get_post( $post_id );
        $content = $post->post_content;
        if ( empty( $content ) || trim( wp_strip_all_tags( $content ) ) === '' ) {
            $content = GateTouch_Analysis::capture_rendered_template( $post_id );
        }
        $content = GateTouch_Helpers::clean_meta_text( $content );
        $content = substr( $content, 0, 5000 );

        $system = 'You are a social media growth expert. Create high-engagement posts for LinkedIn, Facebook, and X (Twitter) based on the content. Use emojis and appropriate tone for each platform. Respond ONLY with valid JSON.';
        $user   = "Content: {$content}\n\nReturn JSON: {
            \"linkedin\": \"...\",
            \"facebook\": \"...\",
            \"twitter\": \"...\"
        }";

        return self::call( $system, $user );
    }

    /**
     * Generate AI Image using DALL-E 3 (OpenAI only).
     */
    public static function generate_ai_image( $prompt ) {
        $api_key = self::get_key( 'openai' );
        if ( ! $api_key ) return [ 'error' => 'AI Image generation requires an OpenAI API key (DALL-E 3). Add your OpenAI key in GT SEO/GEO/AEO → AI Settings even if you use a different provider for text generation.' ];

        $response = wp_remote_post( 'https://api.openai.com/v1/images/generations', [
            'timeout'   => 60,
            'sslverify' => true,
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ],
            'body' => wp_json_encode( [
                'model'  => 'dall-e-3',
                'prompt' => $prompt . ' -- Cinematic, high-quality, professional photography style.',
                'n'      => 1,
                'size'   => '1024x1024',
            ] ),
        ] );

        if ( is_wp_error( $response ) ) return [ 'error' => $response->get_error_message() ];
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        $url = $body['data'][0]['url'] ?? null;
        return $url ? [ 'url' => $url ] : [ 'error' => $body['error']['message'] ?? 'Image generation failed.' ];
    }

    /**
     * Find semantic internal link opportunities.
     */
    public static function find_internal_links( $content, $site_posts ) {
        $system = 'You are a technical SEO expert. Given a content snippet and a list of other posts on the site, identify the 3-5 best internal linking opportunities. Respond ONLY with valid JSON.';

        $list = '';
        foreach ( $site_posts as $p ) {
            $list .= "ID: {$p['id']} | Title: {$p['title']}\n";
        }

        $user = "Content Snippet: \n" . substr( $content, 0, 3000 ) . "\n\nAvailable Posts to Link To:\n{$list}\n\nReturn JSON: {\"suggestions\": [{\"post_id\": 123, \"anchor_text\": \"...\", \"reason\": \"...\"}]}";

        return self::call( $system, $user );
    }

    /**
     * Find the closest semantic redirect target from a candidate list.
     */
    public static function find_semantic_match( $source_title, $candidates ) {
        $candidate_rows = self::normalize_semantic_candidates( $candidates );
        if ( empty( $candidate_rows ) ) {
            return [
                'best_match_id' => 0,
                'confidence'    => 0,
                'reason'        => 'No redirect candidates available.',
            ];
        }

        if ( self::is_api_operational() ) {
            $system = 'You are a technical SEO redirect strategist. Choose the single best semantic replacement page for a removed URL. Return ONLY valid JSON.';
            $user   = 'Removed page title: ' . sanitize_text_field( wp_strip_all_tags( $source_title ) ) . "\n\n";
            $user  .= "Candidate pages JSON:\n" . wp_json_encode( $candidate_rows ) . "\n\n";
            $user  .= "Return JSON exactly in this shape:\n";
            $user  .= "{\"best_match_id\": integer, \"confidence\": number between 0 and 1, \"reason\": \"short explanation\"}";

            $result = self::call( $system, $user, '', 0.2, 500 );
            $match  = self::validate_semantic_match_result( $result, $candidate_rows );

            if ( ! empty( $match['best_match_id'] ) ) {
                return $match;
            }
        }

        return self::fallback_semantic_match( $source_title, $candidate_rows );
    }

    private static function normalize_semantic_candidates( $candidates ) {
        $rows = [];
        foreach ( (array) $candidates as $candidate ) {
            if ( is_object( $candidate ) ) {
                $candidate = (array) $candidate;
            }
            if ( ! is_array( $candidate ) ) {
                continue;
            }

            $id    = absint( $candidate['id'] ?? 0 );
            $title = sanitize_text_field( wp_strip_all_tags( $candidate['title'] ?? '' ) );

            if ( ! $id || '' === $title ) {
                continue;
            }

            $rows[] = [
                'id'    => $id,
                'title' => $title,
            ];
        }

        return array_slice( $rows, 0, 30 );
    }

    private static function validate_semantic_match_result( $result, $candidate_rows ) {
        if ( ! is_array( $result ) || isset( $result['error'] ) ) {
            return [
                'best_match_id' => 0,
                'confidence'    => 0,
                'reason'        => 'AI matching unavailable.',
            ];
        }

        $allowed_ids = array_map( 'absint', wp_list_pluck( $candidate_rows, 'id' ) );
        $match_id    = absint( $result['best_match_id'] ?? 0 );
        if ( ! in_array( $match_id, $allowed_ids, true ) ) {
            return [
                'best_match_id' => 0,
                'confidence'    => 0,
                'reason'        => 'AI returned a candidate outside the allowed list.',
            ];
        }

        $confidence = isset( $result['confidence'] ) ? (float) $result['confidence'] : 0;
        $confidence = max( 0, min( 1, $confidence ) );

        return [
            'best_match_id' => $match_id,
            'confidence'    => $confidence,
            'reason'        => sanitize_text_field( $result['reason'] ?? '' ),
        ];
    }

    private static function fallback_semantic_match( $source_title, $candidate_rows ) {
        $source = self::normalize_match_text( $source_title );
        if ( '' === $source ) {
            return [
                'best_match_id' => 0,
                'confidence'    => 0,
                'reason'        => 'Removed page title is empty.',
            ];
        }

        $best = [
            'best_match_id' => 0,
            'confidence'    => 0,
            'reason'        => 'No close lexical match found.',
        ];

        foreach ( $candidate_rows as $candidate ) {
            $target = self::normalize_match_text( $candidate['title'] );
            if ( '' === $target ) {
                continue;
            }

            similar_text( $source, $target, $similarity_percent );
            $similarity = $similarity_percent / 100;
            $overlap    = self::token_overlap_score( $source, $target );
            $confidence = round( ( $similarity * 0.6 ) + ( $overlap * 0.4 ), 2 );

            if ( $confidence > $best['confidence'] ) {
                $best = [
                    'best_match_id' => absint( $candidate['id'] ),
                    'confidence'    => $confidence,
                    'reason'        => 'Matched by title similarity and shared topic terms.',
                ];
            }
        }

        return $best;
    }

    private static function normalize_match_text( $text ) {
        $text = strtolower( remove_accents( wp_strip_all_tags( (string) $text ) ) );
        $text = preg_replace( '/[^a-z0-9\s]+/', ' ', $text );
        $text = preg_replace( '/\s+/', ' ', $text );

        return trim( $text );
    }

    private static function token_overlap_score( $source, $target ) {
        $source_tokens = array_unique( array_filter( explode( ' ', $source ) ) );
        $target_tokens = array_unique( array_filter( explode( ' ', $target ) ) );
        $union         = array_unique( array_merge( $source_tokens, $target_tokens ) );

        if ( empty( $union ) ) {
            return 0;
        }

        $intersection = array_intersect( $source_tokens, $target_tokens );
        return count( $intersection ) / count( $union );
    }

    /**
     * Analyze a single headline/title for SEO and engagement.
     */
    public static function analyze_headline( $headline ) {
        $system = 'You are a headline analysis expert. Analyze the given headline for SEO, click-through rate (CTR), and emotional impact. Respond ONLY with valid JSON.';
        $user   = "Analyze this headline: \"{$headline}\"

        Return JSON with EXACTLY these keys:
        {
          \"score\": 0-100,
          \"sentiment\": \"Positive|Negative|Neutral\",
          \"word_balance\": {
            \"common\": \"percentage\",
            \"uncommon\": \"percentage\",
            \"emotional\": \"percentage\",
            \"power\": \"percentage\"
          },
          \"character_count\": number,
          \"word_count\": number,
          \"readability\": \"Grade Level\",
          \"improvements\": [\"tip 1\", \"tip 2\"],
          \"seo_analysis\": \"1 sentence about SEO strength\"
        }";

        return self::call( $system, $user );
    }

    /**
     * Deep Content Optimization (NLP, Readability, Sentiment).
     */
    public static function optimize_content( $content, $focus_keyword = '' ) {
        $system = 'You are a world-class content optimizer. Analyze the provided content for NLP density, readability, and sentiment. Provide specific suggestions to improve search ranking and user engagement. Respond ONLY with valid JSON.';

        $user = "Focus Keyword: {$focus_keyword}\n\nContent:\n" . substr( $content, 0, 8000 ) . "\n\nSTRICT OPTIMIZATION TASKS:
        1. Calculate Readability Score (Flesch-Kincaid).
        2. Detect Content Sentiment.
        3. Identify missing NLP semantic keywords (LSI).
        4. Suggest structural improvements for Answer Box (AEO).

        Return JSON:
        {
          \"readability\": { \"score\": 0-100, \"grade\": \"...\", \"label\": \"Easy|Average|Hard\" },
          \"sentiment\": { \"score\": -1 to 1, \"label\": \"Positive|Neutral|Negative\" },
          \"nlp_keywords\": [ { \"word\": \"...\", \"relevance\": 0-1 } ],
          \"missing_topics\": [ \"topic 1\", \"topic 2\" ],
          \"aeo_suggestions\": [ \"suggestion 1\", \"suggestion 2\" ],
          \"content_score\": 0-100
        }";

        return self::call( $system, $user );
    }

    // ── Key Storage (multi-provider) ──────────────────────────────────────────

    /**
     * Get the decrypted API key for a given provider.
     *
     * @param string $provider 'openai' | 'anthropic' | 'gemini'
     */
    public static function get_key( $provider = '' ) {
        if ( ! $provider ) {
            $provider = self::get_provider();
        }

        $option_map = [
            'openai'    => 'gatetouch_openai_key',
            'anthropic' => 'gatetouch_anthropic_key',
            'gemini'    => 'gatetouch_gemini_key',
        ];

        $option = $option_map[ $provider ] ?? 'gatetouch_openai_key';
        $raw    = get_option( $option, '' );

        if ( empty( $raw ) ) return '';

        if ( strpos( $raw, 'riq_enc:' ) === 0 ) {
            return self::decrypt( substr( $raw, 8 ) );
        }

        // Migrate unencrypted key
        self::update_key( $raw, $provider );
        return $raw;
    }

    /**
     * Encrypt and save an API key for a given provider.
     *
     * @param string $key
     * @param string $provider 'openai' | 'anthropic' | 'gemini'
     */
    public static function update_key( $key, $provider = 'openai' ) {
        $option_map = [
            'openai'    => 'gatetouch_openai_key',
            'anthropic' => 'gatetouch_anthropic_key',
            'gemini'    => 'gatetouch_gemini_key',
        ];

        $option = $option_map[ $provider ] ?? 'gatetouch_openai_key';

        if ( empty( $key ) ) {
            update_option( $option, '' );
            return;
        }

        $encrypted = 'riq_enc:' . self::encrypt( $key );
        update_option( $option, $encrypted );

        delete_option( 'gatetouch_api_error_count' );
        update_option( 'gatetouch_api_status', 'pending' );
    }

    private static function encrypt( $value ) {
        if ( ! function_exists( 'openssl_encrypt' ) ) {
            return base64_encode( $value ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
        }
        $method = 'aes-256-cbc';
        $iv     = substr( hash( 'sha256', AUTH_SALT ), 0, 16 );
        return base64_encode( openssl_encrypt( $value, $method, SECURE_AUTH_SALT, 0, $iv ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
    }

    private static function decrypt( $value ) {
        if ( ! function_exists( 'openssl_decrypt' ) ) {
            return base64_decode( $value ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
        }
        $method = 'aes-256-cbc';
        $iv     = substr( hash( 'sha256', AUTH_SALT ), 0, 16 );
        return openssl_decrypt( base64_decode( $value ), $method, SECURE_AUTH_SALT, 0, $iv ) ?: ''; // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
    }

    // ── Connection Validation ─────────────────────────────────────────────────

    /**
     * Test API connection for the active (or specified) provider.
     */
    public static function validate_api_connection( $key = '', $provider = '' ) {
        if ( ! $provider ) {
            $provider = self::get_provider();
        }
        $test_key = $key ?: self::get_key( $provider );
        if ( ! $test_key ) return [ 'success' => false, 'error' => 'No API key provided.' ];

        switch ( $provider ) {
            case 'anthropic':
                return self::test_anthropic( $test_key );
            case 'gemini':
                return self::test_gemini( $test_key );
            default:
                return self::test_openai( $test_key );
        }
    }

    private static function test_openai( $key ) {
        $response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', [
            'timeout'   => 15,
            'sslverify' => true,
            'headers' => [
                'Authorization' => 'Bearer ' . $key,
                'Content-Type'  => 'application/json',
            ],
            'body' => wp_json_encode( [
                'model'      => 'gpt-4o-mini',
                'messages'   => [ [ 'role' => 'user', 'content' => 'ping' ] ],
                'max_tokens' => 5,
            ] ),
        ] );

        if ( is_wp_error( $response ) ) return [ 'success' => false, 'error' => $response->get_error_message() ];

        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $code === 200 ) {
            update_option( 'gatetouch_api_status', 'valid' );
            delete_option( 'gatetouch_api_error_count' );
            return [ 'success' => true ];
        }

        $error = $body['error']['message'] ?? 'Unknown OpenAI error.';
        update_option( 'gatetouch_api_status', 'invalid' );
        return [ 'success' => false, 'error' => $error ];
    }

    private static function test_anthropic( $key ) {
        $response = wp_remote_post( 'https://api.anthropic.com/v1/messages', [
            'timeout'   => 15,
            'sslverify' => true,
            'headers' => [
                'x-api-key'         => $key,
                'anthropic-version' => '2023-06-01',
                'Content-Type'      => 'application/json',
            ],
            'body' => wp_json_encode( [
                'model'      => 'claude-haiku-4-5-20251001',
                'max_tokens' => 10,
                'messages'   => [ [ 'role' => 'user', 'content' => 'ping' ] ],
            ] ),
        ] );

        if ( is_wp_error( $response ) ) return [ 'success' => false, 'error' => $response->get_error_message() ];

        $code = wp_remote_retrieve_response_code( $response );
        if ( $code === 200 ) {
            update_option( 'gatetouch_api_status', 'valid' );
            delete_option( 'gatetouch_api_error_count' );
            return [ 'success' => true ];
        }

        $body  = json_decode( wp_remote_retrieve_body( $response ), true );
        $error = $body['error']['message'] ?? 'Unknown Anthropic error.';
        update_option( 'gatetouch_api_status', 'invalid' );
        return [ 'success' => false, 'error' => $error ];
    }

    private static function test_gemini( $key ) {
        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $key;
        $response = wp_remote_post( $endpoint, [
            'timeout'   => 15,
            'sslverify' => true,
            'headers'   => [ 'Content-Type' => 'application/json' ],
            'body'      => wp_json_encode( [
                'contents' => [ [ 'parts' => [ [ 'text' => 'ping' ] ] ] ],
                'generationConfig' => [ 'maxOutputTokens' => 10 ],
            ] ),
        ] );

        if ( is_wp_error( $response ) ) return [ 'success' => false, 'error' => $response->get_error_message() ];

        $code = wp_remote_retrieve_response_code( $response );
        if ( $code === 200 ) {
            update_option( 'gatetouch_api_status', 'valid' );
            delete_option( 'gatetouch_api_error_count' );
            return [ 'success' => true ];
        }

        $body  = json_decode( wp_remote_retrieve_body( $response ), true );
        $error = $body['error']['message'] ?? 'Unknown Gemini error.';
        update_option( 'gatetouch_api_status', 'invalid' );
        return [ 'success' => false, 'error' => $error ];
    }

    // ── Operational Status ────────────────────────────────────────────────────

    public static function is_api_operational() {
        $status = get_option( 'gatetouch_api_status', 'pending' );
        if ( 'invalid' === $status ) return false;

        $key = self::get_key( self::get_provider() );
        if ( empty( $key ) ) return false;

        $errors = (int) get_option( 'gatetouch_api_error_count', 0 );
        if ( $errors >= 5 ) return false;

        return true;
    }

    public static function is_api_valid() {
        return get_option( 'gatetouch_api_status', '' ) === 'valid';
    }

    public static function has_api_key() {
        return self::is_api_operational();
    }

    // ── Advanced AI Methods ───────────────────────────────────────────────────

    public static function audit_content_depth( $title, $content ) {
        $system = "You are an Enterprise SEO Content Auditor. Your goal is to analyze the content against the title and detect 'Intent Coverage' gaps and 'Topical Depth' weaknesses. You must return ONLY JSON. Do not return markdown.";

        $clean_content = wp_trim_words( wp_strip_all_tags( $content ), 1500 );

        $user = "Title: {$title}\n\nContent:\n{$clean_content}\n\n" .
                "Identify up to 3 missing semantic entities or missing search intents that top-ranking competitors likely cover. Rate the topical depth score out of 100. " .
                "Return JSON exactly matching this format:\n" .
                "{\n" .
                "  \"depth_score\": integer,\n" .
                "  \"gaps\": [\"string\", \"string\"]\n" .
                '}';

        $res = self::call( $system, $user, '', 0.5, 800 );
        if ( isset( $res['error'] ) ) return $res;

        return is_array( $res ) ? $res : [ 'error' => 'Invalid JSON returned from AI' ];
    }

    public static function compare_with_competitor( $user_content, $competitor_data ) {
        $system = "You are an Enterprise SEO Strategy Engine. Compare the user's content against the competitor's content. " .
                  'Your goal is to perform Entity Clustering and detect missing entity mentions, keyword gaps, and topical depth differences. ' .
                  'Return ONLY valid JSON.';

        $clean_user = wp_trim_words( wp_strip_all_tags( $user_content ), 1500 );
        $comp_title = $competitor_data['title'] ?? '';
        $comp_desc  = $competitor_data['description'] ?? '';
        $comp_h1    = implode( ' ', $competitor_data['h1'] ?? [] );
        $comp_h2    = implode( ' ', $competitor_data['h2'] ?? [] );

        $user = "User Content:\n{$clean_user}\n\n" .
                "Competitor Data:\nTitle: {$comp_title}\nDesc: {$comp_desc}\nH1: {$comp_h1}\nH2: {$comp_h2}\n\n" .
                "Identify missing entity clusters, topic gaps, and keyword opportunities the user is missing compared to the competitor. " .
                "Return JSON exactly matching this format:\n" .
                "{\n" .
                "  \"missing_entities\": [\"Entity A\", \"Entity B\"],\n" .
                "  \"keyword_clusters\": [\"Cluster 1\", \"Cluster 2\"],\n" .
                "  \"topical_gaps\": [\"Gap 1 description\", \"Gap 2 description\"],\n" .
                "  \"competitor_advantage\": \"1 sentence explaining the competitor's semantic advantage\"\n" .
                '}';

        $res = self::call( $system, $user, '', 0.5, 800 );
        if ( isset( $res['error'] ) ) return $res;

        return is_array( $res ) ? $res : [ 'error' => 'Invalid JSON returned from AI' ];
    }

    // ── Usage Tracking & Error Handling ───────────────────────────────────────

    private static function track_usage( $usage, $model ) {
        $stats = get_option( 'gatetouch_api_usage', [] );
        $today = wp_date( 'Y-m-d' );

        if ( ! isset( $stats[ $today ] ) ) {
            $stats[ $today ] = [ 'tokens' => 0, 'requests' => 0, 'cost' => 0 ];
        }

        $tokens = (int) ( $usage['total_tokens'] ?? 0 );
        $stats[ $today ]['tokens']   += $tokens;
        $stats[ $today ]['requests'] += 1;

        // Rough cost estimation
        $cost_per_1k = ( strpos( $model, 'mini' ) !== false || strpos( $model, 'flash' ) !== false || strpos( $model, 'haiku' ) !== false ) ? 0.00015 : 0.005;
        $stats[ $today ]['cost'] += ( $tokens / 1000 ) * $cost_per_1k;

        if ( count( $stats ) > 30 ) {
            array_shift( $stats );
        }

        update_option( 'gatetouch_api_usage', $stats );
    }

    private static function log_api_failure( $error ) {
        $count = (int) get_option( 'gatetouch_api_error_count', 0 );
        update_option( 'gatetouch_api_error_count', $count + 1 );
        GateTouch_Logger::error( 'API Failure: ' . $error );
    }

    private static function mark_api_invalid() {
        update_option( 'gatetouch_api_status', 'invalid' );
    }
}
