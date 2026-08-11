<?php
/**
 * Template Name: Lost Password
 *
 * Branded account recovery form.
 *
 * @package Ascendance
 */

$errors = new WP_Error();
$success = false;

if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['action'] ) && 'lostpassword' === $_POST['action'] ) {
    if ( empty( $_POST['user_login'] ) ) {
        $errors->add( 'empty_username', esc_html__( 'Please enter your email address.', 'ascendance' ) );
    } else {
        $login = trim( $_POST['user_login'] );
        $user_data = strpos( $login, '@' ) !== false ? get_user_by( 'email', $login ) : get_user_by( 'login', $login );

        if ( ! $user_data ) {
            $errors->add( 'invalid_user', esc_html__( 'No registered account matches that email address.', 'ascendance' ) );
        } else {
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

<main>
<section class="auth-wrap">
	<div class="wrap">
		<div class="auth-card">
			<span class="kicker">Account recovery</span>
			<h1>Reset Credentials</h1>
			<p class="auth-sub">Enter your registered email address below to receive a secure recovery link.</p>

			<?php if ( $success ) : ?>
				<div class="note" style="border-left: 3px solid var(--success, #3f6b4a); background: rgba(63,107,74,0.1); color: var(--success, #3f6b4a); padding: 14px 18px; margin-bottom: 24px; font-weight: 600; font-family: var(--font-ui); font-size: 14px; border-radius: 2px;">
					<strong>Success:</strong> A password recovery email has been sent. Please check your inbox (and spam folder) for instructions.
				</div>
			<?php endif; ?>

			<?php if ( $errors->has_errors() ) : ?>
				<div class="note" style="border-left: 3px solid var(--red); background: var(--red-wash); color: var(--red); padding: 14px 18px; margin-bottom: 24px; font-weight: 600; font-family: var(--font-ui); font-size: 14px; border-radius: 2px;">
					<?php foreach ( $errors->get_error_messages() as $error_msg ) : ?>
						<p style="margin:0;"><?php echo esc_html( $error_msg ); ?></p>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! $success ) : ?>
				<form name="lostpasswordform" id="lostpasswordform" method="post" action="" class="auth-form">
					<label>Email Address
						<input type="text" name="user_login" id="user_login" autocomplete="email" placeholder="you@firm.com" required />
					</label>
					<input type="hidden" name="action" value="lostpassword" />
					<input type="submit" name="wp-submit" id="wp-submit" class="btn-primary full" value="Send Reset Instructions" style="border:none; cursor:pointer;" />
				</form>
			<?php endif; ?>

			<div class="auth-alt">
				<span>Remembered your password?</span>
				<a href="<?php echo esc_url( home_url( '/login/' ) ); ?>">Return to Log in &rarr;</a>
			</div>
		</div>

		<aside class="auth-aside">
			<div class="auth-aside-k">Inside your account</div>
			<ul class="auth-aside-list">
				<li>Every brief and dossier your tier includes, in full</li>
				<li>Saved reading list across devices</li>
				<li>Registers, trackers and ratings</li>
				<li>Your briefing-email preferences and billing</li>
			</ul>
			<a class="auth-aside-cta" href="<?php echo esc_url( home_url( '/advisory/' ) ); ?>">Advisory client? Talk to the desk &rarr;</a>
		</aside>
	</div>
</section>
</main>

<?php
get_footer();
