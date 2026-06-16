<?php
/**
 * Template Name: Intelligence Hub
 * Filterable archive of all intelligence content types.
 *
 * @package Ascendance
 */

get_header();

// Collect filter options from taxonomies
$topics  = get_terms( array( 'taxonomy' => 'topic', 'hide_empty' => true, 'orderby' => 'name', 'order' => 'ASC' ) );
$regions = get_terms( array( 'taxonomy' => 'region', 'hide_empty' => true, 'orderby' => 'name', 'order' => 'ASC' ) );

// Query ALL intelligence content
$hub_query = new WP_Query( array(
	'post_type'      => array( 'brief', 'update', 'dossier' ),
	'posts_per_page' => 24,
	'post_status'    => 'publish',
	'orderby'        => 'date',
	'order'          => 'DESC',
) );
?>

<main id="primary" class="site-main">

	<!-- ═══ PAGE HERO ═════════════════════════════════════════ -->
	<section class="page-hero">
		<div class="container">
			<div class="page-hero-inner">
				<p class="page-hero-eyebrow"><?php esc_html_e( '// Intelligence Ledger', 'ascendance' ); ?></p>
				<h1 class="page-hero-title"><?php esc_html_e( 'The Intelligence Hub', 'ascendance' ); ?></h1>
				<p class="page-hero-desc"><?php esc_html_e( 'Browse all Intelligence Briefs, Dynamic Updates, and Strategic Dossiers. Filter by content type, topic, and region.', 'ascendance' ); ?></p>
			</div>
		</div>
	</section>

	<!-- ═══ FILTER BAR + GRID ═════════════════════════════════ -->
	<section class="intel-hub-section section-lg">
		<div class="container">

			<!-- Filter Bar -->
			<div class="filter-bar">
				<div class="filter-tabs" id="intel-type-tabs" role="tablist">
					<button class="filter-tab active" data-type="all" role="tab"><?php esc_html_e( 'All', 'ascendance' ); ?></button>
					<button class="filter-tab" data-type="brief" role="tab"><?php esc_html_e( 'Briefs', 'ascendance' ); ?></button>
					<button class="filter-tab" data-type="update" role="tab"><?php esc_html_e( 'Updates', 'ascendance' ); ?></button>
					<button class="filter-tab" data-type="dossier" role="tab"><?php esc_html_e( 'Dossiers', 'ascendance' ); ?></button>
				</div>

				<div class="filter-selects">
					<!-- Topic filter -->
					<select class="filter-select" id="filter-topic" data-taxonomy="topic" aria-label="<?php esc_attr_e( 'Filter by Topic', 'ascendance' ); ?>">
						<option value=""><?php esc_html_e( 'All Topics', 'ascendance' ); ?></option>
						<?php if ( ! is_wp_error( $topics ) && ! empty( $topics ) ) :
							foreach ( $topics as $topic ) : ?>
								<option value="<?php echo esc_attr( $topic->slug ); ?>"><?php echo esc_html( $topic->name ); ?></option>
							<?php endforeach;
						endif; ?>
					</select>

					<!-- Region filter -->
					<select class="filter-select" id="filter-region" data-taxonomy="region" aria-label="<?php esc_attr_e( 'Filter by Region', 'ascendance' ); ?>">
						<option value=""><?php esc_html_e( 'All Regions', 'ascendance' ); ?></option>
						<?php if ( ! is_wp_error( $regions ) && ! empty( $regions ) ) :
							foreach ( $regions as $region ) : ?>
								<option value="<?php echo esc_attr( $region->slug ); ?>"><?php echo esc_html( $region->name ); ?></option>
							<?php endforeach;
						endif; ?>
					</select>
				</div>
			</div>

			<!-- Intelligence Grid -->
			<div class="intel-grid" id="intel-hub-grid">
				<?php
				if ( $hub_query->have_posts() ) :
					while ( $hub_query->have_posts() ) :
						$hub_query->the_post();
						get_template_part( 'template-parts/intelligence-card', null, array( 'post_id' => get_the_ID() ) );
					endwhile;
					wp_reset_postdata();
				else :
				?>
					<div style="grid-column:1/-1;text-align:center;padding:4rem 0;">
						<i class="fa-solid fa-database" style="font-size:2.5rem;color:rgba(188,27,29,0.4);margin-bottom:1rem;display:block;"></i>
						<p style="font-family:var(--font-heading);color:rgba(247,244,239,0.4);">
							<?php esc_html_e( 'No intelligence published yet. Check back soon.', 'ascendance' ); ?>
						</p>
					</div>
				<?php endif; ?>
			</div>

			<!-- Pagination -->
			<?php if ( $hub_query->max_num_pages > 1 ) : ?>
				<div style="display:flex;justify-content:center;gap:var(--space-3);margin-top:var(--space-8);">
					<?php
					echo paginate_links( array(
						'total'     => $hub_query->max_num_pages,
						'prev_text' => '<i class="fa-solid fa-arrow-left"></i>',
						'next_text' => '<i class="fa-solid fa-arrow-right"></i>',
					) );
					?>
				</div>
			<?php endif; ?>

		</div>
	</section>

	<?php get_template_part( 'template-parts/cta-strip', null, array(
		'heading'   => __( 'Unlock the Full Intelligence Library', 'ascendance' ),
		'body'      => __( 'Professional and Enterprise subscribers get unlimited access to all briefs, updates, dossiers, and the full archives.', 'ascendance' ),
		'btn_label' => __( 'See Pricing', 'ascendance' ),
		'btn_url'   => home_url( '/services/' ),
		'btn2_label' => __( 'Subscribe Free', 'ascendance' ),
		'btn2_url'  => home_url( '/newsletter/' ),
	) ); ?>

</main>

<?php get_footer(); ?>
