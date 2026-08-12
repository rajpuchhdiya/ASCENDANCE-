<?php
// Load WordPress bootstrap
require_once dirname( __DIR__ ) . '/wp-load.php';

// Ensure the user has admin credentials or run in CLI
if ( php_sapi_name() !== 'cli' && ! current_user_can( 'manage_options' ) ) {
    die( 'Access denied.' );
}

// 1. Mock content with div wrappers
$content_html = '
<div class="seo-metadata-block">
Meta Title: AI in Aviation Geopolitics
Meta Description: Explore aviation AI in strategic regions.
Meta Keywords: AI, Aviation, Africa
Focus Keyword: Aviation AI
SEO Slug: aviation-ai-geopolitics
Suggested Internal Linking Opportunities:
- <a href="http://localhost/Ascendance/briefs/test">Internal Link</a>
Suggested External References:
- <a href="https://example.com">External Link</a>
</div>
<div class="intelligence-metadata-block">
Subhead: Examining AI in aviation.
Analytical Claim: AI is strategic.
Public Excerpt: Aviation sectors in Africa are altering.
Executive Summary: This is executive summary.
Key Findings:
- Bullet 1
- Bullet 2
Key Takeaways:
- Takeaway 1
- Takeaway 2
Sources:
Source: IATA | URL: https://iata.org | Date: 2026-07-17
Categories: Geopolitics, Aviation
Tags: AI, Africa, Tech
Author: admin
</div>
<h1>AI Applications in Aviation Across Strategic Regions</h1>
<p>This is the first paragraph of the article. It should be kept intact.</p>
<h2>Sub-heading 1</h2>
<p>More content here.</p>
';

// 2. Mock content without div wrappers (plain markdown/text)
$content_plain = '
Meta Title: Plain AI in Aviation Geopolitics
Meta Description: Plain explore aviation AI.
Meta Keywords: Plain AI, Aviation, Africa
Focus Keyword: Plain Aviation AI
SEO Slug: plain-aviation-ai-geopolitics
Categories: Plain Category, Plain Aviation
Tags: Plain Tag, Plain Tech
Author: admin

Subhead: Plain examining AI.
Analytical Claim: Plain AI is strategic.
Public Excerpt: Plain excerpt.
Executive Summary: Plain executive summary.
Key Takeaways:
- Plain Takeaway 1
- Plain Takeaway 2
Sources:
Source: Plain Source | URL: https://example.com | Date: 2026-07-17

=== SUGGESTED_IMAGE_PROMPTS ===
Featured Image: A beautiful photograph depicting plain aviation.

=== ARTICLE CONTENT ===
<h1>Plain AI Applications in Aviation</h1>
<p>Plain first paragraph. It should be kept intact.</p>
<h2>Plain Sub-heading 1</h2>
<p>Plain more content here.</p>
';

