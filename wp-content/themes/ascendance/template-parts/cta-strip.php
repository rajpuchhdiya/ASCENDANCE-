<?php
/**
 * Template Part: CTA Strip
 *
 * Bottom call-to-action band shared across public pages.
 *
 * @package Ascendance
 */

$heading    = $args['heading']    ?? __( 'Ready to Access Strategic Intelligence?', 'ascendance' );
$body       = $args['body']       ?? __( 'Join thousands of decision-makers, analysts, and executives who rely on Ascendance for forward-looking intelligence that drives action.', 'ascendance' );
$btn_label  = $args['btn_label']  ?? __( 'Start Free Trial', 'ascendance' );
$btn_url    = $args['btn_url']    ?? home_url( '/newsletter/' );
$btn2_label = $args['btn2_label'] ?? __( 'View Pricing', 'ascendance' );
$btn2_url   = $args['btn2_url']   ?? ( function_exists( 'pmpro_url' ) ? pmpro_url( 'levels' ) : home_url( '/membership-levels/' ) );
?>
<section class="cta-strip">
	<div class="wrap">
		<div class="cta-strip-inner">
			<h2><?php echo esc_html( $heading ); ?></h2>
			<p><?php echo esc_html( $body ); ?></p>
			<div class="btn-group">
				<a href="<?php echo esc_url( $btn_url ); ?>" class="btn-primary"><?php echo esc_html( $btn_label ); ?></a>
				<?php if ( $btn2_label && $btn2_url ) : ?>
					<a href="<?php echo esc_url( $btn2_url ); ?>" class="btn-outline"><?php echo esc_html( $btn2_label ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
