<?php
/**
 * Template Name: Payment Cancelled
 *
 * @package Ascendance
 */

get_header();
?>

<main class="min-h-screen bg-cream dark:bg-navy-deep text-brand-text-primary dark:text-cream py-16 md:py-24">
    <div class="max-w-2xl mx-auto px-4 text-center">
        <div class="bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-8 md:p-12 rounded-sm shadow-lg">
            <div class="w-16 h-16 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center mx-auto mb-6 text-2xl">
                <i class="fa-solid fa-ban"></i>
            </div>
            
            <h1 class="text-3xl font-serif font-bold text-brand-text-primary dark:text-white mb-3">
                <?php esc_html_e( 'Checkout Cancelled', 'ascendance' ); ?>
            </h1>
            
            <p class="text-base text-brand-text-secondary dark:text-cream/80 mb-8">
                <?php esc_html_e( 'No payment was completed and no subscription entitlement was granted.', 'ascendance' ); ?>
            </p>

            <a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>" class="inline-flex items-center justify-center px-8 py-3.5 border border-transparent text-sm font-bold uppercase tracking-wider rounded-sm text-white bg-brand-red hover:bg-brand-red-dark transition-colors shadow-sm">
                <i class="fa-solid fa-arrow-left mr-2"></i>
                <?php esc_html_e( 'Return to Plans', 'ascendance' ); ?>
            </a>
        </div>
    </div>
</main>

<?php
get_footer();
