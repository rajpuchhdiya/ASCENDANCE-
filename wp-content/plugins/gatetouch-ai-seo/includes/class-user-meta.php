<?php
defined( 'ABSPATH' ) || exit;

/**
 * SEO fields on the user profile.
 *
 * Drives two things: the author archive's own metadata, and the author entity
 * (Person schema with sameAs, jobTitle and credentials) attached to every article
 * they write. Author entities are the single strongest E-E-A-T signal available
 * to a WordPress site, and are heavily weighted by AI answer engines.
 */
class GateTouch_User_Meta {

    const META_KEY = 'gatetouch_seo';

    /** Social profile fields, in output order. */
    public static function social_fields() {
        return [
            'twitter'   => 'X (Twitter) URL',
            'linkedin'  => 'LinkedIn URL',
            'facebook'  => 'Facebook URL',
            'instagram' => 'Instagram URL',
            'youtube'   => 'YouTube URL',
            'pinterest' => 'Pinterest URL',
            'github'    => 'GitHub URL',
            'wikipedia' => 'Wikipedia URL',
            'website'   => 'Personal website URL',
        ];
    }

    public function __construct() {
        add_action( 'show_user_profile', [ $this, 'render_fields' ] );
        add_action( 'edit_user_profile', [ $this, 'render_fields' ] );
        add_action( 'personal_options_update',  [ $this, 'save' ] );
        add_action( 'edit_user_profile_update', [ $this, 'save' ] );
    }

    /**
     * Normalised SEO meta for a user, shaped like post/term meta.
     */
    public static function get( $user_id ) {
        $user_id = (int) $user_id;
        if ( ! $user_id ) {
            return [];
        }

        $meta = get_user_meta( $user_id, self::META_KEY, true );
        return is_array( $meta ) ? $meta : [];
    }

    public static function defaults() {
        return [
            'meta_title'       => '',
            'meta_description' => '',
            'canonical'        => '',
            'noindex'          => '',
            'og_image'         => '',
            'job_title'        => '',
            'credentials'      => '',
            'expertise'        => '',
            'social'           => [],
        ];
    }

    /**
     * Every external profile URL for a user, for Person.sameAs.
     */
    public static function same_as( $user_id ) {
        $meta   = self::get( $user_id );
        $social = isset( $meta['social'] ) && is_array( $meta['social'] ) ? $meta['social'] : [];

        $urls = array_values( array_filter( array_map( 'esc_url_raw', $social ) ) );

        // WordPress core stores one URL on the user record too.
        $user = get_userdata( $user_id );
        if ( $user && $user->user_url ) {
            $urls[] = esc_url_raw( $user->user_url );
        }

        return array_values( array_unique( $urls ) );
    }

