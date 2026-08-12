<?php
/**
 * GateTouch — SEO Analysis Suite
 * Professional Enterprise Edition
 */
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files are included inside class methods, so render variables are local.
// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Admin audit tab routing uses sanitized read-only GET parameters and does not change state.

// Get homepage ID
$home_id = (int) get_option( 'page_on_front' );
if ( ! $home_id ) {
    $latest  = get_posts( [ 'posts_per_page' => 1 ] );
    $home_id = ! empty( $latest ) ? $latest[0]->ID : 0;
}
?>
<div class="gatetouch-analysis-suite">
    <?php $active_sub_tab = isset( $_GET['audit_tab'] ) ? sanitize_key( $_GET['audit_tab'] ) : 'homepage'; ?>
    
    <div class="gatetouch-tabs-simple" style="margin-top:0;">
        <button type="button" class="gatetouch-page-tab <?php echo esc_attr( $active_sub_tab === 'homepage' ? 'active' : '' ); ?>" data-target="tab-homepage">Homepage Audit</button>
        <button type="button" class="gatetouch-page-tab <?php echo esc_attr( $active_sub_tab === 'site' ? 'active' : '' ); ?>" data-target="tab-site">Site Audit</button>
        <button type="button" class="gatetouch-page-tab <?php echo esc_attr( $active_sub_tab === 'headlines' ? 'active' : '' ); ?>" data-target="tab-headlines">Headline Analyzer</button>
        <button type="button" class="gatetouch-page-tab <?php echo esc_attr( $active_sub_tab === 'competitor' ? 'active' : '' ); ?>" data-target="tab-competitor">Competitor Audit</button>
        <button type="button" class="gatetouch-page-tab <?php echo esc_attr( $active_sub_tab === 'crawler' ? 'active' : '' ); ?>" data-target="tab-crawler">Broken Link Scanner</button>
    </div>

    <!-- Tab: Homepage Audit -->
    <div id="tab-homepage" class="gatetouch-sub-tab-content" style="<?php echo esc_attr( $active_sub_tab === 'homepage' ? '' : 'display:none;' ); ?>">
        
        <div class="gatetouch-card" style="margin-bottom:30px;">
            <div class="gatetouch-card__header" style="justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="background:var(--riq-ai-gradient); width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; color:#fff;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                    </div>
                    <strong><?php esc_html_e( 'Homepage Diagnostic Engine', 'gatetouch-ai-seo' ); ?></strong>
                </div>
                <div class="gatetouch-header-actions" style="display:flex; gap:10px;">
                     <button id="gatetouch-theme-toggle-inline" class="gatetouch-btn gatetouch-btn--ghost gatetouch-btn--sm" title="Toggle Theme">🌓</button>
                </div>
            </div>
            
            <div class="gatetouch-card__body" style="padding:40px; display:flex; align-items:center; gap:50px;">
                <div class="gatetouch-score-visualizer" style="position:relative; width:160px; height:160px; flex-shrink:0;">
                    <svg viewBox="0 0 36 36" style="width:160px; height:160px; transform: rotate(-90deg);">
                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#f1f5f9" stroke-width="2.5" />
                        <path id="gatetouch-score-circle" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="var(--riq-primary)" stroke-width="2.5" stroke-dasharray="0, 100" />
                    </svg>
                    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); text-align:center;">
                        <div style="font-size:48px; font-weight:800; color:var(--riq-text); line-height:1;"><span id="gatetouch-home-score">--</span></div>
                        <div id="gatetouch-home-label" style="font-size:11px; font-weight:800; color:var(--riq-primary); margin-top:5px; text-transform:uppercase; letter-spacing:1px;"><?php esc_html_e( 'SCANNING', 'gatetouch-ai-seo' ); ?></div>
                    </div>
                </div>
                
                <div style="flex-grow:1;">
                    <h2 style="font-size:26px; font-weight:800; margin:0 0 12px; color:var(--riq-text);"><?php esc_html_e( 'Enterprise Technical Audit', 'gatetouch-ai-seo' ); ?></h2>
                    <p style="color:var(--riq-text-light); font-size:15px; line-height:1.6; margin-bottom:24px; max-width:500px;">
                        <?php esc_html_e( 'Analyzing', 'gatetouch-ai-seo' ); ?> <code style="background:#f1f5f9; padding:3px 8px; border-radius:6px; font-weight:600; color:var(--riq-primary);"><?php echo esc_url( get_home_url() ); ?></code>.
                        Our AI engine evaluates 40+ technical signals including Core Web Vitals, Semantic Richness, and Entity Relationships.
                    </p>
                    <button type="button" id="gatetouch-refresh-home" class="gatetouch-btn gatetouch-btn--primary">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right:8px;"><path d="M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                        Run Deep Analysis
                    </button>
                </div>

                <div class="gatetouch-site-brand" style="width:180px; flex-shrink:0; display:flex; align-items:center; justify-content:center;">
                <?php
                $custom_logo_id = get_theme_mod( 'custom_logo' );
                if ( $custom_logo_id ) {
                    echo wp_get_attachment_image( $custom_logo_id, 'medium', false, [ 'style' => 'max-width:180px; max-height:120px; object-fit:contain; border-radius:12px; filter: drop-shadow(0 10px 15px rgba(0,0,0,0.05));' ] );
                } else {
                    echo '<div style="width:140px; height:140px; background:var(--riq-ai-gradient); border-radius:24px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:8px; box-shadow:0 12px 30px rgba(99,102,241,0.25);">
                        <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8">
                            <path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>
                        </svg>
                        <span style="color:white; font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:1px;">'.esc_html(get_bloginfo('name')).'</span>
                    </div>';
                }
                ?>
                </div>
            </div>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 class="riq-section-title" style="margin:0;"><?php esc_html_e( 'Optimization Checklist', 'gatetouch-ai-seo' ); ?></h3>
            <div style="display:flex; gap:12px;">
                <div class="gatetouch-badge gatetouch-badge--blue" style="background:#fef2f2; color:#ef4444;"><span id="gatetouch-issue-count">0</span> <?php esc_html_e( 'Issues', 'gatetouch-ai-seo' ); ?></div>
                <div class="gatetouch-badge gatetouch-badge--amber"><span id="gatetouch-warn-count">0</span> <?php esc_html_e( 'Warnings', 'gatetouch-ai-seo' ); ?></div>
                <div class="gatetouch-badge gatetouch-badge--green"><span id="gatetouch-good-count">0</span> <?php esc_html_e( 'Passed', 'gatetouch-ai-seo' ); ?></div>
            </div>
        </div>

        <div id="gatetouch-home-checks" class="gatetouch-checklist-container">
             <div class="gatetouch-card" style="padding:60px; text-align:center;">
                <div class="riq-spinner" style="margin:0 auto 20px;"></div>
                Initializing AI Diagnostic Engine...
            </div>
        </div>
    </div>

    <!-- Tab: Site Audit -->
    <div id="tab-site" class="gatetouch-sub-tab-content" style="<?php echo esc_attr( $active_sub_tab === 'site' ? '' : 'display:none;' ); ?>">
        <div class="gatetouch-card">
            <div class="gatetouch-card__header" style="justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:20px;">
                    <div><?php esc_html_e( 'Full Site Audit Overview', 'gatetouch-ai-seo' ); ?></div>
                    <label style="display:flex; align-items:center; gap:8px; font-size:12px; font-weight:600; cursor:pointer;">
                        <input type="checkbox" id="riq-toggle-passed" checked> Show Passed Checks
                    </label>
                </div>
                <button id="gatetouch-trigger-full-scan" class="gatetouch-btn gatetouch-btn--ai"><?php esc_html_e( 'Dispatch Site-Wide Scan', 'gatetouch-ai-seo' ); ?></button>
            </div>
            <div class="gatetouch-card__body--no-pad">
                <table class="gatetouch-premium-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Page / Post Details', 'gatetouch-ai-seo' ); ?></th>
                            <th style="width:120px; text-align:center;"><?php esc_html_e( 'Score', 'gatetouch-ai-seo' ); ?></th>
                            <th style="width:180px;"><?php esc_html_e( 'Technical Signal', 'gatetouch-ai-seo' ); ?></th>
                            <th style="width:150px; text-align:right;"><?php esc_html_e( 'Actions', 'gatetouch-ai-seo' ); ?></th>
                        </tr>
                    </thead>
                    <tbody id="gatetouch-audit-results">
                        <tr><td colspan="4" style="padding:60px; text-align:center; color:var(--riq-text-light);"><?php esc_html_e( 'Loading site-wide audit data...', 'gatetouch-ai-seo' ); ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab: Headline Analyzer -->
    <div id="tab-headlines" class="gatetouch-sub-tab-content" style="<?php echo esc_attr( $active_sub_tab === 'headlines' ? '' : 'display:none;' ); ?>">
        <div class="gatetouch-card">
            <div class="gatetouch-card__header"><?php esc_html_e( 'Headline Sentiment & CTR Analyzer', 'gatetouch-ai-seo' ); ?></div>
            <div class="gatetouch-card__body" style="padding:40px;">
                <p style="margin-bottom:24px; color:var(--riq-text-light); font-size:15px;"><?php esc_html_e( 'Enter your post title to analyze its impact, emotional value, and search engine click-ability.', 'gatetouch-ai-seo' ); ?></p>
                <div style="display:flex; gap:12px; margin-bottom:30px;">
                    <input type="text" id="gatetouch-headline-input" class="riq-inline-input" style="flex:1; height:50px; font-size:16px;" placeholder="How to build an enterprise-grade SEO strategy...">
                    <button id="gatetouch-headline-analyze-btn" class="gatetouch-btn gatetouch-btn--primary" style="padding:0 30px;"><?php esc_html_e( 'Analyze', 'gatetouch-ai-seo' ); ?></button>
                </div>
                <div id="gatetouch-headline-result"></div>
            </div>
        </div>
    </div>

    <!-- Tab: Competitor Analysis -->
    <div id="tab-competitor" class="gatetouch-sub-tab-content" style="<?php echo esc_attr( $active_sub_tab === 'competitor' ? '' : 'display:none;' ); ?>">
        <div class="gatetouch-card">
            <div class="gatetouch-card__header"><?php esc_html_e( 'Competitor Reverse-Engineering Suite', 'gatetouch-ai-seo' ); ?></div>
            <div class="gatetouch-card__body" style="padding:40px;">
                <p style="margin-bottom:24px; color:var(--riq-text-light); font-size:15px;"><?php esc_html_e( 'Paste a competitor\'s URL below to perform a semantic gap analysis and identify their ranking secrets.', 'gatetouch-ai-seo' ); ?></p>
                <div style="display:flex; gap:12px; margin-bottom:40px;">
                    <input type="url" id="gatetouch-competitor-url" class="riq-inline-input" style="flex:1; height:50px; font-size:16px;" placeholder="https://competitor.com/blog-post-url/">
                    <button id="gatetouch-analyze-competitor-btn" class="gatetouch-btn gatetouch-btn--ai" style="padding:0 30px;"><?php esc_html_e( 'Deep Scan', 'gatetouch-ai-seo' ); ?></button>
                </div>
                <div id="gatetouch-competitor-results">
                    <div class="gatetouch-empty-state">
                        <span style="display:block; color:#94a3b8; margin-bottom:15px;"><?php echo wp_kses( GateTouch_Helpers::icon( 'search', 40 ), GateTouch_Helpers::svg_kses_allowed() ); ?></span>
                        Results will appear here after analysis.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab: Broken Link Scanner -->
    <div id="tab-crawler" class="gatetouch-sub-tab-content" style="<?php echo esc_attr( $active_sub_tab === 'crawler' ? '' : 'display:none;' ); ?>">
        <div class="gatetouch-card">
            <div class="gatetouch-card__header" style="justify-content:space-between;">
                <div><?php esc_html_e( 'Enterprise Link Intelligence', 'gatetouch-ai-seo' ); ?></div>
                <button id="gatetouch-start-crawl-btn" class="gatetouch-btn gatetouch-btn--primary"><?php esc_html_e( 'Initiate New Crawl', 'gatetouch-ai-seo' ); ?></button>
            </div>
            <div class="gatetouch-card__body" style="padding:30px;">
                <div class="gatetouch-stats-grid" style="margin-bottom:30px;">
                    <div class="gatetouch-stat-card">
                        <div class="gatetouch-stat-card__label"><?php esc_html_e( 'Crawler Status', 'gatetouch-ai-seo' ); ?></div>
                        <div id="riq-cr-status" class="gatetouch-stat-card__num" style="font-size:18px;"><?php esc_html_e( 'READY', 'gatetouch-ai-seo' ); ?></div>
                    </div>
                    <div class="gatetouch-stat-card">
                        <div class="gatetouch-stat-card__label"><?php esc_html_e( 'Pages Crawled', 'gatetouch-ai-seo' ); ?></div>
                        <div id="riq-cr-crawled" class="gatetouch-stat-card__num">0</div>
                    </div>
                    <div class="gatetouch-stat-card">
                        <div class="gatetouch-stat-card__label"><?php esc_html_e( 'Broken Discovery', 'gatetouch-ai-seo' ); ?></div>
                        <div id="riq-cr-broken" class="gatetouch-stat-card__num" style="color:#ef4444;">0</div>
                    </div>
                </div>
                
                <h4 class="riq-section-title" style="font-size:14px; margin-bottom:15px;"><?php esc_html_e( 'Critical Broken Links', 'gatetouch-ai-seo' ); ?></h4>
                <div class="gatetouch-bulk-table-container">
                    <table class="gatetouch-premium-table">
                        <tbody id="gatetouch-crawl-broken-list">
                            <tr><td style="padding:40px; text-align:center; color:var(--riq-text-light);"><?php esc_html_e( 'Start a scan to detect broken link patterns.', 'gatetouch-ai-seo' ); ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Checklist Template -->
<template id="riq-checklist-template">
<div class="gatetouch-checklist-item status-{{status}}" id="riq-issue-{{id}}">
    <div class="gatetouch-checklist-header">
        <div class="gatetouch-checklist-icon gatetouch-status--{{status}}">{{icon}}</div>
        <div class="gatetouch-checklist-title">{{title}}</div>
        <div class="gatetouch-checklist-arrow">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </div>
    </div>
    <div class="gatetouch-checklist-body">
        <div class="gatetouch-fix-area" style="background:#f8fafc; padding:20px; border-radius:12px; border-left:4px solid var(--riq-primary);">
            <div class="gatetouch-checklist-fix" style="margin-top:0;">{{fix_beginner}}</div>
            <div class="gatetouch-checklist-actions" style="margin-top:15px;">
                {{action_btn}}
                <a href="{{learn_more}}" class="gatetouch-learn-more">Learn more →</a>
            </div>
        </div>
    </div>
</div>
</template>
