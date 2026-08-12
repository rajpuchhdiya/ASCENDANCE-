<?php
/**
 * GateTouch — AI & AEO Settings Panel
 */
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files are included inside class methods, so render variables are local.

// Save Logic
if ( isset( $_POST['gatetouch_save_ai_aeo'] ) && check_admin_referer( 'gatetouch_save_ai_aeo' ) ) {
    $provider = sanitize_key( wp_unslash( $_POST['ai_provider'] ?? 'openai' ) );
    update_option( 'gatetouch_ai_provider', $provider );
    update_option( 'gatetouch_ai_model', sanitize_text_field( wp_unslash( $_POST['ai_model'] ?? 'gpt-4o' ) ) );
    update_option( 'gatetouch_auto_generate', isset( $_POST['auto_generate'] ) ? '1' : '0' );

    $openai_key    = sanitize_text_field( wp_unslash( $_POST['openai_key']    ?? '' ) );
    $anthropic_key = sanitize_text_field( wp_unslash( $_POST['anthropic_key'] ?? '' ) );
    $gemini_key    = sanitize_text_field( wp_unslash( $_POST['gemini_key']    ?? '' ) );

    if ( $openai_key    && $openai_key    !== '••••••••' ) GateTouch_AI_Engine::update_key( $openai_key,    'openai' );
    if ( $anthropic_key && $anthropic_key !== '••••••••' ) GateTouch_AI_Engine::update_key( $anthropic_key, 'anthropic' );
    if ( $gemini_key    && $gemini_key    !== '••••••••' ) GateTouch_AI_Engine::update_key( $gemini_key,    'gemini' );

    // Merge, never overwrite: Search Appearance owns the Organization fields and the
    // structured-data flags inside this same option. A blind write here wipes them.
    $schema_existing = get_option( 'gatetouch_schema_settings', [] );
    $schema_existing = is_array( $schema_existing ) ? $schema_existing : [];
    $schema_settings = array_merge( $schema_existing, [
        'faq_automation'   => isset( $_POST['faq_automation'] ) ? '1' : '0',
        'nlp_optimization' => isset( $_POST['nlp_optimization'] ) ? '1' : '0',
        'entity_mapping'   => isset( $_POST['entity_mapping'] ) ? '1' : '0',
    ] );
    update_option( 'gatetouch_schema_settings', $schema_settings );

    GateTouch_Helpers::notice( __( '✅ AI & AEO settings saved successfully!', 'gatetouch-ai-seo' ), 'success' );
}

$active_provider = get_option( 'gatetouch_ai_provider', 'openai' );
$ai_model        = get_option( 'gatetouch_ai_model', 'gpt-4o' );
$auto_gen        = get_option( 'gatetouch_auto_generate', '1' );
$schema          = get_option( 'gatetouch_schema_settings', [] );

$has_openai    = (bool) GateTouch_AI_Engine::get_key( 'openai' );
$has_anthropic = (bool) GateTouch_AI_Engine::get_key( 'anthropic' );
$has_gemini    = (bool) GateTouch_AI_Engine::get_key( 'gemini' );

$provider_models = [
    'openai'    => [
        'gpt-4o'        => 'GPT-4o — Best overall (Recommended)',
        'gpt-4o-mini'   => 'GPT-4o Mini — Fast & affordable',
        'gpt-4-turbo'   => 'GPT-4 Turbo',
        'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
    ],
    'anthropic' => [
        'claude-sonnet-4-6'         => 'Claude Sonnet 4.6 — Best overall (Recommended)',
        'claude-haiku-4-5-20251001' => 'Claude Haiku 4.5 — Fast & affordable',
        'claude-opus-4-7'           => 'Claude Opus 4.7 — Most powerful',
    ],
    'gemini'    => [
        'gemini-1.5-pro'   => 'Gemini 1.5 Pro — Best overall (Recommended)',
        'gemini-1.5-flash' => 'Gemini 1.5 Flash — Fast & affordable',
        'gemini-2.0-flash' => 'Gemini 2.0 Flash — Latest generation',
    ],
];

