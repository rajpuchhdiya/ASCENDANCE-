<?php
/**
 * The template for displaying all single Update CPT posts
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="site-main register-editorial">

	<?php
	while ( have_posts() ) :
		the_post();
		
		$post_id = get_the_ID();
		$parent_brief_id = get_field( 'parent_brief', $post_id );
		$impact = get_field( 'impact_assessment', $post_id ) ?: 'medium';
		$key_update = get_field( 'key_update', $post_id );

		// Impact Assessment styles
		$impact_colors = array(
			'low'      => array( 'bg' => 'rgba(0, 255, 102, 0.1)', 'border' => '#00FF66', 'text' => '#00FF66' ),
			'medium'   => array( 'bg' => 'rgba(255, 204, 0, 0.1)', 'border' => '#FFCC00', 'text' => '#FFCC00' ),
			'high'     => array( 'bg' => 'rgba(255, 102, 0, 0.1)', 'border' => '#FF6600', 'text' => '#FF6600' ),
			'critical' => array( 'bg' => 'rgba(188, 27, 29, 0.1)', 'border' => '#BC1B1D', 'text' => '#BC1B1D' ),
		);
		$style = isset( $impact_colors[ $impact ] ) ? $impact_colors[ $impact ] : $impact_colors['medium'];
		?>

		<article id="post-<?php the_ID(); ?>" <?php post_class( 'container' ); ?>>
			<!-- Update Header -->
			<header style="max-width: 800px; margin: 0 auto var(--space-40) auto; text-align: center;">
				<div style="display: flex; justify-content: center; gap: 10px; margin-bottom: var(--space-10); flex-wrap: wrap;">
					<span class="paywall-badge" style="background-color: var(--color-deep-navy); color: var(--color-white); border: 1px solid var(--border-color);"><?php esc_html_e( 'Real-time Update', 'ascendance' ); ?></span>
					<span style="background-color: <?php echo esc_attr( $style['bg'] ); ?>; border: 1px solid <?php echo esc_attr( $style['border'] ); ?>; color: <?php echo esc_attr( $style['text'] ); ?>; font-family: var(--font-heading); font-size: 11px; font-weight: bold; text-transform: uppercase; padding: 2px 8px; border-radius: var(--radius-sm);"><?php printf( esc_html__( 'Impact: %s', 'ascendance' ), esc_html( $impact ) ); ?></span>
				</div>

				<h1 style="margin-bottom: var(--space-20); color: var(--color-deep-navy); font-family: var(--font-heading);"><?php the_title(); ?></h1>
				
				<div style="display: flex; justify-content: center; gap: var(--space-30); color: var(--text-muted); font-size: var(--font-size-sm); font-family: var(--font-heading);">
					<span><i class="fa-regular fa-clock" style="color: var(--color-red); margin-right: 6px;"></i> <?php echo get_the_date(); ?></span>
					<span><i class="fa-solid fa-earth-americas" style="color: var(--color-red); margin-right: 6px;"></i> <?php the_terms( $post_id, 'region', '', ', ', '' ); ?></span>
				</div>
			</header>

			<div class="entry-content">
				
				<!-- Parent Brief Connection -->
				<?php if ( ! empty( $parent_brief_id ) ) : ?>
					<div style="background-color: rgba(10, 22, 40, 0.03); border: 1px solid rgba(10, 22, 40, 0.08); border-radius: var(--radius-md); padding: var(--space-20); margin-bottom: var(--space-40); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
						<div>
							<span style="font-family: var(--font-heading); font-size: var(--font-size-xs); color: var(--text-muted); text-transform: uppercase; display: block;"><?php esc_html_e( 'PARENT INTEL REPORT', 'ascendance' ); ?></span>
							<strong style="color: var(--color-deep-navy); font-family: var(--font-heading); font-size: var(--font-size-sm);"><?php echo esc_html( get_the_title( $parent_brief_id ) ); ?></strong>
						</div>
						<a href="<?php echo esc_url( get_permalink( $parent_brief_id ) ); ?>" class="btn btn-secondary" style="padding: 6px 16px; font-size: 12px;"><?php esc_html_e( 'Read Parent Brief', 'ascendance' ); ?></a>
					</div>
				<?php endif; ?>

				<!-- Main Content (Paywall Filtered) -->
				<div class="main-body-content" style="color: rgba(10, 22, 40, 0.85); font-family: var(--font-body);">
					<?php the_content(); ?>
				</div>

				<!-- Key Update Specific Content -->
				<?php if ( ! empty( $key_update ) && class_exists( 'Ascendance\Core\Paywall' ) && Ascendance\Core\Paywall::get_instance()->user_has_access() ) : ?>
					<div style="margin-top: var(--space-40); border-top: 1px solid rgba(10, 22, 40, 0.08); padding-top: var(--space-30);">
						<h3 style="color: var(--color-deep-navy); font-family: var(--font-heading); margin-bottom: var(--space-20); font-size: var(--font-size-md);"><i class="fa-solid fa-bell-concierge" style="color: var(--color-red); margin-right: 8px;"></i><?php esc_html_e( 'Critical Adjustments Ledger', 'ascendance' ); ?></h3>
						<div style="color: rgba(10, 22, 40, 0.85); font-size: var(--font-size-sm); line-height: 1.7;">
							<?php echo wp_kses_post( $key_update ); ?>
						</div>
					</div>
				<?php endif; ?>

			</div>
		</article>

	<?php endwhile; ?>

</main>

<?php
get_footer();
