<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files are included inside class methods, so render variables are local.

if ( ! GateTouch_AI_Engine::has_api_key() ) {
    GateTouch_Helpers::page_header( __( 'Bulk AI Optimizer', 'gatetouch-ai-seo' ), __( 'Optimize all your posts & pages with one click', 'gatetouch-ai-seo' ) );
    GateTouch_Helpers::api_key_gate( __( 'Bulk AI Optimizer', 'gatetouch-ai-seo' ) );
    return;
}
?>

    <div class="gatetouch-card">
        <div class="gatetouch-card__header" style="display:flex; align-items:center; gap:12px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            Bulk Meta Manager
            <span class="gatetouch-badge gatetouch-badge--primary"><?php esc_html_e( 'AI Powered', 'gatetouch-ai-seo' ); ?></span>
        </div>

        <!-- SAAS FILTER TOOLBAR -->
        <div class="gatetouch-saas-toolbar" style="margin-bottom: 20px;">
            <div style="display:flex; gap:12px; align-items:center;">
                <select id="riq-bulk-type" class="gatetouch-saas-select" style="min-width: 140px;">
                    <option value="post" selected><?php esc_html_e( 'Posts Only', 'gatetouch-ai-seo' ); ?></option>
                    <option value="page"><?php esc_html_e( 'Pages Only', 'gatetouch-ai-seo' ); ?></option>
                    <?php foreach ( get_post_types( [ 'public' => true, '_builtin' => false ] ) as $cpt ) : ?>
                    <option value="<?php echo esc_attr( $cpt ); ?>"><?php echo esc_html( ucfirst($cpt) ); ?> Only</option>
                    <?php endforeach; ?>
                </select>
                <select id="riq-bulk-filter" class="gatetouch-saas-select" style="min-width: 140px;">
                    <option value="all" selected><?php esc_html_e( 'All Statuses', 'gatetouch-ai-seo' ); ?></option>
                    <option value="missing"><?php esc_html_e( 'Missing Meta Only', 'gatetouch-ai-seo' ); ?></option>
                    <option value="has_meta"><?php esc_html_e( 'Optimized Only', 'gatetouch-ai-seo' ); ?></option>
                </select>
            </div>
            <div style="display:flex; gap:12px; align-items:center; flex-grow:1; justify-content:flex-end;">
                <div style="position:relative; width: 100%; max-width: 320px;">
                    <span style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#9ca3af; display:flex; align-items:center;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </span>
                    <input type="text" id="riq-bulk-search" class="gatetouch-saas-input" placeholder="Search content by title…" style="padding-left: 36px; width: 100%; box-sizing: border-box;" />
                </div>
                <button type="button" id="riq-bulk-load" class="gatetouch-saas-btn gatetouch-saas-btn--secondary">
                    Refresh
                </button>
            </div>
        </div>

        <div style="display:flex; justify-content: space-between; align-items:center; padding: 0 30px 24px; border-bottom: 1px solid var(--riq-glass-border);">
            <div style="font-size:13px; color:var(--riq-text-mid); font-weight:600;">
                <span id="riq-bulk-count-label">0 items found</span>
            </div>
            <div class="gatetouch-bulk-actions" style="display:flex; gap:12px;">
                <?php 
                $has_key      = GateTouch_AI_Engine::has_api_key();
                $lock_class   = ! $has_key ? 'gatetouch-locked-feature' : '';
                $lock_tooltip = __( 'OpenAI API Key Required. Click to configure.', 'gatetouch-ai-seo' );
                $lock_url     = admin_url( 'admin.php?page=gatetouch-settings&tab=ai' );
                ?>
                <div class="<?php echo esc_attr( $lock_class ); ?>" <?php if ( ! $has_key ) : ?>data-tooltip="<?php echo esc_attr( $lock_tooltip ); ?>" onclick="window.location.href='<?php echo esc_js( esc_url_raw( $lock_url ) ); ?>';"<?php endif; ?>>
                    <button type="button" id="riq-bulk-gen-all" class="gatetouch-btn gatetouch-btn--ai" <?php disabled( ! $has_key ); ?>>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                        AI Optimize All
                    </button>
                    <button type="button" id="riq-bulk-queue-all" class="gatetouch-btn gatetouch-btn--ghost" <?php disabled( ! $has_key ); ?> style="margin-left:8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
                        Queue Background
                    </button>
                </div>
            </div>
        </div>

        <!-- MODERN PROGRESS BAR -->
        <div id="riq-bulk-progress" class="gatetouch-progress-modern" style="display:none;">
            <div class="gatetouch-progress-label">
                <span id="riq-bulk-status"><?php esc_html_e( 'Initializing AI Engine...', 'gatetouch-ai-seo' ); ?></span>
                <span id="riq-bulk-perc">0%</span>
            </div>
            <div class="gatetouch-bar-outer">
                <div class="gatetouch-bar-inner" id="riq-bulk-bar" style="width:0%;"></div>
            </div>
        </div>

        <!-- TABLE & EMPTY STATE -->
        <div id="riq-bulk-table-wrap">
            <div id="riq-bulk-empty" class="gatetouch-empty-state" style="padding: 80px 40px; text-align:center;">
                <div class="gatetouch-empty-icon" style="width:80px; height:80px; background:#f1f5f9; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 24px; color:var(--riq-primary);">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                </div>
                <h3 style="font-size:20px; font-weight:800; margin-bottom:8px;"><?php esc_html_e( 'Ready to rank?', 'gatetouch-ai-seo' ); ?></h3>
                <p style="color:var(--riq-text-light); max-width:400px; margin:0 auto 24px;"><?php esc_html_e( 'Select your content type above and load your posts to start AI-powered meta optimization.', 'gatetouch-ai-seo' ); ?></p>
                <button type="button" onclick="jQuery('#riq-bulk-load').click();" class="gatetouch-btn gatetouch-btn--primary">
                    Get Started Now
                    <svg style="margin-left:8px;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </button>
            </div>

            <div class="gatetouch-bulk-table-container">
                <table class="gatetouch-premium-table" id="riq-bulk-table" style="display:none; width: 100%;">
                    <thead>
                        <tr>
                            <th style="width:40px;"><input type="checkbox" id="riq-check-all" /></th>
                            <th style="width:200px;"><?php esc_html_e( 'Post Title & Info', 'gatetouch-ai-seo' ); ?></th>
                            <th style="width:200px;"><?php esc_html_e( 'Meta Title', 'gatetouch-ai-seo' ); ?></th>
                            <th style="width:250px;"><?php esc_html_e( 'Meta Description', 'gatetouch-ai-seo' ); ?></th>
                            <th style="width:150px;"><?php esc_html_e( 'Focus Keyword', 'gatetouch-ai-seo' ); ?></th>
                            <th style="width:120px;"><?php esc_html_e( 'Status', 'gatetouch-ai-seo' ); ?></th>
                            <th style="width:120px; text-align:right;"><?php esc_html_e( 'Actions', 'gatetouch-ai-seo' ); ?></th>
                        </tr>
                    </thead>
                    <tbody id="riq-bulk-tbody"></tbody>
                </table>
            </div>
        </div>
        <div id="riq-bulk-pagination" class="gatetouch-pagination" style="padding:12px 20px;"></div>
    </div>

    <!-- AI CHOICE MODAL -->
    <div id="riq-ai-modal" class="gatetouch-modal" style="display:none;">
        <div class="gatetouch-modal__content">
            <div class="gatetouch-modal__header">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="background:var(--riq-ai-gradient); color:#fff; width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center;"><?php echo wp_kses( GateTouch_Helpers::icon( 'brain', 20 ), GateTouch_Helpers::svg_kses_allowed() ); ?></div>
                    <div>
                        <h3 style="margin:0; font-size:16px;"><?php esc_html_e( 'AI Optimization Choice', 'gatetouch-ai-seo' ); ?></h3>
                        <p style="margin:0; font-size:12px; color:var(--riq-text-mid);"><?php esc_html_e( 'This post already has meta tags. How should AI proceed?', 'gatetouch-ai-seo' ); ?></p>
                    </div>
                </div>
            </div>
            <div class="gatetouch-modal__body">
                <div class="gatetouch-choice-grid">
                    <button type="button" class="gatetouch-choice-card" data-mode="improve">
                        <div class="gatetouch-choice-icon"><?php echo wp_kses( GateTouch_Helpers::icon( 'sparkles', 24 ), GateTouch_Helpers::svg_kses_allowed() ); ?></div>
                        <div class="gatetouch-choice-text">
                            <strong><?php esc_html_e( 'Improve Existing', 'gatetouch-ai-seo' ); ?></strong>
                            <span><?php esc_html_e( 'Enhance your current tags while keeping the original meaning.', 'gatetouch-ai-seo' ); ?></span>
                        </div>
                    </button>
                    <button type="button" class="gatetouch-choice-card" data-mode="generate">
                        <div class="gatetouch-choice-icon"><?php echo wp_kses( GateTouch_Helpers::icon( 'refresh', 24 ), GateTouch_Helpers::svg_kses_allowed() ); ?></div>
                        <div class="gatetouch-choice-text">
                            <strong><?php esc_html_e( 'Full Overwrite', 'gatetouch-ai-seo' ); ?></strong>
                            <span><?php esc_html_e( 'Start fresh and generate entirely new high-performance tags.', 'gatetouch-ai-seo' ); ?></span>
                        </div>
                    </button>
                </div>
            </div>
            <div class="gatetouch-modal__footer">
                <button type="button" class="gatetouch-btn gatetouch-btn--ghost riq-modal-close"><?php esc_html_e( 'Cancel', 'gatetouch-ai-seo' ); ?></button>
            </div>
        </div>
    </div>
