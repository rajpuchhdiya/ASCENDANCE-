<?php
/**
 * Template Name: Lost Password
 *
 * This template displays the custom branded lost password reset request form.
 *
 * @package Ascendance
 */

$errors = new WP_Error();
$success = false;

if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['action'] ) && 'lostpassword' === $_POST['action'] ) {
    if ( empty( $_POST['user_login'] ) ) {
        $errors->add( 'empty_username', esc_html__( 'Please enter a username or email address.', 'ascendance' ) );
    } else {
        $login = trim( $_POST['user_login'] );
        $user_data = null;
        if ( strpos( $login, '@' ) !== false ) {
            $user_data = get_user_by( 'email', $login );
        } else {
            $user_data = get_user_by( 'login', $login );
        }

        if ( ! $user_data ) {
            $errors->add( 'invalid_user', esc_html__( 'No account matches that username or email address.', 'ascendance' ) );
        } else {
            // Trigger WordPress retrieve_password logic
            $result = retrieve_password( $user_data->user_login );
            if ( is_wp_error( $result ) ) {
                $errors = $result;
            } else {
                $success = true;
            }
        }
    }
}

get_header();
?>

<main id="primary" class="site-main">
    <section class="page-hero bg-navy-deep text-white py-16 md:py-24 border-b border-brand-divider-dark">
        <div class="container mx-auto px-6 md:px-8">
            <div class="page-hero-inner">
                <p class="page-hero-eyebrow text-xs font-mono uppercase tracking-widest text-brand-red mb-4 block"><?php esc_html_e( '// ACCOUNT RECOVERY', 'ascendance' ); ?></p>
                <h1 class="page-hero-title text-3xl md:text-5xl font-sans font-bold leading-tight mb-6"><?php esc_html_e( 'Reset Credentials', 'ascendance' ); ?></h1>
                <p class="page-hero-desc text-base md:text-lg text-cream/80 max-w-[720px] leading-relaxed">
                    <?php esc_html_e( 'Enter your registered credentials below to receive a secure recovery email containing a password reset link.', 'ascendance' ); ?>
                </p>
            </div>
        </div>
    </section>

    <div class="content-wrapper py-16 md:py-24 bg-cream dark:bg-navy-deep border-b border-brand-divider-light dark:border-brand-divider-dark">
        <div class="container mx-auto max-w-[480px] px-6">

            <?php
            // Show success notice
            if ( $success ) {
                echo '<div class="pmpro_message pmpro_success mb-6 border-l-[3px] border-l-brand-red bg-white dark:bg-navy-mid p-4 rounded-sm text-sm text-brand-text-primary dark:text-cream border border-brand-divider-light dark:border-brand-divider-dark shadow-sm">';
                echo '<strong>' . esc_html__( 'Success:', 'ascendance' ) . '</strong> ' . esc_html__( 'A recovery email has been sent. Please check your inbox (and spam folder) for the password reset instructions.', 'ascendance' );
                echo '</div>';
            }

            // Show error alerts
            if ( $errors->has_errors() ) {
                echo '<div class="pmpro_message pmpro_error mb-6 border-l-[3px] border-l-brand-red bg-white dark:bg-navy-mid p-4 rounded-sm text-sm text-brand-text-primary dark:text-cream border border-brand-divider-light dark:border-brand-divider-dark shadow-sm">';
                foreach ( $errors->get_error_messages() as $error_msg ) {
                    echo '<p class="m-0"><strong>' . esc_html__( 'Error:', 'ascendance' ) . '</strong> ' . esc_html( $error_msg ) . '</p>';
                }
                echo '</div>';
            }
            ?>

            <?php if ( ! $success ) : ?>
                <div class="card bg-white dark:bg-navy-mid border-l-[3px] border-l-brand-red border-y border-r border-brand-divider-light dark:border-brand-divider-dark p-8 md:p-10 rounded-sm shadow-md transition-all duration-300">
                    <form name="lostpasswordform" id="lostpasswordform" method="post" action="" class="flex flex-col gap-6">
                        
                        <p class="m-0 flex flex-col gap-2">
                            <label for="user_login" class="font-sans text-sm font-medium text-brand-text-muted dark:text-cream/60">
                                <?php esc_html_e( 'Username or Email Address', 'ascendance' ); ?>
                            </label>
                            <input type="text" name="user_login" id="user_login" class="input w-full px-4 py-3 bg-white dark:bg-navy-deep border border-brand-divider-light dark:border-brand-divider-dark rounded-sm text-brand-text-primary dark:text-cream font-mono text-sm outline-none transition-all duration-150 focus:border-brand-red focus:shadow-[0_0_15px_rgba(188,27,29,0.3)]" value="" size="20" required />
                        </p>

                        <input type="hidden" name="action" value="lostpassword" />

                        <p class="mt-2.5 mb-0">
                            <input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large bg-brand-red hover:bg-brand-red-light text-white border-none rounded-sm py-3.5 px-6 font-sans font-bold text-sm uppercase tracking-wider cursor-pointer transition-all duration-150 active:scale-[0.98] w-full" value="<?php esc_attr_e( 'Send Reset Instructions', 'ascendance' ); ?>" />
                        </p>
                    </form>
                </div>
            <?php endif; ?>

            <div class="text-center mt-8">
                <a href="<?php echo esc_url( home_url( '/login/' ) ); ?>" class="back-to-login-link text-navy-deep dark:text-cream font-sans text-sm no-underline font-bold inline-flex items-center gap-2 transition-colors duration-150 hover:text-brand-red">
                    <i class="fa-solid fa-arrow-left-long text-brand-red transition-transform duration-150 hover:-translate-x-1"></i><?php esc_html_e( 'Return to Subscriber Login', 'ascendance' ); ?>
                </a>
            </div>

        </div>
    </div>
</main>

<?php
get_footer();
