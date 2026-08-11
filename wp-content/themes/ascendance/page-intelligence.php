<?php
/**
 * Template Name: Intelligence Hub
 * Filterable archive of all intelligence content types (AJAX-powered).
 *
 * @package Ascendance
 */

get_header();

// Collect filter options from taxonomies for the dropdowns
$topics  = get_terms( array( 'taxonomy' => 'topic',  'hide_empty' => true, 'orderby' => 'name', 'order' => 'ASC' ) );
$regions = get_terms( array( 'taxonomy' => 'region', 'hide_empty' => true, 'orderby' => 'name', 'order' => 'ASC' ) );

// Initial load: show first page of all posts (AJAX takes over on filter change)
$hub_query = new WP_Query( array(
	'post_type'      => array( 'brief', 'update', 'dossier' ),
	'posts_per_page' => 12,
	'paged'          => 1,
	'post_status'    => 'publish',
	'orderby'        => 'date',
	'order'          => 'DESC',
) );
?>

<main id="primary" class="site-main">

	<!-- PAGE HERO -->
	<section class="intel-hero">
		<div class="wrap">
			<span class="kicker">Intelligence Ledger</span>
			<h1>The Intelligence Hub</h1>
			<p>Browse all Intelligence Briefs, Dynamic Updates, and Strategic Dossiers. Filter by content type, topic, and region.</p>
		</div>
	</section>

	<!-- FILTER BAR + GRID -->
	<section class="intel-hub-section">
		<div class="wrap">

			<!-- Filter Bar -->
			<div class="filter-bar">
				<div class="filter-tabs" id="intel-type-tabs" role="tablist">
					<button type="button" class="filter-tab active" data-type="all" role="tab"><?php esc_html_e( 'All',      'ascendance' ); ?></button>
					<button type="button" class="filter-tab" data-type="brief" role="tab"><?php esc_html_e( 'Briefs',   'ascendance' ); ?></button>
					<button type="button" class="filter-tab" data-type="update" role="tab"><?php esc_html_e( 'Updates',  'ascendance' ); ?></button>
					<button type="button" class="filter-tab" data-type="dossier" role="tab"><?php esc_html_e( 'Dossiers', 'ascendance' ); ?></button>
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

			<!-- Results count -->
			<div class="intel-results-bar" id="intel-results-bar">
				<span class="intel-results-count" id="intel-results-count">
					<?php
					printf( esc_html__( '%d results', 'ascendance' ), $hub_query->found_posts );
					?>
				</span>
			</div>

			<!-- Intelligence Grid (AJAX target) -->
			<div class="intel-grid-wrapper" style="position:relative;">
				<div class="intel-loading" id="intel-loading" style="display:none; position:absolute; inset:0; background:rgba(250,248,243,0.7); z-index:20; justify-content:center; align-items:center;">
					<div class="intel-loading-spinner" style="width:32px; height:32px; border:2px solid var(--red); border-top-color:transparent; border-radius:50%; animation:spin 0.8s linear infinite;"></div>
				</div>

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
						<div style="grid-column:1/-1; text-align:center; padding:48px 0; color:var(--ink-3);">
							<p><?php esc_html_e( 'No intelligence published yet. Check back soon.', 'ascendance' ); ?></p>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<!-- Pagination (AJAX target) -->
			<div class="archive-pagination" id="intel-pagination" style="margin-top:40px; display:flex; justify-content:center; gap:8px;">
				<?php if ( $hub_query->max_num_pages > 1 ) : ?>
					<?php
					echo paginate_links( array(
						'current'   => 1,
						'total'     => $hub_query->max_num_pages,
						'prev_text' => '&larr;',
						'next_text' => '&rarr;',
						'type'      => 'plain',
					) );
					?>
				<?php endif; ?>
			</div>

		</div>
	</section>

	<?php get_template_part( 'template-parts/cta-strip', null, array(
		'heading'    => __( 'Unlock the Full Intelligence Library', 'ascendance' ),
		'body'       => __( 'Professional and Enterprise subscribers get unlimited access to all briefs, updates, dossiers, and the full archives.', 'ascendance' ),
		'btn_label'  => __( 'See Pricing', 'ascendance' ),
		'btn_url'    => function_exists( 'pmpro_url' ) ? pmpro_url( 'levels' ) : home_url( '/membership-levels/' ),
		'btn2_label' => __( 'Subscribe Free', 'ascendance' ),
		'btn2_url'   => home_url( '/newsletter/' ),
	) ); ?>

</main>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>

<?php get_footer(); ?>
