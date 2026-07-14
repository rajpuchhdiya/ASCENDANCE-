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

	<!-- ═══ PAGE HERO ═════════════════════════════════════════ -->
	<section class="page-hero bg-navy-deep text-white py-16 md:py-24 border-b border-brand-divider-dark">
		<div class="container mx-auto px-6 md:px-8">
			<div class="page-hero-inner">
				<p class="page-hero-eyebrow text-xs font-mono uppercase tracking-widest text-brand-red mb-4 block"><?php esc_html_e( '// Intelligence Ledger', 'ascendance' ); ?></p>
				<h1 class="page-hero-title text-3xl md:text-5xl font-sans font-bold leading-tight mb-6"><?php esc_html_e( 'The Intelligence Hub', 'ascendance' ); ?></h1>
				<p class="page-hero-desc text-base md:text-lg text-cream/80 max-w-[720px] leading-relaxed"><?php esc_html_e( 'Browse all Intelligence Briefs, Dynamic Updates, and Strategic Dossiers. Filter by content type, topic, and region.', 'ascendance' ); ?></p>
			</div>
		</div>
	</section>

	<!-- ═══ FILTER BAR + GRID ═════════════════════════════════ -->
	<section class="intel-hub-section section py-20 bg-cream dark:bg-navy-deep border-b border-brand-divider-light dark:border-brand-divider-dark">
		<div class="container mx-auto px-6 md:px-8">

			<!-- Filter Bar -->
			<div class="filter-bar flex flex-col md:flex-row justify-between items-center gap-6 mb-8 border-b border-brand-divider-light dark:border-brand-divider-dark/20 pb-6">
				<div class="filter-tabs flex gap-2" id="intel-type-tabs" role="tablist">
					<button class="filter-tab px-4 py-2 text-xs font-sans font-bold uppercase tracking-wider border border-brand-divider-light dark:border-brand-divider-dark rounded-sm text-brand-text-muted dark:text-cream/50 bg-white dark:bg-navy-mid hover:border-brand-red hover:text-brand-red dark:hover:text-brand-red-light transition-all duration-150 active" data-type="all" role="tab"><?php esc_html_e( 'All',      'ascendance' ); ?></button>
					<button class="filter-tab px-4 py-2 text-xs font-sans font-bold uppercase tracking-wider border border-brand-divider-light dark:border-brand-divider-dark rounded-sm text-brand-text-muted dark:text-cream/50 bg-white dark:bg-navy-mid hover:border-brand-red hover:text-brand-red dark:hover:text-brand-red-light transition-all duration-150" data-type="brief" role="tab"><?php esc_html_e( 'Briefs',   'ascendance' ); ?></button>
					<button class="filter-tab px-4 py-2 text-xs font-sans font-bold uppercase tracking-wider border border-brand-divider-light dark:border-brand-divider-dark rounded-sm text-brand-text-muted dark:text-cream/50 bg-white dark:bg-navy-mid hover:border-brand-red hover:text-brand-red dark:hover:text-brand-red-light transition-all duration-150" data-type="update" role="tab"><?php esc_html_e( 'Updates',  'ascendance' ); ?></button>
					<button class="filter-tab px-4 py-2 text-xs font-sans font-bold uppercase tracking-wider border border-brand-divider-light dark:border-brand-divider-dark rounded-sm text-brand-text-muted dark:text-cream/50 bg-white dark:bg-navy-mid hover:border-brand-red hover:text-brand-red dark:hover:text-brand-red-light transition-all duration-150" data-type="dossier" role="tab"><?php esc_html_e( 'Dossiers', 'ascendance' ); ?></button>
				</div>

				<div class="filter-selects flex gap-4 w-full md:w-auto">
					<!-- Topic filter -->
					<select class="filter-select px-4 py-2.5 bg-white dark:bg-navy-deep border border-brand-divider-light dark:border-brand-divider-dark rounded-sm text-brand-text-primary dark:text-cream font-sans text-xs outline-none transition-all duration-150 focus:border-brand-red w-full md:w-48" id="filter-topic" data-taxonomy="topic" aria-label="<?php esc_attr_e( 'Filter by Topic', 'ascendance' ); ?>">
						<option value=""><?php esc_html_e( 'All Topics', 'ascendance' ); ?></option>
						<?php if ( ! is_wp_error( $topics ) && ! empty( $topics ) ) :
							foreach ( $topics as $topic ) : ?>
								<option value="<?php echo esc_attr( $topic->slug ); ?>"><?php echo esc_html( $topic->name ); ?></option>
						<?php endforeach;
						endif; ?>
					</select>

					<!-- Region filter -->
					<select class="filter-select px-4 py-2.5 bg-white dark:bg-navy-deep border border-brand-divider-light dark:border-brand-divider-dark rounded-sm text-brand-text-primary dark:text-cream font-sans text-xs outline-none transition-all duration-150 focus:border-brand-red w-full md:w-48" id="filter-region" data-taxonomy="region" aria-label="<?php esc_attr_e( 'Filter by Region', 'ascendance' ); ?>">
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
			<div class="intel-results-bar mb-6 text-xs text-brand-text-muted dark:text-cream/50 font-sans" id="intel-results-bar">
				<span class="intel-results-count" id="intel-results-count">
					<?php
					/* translators: %d = number of matching results */
					printf( esc_html__( '%d results', 'ascendance' ), $hub_query->found_posts );
					?>
				</span>
			</div>

			<!-- Intelligence Grid (AJAX target) -->
			<div class="intel-grid-wrapper relative">
				<div class="intel-loading absolute inset-0 bg-cream/70 dark:bg-navy-deep/70 z-20 flex justify-center items-center hidden" id="intel-loading" aria-hidden="true">
					<div class="intel-loading-spinner w-8 h-8 border-2 border-brand-red border-t-transparent rounded-full animate-spin"></div>
				</div>

				<div class="intel-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="intel-hub-grid">
					<?php
					if ( $hub_query->have_posts() ) :
						while ( $hub_query->have_posts() ) :
							$hub_query->the_post();
							get_template_part( 'template-parts/intelligence-card', null, array( 'post_id' => get_the_ID() ) );
						endwhile;
						wp_reset_postdata();
					else :
					?>
						<div class="col-span-full text-center py-16 flex flex-col items-center gap-4">
							<i class="fa-solid fa-database text-4xl text-brand-red mb-2"></i>
							<p class="text-sm text-brand-text-muted dark:text-cream/50 font-sans">
								<?php esc_html_e( 'No intelligence published yet. Check back soon.', 'ascendance' ); ?>
							</p>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<!-- Pagination (AJAX target) -->
			<div class="archive-pagination mt-12 flex justify-center" id="intel-pagination">
				<?php if ( $hub_query->max_num_pages > 1 ) : ?>
					<?php
					echo paginate_links( array(
						'current'   => 1,
						'total'     => $hub_query->max_num_pages,
						'prev_text' => '<i class="fa-solid fa-arrow-left"></i>',
						'next_text' => '<i class="fa-solid fa-arrow-right"></i>',
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

<?php get_footer(); ?>
