<?php
/**
 * Template Part: Article Tags & Topics
 * Displays the taxonomy terms (Topics, Regions, Tags) for the current post.
 *
 * @package Ascendance
 */

$post_id = get_the_ID();
if ( ! $post_id ) return;

$topics  = get_the_terms( $post_id, 'topic' );
$regions = get_the_terms( $post_id, 'region' );
$tags    = get_the_tags( $post_id );

$has_terms = ( $topics && ! is_wp_error( $topics ) ) || ( $regions && ! is_wp_error( $regions ) ) || ( $tags && ! is_wp_error( $tags ) );

if ( ! $has_terms ) return;
?>

<div class="as-article-taxonomies">
	<span class="as-tax-label">Filed under:</span>
	<div class="as-tax-chips">
		<?php
		if ( $topics && ! is_wp_error( $topics ) ) {
			foreach ( $topics as $term ) {
				echo '<a href="' . esc_url( home_url( '/intelligence/?topic=' . $term->slug ) ) . '" class="as-chip as-chip-topic">' . esc_html( $term->name ) . '</a>';
			}
		}
		if ( $regions && ! is_wp_error( $regions ) ) {
			foreach ( $regions as $term ) {
				echo '<a href="' . esc_url( home_url( '/intelligence/?region=' . $term->slug ) ) . '" class="as-chip as-chip-region">' . esc_html( $term->name ) . '</a>';
			}
		}
		if ( $tags && ! is_wp_error( $tags ) ) {
			foreach ( $tags as $term ) {
				echo '<a href="' . esc_url( get_tag_link( $term->term_id ) ) . '" class="as-chip as-chip-tag">#' . esc_html( $term->name ) . '</a>';
			}
		}
		?>
	</div>
</div>

<style>
.as-article-taxonomies {
	margin: 40px 0 20px 0;
	padding-top: 24px;
	border-top: 1px solid var(--hairline, #ddd);
	display: flex;
	flex-direction: column;
	gap: 12px;
}
.as-tax-label {
	font-family: var(--font-mono, monospace);
	font-size: 11px;
	font-weight: 600;
	color: var(--ink-3, #666);
	text-transform: uppercase;
	letter-spacing: 0.08em;
}
.as-tax-chips {
	display: flex;
	flex-wrap: wrap;
	gap: 8px;
}
.as-chip {
	display: inline-flex;
	align-items: center;
	padding: 6px 12px;
	background: var(--surface-2, #f5f5f5);
	color: var(--ink-2, #333);
	font-size: 12px;
	font-weight: 500;
	border-radius: 40px;
	text-decoration: none;
	transition: background 0.15s, color 0.15s;
}
.as-chip:hover {
	background: var(--red, #c52225);
	color: #fff;
}
.as-chip-tag {
	background: transparent;
	border: 1px solid var(--hairline, #ddd);
}
.as-chip-tag:hover {
	border-color: var(--red, #c52225);
}
</style>
