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
	if ( empty( $excerpt ) ) {
		$excerpt = get_field( 'executive_summary', $post_id ) ?: get_field( 'analytical_claim', $post_id ) ?: get_field( 'subhead', $post_id ) ?: get_field( 'public_excerpt', $post_id ) ?: '';
	}
	$tier_access = get_field( 'tier_access', $post_id ) ?: '';
	$impact      = get_field( 'impact_assessment', $post_id ) ?: '';
} else {
	$tier_access = get_post_meta( $post_id, 'tier_access', true ) ?: '';
	$impact      = get_post_meta( $post_id, 'impact_assessment', true ) ?: '';
}

// Taxonomy terms for data attributes (for JS filtering) and display
$topic_terms  = get_the_terms( $post_id, 'topic' );
$region_terms = get_the_terms( $post_id, 'region' );

$topic_str  = '';
$region_str = '';

if ( $topic_terms && ! is_wp_error( $topic_terms ) ) {
	$topic_slugs = wp_list_pluck( $topic_terms, 'slug' );
	$topic_str   = implode( ',', $topic_slugs );
}

if ( $region_terms && ! is_wp_error( $region_terms ) ) {
	$region_slugs = wp_list_pluck( $region_terms, 'slug' );
	$region_str   = implode( ',', $region_slugs );
}
?>
<a href="<?php echo esc_url( $permalink ); ?>"
   class="intel-card"
   data-post-type="<?php echo esc_attr( $post_type ); ?>"
   data-topic="<?php echo esc_attr( $topic_str ); ?>"
   data-region="<?php echo esc_attr( $region_str ); ?>">

	<div>
		<div class="intel-card-badges">
			<?php if ( $show_type ) : ?>
				<span class="intel-card-type"><?php echo esc_html( ascendance_cpt_label( $post_type ) ); ?></span>
			<?php endif; ?>

			<?php 
			if ( $topic_terms && ! is_wp_error( $topic_terms ) ) :
				foreach ( $topic_terms as $term ) :
			?>
				<span class="intel-card-type intel-card-topic" style="color:var(--ink-2); font-weight:500; border-color:var(--hairline); padding-left:0; border:none; letter-spacing:0; text-transform:none;">&middot; <?php echo esc_html( $term->name ); ?></span>
			<?php 
				endforeach;
			endif; 
			?>

			<?php if ( $tier_access ) : ?>
				<?php echo ascendance_tier_badge( $tier_access ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php endif; ?>

			<?php if ( $impact ) : ?>
				<?php echo ascendance_impact_badge( $impact ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php endif; ?>
		</div>

		<h3 class="intel-card-title"><?php echo esc_html( $title ); ?></h3>

		<?php if ( $excerpt ) : ?>
			<p class="intel-card-excerpt"><?php echo esc_html( wp_trim_words( $excerpt, 22 ) ); ?></p>
		<?php endif; ?>
	</div>

	<div class="intel-card-footer">
		<span class="intel-card-date"><?php echo esc_html( $date ); ?></span>
		<span class="intel-card-cta">
			<?php esc_html_e( 'Access', 'ascendance' ); ?> &rarr;
		</span>
	</div>

</a>
