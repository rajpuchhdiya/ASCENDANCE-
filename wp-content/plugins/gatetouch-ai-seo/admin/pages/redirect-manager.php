<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files are included inside class methods, so render variables are local.
GateTouch_Redirects::create_table();
?>
<div class="gatetouch-redirects-manager">
    
    <!-- Header Stats -->
    <div class="gatetouch-grid gatetouch-grid--4" style="margin-bottom:25px;">
        <div class="gatetouch-stat-card">
            <div class="stat-label"><?php esc_html_e( 'Total Redirects', 'gatetouch-ai-seo' ); ?></div>
            <div class="stat-value" id="stat-total-redirects">0</div>
        </div>
        <div class="gatetouch-stat-card">
            <div class="stat-label"><?php esc_html_e( 'Active 404s', 'gatetouch-ai-seo' ); ?></div>
            <div class="stat-value" id="stat-active-404s">0</div>
        </div>
        <div class="gatetouch-stat-card">
            <div class="stat-label"><?php esc_html_e( 'AI Matches', 'gatetouch-ai-seo' ); ?></div>
            <div class="stat-value" id="stat-ai-matches">0</div>
        </div>
        <div class="gatetouch-stat-card">
            <div class="stat-label"><?php esc_html_e( 'Crawl Efficiency', 'gatetouch-ai-seo' ); ?></div>
            <div class="stat-value" id="stat-crawl-efficiency" style="color:var(--riq-primary);">100%</div>
        </div>
    </div>

    <!-- Main Navigation -->
    <div class="gatetouch-card">
        <div class="gatetouch-card__header" style="border-bottom:none; padding-bottom:0;">
            <div class="gatetouch-sub-tabs" style="display:flex; gap:30px;">
                <div class="sub-tab active" data-tab="manager"><?php esc_html_e( 'Manage Redirects', 'gatetouch-ai-seo' ); ?></div>
                <div class="sub-tab" data-tab="monitoring">404 Monitoring</div>
                <div class="sub-tab" data-tab="settings"><?php esc_html_e( 'Rules & Priorities', 'gatetouch-ai-seo' ); ?></div>
            </div>
        </div>
        
        <!-- Tab: Redirect Manager -->
        <div class="gatetouch-tab-panel active" id="panel-manager">
            <div class="gatetouch-card__body">
                <div style="display:flex; justify-content:space-between; margin-bottom:20px;">
                    <div class="gatetouch-search-bar" style="flex-grow:1; max-width:400px;">
                        <input type="text" id="gatetouch-redirect-search" class="gatetouch-input" placeholder="Search by URL or Regex...">
                    </div>
                    <button id="gatetouch-add-redirect-btn" class="gatetouch-btn gatetouch-btn--primary">+ Add Redirect</button>
                </div>

                <table class="gatetouch-premium-table" id="gatetouch-redirects-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Source Path', 'gatetouch-ai-seo' ); ?></th>
                            <th><?php esc_html_e( 'Target Destination', 'gatetouch-ai-seo' ); ?></th>
                            <th><?php esc_html_e( 'Format', 'gatetouch-ai-seo' ); ?></th>
                            <th>Type</th>
                            <th>Hits</th>
                            <th><?php esc_html_e( 'Status', 'gatetouch-ai-seo' ); ?></th>
                            <th><?php esc_html_e( 'Actions', 'gatetouch-ai-seo' ); ?></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <div id="gatetouch-redirects-pagination" class="gatetouch-pagination"></div>
            </div>
        </div>

        <!-- Tab: 404 Monitoring -->
        <div class="gatetouch-tab-panel" id="panel-monitoring">
            <div class="gatetouch-card__body">
                <div style="background:var(--riq-bg); padding:15px; border-radius:12px; margin-bottom:20px; display:flex; align-items:center; gap:15px;">
                    <span class="rk-icon-box rk-icon-box--ai"><?php echo wp_kses( GateTouch_Helpers::icon( 'robot', 20 ), GateTouch_Helpers::svg_kses_allowed() ); ?></span>
                    <div>
                        <h4 style="margin:0;"><?php esc_html_e( 'AI Smart Suggestions', 'gatetouch-ai-seo' ); ?></h4>
                        <p style="margin:5px 0 0; font-size:13px; color:var(--riq-text-light);"><?php esc_html_e( 'Our AI detects 404s and suggests relevant targets based on content similarity.', 'gatetouch-ai-seo' ); ?></p>
                    </div>
                </div>
                <table class="gatetouch-premium-table" id="gatetouch-404-table">
                    <thead>
                        <tr>
                            <th>404 URL</th>
                            <th>Hits</th>
                            <th><?php esc_html_e( 'Last Accessed', 'gatetouch-ai-seo' ); ?></th>
                            <th>Bot?</th>
                            <th><?php esc_html_e( 'AI Suggestion', 'gatetouch-ai-seo' ); ?></th>
                            <th><?php esc_html_e( 'Action', 'gatetouch-ai-seo' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="6" style="text-align:center; padding:40px;"><?php esc_html_e( 'No 404 errors logged yet.', 'gatetouch-ai-seo' ); ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Advanced Redirect Modal -->
<div id="gatetouch-redirect-modal" class="gatetouch-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:99999;">
    <div class="gatetouch-modal-content" style="background:#fff; width:600px; margin:100px auto; border-radius:16px; overflow:hidden; box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);">
        <div class="gatetouch-modal-header" style="padding:20px 30px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center;">
            <h3 id="gatetouch-modal-title" style="margin:0;"><?php esc_html_e( 'Create Enterprise Redirect', 'gatetouch-ai-seo' ); ?></h3>
            <span class="gatetouch-modal-close" style="cursor:pointer; font-size:24px; color:#64748b;">&times;</span>
        </div>
        <form id="gatetouch-redirect-form" style="padding:30px;">
            <input type="hidden" name="redirect_id" id="gatetouch-redirect-id" value="0">
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
                <div class="gatetouch-form-group">
                    <label style="display:block; font-size:13px; font-weight:700; margin-bottom:8px;"><?php esc_html_e( 'Match Format', 'gatetouch-ai-seo' ); ?></label>
                    <select name="redirect_format" id="gatetouch-redirect-format" class="gatetouch-input">
                        <option value="exact"><?php esc_html_e( 'Exact Match', 'gatetouch-ai-seo' ); ?></option>
                        <option value="regex"><?php esc_html_e( 'Regex Pattern', 'gatetouch-ai-seo' ); ?></option>
                        <option value="wildcard">Wildcard (*)</option>
                    </select>
                </div>
                <div class="gatetouch-form-group">
                    <label style="display:block; font-size:13px; font-weight:700; margin-bottom:8px;"><?php esc_html_e( 'Redirect Type', 'gatetouch-ai-seo' ); ?></label>
                    <select name="redirect_type" id="gatetouch-redirect-type" class="gatetouch-input">
                        <option value="301">301 Permanent</option>
                        <option value="302">302 Temporary</option>
                        <option value="410">410 Content Gone</option>
                        <option value="451">451 Legal Removal</option>
                    </select>
                </div>
            </div>

            <div class="gatetouch-form-group" style="margin-bottom:20px;">
                <label style="display:block; font-size:13px; font-weight:700; margin-bottom:8px;"><?php esc_html_e( 'Source URL / Pattern', 'gatetouch-ai-seo' ); ?></label>
                <input type="text" name="source_url" id="gatetouch-redirect-source" class="gatetouch-input" placeholder="/old-path/" required>
            </div>

            <div class="gatetouch-form-group" style="margin-bottom:20px;">
                <label style="display:block; font-size:13px; font-weight:700; margin-bottom:8px;"><?php esc_html_e( 'Target URL / Destination', 'gatetouch-ai-seo' ); ?></label>
                <div style="display:flex; gap:10px;">
                    <input type="text" name="target_url" id="gatetouch-redirect-target" class="gatetouch-input" style="flex-grow:1;" placeholder="https://..." required>
                    <button type="button" id="gatetouch-ai-match-btn" class="gatetouch-btn gatetouch-btn--ghost" title="AI Suggestion" style="padding:0 12px; display:inline-flex; align-items:center; justify-content:center;"><?php echo wp_kses( GateTouch_Helpers::icon( 'sparkles', 16 ), GateTouch_Helpers::svg_kses_allowed() ); ?></button>
                </div>
                <div id="gatetouch-ai-suggestions-list" style="margin-top:10px; display:none;"></div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
                <div class="gatetouch-form-group">
                    <label style="display:block; font-size:13px; font-weight:700; margin-bottom:8px;"><?php esc_html_e( 'Priority', 'gatetouch-ai-seo' ); ?></label>
                    <input type="number" name="redirect_priority" id="gatetouch-redirect-priority" class="gatetouch-input" value="10">
                </div>
                <div class="gatetouch-form-group">
                    <label style="display:block; font-size:13px; font-weight:700; margin-bottom:8px;"><?php esc_html_e( 'Status', 'gatetouch-ai-seo' ); ?></label>
                    <select name="redirect_status" id="gatetouch-redirect-status" class="gatetouch-input">
                        <option value="active"><?php esc_html_e( 'Active', 'gatetouch-ai-seo' ); ?></option>
                        <option value="inactive"><?php esc_html_e( 'Inactive', 'gatetouch-ai-seo' ); ?></option>
                    </select>
                </div>
            </div>

            <div style="margin-top:20px; display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="gatetouch-btn gatetouch-btn--ghost gatetouch-modal-close"><?php esc_html_e( 'Discard', 'gatetouch-ai-seo' ); ?></button>
                <button type="submit" class="gatetouch-btn gatetouch-btn--primary"><?php esc_html_e( 'Save Rule', 'gatetouch-ai-seo' ); ?></button>
            </div>
        </form>
    </div>
</div>


