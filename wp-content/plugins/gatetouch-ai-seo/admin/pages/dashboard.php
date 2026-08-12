<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files are included inside class methods, so render variables are local.

// Fetch core data
$audit      = GateTouch_Analyzer::get_results();
$seo_score  = $audit['scores']['seo'] ?? 0;
$aeo_score  = $audit['scores']['aeo'] ?? 0;
$geo_score  = $audit['scores']['geo'] ?? 0;
$tech_score = $audit['scores']['tech'] ?? 0;
$issues     = $audit['issues'] ?? [];
$stats      = $audit['stats'] ?? [];

$has_key    = GateTouch_AI_Engine::has_api_key();
$ai_usage   = get_option( 'gatetouch_api_usage', [] );
$today      = wp_date( 'Y-m-d' );
$daily_usage = $ai_usage[$today] ?? [ 'tokens' => 0, 'requests' => 0, 'cost' => 0 ];

$queue      = get_option( 'gatetouch_task_queue', [] );
$progress   = get_option( 'gatetouch_bulk_progress', [ 'total' => 0, 'current' => 0 ] );
$crawl_summary = GateTouch_Crawler::get_summary();

$db_stats   = GateTouch_Performance_Engine::get_health_stats();
$insights   = GateTouch_Insights_Engine::get_insights();

// System Health Data
$php_version = PHP_VERSION;
$wp_version  = get_bloginfo( 'version' );
$memory_limit = ini_get( 'memory_limit' );
$cron_status = wp_next_scheduled( 'gatetouch_process_queue' ) ? 'Active' : 'Idle';
$rest_status = function_exists( 'rest_get_url_prefix' ) ? 'Operational' : 'Disabled';

