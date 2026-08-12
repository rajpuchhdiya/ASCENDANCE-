<?php
defined( 'ABSPATH' ) || exit;

/**
 * Performs SEO analysis and rendered-content extraction for posts and pages.
 */
class GateTouch_Analysis {

    /**
     * Internal renderer to capture content from PHP templates
     */
    public static function capture_rendered_template( $post_id ) {
        $post_obj = get_post( $post_id );
        if ( ! $post_obj ) return '';

        // Fallback for Elementor
        if ( class_exists( '\Elementor\Plugin' ) ) {
            $elementor = \Elementor\Plugin::$instance;
            $content   = $elementor->frontend->get_builder_content( $post_id );
            if ( ! empty( $content ) ) return $content;
        }

        // Fallback for Divi
        if ( function_exists( 'et_builder_render_layout' ) ) {
            $content = et_builder_render_layout( $post_id );
            if ( ! empty( $content ) ) return $content;
        }

        // Standard WordPress Content with filters
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Applies the core WordPress content filter intentionally.
        $content = apply_filters( 'the_content', $post_obj->post_content );

        // If still empty, use raw content
        if ( empty( trim( wp_strip_all_tags( $content ) ) ) ) {
            $content = $post_obj->post_content;
        }

        return $content;
    }

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

    private static function keyword_occurrences( $haystack, $keyword ) {
        $keyword_norm = self::normalize_text( $keyword );
        if ( $keyword_norm === '' ) {
            return 0;
        }

        $haystack_norm = ' ' . self::normalize_text( $haystack ) . ' ';
        return preg_match_all( '/\s' . preg_quote( $keyword_norm, '/' ) . '\s/u', $haystack_norm );
    }

