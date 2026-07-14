<?php
/**
 * Template Name: Subscriber Login
 *
 * This template displays the custom branded login form for subscribers.
 *
 * @package Ascendance
 */

if ( is_user_logged_in() ) {
    wp_safe_redirect( home_url( '/dashboard/' ) );
    exit;
}

get_header();
?>

<main id="primary" class="site-main">
    <section class="page-hero bg-navy-deep text-white py-16 md:py-24 border-b border-brand-divider-dark">
        <div class="container mx-auto px-6 md:px-8">
            <div class="page-hero-inner">
                <p class="page-hero-eyebrow text-xs font-mono uppercase tracking-widest text-brand-red mb-4 block"><?php esc_html_e( '// AUTHENTICATION GATEWAY', 'ascendance' ); ?></p>
                <h1 class="page-hero-title text-3xl md:text-5xl font-sans font-bold leading-tight mb-6"><?php esc_html_e( 'Subscriber Authentication', 'ascendance' ); ?></h1>
                <p class="page-hero-desc text-base md:text-lg text-cream/80 max-w-[720px] leading-relaxed">
                    <?php esc_html_e( 'Sign in to access your intelligence reports, real-time briefing updates, and dossier assets.', 'ascendance' ); ?>
                </p>
            </div>
        </div>
    </section>

    <div class="content-wrapper py-16 md:py-24 bg-cream dark:bg-navy-deep border-b border-brand-divider-light dark:border-brand-divider-dark">
        <div class="container mx-auto max-w-[480px] px-6">
            
            <?php
            // Handle error messages
            if ( isset( $_GET['login'] ) ) {
                $error_type = sanitize_key( $_GET['login'] );
                if ( 'failed' === $error_type ) {
                    echo '<div class="pmpro_message pmpro_error mb-6 border-l-[3px] border-l-brand-red bg-white dark:bg-navy-mid p-4 rounded-sm text-sm text-brand-text-primary dark:text-cream border border-brand-divider-light dark:border-brand-divider-dark shadow-sm">';
                    echo '<strong>' . esc_html__( 'Error:', 'ascendance' ) . '</strong> ' . esc_html__( 'Invalid username or password. Please try again.', 'ascendance' );
                    echo '</div>';
                } elseif ( 'empty' === $error_type ) {
                    echo '<div class="pmpro_message pmpro_error mb-6 border-l-[3px] border-l-brand-red bg-white dark:bg-navy-mid p-4 rounded-sm text-sm text-brand-text-primary dark:text-cream border border-brand-divider-light dark:border-brand-divider-dark shadow-sm">';
                    echo '<strong>' . esc_html__( 'Error:', 'ascendance' ) . '</strong> ' . esc_html__( 'Both username and password fields are required.', 'ascendance' );
                    echo '</div>';
                }
            }
            ?>

            <div class="card bg-white dark:bg-navy-mid border-l-[3px] border-l-brand-red border-y border-r border-brand-divider-light dark:border-brand-divider-dark p-8 md:p-10 rounded-sm shadow-md transition-all duration-300">
                <?php
                wp_login_form( array(
                    'echo'           => true,
                    'redirect'       => home_url( '/dashboard/' ),
                    'form_id'        => 'ascendance-loginform',
                    'label_username' => esc_html__( 'Username or Email Address', 'ascendance' ),
                    'label_password' => esc_html__( 'Password', 'ascendance' ),
                    'label_remember' => esc_html__( 'Keep me signed in', 'ascendance' ),
                    'label_log_in'   => esc_html__( 'Access Platform', 'ascendance' ),
                    'id_username'    => 'user_login',
                    'id_password'    => 'user_pass',
                    'id_remember'    => 'rememberme',
                    'id_submit'      => 'wp-submit',
                    'remember'       => true,
                    'value_username' => '',
                    'value_remember' => true,
                ) );
                ?>

                <div class="mt-8 flex justify-between items-center gap-4 border-t border-dashed border-brand-divider-light dark:border-brand-divider-dark/20 pt-6 text-sm font-sans">
                    <a href="<?php echo esc_url( home_url( '/lostpassword/' ) ); ?>" class="text-brand-text-muted hover:text-brand-red flex items-center gap-1.5 transition-colors duration-150">
                        <i class="fa-solid fa-lock-open text-brand-red"></i><?php esc_html_e( 'Forgot password?', 'ascendance' ); ?>
                    </a>
                    <a href="<?php echo esc_url( function_exists( 'pmpro_url' ) ? pmpro_url( 'levels' ) : home_url( '/membership-levels/' ) ); ?>" class="text-brand-text-primary dark:text-cream hover:text-brand-red font-bold flex items-center gap-1.5 transition-colors duration-150">
                        <?php esc_html_e( 'Subscribe to Tier', 'ascendance' ); ?><i class="fa-solid fa-arrow-right text-brand-red"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>
</main>

<style>
/* Login Form Style Overrides to match Ascendance Brand via Tailwind utilities */
#ascendance-loginform {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}
#ascendance-loginform p {
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
#ascendance-loginform p.login-remember {
    flex-direction: row;
    align-items: center;
    gap: 0.5rem;
}
#ascendance-loginform label {
    font-family: var(--font-heading);
    font-size: var(--font-size-sm);
    color: var(--text-primary);
    font-weight: 500;
}
#ascendance-loginform input[type="text"],
#ascendance-loginform input[type="password"] {
    background-color: var(--input-bg);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    padding: 12px 16px;
    color: var(--input-text);
    font-family: var(--font-mono);
    font-size: var(--font-size-sm);
    transition: border-color 0.2s, box-shadow 0.2s;
    width: 100%;
}
#ascendance-loginform input[type="text"]:focus,
#ascendance-loginform input[type="password"]:focus {
    outline: none;
    border-color: var(--color-red);
    box-shadow: 0 0 0 2px rgba(229, 62, 62, 0.2);
}
#ascendance-loginform p.login-submit {
    margin-top: 10px;
}
#ascendance-loginform input[type="submit"] {
    background-color: var(--color-red);
    color: var(--color-white);
    border: none;
    border-radius: var(--radius-sm);
    padding: 14px 24px;
    font-family: var(--font-heading);
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
    cursor: pointer;
    transition: background-color 0.2s, transform 0.1s;
    width: 100%;
}
#ascendance-loginform input[type="submit"]:hover {
    background-color: #c53030;
}
#ascendance-loginform input[type="submit"]:active {
    transform: scale(0.98);
}
</style>

<?php
get_footer();
