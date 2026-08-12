<?php
/**
 * Import & Migration screen.
 *
 * Detect → preview → import → verify, with every step reported honestly.
 */
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files are included inside class methods, so render variables are local.

$gt_detected = GateTouch_Migration_Engine::detect_detailed();
$gt_report   = GateTouch_Migration_Engine::last_report();
$gt_all      = GateTouch_Migration_Engine::sources();
?>
<div class="gatetouch-admin-wrap gatetouch-mig">
    <?php
    GateTouch_Helpers::page_header(
        __( 'Import & Migration', 'gatetouch-ai-seo' ),
        __( 'Bring your SEO data across from another plugin without losing rankings', 'gatetouch-ai-seo' )
    );
    ?>

    <div class="gatetouch-sa-intro">
        <h2><?php esc_html_e( 'How this works', 'gatetouch-ai-seo' ); ?></h2>
        <p>
            <?php esc_html_e( 'Your existing titles, descriptions, keywords, robots rules, canonicals, social metadata, schema types, redirects and site-wide templates are copied into this plugin. Template variables are converted automatically — a Yoast title like "%%title%% %%sep%% %%sitename%%" becomes "#title# #sep# #site_title#" so it keeps rendering correctly.', 'gatetouch-ai-seo' ); ?>
        </p>
        <p>
            <strong><?php esc_html_e( 'Nothing is deleted from your old plugin.', 'gatetouch-ai-seo' ); ?></strong>
            <?php esc_html_e( 'The import only reads. You can keep the old plugin installed until you are satisfied, then deactivate it — this plugin stays silent while another SEO plugin is active, so there is never a period with duplicate meta tags.', 'gatetouch-ai-seo' ); ?>
        </p>
    </div>

    <?php if ( empty( $gt_detected ) ) : ?>

        <div class="gatetouch-card">
            <div class="gatetouch-card__header"><?php esc_html_e( 'No SEO data found', 'gatetouch-ai-seo' ); ?></div>
            <div class="gatetouch-card__body">
                <p><?php esc_html_e( 'No data from a supported SEO plugin was detected on this site. If you migrated recently, make sure the old plugin\'s data has not already been deleted.', 'gatetouch-ai-seo' ); ?></p>
                <p class="gatetouch-sa-hint">
                    <?php esc_html_e( 'Supported sources:', 'gatetouch-ai-seo' ); ?>
                    <?php echo esc_html( implode( ', ', array_map( static function ( $s ) { return $s->label(); }, $gt_all ) ) ); ?>
                </p>
            </div>
        </div>

    <?php else : ?>

        <?php foreach ( $gt_detected as $gt_slug => $gt_info ) : ?>
            <div class="gatetouch-card gatetouch-mig-card" data-source="<?php echo esc_attr( $gt_slug ); ?>">
                <div class="gatetouch-card__header">
                    <span><?php echo esc_html( $gt_info['label'] ); ?></span>
                    <span style="display:flex; align-items:center; gap:8px;">
                        <?php if ( ! empty( $gt_info['imported'] ) ) : ?>
                            <span class="gatetouch-mig-badge gatetouch-mig-badge--done">
                                <?php
                                printf(
                                    /* translators: %s: human readable time difference, e.g. "2 hours" */
                                    esc_html__( 'Imported %s ago', 'gatetouch-ai-seo' ),
                                    esc_html( human_time_diff( (int) $gt_info['imported_at'], time() ) )
                                );
                                ?>
                            </span>
                        <?php endif; ?>
                        <?php if ( $gt_info['active'] ) : ?>
                            <span class="gatetouch-mig-badge gatetouch-mig-badge--active"><?php esc_html_e( 'Currently active', 'gatetouch-ai-seo' ); ?></span>
                        <?php else : ?>
                            <span class="gatetouch-mig-badge"><?php esc_html_e( 'Data found', 'gatetouch-ai-seo' ); ?></span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="gatetouch-card__body">

                    <?php if ( ! empty( $gt_info['imported'] ) ) : ?>
                        <div class="gatetouch-mig-result is-success" style="margin-bottom:18px;">
                            <p style="margin:0;">
                                <strong><?php esc_html_e( 'Already imported.', 'gatetouch-ai-seo' ); ?></strong>
                                <?php esc_html_e( 'The counts below still show data in the other plugin because importing only reads — nothing was deleted from it. You do not need to run this again unless you have added new content there since.', 'gatetouch-ai-seo' ); ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <div class="gatetouch-mig-stats">
                        <?php
                        $gt_labels = [
                            'posts'     => __( 'Posts & pages', 'gatetouch-ai-seo' ),
                            'terms'     => __( 'Categories & tags', 'gatetouch-ai-seo' ),
                            'users'     => __( 'Authors', 'gatetouch-ai-seo' ),
                            'redirects' => __( 'Redirects', 'gatetouch-ai-seo' ),
                        ];
                        foreach ( $gt_labels as $gt_key => $gt_label ) :
                            ?>
                            <div class="gatetouch-mig-stat">
                                <span class="gatetouch-mig-stat__num"><?php echo esc_html( number_format_i18n( $gt_info['counts'][ $gt_key ] ?? 0 ) ); ?></span>
                                <span class="gatetouch-mig-stat__label"><?php echo esc_html( $gt_label ); ?></span>
                            </div>
                        <?php endforeach; ?>
                        <div class="gatetouch-mig-stat">
                            <span class="gatetouch-mig-stat__num"><?php echo $gt_info['settings'] ? '✓' : '—'; ?></span>
                            <span class="gatetouch-mig-stat__label"><?php esc_html_e( 'Site templates', 'gatetouch-ai-seo' ); ?></span>
                        </div>
                    </div>

                    <div class="gatetouch-mig-options">
                        <strong><?php esc_html_e( 'What to import', 'gatetouch-ai-seo' ); ?></strong>
                        <div class="gatetouch-mig-checks">
                            <?php
                            $gt_types = [
                                'settings'  => __( 'Site-wide templates & schema', 'gatetouch-ai-seo' ),
                                'posts'     => __( 'Posts & pages', 'gatetouch-ai-seo' ),
                                'terms'     => __( 'Categories & tags', 'gatetouch-ai-seo' ),
                                'users'     => __( 'Author profiles', 'gatetouch-ai-seo' ),
                                'redirects' => __( 'Redirects', 'gatetouch-ai-seo' ),
                            ];
                            foreach ( $gt_types as $gt_type => $gt_type_label ) :
                                ?>
                                <label class="gatetouch-sa-check">
                                    <input type="checkbox" class="gatetouch-mig-type" value="<?php echo esc_attr( $gt_type ); ?>" checked />
                                    <?php echo esc_html( $gt_type_label ); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <label class="gatetouch-sa-check gatetouch-mig-overwrite">
                            <input type="checkbox" class="gatetouch-mig-overwrite-input" />
                            <?php esc_html_e( 'Overwrite data I have already entered in this plugin', 'gatetouch-ai-seo' ); ?>
                        </label>
                        <p class="gatetouch-sa-hint">
                            <?php esc_html_e( 'Leave this off unless you are re-running the import. By default only empty fields are filled, so your own work is never replaced.', 'gatetouch-ai-seo' ); ?>
                        </p>
                    </div>

                    <div class="gatetouch-mig-actions">
                        <button type="button" class="gatetouch-btn gatetouch-btn--secondary gatetouch-mig-preview">
                            <?php esc_html_e( 'Preview import', 'gatetouch-ai-seo' ); ?>
                        </button>
                        <button type="button" class="gatetouch-btn gatetouch-btn--primary gatetouch-mig-run">
                            <?php esc_html_e( 'Start import', 'gatetouch-ai-seo' ); ?>
                        </button>
                        <button type="button" class="gatetouch-btn gatetouch-btn--secondary gatetouch-mig-verify">
                            <?php esc_html_e( 'Verify', 'gatetouch-ai-seo' ); ?>
                        </button>
                    </div>

                    <div class="gatetouch-mig-progress" hidden>
                        <div class="gatetouch-mig-progress__bar"><span></span></div>
                        <p class="gatetouch-mig-progress__label" role="status" aria-live="polite"></p>
                    </div>

                    <div class="gatetouch-mig-result" hidden></div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="gatetouch-card">
            <div class="gatetouch-card__header"><?php esc_html_e( 'After importing', 'gatetouch-ai-seo' ); ?></div>
            <div class="gatetouch-card__body">
                <ol class="gatetouch-mig-steps">
                    <li><?php esc_html_e( 'Run Verify above. It re-reads both plugins and reports any field that did not land.', 'gatetouch-ai-seo' ); ?></li>
                    <li><?php esc_html_e( 'Spot-check a few posts, one category and one author page in Search Appearance.', 'gatetouch-ai-seo' ); ?></li>
                    <li><?php esc_html_e( 'Deactivate the old SEO plugin. This plugin stays dormant until you do, to avoid duplicate meta tags.', 'gatetouch-ai-seo' ); ?></li>
                    <li><?php esc_html_e( 'View any page\'s source and confirm a single set of meta tags is present.', 'gatetouch-ai-seo' ); ?></li>
                </ol>

                <p class="gatetouch-sa-hint">
                    <?php esc_html_e( 'Your site-wide settings were snapshotted before the import began.', 'gatetouch-ai-seo' ); ?>
                </p>
                <button type="button" class="gatetouch-btn gatetouch-btn--secondary" id="gatetouch-mig-rollback">
                    <?php esc_html_e( 'Restore settings from before the import', 'gatetouch-ai-seo' ); ?>
                </button>
                <span id="gatetouch-mig-rollback-status" role="status" aria-live="polite"></span>
            </div>
        </div>

    <?php endif; ?>

    <?php if ( ! empty( $gt_report['imported'] ) ) : ?>
        <div class="gatetouch-card">
            <div class="gatetouch-card__header"><?php esc_html_e( 'Last import', 'gatetouch-ai-seo' ); ?></div>
            <div class="gatetouch-card__body">
                <table class="gatetouch-mig-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Type', 'gatetouch-ai-seo' ); ?></th>
                            <th><?php esc_html_e( 'Imported', 'gatetouch-ai-seo' ); ?></th>
                            <th><?php esc_html_e( 'Skipped', 'gatetouch-ai-seo' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $gt_report['imported'] as $gt_key => $gt_count ) : ?>
                            <tr>
                                <td><?php echo esc_html( $gt_key ); ?></td>
                                <td><?php echo esc_html( number_format_i18n( $gt_count ) ); ?></td>
                                <td><?php echo esc_html( number_format_i18n( $gt_report['skipped'][ $gt_key ] ?? 0 ) ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="gatetouch-sa-hint">
                    <?php esc_html_e( '"Skipped" means the record already had a value in this plugin and was left untouched.', 'gatetouch-ai-seo' ); ?>
                </p>
            </div>
        </div>
    <?php endif; ?>
</div>
