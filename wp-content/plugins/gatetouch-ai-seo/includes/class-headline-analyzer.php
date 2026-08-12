<?php
defined( 'ABSPATH' ) || exit;

/**
 * Scores headlines for SEO readability, sentiment, and click appeal.
 */
class GateTouch_Headline_Analyzer {

    public static function analyze( $headline ) {
        $headline = trim( $headline );
        $words    = preg_split( '/\s+/', strtolower($headline), -1, PREG_SPLIT_NO_EMPTY );
        $count    = count( $words );

        if ( $count === 0 ) return [ 'error' => 'Please enter a headline.' ];

        $common_words   = ['a', 'an', 'the', 'is', 'at', 'which', 'on', 'for', 'to', 'in', 'of', 'and', 'or', 'but'];
        $emotional_words = ['wonderful', 'brilliant', 'heartbreaking', 'surprising', 'terrifying', 'joy', 'love', 'hate', 'fear', 'amazing', 'huge'];
        $power_words     = ['how', 'why', 'free', 'best', 'top', 'fast', 'easy', 'new', 'secret', 'now', 'guaranteed', 'results'];

        $common_found    = array_intersect( $words, $common_words );
        $emotional_found = array_intersect( $words, $emotional_words );
        $power_found     = array_intersect( $words, $power_words );

        $score = 0;
        
        // Word count score (ideal 6-12 words)
        if ( $count >= 6 && $count <= 12 ) $score += 30;
        elseif ( $count > 0 ) $score += 15;

        // Balance score
        if ( ! empty( $common_found ) ) $score += 20;
        if ( ! empty( $emotional_found ) ) $score += 25;
        if ( ! empty( $power_found ) ) $score += 25;

        $final_score = min( 100, $score );

        $common_perc    = $count > 0 ? round( ( count( $common_found ) / $count ) * 100 ) . '%' : '0%';
        $emotional_perc = $count > 0 ? round( ( count( $emotional_found ) / $count ) * 100 ) . '%' : '0%';
        $power_perc     = $count > 0 ? round( ( count( $power_found ) / $count ) * 100 ) . '%' : '0%';

        return [
            'score'           => $final_score,
            'seo_analysis'    => self::get_analysis_msg( $final_score ),
            'sentiment'       => count($emotional_found) > 0 ? 'Positive/Emotional' : 'Neutral',
            'character_count' => mb_strlen( $headline ),
            'word_count'      => $count,
            'readability'     => $count <= 12 ? 'Excellent' : 'Consider Shortening',
            'word_balance'    => [
                'common'    => $common_perc,
                'uncommon'  => '0%', // Fallback
                'emotional' => $emotional_perc,
                'power'     => $power_perc
            ],
            'improvements'    => self::get_improvements( $headline, $count, $power_found, $emotional_found ),
            'html'            => '' // We'll let JS render it now
        ];
    }

    private static function get_improvements( $headline, $count, $power_found, $emotional_found ) {
        $tips = [];
        if ( $count < 6 ) $tips[] = __( 'Your headline is a bit short. Aim for 6-12 words for better CTR.', 'gatetouch-ai-seo' );
        if ( $count > 12 ) $tips[] = __( 'Your headline is long. Consider trimming it to make it punchier.', 'gatetouch-ai-seo' );
        if ( empty( $power_found ) ) $tips[] = __( 'Add at least one Power Word (e.g., "Best", "Top", "Fast") to grab attention.', 'gatetouch-ai-seo' );
        if ( empty( $emotional_found ) ) $tips[] = __( 'Try adding an Emotional Word to connect with your readers.', 'gatetouch-ai-seo' );
        if ( empty( $tips ) ) $tips[] = __( 'Great headline! You can try testing different variations for maximum impact.', 'gatetouch-ai-seo' );
        return $tips;
    }

    private static function get_analysis_msg( $score ) {
        if ( $score >= 80 ) return __( 'Irresistible headline! High CTR potential.', 'gatetouch-ai-seo' );
        if ( $score >= 60 ) return __( 'Good headline, but could use more power words.', 'gatetouch-ai-seo' );
        return __( 'Headline needs more emotional or power words to drive clicks.', 'gatetouch-ai-seo' );
    }

    private static function get_color( $score ) {
        if ( $score >= 80 ) return '#10b981';
        if ( $score >= 50 ) return '#f59e0b';
        return '#ef4444';
    }

    private static function get_label( $score ) {
        if ( $score >= 80 ) return __( 'Irresistible', 'gatetouch-ai-seo' );
        if ( $score >= 60 ) return __( 'Good', 'gatetouch-ai-seo' );
        if ( $score >= 40 ) return __( 'Average', 'gatetouch-ai-seo' );
        return __( 'Weak', 'gatetouch-ai-seo' );
    }
}
