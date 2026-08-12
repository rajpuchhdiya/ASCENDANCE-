<?php
defined( 'ABSPATH' ) || exit;

/**
 * GateTouch Local SEO Module
 * 
 * Handles Local Business schema and location information.
 */
class GateTouch_Local_SEO {

    public function __construct() {
        // Output Local Business Schema in head
        add_action( 'wp_head', [ $this, 'output_local_schema' ], 20 );

        // Shortcode to display location info
        add_shortcode( 'gatetouch_location', [ $this, 'render_location_shortcode' ] );

        // AJAX AI Generator
        if ( is_admin() ) {
            add_action( 'wp_ajax_gatetouch_generate_local_desc', [ $this, 'ajax_generate_desc' ] );
        }
    }

    /**
     * Get Local SEO settings
     */
    public static function get_settings() {
        return get_option( 'gatetouch_local_seo_settings', [
            'enabled'      => 'no',
            'business_type'=> 'LocalBusiness',
            'business_name'=> get_bloginfo('name'),
            'address'      => '',
            'city'         => '',
            'state'        => '',
            'zip'          => '',
            'country'      => '',
            'phone'        => '',
            'email'        => '',
            'url'          => home_url('/'),
            'opening_hours'=> '',
            'price_range'  => '$$',
        ] );
    }

    /**
     * Output Local Business JSON-LD
     */
    public function output_local_schema() {
        if ( class_exists( 'GateTouch_Core' ) && GateTouch_Core::has_conflicts() ) {
            return;
        }

        $opts = self::get_settings();
        if ( ( $opts['enabled'] ?? 'no' ) !== 'yes' ) return;

        // The schema engine folds these settings into the single Organization
        // entity in its @graph. Emitting a second standalone LocalBusiness node
        // would give the site two competing business entities.
        if ( class_exists( 'GateTouch_Schema_Engine' ) && GateTouch_Schema_Engine::is_enabled() ) {
            return;
        }


        $schema = [
            '@context' => 'https://schema.org',
            '@type'    => esc_attr( $opts['business_type'] ?? 'LocalBusiness' ),
            'name'     => esc_html( $opts['business_name'] ),
            'url'      => esc_url( $opts['url'] ),
            'telephone'=> esc_html( $opts['phone'] ),
            'email'    => esc_html( $opts['email'] ),
            'priceRange' => esc_html( $opts['price_range'] ),
            'address'  => [
                '@type'           => 'PostalAddress',
                'streetAddress'   => esc_html( $opts['address'] ),
                'addressLocality' => esc_html( $opts['city'] ),
                'addressRegion'   => esc_html( $opts['state'] ),
                'postalCode'      => esc_html( $opts['zip'] ),
                'addressCountry'  => esc_html( $opts['country'] ),
            ]
        ];

        if ( ! empty( $opts['opening_hours'] ) ) {
            $schema['openingHours'] = esc_html( $opts['opening_hours'] );
        }

        echo "\n";
        GateTouch_Helpers::print_json_ld( $schema, 'gatetouch-schema-local' );
    }

    /**
     * Render the [gatetouch_location] shortcode
     */
    public function render_location_shortcode( $atts ) {
        $opts = self::get_settings();
        if ( ( $opts['enabled'] ?? 'no' ) !== 'yes' ) return '';

        ob_start();
        ?>
        <div class="gatetouch-location-info">
            <h3 class="gatetouch-location-name"><?php echo esc_html( $opts['business_name'] ); ?></h3>
            <address class="gatetouch-location-address">
                <?php echo esc_html( $opts['address'] ); ?><br>
                <?php echo esc_html( $opts['city'] ); ?>, <?php echo esc_html( $opts['state'] ); ?> <?php echo esc_html( $opts['zip'] ); ?><br>
                <?php echo esc_html( $opts['country'] ); ?>
            </address>
            <?php if ( ! empty( $opts['phone'] ) ) : ?>
                <p class="gatetouch-location-phone"><strong>Phone:</strong> <a href="tel:<?php echo esc_attr( $opts['phone'] ); ?>"><?php echo esc_html( $opts['phone'] ); ?></a></p>
            <?php endif; ?>
            <?php if ( ! empty( $opts['opening_hours'] ) ) : ?>
                <p class="gatetouch-location-hours"><strong>Hours:</strong> <?php echo esc_html( $opts['opening_hours'] ); ?></p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    /**
     * AI Local Description Generator
     */
    public function ajax_generate_desc() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );

        $opts = self::get_settings();
        $name = $opts['business_name'];
        $type = $opts['business_type'];
        $city = $opts['city'];

        $system = 'You are a local SEO copywriter. Generate a keyword-rich business description for a Local Business. Respond ONLY with the text.';
        $user   = "Create a 50-word description for:
Name: {$name}
Type: {$type}
City: {$city}
Focus on 'near me' intent and local expertise.";

        $res = GateTouch_AI_Engine::call( $system, $user, 'gpt-4o-mini' );
        
        if ( is_string( $res ) ) {
            wp_send_json_success( [ 'description' => $res ] );
        } else {
            wp_send_json_error( __( 'AI generation failed.', 'gatetouch-ai-seo' ) );
        }
    }
}
