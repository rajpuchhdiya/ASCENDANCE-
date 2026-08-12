<?php
/**
 * Template Name: Payment Successful
 *
 * @package Ascendance
 */

get_header();
?>

<main class="min-h-screen bg-cream dark:bg-navy-deep text-brand-text-primary dark:text-cream py-16 md:py-24">
    <div class="max-w-2xl mx-auto px-4 text-center">
        <div class="bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-8 md:p-12 rounded-sm shadow-lg">
            <div class="w-16 h-16 rounded-full bg-green-500/10 text-green-600 dark:text-green-400 flex items-center justify-center mx-auto mb-6 text-2xl">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            
            <h1 class="text-3xl font-serif font-bold text-brand-text-primary dark:text-white mb-3">
                <?php esc_html_e( 'Payment Successful', 'ascendance' ); ?>
            </h1>
            
            <?php
            $is_logged_in = is_user_logged_in();
            $user = wp_get_current_user();
            $has_tier = false;
            if ( $is_logged_in ) {
                $roles = (array) $user->roles;
                if ( in_array( 'ascendance_essential', $roles, true ) || in_array( 'ascendance_professional', $roles, true ) || in_array( 'ascendance_enterprise', $roles, true ) ) {
                    $has_tier = true;
                }
            }
            ?>
            <p class="text-base text-brand-text-secondary dark:text-cream/80 mb-2">
                <?php esc_html_e( 'Your Ascendance subscription payment has been received.', 'ascendance' ); ?>
            </p>
            <p class="text-sm text-brand-text-secondary dark:text-cream/70 mb-8">
                <?php if ( $has_tier ) : ?>
                    <?php esc_html_e( 'Your account activation is confirmed. You can now access your member dashboard and subscriber briefings.', 'ascendance' ); ?>
                <?php else : ?>
                    <?php esc_html_e( 'Your account entitlement is currently activating in the background. Access will be unlocked automatically upon final payment confirmation.', 'ascendance' ); ?>
                <?php endif; ?>
            </p>

            <a href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>" class="inline-flex items-center justify-center px-8 py-3.5 border border-transparent text-sm font-bold uppercase tracking-wider rounded-sm text-white bg-brand-red hover:bg-brand-red-dark transition-colors shadow-sm">
                <i class="fa-solid fa-gauge-high mr-2"></i>
                <?php esc_html_e( 'Go to Dashboard', 'ascendance' ); ?>
            </a>
        </div>
    </div>
</main>

<?php
get_footer();
