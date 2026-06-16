<?php
/**
 * Template Name: Industries
 * Page template for the Industries/Sectors page.
 *
 * @package Ascendance
 */

get_header();

$sectors = array(
	array(
		'icon'   => 'fa-earth-americas',
		'title'  => 'Geopolitics & Diplomacy',
		'slug'   => 'geopolitics',
		'topic'  => 'Geopolitics',
		'desc'   => 'Interstate relations, multilateral frameworks, alliance architecture, territorial disputes, and the strategic competition between major powers.',
		'tags'   => array( 'SIGINT', 'Diplomatic Intelligence', 'Military Affairs' ),
	),
	array(
		'icon'   => 'fa-chart-line',
		'title'  => 'Economics & Markets',
		'slug'   => 'economics',
		'topic'  => 'Economics & Markets',
		'desc'   => 'Trade policy, sanctions regimes, sovereign debt dynamics, currency risk, commodity markets, and the intersection of economic policy with geopolitical strategy.',
		'tags'   => array( 'Economic Intelligence', 'Financial Intelligence', 'Emerging Markets' ),
	),
	array(
		'icon'   => 'fa-microchip',
		'title'  => 'Technology & AI',
		'slug'   => 'technology',
		'topic'  => 'Technology & AI',
		'desc'   => 'AI regulation divergence, semiconductor supply chains, cyber threat landscapes, platform governance, and the weaponisation of technology in strategic competition.',
		'tags'   => array( 'Technology Risk', 'Regulatory Risk', 'OSINT' ),
	),
	array(
		'icon'   => 'fa-bolt',
		'title'  => 'Energy & Resources',
		'slug'   => 'energy',
		'topic'  => 'Energy & Resources',
		'desc'   => 'Energy security, LNG market dynamics, renewable transition risk, critical mineral supply chains, and the geopolitics of climate policy.',
		'tags'   => array( 'Critical Infrastructure', 'Political Risk', 'Economic Intelligence' ),
	),
	array(
		'icon'   => 'fa-shield-halved',
		'title'  => 'Security & Defence',
		'slug'   => 'security',
		'topic'  => 'Security & Defence',
		'desc'   => 'Military modernisation, theatre balance dynamics, hybrid warfare, sub-threshold coercion, and the evolution of deterrence doctrine across major military powers.',
		'tags'   => array( 'Military Affairs', 'SIGINT', 'HUMINT' ),
	),
	array(
		'icon'   => 'fa-scale-balanced',
		'title'  => 'Governance & Policy',
		'slug'   => 'governance',
		'topic'  => 'Governance & Policy',
		'desc'   => 'Sanctions policy, regulatory frameworks, democratic backsliding risk, institutional stability assessments, and the political economy of policy change.',
		'tags'   => array( 'Regulatory Risk', 'Political Risk', 'Diplomatic Intelligence' ),
	),
);
?>

<main id="primary" class="site-main">

	<!-- ═══ PAGE HERO ═════════════════════════════════════════ -->
	<section class="page-hero">
		<div class="container">
			<div class="page-hero-inner">
				<p class="page-hero-eyebrow"><?php esc_html_e( '// Strategic Sectors', 'ascendance' ); ?></p>
				<h1 class="page-hero-title"><?php esc_html_e( 'Intelligence Across Six Sectors', 'ascendance' ); ?></h1>
				<p class="page-hero-desc"><?php esc_html_e( 'Ascendance covers the six domains where strategic risk is highest and decision-making demands the most rigorous forward-looking analysis.', 'ascendance' ); ?></p>
			</div>
		</div>
	</section>

	<!-- ═══ SECTORS GRID ══════════════════════════════════════ -->
	<section class="industry-section section-lg" id="sectors">
		<div class="container">
			<div class="industry-grid">
				<?php foreach ( $sectors as $sector ) :
					// Find the topic term for this sector
					$topic_term = get_term_by( 'name', $sector['topic'], 'topic' );
					$archive_url = $topic_term
						? get_term_link( $topic_term, 'topic' )
						: home_url( '/intelligence/' );
					if ( is_wp_error( $archive_url ) ) $archive_url = home_url( '/intelligence/' );
				?>
					<a href="<?php echo esc_url( $archive_url ); ?>" class="industry-card reveal">
						<div class="industry-card-icon">
							<i class="fa-solid <?php echo esc_attr( $sector['icon'] ); ?>"></i>
						</div>
						<h3><?php echo esc_html( $sector['title'] ); ?></h3>
						<p><?php echo esc_html( $sector['desc'] ); ?></p>
						<div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:var(--space-2);">
							<?php foreach ( $sector['tags'] as $tag ) : ?>
								<span style="font-family:var(--font-heading);font-size:0.65rem;color:rgba(188,27,29,0.8);background:rgba(188,27,29,0.08);border:1px solid rgba(188,27,29,0.15);padding:2px 8px;border-radius:2px;">
									<?php echo esc_html( $tag ); ?>
								</span>
							<?php endforeach; ?>
						</div>
						<div class="industry-card-link">
							<?php esc_html_e( 'View Intelligence', 'ascendance' ); ?>
							<i class="fa-solid fa-arrow-right"></i>
						</div>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- ═══ RECENT CROSS-SECTOR INTELLIGENCE ══════════════════ -->
	<section class="section bg-navy-mid">
		<div class="container">
			<div class="section-header">
				<span class="section-eyebrow"><?php esc_html_e( 'Cross-Sector', 'ascendance' ); ?></span>
				<h2 class="section-title reveal"><?php esc_html_e( 'Recent Intelligence Across Sectors', 'ascendance' ); ?></h2>
			</div>
			<div class="intel-grid">
				<?php
				$cross_sector = new WP_Query( array(
					'post_type'      => array( 'brief', 'dossier' ),
					'posts_per_page' => 3,
					'post_status'    => 'publish',
					'orderby'        => 'date',
					'order'          => 'DESC',
				) );

				if ( $cross_sector->have_posts() ) :
					while ( $cross_sector->have_posts() ) :
						$cross_sector->the_post();
						get_template_part( 'template-parts/intelligence-card', null, array( 'post_id' => get_the_ID() ) );
					endwhile;
					wp_reset_postdata();
				endif;
				?>
			</div>
		</div>
	</section>

	<?php get_template_part( 'template-parts/cta-strip', null, array(
		'heading'   => __( 'Intelligence Calibrated to Your Sector', 'ascendance' ),
		'body'      => __( 'Use topic and region filters in the Intelligence Hub to surface exactly the analysis relevant to your decision context.', 'ascendance' ),
		'btn_label' => __( 'Open Intelligence Hub', 'ascendance' ),
		'btn_url'   => home_url( '/intelligence/' ),
	) ); ?>

</main>

<?php get_footer(); ?>