?>
<div class="gatetouch-admin-wrap">
    <?php
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Helper escapes its own output.
    GateTouch_Helpers::page_header( __( 'Enterprise SEO Control Center', 'gatetouch-ai-seo' ), __( 'Real-time intelligence and AI automation platform.', 'gatetouch-ai-seo' ) );
    ?>

    <div class="gatetouch-enterprise-dashboard-v2">
        
        <!-- 1. WEBSITE SEO HEALTH OVERVIEW -->
        <div class="riq-dashboard-section">
            <h2 class="riq-section-title"><?php esc_html_e( 'Website SEO Health Overview', 'gatetouch-ai-seo' ); ?></h2>
            <div class="riq-health-grid">
                <!-- SEO Score -->
                <div class="riq-health-card">
                    <div class="riq-health-card__main">
                        <div class="riq-health-card__score" style="color: <?php echo esc_attr( GateTouch_Helpers::get_score_color( $seo_score ) ); ?>;">
                            <?php echo esc_html( $seo_score ); ?><span>%</span>
                        </div>
                        <div class="riq-health-card__label"><?php esc_html_e( 'Overall SEO Score', 'gatetouch-ai-seo' ); ?></div>
                    </div>
                    <div class="riq-health-card__chart">
                        <svg viewBox="0 0 36 36" class="riq-circular-chart">
                            <path class="riq-circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path class="riq-circle" stroke-dasharray="<?php echo esc_attr( $seo_score ); ?>, 100" stroke="<?php echo esc_attr( GateTouch_Helpers::get_score_color( $seo_score ) ); ?>" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                    </div>
                </div>

                <!-- AEO Score -->
                <div class="riq-health-card">
                    <div class="riq-health-card__main">
                        <div class="riq-health-card__score" style="color: #6366f1;">
                            <?php echo esc_html( $aeo_score ); ?><span>%</span>
                        </div>
                        <div class="riq-health-card__label"><?php esc_html_e( 'AEO Readiness', 'gatetouch-ai-seo' ); ?></div>
                    </div>
                    <div class="riq-health-card__chart">
                        <svg viewBox="0 0 36 36" class="riq-circular-chart">
                            <path class="riq-circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path class="riq-circle" stroke-dasharray="<?php echo esc_attr( $aeo_score ); ?>, 100" stroke="#6366f1" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                    </div>
                </div>

                <!-- Technical Score -->
                <div class="riq-health-card">
                    <div class="riq-health-card__main">
                        <div class="riq-health-card__score" style="color: #3b82f6;">
                            <?php echo esc_html( $tech_score ); ?><span>%</span>
                        </div>
                        <div class="riq-health-card__label"><?php esc_html_e( 'Technical Health', 'gatetouch-ai-seo' ); ?></div>
                    </div>
                    <div class="riq-health-card__chart">
                        <svg viewBox="0 0 36 36" class="riq-circular-chart">
                            <path class="riq-circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path class="riq-circle" stroke-dasharray="<?php echo esc_attr( $tech_score ); ?>, 100" stroke="#3b82f6" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                    </div>
                </div>

                <!-- GEO Score -->
                <div class="riq-health-card">
                    <div class="riq-health-card__main">
                        <div class="riq-health-card__score" style="color: #a855f7;">
                            <?php echo esc_html( $geo_score ); ?><span>%</span>
                        </div>
                        <div class="riq-health-card__label"><?php esc_html_e( 'GEO Visibility', 'gatetouch-ai-seo' ); ?></div>
                    </div>
                    <div class="riq-health-card__chart">
                        <svg viewBox="0 0 36 36" class="riq-circular-chart">
                            <path class="riq-circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path class="riq-circle" stroke-dasharray="<?php echo esc_attr( $geo_score ); ?>, 100" stroke="#a855f7" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Layout Grid -->
        <div class="riq-dashboard-main-grid">
            
            <!-- Left Column -->
            <div class="riq-dashboard-col">
                
                <!-- 2. QUICK ACTION CENTER -->
                <div class="riq-panel">
                    <div class="riq-panel__header">
                        <div class="riq-panel__title"><?php esc_html_e( 'Quick Action Center', 'gatetouch-ai-seo' ); ?></div>
                    </div>
                    <div class="riq-action-grid">
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=gatetouch-content-ai' ) ); ?>" class="riq-action-btn">
                            <span class="riq-action-btn__icon"><?php echo wp_kses( GateTouch_Helpers::icon( 'sparkles', 20 ), GateTouch_Helpers::svg_kses_allowed() ); ?></span>
                            <span class="riq-action-btn__label"><?php esc_html_e( 'Generate SEO Meta', 'gatetouch-ai-seo' ); ?></span>
                        </a>
                        <a href="#" id="riq-run-full-audit" class="riq-action-btn">
                            <span class="riq-action-btn__icon"><?php echo wp_kses( GateTouch_Helpers::icon( 'search', 20 ), GateTouch_Helpers::svg_kses_allowed() ); ?></span>
                            <span class="riq-action-btn__label"><?php esc_html_e( 'Run SEO Scan', 'gatetouch-ai-seo' ); ?></span>
                        </a>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=gatetouch-settings&tab=ai' ) ); ?>" class="riq-action-btn">
                            <span class="riq-action-btn__icon"><?php echo wp_kses( GateTouch_Helpers::icon( 'key', 20 ), GateTouch_Helpers::svg_kses_allowed() ); ?></span>
                            <span class="riq-action-btn__label"><?php esc_html_e( 'Configure AI Provider', 'gatetouch-ai-seo' ); ?></span>
                        </a>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=gatetouch-audit&tab=aeo' ) ); ?>" class="riq-action-btn">
                            <span class="riq-action-btn__icon"><?php echo wp_kses( GateTouch_Helpers::icon( 'puzzle', 20 ), GateTouch_Helpers::svg_kses_allowed() ); ?></span>
                            <span class="riq-action-btn__label"><?php esc_html_e( 'Validate Schema', 'gatetouch-ai-seo' ); ?></span>
                        </a>
                        <a href="#" id="riq-ping-sitemap" class="riq-action-btn">
                            <span class="riq-action-btn__icon"><?php echo wp_kses( GateTouch_Helpers::icon( 'map', 20 ), GateTouch_Helpers::svg_kses_allowed() ); ?></span>
                            <span class="riq-action-btn__label"><?php esc_html_e( 'Regenerate Sitemap', 'gatetouch-ai-seo' ); ?></span>
                        </a>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=gatetouch-settings&tab=redirects' ) ); ?>" class="riq-action-btn">
                            <span class="riq-action-btn__icon"><?php echo wp_kses( GateTouch_Helpers::icon( 'arrows-shuffle', 20 ), GateTouch_Helpers::svg_kses_allowed() ); ?></span>
                            <span class="riq-action-btn__label"><?php esc_html_e( 'Redirect Manager', 'gatetouch-ai-seo' ); ?></span>
                        </a>
                    </div>
                </div>

                <!-- 4. SEO ISSUE CENTER -->
                <div class="riq-panel">
                    <div class="riq-panel__header">
                        <div class="riq-panel__title"><?php esc_html_e( 'SEO Issue Center', 'gatetouch-ai-seo' ); ?></div>
                        <span class="riq-badge riq-badge--error"><?php echo esc_html( count( $issues ) ); ?> <?php esc_html_e( 'Active', 'gatetouch-ai-seo' ); ?> <?php echo esc_html( count( $issues ) === 1 ? __( 'Issue', 'gatetouch-ai-seo' ) : __( 'Issues', 'gatetouch-ai-seo' ) ); ?></span>
                    </div>
                    <div class="riq-issue-list">
                        <?php if ( empty($issues) ) : ?>
                            <div class="riq-empty-state">
                                <span class="riq-empty-state__icon"><?php echo wp_kses( GateTouch_Helpers::icon( 'check-circle', 32 ), GateTouch_Helpers::svg_kses_allowed() ); ?></span>
                                <div class="riq-empty-state__text"><?php esc_html_e( 'Zero critical issues detected. Excellent work!', 'gatetouch-ai-seo' ); ?></div>
                            </div>
                        <?php else: 
                            foreach ( $issues as $issue ) : 
                                $priority_class = sanitize_html_class( strtolower( $issue['priority'] ) );
                            ?>
                            <div class="riq-issue-item riq-issue--<?php echo esc_attr( $priority_class ); ?>">
                                <div class="riq-issue-item__header">
                                    <span class="riq-issue-item__priority"><?php echo esc_html($issue['priority']); ?></span>
                                    <strong class="riq-issue-item__title"><?php echo esc_html($issue['title'] ); ?></strong>
                                    <a href="<?php echo esc_url($issue['learn_more'] ?? '#'); ?>" class="riq-issue-item__doc-link" title="Documentation">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                    </a>
                                </div>
                                <div class="riq-issue-item__body">
                                    <p><?php echo esc_html($issue['explanation']); ?></p>
                                    <div class="riq-issue-item__impact">
                                        <strong>Impact:</strong> <?php echo esc_html($issue['seo_impact'][0] ?? 'Affects search visibility'); ?>
                                    </div>
                                </div>
                                <div class="riq-issue-item__actions">
                                    <a href="<?php echo esc_url($issue['action_btn']['link'] ?? '#'); ?>" class="riq-btn riq-btn--sm riq-btn--primary">
                                        <?php echo esc_html($issue['action_btn']['text'] ?? 'Fix Issue'); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>

                <!-- 5. CONTENT OPTIMIZATION INSIGHTS -->
                <div class="riq-panel">
                    <div class="riq-panel__header">
                        <div class="riq-panel__title"><?php esc_html_e( 'Content Optimization Insights', 'gatetouch-ai-seo' ); ?></div>
                    </div>
                    <div class="riq-insights-list">
                        <?php foreach ( $insights as $insight ) : ?>
                            <div class="riq-insight-row">
                                <div class="riq-insight-row__icon"><?php echo esc_html( $insight['icon'] ); ?></div>
                                <div class="riq-insight-row__content">
                                    <strong><?php echo esc_html($insight['title']); ?></strong>
                                    <span><?php echo esc_html($insight['text']); ?></span>
                                </div>
                                <a href="<?php echo esc_url($insight['action']); ?>" class="riq-btn riq-btn--ghost riq-btn--sm">View</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="riq-dashboard-col">
                
                <!-- 3. AI STATUS PANEL -->
                <div class="riq-panel riq-panel--ai">
                    <div class="riq-panel__header">
                        <div class="riq-panel__title"><?php esc_html_e( 'AI Engine Status', 'gatetouch-ai-seo' ); ?></div>
                        <span class="riq-status-dot riq-status--<?php echo esc_attr( $has_key ? 'ok' : 'err' ); ?>"></span>
                    </div>
                    <div class="riq-ai-status">
                        <div class="riq-ai-stat-row">
                            <span class="riq-ai-stat-label"><?php esc_html_e( 'Connection', 'gatetouch-ai-seo' ); ?></span>
                            <span class="riq-ai-stat-value"><?php echo esc_html( $has_key ? __( 'Operational', 'gatetouch-ai-seo' ) : __( 'Disconnected', 'gatetouch-ai-seo' ) ); ?></span>
                        </div>
                        <div class="riq-ai-stat-row">
                            <span class="riq-ai-stat-label"><?php esc_html_e( 'Active Model', 'gatetouch-ai-seo' ); ?></span>
                            <span class="riq-ai-stat-value"><?php echo esc_html(get_option('gatetouch_ai_model', 'GPT-4o')); ?></span>
                        </div>
                        <div class="riq-ai-stat-row">
                            <span class="riq-ai-stat-label"><?php esc_html_e( 'Tokens (Today)', 'gatetouch-ai-seo' ); ?></span>
                            <span class="riq-ai-stat-value"><?php echo esc_html( number_format_i18n( $daily_usage['tokens'] ) ); ?></span>
                        </div>
                        <div class="riq-ai-stat-row">
                            <span class="riq-ai-stat-label"><?php esc_html_e( 'Est. Cost (Today)', 'gatetouch-ai-seo' ); ?></span>
                            <span class="riq-ai-stat-value">$<?php echo esc_html( number_format_i18n( $daily_usage['cost'], 4 ) ); ?></span>
                        </div>
                        <div class="riq-ai-usage-meter">
                            <div class="riq-ai-usage-bar"><div style="width: <?php echo esc_attr( min( 100, ( $daily_usage['requests'] / 100 ) * 100 ) ); ?>%;"></div></div>
                            <span class="riq-ai-usage-text"><?php echo esc_html( $daily_usage['requests'] ); ?> / 100 <?php esc_html_e( 'Daily Requests Used', 'gatetouch-ai-seo' ); ?></span>
                        </div>
                    </div>
                </div>

                <!-- 6. INDEXING & CRAWL STATUS -->
                <div class="riq-panel">
                    <div class="riq-panel__header">
                        <div class="riq-panel__title"><?php esc_html_e( 'Indexing & Crawl Status', 'gatetouch-ai-seo' ); ?></div>
                    </div>
                    <div class="riq-crawl-status">
                        <div class="riq-crawl-overview">
                            <div class="riq-crawl-stat">
                                <div class="riq-crawl-stat__num"><?php echo esc_html( $stats['total_content'] ?? 0 ); ?></div>
                                <div class="riq-crawl-stat__label"><?php esc_html_e( 'Total Pages', 'gatetouch-ai-seo' ); ?></div>
                            </div>
                            <div class="riq-crawl-stat">
                                <div class="riq-crawl-stat__num"><?php echo esc_html( $crawl_summary['results']['success'] ?? 0 ); ?></div>
                                <div class="riq-crawl-stat__label"><?php esc_html_e( 'Crawled', 'gatetouch-ai-seo' ); ?></div>
                            </div>
                            <div class="riq-crawl-stat">
                                <div class="riq-crawl-stat__num"><?php echo esc_html( count( $crawl_summary['results']['broken_links'] ?? [] ) ); ?></div>
                                <div class="riq-crawl-stat__label"><?php esc_html_e( 'Broken', 'gatetouch-ai-seo' ); ?></div>
                            </div>
                        </div>
                        <div class="riq-crawl-last">
                            <?php esc_html_e( 'Last Crawl:', 'gatetouch-ai-seo' ); ?> <?php echo esc_html( $crawl_summary['last_run'] ? gmdate( 'M j, Y H:i', $crawl_summary['last_run'] ) : __( 'Never', 'gatetouch-ai-seo' ) ); ?>
                        </div>
                        <button id="riq-start-crawl" class="riq-btn riq-btn--ghost riq-btn--full"><?php esc_html_e( 'Run Full Site Crawl', 'gatetouch-ai-seo' ); ?></button>
                    </div>
                </div>

                <!-- 7. BULK OPTIMIZATION PANEL -->
                <div class="riq-panel">
                    <div class="riq-panel__header">
                        <div class="riq-panel__title"><?php esc_html_e( 'Bulk Optimization Queue', 'gatetouch-ai-seo' ); ?></div>
                        <span class="riq-badge"><?php echo esc_html( count( $queue ) ); ?> <?php esc_html_e( 'Pending', 'gatetouch-ai-seo' ); ?></span>
                    </div>
                    <div class="riq-queue-panel">
                        <?php if ( empty($queue) && empty($progress['total']) ) : ?>
                            <div class="riq-empty-state riq-empty-state--sm">
                                <div class="riq-empty-state__text"><?php esc_html_e( 'Queue is empty.', 'gatetouch-ai-seo' ); ?></div>
                            </div>
                        <?php else: 
                            $pct = $progress['total'] > 0 ? round(($progress['current'] / $progress['total']) * 100) : 0;
                        ?>
                            <div class="riq-progress-container">
                                <div class="riq-progress-info">
                                    <span><?php esc_html_e( 'Bulk Operation in Progress...', 'gatetouch-ai-seo' ); ?></span>
                                    <span><?php echo esc_html( $pct ); ?>%</span>
                                </div>
                                <div class="riq-progress-bar"><div style="width: <?php echo esc_attr( $pct ); ?>%;"></div></div>
                                <div class="riq-progress-stats">
                                    <?php echo esc_html( $progress['current'] ); ?> / <?php echo esc_html( $progress['total'] ); ?> <?php esc_html_e( 'posts optimized', 'gatetouch-ai-seo' ); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=gatetouch-content-ai' ) ); ?>" class="riq-btn riq-btn--sm riq-btn--ghost riq-btn--full"><?php esc_html_e( 'Manage Queue', 'gatetouch-ai-seo' ); ?></a>
                    </div>
                </div>

                <!-- 8. SYSTEM HEALTH PANEL -->
                <div class="riq-panel">
                    <div class="riq-panel__header">
                        <div class="riq-panel__title"><?php esc_html_e( 'System Health', 'gatetouch-ai-seo' ); ?></div>
                    </div>
                    <div class="riq-system-health">
                        <div class="riq-health-item">
                            <span><?php esc_html_e( 'PHP Version', 'gatetouch-ai-seo' ); ?></span>
                            <strong><?php echo esc_html( $php_version ); ?></strong>
                        </div>
                        <div class="riq-health-item">
                            <span><?php esc_html_e( 'WordPress', 'gatetouch-ai-seo' ); ?></span>
                            <strong><?php echo esc_html( $wp_version ); ?></strong>
                        </div>
                        <div class="riq-health-item">
                            <span><?php esc_html_e( 'Memory Limit', 'gatetouch-ai-seo' ); ?></span>
                            <strong><?php echo esc_html( $memory_limit ); ?></strong>
                        </div>
                        <div class="riq-health-item">
                            <span><?php esc_html_e( 'WP-Cron', 'gatetouch-ai-seo' ); ?></span>
                            <strong class="riq-status-text--ok"><?php echo esc_html( $cron_status ); ?></strong>
                        </div>
                        <div class="riq-health-item">
                            <span><?php esc_html_e( 'REST API', 'gatetouch-ai-seo' ); ?></span>
                            <strong class="riq-status-text--ok"><?php echo esc_html( $rest_status ); ?></strong>
                        </div>
                        <div class="riq-health-item">
                            <span><?php esc_html_e( 'DB Health', 'gatetouch-ai-seo' ); ?></span>
                            <button id="riq-optimize-db-v2" class="riq-btn riq-btn--xs riq-btn--ghost"><?php esc_html_e( 'Optimize Now', 'gatetouch-ai-seo' ); ?></button>
                        </div>
                    </div>
                </div>

                <!-- 9. HELP CENTER PANEL -->
                <div class="riq-panel riq-panel--help">
                    <div class="riq-panel__header">
                        <div class="riq-panel__title"><?php esc_html_e( 'Help & Documentation', 'gatetouch-ai-seo' ); ?></div>
                    </div>
                    <div class="riq-help-links">
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=gatetouch-help&id=intro' ) ); ?>" class="riq-help-link">
                            <span class="rk-icon-box rk-icon-box--sm rk-icon-box--indigo"><?php echo wp_kses( GateTouch_Helpers::icon( 'rocket', 14 ), GateTouch_Helpers::svg_kses_allowed() ); ?></span>
                            <?php esc_html_e( 'Getting Started Guide', 'gatetouch-ai-seo' ); ?>
                        </a>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=gatetouch-help&id=openai-api' ) ); ?>" class="riq-help-link">
                            <span class="rk-icon-box rk-icon-box--sm rk-icon-box--purple"><?php echo wp_kses( GateTouch_Helpers::icon( 'key', 14 ), GateTouch_Helpers::svg_kses_allowed() ); ?></span>
                            <?php esc_html_e( 'AI Configuration Help', 'gatetouch-ai-seo' ); ?>
                        </a>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=gatetouch-help&id=seo-analyzer' ) ); ?>" class="riq-help-link">
                            <span class="rk-icon-box rk-icon-box--sm rk-icon-box--blue"><?php echo wp_kses( GateTouch_Helpers::icon( 'search', 14 ), GateTouch_Helpers::svg_kses_allowed() ); ?></span>
                            <?php esc_html_e( 'SEO Analysis Guide', 'gatetouch-ai-seo' ); ?>
                        </a>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=gatetouch-help&id=troubleshooting' ) ); ?>" class="riq-help-link">
                            <span class="rk-icon-box rk-icon-box--sm rk-icon-box--slate"><?php echo wp_kses( GateTouch_Helpers::icon( 'tool', 14 ), GateTouch_Helpers::svg_kses_allowed() ); ?></span>
                            <?php esc_html_e( 'Troubleshooting', 'gatetouch-ai-seo' ); ?>
                        </a>
                    </div>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=gatetouch-help' ) ); ?>" class="riq-btn riq-btn--sm riq-btn--primary riq-btn--full" style="margin-top: 15px;"><?php esc_html_e( 'Open Help Center', 'gatetouch-ai-seo' ); ?></a>
                </div>

            </div>
        </div>
    </div>
</div>

