<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files are included inside class methods, so render variables are local.
/* @var WP_Post $post */
/* @var array   $meta */
/* @var array   $analysis */

$score_val   = $analysis['score'] ?? 0;
$score_color = $analysis['color'] ?? '#9ca3af';
$score_label = $analysis['label'] ?? 'Not Analyzed';
$host        = wp_parse_url( home_url(), PHP_URL_HOST );


?>
<div id="gatetouch-meta-box" class="gatetouch-mb">

    <!-- ── PREMIUM HEADER ──────────────────────────────────────── -->

    <div class="gatetouch-mb__header">
        <div class="gatetouch-mb__brand">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"
                      stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>gatetouch <span style="font-weight:400; color:var(--riq-text-mid);">AI SEO</span></span>
        </div>

        <div style="display:flex; align-items:center; gap:24px;">
            <!-- CONTENT STAT -->
            <div style="text-align:right;">
                <div style="font-size:10px; font-weight:800; color:var(--riq-text-light); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:4px;">Content Depth</div>
                <div id="gatetouch_main_word_count" style="font-size:15px; font-weight:800; color:var(--riq-text);"><?php echo number_format($analysis['word_count'] ?? 0); ?> <span style="font-weight:400; font-size:12px; color:var(--riq-text-mid);">Words</span></div>
            </div>

            <!-- SEO SCORE BADGE -->
            <div style="display:flex; flex-direction:column; align-items:flex-end;">
                <div style="font-size:10px; font-weight:800; color:var(--riq-text-light); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:4px;">SEO Performance</div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <div id="gatetouch_seo_score_label" style="font-size:13px; font-weight:700; color:<?php echo esc_attr($score_color); ?>;"><?php echo esc_html($score_label); ?></div>
                    <div id="gatetouch_seo_score_badge" style="background:<?php echo esc_attr($score_color); ?>; color:#fff; padding:4px 10px; border-radius:6px; font-weight:800; font-size:14px; min-width:32px; text-align:center; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <?php echo esc_html( (int) $score_val ); ?>
                    </div>
                </div>
            </div>

            <!-- AI SCORE BADGE -->
            <div style="display:flex; flex-direction:column; align-items:flex-end; border-left:1px solid #f1f5f9; padding-left:24px;">
                <div style="font-size:10px; font-weight:800; color:var(--riq-text-light); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:4px;">AI Readiness</div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <a href="#" id="gatetouch-link-how-to-improve" style="font-size:10px; font-weight:700; color:var(--riq-primary); text-decoration:none; text-transform:uppercase;">How to improve? →</a>
                    <div id="gatetouch_ai_score_badge" style="background:<?php echo esc_attr($analysis['ai_color'] ?? '#6366f1'); ?>; color:#fff; padding:4px 10px; border-radius:6px; font-weight:800; font-size:14px; min-width:32px; text-align:center; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <?php echo esc_html( (int) ( $analysis['ai_score'] ?? 0 ) ); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── TAB NAV ────────────────────────────────────────────── -->
    <div class="gatetouch-mb__tabs-wrap">
        <div class="gatetouch-mb__tabs">
            <button type="button" class="gatetouch-tab-btn active" data-tab="seo">Search Engine</button>
            <button type="button" class="gatetouch-tab-btn" data-tab="social">Social Media</button>
            <button type="button" class="gatetouch-tab-btn" data-tab="schema">AI Content & Schema</button>
            <button type="button" class="gatetouch-tab-btn" data-tab="analysis">Full Analysis</button>
            <button type="button" class="gatetouch-tab-btn" data-tab="advanced">Advanced</button>
        </div>
    </div>

    <!-- ── MAIN CONTENT ────────────────────────────────────────── -->
    <div class="gatetouch-mb__body">
        <?php wp_nonce_field( 'gatetouch_save_meta', 'gatetouch_nonce' ); ?>
        <input type="hidden" id="gatetouch_active_tab" value="seo" />
        <input type="hidden" id="gatetouch_active_subtab_analysis" value="analysis-seo" />
        <input type="hidden" id="gatetouch_active_subtab_schema" value="schema-main" />

        <!-- SEO TAB -->
        <div class="gatetouch-tab-panel active" id="gatetouch-tab-seo">

            <!-- ── AI COMMAND CENTER ────────────────────────────────────── -->
            <div class="gatetouch-mb__ai-bar" style="margin-bottom:30px; border-radius:12px;">
                <div class="gatetouch-ai-badge">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                    </svg>
                </div>
                <div class="gatetouch-mb__ai-content">
                    <strong>AI SEO Content Engine</strong>
                    <span>Generate high-performance metadata optimized for AEO/GEO in seconds.</span>
                </div>
                <div style="display:flex; gap:10px;">
                    <button type="button" id="gatetouch-btn-generate" class="gatetouch-btn gatetouch-btn--ai" <?php disabled( ! $has_key ); ?>>
                        Auto-Generate
                    </button>
                    <button type="button" id="gatetouch-btn-improve" class="gatetouch-btn gatetouch-btn--secondary" <?php disabled( ! $has_key ); ?>>Refine AI</button>
                </div>
            </div>

            <!-- ── MAIN CONTENT GRID ───────────────────────────────────── -->
            <div class="gatetouch-seo-grid" style="display:flex; flex-direction:column; gap:24px;">

                <!-- LEFT COLUMN: INPUTS -->
                <div class="gatetouch-seo-inputs">

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:24px;">
                        <div class="gatetouch-field gatetouch-field--keyword">
                            <label class="gatetouch-label"><span class="gatetouch-label__text">Target Focus Keyword</span></label>
                            <input type="text" id="gatetouch_focus_keyword" name="gatetouch_focus_keyword"
                                   value="<?php echo esc_attr( $meta['focus_keyword'] ?? '' ); ?>"
                                   placeholder="Main keyword..." class="gatetouch-input" />
                        </div>

                        <div class="gatetouch-field">
                            <label class="gatetouch-label"><span class="gatetouch-label__text">Secondary Keywords</span></label>
                            <input type="text" id="gatetouch_additional_keywords" name="gatetouch_additional_keywords"
                                   value="<?php echo esc_attr( $meta['additional_keywords'] ?? '' ); ?>"
                                   placeholder="e.g. word1, word2..." class="gatetouch-input" />
                        </div>
                    </div>

                    <div class="gatetouch-field gatetouch-field--title">
                        <label class="gatetouch-label">
                            <span class="gatetouch-label__text">SEO Title</span>
                            <span id="gatetouch-title-counter" class="gatetouch-counter">0 / 60</span>
                        </label>
                        <input type="text" id="gatetouch_meta_title" name="gatetouch_meta_title"
                               value="<?php echo esc_attr( $meta['meta_title'] ?? '' ); ?>" class="gatetouch-input" />
                        <div class="gatetouch-progress-bg"><div id="gatetouch-title-bar" class="gatetouch-progress-fill"></div></div>
                        <div class="gatetouch-tag-group" style="margin-top:10px;">
                            <?php foreach ( GateTouch_Variables::get_supported_vars() as $tag => $label ) :
                                if ( in_array( $tag, [ '#title#', '#sep#', '#site_title#' ] ) ) : ?>
                                <div class="gatetouch-tag-badge" data-tag="<?php echo esc_attr($tag); ?>" data-target="gatetouch_meta_title">
                                    <?php echo esc_html($tag); ?>
                                </div>
                            <?php endif; endforeach; ?>
                        </div>
                    </div>

                    <div class="gatetouch-field gatetouch-field--desc" style="margin-bottom:0;">
                        <label class="gatetouch-label">
                            <span class="gatetouch-label__text">Meta Description</span>
                            <span id="gatetouch-desc-counter" class="gatetouch-counter">0 / 160</span>
                        </label>
                        <textarea id="gatetouch_meta_description" name="gatetouch_meta_description" rows="4" class="gatetouch-textarea"><?php echo esc_textarea( $meta['meta_description'] ?? '' ); ?></textarea>
                        <div class="gatetouch-progress-bg"><div id="gatetouch-desc-bar" class="gatetouch-progress-fill"></div></div>
                        <div class="gatetouch-tag-group" style="margin-top:10px;">
                            <?php foreach ( GateTouch_Variables::get_supported_vars() as $tag => $label ) :
                                if ( in_array( $tag, [ '#title#', '#excerpt#', '#site_title#' ] ) ) : ?>
                                <div class="gatetouch-tag-badge" data-tag="<?php echo esc_attr($tag); ?>" data-target="gatetouch_meta_description">
                                    <?php echo esc_html($tag); ?>
                                </div>
                            <?php endif; endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: PREVIEW -->
                <div class="gatetouch-seo-preview-col">
                    <div class="gatetouch-sticky-preview">
                        <div class="gatetouch-preview-label">Google Search Preview</div>
                        <div class="gatetouch-google-card" style="width:100%; max-width:100%;">
                            <div class="gatetouch-google-header">
                                <div style="width:20px; height:20px; background:#f1f5f9; border-radius:4px; display:flex; align-items:center; justify-content:center; font-size:10px; color:#5f6368; font-weight:800;">G</div>
                                <div class="gatetouch-google-site">https://<?php echo esc_html($host); ?> › <span id="gatetouch-serp-slug" style="color:#5f6368;"><?php echo esc_html(str_replace(' ', '-', strtolower(get_the_title()))); ?></span></div>
                            </div>
                            <div id="gatetouch-serp-title" class="gatetouch-google-title" style="font-family: arial, sans-serif; font-size:20px;"><?php echo esc_html( ( $meta['meta_title'] ?? '' ) ?: get_the_title() ); ?></div>
                            <div id="gatetouch-serp-desc" class="gatetouch-google-desc" style="font-family: arial, sans-serif; font-size:14px;"><?php echo esc_html( ( $meta['meta_description'] ?? '' ) ?: 'Your meta description will appear here as it would in Google search results...' ); ?></div>
                        </div>

                        <div style="margin-top:24px; padding:16px; background:hsla(var(--riq-p), 0.05); border:1px solid hsla(var(--riq-p), 0.1); border-radius:12px;">
                            <div style="font-size:11px; font-weight:800; color:var(--riq-primary); text-transform:uppercase; margin-bottom:8px;">Live Audit Tip</div>
                            <p style="font-size:12px; color:var(--riq-text-mid); line-height:1.5; margin:0;">Ensure your <strong>Target Keyword</strong> is at the beginning of the SEO Title for maximum ranking impact in AI search.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- SOCIAL TAB -->
        <div class="gatetouch-tab-panel" id="gatetouch-tab-social">
            <div class="gatetouch-field">
                <label class="gatetouch-label"><span class="gatetouch-label__text">Facebook Title</span></label>
                <div class="gatetouch-tag-group">
                    <div class="gatetouch-tag-badge" data-tag="#title#" data-target="gatetouch_og_title">#title#</div>
                    <div class="gatetouch-tag-badge" data-tag="#sep#" data-target="gatetouch_og_title">#sep#</div>
                    <div class="gatetouch-tag-badge" data-tag="#site_title#" data-target="gatetouch_og_title">#site_title#</div>
                </div>
                <input type="text" id="gatetouch_og_title" name="gatetouch_og_title" value="<?php echo esc_attr($meta['og_title'] ?? ''); ?>" class="gatetouch-input" />
            </div>
            <div class="gatetouch-field">
                <label class="gatetouch-label"><span class="gatetouch-label__text">Facebook Description</span></label>
                <div class="gatetouch-tag-group">
                    <div class="gatetouch-tag-badge" data-tag="#excerpt#" data-target="gatetouch_og_description">#excerpt#</div>
                    <div class="gatetouch-tag-badge" data-tag="#sep#" data-target="gatetouch_og_description">#sep#</div>
                </div>
                <textarea id="gatetouch_og_description" name="gatetouch_og_description" rows="3" class="gatetouch-textarea"><?php echo esc_textarea($meta['og_description'] ?? ''); ?></textarea>
            </div>
            <div class="gatetouch-field">
                <label class="gatetouch-label"><span class="gatetouch-label__text">Image Source</span></label>
                <?php
                $custom_og_image   = $meta['og_image'] ?? '';
                $fallback_og_image = $analysis['fallback_og_image'] ?? '';
                $effective_og_image = ! empty( $custom_og_image ) ? $custom_og_image : $fallback_og_image;
                ?>
                <div style="display:flex; gap:10px;">
                    <input type="text" id="gatetouch_og_image" name="gatetouch_og_image" value="<?php echo esc_url( $effective_og_image ); ?>" data-fallback-image="<?php echo esc_url( $fallback_og_image ); ?>" data-using-fallback="<?php echo esc_attr( empty( $custom_og_image ) && ! empty( $fallback_og_image ) ? '1' : '0' ); ?>" class="gatetouch-input" placeholder="https://..." />
                    <input type="hidden" name="gatetouch_og_image_fallback" value="<?php echo esc_url( $fallback_og_image ); ?>" />
                    <button type="button" id="gatetouch-og-media-btn" class="gatetouch-btn gatetouch-btn--secondary gatetouch-btn--sm">Select</button>
                </div>
                <?php if ( empty( $custom_og_image ) && ! empty( $fallback_og_image ) ) : ?>
                    <p class="gatetouch-hint"><?php esc_html_e( 'Using the featured image automatically. Select a custom image to override it.', 'gatetouch-ai-seo' ); ?></p>
                <?php endif; ?>
            </div>
            <div class="gatetouch-field">
                <label class="gatetouch-label"><span class="gatetouch-label__text">Video URL</span></label>
                <input type="url" name="gatetouch_og_video" value="<?php echo esc_url($meta['og_video'] ?? ''); ?>" placeholder="https://youtube.com/..." class="gatetouch-input" />
            </div>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:20px;">
                <div class="gatetouch-field">
                    <label class="gatetouch-label"><span class="gatetouch-label__text">Object Type</span></label>
                    <select name="gatetouch_og_type" class="gatetouch-select">
                        <option value="article" <?php selected(($meta['og_type'] ?? 'article'), 'article'); ?>>Article</option>
                        <option value="website" <?php selected(($meta['og_type'] ?? ''), 'website'); ?>>Website</option>
                        <option value="product" <?php selected(($meta['og_type'] ?? ''), 'product'); ?>>Product</option>
                        <option value="video" <?php selected(($meta['og_type'] ?? ''), 'video'); ?>>Video</option>
                    </select>
                </div>
                <div class="gatetouch-field">
                    <label class="gatetouch-label"><span class="gatetouch-label__text">Article Section</span></label>
                    <input type="text" name="gatetouch_article_section" value="<?php echo esc_attr($meta['article_section'] ?? ''); ?>" placeholder="e.g. Technology" class="gatetouch-input" />
                </div>
            </div>
            <div class="gatetouch-field">
                <label class="gatetouch-label"><span class="gatetouch-label__text">Article Tags</span></label>
                <input type="text" name="gatetouch_article_tags" value="<?php echo esc_attr($meta['article_tags'] ?? ''); ?>" placeholder="tag1, tag2..." class="gatetouch-input" />
            </div>

            <div class="gatetouch-social-preview">
                <div class="gatetouch-preview-label">Facebook Share Preview</div>
                <div class="gatetouch-facebook-card">
                    <div class="gatetouch-facebook-img">
                        <?php $disp_img = $effective_og_image; ?>
                        <img id="gatetouch-fb-img-preview" src="<?php echo esc_url($disp_img); ?>" style="<?php echo esc_attr( empty( $disp_img ) ? 'display:none;' : '' ); ?>" />
                        <div id="gatetouch-fb-img-placeholder" style="<?php echo esc_attr( ! empty( $disp_img ) ? 'display:none;' : '' ); ?> display:flex; align-items:center; justify-content:center; height:100%; background:#f1f5f9; color:#94a3b8; font-size:12px;">No Image Selected</div>
                    </div>
                    <div class="gatetouch-facebook-content">
                        <div class="gatetouch-facebook-site"><?php echo esc_html(strtoupper($host)); ?></div>
                        <div id="gatetouch-fb-title" class="gatetouch-facebook-title"><?php echo esc_html($meta['og_title'] ?? ($meta['meta_title'] ?? get_the_title())); ?></div>
                        <div id="gatetouch-fb-desc" class="gatetouch-facebook-desc"><?php echo esc_html($meta['og_description'] ?? ($meta['meta_description'] ?? '')); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI CONTENT & SCHEMA TAB (UNIFIED) -->
        <div class="gatetouch-tab-panel" id="gatetouch-tab-schema">

            <!-- Sub-Tabs Navigation -->
            <div class="gatetouch-sub-tabs" style="display:flex; gap:10px; margin-bottom:24px; border-bottom:1px solid #f1f5f9; padding-bottom:12px;">
                <button type="button" class="gatetouch-sub-tab-btn active" data-subtab="schema-main">Schema & JSON</button>
                <button type="button" class="gatetouch-sub-tab-btn" data-subtab="schema-points">Key Points</button>
                <button type="button" class="gatetouch-sub-tab-btn" data-subtab="schema-faq">FAQs Engine</button>
                <button type="button" class="gatetouch-sub-tab-btn" data-subtab="schema-social">Social Posts</button>
            </div>

            <!-- Sub-Tab: Schema & JSON -->
            <div class="gatetouch-sub-tab-panel active" id="gatetouch-sub-tab-schema-main">
                 <div class="gatetouch-field">
                    <label class="gatetouch-label">
                        <span class="gatetouch-label__text">Primary Schema Type</span>
                        <div<?php if ( ! $has_key ) : ?> class="gatetouch-locked" data-riq-tooltip="<?php esc_attr_e( 'AI API Required', 'gatetouch-ai-seo' ); ?>"<?php endif; ?>>
                            <button type="button" id="gatetouch-btn-smart-schema" class="gatetouch-btn gatetouch-btn--ai gatetouch-btn--xs" <?php disabled( ! $has_key ); ?>>Smart Auto-Detect</button>
                        </div>
                    </label>
                    <select id="gatetouch_schema_type" name="gatetouch_schema_type" class="gatetouch-select">
                        <option value="Article" <?php selected(($meta['schema_type'] ?? ''), 'Article'); ?>>Article</option>
                        <option value="Product" <?php selected(($meta['schema_type'] ?? ''), 'Product'); ?>>Product</option>
                        <option value="FAQPage" <?php selected(($meta['schema_type'] ?? ''), 'FAQPage'); ?>>FAQ Page</option>
                        <option value="None" <?php selected(($meta['schema_type'] ?? ''), 'None'); ?>>None</option>
                    </select>
                </div>

                <div class="gatetouch-field">
                    <label class="gatetouch-label"><span class="gatetouch-label__text">Custom Schema JSON-LD</span></label>
                    <textarea id="gatetouch_custom_schema" name="gatetouch_custom_schema" rows="8" class="gatetouch-textarea" style="font-family: monospace; font-size: 12px;"><?php echo esc_textarea($meta['custom_schema'] ?? ''); ?></textarea>
                    <p class="gatetouch-hint">AI-generated or manual JSON-LD. This will be output in the page head.</p>
                </div>
            </div>

            <!-- Sub-Tab: Key Points -->
            <div class="gatetouch-sub-tab-panel" id="gatetouch-sub-tab-schema-points">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                    <div>
                        <strong style="font-size:14px; color:var(--riq-text);">AI Key Takeaways</strong>
                        <p style="font-size:12px; color:var(--riq-text-mid); margin:4px 0 0 0;">Summarized content for better user engagement.</p>
                    </div>
                    <div<?php if ( ! $has_key ) : ?> class="gatetouch-locked" data-riq-tooltip="<?php esc_attr_e( 'AI API Required', 'gatetouch-ai-seo' ); ?>"<?php endif; ?>>
                        <button type="button" id="gatetouch-btn-ai-points" class="gatetouch-btn gatetouch-btn--ai gatetouch-btn--sm" <?php disabled( ! $has_key ); ?>>
                            <?php echo esc_html( ! empty( $meta['key_points'] ) ? 'Regenerate Points' : 'Generate Key Points' ); ?>
                        </button>
                    </div>
                </div>
                <div class="gatetouch-field">
                    <textarea id="gatetouch_key_points" name="gatetouch_key_points" rows="6" class="gatetouch-textarea" placeholder="AI will extract key points here..."><?php echo esc_textarea($meta['key_points'] ?? ''); ?></textarea>
                    <p class="gatetouch-hint">You can manually edit these points. They are saved with your post content.</p>
                </div>
            </div>

            <!-- Sub-Tab: FAQs -->
            <div class="gatetouch-sub-tab-panel" id="gatetouch-sub-tab-schema-faq">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                    <div>
                        <strong style="font-size:14px; color:var(--riq-text);">Structured-Data FAQs</strong>
                        <p style="font-size:12px; color:var(--riq-text-mid); margin:4px 0 0 0;">Questions and answers extracted from your content.</p>
                    </div>
                    <div<?php if ( ! $has_key ) : ?> class="gatetouch-locked" data-riq-tooltip="<?php esc_attr_e( 'AI API Required', 'gatetouch-ai-seo' ); ?>"<?php endif; ?>>
                        <button type="button" id="gatetouch-btn-faq" class="gatetouch-btn gatetouch-btn--ai gatetouch-btn--sm" <?php disabled( ! $has_key ); ?>>
                             <?php echo esc_html( ! empty( $meta['faqs'] ) ? 'Regenerate FAQs' : 'Extract FAQs' ); ?>
                        </button>
                    </div>
                </div>
                <div id="gatetouch-faq-list">
                    <?php if ( ! empty( $meta['faqs'] ) ) : foreach ( $meta['faqs'] as $faq ) : ?>
                    <div class="gatetouch-faq-item">
                        <span class="gatetouch-faq-remove">✕</span>
                        <input type="text" name="gatetouch_faq_q[]" value="<?php echo esc_attr($faq['question']); ?>" class="gatetouch-input" style="margin-bottom:8px; font-weight:600;" />
                        <textarea name="gatetouch_faq_a[]" rows="2" class="gatetouch-textarea"><?php echo esc_textarea($faq['answer']); ?></textarea>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
                <button type="button" id="gatetouch-faq-add" class="gatetouch-btn gatetouch-btn--ghost gatetouch-btn--sm" style="width:100%;">+ Add Manual FAQ</button>
            </div>

            <!-- Sub-Tab: Social Posts -->
            <div class="gatetouch-sub-tab-panel" id="gatetouch-sub-tab-schema-social">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                    <div>
                        <strong style="font-size:14px; color:var(--riq-text);">AI Social Media Kit</strong>
                        <p style="font-size:12px; color:var(--riq-text-mid); margin:4px 0 0 0;">Ready-to-use posts for LinkedIn, Facebook, and X.</p>
                    </div>
                    <div<?php if ( ! $has_key ) : ?> class="gatetouch-locked" data-riq-tooltip="<?php esc_attr_e( 'AI API Required', 'gatetouch-ai-seo' ); ?>"<?php endif; ?>>
                        <button type="button" id="gatetouch-btn-ai-social" class="gatetouch-btn gatetouch-btn--ai gatetouch-btn--sm" <?php disabled( ! $has_key ); ?>>
                            <?php echo esc_html( ! empty( $meta['social_posts'] ) ? 'Regenerate Kit' : 'Generate Social Posts' ); ?>
                        </button>
                    </div>
                </div>
                <div class="gatetouch-field">
                    <textarea id="gatetouch_social_posts" name="gatetouch_social_posts" rows="10" class="gatetouch-textarea" style="font-size:13px; line-height:1.6;" placeholder="Social posts will be generated here..."><?php echo esc_textarea($meta['social_posts'] ?? ''); ?></textarea>
                    <p class="gatetouch-hint">Full kit containing formatted posts for multiple platforms.</p>
                </div>
            </div>
        </div>

        <!-- ANALYSIS TAB -->
        <div class="gatetouch-tab-panel" id="gatetouch-tab-analysis">
            <!-- Sub-Tab Navigation for Analysis -->
            <div class="gatetouch-sub-tabs" style="margin-bottom:20px; border-bottom:1px solid #f1f5f9; padding-bottom:10px; display:flex; gap:15px;">
                <button type="button" class="gatetouch-sub-tab-btn active" data-subtab="analysis-seo">SEO Audit</button>
                <button type="button" class="gatetouch-sub-tab-btn" data-subtab="analysis-ai">AI & AEO Audit</button>
            </div>

            <div id="gatetouch-analysis-wrap">

                <!-- SUB-PANEL: SEO AUDIT -->
                <div class="gatetouch-sub-tab-panel active" id="gatetouch-sub-tab-analysis-seo">
                    <!-- SEO AUDIT CARD -->
                    <div class="gatetouch-ai-insights-card" style="margin-bottom:30px; border: 2px solid var(--riq-primary); border-radius:12px; overflow:hidden;">
                        <div class="gatetouch-ai-insights-head" style="background:var(--riq-primary); color:#fff; padding:15px 20px; display:flex; justify-content:space-between; align-items:center;">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <strong style="font-size:16px; letter-spacing:0.02em;">TRADITIONAL SEO PERFORMANCE AUDIT</strong>
                            </div>
                        </div>

                        <div class="gatetouch-ai-insights-body" style="padding:25px; background:#fff;">
                            <?php if ( ! empty( $analysis['checks'] ) ) : ?>
                                <div class="gatetouch-analysis-grid">
                                    <?php
                                    $cats = [
                                        'basic'       => [ 'label' => 'Basic SEO', 'icon' => '⚙️' ],
                                        'content'     => [ 'label' => 'Content & Cornerstone', 'icon' => '📝' ],
                                        'keywords'    => [ 'label' => 'Secondary Keywords', 'icon' => '🔑' ],
                                        'readability' => [ 'label' => 'Readability', 'icon' => '👁️' ],
                                    ];
                                    foreach ( $cats as $key => $cat ) :
                                        $cat_checks = array_filter( $analysis['checks'], function($c) use ($key) { return ( $c['category'] ?? '' ) === $key; } );
                                        if ( empty( $cat_checks ) ) continue;
                                    ?>
                                        <div class="gatetouch-analysis-section">
                                            <div class="gatetouch-analysis-section-head">
                                                <span><?php echo esc_html($cat['icon']); ?> <?php echo esc_html($cat['label']); ?></span>
                                            </div>
                                            <div class="gatetouch-analysis-checklist">
                                                <?php foreach ( $cat_checks as $check ) : ?>
                                                    <div class="gatetouch-check-item gatetouch-check--<?php echo esc_attr($check['status']); ?>" data-key="<?php echo esc_attr($check['key'] ?? ''); ?>">
                                                        <span class="gatetouch-check-icon"></span>
                                                        <span class="gatetouch-check-text"><?php echo esc_html($check['message']); ?></span>
                                                        <?php if ( ! empty( $check['key'] ) ) : ?>
                                                            <span class="gatetouch-check-help" title="View Expert Guidance">?</span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else : ?>
                                <div style="text-align:center; padding:40px;">
                                    <p style="color:var(--riq-text-mid);">Run the AI Analysis to see traditional SEO improvements.</p>
                                    <button type="button" class="gatetouch-btn gatetouch-btn--primary gatetouch-manual-analyze">Analyze SEO Now</button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- SUB-PANEL: AI & AEO AUDIT -->
                <div class="gatetouch-sub-tab-panel" id="gatetouch-sub-tab-analysis-ai">
                    <!-- AI SEARCH INSIGHTS -->
                    <div class="gatetouch-ai-insights-card <?php echo esc_attr( empty( $meta['search_intent'] ) ? 'is-empty' : '' ); ?>" style="margin-bottom:30px; border: 2px solid #6366f1; border-radius:12px; overflow:hidden;">
                        <div class="gatetouch-ai-insights-head" style="background:#6366f1; color:#fff; padding:15px 20px; display:flex; justify-content:space-between; align-items:center;">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                                </svg>
                                <strong style="font-size:16px; letter-spacing:0.02em;">AI SEARCH STRATEGY (AEO, GEO & LLMO)</strong>
                            </div>
                            <?php if ( isset( $analysis['ai_score'] ) ) : ?>
                                <div class="gatetouch-intent-badge" id="gatetouch-ai-card-score-badge" style="background:#fff; color:#6366f1; font-weight:800; padding:4px 12px; border-radius:20px; font-size:12px;">
                                    AI Health: <?php echo esc_html( (int) $analysis['ai_score'] ); ?>%
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="gatetouch-ai-insights-body" style="padding:25px; background:#fff;">

                            <!-- AI Checklist (Cognitive SEO) -->
                            <div id="gatetouch-ai-analysis-grid">
                                <?php
                                    $cog_checks = array_filter( $analysis['checks'] ?? [], function($c) { return ( $c['category'] ?? '' ) === 'cognitive'; } );
                                    if ( ! empty( $cog_checks ) ) :
                                ?>
                                    <div class="gatetouch-analysis-grid" style="margin-bottom:25px; border-bottom:1px solid #f1f5f9; padding-bottom:20px;">
                                        <div class="gatetouch-analysis-section" style="width:100%; box-shadow:none; border:none; padding:0;">
                                            <div class="gatetouch-analysis-checklist">
                                                <?php foreach ( $cog_checks as $check ) : ?>
                                                    <div class="gatetouch-check-item gatetouch-check--<?php echo esc_attr($check['status']); ?>" data-key="<?php echo esc_attr($check['key'] ?? ''); ?>">
                                                        <span class="gatetouch-check-icon"></span>
                                                        <span class="gatetouch-check-text"><?php echo esc_html($check['message']); ?></span>
                                                        <?php if ( ! empty( $check['key'] ) ) : ?>
                                                            <span class="gatetouch-check-help" title="View Expert Guidance">?</span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if ( ! empty( $meta['search_intent'] ) ) : ?>
                                <div style="margin-bottom:25px; border-bottom:1px solid #f1f5f9; padding-bottom:20px;">
                                    <div style="font-size:12px; text-transform:uppercase; font-weight:800; color:#6366f1; margin-bottom:10px; letter-spacing:0.05em;">AI Search Intent Detection:</div>
                                    <div style="display:flex; align-items:center; gap:12px;">
                                        <span class="gatetouch-intent-tag" style="background:#eef2ff; color:#6366f1; padding:6px 16px; border-radius:30px; font-weight:700; font-size:13px;"><?php echo esc_html($meta['search_intent']); ?> Intent</span>
                                        <span style="font-size:15px; font-weight:500; color:#1e293b; line-height:1.4;"><?php echo esc_html($meta['intent_explanation'] ?? ''); ?></span>
                                    </div>
                                </div>
                                <?php if ( ! empty( $meta['missing_topics'] ) ) : ?>
                                    <div style="margin-bottom:30px;">
                                        <div style="font-size:12px; text-transform:uppercase; font-weight:800; color:#ef4444; margin-bottom:10px; letter-spacing:0.05em;">Semantic Gaps (AEO Requirements):</div>
                                        <p style="font-size:14px; color:#475569; margin-bottom:15px; line-height:1.5;">To rank in AI-driven search (SGE/Gemini), you MUST cover these topics:</p>
                                        <div class="gatetouch-topic-tags" style="display:flex; flex-wrap:wrap; gap:8px;">
                                            <?php foreach ( (array) $meta['missing_topics'] as $topic ) : ?>
                                                <span class="gatetouch-topic-tag" style="background:#fef2f2; color:#ef4444; border:1px solid #fee2e2; padding:8px 14px; border-radius:8px; font-weight:700; font-size:13px;"><?php echo esc_html($topic); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                            <?php else : ?>
                                <div style="text-align:center; padding:30px 0;">
                                    <div style="color:#6366f1; margin-bottom:15px;"><?php echo wp_kses( GateTouch_Helpers::icon( 'robot', 40 ), GateTouch_Helpers::svg_kses_allowed() ); ?></div>
                                    <p style="font-size:16px; font-weight:700; color:#1e293b; margin-bottom:8px;">Run AI Search Optimization (AEO)</p>
                                    <p style="font-size:14px; color:#64748b; margin-bottom:25px; max-width:400px; margin-left:auto; margin-right:auto;">Our AI will tell you exactly how to optimize your content for Google SGE, Gemini, and ChatGPT.</p>
                                    <button type="button" class="gatetouch-btn gatetouch-btn--primary gatetouch-manual-analyze" style="padding:12px 30px; font-weight:700;">Run AEO Analysis</button>
                                </div>
                            <?php endif; ?>

                            <div style="margin-top:40px; border-top:1px solid #f1f5f9; padding-top:30px;">
                                <div style="font-size:12px; text-transform:uppercase; font-weight:800; color:var(--riq-text-light); margin-bottom:20px; letter-spacing:0.05em;">Complete Optimization Checklist:</div>
                                <?php if ( ! empty( $analysis['checks'] ) ) : ?>
                                    <div class="gatetouch-analysis-grid" id="gatetouch-ai-tech-checklist">
                                        <?php
                                        $cats = [
                                            'basic'       => [ 'label' => 'Basic SEO', 'icon' => '⚙️' ],
                                            'content'     => [ 'label' => 'Content & Cornerstone', 'icon' => '📝' ],
                                            'readability' => [ 'label' => 'Readability', 'icon' => '👁️' ],
                                        ];
                                        foreach ( $cats as $key => $cat ) :
                                            $cat_checks = array_filter( $analysis['checks'], function($c) use ($key) { return ( $c['category'] ?? '' ) === $key; } );
                                            if ( empty( $cat_checks ) ) continue;
                                        ?>
                                            <div class="gatetouch-analysis-section">
                                                <div class="gatetouch-analysis-section-head">
                                                    <span><?php echo esc_html($cat['icon']); ?> <?php echo esc_html($cat['label']); ?></span>
                                                </div>
                                                <div class="gatetouch-analysis-checklist">
                                                    <?php foreach ( $cat_checks as $check ) : ?>
                                                        <div class="gatetouch-check-item gatetouch-check--<?php echo esc_attr($check['status']); ?>" data-key="<?php echo esc_attr($check['key'] ?? ''); ?>">
                                                            <span class="gatetouch-check-icon"></span>
                                                            <span class="gatetouch-check-text"><?php echo esc_html($check['message']); ?></span>
                                                            <?php if ( ! empty( $check['key'] ) ) : ?>
                                                                <span class="gatetouch-check-help" title="View Expert Guidance">?</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- End #gatetouch-analysis-wrap -->
        </div> <!-- End #gatetouch-tab-analysis -->


        <!-- ADVANCED TAB -->
        <div class="gatetouch-tab-panel" id="gatetouch-tab-advanced">
            <div class="gatetouch-field">
                <label class="gatetouch-label"><span class="gatetouch-label__text">Canonical URL</span></label>
                <input type="url" name="gatetouch_canonical" value="<?php echo esc_url($meta['canonical'] ?? ''); ?>" placeholder="<?php echo esc_url(get_permalink()); ?>" class="gatetouch-input" />
                <p class="gatetouch-hint">Override the default canonical URL for this post.</p>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:24px;">
                <div class="gatetouch-field">
                    <label class="gatetouch-label"><span class="gatetouch-label__text">Sitemap Priority</span></label>
                    <select name="gatetouch_priority" class="gatetouch-select">
                        <option value="default" <?php selected(($meta['priority'] ?? 'default'), 'default'); ?>>Default</option>
                        <option value="1.0" <?php selected(($meta['priority'] ?? ''), '1.0'); ?>>1.0 (High)</option>
                        <option value="0.8" <?php selected(($meta['priority'] ?? ''), '0.8'); ?>>0.8</option>
                        <option value="0.5" <?php selected(($meta['priority'] ?? ''), '0.5'); ?>>0.5 (Low)</option>
                    </select>
                </div>
                <div class="gatetouch-field">
                    <label class="gatetouch-label"><span class="gatetouch-label__text">Frequency</span></label>
                    <select name="gatetouch_frequency" class="gatetouch-select">
                        <option value="default" <?php selected(($meta['frequency'] ?? 'default'), 'default'); ?>>Default</option>
                        <option value="always" <?php selected(($meta['frequency'] ?? ''), 'always'); ?>>Always</option>
                        <option value="hourly" <?php selected(($meta['frequency'] ?? ''), 'hourly'); ?>>Hourly</option>
                        <option value="daily" <?php selected(($meta['frequency'] ?? ''), 'daily'); ?>>Daily</option>
                        <option value="weekly" <?php selected(($meta['frequency'] ?? ''), 'weekly'); ?>>Weekly</option>
                    </select>
                </div>
            </div>

            <!-- LINK ASSISTANT -->
            <div style="margin-top:10px;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                    <strong style="font-size:14px; color:var(--riq-text);">AI Link Assistant</strong>
                    <div<?php if ( ! $has_key ) : ?> class="gatetouch-locked" data-riq-tooltip="<?php esc_attr_e( 'AI API Required', 'gatetouch-ai-seo' ); ?>"<?php endif; ?>>
                        <button type="button" id="gatetouch-btn-fetch-links" class="gatetouch-btn gatetouch-btn--ai gatetouch-btn--sm" <?php disabled( ! $has_key ); ?>>Find Suggestions</button>
                    </div>
                </div>
                <div id="gatetouch-link-suggestions" class="gatetouch-link-list">
                    <p style="font-size:13px; color:var(--riq-text-mid); text-align:center; padding:15px; background:var(--riq-bg-faint); border-radius:10px;">Click the button above to discover semantic linking opportunities.</p>
                </div>
            </div>

            <div class="gatetouch-field" style="margin-top:30px;">
                <label class="gatetouch-label"><span class="gatetouch-label__text">Search Engine Visibility</span></label>
                <div style="display:flex; flex-direction:column; gap:12px; background:#f8fafc; padding:15px; border-radius:12px; border:1px solid var(--riq-border);">
                    <label style="display:flex; align-items:center; gap:12px; cursor:pointer;">
                        <input type="checkbox" name="gatetouch_noindex" value="1" <?php checked(!empty($meta['noindex'])); ?> />
                        <span style="font-size:13px; color:var(--riq-text);">No-Index (Hide from Search Engines)</span>
                    </label>
                    <label style="display:flex; align-items:center; gap:12px; cursor:pointer;">
                        <input type="checkbox" name="gatetouch_nofollow" value="1" <?php checked(!empty($meta['nofollow'])); ?> />
                        <span style="font-size:13px; color:var(--riq-text);">No-Follow (Don't follow links)</span>
                    </label>
                    <label style="display:flex; align-items:center; gap:12px; cursor:pointer; border-top:1px solid #f1f5f9; padding-top:10px; margin-top:5px;">
                        <input type="checkbox" name="gatetouch_breadcrumbs_enabled" value="1" <?php checked(!empty($meta['breadcrumbs_enabled']) || !isset($meta['breadcrumbs_enabled'])); ?> />
                        <span style="font-size:13px; color:var(--riq-text);">Allow Breadcrumbs for this post</span>
                    </label>
                    <span style="font-size:12px; color:var(--riq-text-light);">Global breadcrumb display and shortcode: <code>[gatetouch_breadcrumbs]</code></span>
                </div>
            </div>
        </div>

    </div>
</div>
