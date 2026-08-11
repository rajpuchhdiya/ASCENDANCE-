<?php
/**
 * The template for displaying Entity CPT Archives
 *
 * @package Ascendance
 */

get_header();

$search_q    = isset( $_GET['q'] ) ? sanitize_text_field( $_GET['q'] ) : ( get_search_query() ?: '' );
$type_filter = isset( $_GET['type'] ) ? sanitize_text_field( $_GET['type'] ) : '';

$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

$args = array(
    'post_type'      => 'entity',
    'post_status'    => 'publish',
    'posts_per_page' => 12,
    'paged'          => $paged,
    'orderby'        => 'title',
    'order'          => 'ASC',
);

if ( ! empty( $search_q ) ) {
    $args['s'] = $search_q;
}

if ( ! empty( $type_filter ) ) {
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'entity_type',
            'field'    => 'slug',
            'terms'    => $type_filter,
        ),
    );
}

$entity_query = new WP_Query( $args );
?>

<main id="primary" class="as-page-wrap">

	<!-- Page Hero Band -->
	<section class="as-page-hero">
		<div class="as-page-hero-inner">
			<p class="as-page-eyebrow">// <?php esc_html_e( 'Intelligence Database', 'ascendance' ); ?></p>
			<h1 class="as-page-title"><?php esc_html_e( 'Entity Intelligence Profiles', 'ascendance' ); ?></h1>
			<p class="as-page-desc"><?php esc_html_e( 'Interconnected data graph of commercial entities, mining concessions, infrastructure corridors, and regulatory institutions across Central Africa.', 'ascendance' ); ?></p>
		</div>
	</section>

	<div class="as-page-body" style="max-width:1140px; margin:0 auto; padding:32px 20px;">
		<!-- Search & Filter Bar -->
		<form method="GET" action="<?php echo esc_url( home_url( '/entities/' ) ); ?>" style="display:flex; flex-wrap:wrap; gap:12px; margin-bottom:32px; background:#fff; padding:16px; border:1px solid var(--hairline, #eee); border-radius:4px;">
			<div style="flex:1; min-width:240px;">
				<input type="text" name="q" value="<?php echo esc_attr( $search_q ); ?>" placeholder="Search entity name, official name, or alias (e.g. CMOC, Tenke)..." style="width:100%; padding:8px 12px; font-size:13px; border:1px solid var(--hairline, #ccc); border-radius:3px;">
			</div>
			<div>
				<select name="type" style="padding:8px 12px; font-size:13px; border:1px solid var(--hairline, #ccc); border-radius:3px;">
					<option value="">All Entity Types</option>
					<?php
					$e_types = get_terms( array( 'taxonomy' => 'entity_type', 'hide_empty' => false ) );
					if ( ! is_wp_error( $e_types ) && ! empty( $e_types ) ) {
						foreach ( $e_types as $et ) {
							echo '<option value="' . esc_attr( $et->slug ) . '" ' . selected( $type_filter, $et->slug, false ) . '>' . esc_html( $et->name ) . '</option>';
						}
					}
					?>
				</select>
			</div>
			<button type="submit" class="btn btn-primary" style="padding:8px 16px; font-size:13px; background:var(--accent, #BC1B1D); color:#fff; border:none; border-radius:3px; cursor:pointer;">
				Search Entities
			</button>
		</form>

		<!-- Entity Directory Grid -->
		<?php if ( $entity_query->have_posts() ) : ?>
			<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:20px;">
				<?php while ( $entity_query->have_posts() ) : $entity_query->the_post();
					$e_id      = get_the_ID();
					$types     = wp_get_post_terms( $e_id, 'entity_type', array( 'fields' => 'names' ) );
					$type_lbl  = ( ! is_wp_error( $types ) && ! empty( $types ) ) ? $types[0] : 'Entity';
					$off_name  = get_post_meta( $e_id, 'official_name', true );
					$country   = get_post_meta( $e_id, 'country', true );
					$status    = get_post_meta( $e_id, 'entity_status', true ) ?: 'active';
				?>
				<article style="background:#fff; border:1px solid var(--hairline, #eee); border-radius:4px; padding:20px; display:flex; flex-direction:column; justify-space-between;">
					<div>
						<div style="display:flex; justify-content:space-between; align-items:center; font-size:10px; font-family:var(--font-mono); text-transform:uppercase; color:var(--ink-3); margin-bottom:8px;">
							<span style="font-weight:bold; color:var(--accent, #BC1B1D);"><?php echo esc_html( $type_lbl ); ?></span>
							<span style="color:#27AE60; font-weight:bold;">● <?php echo esc_html( ucfirst( $status ) ); ?></span>
						</div>

						<h2 style="font-size:18px; font-weight:700; margin:0 0 6px 0;">
							<a href="<?php the_permalink(); ?>" style="color:inherit; text-decoration:none;">
								<?php the_title(); ?>
							</a>
						</h2>

						<?php if ( ! empty( $off_name ) && 0 !== strcasecmp( $off_name, get_the_title() ) ) : ?>
							<div style="font-size:12px; color:var(--ink-2); margin-bottom:8px; font-style:italic;">
								<?php echo esc_html( $off_name ); ?>
							</div>
						<?php endif; ?>

						<p style="font-size:13px; color:var(--ink-2); line-height:1.5; margin:0 0 16px 0;">
							<?php echo esc_html( wp_trim_words( get_the_excerpt(), 18, '...' ) ); ?>
						</p>
					</div>

					<div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--hairline, #f0f0f0); padding-top:12px; margin-top:auto;">
						<span style="font-size:11px; font-family:var(--font-mono); color:var(--ink-3);">
							<?php echo ! empty( $country ) ? esc_html( $country ) : 'Central Africa'; ?>
						</span>
						<a href="<?php the_permalink(); ?>" class="btn btn-ghost" style="font-size:11px; padding:4px 10px;">
							View Profile &rarr;
						</a>
					</div>
				</article>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>

			<div style="margin-top:32px; text-align:center;">
				<?php
				echo paginate_links( array(
					'total'   => $entity_query->max_num_pages,
					'current' => $paged,
				) );
				?>
			</div>

		<?php else : ?>
			<div style="background:#fff; border:1px solid var(--hairline, #eee); padding:40px; text-align:center; border-radius:4px;">
				<p style="margin:0 0 12px 0; color:var(--ink-2); font-size:15px;">No entities found matching your search criteria.</p>
				<a href="<?php echo esc_url( home_url( '/entities/' ) ); ?>" class="btn btn-ghost" style="font-size:12px;">Reset Search &rarr;</a>
			</div>
		<?php endif; ?>
	</div>

</main>

<?php
get_footer();
