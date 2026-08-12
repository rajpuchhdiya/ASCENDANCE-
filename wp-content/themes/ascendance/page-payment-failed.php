<?php
/**
 * Template Name: Payment Failed
 *
 * @package Ascendance
 */

get_header();
?>

<main class="min-h-screen bg-cream dark:bg-navy-deep text-brand-text-primary dark:text-cream py-16 md:py-24">
    <div class="max-w-2xl mx-auto px-4 text-center">
        <div class="bg-white dark:bg-navy-mid border border-brand-red/30 p-8 md:p-12 rounded-sm shadow-lg">
            <div class="w-16 h-16 rounded-full bg-brand-red/10 text-brand-red flex items-center justify-center mx-auto mb-6 text-2xl">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>
            
            <h1 class="text-3xl font-serif font-bold text-brand-text-primary dark:text-white mb-3">
                <?php esc_html_e( 'Payment Could Not Be Completed', 'ascendance' ); ?>
            </h1>
            
            <p class="text-base text-brand-text-secondary dark:text-cream/80 mb-8">
                <?php esc_html_e( 'We were unable to process your payment method. No charge was completed and no access was granted.', 'ascendance' ); ?>
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>" class="inline-flex items-center justify-center px-8 py-3.5 border border-transparent text-sm font-bold uppercase tracking-wider rounded-sm text-white bg-brand-red hover:bg-brand-red-dark transition-colors shadow-sm">
                    <i class="fa-solid fa-rotate-right mr-2"></i>
                    <?php esc_html_e( 'Try Again', 'ascendance' ); ?>
                </a>
                <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="inline-flex items-center justify-center px-8 py-3.5 border border-brand-divider-light dark:border-brand-divider-dark text-sm font-medium rounded-sm text-brand-text-primary dark:text-cream hover:bg-cream/40 dark:hover:bg-navy-deep transition-colors">
                    <?php esc_html_e( 'Contact Support', 'ascendance' ); ?>
                </a>
            </div>
        </div>
    </div>
</main>

<?php
get_footer();
