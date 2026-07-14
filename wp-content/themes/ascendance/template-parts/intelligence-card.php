<?php
/**
 * Template Part: Intelligence Card
 *
 * Displays a single brief, update, or dossier card.
 * Expects $args to be passed via get_template_part():
 *   'post_id'   int     Post ID (defaults to current post in loop)
 *   'show_type' bool    Show CPT type label (default true)
 *
 * @package Ascendance
 */

$post_id    = $args['post_id'] ?? get_the_ID();
$show_type  = $args['show_type'] ?? true;
$post_type  = get_post_type( $post_id );
$permalink  = get_permalink( $post_id );
$title      = get_the_title( $post_id );
$date       = get_the_date( 'M j, Y', $post_id );
$excerpt    = get_the_excerpt( $post_id );

// ACF / meta
$tier_access = '';
$impact      = '';

if ( function_exists( 'get_field' ) ) {
	$tier_access = get_field( 'tier_access', $post_id ) ?: '';
	$impact      = get_field( 'impact_assessment', $post_id ) ?: '';
} else {
	$tier_access = get_post_meta( $post_id, 'tier_access', true ) ?: '';
	$impact      = get_post_meta( $post_id, 'impact_assessment', true ) ?: '';
}

// Taxonomy terms for data attributes (for JS filtering)
$topics  = wp_get_object_terms( $post_id, 'topic', array( 'fields' => 'slugs' ) );
$regions = wp_get_object_terms( $post_id, 'region', array( 'fields' => 'slugs' ) );
$topic_str  = is_array( $topics )  ? implode( ',', $topics )  : '';
$region_str = is_array( $regions ) ? implode( ',', $regions ) : '';
?>
<a href="<?php echo esc_url( $permalink ); ?>"
   class="intel-card reveal bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-6 rounded-sm shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 flex flex-col justify-between h-full"
   data-post-type="<?php echo esc_attr( $post_type ); ?>"
   data-topic="<?php echo esc_attr( $topic_str ); ?>"
   data-region="<?php echo esc_attr( $region_str ); ?>">

	<div>
		<div class="intel-card-badges flex flex-wrap gap-2 mb-4 items-center">
			<?php if ( $show_type ) : ?>
				<span class="intel-card-type text-[10px] font-sans font-bold uppercase tracking-wider text-brand-red"><?php echo esc_html( ascendance_cpt_label( $post_type ) ); ?></span>
			<?php endif; ?>

			<?php if ( $tier_access ) : ?>
				<?php echo ascendance_tier_badge( $tier_access ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php endif; ?>

			<?php if ( $impact ) : ?>
				<?php echo ascendance_impact_badge( $impact ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php endif; ?>
		</div>

		<h3 class="intel-card-title text-base md:text-lg font-sans font-bold text-brand-text-primary dark:text-white mb-3 line-clamp-2 leading-snug"><?php echo esc_html( $title ); ?></h3>

		<?php if ( $excerpt ) : ?>
			<p class="intel-card-excerpt text-sm text-brand-text-muted dark:text-cream/70 leading-relaxed mb-4 line-clamp-3 font-serif"><?php echo esc_html( $excerpt ); ?></p>
		<?php endif; ?>
	</div>

	<div class="intel-card-footer border-t border-brand-divider-light dark:border-brand-divider-dark/10 pt-4 text-xs font-sans text-brand-text-muted dark:text-cream/50 mt-auto flex justify-between items-center w-full">
		<span class="intel-card-date"><?php echo esc_html( $date ); ?></span>
		<span class="intel-card-cta font-bold text-brand-red hover:text-brand-red-light transition-colors duration-150 flex items-center gap-1">
			<?php esc_html_e( 'Access', 'ascendance' ); ?>
			<i class="fa-solid fa-arrow-right"></i>
		</span>
	</div>

</a>
