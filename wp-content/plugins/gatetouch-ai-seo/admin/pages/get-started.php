<?php
/**
 * First-run setup wizard.
 *
 * Ordering is deliberate. The old wizard opened on an API key field, which reads
 * as a paywall to a free user and blocks the 90% of value that needs no key at
 * all. The funnel now runs: bring your data across → tell us who you are →
 * confirm the essentials → optionally add AI.
 *
 * Every step persists. Steps 2-4 post to gatetouch_complete_setup, which is the
 * single writer for the whole wizard.
 */
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files are included inside class methods, so render variables are local.
// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Setup wizard step routing uses sanitized read-only GET parameters and does not change state.

$total_steps = 4;
$step = isset( $_GET['step'] ) ? (int) $_GET['step'] : 1;
$step = max( 1, min( $total_steps, $step ) );
$progress = (int) round( ( ( $step - 1 ) / $total_steps ) * 100 );

$gt_detected = class_exists( 'GateTouch_Migration_Engine' ) ? GateTouch_Migration_Engine::detect_detailed() : [];
$gt_schema   = class_exists( 'GateTouch_Schema_Engine' ) ? GateTouch_Schema_Engine::settings() : [];
$gt_sa       = GateTouch_Search_Appearance::settings();
$gt_sitemap  = get_option( 'gatetouch_sitemap_settings', [] );
$gt_bc       = get_option( 'gatetouch_breadcrumb_settings', [] );

$gt_step_labels = [
    1 => __( 'Import your data', 'gatetouch-ai-seo' ),
    2 => __( 'Your site identity', 'gatetouch-ai-seo' ),
    3 => __( 'Search essentials', 'gatetouch-ai-seo' ),
    4 => __( 'AI provider (optional)', 'gatetouch-ai-seo' ),
];
?>