    /**
     * Analyze an External URL (Competitor Audit)
     */
    public static function analyze_external_url( $url ) {
        $response = wp_remote_get( $url, [ 'timeout' => 15, 'user-agent' => 'Mozilla/5.0 (compatible; GateTouchBot/1.0)' ] );
        if ( is_wp_error( $response ) ) return [ 'error' => $response->get_error_message() ];

        $html    = wp_remote_retrieve_body( $response );
        $code    = wp_remote_retrieve_response_code( $response );

        if ( $code !== 200 ) return [ 'error' => "HTTP Error: {$code}" ];

        // Extract basic SEO elements
        preg_match( '/<title>(.*?)<\/title>/is', $html, $title_match );
        preg_match( '/<meta[^>]*name=["\']description["\'][^>]*content=["\']([^"\']*)["\'][^>]*>/i', $html, $desc_match );
        preg_match_all( '/<h1[^>]*>(.*?)<\/h1>/is', $html, $h1_matches );
        preg_match_all( '/<h2[^>]*>(.*?)<\/h2>/is', $html, $h2_matches );
        preg_match_all( '/<img[^>]+alt=["\']([^"\']*)["\'][^>]*>/i', $html, $alt_matches );

        $title = $title_match[1] ?? 'N/A';
        $desc  = $desc_match[1]  ?? 'N/A';
        $text  = wp_strip_all_tags( preg_replace('/<script.*?<\/script>/is', '', $html) );
        $word_count = count( preg_split( '/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY ) );

        $checks = [];
        $score = 0;
        $max = 100;

        // Simple Score calculation
        if ( mb_strlen($title) >= 40 && mb_strlen($title) <= 65 ) $score += 20;
        if ( mb_strlen($desc) >= 120 && mb_strlen($desc) <= 160 ) $score += 20;
        if ( count($h1_matches[0]) === 1 ) $score += 20;
        if ( count($h2_matches[0]) >= 2 ) $score += 20;
        if ( $word_count >= 1000 ) $score += 20;

        return [
            'url'         => $url,
            'title'       => $title,
            'description' => $desc,
            'h1_count'    => count($h1_matches[0]),
            'h2_count'    => count($h2_matches[0]),
            'word_count'  => $word_count,
            'score'       => $score,
            'color'       => self::score_color( $score ),
            'label'       => self::score_label( $score ),
        ];
    }

    /**
     * Run full 15-point SEO analysis
     */
    public static function analyze( $post_id, $focus_keyword = '', $args = [] ) {
        clean_post_cache( $post_id );
        $post = get_post( $post_id );
        if ( ! $post ) return [];

        $content = isset( $args['content'] ) ? $args['content'] : $post->post_content;

        // Fallback for PHP Templates: If content is empty, render internally
        if ( empty( $content ) || trim( wp_strip_all_tags( $content ) ) === '' ) {
            $content = self::capture_rendered_template( $post_id );
        }

        $meta = get_post_meta( $post_id, GATETOUCH_META_KEY, true ) ?: [];

        $title = isset( $args['title'] ) ? trim( (string) $args['title'] ) : '';
        if ( $title === '' ) {
            $title = ! empty( $meta['meta_title'] ) ? $meta['meta_title'] : $post->post_title;
        }

        $provided_feat_id = isset( $args['featured_image_id'] ) ? (int) $args['featured_image_id'] : 0;
        $feat_id          = $provided_feat_id > 0 ? $provided_feat_id : get_post_thumbnail_id( $post_id );

        // If content is empty/blocks-only in editor, try to get more via deep capture
        if ( empty( trim( wp_strip_all_tags( $content ) ) ) ) {
            $content = self::capture_rendered_template( $post_id );
        }
        $clean_content = preg_replace('/<svg.*?<\/svg>/is', '', $content);
        $text          = wp_strip_all_tags( $clean_content );

        $is_cornerstone = ! empty( $meta['is_cornerstone'] );
        $kw             = strtolower( trim( $focus_keyword ?: ( $meta['focus_keyword'] ?? '' ) ) );
        // Normally a comma-separated string, but an array has reached this from
        // importers and generated drafts before now — and an unguarded explode()
        // here is fatal, which takes down the whole post edit screen.
        $raw_add_kws    = $meta['additional_keywords'] ?? '';
        $add_kws        = is_array( $raw_add_kws ) ? $raw_add_kws : explode( ',', (string) $raw_add_kws );
        $add_kws        = array_filter( array_map( 'trim', array_map( 'strval', $add_kws ) ) );

        $text_lc    = strtolower( $text );

        // Count words using a more robust pattern that handles Unicode
        $word_count = count( preg_split( '/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY ) );
        $sentences  = preg_split( '/(?<=[.?!])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY );

        $checks = [];
        $points = 0;
        $max_points = 0;

        /**
         * ── CATEGORY: TRADITIONAL SEO (Max Base: 100) ──
         * We use a fixed-point system to ensure consistency.
         */

        // 1. Focus Keyword Set (5 pts)
        $max_points += 5;
        if ( $kw ) {
            $points += 5;
            $checks[] = self::pass( "Focus keyword is set: \"{$kw}\".", 'kw_set', 'basic' );
        } else {
            $checks[] = self::fail( 'No focus keyword set.', 'kw_set', 'basic' );
        }

        // 2. Keyword in Post Title (10 pts)
        $max_points += 10;
        $title_sources = trim( $title . ' ' . ( $meta['meta_title'] ?? '' ) );
        if ( $kw && self::keyword_matches( $title_sources, $kw ) ) {
            $points += 10;
            $checks[] = self::pass( 'Focus keyword found in the page title or SEO title. ✓', 'kw_title', 'basic' );
        } else {
            $checks[] = self::fail( 'Add focus keyword to your page title or SEO title.', 'kw_title', 'basic' );
        }

        // 3. Meta Title Length (8 pts)
        $max_points += 8;
        $mtl = mb_strlen( $meta['meta_title'] ?? '' );
        if ( $mtl >= 50 && $mtl <= 60 ) {
            $points += 8;
            $checks[] = self::pass( "Meta title length is ideal ({$mtl} chars).", 'meta_title_len', 'basic' );
        } elseif ( $mtl > 0 ) {
            $points += 4;
            $checks[] = self::warn( "Meta title is {$mtl} chars. Aim for 50–60.", 'meta_title_len', 'basic' );
        } else {
            $checks[] = self::fail( "Missing Meta Title.", 'meta_title_len', 'basic' );
        }

        // 4. Meta Description Length (8 pts)
        $max_points += 8;
        $mdl = mb_strlen( $meta['meta_description'] ?? '' );
        if ( $mdl >= 145 && $mdl <= 160 ) {
            $points += 8;
            $checks[] = self::pass( "Meta description length is ideal ({$mdl} chars).", 'meta_desc_len', 'basic' );
        } elseif ( $mdl > 0 ) {
            $points += 4;
            $checks[] = self::warn( "Meta description is {$mdl} chars. Aim for 145–160.", 'meta_desc_len', 'basic' );
        } else {
            $checks[] = self::fail( "Missing Meta Description.", 'meta_desc_len', 'basic' );
        }

        // 5. Featured Image (6 pts)
        $max_points += 6;
        if ( $feat_id ) {
            $points += 6;
            $checks[] = self::pass( 'Featured image is set.', 'featured_img', 'basic' );
        } else {
            $checks[] = self::fail( 'No featured image set.', 'featured_img', 'basic' );
        }

        // ── CATEGORY: CONTENT & CORNERSTONE ───────────────────────

        // 6. Word Count (10 pts)
        $min_words = $is_cornerstone ? 1500 : 900;
        $max_points += 10;
        if ( $word_count >= $min_words ) {
            $points += 10;
            $checks[] = self::pass( "Content length is excellent: {$word_count} words.", 'word_count', 'content' );
        } elseif ( $word_count >= ( $min_words / 2 ) ) {
            $points += 5;
            $checks[] = self::warn( "{$word_count} words. " . ($is_cornerstone ? "Cornerstone needs 1500+." : "Aim for 900+."), 'word_count', 'content' );
        } else {
            $checks[] = self::fail( "Content is too short ({$word_count} words).", 'word_count', 'content' );
        }

        // 7. Focus Keyword in Introduction (8 pts)
        if ( $kw ) {
            $max_points += 8;
            $intro_text = mb_substr( $text, 0, 700 );
            if ( self::keyword_matches( $intro_text, $kw ) ) {
                $points += 8;
                $checks[] = self::pass( 'Focus keyword found in the introduction.', 'kw_intro', 'basic' );
            } else {
                $checks[] = self::fail( 'Focus keyword not found in the first paragraph.', 'kw_intro', 'basic' );
            }
        }

        // 8. Focus Keyword in Subheadings (7 pts)
        if ( $kw ) {
            $max_points += 7;
            preg_match_all( '/<h[234][^>]*>(.*?)<\/h[234]>/is', $content, $h_tags );
            $h_combined = wp_strip_all_tags( implode( ' ', $h_tags[0] ) );
            if ( self::keyword_matches( $h_combined, $kw ) ) {
                $points += 7;
                $checks[] = self::pass( 'Focus keyword used in subheadings (H2, H3, or H4).', 'kw_subheadings', 'basic' );
            } else {
                $checks[] = self::warn( 'Use your focus keyword in at least one subheading.', 'kw_subheadings', 'basic' );
            }
        }

        // 9. Focus Keyword in Image Alt Attributes (6 pts)
        if ( $kw ) {
            $max_points += 6;

            // Robust regex to find alt attributes regardless of quote style or spacing
            // Matches: alt="keyword", alt='keyword', alt = "keyword", etc.
            preg_match_all( '/alt\s*=\s*["\']([^"\']*)["\']/i', $content, $alt_matches );

            $alt_texts = $alt_matches[1];

            // Also check the featured image alt text
            if ( $feat_id ) {
                $feat_alt = get_post_meta( $feat_id, '_wp_attachment_image_alt', true );
                if ( ! empty( $feat_alt ) ) {
                    $alt_texts[] = $feat_alt;
                }
            }

            $alt_combined = implode( ' ', $alt_texts );
            if ( ! empty( $alt_combined ) && self::keyword_matches( $alt_combined, $kw ) ) {
                $points += 6;
                $checks[] = self::pass( 'Focus keyword found in image alt attributes.', 'kw_alt', 'basic' );
            } else {
                $checks[] = self::warn( 'Add your focus keyword to your image alt attributes.', 'kw_alt', 'basic' );
            }
        }

        // 10. Focus Keyword Density (5 pts)
        if ( $kw && $word_count > 0 ) {
            $max_points += 5;
            $kw_matches = self::keyword_occurrences( $text, $kw );
            $density = round( ( $kw_matches / $word_count ) * 100, 2 );

            if ( $density >= 0.5 && $density <= 2.5 ) {
                $points += 5;
                $checks[] = self::pass( "Focus keyword density is perfect ({$density}%).", 'kw_density', 'basic' );
            } elseif ( $density > 2.5 ) {
                $points += 2;
                $checks[] = self::warn( "Keyword density is high ({$density}%). Avoid keyword stuffing.", 'kw_density', 'basic' );
            } elseif ( self::keyword_token_coverage( $text, $kw ) >= 0.8 ) {
                $points += 3;
                $checks[] = self::warn( 'Keyword terms appear naturally, but the exact focus phrase is rare. Use the exact phrase once or twice in body copy.', 'kw_density', 'basic' );
            } else {
                $points += 2;
                $checks[] = self::warn( "Keyword density is low ({$density}%). Aim for 0.5% – 2.5%.", 'kw_density', 'basic' );
            }
        }

        // 11. Internal Links (6 pts)
        $min_links = $is_cornerstone ? 5 : 2;
        $max_points += 6;
        $site_host = wp_parse_url( home_url(), PHP_URL_HOST );
        preg_match_all( '/<a[^>]+href=["\']https?:\/\/' . preg_quote($site_host,'/').'[^"\']*["\'][^>]*>/i', $content, $int_links );
        $int_count = count( $int_links[0] );
        if ( $int_count >= $min_links ) {
            $points += 6;
            $checks[] = self::pass( "{$int_count} internal links found.", 'int_links', 'content' );
        } else {
            $checks[] = self::warn( "Found {$int_count} internal links. Add more to reach {$min_links}.", 'int_links', 'content' );
        }

        // 11b. External Links (5 pts)
        $max_points += 5;
        preg_match_all( '/<a[^>]+href=["\'](https?:\/\/(?!' . preg_quote($site_host,'/') . ')[^"\']+)["\'][^>]*>/i', $content, $ext_links );
        $ext_count = count( $ext_links[0] );
        if ( $ext_count > 0 ) {
            $points += 5;
            $checks[] = self::pass( "{$ext_count} outbound links found.", 'ext_links', 'content' );
        } else {
            $checks[] = self::warn( 'Add at least one link to an external source.', 'ext_links', 'content' );
        }

        // 12. AI Content Audit Engine (Topical Depth) (15 pts)
        if ( class_exists('GateTouch_AI_Engine') && \GateTouch_AI_Engine::is_api_operational() && $word_count > 300 ) {
            $max_points += 15;
            $depth_meta = get_post_meta( $post_id, '_gatetouch_ai_depth_cache', true );

            if ( empty($depth_meta) || ( time() - ($depth_meta['time'] ?? 0) > HOUR_IN_SECONDS ) ) {
                $depth_meta = \GateTouch_AI_Engine::audit_content_depth( $title, $text );
                $depth_meta['time'] = time();
                update_post_meta( $post_id, '_gatetouch_ai_depth_cache', $depth_meta );
            }

            if ( ! isset($depth_meta['error']) && isset($depth_meta['depth_score']) ) {
                $d_score = (int) $depth_meta['depth_score'];
                $points += ($d_score / 100) * 15;

                if ( $d_score >= 80 ) {
                    $checks[] = self::pass( "Excellent Topical Depth ({$d_score}%).", 'ai_topical_depth', 'content' );
                } else {
                    $checks[] = self::warn( "AI Topical Depth is {$d_score}%. Missing some intents.", 'ai_topical_depth', 'content' );
                }
            } else {
                $points += 10;
            }
        }

        // 13. Focus Keyword in URL (Slug) (7 pts)
        if ( $kw ) {
            $max_points += 7;
            $slug = basename( get_permalink( $post_id ) );
            if ( self::keyword_matches( str_replace( '-', ' ', $slug ), $kw ) ) {
                $points += 7;
                $checks[] = self::pass( 'Focus keyword found in the URL slug. ✓', 'kw_url', 'basic' );
            } else {
                $checks[] = self::warn( 'Include the focus keyword in your URL slug.', 'kw_url', 'basic' );
            }
        }

        // ── CATEGORY: READABILITY ─────────────────────────────────

        // 14. Sentence Length (6 pts)
        $max_points += 6;
        $sentence_count  = max( 1, count( $sentences ) );
        $avg_sent_words  = round( $word_count / $sentence_count, 1 );
        if ( $avg_sent_words <= 20 ) {
            $points += 6;
            $checks[] = self::pass( "Avg sentence length {$avg_sent_words} words.", 'readability', 'readability' );
        } else {
            $checks[] = self::warn( "Sentences are long (avg {$avg_sent_words} words).", 'readability', 'readability' );
        }

        // 15. Subheadings (8 pts)
        $max_points += 8;
        preg_match_all( '/<h([23456])[^>]*>(.*?)<\/h\1>/is', $content, $h_matches );
        $h_count = count( $h_matches[0] );
        if ( $h_count >= 2 ) {
            $points += 8;
            $checks[] = self::pass( "Great structure: {$h_count} subheadings found.", 'headings', 'readability' );
        } else {
            $checks[] = self::warn( 'Add more subheadings to improve readability.', 'headings', 'readability' );
        }

        // 16. Secondary Keywords (Bonus: Does not increase max_points)
        if ( ! empty( $add_kws ) ) {
            foreach ( $add_kws as $akw ) {
                if ( self::keyword_matches( $text_lc, $akw ) ) {
                    $points += 1; // Bonus points for each secondary kw
                    $checks[] = self::pass( "Secondary keyword \"{$akw}\" used.", 'akw_' . sanitize_title($akw), 'keywords' );
                } else {
                    $checks[] = self::warn( "Secondary keyword \"{$akw}\" missing.", 'akw_' . sanitize_title($akw), 'keywords' );
                }
            }
        }

        $final = $max_points > 0 ? (int) round( ( $points / $max_points ) * 100 ) : 0;
        $final = min( 100, $final );

        // Initialize $save_meta with existing meta and update with new analysis data
        $save_meta = $meta;
        if ( ! empty( $focus_keyword ) ) {
            $save_meta['focus_keyword'] = $focus_keyword;
        }

        // ── SYNC WITH CENTRAL SCORING ENGINE ──
        // This ensures the Editor, Dashboard, and AEO Center always show identical numbers.
        require_once GATETOUCH_PATH . 'includes/class-scoring-engine.php';
        $audit_results = GateTouch_Scoring_Engine::audit_post( $post_id, [
            'content' => $content,
            'title'   => $title,
            'meta'    => $save_meta
        ] );

        $final    = $audit_results['seo']['score'];
        $ai_final = $audit_results['total_score'];

        $final_results = [
            'score'      => $final,
            'ai_score'   => $ai_final,
            'ai_color'   => self::score_color( $ai_final ),
            'color'      => self::score_color( $final ),
            'label'      => self::score_label( $final ),
            'checks'     => $checks,
            'word_count' => $word_count,
            'is_cornerstone' => $is_cornerstone,
            'pass_count' => count( array_filter( $checks, function($c) { return $c['status'] === 'pass'; } ) ),
            'warn_count' => count( array_filter( $checks, function($c) { return $c['status'] === 'warn'; } ) ),
            'fail_count' => count( array_filter( $checks, function($c) { return $c['status'] === 'fail'; } ) ),
        ];

        // Social Fallbacks
        $og_image = $meta['og_image'] ?? '';
        if ( empty( $og_image ) ) {
            $feat_id = get_post_thumbnail_id( $post_id );
            if ( $feat_id ) {
                $og_image = wp_get_attachment_url( $feat_id );
            } else {
                $logo_id = get_theme_mod( 'custom_logo' );
                if ( $logo_id ) {
                    $og_image = wp_get_attachment_url( $logo_id );
                }
            }
        }
        $final_results['fallback_og_image'] = $og_image;

        // Persistence: Save key metrics for fast retrieval in columns/dashboards
        $save_meta['score']          = $final;
        $save_meta['ai_score']       = $ai_final;
        $save_meta['word_count']     = $word_count;
        $save_meta['last_optimized'] = time();
        $save_meta['checks_summary'] = [
            'pass' => $final_results['pass_count'],
            'warn' => $final_results['warn_count'],
            'fail' => $final_results['fail_count'],
        ];
        $save_meta['checks'] = $checks;
        update_post_meta( $post_id, GATETOUCH_META_KEY, $save_meta );

        // Invalidate the Site Audit transient cache so the audit tab reflects these changes
        update_option( 'gatetouch_audit_cache_version', time() );

        // Ensure the returned results match the persisted data exactly
        $final_results['focus_keyword'] = $save_meta['focus_keyword'] ?? '';

        return $final_results;
    }


    public static function pass( $msg, $key, $cat = 'basic' ) {
        $guidance = GateTouch_SEO_Library::get_guidance( $key );
        return array_merge( $guidance, [ 'status' => 'pass', 'message' => $msg, 'key' => $key, 'category' => $cat ] );
    }

    public static function warn( $msg, $key, $cat = 'basic' ) {
        $guidance = GateTouch_SEO_Library::get_guidance( $key );
        return array_merge( $guidance, [ 'status' => 'warn', 'message' => $msg, 'key' => $key, 'category' => $cat ] );
    }

    public static function fail( $msg, $key, $cat = 'basic' ) {
        $guidance = GateTouch_SEO_Library::get_guidance( $key );
        return array_merge( $guidance, [ 'status' => 'fail', 'message' => $msg, 'key' => $key, 'category' => $cat ] );
    }

    public static function score_color( $score ) {
        if ( $score >= 80 ) return '#10b981';
        if ( $score >= 50 ) return '#f59e0b';
        return '#ef4444';
    }

    public static function score_label( $score ) {
        if ( $score >= 80 ) return __( 'Excellent', 'gatetouch-ai-seo' );
        if ( $score >= 65 ) return __( 'Good', 'gatetouch-ai-seo' );
        if ( $score >= 50 ) return __( 'Needs Work', 'gatetouch-ai-seo' );
        return __( 'Poor', 'gatetouch-ai-seo' );
    }
}
