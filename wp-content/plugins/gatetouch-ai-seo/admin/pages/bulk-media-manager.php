<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files are included inside class methods, so render variables are local.
// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Admin media dashboard intentionally counts attachments missing alt-text metadata.

// Get Media Stats
$total_images = count( get_posts( [ 'post_type' => 'attachment', 'post_status' => 'any', 'posts_per_page' => -1, 'post_mime_type' => 'image' ] ) );
$missing_alt  = count( get_posts( [ 'post_type' => 'attachment', 'post_status' => 'any', 'posts_per_page' => -1, 'post_mime_type' => 'image', 'meta_query' => [ [ 'key' => '_wp_attachment_image_alt', 'value' => '', 'compare' => '=' ] ] ] ) );
$optimized    = $total_images - $missing_alt;
$health_score = $total_images > 0 ? round( ( $optimized / $total_images ) * 100 ) : 100;

// This panel is both its own screen and a section of the AI Image Tools tab.
// Embedded, it must not emit a second page-level <h1>.
$gt_media_embedded = ! empty( $gt_media_embedded );
?>

<div class="<?php echo esc_attr( $gt_media_embedded ? 'gatetouch-media-manager' : 'gatetouch-admin-wrap gatetouch-media-manager' ); ?>">
    <?php if ( $gt_media_embedded ) : ?>
        <div class="gatetouch-section-heading">
            <div class="gatetouch-setting-icon">
                <?php echo wp_kses( GateTouch_Helpers::icon( 'photo', 20 ), GateTouch_Helpers::svg_kses_allowed() ); ?>
            </div>
            <div class="gatetouch-section-heading__text">
                <strong><?php esc_html_e( 'Bulk Media Meta Manager', 'gatetouch-ai-seo' ); ?></strong>
                <p><?php esc_html_e( 'Optimize and automate your Media Library SEO from one centralized hub.', 'gatetouch-ai-seo' ); ?></p>
            </div>
        </div>
    <?php else : ?>
        <div class="gatetouch-media-header" style="margin-bottom:30px;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h1 style="font-size:28px; font-weight:800; margin:0; color:var(--riq-text);"><?php esc_html_e( 'Bulk Media Meta Manager', 'gatetouch-ai-seo' ); ?></h1>
                    <p style="color:var(--riq-text-light); font-size:14px; margin:5px 0 0;"><?php esc_html_e( 'Optimize and automate your Media Library SEO from one centralized hub.', 'gatetouch-ai-seo' ); ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- STATS CARDS -->
    <div class="gatetouch-stats-row" style="display:grid; grid-template-columns: repeat(5, 1fr); gap:20px; margin-bottom:30px;">
        <div class="gatetouch-stat-premium">
            <div class="gatetouch-stat-premium__label"><?php esc_html_e( 'Total Images', 'gatetouch-ai-seo' ); ?></div>
            <div class="gatetouch-stat-premium__val" id="riq-stat-total"><?php echo esc_html( $total_images ); ?></div>
            <div class="gatetouch-stat-premium__trend"><?php esc_html_e( 'Media Assets', 'gatetouch-ai-seo' ); ?></div>
        </div>
        <div class="gatetouch-stat-premium">
            <div class="gatetouch-stat-premium__label"><?php esc_html_e( 'Missing ALT Tags', 'gatetouch-ai-seo' ); ?></div>
            <div class="gatetouch-stat-premium__val" id="riq-stat-missing" style="color:#ef4444;"><?php echo esc_html( $missing_alt ); ?></div>
            <div class="gatetouch-stat-premium__trend"><?php esc_html_e( 'Needs Attention', 'gatetouch-ai-seo' ); ?></div>
        </div>
        <div class="gatetouch-stat-premium">
            <div class="gatetouch-stat-premium__label"><?php esc_html_e( 'Optimized', 'gatetouch-ai-seo' ); ?></div>
            <div class="gatetouch-stat-premium__val" id="riq-stat-optimized" style="color:#10b981;"><?php echo esc_html( $optimized ); ?></div>
            <div class="gatetouch-stat-premium__trend"><?php esc_html_e( 'SEO Ready', 'gatetouch-ai-seo' ); ?></div>
        </div>
        <div class="gatetouch-stat-premium gatetouch-stat-premium--ai">
            <div class="gatetouch-stat-premium__label"><?php esc_html_e( 'SEO Health Score', 'gatetouch-ai-seo' ); ?></div>
            <div class="gatetouch-stat-premium__val" id="riq-stat-health"><?php echo esc_html( $health_score ); ?>%</div>
            <div class="gatetouch-stat-premium__trend"><?php esc_html_e( 'Optimization Level', 'gatetouch-ai-seo' ); ?></div>
        </div>
    </div>

    <!-- TABS -->
    <div class="gatetouch-tabs-modern" style="margin-bottom:30px; border-bottom:1px solid #e2e8f0; display:flex; gap:30px;">
        <a href="#" class="riq-tab-link active" data-tab="overview"><?php esc_html_e( 'Media Vault', 'gatetouch-ai-seo' ); ?></a>
        <a href="#" class="riq-tab-link" data-tab="missing"><?php esc_html_e( 'Missing Metadata', 'gatetouch-ai-seo' ); ?></a>
    </div>

    <!-- MAIN DASHBOARD CONTENT -->
    <div class="gatetouch-card" style="border:none; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
        <div class="gatetouch-card__header" style="background:#fff; border-bottom:1px solid #f1f5f9; padding:20px 30px; display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; gap:20px; align-items:center; flex:1;">
                <div style="position:relative; flex:1; max-width:400px;">
                    <input type="text" id="riq-media-search" placeholder="Search files, alt text, or titles..." class="gatetouch-input" style="width:100%; padding-left:40px; height:42px; border-radius:10px;">
                    <svg style="position:absolute; left:14px; top:12px; color:#94a3b8; width:18px; height:18px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </div>
                <select class="gatetouch-select" style="height:42px; border-radius:10px;">
                    <option><?php esc_html_e( 'Sort by: Newest First', 'gatetouch-ai-seo' ); ?></option>
                    <option><?php esc_html_e( 'Missing ALT Text', 'gatetouch-ai-seo' ); ?></option>
                    <option><?php esc_html_e( 'Missing Captions', 'gatetouch-ai-seo' ); ?></option>
                    <option><?php esc_html_e( 'High SEO Score', 'gatetouch-ai-seo' ); ?></option>
                    <option><?php esc_html_e( 'Low SEO Score', 'gatetouch-ai-seo' ); ?></option>
                </select>
            </div>
            <div style="display:flex; gap:10px;">
                <button class="gatetouch-btn gatetouch-btn--ghost gatetouch-btn--sm"><?php esc_html_e( 'Export CSV', 'gatetouch-ai-seo' ); ?></button>
                <button class="gatetouch-btn gatetouch-btn--primary gatetouch-btn--sm" id="riq-media-run-bulk"><?php esc_html_e( 'Generate AI ALT Text', 'gatetouch-ai-seo' ); ?></button>
            </div>
        </div>

        <div class="gatetouch-card__body" style="padding:0;">
            <div class="gatetouch-bulk-table-container">
                <table class="gatetouch-premium-table" id="riq-media-table">
                    <thead>
                        <tr>
                            <th style="width:40px; padding-left:30px;"><input type="checkbox" id="riq-media-select-all"></th>
                            <th style="width:80px;"><?php esc_html_e( 'Image', 'gatetouch-ai-seo' ); ?></th>
                            <th style="width:200px;"><?php esc_html_e( 'File Details', 'gatetouch-ai-seo' ); ?></th>
                            <th style="width:250px;"><?php esc_html_e( 'ALT Text', 'gatetouch-ai-seo' ); ?></th>
                            <th style="width:150px;"><?php esc_html_e( 'Title & Caption', 'gatetouch-ai-seo' ); ?></th>
                            <th style="width:120px;"><?php esc_html_e( 'SEO Score', 'gatetouch-ai-seo' ); ?></th>
                            <th style="width:120px;"><?php esc_html_e( 'Status', 'gatetouch-ai-seo' ); ?></th>
                            <th style="text-align:right; padding-right:30px;"><?php esc_html_e( 'Actions', 'gatetouch-ai-seo' ); ?></th>
                        </tr>
                    </thead>
                    <tbody id="riq-media-tbody">
                        <?php if ( $total_images === 0 ) : ?>
                            <tr><td colspan="8" style="padding:100px; text-align:center;">
                                <div style="color:#94a3b8; margin-bottom:20px;"><?php echo wp_kses( GateTouch_Helpers::icon( 'photo', 48 ), GateTouch_Helpers::svg_kses_allowed() ); ?></div>
                                <h3 style="margin:0;"><?php esc_html_e( 'No images found in your Media Library.', 'gatetouch-ai-seo' ); ?></h3>
                                <p style="color:#64748b;"><?php esc_html_e( 'Upload some images to start optimizing your media SEO.', 'gatetouch-ai-seo' ); ?></p>
                            </td></tr>
                        <?php else : ?>
                            <!-- Media rows will be loaded via AJAX for performance -->
                             <tr><td colspan="8" style="padding:100px; text-align:center;">
                                <div class="riq-spinner" style="margin:0 auto 20px;"></div>
                                <p style="color:#64748b; font-weight:600;"><?php esc_html_e( 'Connecting to Media Vault...', 'gatetouch-ai-seo' ); ?></p>
                            </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="gatetouch-card__footer" style="padding:20px 30px; background:#fcfdfe; display:flex; justify-content:space-between; align-items:center;">
             <div style="font-size:13px; color:#64748b;"><?php esc_html_e( 'Showing 1-20 of', 'gatetouch-ai-seo' ); ?> <?php echo esc_html( $total_images ); ?> <?php esc_html_e( 'images', 'gatetouch-ai-seo' ); ?></div>
             <div class="gatetouch-pagination-simple">
                 <button class="gatetouch-btn gatetouch-btn--ghost gatetouch-btn--sm" disabled><?php esc_html_e( 'Previous', 'gatetouch-ai-seo' ); ?></button>
                 <button class="gatetouch-btn gatetouch-btn--ghost gatetouch-btn--sm">Next</button>
             </div>
        </div>
    </div>

    <!-- EMPTY STATE (Alternative) -->
    <div id="riq-media-empty-optimized" style="display:none; padding:100px; text-align:center; background:#fff; border-radius:20px; border:2px dashed #e2e8f0; margin-top:30px;">
        <div style="color:#6366f1; margin-bottom:20px;"><?php echo wp_kses( GateTouch_Helpers::icon( 'check-circle', 56 ), GateTouch_Helpers::svg_kses_allowed() ); ?></div>
        <h2 style="margin:0; font-size:24px; font-weight:800;"><?php esc_html_e( 'Perfect Score!', 'gatetouch-ai-seo' ); ?></h2>
        <p style="color:#64748b; font-size:16px;"><?php esc_html_e( 'Great! Your media library is fully optimized and AI-enhanced.', 'gatetouch-ai-seo' ); ?></p>
    </div>
</div>
