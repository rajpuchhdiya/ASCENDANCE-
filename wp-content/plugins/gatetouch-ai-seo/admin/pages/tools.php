<?php
/**
 * GateTouch — Advanced Tools
 */
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files are included inside class methods, so render variables are local.

$robots_opts = get_option( 'gatetouch_robots_settings', [] );
$htaccess    = file_exists( ABSPATH . '.htaccess' ) ? file_get_contents( ABSPATH . '.htaccess' ) : '';
$is_htaccess_writable = wp_is_writable( ABSPATH . '.htaccess' );

// File permissions map
$perm_checks = [
    [ 'label' => '.htaccess',        'path' => ABSPATH . '.htaccess',                   'required' => false ],
    [ 'label' => 'robots.txt',       'path' => ABSPATH . 'robots.txt',                  'required' => false ],
    [ 'label' => 'wp-content/',      'path' => WP_CONTENT_DIR,                          'required' => true  ],
    [ 'label' => 'uploads/',         'path' => wp_upload_dir()['basedir'],              'required' => true  ],
    [ 'label' => 'Plugin dir',       'path' => GATETOUCH_PATH,                             'required' => false ],
    [ 'label' => 'llms.txt (virtual)', 'path' => ABSPATH,                              'required' => false ],
];

$perm_results = [];
foreach ( $perm_checks as $check ) {
    $exists   = file_exists( $check['path'] );
    $writable = $exists && wp_is_writable( $check['path'] );
    $perm_results[] = array_merge( $check, [
        'exists'   => $exists,
        'writable' => $writable,
        'perms'    => $exists ? substr( sprintf( '%o', fileperms( $check['path'] ) ), -4 ) : '—',
    ] );
}
$post_types  = get_post_types( [ 'public' => true ], 'objects' );
$taxonomies  = get_taxonomies( [ 'public' => true ], 'objects' );
?>