$providers_meta = [
    'openai' => [
        'name' => 'OpenAI', 'desc' => 'GPT-4o, DALL-E 3', 'icon' => '🤖',
        'has_key' => $has_openai,
        'placeholder' => 'sk-proj-...',
        'key_name' => 'openai_key',
        'key_link' => 'https://platform.openai.com/api-keys',
        'key_link_label' => 'Get OpenAI API Key →',
    ],
    'anthropic' => [
        'name' => 'Anthropic', 'desc' => 'Claude Sonnet / Opus', 'icon' => '🧠',
        'has_key' => $has_anthropic,
        'placeholder' => 'sk-ant-api...',
        'key_name' => 'anthropic_key',
        'key_link' => 'https://console.anthropic.com/settings/keys',
        'key_link_label' => 'Get Anthropic API Key →',
    ],
    'gemini' => [
        'name' => 'Google Gemini', 'desc' => 'Gemini 1.5 Pro / Flash', 'icon' => '✨',
        'has_key' => $has_gemini,
        'placeholder' => 'AIza...',
        'key_name' => 'gemini_key',
        'key_link' => 'https://aistudio.google.com/app/apikey',
        'key_link_label' => 'Get Gemini API Key →',
    ],
];
?>

<!-- ════════════════════════════════════════════════════
     HELP INSTRUCTION PANEL (right-side slide-in)
════════════════════════════════════════════════════ -->
<div id="riq-ai-help-overlay" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.45); z-index:999990; backdrop-filter:blur(3px);"></div>

