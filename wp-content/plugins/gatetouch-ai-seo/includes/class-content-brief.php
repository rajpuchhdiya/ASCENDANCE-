<?php
defined( 'ABSPATH' ) || exit;

/**
 * GateTouch_Content_Brief
 *
 * Generates a unified SEO + AEO + GEO content brief for a target keyword.
 * Aligned with Google 2024/25 ranking signals, AI Overviews, and GEO citation patterns.
 */
class GateTouch_Content_Brief {

    /**
     * Generate a full three-dimensional content brief.
     *
     * @param string $keyword   Target keyword or topic.
     * @param string $site_url  Optional — used to contextualise internal linking advice.
     * @return array            Parsed brief data or ['error' => '...'].
     */
    public static function generate( string $keyword, string $site_url = '' ): array {
        if ( ! GateTouch_AI_Engine::has_api_key() ) {
            return [ 'error' => 'Please connect your AI provider API key first.' ];
        }

        $site_context = $site_url ? "Site: {$site_url}" : '';

        $system = 'You are a senior SEO strategist, AEO (Answer Engine Optimization) expert, and GEO (Generative Engine Optimization) specialist. ' .
            'You understand Google\'s 2024/25 ranking signals (E-E-A-T, Helpful Content, AI Overviews), how AI answer engines select cited sources (AEO), ' .
            'and how generative AI systems (ChatGPT, Perplexity, Google AI Overviews) decide what to cite (GEO). ' .
            'Your job is to produce a single, actionable content brief that covers all three dimensions. Respond ONLY with a valid JSON object.';

        $user = "Target Keyword: \"{$keyword}\"
{$site_context}

Generate a comprehensive content brief. Return JSON with EXACTLY this structure:

{
  \"keyword\": \"...\",
  \"search_intent\": \"Informational | Commercial | Transactional | Navigational\",
  \"content_type\": \"Guide | Article | Comparison | FAQ | Product Page | Landing Page\",

  \"seo\": {
    \"title_tag\": \"SEO-optimised title tag (50-60 chars)\",
    \"meta_description\": \"Compelling meta description (145-160 chars) with keyword\",
    \"focus_keyword\": \"exact primary keyword\",
    \"secondary_keywords\": [\"lsi1\", \"lsi2\", \"lsi3\", \"lsi4\"],
    \"word_count_target\": \"1200-1800\",
    \"heading_structure\": [
      { \"level\": \"H1\", \"text\": \"...\" },
      { \"level\": \"H2\", \"text\": \"...\" },
      { \"level\": \"H2\", \"text\": \"...\" },
      { \"level\": \"H3\", \"text\": \"...\" }
    ],
    \"schema_type\": \"Article | FAQPage | HowTo | Product | LocalBusiness\",
    \"eeat_tips\": [\"tip about author bio\", \"tip about external citations\", \"tip about freshness\"],
    \"internal_link_anchors\": [\"anchor text 1\", \"anchor text 2\"]
  },

  \"aeo\": {
    \"featured_snippet_target\": \"The exact question this content should win a featured snippet for\",
    \"answer_box_opening\": \"A 2-3 sentence direct answer to put at the top of the content\",
    \"question_subheadings\": [\"Who is...?\", \"What is...?\", \"How does...?\", \"Why is...?\"],
    \"faqs\": [
      { \"question\": \"...\", \"answer\": \"...\" },
      { \"question\": \"...\", \"answer\": \"...\" },
      { \"question\": \"...\", \"answer\": \"...\" },
      { \"question\": \"...\", \"answer\": \"...\" }
    ],
    \"readability_target\": \"Grade 7-8 (Flesch-Kincaid)\",
    \"conversational_phrases\": [\"phrase 1\", \"phrase 2\", \"phrase 3\"]
  },

  \"geo\": {
    \"key_entities\": [\"Entity 1\", \"Entity 2\", \"Entity 3\", \"Entity 4\", \"Entity 5\"],
    \"entity_definitions\": [
      { \"entity\": \"...\", \"definition\": \"One sentence definition to include in content\" }
    ],
    \"citation_hook\": \"A single sentence that is highly quotable and citable by AI systems\",
    \"topical_cluster_ideas\": [\"supporting article 1\", \"supporting article 2\", \"supporting article 3\"],
    \"llms_txt_note\": \"Specific note on how llms.txt helps this topic get cited\",
    \"geo_tips\": [\"tip 1\", \"tip 2\", \"tip 3\"]
  },

  \"content_outline\": [
    { \"section\": \"Introduction\", \"notes\": \"Include the direct answer box and focus keyword in first 100 words\" },
    { \"section\": \"H2: ...\", \"notes\": \"...\" },
    { \"section\": \"H2: ...\", \"notes\": \"...\" },
    { \"section\": \"FAQ Section\", \"notes\": \"FAQPage schema — 4 Q&A pairs targeting AI answer boxes\" },
    { \"section\": \"Conclusion\", \"notes\": \"Include a key takeaway sentence that is citable by AI systems\" }
  ],

  \"competitive_edge\": \"One sentence on what makes this content better than existing results\"
}";

        $result = GateTouch_AI_Engine::call( $system, $user );

        if ( isset( $result['error'] ) ) {
            return $result;
        }

        // Normalise — some providers wrap the JSON inside a key
        if ( isset( $result['keyword'] ) ) {
            return $result;
        }

        // Try to find nested JSON if the model wrapped it
        foreach ( $result as $val ) {
            if ( is_array( $val ) && isset( $val['keyword'] ) ) {
                return $val;
            }
        }

        return [ 'error' => 'Unexpected response format from AI provider. Please try again.' ];
    }

    public static function save( int $post_id, array $brief ): bool {
        return (bool) update_post_meta( $post_id, '_gatetouch_content_brief', $brief );
    }

    public static function get( int $post_id ): array {
        return get_post_meta( $post_id, '_gatetouch_content_brief', true ) ?: [];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Brief library
    //
    // Every generated brief costs an API call, so they are kept. Stored in a
    // single non-autoloaded option: briefs are large, and nothing outside this
    // screen needs them, so autoloading them on every request would be waste.
    // ─────────────────────────────────────────────────────────────────────────

    const LIBRARY_OPTION = 'gatetouch_brief_library';
    const LIBRARY_MAX    = 30;

    /**
     * Stable id for a keyword, so regenerating the same topic replaces its entry
     * rather than filling the library with near-duplicates.
     */
    public static function key_for( string $keyword ): string {
        return substr( md5( strtolower( trim( $keyword ) ) ), 0, 12 );
    }

    /** @return array<string, array> Newest first. */
    public static function library(): array {
        $lib = get_option( self::LIBRARY_OPTION, [] );
        return is_array( $lib ) ? $lib : [];
    }

    /**
     * Persist a brief, newest first, trimming the oldest beyond LIBRARY_MAX.
     *
     * @return string The entry id.
     */
    public static function remember( string $keyword, array $brief ): string {
        $id  = self::key_for( $keyword );
        $lib = self::library();

        unset( $lib[ $id ] );

        // Prepend so the library reads newest first.
        $lib = [ $id => [
            'id'         => $id,
            'keyword'    => $keyword,
            'intent'     => (string) ( $brief['search_intent'] ?? '' ),
            'type'       => (string) ( $brief['content_type'] ?? '' ),
            'created'    => time(),
            'brief'      => $brief,
        ] ] + $lib;

        if ( count( $lib ) > self::LIBRARY_MAX ) {
            $lib = array_slice( $lib, 0, self::LIBRARY_MAX, true );
        }

        update_option( self::LIBRARY_OPTION, $lib, false );

        return $id;
    }

    /** @return array Empty array when the id is unknown. */
    public static function from_library( string $id ): array {
        $lib = self::library();
        return isset( $lib[ $id ]['brief'] ) && is_array( $lib[ $id ]['brief'] ) ? $lib[ $id ]['brief'] : [];
    }

    public static function cached( string $keyword ): array {
        return self::from_library( self::key_for( $keyword ) );
    }

    public static function forget( string $id ): bool {
        $lib = self::library();
        if ( ! isset( $lib[ $id ] ) ) {
            return false;
        }
        unset( $lib[ $id ] );
        update_option( self::LIBRARY_OPTION, $lib, false );
        return true;
    }

    /**
     * Library entries without the heavy brief payload — enough to render a list.
     *
     * @return array<int, array>
     */
    public static function library_index(): array {
        $out = [];
        foreach ( self::library() as $id => $entry ) {
            $out[] = [
                'id'      => $id,
                'keyword' => (string) ( $entry['keyword'] ?? '' ),
                'intent'  => (string) ( $entry['intent'] ?? '' ),
                'type'    => (string) ( $entry['type'] ?? '' ),
                'created' => (int) ( $entry['created'] ?? 0 ),
                'ago'     => isset( $entry['created'] )
                    ? sprintf(
                        /* translators: %s: human readable time difference */
                        __( '%s ago', 'gatetouch-ai-seo' ),
                        human_time_diff( (int) $entry['created'], time() )
                    )
                    : '',
            ];
        }
        return $out;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Exports
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Render the whole brief as Markdown, ready to paste into a doc, an issue
     * tracker, or a writing tool.
     */
    public static function to_markdown( array $b ): string {
        $seo = (array) ( $b['seo'] ?? [] );
        $aeo = (array) ( $b['aeo'] ?? [] );
        $geo = (array) ( $b['geo'] ?? [] );

        $list = static function ( $items, string $empty = '_None specified_' ): string {
            $items = array_filter( array_map( 'strval', (array) $items ), 'strlen' );
            if ( ! $items ) {
                return $empty . "\n";
            }
            return implode( "\n", array_map( static function ( $i ) { return '- ' . $i; }, $items ) ) . "\n";
        };

        $md  = '# Content Brief: ' . (string) ( $b['keyword'] ?? '' ) . "\n\n";
        $md .= '**Search intent:** ' . ( $b['search_intent'] ?? '—' ) . "  \n";
        $md .= '**Content type:** ' . ( $b['content_type'] ?? '—' ) . "  \n";
        $md .= '**Target length:** ' . ( $seo['word_count_target'] ?? '—' ) . " words\n\n";

        if ( ! empty( $b['competitive_edge'] ) ) {
            $md .= '> ' . $b['competitive_edge'] . "\n\n";
        }

        $md .= "---\n\n## SEO\n\n";
        $md .= '**Title tag:** ' . ( $seo['title_tag'] ?? '—' ) . "  \n";
        $md .= '**Meta description:** ' . ( $seo['meta_description'] ?? '—' ) . "  \n";
        $md .= '**Focus keyword:** ' . ( $seo['focus_keyword'] ?? '—' ) . "  \n";
        $md .= '**Schema type:** ' . ( $seo['schema_type'] ?? '—' ) . "\n\n";
        $md .= "### Secondary keywords\n" . $list( $seo['secondary_keywords'] ?? [] ) . "\n";

        $md .= "### Heading structure\n";
        $headings = (array) ( $seo['heading_structure'] ?? [] );
        if ( $headings ) {
            foreach ( $headings as $h ) {
                $level = strtoupper( (string) ( $h['level'] ?? 'H2' ) );
                $depth = max( 0, (int) substr( $level, 1 ) - 1 );
                $md   .= str_repeat( '  ', $depth ) . '- **' . $level . '** ' . ( $h['text'] ?? '' ) . "\n";
            }
        } else {
            $md .= "_None specified_\n";
        }
        $md .= "\n### E-E-A-T\n" . $list( $seo['eeat_tips'] ?? [] ) . "\n";
        $md .= "### Internal link anchors\n" . $list( $seo['internal_link_anchors'] ?? [] ) . "\n";

        $md .= "---\n\n## AEO — Answer Engine Optimization\n\n";
        $md .= '**Featured snippet target:** ' . ( $aeo['featured_snippet_target'] ?? '—' ) . "\n\n";
        $md .= "**Answer box opening:**\n\n> " . str_replace( "\n", "\n> ", (string) ( $aeo['answer_box_opening'] ?? '—' ) ) . "\n\n";
        $md .= '**Readability target:** ' . ( $aeo['readability_target'] ?? '—' ) . "\n\n";
        $md .= "### Question subheadings\n" . $list( $aeo['question_subheadings'] ?? [] ) . "\n";
        $md .= "### Conversational phrases\n" . $list( $aeo['conversational_phrases'] ?? [] ) . "\n";

        $md .= "### FAQs\n\n";
        $faqs = (array) ( $aeo['faqs'] ?? [] );
        if ( $faqs ) {
            foreach ( $faqs as $i => $faq ) {
                $md .= '**Q' . ( $i + 1 ) . '. ' . ( $faq['question'] ?? '' ) . "**\n\n" . ( $faq['answer'] ?? '' ) . "\n\n";
            }
        } else {
            $md .= "_None generated_\n\n";
        }

        $md .= "---\n\n## GEO — Generative Engine Optimization\n\n";
        $md .= "**Citation hook:**\n\n> " . (string) ( $geo['citation_hook'] ?? '—' ) . "\n\n";
        $md .= "### Key entities\n" . $list( $geo['key_entities'] ?? [] ) . "\n";

        $md .= "### Entity definitions\n";
        $defs = (array) ( $geo['entity_definitions'] ?? [] );
        if ( $defs ) {
            foreach ( $defs as $d ) {
                $md .= '- **' . ( $d['entity'] ?? '' ) . '** — ' . ( $d['definition'] ?? '' ) . "\n";
            }
        } else {
            $md .= "_None specified_\n";
        }
        $md .= "\n### Supporting cluster ideas\n" . $list( $geo['topical_cluster_ideas'] ?? [] ) . "\n";
        $md .= "### GEO tips\n" . $list( $geo['geo_tips'] ?? [] ) . "\n";

        $md .= "---\n\n## Content outline\n\n";
        $outline = (array) ( $b['content_outline'] ?? [] );
        if ( $outline ) {
            foreach ( $outline as $i => $sec ) {
                $md .= ( $i + 1 ) . '. **' . ( $sec['section'] ?? '' ) . "**  \n   " . ( $sec['notes'] ?? '' ) . "\n";
            }
        } else {
            $md .= "_None specified_\n";
        }

        return $md;
    }

    /**
     * Build block-editor post content from the brief.
     *
     * This is a scaffold, not an article: the answer box, every planned heading
     * with its guidance, and a real FAQ section. The writer replaces the guidance
     * notes with prose. No AI call is made, so it costs nothing and is instant.
     */
    public static function to_post_content( array $b ): string {
        $seo = (array) ( $b['seo'] ?? [] );
        $aeo = (array) ( $b['aeo'] ?? [] );
        $geo = (array) ( $b['geo'] ?? [] );

        $blocks = [];

        $para = static function ( string $text ) {
            return "<!-- wp:paragraph -->\n<p>" . wp_kses_post( $text ) . "</p>\n<!-- /wp:paragraph -->";
        };
        $heading = static function ( string $text, int $level = 2 ) {
            return '<!-- wp:heading {"level":' . $level . "} -->\n<h{$level}>" . esc_html( $text ) . "</h{$level}>\n<!-- /wp:heading -->";
        };

        // Direct answer up front — the single biggest AEO/GEO lever.
        if ( ! empty( $aeo['answer_box_opening'] ) ) {
            $blocks[] = $para( (string) $aeo['answer_box_opening'] );
        }

        // Writer guidance that never reaches the front end.
        $guidance = [];
        if ( ! empty( $seo['focus_keyword'] ) )      { $guidance[] = 'Focus keyword: ' . $seo['focus_keyword']; }
        if ( ! empty( $seo['word_count_target'] ) )  { $guidance[] = 'Target length: ' . $seo['word_count_target'] . ' words'; }
        if ( ! empty( $aeo['readability_target'] ) ) { $guidance[] = 'Readability: ' . $aeo['readability_target']; }
        if ( ! empty( $geo['citation_hook'] ) )      { $guidance[] = 'Citation hook to work in: ' . $geo['citation_hook']; }
        if ( ! empty( $seo['secondary_keywords'] ) ) { $guidance[] = 'Secondary keywords: ' . implode( ', ', (array) $seo['secondary_keywords'] ); }
        if ( ! empty( $geo['key_entities'] ) )       { $guidance[] = 'Entities to define: ' . implode( ', ', (array) $geo['key_entities'] ); }

        if ( $guidance ) {
            $blocks[] = "<!-- wp:paragraph -->\n<p><em>" . esc_html__( 'Brief notes (delete before publishing):', 'gatetouch-ai-seo' ) . '</em><br />'
                . esc_html( implode( ' · ', $guidance ) ) . "</p>\n<!-- /wp:paragraph -->";
        }

        // One section per outline entry, with its notes as a starting placeholder.
        $outline = (array) ( $b['content_outline'] ?? [] );
        if ( ! $outline && ! empty( $seo['heading_structure'] ) ) {
            foreach ( (array) $seo['heading_structure'] as $h ) {
                if ( strtoupper( (string) ( $h['level'] ?? '' ) ) === 'H1' ) {
                    continue;
                }
                $outline[] = [ 'section' => (string) ( $h['text'] ?? '' ), 'notes' => '' ];
            }
        }

        foreach ( $outline as $sec ) {
            $title = trim( preg_replace( '/^H[2-4]:\s*/i', '', (string) ( $sec['section'] ?? '' ) ) );
            if ( '' === $title ) {
                continue;
            }
            // The FAQ section is built from the real Q&A pairs below.
            if ( preg_match( '/^faq/i', $title ) ) {
                continue;
            }
            $blocks[] = $heading( $title, 2 );
            $blocks[] = $para( '' !== (string) ( $sec['notes'] ?? '' ) ? (string) $sec['notes'] : '…' );
        }

        // FAQ section — matches the FAQPage schema the plugin can emit.
        $faqs = (array) ( $aeo['faqs'] ?? [] );
        if ( $faqs ) {
            $blocks[] = $heading( __( 'Frequently asked questions', 'gatetouch-ai-seo' ), 2 );
            foreach ( $faqs as $faq ) {
                if ( empty( $faq['question'] ) ) {
                    continue;
                }
                $blocks[] = $heading( (string) $faq['question'], 3 );
                $blocks[] = $para( (string) ( $faq['answer'] ?? '' ) );
            }
        }

        return implode( "\n\n", $blocks );
    }

    /**
     * Create a draft post from a brief and apply everything the brief decided:
     * title, meta description, focus keyword, schema type and the FAQ pairs.
     *
     * @return array{post_id:int, edit_url:string}|array{error:string}
     */
    public static function create_draft( array $brief, string $status = 'draft' ) {
        if ( ! current_user_can( 'edit_posts' ) ) {
            return [ 'error' => __( 'You do not have permission to create posts.', 'gatetouch-ai-seo' ) ];
        }

        $seo = (array) ( $brief['seo'] ?? [] );
        $aeo = (array) ( $brief['aeo'] ?? [] );

        // Prefer the H1 for the post title; the title tag is the SERP headline.
        $title = '';
        foreach ( (array) ( $seo['heading_structure'] ?? [] ) as $h ) {
            if ( strtoupper( (string) ( $h['level'] ?? '' ) ) === 'H1' ) {
                $title = (string) ( $h['text'] ?? '' );
                break;
            }
        }
        if ( '' === $title ) {
            $title = (string) ( $seo['title_tag'] ?? ( $brief['keyword'] ?? __( 'Untitled brief', 'gatetouch-ai-seo' ) ) );
        }

        $status  = in_array( $status, [ 'draft', 'pending' ], true ) ? $status : 'draft';
        $post_id = wp_insert_post( [
            'post_title'   => wp_strip_all_tags( $title ),
            'post_content' => self::to_post_content( $brief ),
            'post_status'  => $status,
            'post_type'    => 'post',
        ], true );

        if ( is_wp_error( $post_id ) ) {
            return [ 'error' => $post_id->get_error_message() ];
        }

        // Apply the brief's SEO decisions using the plugin's own meta shape.
        $meta = [
            'meta_title'       => (string) ( $seo['title_tag'] ?? '' ),
            'meta_description' => (string) ( $seo['meta_description'] ?? '' ),
            'focus_keyword'    => (string) ( $seo['focus_keyword'] ?? ( $brief['keyword'] ?? '' ) ),
        ];

        if ( ! empty( $seo['schema_type'] ) ) {
            // The model sometimes returns "Article | FAQPage"; take the first.
            $meta['schema_type'] = trim( strtok( (string) $seo['schema_type'], '|' ) );
        }
        if ( ! empty( $seo['secondary_keywords'] ) ) {
            // Stored as a comma-separated string, not an array — that is the
            // shape the meta box saves and the shape class-analysis.php
            // explode()s when it scores the post on the edit screen.
            $meta['additional_keywords'] = implode( ', ', array_map( 'sanitize_text_field', (array) $seo['secondary_keywords'] ) );
        }
        if ( ! empty( $aeo['faqs'] ) ) {
            $meta['faqs'] = array_values( array_filter( array_map( static function ( $f ) {
                return empty( $f['question'] ) ? null : [
                    'question' => sanitize_text_field( (string) $f['question'] ),
                    'answer'   => sanitize_textarea_field( (string) ( $f['answer'] ?? '' ) ),
                ];
            }, (array) $aeo['faqs'] ) ) );
        }

        update_post_meta( $post_id, GATETOUCH_META_KEY, $meta );

        // Keep the brief attached to the post it produced.
        self::save( $post_id, $brief );

        return [
            'post_id'  => (int) $post_id,
            'edit_url' => get_edit_post_link( $post_id, 'raw' ),
        ];
    }
}
