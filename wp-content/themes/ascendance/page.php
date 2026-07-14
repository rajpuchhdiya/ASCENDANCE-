<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="site-main">

	<?php
	while ( have_posts() ) :
		the_post();
		?>

		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<?php
			$eyebrow = '// ' . strtoupper( get_the_title() );
			$desc = '';
			$uri = $_SERVER['REQUEST_URI'];
			
			if ( strpos( $uri, 'membership-account' ) !== false ) {
				$eyebrow = __( '// Member Portal', 'ascendance' );
				$desc = __( 'Manage your active subscription tiers, billing cycles, and profile settings.', 'ascendance' );
			} elseif ( strpos( $uri, 'edit-profile' ) !== false ) {
				$eyebrow = __( '// Member Profile', 'ascendance' );
				$desc = __( 'Update your credentials, personal info, and password settings.', 'ascendance' );
			} elseif ( strpos( $uri, 'membership-levels' ) !== false ) {
				$eyebrow = __( '// Subscription Plans', 'ascendance' );
				$desc = __( 'Select a strategic intelligence tier to unlock full access to the Ascendance Platform.', 'ascendance' );
			} elseif ( strpos( $uri, 'membership-checkout' ) !== false ) {
				$eyebrow = __( '// Secure Checkout', 'ascendance' );
				$desc = __( 'Review your details and finalize your credentials to activate platform access.', 'ascendance' );
			} elseif ( strpos( $uri, 'membership-billing' ) !== false ) {
				$eyebrow = __( '// Billing Details', 'ascendance' );
				$desc = __( 'Update payment methods, view invoice history, and manage your billing settings.', 'ascendance' );
			} elseif ( strpos( $uri, 'membership-confirmation' ) !== false ) {
				$eyebrow = __( '// System Status: Active', 'ascendance' );
				$desc = __( 'Your credentials have been provisioned. Welcome to Ascendance.', 'ascendance' );
			} else {
				$desc = get_the_excerpt();
			}
			?>
			<section class="page-hero bg-navy-deep text-white py-16 md:py-24 border-b border-brand-divider-dark">
				<div class="container mx-auto px-6 md:px-8">
					<div class="page-hero-inner">
						<p class="page-hero-eyebrow text-xs font-mono uppercase tracking-widest text-brand-red mb-4 block"><?php echo esc_html( $eyebrow ); ?></p>
						<h1 class="page-hero-title text-3xl md:text-5xl font-sans font-bold leading-tight mb-6"><?php the_title(); ?></h1>
						<?php if ( ! empty( $desc ) ) : ?>
							<p class="page-hero-desc text-base md:text-lg text-cream/80 max-w-[720px] leading-relaxed"><?php echo esc_html( $desc ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			</section>

			<div class="content-wrapper py-16 md:py-24 bg-cream dark:bg-navy-deep border-b border-brand-divider-light dark:border-brand-divider-dark">
				<div class="container mx-auto px-6 md:px-8">
					<div class="main-content">
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="post-featured-image mb-10 max-w-full overflow-hidden border border-brand-divider-light dark:border-brand-divider-dark rounded-sm">
								<?php the_post_thumbnail( 'full' ); ?>
							</div>
						<?php endif; ?>

						<div class="entry-content text-brand-text-primary dark:text-cream leading-relaxed">
							<?php
							the_content();

							wp_link_pages(
								array(
									'before' => '<div class="page-links mt-6 text-sm">' . esc_html__( 'Pages:', 'ascendance' ),
									'after'  => '</div>',
								)
							);
							?>
						</div>

						<?php
						// If comments are open or we have at least one comment, load up the comment template.
						if ( comments_open() || get_comments_number() ) :
							comments_template();
						endif;
						?>
					</div>
				</div>
			</div>
		</article>

		<?php
	endwhile; // End of the loop.
	?>

</main><!-- #primary -->

<?php
get_footer();
