<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files are included inside class methods, so render variables are local.
// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Admin list filters use sanitized read-only GET parameters and do not change state.
?>
<div class="gatetouch-card">
    <div class="gatetouch-card__header">
        <div style="display:flex; align-items:center; gap:12px;">
            <span class="rk-icon-box rk-icon-box--indigo"><?php echo wp_kses( GateTouch_Helpers::icon( 'link', 20 ), GateTouch_Helpers::svg_kses_allowed() ); ?></span>
            <div>
                <h3 style="margin:0; font-size:18px; font-weight:800;"><?php esc_html_e( 'Internal Link Assistant', 'gatetouch-ai-seo' ); ?></h3>
                <p style="margin:2px 0 0; font-size:13px; color:var(--riq-text-light); font-weight:400;"><?php esc_html_e( 'Build a powerful internal link structure with AI suggestions.', 'gatetouch-ai-seo' ); ?></p>
            </div>
        </div>
    </div>
    <div class="gatetouch-card__body">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <div class="gatetouch-alert gatetouch-alert--info" style="margin:0; flex:1;">
                Analyze your content entities to find the most relevant internal linking opportunities across your site.
            </div>
            <div style="margin-left:20px;">
                <form method="get" action="" style="display:flex; gap:10px; align-items:center;">
                    <input type="hidden" name="page" value="gatetouch-audit">
                    <input type="hidden" name="tab" value="links">
                    <select name="riq_post_type" class="gatetouch-select" onchange="this.form.submit()" style="height:42px; border-radius:10px; min-width:150px;">
                        <?php 
                        $filter = isset($_GET['riq_post_type']) ? sanitize_key($_GET['riq_post_type']) : 'all';
                        ?>
                        <option value="all" <?php selected($filter, 'all'); ?>>All Content</option>
                        <option value="post" <?php selected($filter, 'post'); ?>>Posts Only</option>
                        <option value="page" <?php selected($filter, 'page'); ?>>Pages Only</option>
                    </select>
                </form>
            </div>
        </div>

        <table class="wp-list-table widefat fixed striped posts" style="border:none; box-shadow:none;">
            <thead>
                <tr>
                    <th style="padding-left:20px;"><?php esc_html_e( 'Source Page', 'gatetouch-ai-seo' ); ?></th>
                    <th><?php esc_html_e( 'Content Type', 'gatetouch-ai-seo' ); ?></th>
                    <th style="width:200px; text-align:right; padding-right:20px;"><?php esc_html_e( 'Actions', 'gatetouch-ai-seo' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $pt_filter = ($filter === 'all') ? ['post', 'page'] : [$filter];
                $posts = get_posts(['post_type' => $pt_filter, 'numberposts' => 20]);
                foreach ($posts as $p) :
                ?>
                <tr class="gatetouch-link-row" data-id="<?php echo esc_attr( $p->ID ); ?>">
                    <td style="padding-left:20px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <span class="gatetouch-expand-icon" style="cursor:pointer; color:#94a3b8;">▶</span>
                            <strong><?php echo esc_html($p->post_title); ?></strong>
                        </div>
                    </td>
                    <td><span class="gatetouch-badge gatetouch-badge--ghost"><?php echo esc_html(get_post_type($p->ID)); ?></span></td>
                    <td style="text-align:right; padding-right:20px;">
                        <button class="gatetouch-btn gatetouch-btn--ai gatetouch-btn--sm gatetouch-find-links" data-id="<?php echo esc_attr( $p->ID ); ?>">
                            Find Links
                        </button>
                    </td>
                </tr>
                <tr class="gatetouch-link-details" id="details-<?php echo esc_attr( $p->ID ); ?>" style="display:none; background:#f8fafc;">
                    <td colspan="3" style="padding:0; border-top:none;">
                        <div class="gatetouch-link-results-content" style="padding:30px; border-left:4px solid var(--riq-primary);">
                            <div style="text-align:center; padding:20px; color:#64748b;">
                                <p>Click "Find Links" to analyze this post for internal linking opportunities.</p>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

