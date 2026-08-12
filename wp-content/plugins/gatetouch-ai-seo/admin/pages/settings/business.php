<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files are included inside class methods, so render variables are local.

if ( isset( $_POST['gatetouch_save_business'] ) && check_admin_referer( 'gatetouch_save_business' ) ) {
    $twitter_site = isset( $_POST['twitter_site'] ) ? ltrim( sanitize_text_field( wp_unslash( $_POST['twitter_site'] ) ), '@' ) : '';

    update_option( 'gatetouch_twitter_site', $twitter_site );
    
    $social = [
        'facebook_enabled' => isset( $_POST['fb_enabled'] ) ? '1' : '0',
        'twitter_enabled'  => isset( $_POST['tw_enabled'] ) ? '1' : '0',
        'twitter_card'     => sanitize_text_field( wp_unslash( $_POST['tw_card'] ?? 'summary_large_image' ) ),
    ];
    update_option( 'gatetouch_social_settings', $social );

    // Local business / LocalBusiness schema. Written under the same option name
    // GateTouch_Local_SEO::get_settings() reads, so the panel and the engine
    // finally agree — previously the only editor wrote a different option and
    // its data was never rendered.
    if ( isset( $_POST['local_submitted'] ) ) {
        $local = GateTouch_Local_SEO::get_settings();
        $local = is_array( $local ) ? $local : [];

        $local['enabled']       = isset( $_POST['local_enabled'] ) ? 'yes' : 'no';
        $local['business_type'] = sanitize_text_field( wp_unslash( $_POST['local_business_type'] ?? 'LocalBusiness' ) );
        $local['business_name'] = sanitize_text_field( wp_unslash( $_POST['local_business_name'] ?? '' ) );
        $local['address']       = sanitize_text_field( wp_unslash( $_POST['local_address'] ?? '' ) );
        $local['city']          = sanitize_text_field( wp_unslash( $_POST['local_city'] ?? '' ) );
        $local['state']         = sanitize_text_field( wp_unslash( $_POST['local_state'] ?? '' ) );
        $local['zip']           = sanitize_text_field( wp_unslash( $_POST['local_zip'] ?? '' ) );
        $local['country']       = sanitize_text_field( wp_unslash( $_POST['local_country'] ?? '' ) );
        $local['phone']         = sanitize_text_field( wp_unslash( $_POST['local_phone'] ?? '' ) );
        $local['email']         = sanitize_email( wp_unslash( $_POST['local_email'] ?? '' ) );
        $local['opening_hours'] = sanitize_textarea_field( wp_unslash( $_POST['local_hours'] ?? '' ) );
        $local['price_range']   = sanitize_text_field( wp_unslash( $_POST['local_price_range'] ?? '' ) );

        update_option( 'gatetouch_local_seo_settings', $local );
    }

    GateTouch_Helpers::notice( '✅ Business & Social settings saved!', 'success' );
}

