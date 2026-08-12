<?php
defined( 'ABSPATH' ) || exit;

/**
 * GateTouch AI Insights Engine
 * 
 * Generates proactive notifications and actionable insights based on site SEO/AEO/GEO health.
 */
class GateTouch_Insights_Engine {

    /**
     * Get the latest insights for the dashboard
     */
    public static function get_insights( $limit = 5 ) {
        $insights = [];
        $audit = GateTouch_Analyzer::get_results();

        // 1. Check for Semantic Gaps (GEO)
        if ( ( $audit['scores']['geo'] ?? 0 ) < 60 ) {
            $insights[] = [
                'type'     => 'geo',
                'priority' => 'high',
                'title'    => 'Semantic Authority Gap Detected',
                'text'     => 'Your content lacks deep entity coverage for your primary topics. Run a Content AI analysis to identify missing semantic keywords.',
                'action'   => admin_url( 'admin.php?page=gatetouch-content-ai' ),
                'icon'     => '🧠'
            ];
        }

        // 2. Check for AEO Readiness
        if ( ( $audit['scores']['aeo'] ?? 0 ) < 50 ) {
            $insights[] = [
                'type'     => 'aeo',
                'priority' => 'critical',
                'title'    => 'AI Search Visibility Risk',
                'text'     => 'Your site structure is not optimized for AI answer extraction. Add FAQ schema and conversational headers.',
                'action'   => admin_url( 'admin.php?page=gatetouch-audit&tab=aeo' ),
                'icon'     => '🤖'
            ];
        }

        // 3. Check for Orphan Pages (Linking)
        $orphans = GateTouch_Link_Assistant::get_orphan_pages( 3 );
        if ( ! empty( $orphans ) ) {
            $insights[] = [
                'type'     => 'linking',
                'priority' => 'medium',
                'title'    => 'Orphan Content Found',
                'text'     => sprintf( 'We found %d pages with no internal links. These pages are invisible to search bots.', count($orphans) ),
                'action'   => admin_url( 'admin.php?page=gatetouch-audit' ),
                'icon'     => '🔗'
            ];
        }

        // 4. Site Speed / Core Web Vitals (Mock check)
        $insights[] = [
            'type'     => 'performance',
            'priority' => 'low',
            'title'    => 'Optimize Image Assets',
            'text'     => 'Large images are slowing down your mobile indexing. Use GT SEO/GEO/AEO Vision AI to optimize alt text and compress images.',
            'action'   => admin_url( 'admin.php?page=gatetouch-content-ai' ),
            'icon'     => '⚡'
        ];

        return array_slice( $insights, 0, $limit );
    }
}
