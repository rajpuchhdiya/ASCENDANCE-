<?php
/**
 * Template Name: Feed & Notification Preferences
 *
 * This template allows logged-in subscribers to customize their feed interests and notifications.
 *
 * @package Ascendance
 */

if ( ! is_user_logged_in() ) {
    wp_safe_redirect( home_url( '/login/' ) );
    exit;
}

$user_id = get_current_user_id();
$success = false;

// Handle form submission
if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['ascendance_pref_nonce'] ) ) {
    if ( wp_verify_nonce( $_POST['ascendance_pref_nonce'], 'save_preferences' ) ) {
        $topics = isset( $_POST['preferred_topics'] ) ? array_map( 'intval', $_POST['preferred_topics'] ) : array();
        $regions = isset( $_POST['preferred_regions'] ) ? array_map( 'intval', $_POST['preferred_regions'] ) : array();

        update_user_meta( $user_id, 'preferred_topics', $topics );
        update_user_meta( $user_id, 'preferred_regions', $regions );
        
        $success = true;
    }
}

// Retrieve data
$preferred_topics = get_user_meta( $user_id, 'preferred_topics', true );
if ( empty( $preferred_topics ) ) {
    $preferred_topics = get_user_meta( $user_id, 'preferred_industries', true );
}
$preferred_regions = get_user_meta( $user_id, 'preferred_regions', true );

if ( ! is_array( $preferred_topics ) ) {
    $preferred_topics = array();
}
if ( ! is_array( $preferred_regions ) ) {
    $preferred_regions = array();
}

$topics = get_terms( array( 'taxonomy' => 'topic', 'hide_empty' => false ) );
$regions = get_terms( array( 'taxonomy' => 'region', 'hide_empty' => false ) );

get_header();
?>

<main id="primary" class="site-main">
    <section class="page-hero bg-navy-deep text-white py-16 md:py-24 border-b border-brand-divider-dark">
        <div class="container mx-auto px-6 md:px-8">
            <div class="page-hero-inner">
                <p class="page-hero-eyebrow text-xs font-mono uppercase tracking-widest text-brand-red mb-4 block"><?php esc_html_e( '// FILTER CONFIGURATION', 'ascendance' ); ?></p>
                <h1 class="page-hero-title text-3xl md:text-5xl font-sans font-bold leading-tight mb-6"><?php esc_html_e( 'Intelligence Preferences', 'ascendance' ); ?></h1>
                <p class="page-hero-desc text-base md:text-lg text-cream/80 max-w-[720px] leading-relaxed">
                    <?php esc_html_e( 'Select the regions and topics of interest to filter your feed recommendations and control which updates trigger alert notifications.', 'ascendance' ); ?>
                </p>
            </div>
        </div>
    </section>

    <div class="content-wrapper py-16 md:py-24 bg-cream dark:bg-navy-deep border-b border-brand-divider-light dark:border-brand-divider-dark">
        <div class="container mx-auto max-w-[720px] px-6">

             <?php if ( $success ) : ?>
                <div class="pmpro_message pmpro_success mb-6 border-l-[3px] border-l-brand-red bg-white dark:bg-navy-mid p-4 rounded-sm text-sm text-brand-text-primary dark:text-cream border border-brand-divider-light dark:border-brand-divider-dark shadow-sm">
                    <strong><?php esc_html_e( 'Preferences Saved:', 'ascendance' ); ?></strong> <?php esc_html_e( 'Your intelligence filters and subscription categories have been updated successfully.', 'ascendance' ); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="" class="flex flex-col gap-10">
                <?php wp_nonce_field( 'save_preferences', 'ascendance_pref_nonce' ); ?>

                <!-- Topics Panel -->
                <div class="card bg-white dark:bg-navy-mid border-l-[3px] border-l-brand-red border-y border-r border-brand-divider-light dark:border-brand-divider-dark p-8 md:p-10 rounded-sm shadow-md transition-all duration-300">
                    <h3 class="text-lg font-sans font-bold text-brand-text-primary dark:text-white mb-6 border-b border-dashed border-brand-divider-light dark:border-brand-divider-dark/20 pb-4">
                        <i class="fa-solid fa-tags text-brand-red mr-3"></i><?php esc_html_e( 'Topics of Interest', 'ascendance' ); ?>
                    </h3>
                    
                    <?php if ( ! empty( $topics ) && ! is_wp_error( $topics ) ) : ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <?php foreach ( $topics as $topic ) : ?>
                                <label class="inline-flex items-center gap-3 cursor-pointer font-sans text-sm font-medium text-brand-text-primary dark:text-cream select-none">
                                    <input type="checkbox" name="preferred_topics[]" value="<?php echo esc_attr( $topic->term_id ); ?>" <?php checked( in_array( $topic->term_id, $preferred_topics, true ) ); ?> class="w-4.5 h-4.5 accent-brand-red" />
                                    <?php echo esc_html( $topic->name ); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p class="text-brand-text-muted dark:text-cream/50 italic text-sm"><?php esc_html_e( 'No intelligence topics registered.', 'ascendance' ); ?></p>
                    <?php endif; ?>
                </div>

                <!-- Regions Panel -->
                <div class="card bg-white dark:bg-navy-mid border-l-[3px] border-l-brand-red border-y border-r border-brand-divider-light dark:border-brand-divider-dark p-8 md:p-10 rounded-sm shadow-md transition-all duration-300">
                    <h3 class="text-lg font-sans font-bold text-brand-text-primary dark:text-white mb-6 border-b border-dashed border-brand-divider-light dark:border-brand-divider-dark/20 pb-4">
                        <i class="fa-solid fa-earth-africa text-brand-red mr-3"></i><?php esc_html_e( 'Geographic Corridors', 'ascendance' ); ?>
                    </h3>
                    
                    <?php if ( ! empty( $regions ) && ! is_wp_error( $regions ) ) : ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <?php foreach ( $regions as $region ) : ?>
                                <label class="inline-flex items-center gap-3 cursor-pointer font-sans text-sm font-medium text-brand-text-primary dark:text-cream select-none">
                                    <input type="checkbox" name="preferred_regions[]" value="<?php echo esc_attr( $region->term_id ); ?>" <?php checked( in_array( $region->term_id, $preferred_regions, true ) ); ?> class="w-4.5 h-4.5 accent-brand-red" />
                                    <?php echo esc_html( $region->name ); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p class="text-brand-text-muted dark:text-cream/50 italic text-sm"><?php esc_html_e( 'No geographic regions registered.', 'ascendance' ); ?></p>
                    <?php endif; ?>
                </div>

                <!-- Action Bar -->
                <div class="flex justify-between items-center flex-wrap gap-4 mt-4">
                    <a href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>" class="text-brand-text-muted hover:text-brand-red font-sans text-sm font-bold inline-flex items-center gap-2 transition-colors duration-150">
                        <i class="fa-solid fa-chevron-left text-brand-red transition-transform duration-150 hover:-translate-x-1"></i><?php esc_html_e( 'Back to Dashboard', 'ascendance' ); ?>
                    </a>
                    <input type="submit" class="btn btn-primary cursor-pointer" value="<?php esc_attr_e( 'Save My Selection', 'ascendance' ); ?>" />
                </div>
            </form>

        </div>
    </div>
</main>

<?php
get_footer();