$tw_site = get_option( 'gatetouch_twitter_site', '' );
$social  = get_option( 'gatetouch_social_settings', [] );
$local   = GateTouch_Local_SEO::get_settings();
$local   = is_array( $local ) ? $local : [];
?>
<div class="gatetouch-settings-group">
    <form method="post">
        <?php wp_nonce_field( 'gatetouch_save_business' ); ?>

        <div class="gatetouch-card">
            <div class="gatetouch-card__header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                Social Graph & Identity
            </div>
            <div class="gatetouch-settings-rows">
                <div class="gatetouch-setting-row">
                    <div class="gatetouch-setting-label">
                        <strong><?php esc_html_e( 'Twitter/X Site Handle', 'gatetouch-ai-seo' ); ?></strong>
                        <p><?php esc_html_e( 'Your @username for Twitter Card meta tags.', 'gatetouch-ai-seo' ); ?></p>
                    </div>
                    <div class="gatetouch-setting-control">
                        <input type="text" name="twitter_site" value="<?php echo esc_attr( $tw_site ); ?>" placeholder="yourhandle (no @)" class="gatetouch-input-full" />
                    </div>
                </div>
                <div class="gatetouch-setting-row">
                    <div class="gatetouch-setting-label">
                        <strong><?php esc_html_e( 'Twitter Card Type', 'gatetouch-ai-seo' ); ?></strong>
                    </div>
                    <div class="gatetouch-setting-control">
                        <select name="tw_card" class="gatetouch-select">
                            <option value="summary" <?php selected($social['twitter_card'] ?? '', 'summary'); ?>>Summary</option>
                            <option value="summary_large_image" <?php selected($social['twitter_card'] ?? '', 'summary_large_image'); ?>>Summary with Large Image</option>
                        </select>
                    </div>
                </div>
                <div class="gatetouch-setting-row">
                    <div class="gatetouch-setting-label">
                        <strong><?php esc_html_e( 'OpenGraph Tags', 'gatetouch-ai-seo' ); ?></strong>
                        <p><?php esc_html_e( 'Enable Facebook/OpenGraph metadata.', 'gatetouch-ai-seo' ); ?></p>
                    </div>
                    <div class="gatetouch-setting-control">
                        <?php GateTouch_Helpers::toggle( 'fb_enabled', ( $social['facebook_enabled'] ?? '1' ) === '1' ? 'yes' : 'no' ); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="gatetouch-card">
            <div class="gatetouch-card__header">
                <?php echo wp_kses( GateTouch_Helpers::icon( 'map', 20 ), GateTouch_Helpers::svg_kses_allowed() ); ?>
                <?php esc_html_e( 'Local Business', 'gatetouch-ai-seo' ); ?>
            </div>
            <div class="gatetouch-card__body">
                <input type="hidden" name="local_submitted" value="1" />

                <div class="gatetouch-setting-row">
                    <div class="gatetouch-setting-label">
                        <strong><?php esc_html_e( 'Enable LocalBusiness schema', 'gatetouch-ai-seo' ); ?></strong>
                        <p><?php esc_html_e( 'Publishes your address, hours and contact details as structured data. Required for map packs and for AI assistants to answer "where is" and "when are they open" questions about you.', 'gatetouch-ai-seo' ); ?></p>
                    </div>
                    <div class="gatetouch-setting-control">
                        <?php GateTouch_Helpers::toggle( 'local_enabled', ( $local['enabled'] ?? 'no' ) === 'yes' ? 'yes' : 'no' ); ?>
                    </div>
                </div>

                <div class="gatetouch-sa-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:18px;">
                    <div>
                        <label><strong><?php esc_html_e( 'Business type', 'gatetouch-ai-seo' ); ?></strong></label>
                        <select name="local_business_type" class="gatetouch-input-full">
                            <?php
                            $gt_types = [
                                'LocalBusiness'      => __( 'General local business', 'gatetouch-ai-seo' ),
                                'Restaurant'         => __( 'Restaurant / café', 'gatetouch-ai-seo' ),
                                'Store'              => __( 'Retail store', 'gatetouch-ai-seo' ),
                                'ProfessionalService'=> __( 'Professional service', 'gatetouch-ai-seo' ),
                                'MedicalBusiness'    => __( 'Medical / healthcare', 'gatetouch-ai-seo' ),
                                'LegalService'       => __( 'Legal service', 'gatetouch-ai-seo' ),
                                'HomeAndConstructionBusiness' => __( 'Home & construction', 'gatetouch-ai-seo' ),
                                'AutomotiveBusiness' => __( 'Automotive', 'gatetouch-ai-seo' ),
                                'RealEstateAgent'    => __( 'Real estate', 'gatetouch-ai-seo' ),
                                'LodgingBusiness'    => __( 'Hotel / lodging', 'gatetouch-ai-seo' ),
                            ];
                            foreach ( $gt_types as $gt_val => $gt_label ) :
                                ?>
                                <option value="<?php echo esc_attr( $gt_val ); ?>" <?php selected( $local['business_type'] ?? 'LocalBusiness', $gt_val ); ?>><?php echo esc_html( $gt_label ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label><strong><?php esc_html_e( 'Business name', 'gatetouch-ai-seo' ); ?></strong></label>
                        <input type="text" name="local_business_name" class="gatetouch-input-full" value="<?php echo esc_attr( $local['business_name'] ?? get_bloginfo( 'name' ) ); ?>" />
                    </div>
                </div>

                <div style="margin-top:16px;">
                    <label><strong><?php esc_html_e( 'Street address', 'gatetouch-ai-seo' ); ?></strong></label>
                    <input type="text" name="local_address" class="gatetouch-input-full" value="<?php echo esc_attr( $local['address'] ?? '' ); ?>" />
                </div>

                <div style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr; gap:16px; margin-top:16px;">
                    <div>
                        <label><strong><?php esc_html_e( 'City', 'gatetouch-ai-seo' ); ?></strong></label>
                        <input type="text" name="local_city" class="gatetouch-input-full" value="<?php echo esc_attr( $local['city'] ?? '' ); ?>" />
                    </div>
                    <div>
                        <label><strong><?php esc_html_e( 'State', 'gatetouch-ai-seo' ); ?></strong></label>
                        <input type="text" name="local_state" class="gatetouch-input-full" value="<?php echo esc_attr( $local['state'] ?? '' ); ?>" />
                    </div>
                    <div>
                        <label><strong><?php esc_html_e( 'Postcode', 'gatetouch-ai-seo' ); ?></strong></label>
                        <input type="text" name="local_zip" class="gatetouch-input-full" value="<?php echo esc_attr( $local['zip'] ?? '' ); ?>" />
                    </div>
                    <div>
                        <label><strong><?php esc_html_e( 'Country', 'gatetouch-ai-seo' ); ?></strong></label>
                        <input type="text" name="local_country" class="gatetouch-input-full" value="<?php echo esc_attr( $local['country'] ?? '' ); ?>" />
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-top:16px;">
                    <div>
                        <label><strong><?php esc_html_e( 'Phone', 'gatetouch-ai-seo' ); ?></strong></label>
                        <input type="text" name="local_phone" class="gatetouch-input-full" value="<?php echo esc_attr( $local['phone'] ?? '' ); ?>" />
                    </div>
                    <div>
                        <label><strong><?php esc_html_e( 'Email', 'gatetouch-ai-seo' ); ?></strong></label>
                        <input type="email" name="local_email" class="gatetouch-input-full" value="<?php echo esc_attr( $local['email'] ?? '' ); ?>" />
                    </div>
                    <div>
                        <label><strong><?php esc_html_e( 'Price range', 'gatetouch-ai-seo' ); ?></strong></label>
                        <input type="text" name="local_price_range" class="gatetouch-input-full" placeholder="$$" value="<?php echo esc_attr( $local['price_range'] ?? '' ); ?>" />
                    </div>
                </div>

                <div style="margin-top:16px;">
                    <label><strong><?php esc_html_e( 'Opening hours', 'gatetouch-ai-seo' ); ?></strong></label>
                    <textarea name="local_hours" class="gatetouch-input-full" rows="3" placeholder="Mo-Fr 09:00-17:00&#10;Sa 10:00-14:00"><?php echo esc_textarea( $local['opening_hours'] ?? '' ); ?></textarea>
                    <p class="gatetouch-sa-hint"><?php esc_html_e( 'One rule per line in schema.org format, e.g. "Mo-Fr 09:00-17:00".', 'gatetouch-ai-seo' ); ?></p>
                </div>
            </div>
        </div>

        <div class="gatetouch-form-footer">
            <input type="hidden" name="gatetouch_save_business" value="1" />
            <button type="submit" class="gatetouch-btn gatetouch-btn--primary"><?php esc_html_e( 'Save Business Settings', 'gatetouch-ai-seo' ); ?></button>
        </div>
    </form>
</div>