<div id="riq-ai-help-panel" style="
    position:fixed; top:32px; right:0; bottom:0; width:480px; max-width:100vw;
    background:#fff; z-index:999991;
    box-shadow:-12px 0 50px rgba(0,0,0,0.18);
    display:flex; flex-direction:column;
    transform:translateX(100%); transition:transform 0.32s cubic-bezier(0.4,0,0.2,1);
    font-family:'Inter',-apple-system,sans-serif;">

    <!-- Panel Header -->
    <div style="background:linear-gradient(135deg,#6366f1,#a855f7); padding:24px 28px; flex-shrink:0;">
        <div style="display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:12px; color:#fff;">
                <div style="background:rgba(255,255,255,0.15); width:42px; height:42px; border-radius:12px; display:flex; align-items:center; justify-content:center;" id="riq-hp-provider-icon"><?php echo wp_kses( GateTouch_Helpers::icon( 'robot', 22 ), GateTouch_Helpers::svg_kses_allowed() ); ?></div>
                <div>
                    <div style="font-size:17px; font-weight:800;" id="riq-hp-title"><?php esc_html_e( 'OpenAI Setup Guide', 'gatetouch-ai-seo' ); ?></div>
                    <div style="font-size:12px; opacity:0.8; margin-top:2px;" id="riq-hp-subtitle"><?php esc_html_e( 'Step-by-step configuration', 'gatetouch-ai-seo' ); ?></div>
                </div>
            </div>
            <button id="riq-ai-help-close" style="background:rgba(255,255,255,0.15); border:none; color:#fff; width:34px; height:34px; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
    </div>

    <!-- Panel Body -->
    <div style="flex:1; overflow-y:auto; padding:0;" id="riq-hp-body">

        <!-- OpenAI Instructions -->
        <div class="riq-hp-content" id="riq-hp-openai" style="padding:28px;">
            <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:12px; padding:16px 18px; margin-bottom:24px; display:flex; gap:12px;">
                <span class="rk-icon-box rk-icon-box--sm rk-icon-box--amber" style="flex-shrink:0;"><?php echo wp_kses( GateTouch_Helpers::icon( 'bulb', 16 ), GateTouch_Helpers::svg_kses_allowed() ); ?></span>
                <div style="font-size:13px; color:#1e40af; line-height:1.6;">OpenAI powers GPT-4o — the world's leading AI model for SEO. You need to create an account and add billing credits to use the API.</div>
            </div>

            <div style="font-size:14px; font-weight:800; color:#1e293b; margin-bottom:16px; text-transform:uppercase; letter-spacing:0.05em; display:flex; align-items:center; gap:8px;"><?php echo wp_kses( GateTouch_Helpers::icon( 'file-text', 15 ), GateTouch_Helpers::svg_kses_allowed() ); ?> Setup Steps</div>

            <?php
            $openai_steps = [
                [ 'n' => '1', 'title' => 'Create an OpenAI Account',     'desc' => 'Visit <a href="https://platform.openai.com" target="_blank" rel="noopener" style="color:#6366f1;font-weight:600;">platform.openai.com</a> and sign up for a free account using your email address.' ],
                [ 'n' => '2', 'title' => 'Add Billing Credits',          'desc' => 'Go to <strong>Settings → Billing</strong> and add a minimum of $5 in credits. GPT-4o costs approximately $0.005 per 1K tokens — each meta generation costs roughly $0.01–0.02.' ],
                [ 'n' => '3', 'title' => 'Generate Your API Key',        'desc' => 'Navigate to <strong>API Keys</strong> in the left sidebar. Click <strong>"Create new secret key"</strong>. Give it a name (e.g. "GT SEO/GEO/AEO") and copy the key immediately — it won\'t be shown again.' ],
                [ 'n' => '4', 'title' => 'Paste & Test the Key',         'desc' => 'Paste your key in the <strong>OpenAI API Key</strong> field below. Click <strong>"Test"</strong> to verify it\'s working correctly.' ],
                [ 'n' => '5', 'title' => 'Select GPT-4o Model',          'desc' => 'For best SEO results, use <strong>GPT-4o</strong>. If you want lower costs, use <strong>GPT-4o Mini</strong> — quality is still excellent for most tasks.' ],
                [ 'n' => '6', 'title' => 'Save Settings',                'desc' => 'Click <strong>"Save AI Settings"</strong> at the bottom of the page. GT SEO/GEO/AEO will now use OpenAI to generate all your SEO metadata.' ],
            ];
            foreach ( $openai_steps as $step ) {
                echo '<div style="display:flex; gap:14px; margin-bottom:18px; padding-bottom:18px; border-bottom:1px solid #f1f5f9;">';
                echo '<div style="width:32px; height:32px; background:linear-gradient(135deg,#6366f1,#a855f7); color:#fff; border-radius:10px; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:13px; flex-shrink:0;">' . esc_html( $step['n'] ) . '</div>';
                echo '<div><div style="font-weight:700; color:#1e293b; font-size:14px; margin-bottom:4px;">' . esc_html( $step['title'] ) . '</div>';
                echo '<div style="font-size:13px; color:#475569; line-height:1.6;">' . wp_kses_post( $step['desc'] ) . '</div></div>';
                echo '</div>';
            }
            ?>

            <a href="https://platform.openai.com/api-keys" target="_blank" rel="noopener"
               style="display:flex; align-items:center; justify-content:center; gap:8px; background:linear-gradient(135deg,#6366f1,#a855f7); color:#fff; border-radius:10px; padding:13px; font-weight:700; font-size:14px; text-decoration:none; margin-top:8px;">
                <?php echo wp_kses( GateTouch_Helpers::icon( 'key', 15 ), GateTouch_Helpers::svg_kses_allowed() ); ?> Open OpenAI API Keys Console
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            </a>
            <div style="margin-top:12px; padding:12px 16px; background:#f8fafc; border-radius:8px; font-size:12px; color:#64748b; text-align:center;">Average cost per full-site bulk generation: ~$1–5 for 100 posts</div>
        </div>

        <!-- Anthropic Instructions -->
        <div class="riq-hp-content" id="riq-hp-anthropic" style="padding:28px; display:none;">
            <div style="background:#fdf4ff; border:1px solid #e9d5ff; border-radius:12px; padding:16px 18px; margin-bottom:24px; display:flex; gap:12px;">
                <span class="rk-icon-box rk-icon-box--sm rk-icon-box--amber" style="flex-shrink:0;"><?php echo wp_kses( GateTouch_Helpers::icon( 'bulb', 16 ), GateTouch_Helpers::svg_kses_allowed() ); ?></span>
                <div style="font-size:13px; color:#6b21a8; line-height:1.6;">Anthropic's Claude models are known for nuanced writing and excellent instruction-following — great for high-quality SEO content generation.</div>
            </div>

            <div style="font-size:14px; font-weight:800; color:#1e293b; margin-bottom:16px; text-transform:uppercase; letter-spacing:0.05em; display:flex; align-items:center; gap:8px;"><?php echo wp_kses( GateTouch_Helpers::icon( 'file-text', 15 ), GateTouch_Helpers::svg_kses_allowed() ); ?> Setup Steps</div>

            <?php
            $anthropic_steps = [
                [ 'n' => '1', 'title' => 'Create an Anthropic Account',  'desc' => 'Visit <a href="https://console.anthropic.com" target="_blank" rel="noopener" style="color:#9333ea;font-weight:600;">console.anthropic.com</a> and sign up for an account.' ],
                [ 'n' => '2', 'title' => 'Add Credits to Your Account',  'desc' => 'Go to <strong>Settings → Billing</strong>. Add a minimum of $5 in credits. Claude Sonnet costs ~$3 per million input tokens — extremely cost-effective.' ],
                [ 'n' => '3', 'title' => 'Create an API Key',            'desc' => 'Navigate to <strong>Settings → API Keys</strong>. Click <strong>"Create Key"</strong>. Name it "GT SEO/GEO/AEO" and copy your key immediately.' ],
                [ 'n' => '4', 'title' => 'Paste & Test Your Key',        'desc' => 'Paste the key starting with <code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;">sk-ant-api...</code> into the <strong>Anthropic API Key</strong> field below. Click <strong>"Test"</strong>.' ],
                [ 'n' => '5', 'title' => 'Choose Claude Model',          'desc' => 'We recommend <strong>Claude Sonnet 4.6</strong> for the best balance of speed, quality and cost. Use <strong>Claude Haiku</strong> for faster & cheaper generation.' ],
                [ 'n' => '6', 'title' => 'Save Settings',                'desc' => 'Click <strong>"Save AI Settings"</strong>. All SEO generation will now use Anthropic Claude.' ],
            ];
            foreach ( $anthropic_steps as $step ) {
                echo '<div style="display:flex; gap:14px; margin-bottom:18px; padding-bottom:18px; border-bottom:1px solid #f1f5f9;">';
                echo '<div style="width:32px; height:32px; background:linear-gradient(135deg,#9333ea,#c026d3); color:#fff; border-radius:10px; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:13px; flex-shrink:0;">' . esc_html( $step['n'] ) . '</div>';
                echo '<div><div style="font-weight:700; color:#1e293b; font-size:14px; margin-bottom:4px;">' . esc_html( $step['title'] ) . '</div>';
                echo '<div style="font-size:13px; color:#475569; line-height:1.6;">' . wp_kses_post( $step['desc'] ) . '</div></div>';
                echo '</div>';
            }
            ?>

            <a href="https://console.anthropic.com/settings/keys" target="_blank" rel="noopener"
               style="display:flex; align-items:center; justify-content:center; gap:8px; background:linear-gradient(135deg,#9333ea,#c026d3); color:#fff; border-radius:10px; padding:13px; font-weight:700; font-size:14px; text-decoration:none; margin-top:8px;">
                <?php echo wp_kses( GateTouch_Helpers::icon( 'key', 15 ), GateTouch_Helpers::svg_kses_allowed() ); ?> Open Anthropic Console
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            </a>
            <div style="margin-top:12px; padding:12px 16px; background:#f8fafc; border-radius:8px; font-size:12px; color:#64748b; text-align:center;">Claude Sonnet ~$0.003 per optimization — very cost-efficient</div>
        </div>

        <!-- Gemini Instructions -->
        <div class="riq-hp-content" id="riq-hp-gemini" style="padding:28px; display:none;">
            <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px; padding:16px 18px; margin-bottom:24px; display:flex; gap:12px;">
                <span class="rk-icon-box rk-icon-box--sm rk-icon-box--amber" style="flex-shrink:0;"><?php echo wp_kses( GateTouch_Helpers::icon( 'bulb', 16 ), GateTouch_Helpers::svg_kses_allowed() ); ?></span>
                <div style="font-size:13px; color:#166534; line-height:1.6;">Google Gemini offers a free tier with generous limits — perfect if you're just getting started and don't want to pay for API credits immediately.</div>
            </div>

            <div style="font-size:14px; font-weight:800; color:#1e293b; margin-bottom:16px; text-transform:uppercase; letter-spacing:0.05em; display:flex; align-items:center; gap:8px;"><?php echo wp_kses( GateTouch_Helpers::icon( 'file-text', 15 ), GateTouch_Helpers::svg_kses_allowed() ); ?> Setup Steps</div>

            <?php
            $gemini_steps = [
                [ 'n' => '1', 'title' => 'Sign in with Google',          'desc' => 'Visit <a href="https://aistudio.google.com" target="_blank" rel="noopener" style="color:#16a34a;font-weight:600;">Google AI Studio</a>. Sign in with your existing Google account — no new account needed.' ],
                [ 'n' => '2', 'title' => 'Free Tier Available',          'desc' => 'Gemini 1.5 Flash offers a <strong>free tier</strong> with 15 requests per minute. For heavy usage, you can add billing in Google Cloud Console for higher limits.' ],
                [ 'n' => '3', 'title' => 'Create API Key',               'desc' => 'In Google AI Studio, click <strong>"Get API Key"</strong> in the top navigation. Then click <strong>"Create API Key"</strong>. Copy the key starting with <code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;">AIza...</code>' ],
                [ 'n' => '4', 'title' => 'Paste & Test Your Key',        'desc' => 'Paste the key in the <strong>Google Gemini Key</strong> field below. Click <strong>"Test"</strong> to confirm the connection.' ],
                [ 'n' => '5', 'title' => 'Choose Gemini Model',          'desc' => 'Use <strong>Gemini 1.5 Pro</strong> for the highest quality SEO output. <strong>Gemini 1.5 Flash</strong> is recommended for the free tier due to rate limit differences.' ],
                [ 'n' => '6', 'title' => 'Save Settings',                'desc' => 'Click <strong>"Save AI Settings"</strong>. GT SEO/GEO/AEO will now use Google Gemini for all SEO generation tasks.' ],
            ];
            foreach ( $gemini_steps as $step ) {
                echo '<div style="display:flex; gap:14px; margin-bottom:18px; padding-bottom:18px; border-bottom:1px solid #f1f5f9;">';
                echo '<div style="width:32px; height:32px; background:linear-gradient(135deg,#16a34a,#0891b2); color:#fff; border-radius:10px; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:13px; flex-shrink:0;">' . esc_html( $step['n'] ) . '</div>';
                echo '<div><div style="font-weight:700; color:#1e293b; font-size:14px; margin-bottom:4px;">' . esc_html( $step['title'] ) . '</div>';
                echo '<div style="font-size:13px; color:#475569; line-height:1.6;">' . wp_kses_post( $step['desc'] ) . '</div></div>';
                echo '</div>';
            }
            ?>

            <a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener"
               style="display:flex; align-items:center; justify-content:center; gap:8px; background:linear-gradient(135deg,#16a34a,#0891b2); color:#fff; border-radius:10px; padding:13px; font-weight:700; font-size:14px; text-decoration:none; margin-top:8px;">
                <?php echo wp_kses( GateTouch_Helpers::icon( 'key', 15 ), GateTouch_Helpers::svg_kses_allowed() ); ?> Open Google AI Studio
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            </a>
            <div style="margin-top:12px; padding:12px 16px; background:#f0fdf4; border-radius:8px; font-size:12px; color:#166534; text-align:center;">🎉 Free tier available — no billing required to get started</div>
        </div>

        <!-- Common Tips -->
        <div style="margin:0 28px 28px; padding:20px; background:linear-gradient(135deg,#f8fafc,#f1f5f9); border-radius:12px; border:1px solid #e2e8f0;">
            <div style="font-size:13px; font-weight:800; color:#1e293b; margin-bottom:12px; display:flex; align-items:center; gap:7px;"><?php echo wp_kses( GateTouch_Helpers::icon( 'shield-check', 14 ), GateTouch_Helpers::svg_kses_allowed() ); ?> Security &amp; Privacy</div>
            <ul style="margin:0; padding:0 0 0 16px; font-size:13px; color:#475569; line-height:2; list-style:disc;">
                <li><?php esc_html_e( 'Your API key is encrypted with AES-256-CBC before storage', 'gatetouch-ai-seo' ); ?></li>
                <li><?php esc_html_e( 'Keys are stored only in your local WordPress database', 'gatetouch-ai-seo' ); ?></li>
                <li><?php esc_html_e( 'Keys are never sent to our servers or third parties', 'gatetouch-ai-seo' ); ?></li>
                <li><?php esc_html_e( 'Only the WordPress admin can view or change API keys', 'gatetouch-ai-seo' ); ?></li>
            </ul>
        </div>

    </div><!-- /#riq-hp-body -->

    <div style="padding:16px 28px; border-top:1px solid #e2e8f0; background:#f8fafc; flex-shrink:0; display:flex; gap:10px;">
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=gatetouch-help' ) ); ?>" class="gatetouch-btn gatetouch-btn--ghost" style="flex:1; justify-content:center; font-size:13px;">
            📖 Full Documentation
        </a>
        <a href="https://wordpress.org/support/plugin/gatetouch-ai-seo/" target="_blank" rel="noopener" class="gatetouch-btn gatetouch-btn--primary" style="flex:1; justify-content:center; font-size:13px;">
            Support Forum
        </a>
    </div>