    public function render_fields( $user ) {
        if ( ! current_user_can( 'edit_user', $user->ID ) ) {
            return;
        }

        $meta = wp_parse_args( self::get( $user->ID ), self::defaults() );
        $row  = GateTouch_Search_Appearance::group( 'archives', 'author' );
        ?>
        <h2 id="gatetouch-author-seo"><?php esc_html_e( 'SEO & Author Entity — GT AI SEO/GEO/AEO', 'gatetouch-ai-seo' ); ?></h2>
        <p class="description" style="max-width:640px;">
            <?php esc_html_e( 'These details build the author entity that Google and AI search engines use to judge expertise, authoritativeness and trust. Filling them in materially improves how your articles are attributed and cited.', 'gatetouch-ai-seo' ); ?>
        </p>

        <?php wp_nonce_field( 'gatetouch_save_user_meta', 'gatetouch_user_nonce' ); ?>

        <table class="form-table" role="presentation">
            <tr>
                <th><label for="gatetouch_user_job_title"><?php esc_html_e( 'Job Title', 'gatetouch-ai-seo' ); ?></label></th>
                <td>
                    <input type="text" name="gatetouch_seo[job_title]" id="gatetouch_user_job_title" class="regular-text"
                           value="<?php echo esc_attr( $meta['job_title'] ); ?>" />
                    <p class="description"><?php esc_html_e( 'e.g. "Senior Financial Analyst". Output as Person.jobTitle in schema.', 'gatetouch-ai-seo' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="gatetouch_user_credentials"><?php esc_html_e( 'Credentials', 'gatetouch-ai-seo' ); ?></label></th>
                <td>
                    <input type="text" name="gatetouch_seo[credentials]" id="gatetouch_user_credentials" class="regular-text"
                           value="<?php echo esc_attr( $meta['credentials'] ); ?>" />
                    <p class="description"><?php esc_html_e( 'e.g. "CFA, MBA". Output as Person.hasCredential.', 'gatetouch-ai-seo' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="gatetouch_user_expertise"><?php esc_html_e( 'Areas of Expertise', 'gatetouch-ai-seo' ); ?></label></th>
                <td>
                    <input type="text" name="gatetouch_seo[expertise]" id="gatetouch_user_expertise" class="regular-text"
                           value="<?php echo esc_attr( $meta['expertise'] ); ?>" />
                    <p class="description"><?php esc_html_e( 'Comma separated, e.g. "retirement planning, index funds". Output as Person.knowsAbout.', 'gatetouch-ai-seo' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="gatetouch_user_title"><?php esc_html_e( 'Author Archive Title', 'gatetouch-ai-seo' ); ?></label></th>
                <td>
                    <input type="text" name="gatetouch_seo[meta_title]" id="gatetouch_user_title" class="regular-text"
                           value="<?php echo esc_attr( $meta['meta_title'] ); ?>"
                           placeholder="<?php echo esc_attr( $row['title'] ?? '' ); ?>" />
                    <p class="description"><?php esc_html_e( 'Leave blank to use the site-wide author template.', 'gatetouch-ai-seo' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="gatetouch_user_desc"><?php esc_html_e( 'Author Archive Description', 'gatetouch-ai-seo' ); ?></label></th>
                <td>
                    <textarea name="gatetouch_seo[meta_description]" id="gatetouch_user_desc" rows="3" class="large-text"
                              placeholder="<?php echo esc_attr( $row['desc'] ?? '' ); ?>"><?php echo esc_textarea( $meta['meta_description'] ); ?></textarea>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Robots', 'gatetouch-ai-seo' ); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="gatetouch_seo[noindex]" value="1" <?php checked( ! empty( $meta['noindex'] ) ); ?> />
                        <?php esc_html_e( 'No-index this author archive', 'gatetouch-ai-seo' ); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th><label for="gatetouch_user_og_image"><?php esc_html_e( 'Social Image URL', 'gatetouch-ai-seo' ); ?></label></th>
                <td>
                    <input type="url" name="gatetouch_seo[og_image]" id="gatetouch_user_og_image" class="regular-text"
                           value="<?php echo esc_attr( $meta['og_image'] ); ?>" />
                    <p class="description"><?php esc_html_e( 'Falls back to the avatar.', 'gatetouch-ai-seo' ); ?></p>
                </td>
            </tr>
        </table>

        <h3><?php esc_html_e( 'Verified Profiles', 'gatetouch-ai-seo' ); ?></h3>
        <p class="description" style="max-width:640px;">
            <?php esc_html_e( 'Output as Person.sameAs, which is how search engines link this author to a real identity across the web.', 'gatetouch-ai-seo' ); ?>
        </p>
        <table class="form-table" role="presentation">
            <?php foreach ( self::social_fields() as $key => $label ) : ?>
                <tr>
                    <th><label for="gatetouch_user_social_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
                    <td>
                        <input type="url" name="gatetouch_seo[social][<?php echo esc_attr( $key ); ?>]"
                               id="gatetouch_user_social_<?php echo esc_attr( $key ); ?>" class="regular-text"
                               value="<?php echo esc_attr( $meta['social'][ $key ] ?? '' ); ?>" />
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php
    }

    public function save( $user_id ) {
        if ( ! isset( $_POST['gatetouch_user_nonce'] ) ) {
            return;
        }
        if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['gatetouch_user_nonce'] ) ), 'gatetouch_save_user_meta' ) ) {
            return;
        }
        if ( ! current_user_can( 'edit_user', $user_id ) ) {
            return;
        }

        $raw = isset( $_POST['gatetouch_seo'] ) && is_array( $_POST['gatetouch_seo'] )
            ? wp_unslash( $_POST['gatetouch_seo'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised field by field below.
            : [];

        $social = [];
        foreach ( array_keys( self::social_fields() ) as $key ) {
            $url = esc_url_raw( $raw['social'][ $key ] ?? '' );
            if ( $url ) {
                $social[ $key ] = $url;
            }
        }

        $clean = [
            'meta_title'       => sanitize_text_field( $raw['meta_title'] ?? '' ),
            'meta_description' => sanitize_textarea_field( $raw['meta_description'] ?? '' ),
            'canonical'        => esc_url_raw( $raw['canonical'] ?? '' ),
            'noindex'          => empty( $raw['noindex'] ) ? '' : '1',
            'og_image'         => esc_url_raw( $raw['og_image'] ?? '' ),
            'job_title'        => sanitize_text_field( $raw['job_title'] ?? '' ),
            'credentials'      => sanitize_text_field( $raw['credentials'] ?? '' ),
            'expertise'        => sanitize_text_field( $raw['expertise'] ?? '' ),
            'social'           => $social,
        ];

        update_user_meta( $user_id, self::META_KEY, $clean );

        // Keep the standalone job title in sync — the variable engine reads it directly.
        update_user_meta( $user_id, 'gatetouch_job_title', $clean['job_title'] );

        delete_transient( 'gatetouch_author_count' );
    }
}