<div class="gatetouch-admin-wrap">
    <?php GateTouch_Helpers::page_header( __( 'Tools', 'gatetouch-ai-seo' ), __( 'Advanced SEO utilities, file editors, and data management.', 'gatetouch-ai-seo' ) ); ?>

    <div class="gatetouch-page-tabs">
        <a href="javascript:void(0)" class="gatetouch-page-tab active" data-target="tab-robots"><?php esc_html_e( 'Robots.txt Editor', 'gatetouch-ai-seo' ); ?></a>
        <a href="javascript:void(0)" class="gatetouch-page-tab" data-target="tab-htaccess"><?php esc_html_e( '.htaccess Editor', 'gatetouch-ai-seo' ); ?></a>
        <a href="javascript:void(0)" class="gatetouch-page-tab" data-target="tab-import"><?php esc_html_e( 'Import/Export', 'gatetouch-ai-seo' ); ?></a>
        <a href="javascript:void(0)" class="gatetouch-page-tab" data-target="tab-database"><?php esc_html_e( 'Database Tools', 'gatetouch-ai-seo' ); ?></a>
        <a href="javascript:void(0)" class="gatetouch-page-tab" data-target="tab-status"><?php esc_html_e( 'System Status', 'gatetouch-ai-seo' ); ?></a>
        <a href="javascript:void(0)" class="gatetouch-page-tab" data-target="tab-snippets"><?php esc_html_e( 'Code Snippets', 'gatetouch-ai-seo' ); ?></a>
    </div>

    <!-- Tab: Robots.txt -->
    <div id="tab-robots" class="gatetouch-page-tab-content">
        <div class="gatetouch-card">
            <div class="gatetouch-card__header"><?php esc_html_e( 'Robots.txt Editor', 'gatetouch-ai-seo' ); ?></div>
            <div class="gatetouch-card__body">
                <p><?php esc_html_e( 'The robots.txt editor in GT SEO/GEO/AEO allows you to set up a robots.txt file for your site that will override the default robots.txt file that WordPress creates.', 'gatetouch-ai-seo' ); ?></p>
                
                <div style="margin: 20px 0; display: flex; gap: 15px;">
                    <a href="<?php echo esc_url( home_url( '/robots.txt' ) ); ?>" target="_blank" rel="noopener noreferrer" class="gatetouch-btn gatetouch-btn--secondary">
                        <span class="dashicons dashicons-external" style="font-size:16px; margin-right:5px; width:auto; height:auto;"></span> <?php esc_html_e( 'Open Robots.txt', 'gatetouch-ai-seo' ); ?>
                    </a>
                </div>

                <div class="gatetouch-setting-row">
                    <div class="gatetouch-setting-label"><strong><?php esc_html_e( 'Enable Custom Robots.txt', 'gatetouch-ai-seo' ); ?></strong></div>
                    <div class="gatetouch-setting-control">
                        <?php GateTouch_Helpers::toggle( 'robots_custom_enabled', isset($robots_opts['custom_rules']) ); ?>
                    </div>
                </div>

                <table class="gatetouch-table" style="margin-top:20px;">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th><?php esc_html_e( 'User Agent', 'gatetouch-ai-seo' ); ?></th>
                            <th><?php esc_html_e( 'Directive', 'gatetouch-ai-seo' ); ?></th>
                            <th><?php esc_html_e( 'Value', 'gatetouch-ai-seo' ); ?></th>
                            <th width="50"></th>
                        </tr>
                    </thead>
                    <tbody id="robots-rules-body">
                        <?php
                        $ajax_path = wp_parse_url( admin_url( 'admin-ajax.php' ), PHP_URL_PATH );
                        $rules = $robots_opts['custom_rules'] ?? null;
                        // An explicitly empty saved list is still empty — only fall
                        // back to the WordPress defaults when nothing was ever saved.
                        if ( ! is_array( $rules ) ) {
                            $rules = [
                                [ 'ua' => '*', 'dir' => 'disallow', 'val' => '/wp-admin/' ],
                                [ 'ua' => '*', 'dir' => 'allow',    'val' => $ajax_path ],
                            ];
                        }
                        ?>
                        <?php if ( empty( $rules ) ) : ?>
                            <tr class="gatetouch-table__empty" id="robots-rules-empty">
                                <td colspan="5"><?php esc_html_e( 'No custom rules yet — your robots.txt uses the generated defaults. Add a rule to override them.', 'gatetouch-ai-seo' ); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ( $rules as $i => $rule ) :
                            $rule = is_array( $rule ) ? $rule : [];
                            ?>
                            <tr>
                                <td><?php echo esc_html( $i + 1 ); ?></td>
                                <td><input type="text" name="robots_ua[]" value="<?php echo esc_attr( $rule['ua'] ?? '' ); ?>" class="gatetouch-input-full"></td>
                                <td>
                                    <select name="robots_dir[]" class="gatetouch-input-full">
                                        <option value="disallow" <?php selected( $rule['dir'] ?? '', 'disallow' ); ?>>Disallow</option>
                                        <option value="allow" <?php selected( $rule['dir'] ?? '', 'allow' ); ?>>Allow</option>
                                    </select>
                                </td>
                                <td><input type="text" name="robots_val[]" value="<?php echo esc_attr( $rule['val'] ?? '' ); ?>" class="gatetouch-input-full"></td>
                                <td><button type="button" class="gatetouch-btn gatetouch-btn--ghost remove-rule"><span class="dashicons dashicons-no-alt"></span></button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="button" id="add-robots-rule" class="gatetouch-btn gatetouch-btn--secondary" style="margin-top:10px;">+ Add Rule</button>

                <?php
                // AI crawler policy is owned by Settings → Sitemaps & Files, which
                // governs all 19 crawlers and distinguishes citation crawlers from
                // training crawlers. This used to be a rival four-checkbox editor
                // writing the same option, and it fatal-errored whenever the stored
                // value was not an array. It is now a read-only summary.
                $gt_bot_groups   = GateTouch_Robots::ai_bots();
                $gt_blocked      = [];
                $gt_blocked_cite = [];
                foreach ( $gt_bot_groups as $gt_gkey => $gt_group ) {
                    foreach ( $gt_group['bots'] as $gt_bot => $gt_meta ) {
                        if ( ( $robots_opts[ $gt_meta['key'] ] ?? $gt_meta['default'] ) !== 'yes' ) {
                            $gt_blocked[] = $gt_bot;
                            if ( 'citation' === $gt_gkey ) {
                                $gt_blocked_cite[] = $gt_bot;
                            }
                        }
                    }
                }
                ?>
                <div style="margin-top:30px; border-top:1px solid #f1f5f9; padding-top:24px;">
                    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:20px; flex-wrap:wrap;">
                        <div style="flex:1; min-width:280px;">
                            <strong style="display:block; font-size:14px; color:#0f172a; margin-bottom:4px;"><?php esc_html_e( 'AI crawler access', 'gatetouch-ai-seo' ); ?></strong>
                            <p style="margin:0 0 12px; font-size:13px; color:#64748b; line-height:1.55;">
                                <?php esc_html_e( 'Managed centrally so citation crawlers and training crawlers stay separated — blocking the wrong group removes you from AI answers without protecting anything.', 'gatetouch-ai-seo' ); ?>
                            </p>

                            <?php if ( empty( $gt_blocked ) ) : ?>
                                <span style="display:inline-flex; align-items:center; gap:7px; background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; border-radius:20px; padding:5px 12px; font-size:12px; font-weight:700;">
                                    <span style="width:7px; height:7px; border-radius:50%; background:#10b981;"></span>
                                    <?php esc_html_e( 'All crawlers allowed', 'gatetouch-ai-seo' ); ?>
                                </span>
                            <?php else : ?>
                                <div style="display:flex; flex-wrap:wrap; gap:6px; margin-bottom:10px;">
                                    <?php foreach ( $gt_blocked as $gt_bot ) :
                                        $gt_is_cite = in_array( $gt_bot, $gt_blocked_cite, true );
                                        ?>
                                        <span style="display:inline-flex; align-items:center; gap:6px; border-radius:20px; padding:4px 11px; font-size:11.5px; font-weight:700;
                                            background:<?php echo esc_attr( $gt_is_cite ? '#fef2f2' : '#f1f5f9' ); ?>;
                                            color:<?php echo esc_attr( $gt_is_cite ? '#991b1b' : '#475569' ); ?>;
                                            border:1px solid <?php echo esc_attr( $gt_is_cite ? '#fecaca' : '#e2e8f0' ); ?>;">
                                            <?php echo esc_html( $gt_bot ); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                                <?php if ( ! empty( $gt_blocked_cite ) ) : ?>
                                    <p style="margin:0; font-size:12px; color:#991b1b; line-height:1.55;">
                                        <?php
                                        printf(
                                            /* translators: %s: comma-separated crawler names */
                                            esc_html__( 'Warning: %s can cite and link you in AI answers. Blocking these removes you from those results entirely.', 'gatetouch-ai-seo' ),
                                            '<strong>' . esc_html( implode( ', ', $gt_blocked_cite ) ) . '</strong>'
                                        );
                                        ?>
                                    </p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=gatetouch-settings&tab=files' ) ); ?>" class="gatetouch-btn gatetouch-btn--secondary" style="white-space:nowrap;">
                            <?php esc_html_e( 'Manage AI crawlers →', 'gatetouch-ai-seo' ); ?>
                        </a>
                    </div>
                </div>

                <div class="gatetouch-setting-row">
                    <div class="gatetouch-setting-label"><strong><?php esc_html_e( 'Block Internal Site Search URLs', 'gatetouch-ai-seo' ); ?></strong></div>
                    <div class="gatetouch-setting-control">
                        <?php GateTouch_Helpers::toggle( 'block_search', $robots_opts['block_internal_search'] ?? false ); ?>
                    </div>
                </div>
            </div>
            <div class="gatetouch-card__footer">
                <button type="button" id="save-robots" class="gatetouch-btn gatetouch-btn--primary"><?php esc_html_e( 'Save Robots.txt Settings', 'gatetouch-ai-seo' ); ?></button>
            </div>
        </div>
    </div>

    <!-- Tab: .htaccess -->
    <div id="tab-htaccess" class="gatetouch-page-tab-content" style="display:none;">
        <div class="gatetouch-card">
            <div class="gatetouch-card__header"><?php esc_html_e( '.htaccess Editor', 'gatetouch-ai-seo' ); ?></div>
            <div class="gatetouch-card__body">
                <p><?php esc_html_e( 'This allows you to edit the .htaccess file for your site. Be very careful as an incorrect change could cause your site to become inaccessible.', 'gatetouch-ai-seo' ); ?></p>
                <?php if ( ! $is_htaccess_writable ) : ?>
                    <div class="gatetouch-notice gatetouch-notice--error"><?php esc_html_e( 'Warning: Your .htaccess file is NOT writable by the server. Please check permissions.', 'gatetouch-ai-seo' ); ?></div>
                <?php endif; ?>
                <textarea id="htaccess-content" class="gatetouch-input-full gatetouch-textarea--code" rows="20" <?php disabled( ! $is_htaccess_writable ); ?>><?php echo esc_textarea( $htaccess ); ?></textarea>
            </div>
            <div class="gatetouch-card__footer">
                <button type="button" id="save-htaccess" class="gatetouch-btn gatetouch-btn--primary" <?php disabled( ! $is_htaccess_writable ); ?>><?php esc_html_e( 'Save .htaccess Changes', 'gatetouch-ai-seo' ); ?></button>
            </div>
        </div>
    </div>

    <!-- Tab: Import/Export -->
    <div id="tab-import" class="gatetouch-page-tab-content" style="display:none;">
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
            <div class="gatetouch-card">
                <div class="gatetouch-card__header"><?php esc_html_e( 'Export Settings', 'gatetouch-ai-seo' ); ?></div>
                <div class="gatetouch-card__body">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:20px;">
                        <label><input type="checkbox" class="export-opt" value="general"> <?php esc_html_e( 'Webmaster Tools', 'gatetouch-ai-seo' ); ?></label>
                        <label><input type="checkbox" class="export-opt" value="rss"> <?php esc_html_e( 'RSS Content', 'gatetouch-ai-seo' ); ?></label>
                        <label><input type="checkbox" class="export-opt" value="advanced"> <?php esc_html_e( 'Advanced Settings', 'gatetouch-ai-seo' ); ?></label>
                        <label><input type="checkbox" class="export-opt" value="titles"> <?php esc_html_e( 'Search Appearance', 'gatetouch-ai-seo' ); ?></label>
                        <label><input type="checkbox" class="export-opt" value="social"> <?php esc_html_e( 'Social Networks', 'gatetouch-ai-seo' ); ?></label>
                        <label><input type="checkbox" class="export-opt" value="sitemaps"> <?php esc_html_e( 'Sitemaps', 'gatetouch-ai-seo' ); ?></label>
                    </div>
                    <button type="button" id="riq-export-btn" class="gatetouch-btn gatetouch-btn--secondary"><?php esc_html_e( 'Export Settings', 'gatetouch-ai-seo' ); ?></button>
                </div>
            </div>

            <div class="gatetouch-card">
                <div class="gatetouch-card__header"><?php esc_html_e( 'Import Settings', 'gatetouch-ai-seo' ); ?></div>
                <div class="gatetouch-card__body">
                    <p><?php esc_html_e( 'Import settings from a previously exported JSON file.', 'gatetouch-ai-seo' ); ?></p>
                    <textarea id="riq-import-data" rows="5" class="gatetouch-input-full gatetouch-textarea--code" placeholder="Paste JSON here..."></textarea>
                    <button type="button" id="riq-import-btn" class="gatetouch-btn gatetouch-btn--primary" style="margin-top:10px;"><?php esc_html_e( 'Import Settings', 'gatetouch-ai-seo' ); ?></button>
                </div>
            </div>
        </div>

        <div class="gatetouch-card">
            <div class="gatetouch-card__header"><?php esc_html_e( 'Export Content', 'gatetouch-ai-seo' ); ?></div>
            <div class="gatetouch-card__body">
                <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:10px; margin-bottom:20px;">
                    <?php foreach ( $post_types as $pt ) : ?>
                        <label><input type="checkbox" class="export-content-pt" value="<?php echo esc_attr( $pt->name ); ?>"> <?php echo esc_html( $pt->label ); ?></label>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="gatetouch-btn gatetouch-btn--secondary"><?php esc_html_e( 'Export Content JSON', 'gatetouch-ai-seo' ); ?></button>
            </div>
        </div>

        <div class="gatetouch-card" style="margin-top: 20px;">
            <div class="gatetouch-card__header"><?php esc_html_e( 'Import from Other SEO Plugins', 'gatetouch-ai-seo' ); ?></div>
            <div class="gatetouch-card__body">
                <p style="margin-bottom: 20px;"><?php esc_html_e( 'Migrate your titles, descriptions, focus keywords, redirects, and settings from Yoast SEO, Rank Math, or legacy plugin data. Running multiple SEO plugins simultaneously is not recommended.', 'gatetouch-ai-seo' ); ?></p>
                <?php
                $detected_sources = GateTouch_Migration_Engine::detect_sources();
                if ( empty( $detected_sources ) ) :
                ?>
                    <p style="color:#64748b; font-style:italic;"><?php esc_html_e( 'No other SEO plugins or legacy data detected in the database.', 'gatetouch-ai-seo' ); ?></p>
                <?php else : ?>
                    <div style="display:flex; flex-direction:column; gap:15px;">
                        <?php if ( in_array( 'yoast', $detected_sources, true ) ) : ?>
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 15px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px;">
                                <div>
                                    <strong style="color:#1e293b;"><?php esc_html_e( 'Yoast SEO Data Detected', 'gatetouch-ai-seo' ); ?></strong>
                                    <div style="font-size:12px; color:#64748b; margin-top:2px;"><?php esc_html_e( 'Import meta titles and descriptions into GT SEO/GEO/AEO.', 'gatetouch-ai-seo' ); ?></div>
                                </div>
                                <button type="button" class="gatetouch-btn gatetouch-btn--secondary run-migration-btn" data-source="yoast"><?php esc_html_e( 'Import Yoast Data', 'gatetouch-ai-seo' ); ?></button>
                            </div>
                        <?php endif; ?>

                        <?php if ( in_array( 'rankmath', $detected_sources, true ) ) : ?>
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 15px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px;">
                                <div>
                                    <strong style="color:#1e293b;"><?php esc_html_e( 'Rank Math Data Detected', 'gatetouch-ai-seo' ); ?></strong>
                                    <div style="font-size:12px; color:#64748b; margin-top:2px;"><?php esc_html_e( 'Import meta titles and descriptions into GT SEO/GEO/AEO.', 'gatetouch-ai-seo' ); ?></div>
                                </div>
                                <button type="button" class="gatetouch-btn gatetouch-btn--secondary run-migration-btn" data-source="rankmath"><?php esc_html_e( 'Import Rank Math Data', 'gatetouch-ai-seo' ); ?></button>
                            </div>
                        <?php endif; ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Tab: Database Tools -->
    <div id="tab-database" class="gatetouch-page-tab-content" style="display:none;">
        <div class="gatetouch-card">
            <div class="gatetouch-card__header"><?php esc_html_e( 'Reset / Restore Settings', 'gatetouch-ai-seo' ); ?></div>
            <div class="gatetouch-card__body">
                <p><?php esc_html_e( 'Select settings that you would like to reset to default values.', 'gatetouch-ai-seo' ); ?></p>
                <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:15px; margin:20px 0;">
                    <label><input type="checkbox" name="reset_mod[]" value="general"> <?php esc_html_e( 'Webmaster Tools', 'gatetouch-ai-seo' ); ?></label>
                    <label><input type="checkbox" name="reset_mod[]" value="rss"> <?php esc_html_e( 'RSS Content', 'gatetouch-ai-seo' ); ?></label>
                    <label><input type="checkbox" name="reset_mod[]" value="advanced"> <?php esc_html_e( 'Advanced Settings', 'gatetouch-ai-seo' ); ?></label>
                    <label><input type="checkbox" name="reset_mod[]" value="titles"> <?php esc_html_e( 'Search Appearance', 'gatetouch-ai-seo' ); ?></label>
                    <label><input type="checkbox" name="reset_mod[]" value="social"> <?php esc_html_e( 'Social Networks', 'gatetouch-ai-seo' ); ?></label>
                    <label><input type="checkbox" name="reset_mod[]" value="sitemaps"> <?php esc_html_e( 'Sitemaps', 'gatetouch-ai-seo' ); ?></label>
                    <label><input type="checkbox" name="reset_mod[]" value="robots"> <?php esc_html_e( 'Robots.txt', 'gatetouch-ai-seo' ); ?></label>
                    <label><input type="checkbox" name="reset_mod[]" value="breadcrumbs"> <?php esc_html_e( 'Breadcrumbs', 'gatetouch-ai-seo' ); ?></label>
                </div>
                <button type="button" id="reset-settings" class="gatetouch-btn gatetouch-btn--danger"><?php esc_html_e( 'Reset Selected Settings to Default', 'gatetouch-ai-seo' ); ?></button>
            </div>
        </div>
    </div>

    <!-- Tab: System Status -->
    <div id="tab-status" class="gatetouch-page-tab-content" style="display:none;">
        <div class="gatetouch-card" style="margin-bottom:20px;">
            <div class="gatetouch-card__header"><?php esc_html_e( 'File Permissions', 'gatetouch-ai-seo' ); ?></div>
            <div class="gatetouch-card__body" style="padding:0;">
                <table style="width:100%; border-collapse:collapse; font-size:13px;">
                    <thead>
                        <tr style="background:#f8fafc; border-bottom:2px solid #e2e8f0;">
                            <th style="padding:12px 20px; text-align:left; font-weight:700; color:#475569;"><?php esc_html_e( 'File / Directory', 'gatetouch-ai-seo' ); ?></th>
                            <th style="padding:12px 16px; text-align:left; font-weight:700; color:#475569;"><?php esc_html_e( 'Path', 'gatetouch-ai-seo' ); ?></th>
                            <th style="padding:12px 16px; text-align:center; font-weight:700; color:#475569;"><?php esc_html_e( 'Exists', 'gatetouch-ai-seo' ); ?></th>
                            <th style="padding:12px 16px; text-align:center; font-weight:700; color:#475569;"><?php esc_html_e( 'Writable', 'gatetouch-ai-seo' ); ?></th>
                            <th style="padding:12px 16px; text-align:center; font-weight:700; color:#475569;"><?php esc_html_e( 'Perms', 'gatetouch-ai-seo' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $perm_results as $i => $p ) :
                            $row_bg = ( $i % 2 === 0 ) ? '#fff' : '#fafbfc';
                            $exists_badge   = $p['exists']   ? '<span class="riq-perm-badge riq-perm-badge--ok">✓ Yes</span>'  : '<span class="riq-perm-badge riq-perm-badge--warn">— No</span>';
                            $writable_badge = $p['writable'] ? '<span class="riq-perm-badge riq-perm-badge--ok">✓ Writable</span>' : '<span class="riq-perm-badge riq-perm-badge--fail">✗ Not writable</span>';
                        ?>
                        <tr style="background:<?php echo esc_attr( $row_bg ); ?>; border-bottom:1px solid #f1f5f9;">
                            <td style="padding:12px 20px; font-weight:700; color:#1e293b;"><?php echo esc_html( $p['label'] ); ?></td>
                            <td style="padding:12px 16px; color:#64748b; font-family:monospace; font-size:11px; max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?php echo esc_html( $p['path'] ); ?></td>
                            <td style="padding:12px 16px; text-align:center;"><?php echo wp_kses_post( $exists_badge ); ?></td>
                            <td style="padding:12px 16px; text-align:center;"><?php echo wp_kses_post( $p['exists'] ? $writable_badge : '<span style="color:#94a3b8; font-size:12px;">—</span>' ); ?></td>
                            <td style="padding:12px 16px; text-align:center; font-family:monospace; color:#475569;"><?php echo esc_html( $p['perms'] ); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php
                $has_issues = array_filter( $perm_results, fn( $p ) => $p['required'] && $p['exists'] && ! $p['writable'] );
                if ( ! empty( $has_issues ) ) :
                ?>
                <div style="padding:16px 20px;">
                    <div class="gatetouch-notice gatetouch-notice--error">
                        <?php esc_html_e( 'One or more required directories are not writable. GT SEO/GEO/AEO may not be able to save sitemap cache or generate files. Fix the permissions above or contact your hosting provider.', 'gatetouch-ai-seo' ); ?>
                    </div>
                </div>
                <?php else : ?>
                <div style="padding:16px 20px;">
                    <div class="gatetouch-notice gatetouch-notice--success">
                        <?php esc_html_e( 'All required directories are writable. No permission issues detected.', 'gatetouch-ai-seo' ); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="gatetouch-card">
            <div class="gatetouch-card__header"><?php esc_html_e( 'Server Environment', 'gatetouch-ai-seo' ); ?></div>
            <div class="gatetouch-card__body" style="padding:0;">
                <table style="width:100%; border-collapse:collapse; font-size:13px;">
                    <tbody>
                        <?php
                        $env_checks = [
                            [ 'label' => 'PHP Version',      'value' => PHP_VERSION,                  'ok' => version_compare( PHP_VERSION, '7.4', '>=' ) ],
                            [ 'label' => 'WordPress',        'value' => get_bloginfo('version'),       'ok' => version_compare( get_bloginfo('version'), GATETOUCH_MIN_WP, '>=' ) ],
                            [ 'label' => 'cURL',             'value' => function_exists('curl_init') ? 'Enabled' : 'Missing', 'ok' => function_exists('curl_init') ],
                            [ 'label' => 'OpenSSL',          'value' => defined('OPENSSL_VERSION_TEXT') ? OPENSSL_VERSION_TEXT : 'Available', 'ok' => extension_loaded('openssl') ],
                            [ 'label' => 'Memory Limit',     'value' => ini_get('memory_limit'),       'ok' => wp_convert_hr_to_bytes(ini_get('memory_limit')) >= 67108864 ],
                            [ 'label' => 'Max Execution',    'value' => ini_get('max_execution_time') . 's', 'ok' => (int) ini_get('max_execution_time') >= 30 || (int) ini_get('max_execution_time') === 0 ],
                            [ 'label' => 'WP Cron',          'value' => defined('DISABLE_WP_CRON') && DISABLE_WP_CRON ? 'Disabled' : 'Active', 'ok' => ! (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) ],
                            [ 'label' => 'Permalink Structure', 'value' => get_option('permalink_structure') ?: 'Plain (not recommended)', 'ok' => (bool) get_option('permalink_structure') ],
                        ];
                        foreach ( $env_checks as $i => $env ) :
                            $row_bg = ( $i % 2 === 0 ) ? '#fff' : '#fafbfc';
                        ?>
                        <tr style="background:<?php echo esc_attr( $row_bg ); ?>; border-bottom:1px solid #f1f5f9;">
                            <td style="padding:12px 20px; font-weight:700; color:#1e293b; width:220px;"><?php echo esc_html( $env['label'] ); ?></td>
                            <td style="padding:12px 16px; color:#475569;"><?php echo esc_html( $env['value'] ); ?></td>
                            <td style="padding:12px 20px; text-align:right;">
                                <?php if ( $env['ok'] ) : ?>
                                <span class="riq-perm-badge riq-perm-badge--ok">✓ OK</span>
                                <?php else : ?>
                                <span class="riq-perm-badge riq-perm-badge--fail">✗ Issue</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab: Code Snippets -->
    <div id="tab-snippets" class="gatetouch-page-tab-content" style="display:none;">
        <div class="gatetouch-card">
            <div class="gatetouch-card__header"><?php esc_html_e( 'Useful Code Snippets', 'gatetouch-ai-seo' ); ?></div>
            <div class="gatetouch-card__body">
                <p style="font-size:13px; color:#64748b; margin:0 0 20px;"><?php esc_html_e( 'Copy these snippets into your theme\'s functions.php or a custom plugin to extend GT SEO/GEO/AEO.', 'gatetouch-ai-seo' ); ?></p>
                <?php
                $snippets = [
                    [
                        'title' => 'Get GT SEO/GEO/AEO data for a post',
                        'code'  => '$meta = get_post_meta( $post_id, \'_gatetouch_meta\', true );
$title = $meta[\'seo_title\'] ?? \'\';
$desc  = $meta[\'seo_description\'] ?? \'\';',
                    ],
                    [
                        'title' => 'Disable auto-generate for specific post types',
                        'code'  => 'add_filter( \'gatetouch_auto_generate_post_types\', function( $types ) {
    return array_diff( $types, [ \'product\' ] );
} );',
                    ],
                    [
                        'title' => 'Custom schema for a post',
                        'code'  => 'add_filter( \'gatetouch_schema_output\', function( $schema, $post_id ) {
    if ( get_post_type( $post_id ) === \'product\' ) {
        $schema[\'@type\'] = \'Product\';
    }
    return $schema;
}, 10, 2 );',
                    ],
                ];
                foreach ( $snippets as $s ) : ?>
                <div style="margin-bottom:20px;">
                    <div style="font-size:13px; font-weight:700; color:#1e293b; margin-bottom:8px;"><?php echo esc_html( $s['title'] ); ?></div>
                    <textarea class="gatetouch-input-full gatetouch-textarea--code" rows="<?php echo esc_attr( substr_count( $s['code'], "\n" ) + 2 ); ?>" readonly onclick="this.select()"><?php echo esc_textarea( $s['code'] ); ?></textarea>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</div>
