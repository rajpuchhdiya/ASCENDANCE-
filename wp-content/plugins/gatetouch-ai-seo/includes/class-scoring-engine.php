<?php
defined( 'ABSPATH' ) || exit;

/**
 * GateTouch Scoring Engine
 * 
 * Enterprise-level scoring system for SEO, AEO, and GEO.
 */
class GateTouch_Scoring_Engine {

    private static function normalize_text( $text ) {
        $text = html_entity_decode( wp_strip_all_tags( (string) $text ), ENT_QUOTES, get_bloginfo( 'charset' ) );
        $text = function_exists( 'remove_accents' ) ? remove_accents( $text ) : $text;
        $text = mb_strtolower( $text );
        $text = preg_replace( '/[^\p{L}\p{N}]+/u', ' ', $text );
        return trim( preg_replace( '/\s+/u', ' ', $text ) );
    }

    private static function keyword_terms( $keyword ) {
        $terms = preg_split( '/\s+/u', self::normalize_text( $keyword ), -1, PREG_SPLIT_NO_EMPTY );
        $keep_short = [ 'ai', 'ui', 'ux' ];

        return array_values( array_filter( array_unique( $terms ), function( $term ) use ( $keep_short ) {
            return mb_strlen( $term ) > 2 || is_numeric( $term ) || in_array( $term, $keep_short, true );
        } ) );
    }

    private static function keyword_token_coverage( $haystack, $keyword ) {
        $haystack_norm = ' ' . self::normalize_text( $haystack ) . ' ';
        $terms         = self::keyword_terms( $keyword );

        if ( empty( $terms ) ) {
            return 0;
        }

        $matched = 0;
        foreach ( $terms as $term ) {
            if ( preg_match( '/\s' . preg_quote( $term, '/' ) . '\s/u', $haystack_norm ) ) {
                $matched++;
            }
        }

        return $matched / count( $terms );
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

        $terms = self::keyword_terms( $keyword );
        if ( empty( $terms ) ) {
            return false;
        }

        $coverage = self::keyword_token_coverage( $haystack, $keyword );
        if ( count( $terms ) <= 2 ) {
            return $coverage >= 1;
        }

        return $coverage >= 0.8;
    }

