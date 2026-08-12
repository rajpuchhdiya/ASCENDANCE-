<?php
defined( 'ABSPATH' ) || exit;

/**
 * GateTouch Library
 * 
 * Centralized knowledge base for SEO issue explanations, guidance, and fixes.
 */
class GateTouch_SEO_Library {

    /**
     * Get guidance for a specific SEO check
     */
    public static function get_guidance( $key ) {
        $library = [
            'missing_meta' => [
                'priority'      => 'High',
                'title'         => __( 'Missing Meta Description', 'gatetouch-ai-seo' ),
                'explanation'   => __( 'This page does not contain a meta description tag. Search engines use this text to display snippets in search results.', 'gatetouch-ai-seo' ),
                'seo_impact'    => [
                    'Lower Click-Through Rate (CTR).',
                    'Search engines may show irrelevant text from your page.',
                    'Weakened relevance signals for your focus keywords.'
                ],
                'owner_impact'  => __( 'Potential visitors are less likely to click on your site if they see a generic or messy snippet in Google.', 'gatetouch-ai-seo' ),
                'fix_beginner'  => __( 'Write a concise summary (145-160 chars) of your page content including your main keyword.', 'gatetouch-ai-seo' ),
                'fix_developer' => __( 'Ensure your SEO meta tags are enqueued in wp_head(). GT SEO/GEO/AEO handles this automatically via the standard WP hooks.', 'gatetouch-ai-seo' ),
                'code_example'  => '<meta name="description" content="Your compelling meta description here.">',
                'best_practices' => __( 'Keep descriptions between 145-160 characters. Avoid keyword stuffing.', 'gatetouch-ai-seo' ),
                'learn_more'    => admin_url( 'admin.php?page=gatetouch-help&id=seo-analyzer' )
            ],
            'kw_set' => [
                'priority'      => 'High',
                'title'         => __( 'Focus Keyword Not Set', 'gatetouch-ai-seo' ),
                'explanation'   => __( 'No primary keyword has been assigned to this page. Without a target, GT SEO/GEO/AEO cannot provide specific optimization guidance.', 'gatetouch-ai-seo' ),
                'seo_impact'    => [
                    'Lack of content focus.',
                    'Inaccurate SEO scoring.',
                    'Missed opportunities for topical authority.'
                ],
                'owner_impact'  => __( 'Google won\'t know exactly what search terms this page should rank for.', 'gatetouch-ai-seo' ),
                'fix_beginner'  => __( 'Enter your target keyword in the "Focus Keyword" field in the GT SEO/GEO/AEO meta box.', 'gatetouch-ai-seo' ),
                'fix_developer' => __( 'The focus keyword is stored in the _gatetouch_seo_data post meta key.', 'gatetouch-ai-seo' ),
                'best_practices' => __( 'Choose a keyword with reasonable search volume and clear user intent.', 'gatetouch-ai-seo' ),
            ],
            'kw_title' => [
                'priority'      => 'Critical',
                'title'         => __( 'Keyword Missing in Title', 'gatetouch-ai-seo' ),
                'explanation'   => __( 'Your focus keyword is missing from the H1 / Meta Title. The title is the strongest on-page ranking signal.', 'gatetouch-ai-seo' ),
                'seo_impact'    => [
                    'Significantly lower rankings for the target term.',
                    'Reduced relevance score from Google\'s algorithm.',
                    'Poor user intent alignment.'
                ],
                'owner_impact'  => __( 'This is the #1 reason why pages fail to rank for their intended keywords.', 'gatetouch-ai-seo' ),
                'fix_beginner'  => __( 'Update your post title to include your focus keyword, ideally near the beginning.', 'gatetouch-ai-seo' ),
                'fix_developer' => __( 'Ensure your <h1> tag matches your SEO meta title for the best results.', 'gatetouch-ai-seo' ),
                'code_example'  => '<h1>Professional [Focus Keyword] Solutions</h1>',
                'best_practices' => __( 'Place the keyword as close to the start of the title as possible.', 'gatetouch-ai-seo' ),
            ],
            'word_count' => [
                'priority'      => 'Medium',
                'title'         => __( 'Thin Content Detected', 'gatetouch-ai-seo' ),
                'explanation'   => __( 'The content length is below the recommended threshold for this content type.', 'gatetouch-ai-seo' ),
                'seo_impact'    => [
                    'Difficulty ranking for competitive terms.',
                    'Lower "Quality Score" from search engines.',
                    'Reduced topical authority.'
                ],
                'owner_impact'  => __( 'Short pages often provide less value to users, causing them to leave quickly (high bounce rate).', 'gatetouch-ai-seo' ),
                'fix_beginner'  => __( 'Expand your content by adding more details, answering common questions, and providing unique insights.', 'gatetouch-ai-seo' ),
                'fix_developer' => __( 'Check for "Content Cannibalization" where multiple short pages could be merged into one authoritative guide.', 'gatetouch-ai-seo' ),
                'best_practices' => __( 'Aim for 900+ words for standard posts, and 1500+ for cornerstone content.', 'gatetouch-ai-seo' ),
            ],
            'missing_sitemap' => [
                'priority'      => 'High',
                'title'         => __( 'Missing XML Sitemap', 'gatetouch-ai-seo' ),
                'explanation'   => __( 'No active XML sitemap was found. Sitemaps help search engines find and index your content more efficiently.', 'gatetouch-ai-seo' ),
                'seo_impact'    => [
                    'New posts may take longer to appear in search results.',
                    'Search engines might miss some of your pages.',
                    'Inaccurate crawling of site updates.'
                ],
                'owner_impact'  => __( 'Your latest updates and new products might remain invisible to Google for days.', 'gatetouch-ai-seo' ),
                'fix_beginner'  => __( 'Enable the GT SEO/GEO/AEO sitemap feature in the settings panel.', 'gatetouch-ai-seo' ),
                'fix_developer' => __( 'Ensure your robots.txt links to your sitemap_index.xml file.', 'gatetouch-ai-seo' ),
                'code_example'  => 'Sitemap: ' . home_url('/sitemap_index.xml'),
                'best_practices' => __( 'Submit your sitemap directly to Google Search Console.', 'gatetouch-ai-seo' ),
            ],
            'kw_alt' => [
                'priority'      => 'Medium',
                'title'         => __( 'Keyword in Image Alt Text', 'gatetouch-ai-seo' ),
                'explanation'   => __( 'At least one image on this page is missing your focus keyword in its alt attribute.', 'gatetouch-ai-seo' ),
                'seo_impact'    => [
                    'Reduced visibility in Image Search.',
                    'Weaker topical relevance signals.',
                    'Poor accessibility for screen readers.'
                ],
                'owner_impact'  => __( 'Search engines use alt text to understand what an image is about. If it matches your keyword, it boosts your page\'s overall relevance.', 'gatetouch-ai-seo' ),
                'fix_beginner'  => __( 'Click on an image in the editor and enter your focus keyword in the "Alt Text" field in the sidebar.', 'gatetouch-ai-seo' ),
                'fix_developer' => __( 'Ensure all <img> tags have an alt="" attribute. Use the focus keyword naturally.', 'gatetouch-ai-seo' ),
                'best_practices' => __( 'Describe the image accurately while including the keyword where it makes sense.', 'gatetouch-ai-seo' ),
            ],
            'ai_topical_depth' => [
                'priority'      => 'High',
                'title'         => __( 'Low Topical Depth (AI Audit)', 'gatetouch-ai-seo' ),
                'explanation'   => __( 'The AI engine has detected that your content covers fewer topics than the top-ranking competitors.', 'gatetouch-ai-seo' ),
                'seo_impact'    => [
                    'Difficulty outranking authoritative sites.',
                    'Lower "Answer Engine" visibility (SGE/Gemini).',
                    'Less comprehensive user experience.'
                ],
                'owner_impact'  => __( 'Google prefers content that answers a user\'s query completely. If you skip important topics, users will go back to search to find another site.', 'gatetouch-ai-seo' ),
                'fix_beginner'  => __( 'Review the "AI & AEO Audit" tab. Look at the "Missing Topics" list and add sections to your content that address those specific areas.', 'gatetouch-ai-seo' ),
                'fix_developer' => __( 'Use entity-based SEO by including related semantic terms (LSI keywords) throughout the page.', 'gatetouch-ai-seo' ),
                'best_practices' => __( 'Aim for a Topical Depth score of 80% or higher for the best chance of ranking.', 'gatetouch-ai-seo' ),
                'learn_more'    => admin_url( 'admin.php?page=gatetouch-help&id=aeo' )
            ],
            'meta_title_len' => [
                'priority'      => 'Medium',
                'title'         => __( 'Meta Title Length', 'gatetouch-ai-seo' ),
                'explanation'   => __( 'Your SEO title is either too short or too long. Google typically truncates titles longer than 60 characters.', 'gatetouch-ai-seo' ),
                'seo_impact'    => [ 'Lower CTR if title is cut off.', 'Reduced relevance if too short.' ],
                'fix_beginner'  => __( 'Aim for 50–60 characters. Make it compelling and include your focus keyword.', 'gatetouch-ai-seo' ),
            ],
            'meta_desc_len' => [
                'priority'      => 'Medium',
                'title'         => __( 'Meta Description Length', 'gatetouch-ai-seo' ),
                'explanation'   => __( 'Meta descriptions should be between 145–160 characters to avoid being cut off in search results.', 'gatetouch-ai-seo' ),
                'seo_impact'    => [ 'Truncated snippets look unprofessional.', 'Lower click-through rates.' ],
                'fix_beginner'  => __( 'Write a summary between 145–160 characters that encourages users to click.', 'gatetouch-ai-seo' ),
            ],
            'featured_img' => [
                'priority'      => 'Low',
                'title'         => __( 'Missing Featured Image', 'gatetouch-ai-seo' ),
                'explanation'   => __( 'No featured image is set. Images improve social sharing and user engagement.', 'gatetouch-ai-seo' ),
                'fix_beginner'  => __( 'Set a featured image in the sidebar of the editor.', 'gatetouch-ai-seo' ),
            ],
            'kw_intro' => [
                'priority'      => 'High',
                'title'         => __( 'Keyword in Introduction', 'gatetouch-ai-seo' ),
                'explanation'   => __( 'Your focus keyword was not found in the first paragraph of your content.', 'gatetouch-ai-seo' ),
                'seo_impact'    => [ 'Weakens the immediate relevance signal for users and AI.' ],
                'fix_beginner'  => __( 'Include your focus keyword naturally within the first 100-150 words.', 'gatetouch-ai-seo' ),
            ],
            'kw_subheadings' => [
                'priority'      => 'Medium',
                'title'         => __( 'Keyword in Subheadings', 'gatetouch-ai-seo' ),
                'explanation'   => __( 'None of your subheadings (H2, H3) contain the focus keyword.', 'gatetouch-ai-seo' ),
                'fix_beginner'  => __( 'Include your focus keyword in at least one H2 or H3 tag.', 'gatetouch-ai-seo' ),
            ],
            'kw_density' => [
                'priority'      => 'Low',
                'title'         => __( 'Keyword Density', 'gatetouch-ai-seo' ),
                'explanation'   => __( 'Your keyword appears too many or too few times relative to the word count.', 'gatetouch-ai-seo' ),
                'fix_beginner'  => __( 'Aim for a density between 0.5% and 2.5%. Avoid over-optimization.', 'gatetouch-ai-seo' ),
            ],
            'kw_url' => [
                'priority'      => 'Medium',
                'title'         => __( 'Keyword in URL', 'gatetouch-ai-seo' ),
                'explanation'   => __( 'The URL slug does not contain your focus keyword.', 'gatetouch-ai-seo' ),
                'fix_beginner'  => __( 'Update the permalink to include the focus keyword (e.g., your-keyword-here).', 'gatetouch-ai-seo' ),
            ],
            'int_links' => [
                'priority'      => 'Medium',
                'title'         => __( 'Internal Linking', 'gatetouch-ai-seo' ),
                'explanation'   => __( 'Internal links help distribute authority and guide users through your site.', 'gatetouch-ai-seo' ),
                'fix_beginner'  => __( 'Link to at least 2 other relevant pages on your own website.', 'gatetouch-ai-seo' ),
            ],
            'ext_links' => [
                'priority'      => 'Low',
                'title'         => __( 'Outbound Links', 'gatetouch-ai-seo' ),
                'explanation'   => __( 'Linking to authoritative external sites helps build trust with search engines.', 'gatetouch-ai-seo' ),
                'fix_beginner'  => __( 'Add a link to a high-quality, relevant external resource.', 'gatetouch-ai-seo' ),
            ],
            'readability' => [
                'priority'      => 'Medium',
                'title'         => __( 'Content Readability', 'gatetouch-ai-seo' ),
                'explanation'   => __( 'Sentence length and structure impact how easily users can digest your content.', 'gatetouch-ai-seo' ),
                'fix_beginner'  => __( 'Use shorter sentences (avg 20 words or less) and break up long paragraphs.', 'gatetouch-ai-seo' ),
            ],
            'headings' => [
                'priority'      => 'Medium',
                'title'         => __( 'Subheading Structure', 'gatetouch-ai-seo' ),
                'explanation'   => __( 'Subheadings (H2, H3) help users scan your content and provide semantic structure.', 'gatetouch-ai-seo' ),
                'fix_beginner'  => __( 'Ensure you use at least 2 subheadings to organize your content logically.', 'gatetouch-ai-seo' ),
            ],
        ];

        return $library[$key] ?? [
            'priority'    => 'Medium',
            'title'       => str_replace('_', ' ', ucfirst($key)),
            'explanation' => __( 'We detected a potential issue with this element.', 'gatetouch-ai-seo' ),
            'seo_impact'  => [ __( 'May affect search engine visibility.', 'gatetouch-ai-seo' ) ],
            'owner_impact'=> __( 'This could impact how users find your site.', 'gatetouch-ai-seo' ),
            'fix_beginner'=> __( 'Review your SEO settings for this page.', 'gatetouch-ai-seo' ),
        ];
    }
}