<div class="gatetouch-setup-wizard-wrapper" style="min-height: 100vh; padding: 40px 20px;">
    <div style="max-width: 900px; margin: 0 auto;">

        <!-- Header -->
        <div style="text-align: center; margin-bottom: 40px;">
            <div style="background: linear-gradient(135deg, #6366f1, #a855f7); color: #fff; width: 64px; height: 64px; border-radius: 18px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.4);"><?php echo wp_kses( GateTouch_Helpers::icon( 'rocket', 32 ), GateTouch_Helpers::svg_kses_allowed() ); ?></div>
            <h1 style="font-size: 32px; font-weight: 800; color: #1e293b; margin: 0;"><?php esc_html_e( 'Welcome to GT SEO/GEO/AEO', 'gatetouch-ai-seo' ); ?></h1>
            <p style="color: #64748b; font-size: 16px; margin-top: 8px;"><?php esc_html_e( 'Four short steps. Everything here works without an API key — AI is optional and comes last.', 'gatetouch-ai-seo' ); ?></p>
        </div>

        <!-- Progress -->
        <div style="background: #fff; border-radius: 16px; padding: 30px; border: 1px solid #e2e8f0; margin-bottom: 30px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <span style="font-weight: 700; color: #1e293b; font-size: 14px;">
                    <?php
                    printf(
                        /* translators: 1: current step number, 2: total steps, 3: step name */
                        esc_html__( 'Step %1$d of %2$d — %3$s', 'gatetouch-ai-seo' ),
                        (int) $step,
                        (int) $total_steps,
                        esc_html( $gt_step_labels[ $step ] )
                    );
                    ?>
                </span>
                <span style="font-weight: 800; color: #6366f1; font-size: 14px;"><?php echo esc_html( $progress ); ?>% <?php esc_html_e( 'Complete', 'gatetouch-ai-seo' ); ?></span>
            </div>
            <div style="height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden;">
                <div style="height: 100%; width: <?php echo esc_attr( $progress ); ?>%; background: linear-gradient(to right, #6366f1, #a855f7); transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);"></div>
            </div>
        </div>

        <div class="gatetouch-setup-card" style="background: #fff; border-radius: 20px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">

        <?php if ( 1 === $step ) : ?>

            <div style="padding: 32px 40px 40px;">
                <h2 style="font-size: 22px; font-weight: 800; color: #1e293b; margin:0 0 6px;"><?php esc_html_e( 'Bring your SEO data across', 'gatetouch-ai-seo' ); ?></h2>

                <?php if ( empty( $gt_detected ) ) : ?>
                    <p style="color:#64748b; margin:0 0 24px; line-height:1.6;"><?php esc_html_e( 'We checked for data from Yoast SEO, Rank Math, All in One SEO, SEOPress, SlimSEO and The SEO Framework. Nothing was found, so this is a clean start — there is nothing to import.', 'gatetouch-ai-seo' ); ?></p>
                    <div style="padding:18px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px; color:#166534; font-size:14px;">
                        <?php echo wp_kses( GateTouch_Helpers::icon( 'check-circle', 18 ), GateTouch_Helpers::svg_kses_allowed() ); ?>
                        <?php esc_html_e( 'Nothing to migrate. Continue to the next step.', 'gatetouch-ai-seo' ); ?>
                    </div>
                    <p style="font-size:12px; color:#94a3b8; margin-top:14px;">
                        <?php
                        printf(
                            /* translators: %s: link to the Import & Migrate screen */
                            esc_html__( 'Installing another SEO plugin later? You can run an import at any time from %s.', 'gatetouch-ai-seo' ),
                            '<a href="' . esc_url( admin_url( 'admin.php?page=gatetouch-migrate' ) ) . '" style="color:#6366f1; font-weight:600;">' . esc_html__( 'Import & Migrate', 'gatetouch-ai-seo' ) . '</a>'
                        );
                        ?>
                    </p>
                <?php else : ?>
                    <p style="color:#64748b; margin:0 0 24px; line-height:1.6;"><?php esc_html_e( 'We found existing SEO data on this site. Importing copies your titles, descriptions, keywords, robots rules, canonicals, social metadata and redirects across. Nothing is deleted from the other plugin — the import only reads.', 'gatetouch-ai-seo' ); ?></p>

                    <?php foreach ( $gt_detected as $gt_slug => $gt_info ) : ?>
                    <?php $gt_done = ! empty( $gt_info['imported'] ); ?>
                    <div style="border:2px solid <?php echo esc_attr( $gt_done ? '#bbf7d0' : '#e2e8f0' ); ?>; background:<?php echo esc_attr( $gt_done ? '#f0fdf4' : '#fff' ); ?>; border-radius:12px; padding:18px; margin-bottom:12px; display:flex; align-items:center; justify-content:space-between; gap:16px;">
                        <div>
                            <div style="font-weight:800; color:#0f172a; font-size:15px;"><?php echo esc_html( $gt_info['label'] ); ?></div>
                            <div style="font-size:12px; color:<?php echo esc_attr( $gt_done ? '#166534' : '#64748b' ); ?>; margin-top:3px;">
                                <?php
                                if ( $gt_done ) {
                                    printf(
                                        /* translators: %s: human readable time difference */
                                        esc_html__( 'Already imported %s ago — nothing more to do here.', 'gatetouch-ai-seo' ),
                                        esc_html( human_time_diff( (int) $gt_info['imported_at'], time() ) )
                                    );
                                } else {
                                    echo $gt_info['active']
                                        ? esc_html__( 'Currently active on this site', 'gatetouch-ai-seo' )
                                        : esc_html__( 'Data found', 'gatetouch-ai-seo' );
                                }
                                ?>
                            </div>
                        </div>
                        <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'gatetouch-migrate' ], admin_url( 'admin.php' ) ) ); ?>" class="gatetouch-btn-platform" style="padding:9px 18px; white-space:nowrap;">
                            <?php echo $gt_done ? esc_html__( 'View import →', 'gatetouch-ai-seo' ) : esc_html__( 'Review & import →', 'gatetouch-ai-seo' ); ?>
                        </a>
                    </div>
                    <?php endforeach; ?>

                    <p style="font-size:12px; color:#94a3b8; margin-top:14px;">
                        <?php
                        printf(
                            /* translators: %s: link to the Import & Migrate screen */
                            esc_html__( 'The import screen shows a full preview before anything is written, and keeps a rollback snapshot. You can also do this later from %s.', 'gatetouch-ai-seo' ),
                            '<a href="' . esc_url( admin_url( 'admin.php?page=gatetouch-migrate' ) ) . '" style="color:#6366f1; font-weight:600;">' . esc_html__( 'Import & Migrate', 'gatetouch-ai-seo' ) . '</a>'
                        );
                        ?>
                    </p>
                <?php endif; ?>
            </div>

        <?php elseif ( 2 === $step ) : ?>

            <div style="padding: 32px 40px 40px;">
                <h2 style="font-size: 22px; font-weight: 800; color: #1e293b; margin:0 0 6px;"><?php esc_html_e( 'Who is this site?', 'gatetouch-ai-seo' ); ?></h2>
                <p style="color:#64748b; margin:0 0 24px; line-height:1.6;"><?php esc_html_e( 'This becomes your Organization entity in structured data. Brand entity signals correlate far more strongly with being cited by AI search than backlinks do, so this is the single highest-value field on this screen.', 'gatetouch-ai-seo' ); ?></p>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:18px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:8px; text-transform:uppercase; letter-spacing:0.05em;"><?php esc_html_e( 'This site represents', 'gatetouch-ai-seo' ); ?></label>
                        <select id="wiz-org-type" style="width:100%; height:46px; border:1.5px solid #cbd5e1; border-radius:10px; font-size:14px; padding:0 12px;">
                            <option value="Organization" <?php selected( $gt_schema['org_type'] ?? 'Organization', 'Organization' ); ?>><?php esc_html_e( 'An organization or company', 'gatetouch-ai-seo' ); ?></option>
                            <option value="Person" <?php selected( $gt_schema['org_type'] ?? '', 'Person' ); ?>><?php esc_html_e( 'A person / personal brand', 'gatetouch-ai-seo' ); ?></option>
                            <option value="LocalBusiness" <?php selected( $gt_schema['org_type'] ?? '', 'LocalBusiness' ); ?>><?php esc_html_e( 'A local business with a physical location', 'gatetouch-ai-seo' ); ?></option>
                            <option value="OnlineStore" <?php selected( $gt_schema['org_type'] ?? '', 'OnlineStore' ); ?>><?php esc_html_e( 'An online store', 'gatetouch-ai-seo' ); ?></option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:8px; text-transform:uppercase; letter-spacing:0.05em;"><?php esc_html_e( 'Name', 'gatetouch-ai-seo' ); ?></label>
                        <input type="text" id="wiz-org-name" value="<?php echo esc_attr( ! empty( $gt_schema['org_name'] ) ? $gt_schema['org_name'] : get_bloginfo( 'name' ) ); ?>" style="width:100%; height:46px; padding:0 14px; border:1.5px solid #cbd5e1; border-radius:10px; font-size:14px; box-sizing:border-box;" />
                    </div>
                </div>

                <div style="margin-bottom:18px;">
                    <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:8px; text-transform:uppercase; letter-spacing:0.05em;"><?php esc_html_e( 'Logo URL', 'gatetouch-ai-seo' ); ?></label>
                    <input type="url" id="wiz-org-logo" value="<?php echo esc_attr( $gt_schema['org_logo'] ?? '' ); ?>" placeholder="https://example.com/logo.png" style="width:100%; height:46px; padding:0 14px; border:1.5px solid #cbd5e1; border-radius:10px; font-size:14px; box-sizing:border-box;" />
                    <p style="font-size:12px; color:#64748b; margin:8px 0 0;"><?php esc_html_e( 'Used for the knowledge panel and as the fallback social share image. A square PNG of at least 112×112 works best.', 'gatetouch-ai-seo' ); ?></p>
                </div>

                <p style="font-size:12px; color:#94a3b8; margin:18px 0 0;">
                    <?php
                    printf(
                        /* translators: %s: link to the Search Appearance settings tab */
                        esc_html__( 'Contact details, social profiles and the rest of your Organization entity can be completed later under %s.', 'gatetouch-ai-seo' ),
                        '<a href="' . esc_url( admin_url( 'admin.php?page=gatetouch-settings&tab=appearance' ) ) . '" style="color:#6366f1; font-weight:600;">' . esc_html__( 'Settings → Search Appearance', 'gatetouch-ai-seo' ) . '</a>'
                    );
                    ?>
                </p>
            </div>

        <?php elseif ( 3 === $step ) : ?>

            <div style="padding: 32px 40px 40px;">
                <h2 style="font-size: 22px; font-weight: 800; color: #1e293b; margin:0 0 6px;"><?php esc_html_e( 'Search essentials', 'gatetouch-ai-seo' ); ?></h2>
                <p style="color:#64748b; margin:0 0 24px; line-height:1.6;"><?php esc_html_e( 'These are already switched on with sensible defaults. Change them only if you have a reason to.', 'gatetouch-ai-seo' ); ?></p>

                <div style="border:1px solid #e2e8f0; border-radius:14px; padding:20px; margin-bottom:14px; display:flex; align-items:center; justify-content:space-between; gap:20px;">
                    <div>
                        <strong style="display:block; font-size:15px; color:#0f172a;"><?php esc_html_e( 'XML sitemap', 'gatetouch-ai-seo' ); ?></strong>
                        <span style="font-size:13px; color:#64748b;"><?php esc_html_e( 'Posts, pages, taxonomies and custom post types, with IndexNow ping on publish.', 'gatetouch-ai-seo' ); ?></span>
                    </div>
                    <input type="checkbox" id="wiz-sitemap" <?php checked( ( $gt_sitemap['enabled'] ?? 'yes' ), 'yes' ); ?> style="width:20px; height:20px; flex-shrink:0;" />
                </div>

                <div style="border:1px solid #e2e8f0; border-radius:14px; padding:20px; margin-bottom:14px; display:flex; align-items:center; justify-content:space-between; gap:20px;">
                    <div>
                        <strong style="display:block; font-size:15px; color:#0f172a;"><?php esc_html_e( 'Breadcrumbs', 'gatetouch-ai-seo' ); ?></strong>
                        <span style="font-size:13px; color:#64748b;"><?php esc_html_e( 'Adds navigation plus BreadcrumbList schema, which replaces the raw URL in search results.', 'gatetouch-ai-seo' ); ?></span>
                    </div>
                    <input type="checkbox" id="wiz-breadcrumbs" <?php checked( ( $gt_bc['enabled'] ?? '1' ), '1' ); ?> style="width:20px; height:20px; flex-shrink:0;" />
                </div>

                <div style="border:1px solid #e2e8f0; border-radius:14px; padding:20px;">
                    <strong style="display:block; font-size:15px; color:#0f172a; margin-bottom:4px;"><?php esc_html_e( 'Title separator', 'gatetouch-ai-seo' ); ?></strong>
                    <span style="font-size:13px; color:#64748b;"><?php esc_html_e( 'Sits between your page title and site name.', 'gatetouch-ai-seo' ); ?></span>
                    <div style="display:flex; gap:10px; margin-top:14px;" id="wiz-sep-group">
                        <?php
                        $gt_current_sep = $gt_sa['global']['title_separator'] ?? '|';
                        foreach ( [ '-', '|', '»', '•', '·', '—' ] as $gt_sep ) :
                            $gt_is_active = ( $gt_current_sep === $gt_sep );
                            ?>
                            <span class="sep-choice<?php echo $gt_is_active ? ' active' : ''; ?>" data-sep="<?php echo esc_attr( $gt_sep ); ?>"
                                  style="cursor:pointer; width:44px; height:44px; display:flex; align-items:center; justify-content:center; border-radius:10px; font-size:18px; font-weight:700; border:2px solid <?php echo esc_attr( $gt_is_active ? '#6366f1' : '#e2e8f0' ); ?>; background:<?php echo esc_attr( $gt_is_active ? '#f0f1fe' : '#fff' ); ?>; color:#0f172a;">
                                <?php echo esc_html( $gt_sep ); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="wiz-sep" value="<?php echo esc_attr( $gt_current_sep ); ?>" />
                </div>
            </div>

        <?php else : ?>

            <?php
            $wizard_provider  = get_option( 'gatetouch_ai_provider', 'openai' );
            $wizard_providers = [
                'openai'    => [ 'name' => 'OpenAI',        'desc' => 'GPT-4o · DALL-E',        'placeholder' => 'sk-proj-...',   'link' => 'https://platform.openai.com/api-keys',       'note' => __( 'Requires $5+ billing credit. Roughly $0.01 per optimization.', 'gatetouch-ai-seo' ) ],
                'anthropic' => [ 'name' => 'Anthropic',     'desc' => 'Claude Sonnet / Haiku',  'placeholder' => 'sk-ant-api...', 'link' => 'https://console.anthropic.com/settings/keys', 'note' => __( 'Requires $5+ billing credit. Roughly $0.003 per optimization.', 'gatetouch-ai-seo' ) ],
                'gemini'    => [ 'name' => 'Google Gemini', 'desc' => 'Gemini 1.5 Pro / Flash', 'placeholder' => 'AIza...',       'link' => 'https://aistudio.google.com/app/apikey',      'note' => __( 'Free tier available — start without any billing.', 'gatetouch-ai-seo' ) ],
            ];
            $wizard_models = [
                'openai'    => [ 'gpt-4o' => 'GPT-4o (Recommended)', 'gpt-4o-mini' => 'GPT-4o Mini (Cheaper)' ],
                'anthropic' => [ 'claude-sonnet-4-6' => 'Claude Sonnet 4.6 (Recommended)', 'claude-haiku-4-5-20251001' => 'Claude Haiku 4.5 (Cheaper)' ],
                'gemini'    => [ 'gemini-1.5-pro' => 'Gemini 1.5 Pro (Recommended)', 'gemini-1.5-flash' => 'Gemini 1.5 Flash (Free Tier)' ],
            ];
            ?>
            <div style="padding: 32px 40px 40px;">
                <h2 style="font-size: 22px; font-weight: 800; color: #1e293b; margin:0 0 6px;"><?php esc_html_e( 'Connect an AI provider', 'gatetouch-ai-seo' ); ?></h2>
                <p style="color: #64748b; margin:0 0 10px; line-height:1.6;"><?php esc_html_e( 'Entirely optional. Everything configured so far already works. A key unlocks bulk meta generation, content briefs, image alt text and social captions.', 'gatetouch-ai-seo' ); ?></p>
                <p style="color: #64748b; margin:0 0 24px; line-height:1.6; font-size:13px;"><?php esc_html_e( 'You bring your own key and pay the provider directly. Keys are stored AES-256 encrypted and never leave your site except to call the provider.', 'gatetouch-ai-seo' ); ?></p>

                <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:24px;" id="wizard-provider-grid">
                    <?php foreach ( $wizard_providers as $slug => $info ) :
                        $active = ( $wizard_provider === $slug );
                        ?>
                    <div class="wizard-prov-card" data-provider="<?php echo esc_attr( $slug ); ?>"
                         style="border:2px solid <?php echo esc_attr( $active ? '#6366f1' : '#e2e8f0' ); ?>; background:<?php echo esc_attr( $active ? '#f0f1fe' : '#fff' ); ?>; border-radius:12px; padding:16px; cursor:pointer; position:relative; transition:all 0.15s;">
                        <div style="margin-bottom:10px;"><?php echo wp_kses( GateTouch_Helpers::provider_logo( $slug, 44 ), GateTouch_Helpers::svg_kses_allowed() ); ?></div>
                        <div style="font-weight:800; color:#0f172a; font-size:14px;"><?php echo esc_html( $info['name'] ); ?></div>
                        <div style="font-size:11px; color:#64748b; margin-top:2px;"><?php echo esc_html( $info['desc'] ); ?></div>
                        <?php if ( 'gemini' === $slug ) : ?>
                        <div style="position:absolute; top:10px; right:10px; background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; border-radius:20px; font-size:10px; font-weight:700; padding:2px 8px;">FREE</div>
                        <?php endif; ?>
                        <?php if ( $active ) : ?>
                        <div class="wizard-prov-tick" style="position:absolute; bottom:10px; right:10px; background:#6366f1; width:18px; height:18px; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php foreach ( $wizard_providers as $slug => $info ) :
                    $active = ( $wizard_provider === $slug );
                    ?>
                <div class="wizard-key-row" id="wizard-key-row-<?php echo esc_attr( $slug ); ?>" style="<?php echo esc_attr( $active ? '' : 'display:none;' ); ?>">
                    <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:8px; text-transform:uppercase; letter-spacing:0.05em;">
                        <?php echo esc_html( $info['name'] ); ?> <?php esc_html_e( 'API Key', 'gatetouch-ai-seo' ); ?>
                    </label>
                    <div style="display:flex; gap:10px; align-items:stretch;">
                        <input type="password" id="wizard-key-<?php echo esc_attr( $slug ); ?>"
                               placeholder="<?php echo esc_attr( $info['placeholder'] ); ?>"
                               value="<?php echo esc_attr( GateTouch_AI_Engine::get_key( $slug ) ? '••••••••' : '' ); ?>"
                               autocomplete="new-password"
                               style="flex:1; height:46px; padding:0 14px; border:1.5px solid #cbd5e1; border-radius:10px; font-size:14px; font-family:monospace; box-sizing:border-box;" />
                        <button id="gatetouch_validate_api" class="gatetouch-btn-platform" data-provider="<?php echo esc_attr( $slug ); ?>" style="padding:0 20px; height:46px; white-space:nowrap;">
                            <?php esc_html_e( 'Validate', 'gatetouch-ai-seo' ); ?>
                        </button>
                    </div>
                    <p style="font-size:12px; color:#64748b; margin:8px 0 0;">
                        <?php echo esc_html( $info['note'] ); ?>
                        <a href="<?php echo esc_url( $info['link'] ); ?>" target="_blank" rel="noopener" style="color:#6366f1; font-weight:600; margin-left:4px;"><?php esc_html_e( 'Get key →', 'gatetouch-ai-seo' ); ?></a>
                    </p>
                </div>
                <?php endforeach; ?>

                <div id="api_validation_msg" style="margin-top: 15px; font-size: 14px; display: none;"></div>

                <div style="margin-top:20px; padding:18px; background:#f8fafc; border-radius:12px; border:1px solid #f1f5f9;">
                    <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:8px; text-transform:uppercase; letter-spacing:0.05em;"><?php esc_html_e( 'AI Model', 'gatetouch-ai-seo' ); ?></label>
                    <?php foreach ( $wizard_models as $prov => $models ) :
                        $active = ( $wizard_provider === $prov );
                        ?>
                    <select id="gatetouch_setup_model" data-provider="<?php echo esc_attr( $prov ); ?>" class="wizard-model-select"
                            style="width:100%; height:44px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:14px; padding:0 12px; display:<?php echo esc_attr( $active ? 'block' : 'none' ); ?>;">
                        <?php foreach ( $models as $val => $label ) : ?>
                        <option value="<?php echo esc_attr( $val ); ?>"><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php endforeach; ?>
                </div>

                <input type="hidden" id="wizard-active-provider" value="<?php echo esc_attr( $wizard_provider ); ?>" />
            </div>

        <?php endif; ?>

            <!-- Footer -->
            <div style="padding: 20px 40px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'gatetouch-setup-wizard', 'step' => max( 1, $step - 1 ) ], admin_url( 'admin.php' ) ) ); ?>"
                   class="button button-link" <?php echo 1 === $step ? 'style="visibility:hidden;"' : ''; ?>><?php esc_html_e( 'Back', 'gatetouch-ai-seo' ); ?></a>
                <div style="display: flex; gap: 12px; align-items:center;">
                    <button id="gatetouch_skip_setup" class="button button-link" style="color: #94a3b8;"><?php esc_html_e( 'Skip setup', 'gatetouch-ai-seo' ); ?></button>
                    <?php if ( $step < $total_steps ) : ?>
                        <button id="gatetouch_wizard_next" class="gatetouch-btn-platform" data-next="<?php echo esc_attr( $step + 1 ); ?>" style="padding: 10px 30px; font-weight: 800;"><?php esc_html_e( 'Continue →', 'gatetouch-ai-seo' ); ?></button>
                    <?php else : ?>
                        <button id="gatetouch_finish_setup" class="gatetouch-btn-platform" style="padding: 10px 30px; font-weight: 800; background: #10b981;"><?php esc_html_e( 'Finish setup ✓', 'gatetouch-ai-seo' ); ?></button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <p style="font-size: 13px; color: #94a3b8;">
                <?php esc_html_e( 'Need help?', 'gatetouch-ai-seo' ); ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=gatetouch-help&tab=docs' ) ); ?>" style="color: #6366f1;"><?php esc_html_e( 'Open the documentation', 'gatetouch-ai-seo' ); ?></a>
                <span style="margin: 0 6px; color: #cbd5e1;">·</span>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=gatetouch-help&tab=diagnostics' ) ); ?>" style="color: #6366f1;"><?php esc_html_e( 'Run diagnostics', 'gatetouch-ai-seo' ); ?></a>
                <span style="margin: 0 6px; color: #cbd5e1;">·</span>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=gatetouch-settings' ) ); ?>" style="color: #6366f1;"><?php esc_html_e( 'Skip to all settings', 'gatetouch-ai-seo' ); ?></a>
            </p>
        </div>
    </div>
</div>