    /**
     * Perform a comprehensive site/page audit
     */
    public static function audit_post( $post_id, $args = [] ) {
        $post = get_post( $post_id );
        if ( ! $post ) return [];

        $meta    = $args['meta']    ?? ( get_post_meta( $post_id, GATETOUCH_META_KEY, true ) ?: [] );
        $content = $args['content'] ?? $post->post_content;
        if ( empty( trim( wp_strip_all_tags( (string) $content ) ) ) && class_exists( 'GateTouch_Analysis' ) ) {
            $content = GateTouch_Analysis::capture_rendered_template( $post_id );
        }

        $title = trim( (string) ( $args['title'] ?? '' ) );
        if ( $title === '' ) {
            $title = ! empty( $meta['meta_title'] ) ? $meta['meta_title'] : $post->post_title;
        }
        
        // Clean content for analysis
        $text = wp_strip_all_tags( preg_replace('/<svg.*?<\/svg>/is', '', $content) );
        $word_count = count( preg_split( '/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY ) );

        // 1. Calculate SEO Score (Weight: 40%)
        $seo = self::calculate_seo_score( $post_id, $title, $content, $meta, $word_count );

        // 2. Calculate AEO Score (Weight: 30%) - Answer Engine Optimization
        $aeo = self::calculate_aeo_score( $post_id, $content, $meta );

        // 3. Calculate GEO Score (Weight: 30%) - Generative Engine Optimization
        $geo = self::calculate_geo_score( $post_id, $content, $meta );

        // Total AI Health Score (Weighted)
        $total_score = max( 0, min( 100, intval( round( ( $seo['score'] * 0.4 ) + ( $aeo['score'] * 0.3 ) + ( $geo['score'] * 0.3 ) ) ) ) );

        return [
            'total_score' => $total_score,
            'seo'         => $seo,
            'aeo'         => $aeo,
            'geo'         => $geo,
            'word_count'  => $word_count,
            'timestamp'   => current_time( 'mysql' ),
            'color'       => self::get_score_color( $total_score ),
            'label'       => self::get_score_label( $total_score ),
        ];
    }

    /**
     * SEO Score — aligned with Google 2024/25 ranking signals.
     *
     * Signals: Meta Title (15) | Meta Description (15) | Focus Keyword (15) |
     *          Word Count (15) | Heading Structure (15) | Schema Markup (10) | E-E-A-T (15)
     */
    private static function calculate_seo_score( $post_id, $title, $content, $meta, $word_count ) {
        $score = 0;
        $tips  = [];

        // ── Meta Title (15 pts) ───────────────────────────────────────────────
        $mt  = $meta['meta_title'] ?? '';
        $mtl = mb_strlen( $mt );
        if ( $mtl >= 50 && $mtl <= 60 )   $score += 15;
        elseif ( $mtl > 0 )               { $score += 8;  $tips[] = 'Optimize Meta Title to 50–60 chars (currently ' . $mtl . ').'; }
        else                              { $tips[] = 'Missing Meta Title — Google uses it as the primary ranking signal.'; }

        // ── Meta Description (15 pts) ─────────────────────────────────────────
        $md  = $meta['meta_description'] ?? '';
        $mdl = mb_strlen( $md );
        if ( $mdl >= 145 && $mdl <= 160 ) $score += 15;
        elseif ( $mdl > 0 )               { $score += 8;  $tips[] = 'Optimize Meta Description to 145–160 chars (currently ' . $mdl . ').'; }
        else                              { $tips[] = 'Missing Meta Description — improves CTR from search results.'; }

        // ── Focus Keyword (15 pts) ────────────────────────────────────────────
        $kw = trim( (string) ( $meta['focus_keyword'] ?? '' ) );
        if ( $kw ) {
            $pts = 0;
            $title_sources = trim( $title . ' ' . ( $meta['meta_title'] ?? '' ) );
            if ( self::keyword_matches( $title_sources, $kw ) ) $pts += 5; else $tips[] = 'Add Focus Keyword to the page title or SEO title.';
            if ( self::keyword_matches( mb_substr( wp_strip_all_tags( $content ), 0, 700 ), $kw ) ) $pts += 5; else $tips[] = 'Add Focus Keyword in the opening paragraph.';
            if ( self::keyword_matches( $meta['meta_title'] ?? '', $kw ) ) $pts += 5; else $tips[] = 'Add Focus Keyword to the Meta Title tag.';
            $score += $pts;
        } else {
            $tips[] = 'No Focus Keyword set — required for keyword-based scoring.';
        }

        // ── Word Count / Content Depth (15 pts) ───────────────────────────────
        // Google's Helpful Content system rewards depth, not just length.
        if ( $word_count >= 1200 )      $score += 15;
        elseif ( $word_count >= 800 )   { $score += 10; $tips[] = 'Aim for 1,200+ words for competitive topics (currently ' . $word_count . ').'; }
        elseif ( $word_count >= 400 )   { $score += 5;  $tips[] = 'Thin content (' . $word_count . ' words). Add depth, examples, and original analysis.'; }
        else                            { $tips[] = 'Very thin content (' . $word_count . ' words). Google Helpful Content penalises shallow pages.'; }

        // ── Heading Structure (15 pts) — Google rewards logical H1/H2 hierarchy ─
        $has_h1 = (bool) preg_match( '/<h1[^>]*>/i', $content );
        $h2_count = preg_match_all( '/<h2[^>]*>/i', $content );
        $h3_count = preg_match_all( '/<h3[^>]*>/i', $content );

        if ( $has_h1 && $h2_count >= 3 )        $score += 15;
        elseif ( $has_h1 && $h2_count >= 1 )    { $score += 10; $tips[] = 'Add more H2 subheadings (aim for 3+) to improve content structure.'; }
        elseif ( $h2_count >= 2 )               { $score += 8;  $tips[] = 'Add an H1 heading — it is the strongest on-page SEO signal.'; }
        else                                    { $score += 3;  $tips[] = 'Poor heading structure. Add H1 + multiple H2 subheadings.'; }

        if ( $h3_count < 2 && $word_count > 800 ) $tips[] = 'Add H3 subheadings to break up long sections — improves readability and crawlability.';

        // ── Schema Markup (10 pts) ────────────────────────────────────────────
        $schema_type   = $meta['schema_type']   ?? '';
        $custom_schema = $meta['custom_schema'] ?? '';
        if ( ! empty( $custom_schema ) || ( $schema_type && $schema_type !== 'None' ) ) {
            $score += 10;
        } else {
            $tips[] = 'No Schema Markup set. Schema helps Google and AI engines understand your content type.';
        }

        // ── E-E-A-T Signals (15 pts) — Google's 2024 quality ranking system ──
        // Experience, Expertise, Authoritativeness, Trustworthiness
        $eeat_pts = 0;
        $post_obj = get_post( $post_id );

        // External links = citing sources (authoritative trust signal)
        $ext_links = preg_match_all( '/<a[^>]+href=["\']https?:\/\/(?!' . preg_quote( wp_parse_url( home_url(), PHP_URL_HOST ), '/' ) . ')[^"\']+["\'][^>]*>/i', $content );
        if ( $ext_links >= 2 )   $eeat_pts += 5;
        elseif ( $ext_links == 1 ) { $eeat_pts += 3; $tips[] = 'Add 2+ external links to authoritative sources — signals E-E-A-T to Google.'; }
        else                     { $tips[] = 'No external citations found. Link to authoritative sources to build trust signals.'; }

        // Author bio (expertise signal) — check if user has a bio
        if ( $post_obj ) {
            $author_bio = get_the_author_meta( 'description', $post_obj->post_author );
            if ( ! empty( $author_bio ) ) $eeat_pts += 5;
            else $tips[] = 'Author has no bio set. Add an author biography under Users → Profile — a strong E-E-A-T trust signal.';
        }

        // Content freshness — posts updated in last 12 months score higher
        if ( $post_obj ) {
            $modified = strtotime( $post_obj->post_modified );
            if ( $modified && ( time() - $modified ) < YEAR_IN_SECONDS ) $eeat_pts += 5;
            else $tips[] = 'Content not updated in 12+ months. Refresh with new data or examples — Google favours fresh content.';
        }

        $score += $eeat_pts;
        $score = max( 0, min( 100, intval( $score ) ) );
        return [ 'score' => $score, 'tips' => $tips ];
    }

    /**
     * AEO Score (Answer Engine Optimization)
     * Focuses on readability for AI bots and answer-friendliness.
     */
    private static function calculate_aeo_score( $post_id, $content, $meta ) {
        $score = 0;
        $tips  = [];

        // Question-based headers (25 pts)
        if ( preg_match( '/<h[234][^>]*>.*\?<\/h[234]>/is', $content ) || preg_match( '/\b(how|what|why|best|top|guide|compare)\b/i', wp_strip_all_tags( $content ) ) ) {
            $score += 25;
        } else {
            $tips[] = 'Add question-based subheadings (H2-H4) to trigger AI Answer Boxes.';
        }

        // FAQ Schema (25 pts)
        if ( ! empty( $meta['faqs'] ) || ( isset($meta['schema_type']) && in_array( $meta['schema_type'], [ 'FAQPage', 'HowTo', 'Article', 'BlogPosting' ], true ) ) ) {
            $score += 25;
        } else {
            $tips[] = 'Add FAQ schema to improve AI answer extraction.';
        }

        // Conversational Tone / Simple Sentences (25 pts)
        // Check for common answer patterns (e.g., "The answer is", "To summarize")
        if ( preg_match( '/(is defined as|to summarize|key takeaways|the answer is|in short|overview|summary)/i', $content ) || ! empty( $meta['key_points'] ) ) {
            $score += 25;
        } else {
            $tips[] = 'Use conversational "Takeaway" blocks for better AI readability.';
        }

        // Bullet points / Lists (25 pts)
        if ( strpos( $content, '<li>' ) !== false || preg_match( '/(^|\n)\s*[-*]\s+/m', $content ) ) {
            $score += 25;
        } else {
            $tips[] = 'Use bulleted lists for data-heavy sections to help AI parsing.';
        }

        $score = max( 0, min( 100, intval( $score ) ) );

        // Integration with AI Engine: If the post has been analyzed for Intent, 
        // factor that into the Readiness score (Weight: 60% Technical, 40% AI Intent)
        if ( isset( $meta['intent_match_score'] ) ) {
            $ai_intent = intval( $meta['intent_match_score'] );
            $score     = round( ( $score * 0.6 ) + ( $ai_intent * 0.4 ) );
        }

        return [ 'score' => (int)$score, 'tips' => $tips ];
    }

    /**
     * GEO Score (Generative Engine Optimization).
     *
     * Measures how extractable and quotable a page is to a generative engine
     * (Google AI Overviews, ChatGPT, Perplexity, Copilot).
     *
     * Every signal below is computed from the post itself and needs no API key,
     * so a free install gets a real, actionable score. AEO already scores
     * question headings, lists and FAQ blocks — those are deliberately not
     * repeated here.
     *
     * Weighting follows the published evidence on generative citation:
     *   Passage citability 30 | Evidence density 20 | Authority & freshness 20
     *   Topical authority 20  | Multi-modal 10
     *
     * llms.txt is intentionally NOT scored per post. It is a site-wide file and
     * the primary sources (Google's Mueller and Illyes, SE Ranking's 300k-domain
     * study, OtterlyAI's server-log audit) find no citation lift from it. It is
     * surfaced as a site-level infrastructure signal instead — see
     * site_geo_signals().
     */
    private static function calculate_geo_score( $post_id, $content, $meta ) {
        $score = 0;
        $tips  = [];

        $text  = trim( html_entity_decode( wp_strip_all_tags( $content ), ENT_QUOTES, get_bloginfo( 'charset' ) ) );
        $words = preg_split( '/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY );
        $total_words = count( $words );

        // ── 1. Passage citability (30 pts) ────────────────────────────────────
        // Generative engines lift self-contained blocks of roughly 134-167 words.
        // Reward content actually segmented into that shape, and an answer that
        // lands inside the opening 60 words.
        $passages     = self::split_passages( $content );
        $citable      = 0;
        foreach ( $passages as $passage ) {
            $len = count( preg_split( '/\s+/u', $passage, -1, PREG_SPLIT_NO_EMPTY ) );
            if ( $len >= 120 && $len <= 180 ) {
                $citable++;
            }
        }

        if ( $citable >= 3 ) {
            $score += 20;
        } elseif ( $citable >= 1 ) {
            $score += 12;
            $tips[] = 'Only ' . $citable . ' section(s) are in the 134-167 word range AI engines quote. Split long sections so more of them are independently citable.';
        } else {
            $tips[] = 'No section is in the 134-167 word range AI engines quote. Break content into self-contained blocks of roughly 150 words under their own heading.';
        }

        // Direct answer up front: a definition or answer pattern in the first 60 words.
        $opening = implode( ' ', array_slice( $words, 0, 60 ) );
        if ( preg_match( '/\b(is|are|refers to|means|is defined as)\b/i', $opening ) && $total_words > 0 ) {
            $score += 10;
        } else {
            $tips[] = 'Answer the page\'s core question in the first 40-60 words — lead with a "X is …" definition so the opening is liftable on its own.';
        }

        // ── 2. Evidence density (20 pts) ──────────────────────────────────────
        // Specific numbers and outbound citations to primary sources are the
        // strongest quotability markers.
        $stat_hits = preg_match_all( '/\b\d[\d,.]*\s?(%|percent|million|billion|users|studies?)\b/i', $text );
        $site_host = wp_parse_url( home_url(), PHP_URL_HOST );
        $outbound  = preg_match_all( '/<a[^>]+href=["\']https?:\/\/(?!(?:www\.)?' . preg_quote( $site_host, '/' ) . ')[^"\']+["\']/i', $content );

        if ( $stat_hits >= 2 ) {
            $score += 12;
        } elseif ( $stat_hits === 1 ) {
            $score += 6;
            $tips[] = 'Add more specific figures. Pages carrying concrete statistics are cited far more often than pages making general claims.';
        } else {
            $tips[] = 'No specific statistics found. Add concrete numbers, dates or measured results — generative engines prefer quotable facts over general statements.';
        }

        if ( $outbound >= 1 ) {
            $score += 8;
        } else {
            $tips[] = 'Cite at least one primary source (study, official documentation, dataset). Attributed claims are treated as more trustworthy.';
        }

        // ── 3. Authority and freshness (20 pts) ───────────────────────────────
        $post = get_post( $post_id );

        $author_id = $post ? (int) $post->post_author : 0;
        $has_bio   = $author_id && '' !== trim( (string) get_the_author_meta( 'description', $author_id ) );
        if ( $has_bio ) {
            $score += 10;
        } else {
            $tips[] = 'Add a biography with credentials to this post\'s author profile. Author identity is a direct E-E-A-T signal and feeds Person schema.';
        }

        if ( $post ) {
            $age_days = ( time() - strtotime( $post->post_modified_gmt . ' GMT' ) ) / DAY_IN_SECONDS;
            if ( $age_days <= 365 ) {
                $score += 10;
            } elseif ( $age_days <= 730 ) {
                $score += 5;
                $tips[] = 'This page was last updated over a year ago. Refreshing it restores freshness signals AI engines weigh when choosing between sources.';
            } else {
                $tips[] = 'This page has not been updated in over two years. Generative engines strongly prefer recently maintained sources.';
            }
        }

        // ── 4. Topical authority (20 pts) ─────────────────────────────────────
        $internal = preg_match_all( '/<a[^>]+href=["\'](?:https?:\/\/' . preg_quote( $site_host, '/' ) . '[^"\']*|\/[^"\']*)["\'][^>]*>/i', $content );
        if ( $internal >= 3 ) {
            $score += 12;
        } elseif ( $internal > 0 ) {
            $score += 6;
            $tips[] = 'Add more internal links to supporting content. AI citation favours pages backed by deep topical coverage, not isolated pages.';
        } else {
            $tips[] = 'This page has no internal links. Connect it to related content to establish topical authority.';
        }

        // Entity coverage from AI analysis is a bonus, never a prerequisite.
        if ( ! empty( $meta['missing_topics'] ) ) {
            $score += 4;
            $tips[] = 'Add missing entities: ' . implode( ', ', array_slice( (array) $meta['missing_topics'], 0, 2 ) );
        } elseif ( isset( $meta['missing_topics'] ) ) {
            $score += 8;
        } else {
            $score += 4;
        }

        // ── 5. Multi-modal content (10 pts) ───────────────────────────────────
        // Pages combining text with media see materially higher selection rates.
        $modes  = 0;
        $modes += preg_match( '/<img[^>]+>/i', $content ) ? 1 : 0;
        $modes += preg_match( '/<(video|iframe)[^>]+>/i', $content ) ? 1 : 0;
        $modes += preg_match( '/<table[^>]*>/i', $content ) ? 1 : 0;

        if ( $modes >= 2 ) {
            $score += 10;
        } elseif ( $modes === 1 ) {
            $score += 5;
            $tips[] = 'Add a second content format — a comparison table or an embedded video alongside your images.';
        } else {
            $tips[] = 'This page is text only. Adding an image plus a table or video raises the odds of being selected as a source.';
        }

        $score = max( 0, min( 100, (int) round( $score ) ) );
        return [ 'score' => $score, 'tips' => $tips ];
    }

    /**
     * Split post content into heading-delimited sections so passage length can
     * be measured the way a generative engine chunks a page.
     *
     * @return string[] Plain-text sections.
     */
    private static function split_passages( $content ) {
        $content = preg_replace( '/<(script|style|svg)\b.*?<\/\1>/is', '', (string) $content );

        // Split on any H2-H4 boundary; the heading itself starts a new section.
        $chunks = preg_split( '/<h[234][^>]*>/i', $content );
        if ( ! is_array( $chunks ) ) {
            return [];
        }

        $passages = [];
        foreach ( $chunks as $chunk ) {
            $plain = trim( html_entity_decode( wp_strip_all_tags( $chunk ), ENT_QUOTES, get_bloginfo( 'charset' ) ) );
            if ( '' !== $plain ) {
                $passages[] = $plain;
            }
        }

        return $passages;
    }

    /**
     * Site-wide GEO infrastructure signals.
     *
     * These are properties of the site, not of any single post, so they are
     * reported separately rather than folded into a per-post score.
     *
     * @return array<string, array{label:string, status:string, note:string}>
     */
    public static function site_geo_signals() {
        $llm_opts  = get_option( 'gatetouch_llms_settings', [] );
        $llms_on   = in_array( (string) ( $llm_opts['enable_llms_txt'] ?? $llm_opts['enabled'] ?? 'no' ), [ '1', 'yes' ], true );

        $schema    = class_exists( 'GateTouch_Schema_Engine' ) ? GateTouch_Schema_Engine::settings() : [];
        $has_org   = ! empty( $schema['org_name'] );
        $has_logo  = ! empty( $schema['org_logo'] );

        $robots    = get_option( 'gatetouch_robots_settings', [] );
        $search_bots_allowed = true;
        foreach ( [ 'allow_gptbot', 'allow_oai_searchbot', 'allow_claudebot', 'allow_perplexity' ] as $key ) {
            if ( isset( $robots[ $key ] ) && 'yes' !== $robots[ $key ] ) {
                $search_bots_allowed = false;
                break;
            }
        }

        return [
            'ai_crawlers' => [
                'label'  => __( 'AI search crawler access', 'gatetouch-ai-seo' ),
                'status' => $search_bots_allowed ? 'pass' : 'fail',
                'note'   => $search_bots_allowed
                    ? __( 'Citation crawlers can reach your content.', 'gatetouch-ai-seo' )
                    : __( 'A citation crawler is blocked — this removes you from AI answers entirely. Blocking training crawlers is a separate, safe choice.', 'gatetouch-ai-seo' ),
            ],
            'entity' => [
                'label'  => __( 'Organization entity defined', 'gatetouch-ai-seo' ),
                'status' => ( $has_org && $has_logo ) ? 'pass' : 'warn',
                'note'   => ( $has_org && $has_logo )
                    ? __( 'Your brand resolves to a single entity across the schema graph.', 'gatetouch-ai-seo' )
                    : __( 'Set your Organization name and logo in Search Appearance. Brand entity signals correlate far more strongly with AI citation than backlinks do.', 'gatetouch-ai-seo' ),
            ],
            'llms_txt' => [
                'label'  => __( 'llms.txt published', 'gatetouch-ai-seo' ),
                'status' => $llms_on ? 'pass' : 'info',
                'note'   => __( 'Optional. No major AI search engine currently uses llms.txt for citation, so this is published for forward compatibility only — it does not affect your score.', 'gatetouch-ai-seo' ),
            ],
        ];
    }

    private static function get_score_color( $score ) {
        if ( $score >= 80 ) return '#6366f1'; // Indigo success state.
        if ( $score >= 50 ) return '#f59e0b';
        return '#ef4444';
    }

    private static function get_score_label( $score ) {
        if ( $score >= 90 ) return __( 'Enterprise Ready', 'gatetouch-ai-seo' );
        if ( $score >= 80 ) return __( 'Excellent', 'gatetouch-ai-seo' );
        if ( $score >= 65 ) return __( 'Good', 'gatetouch-ai-seo' );
        if ( $score >= 50 ) return __( 'Needs Optimization', 'gatetouch-ai-seo' );
        return __( 'Critical Gaps', 'gatetouch-ai-seo' );
    }
}