function test_parse( $content, $label ) {
    echo "========================================\n";
    echo "TESTING: $label\n";
    echo "========================================\n";
    
    $original_content = $content;
    $metadata_block = '';
    $seo_block = '';
    $intel_block = '';

    // Try to match by class divs first
    if ( preg_match( '/<div[^>]*class=["\']seo-metadata-block["\'][^>]*>(.*?)<\/div>/is', $content, $matches ) ) {
        $seo_block = $matches[1];
        $content = preg_replace( '/<div[^>]*class=["\']seo-metadata-block["\'][^>]*>(.*?)<\/div>/is', '', $content );
    }
    if ( preg_match( '/<div[^>]*class=["\']intelligence-metadata-block["\'][^>]*>(.*?)<\/div>/is', $content, $matches ) ) {
        $intel_block = $matches[1];
        $content = preg_replace( '/<div[^>]*class=["\']intelligence-metadata-block["\'][^>]*>(.*?)<\/div>/is', '', $content );
    }

    if ( ! empty( $seo_block ) || ! empty( $intel_block ) ) {
        $metadata_block = $seo_block . "\n" . $intel_block;
    } else {
        $body_markers = array(
            '/<h1[^>]*>/i',
            '/<h2[^>]*>/i',
            '/===+\s*ARTICLE\s+CONTENT/i',
            '/===+\s*CONTENT/i',
            '/^\s*#\s+/m',
            '/^\s*##\s+/m'
        );
        $min_pos = false;
        foreach ( $body_markers as $marker ) {
            if ( preg_match( $marker, $content, $m, PREG_OFFSET_CAPTURE ) ) {
                $pos = $m[0][1];
                if ( false === $min_pos || $pos < $min_pos ) {
                    $min_pos = $pos;
                }
            }
        }

        if ( false !== $min_pos && $min_pos > 0 ) {
            $metadata_block = substr( $content, 0, $min_pos );
            $content = substr( $content, $min_pos );
        }
    }

    $metadata_plain = html_entity_decode( strip_tags( $metadata_block ), ENT_QUOTES, 'UTF-8' );
    $search_space = ! empty( $metadata_plain ) ? $metadata_plain : html_entity_decode( strip_tags( $original_content ), ENT_QUOTES, 'UTF-8' );

    $parse_field = function( $pattern, $text ) {
        if ( preg_match( $pattern, $text, $m ) ) {
            return trim( $m[1] );
        }
        return '';
    };

    $meta_title = $parse_field( '/(?:SEO\s+)?(?:Meta\s+)?Title\s*:\s*([^\r\n]+)/i', $search_space );
    $meta_desc = $parse_field( '/(?:Meta\s+)?Description\s*:\s*([^\r\n]+)/i', $search_space );
    $meta_keywords = $parse_field( '/(?:Meta\s+)?Keywords\s*:\s*([^\r\n]+)/i', $search_space );
    $focus_keyword = $parse_field( '/Focus\s+Keyword\s*:\s*([^\r\n]+)/i', $search_space );
    $seo_slug = $parse_field( '/(?:SEO\s+)?Slug(?:\s*\/|\s+URL)?\s*:\s*([^\r\n]+)/i', $search_space );
    $subhead = $parse_field( '/Subhead\s*:\s*([^\r\n]+)/i', $search_space );
    $analytical_claim = $parse_field( '/Analytical\s+Claim\s*:\s*([^\r\n]+)/i', $search_space );
    $public_excerpt = $parse_field( '/Public\s+Excerpt\s*:\s*([^\r\n]+)/i', $search_space );
    $executive_summary = $parse_field( '/Executive\s+Summary\s*:\s*([^\r\n]+)/i', $search_space );
    $categories_str = $parse_field( '/Categories\s*:\s*([^\r\n]+)/i', $search_space );
    $tags_str = $parse_field( '/Tags\s*:\s*([^\r\n]+)/i', $search_space );
    $author_name = $parse_field( '/Author\s*:\s*([^\r\n]+)/i', $search_space );

    $html_space = ! empty( $metadata_block ) ? $metadata_block : $original_content;
    $internal_links = '';
    $external_references = '';
    $key_findings = '';
    $key_takeaways = array();
    $sources_list = array();

    if ( preg_match( '/Suggested\s+Internal\s+Linking\s+Opportunities\s*:\s*(.*?)(?=Suggested\s+External|Subhead|Analytical|Public|Executive|$)/is', $html_space, $m ) ) {
        $internal_links = trim( $m[1] );
    }
    if ( preg_match( '/Suggested\s+External\s+References\s*:\s*(.*?)(?=Subhead|Analytical|Public|Executive|$)/is', $html_space, $m ) ) {
        $external_references = trim( $m[1] );
    }
    if ( preg_match( '/Key\s+Findings\s*:\s*(.*?)(?=Key\s+Takeaways|Sources|$)/is', $html_space, $m ) ) {
        $key_findings = trim( $m[1] );
    }
    
    if ( preg_match( '/Key\s+Takeaways\s*:\s*(.*?)(?=Sources|$)/is', $html_space, $m ) ) {
        $takeaways_block = trim( strip_tags( $m[1] ) );
        preg_match_all( '/(?:-\s*|\d+\.\s*)([^\r\n]+)/', $takeaways_block, $t_matches );
        if ( ! empty( $t_matches[1] ) ) {
            foreach ( $t_matches[1] as $item ) {
                $key_takeaways[] = trim( $item );
            }
        }
    }
    
    if ( preg_match( '/Sources\s*:\s*(.*)/is', $html_space, $m ) ) {
        $sources_block = trim( strip_tags( $m[1] ) );
        $lines = explode( "\n", $sources_block );
        foreach ( $lines as $line ) {
            if ( empty( trim( $line ) ) ) continue;
            $src_name = '';
            if ( preg_match( '/Source\s*:\s*([^|]+)/i', $line, $sm ) ) {
                $src_name = trim( $sm[1] );
            }
            if ( ! empty( $src_name ) ) {
                $sources_list[] = $src_name;
            }
        }
    }

    $featured_image_prompt = '';
    if ( preg_match( '/=== SUGGESTED_IMAGE_PROMPTS ===\s*(.*?)(?=== SUGGESTED_PUBLIC_EXCERPT ===|=== SUGGESTED_KEY_TAKEAWAYS ===|$)/is', $html_space, $m ) ) {
        $suggested_image_prompts = trim( strip_tags( html_entity_decode( $m[1], ENT_QUOTES, 'UTF-8' ) ) );
        if ( preg_match( '/Featured\s+Image\s*:\s*([^\r\n]+)/i', $suggested_image_prompts, $im ) ) {
            $featured_image_prompt = trim( $im[1] );
        }
    }
    if ( empty( $featured_image_prompt ) && preg_match( '/Featured\s+Image\s*:\s*([^\r\n<]+)/i', html_entity_decode( strip_tags( $html_space ), ENT_QUOTES, 'UTF-8' ), $im ) ) {
        $featured_image_prompt = trim( $im[1] );
    }

    $categories_list = array_filter( array_map( 'trim', explode( ',', $categories_str ) ) );
    $tags_list = array_filter( array_map( 'trim', explode( ',', $tags_str ) ) );

    // Strip patterns
    $strip_patterns = array(
        '/^[\s\r\n]*(?:SEO\s+)?(?:Meta\s+)?Title\s*:\s*[^\r\n]+/im',
        '/^[\s\r\n]*(?:Meta\s+)?Description\s*:\s*[^\r\n]+/im',
        '/^[\s\r\n]*(?:Meta\s+)?Keywords\s*:\s*[^\r\n]+/im',
        '/^[\s\r\n]*Focus\s+Keyword\s*:\s*[^\r\n]+/im',
        '/^[\s\r\n]*(?:SEO\s+)?Slug(?:\s*\/|\s+URL)?\s*:\s*[^\r\n]+/im',
        '/^[\s\r\n]*Subhead\s*:\s*[^\r\n]+/im',
        '/^[\s\r\n]*Analytical\s+Claim\s*:\s*[^\r\n]+/im',
        '/^[\s\r\n]*Public\s+Excerpt\s*:\s*[^\r\n]+/im',
        '/^[\s\r\n]*Executive\s+Summary\s*:\s*[^\r\n]+/im',
        '/^[\s\r\n]*Categories\s*:\s*[^\r\n]+/im',
        '/^[\s\r\n]*Tags\s*:\s*[^\r\n]+/im',
        '/^[\s\r\n]*Author\s*:\s*[^\r\n]+/im',
        '/^[\s\r\n]*(?:Featured\s+)?Image\s+Prompt\s*:\s*[^\r\n]+/im',
        '/^[\s\r\n]*(?:Featured\s+)?Image(?:\s+URL)?\s*:\s*[^\r\n]+/im',
        '/^[\s\r\n]*Canonical\s+(?:URL)?\s*:\s*[^\r\n]+/im',
        '/^[\s\r\n]*Open\s+Graph\s+[^\r\n]+/im',
        '/^[\s\r\n]*Twitter\s+[^\r\n]+/im',
        '/^[\s\r\n]*Schema\s+[^\r\n]+/im',
        '/^[\s\r\n]*Suggested\s+Internal\s+Linking\s+Opportunities\s*:\s*/im',
        '/^[\s\r\n]*Suggested\s+External\s+References\s*:\s*/im',
        '/^[\s\r\n]*Key\s+Findings\s*:\s*/im',
        '/^[\s\r\n]*Key\s+Takeaways\s*:\s*/im',
        '/^[\s\r\n]*Sources\s*:\s*/im',
        '/===+\s*SUGGESTED_PUBLIC_EXCERPT\s*===+/i',
        '/===+\s*SUGGESTED_KEY_TAKEAWAYS\s*===+/i',
        '/===+\s*SUGGESTED_IMAGE_PROMPTS\s*===+/i',
        '/===+\s*ARTICLE\s+CONTENT\s*===+/i',
        '/===+\s*ARTICLE\s+CONTENT\s+SECTION\s*===+/i',
        '/===+\s*SEO\s+METADATA\s*===+/i',
        '/===+\s*SEO\s+METADATA\s+SECTION\s*===+/i',
        '/===+\s*INTELLIGENCE\s+METADATA\s*===+/i',
        '/===+\s*INTELLIGENCE\s+METADATA\s+SECTION\s*===+/i',
        '/===+\s*ARTICLE\s*===+/i',
        '/===+\s*CONTENT\s*===+/i',
        '/^\s*===+.*===+\s*$/m',
        '/^\s*---+.*\s*$/m',
    );
    foreach ( $strip_patterns as $pattern ) {
        $content = preg_replace( $pattern, '', $content );
    }

    $content = preg_replace( '/=== SUGGESTED_PUBLIC_EXCERPT ===\s*(.*?)(?=== SUGGESTED_KEY_TAKEAWAYS ===|=== SUGGESTED_IMAGE_PROMPTS ===|\Z)/is', '', $content );
    $content = preg_replace( '/=== SUGGESTED_KEY_TAKEAWAYS ===\s*(.*?)(?=== SUGGESTED_PUBLIC_EXCERPT ===|=== SUGGESTED_IMAGE_PROMPTS ===|\Z)/is', '', $content );
    $content = preg_replace( '/=== SUGGESTED_IMAGE_PROMPTS ===\s*(.*?)(?=== SUGGESTED_PUBLIC_EXCERPT ===|=== SUGGESTED_KEY_TAKEAWAYS ===|\Z)/is', '', $content );

    $content = trim( $content );

    // Extract H1 title
    $post_title = '';
    if ( preg_match( '/<h1[^>]*>(.*?)<\/h1>/is', $content, $matches ) ) {
        $post_title = trim( strip_tags( html_entity_decode( $matches[1], ENT_QUOTES, 'UTF-8' ) ) );
        $post_title = preg_replace( '/^(?:Main\s+)?H1\s+(?:Title\s*)?:\s*/i', '', $post_title );
        $post_title = preg_replace( '/^Title\s*:\s*/i', '', $post_title );
        $post_title = trim( $post_title );
        $content = preg_replace( '/<h1[^>]*>.*?<\/h1>/is', '', $content );
    }

    $content = trim( $content );
    $content = preg_replace( '/^<\/div>/i', '', $content );
    $content = trim( $content );

    echo "SEO Title: $meta_title\n";
    echo "Meta Description: $meta_desc\n";
    echo "Focus Keyword: $focus_keyword\n";
    echo "Slug: $seo_slug\n";
    echo "Subhead: $subhead\n";
    echo "Analytical Claim: $analytical_claim\n";
    echo "Excerpt: $public_excerpt\n";
    echo "Executive Summary: $executive_summary\n";
    echo "Categories: " . implode( ', ', $categories_list ) . "\n";
    echo "Tags: " . implode( ', ', $tags_list ) . "\n";
    echo "Author: $author_name\n";
    echo "Featured Image Prompt: $featured_image_prompt\n";
    echo "Takeaways count: " . count( $key_takeaways ) . "\n";
    echo "Sources count: " . count( $sources_list ) . "\n";
    echo "Cleaned Content Body preview (first 150 chars):\n";
    echo substr( $content, 0, 150 ) . "...\n\n";
}

test_parse( $content_html, "HTML Block Metadata" );
test_parse( $content_plain, "Plain Text / Markdown Metadata" );
