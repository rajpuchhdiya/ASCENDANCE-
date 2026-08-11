<?php
/**
 * Template Name: Login Page
 *
 * @package Ascendance
 */

get_header();
?>

<main>
<section class="auth-wrap">
	<div class="wrap">
		<div class="auth-card">
			<span class="kicker">Subscriber access</span>
			<h1>Log in</h1>
			<p class="auth-sub">Access your saved briefs, dossiers and registers.</p>
			
			<?php if ( isset( $_GET['login'] ) && $_GET['login'] === 'failed' ) : ?>
				<div class="note" style="border-left: 3px solid var(--red); background: var(--red-wash); color: var(--red); padding: 12px 16px; margin-bottom: 20px; font-weight: 600; font-family: var(--font-ui); font-size: 13.5px; border-radius: 2px;">
					Invalid email address or password. Please check your details and try again.
				</div>
			<?php endif; ?>

			<?php if ( is_user_logged_in() ) : ?>
				<div class="panel" style="background:var(--paper-2); padding:24px; border:1px solid var(--hairline-2); border-radius:2px;">
					<p style="font-family:var(--font-body); font-size:16px; margin-bottom:16px;">You are currently signed in.</p>
					<a class="btn-primary" href="<?php echo esc_url( home_url( '/account/' ) ); ?>">Go to Account Dashboard</a>
				</div>
			<?php else : ?>
				<form name="loginform" id="loginform" action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>" method="post">
					<div class="as-form-group" style="margin-bottom: 20px;">
						<label for="user_login">Email</label>
						<input type="email" name="log" id="user_login" class="input" value="" size="20" placeholder="you@firm.com" required>
					</div>
					<div class="as-form-group" style="margin-bottom: 16px;">
						<label for="user_pass">Password</label>
						<input type="password" name="pwd" id="user_pass" class="input" value="" size="20" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required>
					</div>
					
					<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
						<p class="login-remember" style="margin: 0;">
							<label style="display: flex; flex-direction: row; flex-wrap: nowrap; align-items: center; justify-content: flex-start; gap: 8px; cursor: pointer; font-family: var(--font-ui); font-size: 13.5px; color: var(--ink); font-weight: 500; margin: 0;">
								<input name="rememberme" type="checkbox" id="rememberme" value="forever" style="margin: 0; width: auto; display: inline-block; flex-shrink: 0;"> 
								<span style="display: inline-block; white-space: nowrap;">Keep me signed in</span>
							</label>
						</p>
						<a href="<?php echo esc_url( home_url( '/lostpassword/' ) ); ?>" class="auth-link" style="font-family: var(--font-ui); font-size: 13px; font-weight: 600; color: var(--red); text-decoration: none;">Forgot password?</a>
					</div>
					
					<div class="login-submit">
						<input type="submit" name="wp-submit" id="wp-submit" class="btn-primary" value="Log in" style="width: 100%; justify-content: center; font-size: 15px; padding: 12px;">
						<input type="hidden" name="redirect_to" value="<?php echo esc_url( home_url( '/account/' ) ); ?>">
					</div>
				</form>
			<?php endif; ?>

			<div class="auth-alt">
				<span>Not a subscriber yet?</span>
				<a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>">See subscriptions &rarr;</a>
			</div>
			<div class="auth-sso">
				<span class="auth-sso-lbl">Enterprise seat?</span>
				<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Sign in with your organization (SSO)</a>
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