</div>
<!-- /HELP PANEL -->

<!-- ════════════════════════════════════════════════════
     MAIN SETTINGS FORM
════════════════════════════════════════════════════ -->
<div class="gatetouch-settings-group">
    <form method="post" id="gatetouch-ai-settings-form">
        <?php wp_nonce_field( 'gatetouch_save_ai_aeo' ); ?>
        <input type="hidden" name="gatetouch_save_ai_aeo" value="1" />

        <!-- ── 1. PROVIDER SELECTION ── -->
        <div class="gatetouch-card" style="margin-bottom:20px; border:1px solid #e2e8f0; border-radius:16px; overflow:hidden;">
            <div style="background:linear-gradient(90deg,#f8fafc,#f1f5f9); border-bottom:1px solid #e2e8f0; padding:18px 24px; display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    <span style="font-size:15px; font-weight:800; color:#0f172a;"><?php esc_html_e( 'AI Provider', 'gatetouch-ai-seo' ); ?></span>
                </div>
                <button type="button" id="riq-open-ai-help" style="display:inline-flex; align-items:center; gap:6px; background:linear-gradient(135deg,#6366f1,#a855f7); color:#fff; border:none; padding:8px 16px; border-radius:20px; font-size:12px; font-weight:700; cursor:pointer; font-family:inherit;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    <?php esc_html_e( 'How to get my key?', 'gatetouch-ai-seo' ); ?>
                </button>
            </div>
            <div style="padding:20px 24px 24px;">
                <p style="font-size:13px; color:#64748b; margin:0 0 16px;"><?php esc_html_e( 'Select your AI provider. All support full SEO meta generation, schema, FAQs, and content analysis.', 'gatetouch-ai-seo' ); ?></p>
                <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:12px;">
                    <?php foreach ( $providers_meta as $slug => $info ) :
                        $is_active = ( $active_provider === $slug );
                    ?>
                    <label class="riq-provider-card" data-provider="<?php echo esc_attr( $slug ); ?>" style="display:flex; flex-direction:column; gap:10px; padding:16px; background:<?php echo $is_active ? '#f0f1fe' : '#fff'; ?>; border:2px solid <?php echo $is_active ? '#6366f1' : '#e2e8f0'; ?>; border-radius:12px; cursor:pointer; transition:all 0.15s; position:relative;">
                        <input type="radio" name="ai_provider" value="<?php echo esc_attr( $slug ); ?>" <?php checked( $active_provider, $slug ); ?> style="position:absolute; opacity:0; pointer-events:none;" />
                        <div style="display:flex; align-items:center; gap:10px;">
                            <?php echo wp_kses( GateTouch_Helpers::provider_logo( $slug, 40 ), GateTouch_Helpers::svg_kses_allowed() ); ?>
                            <div>
                                <div style="font-weight:800; font-size:14px; color:#0f172a;"><?php echo esc_html( $info['name'] ); ?></div>
                                <div style="font-size:11px; color:#64748b; margin-top:1px;"><?php echo esc_html( $info['desc'] ); ?></div>
                            </div>
                        </div>
                        <?php if ( $info['has_key'] ) : ?>
                        <div style="display:inline-flex; align-items:center; gap:5px; background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700; width:fit-content;">
                            <span style="background:#10b981; width:5px; height:5px; border-radius:50%;"></span>
                            <?php esc_html_e( 'Key Saved', 'gatetouch-ai-seo' ); ?>
                        </div>
                        <?php else : ?>
                        <div style="display:inline-flex; align-items:center; gap:5px; background:#fffbeb; color:#92400e; border:1px solid #fde68a; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700; width:fit-content;">
                            <span style="background:#f59e0b; width:5px; height:5px; border-radius:50%;"></span>
                            <?php esc_html_e( 'Key Required', 'gatetouch-ai-seo' ); ?>
                        </div>
                        <?php endif; ?>
                        <?php if ( $is_active ) : ?>
                        <div style="position:absolute; top:10px; right:10px; background:#6366f1; width:20px; height:20px; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <?php endif; ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- ── 2. API KEY (ACTIVE PROVIDER ONLY) ── -->
        <div class="gatetouch-card" style="margin-bottom:20px; border:1px solid #e2e8f0; border-radius:16px; overflow:hidden;">
            <div style="background:linear-gradient(90deg,#f8fafc,#f1f5f9); border-bottom:1px solid #e2e8f0; padding:18px 24px; display:flex; align-items:center; gap:10px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
                <span style="font-size:15px; font-weight:800; color:#0f172a;" id="riq-key-section-title"><?php esc_html_e( 'API Key', 'gatetouch-ai-seo' ); ?></span>
                <span style="margin-left:auto; font-size:12px; color:#94a3b8;"><?php esc_html_e( 'Encrypted with AES-256 — never shared', 'gatetouch-ai-seo' ); ?></span>
            </div>
            <div style="padding:20px 24px 24px;">
                <?php foreach ( $providers_meta as $slug => $info ) :
                    $is_active = ( $active_provider === $slug );
                    $current_key = GateTouch_AI_Engine::get_key( $slug );
                    $masked = $current_key ? str_repeat( '•', 8 ) : '';
                ?>
                <div class="riq-key-row" id="riq-key-row-<?php echo esc_attr( $slug ); ?>"
                     style="<?php echo esc_attr( $is_active ? '' : 'display:none;' ); ?>">
                    <div style="display:flex; gap:12px; align-items:stretch;">
                        <div style="flex:1;">
                            <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:8px; text-transform:uppercase; letter-spacing:0.05em;">
                                <?php echo esc_html( $info['name'] ); ?> <?php esc_html_e( 'API Key', 'gatetouch-ai-seo' ); ?>
                            </label>
                            <input type="password"
                                   name="<?php echo esc_attr( $info['key_name'] ); ?>"
                                   id="riq-key-input-<?php echo esc_attr( $slug ); ?>"
                                   value="<?php echo esc_attr( $masked ); ?>"
                                   placeholder="<?php echo esc_attr( $info['placeholder'] ); ?>"
                                   autocomplete="new-password"
                                   style="width:100%; height:46px; padding:0 14px; border:1.5px solid #cbd5e1; border-radius:10px; font-size:14px; color:#334155; font-family:monospace; box-sizing:border-box;" />
                        </div>
                        <div style="display:flex; flex-direction:column; gap:6px; justify-content:flex-end;">
                            <button type="button" class="riq-test-key-btn gatetouch-btn gatetouch-btn--ghost"
                                    data-provider="<?php echo esc_attr( $slug ); ?>"
                                    style="height:46px; padding:0 18px; font-size:13px; font-weight:700; white-space:nowrap;">
                                ⚡ <?php esc_html_e( 'Test Connection', 'gatetouch-ai-seo' ); ?>
                            </button>
                        </div>
                    </div>
                    <div class="riq-key-test-result" data-provider="<?php echo esc_attr( $slug ); ?>" style="display:none; margin-top:10px; padding:10px 14px; border-radius:8px; font-size:13px; font-weight:600;"></div>
                    <div style="margin-top:10px; display:flex; align-items:center; justify-content:space-between;">
                        <a href="<?php echo esc_url( $info['key_link'] ); ?>" target="_blank" rel="noopener"
                           style="font-size:12px; font-weight:600; color:#6366f1; text-decoration:none; display:inline-flex; align-items:center; gap:5px;">
                            <?php echo wp_kses( GateTouch_Helpers::icon( 'key', 13 ), GateTouch_Helpers::svg_kses_allowed() ); ?> <?php echo esc_html( $info['key_link_label'] ); ?>
                        </a>
                        <button type="button" class="riq-open-provider-help" data-provider="<?php echo esc_attr( $slug ); ?>"
                                style="background:none; border:none; font-size:12px; font-weight:600; color:#6366f1; cursor:pointer; padding:0; font-family:inherit; display:inline-flex; align-items:center; gap:4px;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            <?php esc_html_e( 'Step-by-step guide', 'gatetouch-ai-seo' ); ?>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ── 3. MODEL SELECTION ── -->
        <div class="gatetouch-card" style="margin-bottom:20px; border:1px solid #e2e8f0; border-radius:16px; overflow:hidden;">
            <div style="background:linear-gradient(90deg,#f8fafc,#f1f5f9); border-bottom:1px solid #e2e8f0; padding:18px 24px; display:flex; align-items:center; gap:10px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
                <span style="font-size:15px; font-weight:800; color:#0f172a;"><?php esc_html_e( 'Model & Automation', 'gatetouch-ai-seo' ); ?></span>
            </div>
            <div style="padding:20px 24px 24px; display:flex; gap:30px; align-items:flex-start; flex-wrap:wrap;">
                <div style="flex:1; min-width:220px;">
                    <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:8px; text-transform:uppercase; letter-spacing:0.05em;"><?php esc_html_e( 'Active Model', 'gatetouch-ai-seo' ); ?></label>
                    <?php foreach ( $provider_models as $prov => $models ) : ?>
                    <select name="ai_model"
                            class="riq-model-select riq-model-select--<?php echo esc_attr( $prov ); ?>"
                            style="width:100%; height:44px; border:1.5px solid #cbd5e1; border-radius:10px; font-size:14px; color:#334155; padding:0 12px; display:<?php echo esc_attr( ( $active_provider === $prov ) ? 'block' : 'none' ); ?>;">
                        <?php foreach ( $models as $val => $label ) : ?>
                        <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $ai_model, $val ); ?>><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php endforeach; ?>
                </div>
                <div style="flex:1; min-width:220px;">
                    <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:8px; text-transform:uppercase; letter-spacing:0.05em;"><?php esc_html_e( 'Auto-Generate on Publish', 'gatetouch-ai-seo' ); ?></label>
                    <div style="display:flex; align-items:center; gap:10px; height:44px;">
                        <?php GateTouch_Helpers::toggle( 'auto_generate', $auto_gen === '1' ? 'yes' : 'no' ); ?>
                        <span style="font-size:13px; color:#64748b;"><?php esc_html_e( 'Generate SEO meta automatically when a post is first published', 'gatetouch-ai-seo' ); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── 4. AEO & SEMANTIC ── -->
        <div class="gatetouch-card" style="margin-bottom:20px; border:1px solid #e2e8f0; border-radius:16px; overflow:hidden;">
            <div style="background:linear-gradient(90deg,#f8fafc,#f1f5f9); border-bottom:1px solid #e2e8f0; padding:18px 24px; display:flex; align-items:center; gap:10px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                <span style="font-size:15px; font-weight:800; color:#0f172a;"><?php esc_html_e( 'AEO & Semantic Optimization', 'gatetouch-ai-seo' ); ?></span>
            </div>
            <div style="padding:8px 24px 20px;">
                <?php
                $aeo_opts = [
                    [ 'key' => 'entity_mapping',   'default' => '1',
                      'title' => __( 'Semantic Entity Mapping', 'gatetouch-ai-seo' ),
                      'desc'  => __( 'Deep relationship mapping for AI search spiders (GPTBot, Google-Extended, PerplexityBot).', 'gatetouch-ai-seo' ) ],
                    [ 'key' => 'faq_automation',   'default' => '1',
                      'title' => __( 'FAQ Schema Automation', 'gatetouch-ai-seo' ),
                      'desc'  => __( 'Auto-detect Q&A structure and deploy FAQPage JSON-LD rich snippets for Google featured snippets.', 'gatetouch-ai-seo' ) ],
                    [ 'key' => 'nlp_optimization', 'default' => '1',
                      'title' => __( 'NLP & Conversational Formatting', 'gatetouch-ai-seo' ),
                      'desc'  => __( 'Align content architecture with Natural Language Processing standards used by AI search engines.', 'gatetouch-ai-seo' ) ],
                ];
                foreach ( $aeo_opts as $i => $opt ) :
                    $is_last = ( $i === count( $aeo_opts ) - 1 );
                ?>
                <div style="<?php echo esc_attr( $is_last ? '' : 'border-bottom:1px solid #f1f5f9;' ); ?> padding:18px 0; display:flex; gap:30px; align-items:center;">
                    <div style="flex:1;">
                        <strong style="font-size:14px; color:#1e293b;"><?php echo esc_html( $opt['title'] ); ?></strong>
                        <p style="font-size:13px; color:#64748b; margin:4px 0 0; line-height:1.5;"><?php echo esc_html( $opt['desc'] ); ?></p>
                    </div>
                    <div style="flex-shrink:0;">
                        <?php GateTouch_Helpers::toggle( $opt['key'], ( $schema[ $opt['key'] ] ?? $opt['default'] ) === '1' ? 'yes' : 'no' ); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ── SAVE ── -->
        <div style="background:#fff; border:1px solid #e2e8f0; padding:20px 24px; border-radius:12px; display:flex; justify-content:flex-end; align-items:center; gap:16px; box-shadow:0 4px 10px rgba(0,0,0,0.02);">
            <span style="font-size:13px; color:#94a3b8;">
                <?php
                $api_ok = GateTouch_AI_Engine::is_api_operational();
                if ( $api_ok ) {
                    echo '<span style="color:#10b981; font-weight:700;">✓ ' . esc_html( ucfirst( $active_provider ) ) . ' connected</span>';
                } else {
                    echo '<span style="color:#f59e0b; font-weight:700; display:inline-flex; align-items:center; gap:5px;">' . wp_kses( GateTouch_Helpers::icon( 'alert-triangle', 14 ), GateTouch_Helpers::svg_kses_allowed() ) . ' API key required</span>';
                }
                ?>
            </span>
            <button type="submit" class="gatetouch-btn gatetouch-btn--primary" style="padding:12px 28px; font-size:14px; font-weight:700; border-radius:10px; cursor:pointer; display:flex; align-items:center; gap:8px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                <?php esc_html_e( 'Save AI Settings', 'gatetouch-ai-seo' ); ?>
            </button>
        </div>
    </form>
</div>
