<?php
/**
 * Front Page — Ascendance Intelligence Platform
 *
 * Hero → Live Brief Feed → Platform Pillars → Membership Tiers → Stats → Newsletter CTA
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="site-main">

	<!-- ═══ HERO ══════════════════════════════════════════════ -->
	<section class="hero-section" id="hero">
		<div class="container">
			<span class="hero-eyebrow"><?php esc_html_e( 'Intelligence Platform', 'ascendance' ); ?></span>

			<h1 class="hero-title">
				<?php esc_html_e( 'Strategic Intelligence for', 'ascendance' ); ?><br>
				<span class="accent"><?php esc_html_e( 'Decision Makers', 'ascendance' ); ?></span>
			</h1>

			<p class="hero-subtitle">
				<?php esc_html_e( 'Premium geopolitical, economic, and technology intelligence delivered weekly. Briefs, dynamic updates, and deep-dive dossiers calibrated to your membership tier.', 'ascendance' ); ?>
			</p>

			<div class="hero-ctas">
				<a href="<?php echo esc_url( home_url( '/newsletter/' ) ); ?>" class="btn btn-primary" id="hero-cta-subscribe">
					<?php esc_html_e( 'Subscribe Free', 'ascendance' ); ?>
				</a>
				<a href="<?php echo esc_url( home_url( '/intelligence/' ) ); ?>" class="btn btn-secondary" id="hero-cta-explore">
					<?php esc_html_e( 'Browse Intelligence', 'ascendance' ); ?>
				</a>
			</div>

			<div class="hero-stats">
				<div class="hero-stat-item">
					<span class="hero-stat-number">
						<?php
						// Live brief count
						$brief_count = wp_count_posts( 'brief' )->publish;
						echo esc_html( $brief_count > 0 ? $brief_count . '+' : '50+' );
						?>
					</span>
					<span class="hero-stat-label"><?php esc_html_e( 'Intelligence Briefs', 'ascendance' ); ?></span>
				</div>
				<div class="hero-stat-item">
					<span class="hero-stat-number">3</span>
					<span class="hero-stat-label"><?php esc_html_e( 'Membership Tiers', 'ascendance' ); ?></span>
				</div>
				<div class="hero-stat-item">
					<span class="hero-stat-number">6</span>
					<span class="hero-stat-label"><?php esc_html_e( 'Strategic Sectors', 'ascendance' ); ?></span>
				</div>
				<div class="hero-stat-item">
					<span class="hero-stat-number">40+</span>
					<span class="hero-stat-label"><?php esc_html_e( 'Countries Covered', 'ascendance' ); ?></span>
				</div>
			</div>
		</div>
	</section>

	<!-- ═══ LIVE INTELLIGENCE FEED ════════════════════════════ -->
	<section class="section bg-navy-mid" id="latest-intelligence">
		<div class="container">
			<div class="section-header">
				<span class="section-eyebrow"><?php esc_html_e( 'Live Feed', 'ascendance' ); ?></span>
				<h2 class="section-title reveal"><?php esc_html_e( 'Latest Intelligence', 'ascendance' ); ?></h2>
				<p class="section-lead reveal reveal-delay-1"><?php esc_html_e( 'The most recent briefs, updates, and dossiers from the Ascendance intelligence ledger.', 'ascendance' ); ?></p>
			</div>

			<div class="intel-grid">
				<?php
				$live_feed = new WP_Query( array(
					'post_type'      => array( 'brief', 'update', 'dossier' ),
					'posts_per_page' => 6,
					'post_status'    => 'publish',
					'orderby'        => 'date',
					'order'          => 'DESC',
				) );

				if ( $live_feed->have_posts() ) :
					while ( $live_feed->have_posts() ) :
						$live_feed->the_post();
						get_template_part( 'template-parts/intelligence-card', null, array( 'post_id' => get_the_ID() ) );
					endwhile;
					wp_reset_postdata();
				else :
					echo '<p style="color:rgba(247,244,239,0.4);font-family:var(--font-heading);font-size:0.9rem;">';
					esc_html_e( 'No intelligence content published yet. Check back soon.', 'ascendance' );
					echo '</p>';
				endif;
				?>
			</div>

			<div style="text-align:center;margin-top:var(--space-8);">
				<a href="<?php echo esc_url( home_url( '/intelligence/' ) ); ?>" class="btn btn-secondary" id="home-view-all">
					<?php esc_html_e( 'View All Intelligence', 'ascendance' ); ?>
					<i class="fa-solid fa-arrow-right" style="margin-left:8px;color:var(--color-red);"></i>
				</a>
			</div>
		</div>
	</section>

	<!-- ═══ PLATFORM PILLARS ══════════════════════════════════ -->
	<section class="pillars-section section" id="platform">
		<div class="container">
			<div class="section-header" style="text-align:center;max-width:580px;margin:0 auto var(--space-8);">
				<span class="section-eyebrow"><?php esc_html_e( 'What We Deliver', 'ascendance' ); ?></span>
				<h2 class="section-title reveal"><?php esc_html_e( 'Three Intelligence Products', 'ascendance' ); ?></h2>
				<p class="section-lead reveal reveal-delay-1"><?php esc_html_e( 'Every product type is crafted for a different decision-making cadence — from strategic planning to real-time situational awareness.', 'ascendance' ); ?></p>
			</div>

			<div class="pillars-grid">
				<div class="pillar-card reveal">
					<div class="pillar-icon"><i class="fa-solid fa-file-contract"></i></div>
					<h3><?php esc_html_e( 'Intelligence Briefs', 'ascendance' ); ?></h3>
					<p><?php esc_html_e( 'Structured weekly analysis. Each brief opens with a falsifiable analytical claim, followed by key findings, source verification, and scenario forecasts up to 18 months out.', 'ascendance' ); ?></p>
				</div>
				<div class="pillar-card reveal reveal-delay-1">
					<div class="pillar-icon"><i class="fa-solid fa-satellite-dish"></i></div>
					<h3><?php esc_html_e( 'Dynamic Updates', 'ascendance' ); ?></h3>
					<p><?php esc_html_e( 'Real-time situational updates linked to parent briefs. Delivered as developments occur — with impact assessment ratings from Low to Critical.', 'ascendance' ); ?></p>
				</div>
				<div class="pillar-card reveal reveal-delay-2">
					<div class="pillar-icon"><i class="fa-solid fa-layer-group"></i></div>
					<h3><?php esc_html_e( 'Strategic Dossiers', 'ascendance' ); ?></h3>
					<p><?php esc_html_e( 'Deep-dive actor and country profiles. Living documents updated monthly, combining historical context with active intelligence threads across five strategic dimensions.', 'ascendance' ); ?></p>
				</div>
			</div>

			<div style="text-align:center;margin-top:var(--space-8);">
				<a href="<?php echo esc_url( home_url( '/services/' ) ); ?>" class="btn btn-secondary" id="home-learn-more">
					<?php esc_html_e( 'Learn More About Each Product', 'ascendance' ); ?>
				</a>
			</div>
		</div>
	</section>

	<!-- ═══ MEMBERSHIP TIERS ══════════════════════════════════ -->
	<section class="tiers-section section-lg" id="pricing">
		<div class="container">
			<div class="section-header" style="text-align:center;max-width:540px;margin:0 auto var(--space-8);">
				<span class="section-eyebrow"><?php esc_html_e( 'Membership', 'ascendance' ); ?></span>
				<h2 class="section-title reveal"><?php esc_html_e( 'Intelligence Tiers', 'ascendance' ); ?></h2>
				<p class="section-lead reveal reveal-delay-1"><?php esc_html_e( 'Choose the tier calibrated to your intelligence requirements. Upgrade or downgrade at any time.', 'ascendance' ); ?></p>
			</div>

			<div class="tiers-grid">
				<!-- Essential -->
				<div class="tier-card reveal">
					<div class="tier-name"><?php esc_html_e( 'Essential', 'ascendance' ); ?></div>
					<div class="tier-price">$29<span>/mo</span></div>
					<p class="tier-desc"><?php esc_html_e( 'Foundational intelligence access for individuals and small teams building their strategic awareness.', 'ascendance' ); ?></p>
					<ul class="tier-features">
						<li><?php esc_html_e( '2 Intelligence Briefs per week', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'Daily Dynamic Updates', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'Regional coverage (1 region)', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'Email newsletter digest', 'ascendance' ); ?></li>
						<li class="locked"><?php esc_html_e( 'Strategic Dossiers', 'ascendance' ); ?></li>
						<li class="locked"><?php esc_html_e( 'API access', 'ascendance' ); ?></li>
					</ul>
					<a href="<?php echo esc_url( home_url( '/newsletter/' ) ); ?>" class="btn btn-secondary" id="tier-essential-cta">
						<?php esc_html_e( 'Start Essential', 'ascendance' ); ?>
					</a>
				</div>

				<!-- Professional (featured) -->
				<div class="tier-card featured reveal reveal-delay-1">
					<div class="tier-name"><?php esc_html_e( 'Professional', 'ascendance' ); ?></div>
					<div class="tier-price">$79<span>/mo</span></div>
					<p class="tier-desc"><?php esc_html_e( 'Comprehensive intelligence access for analysts, consultants, and mid-market enterprise teams.', 'ascendance' ); ?></p>
					<ul class="tier-features">
						<li><?php esc_html_e( 'All Intelligence Briefs (unlimited)', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'Real-time Dynamic Updates', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'Global regional coverage', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'Strategic Dossiers (read access)', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'Weekly analyst call', 'ascendance' ); ?></li>
						<li class="locked"><?php esc_html_e( 'API access', 'ascendance' ); ?></li>
					</ul>
					<a href="<?php echo esc_url( home_url( '/newsletter/' ) ); ?>" class="btn btn-primary" id="tier-professional-cta">
						<?php esc_html_e( 'Start Professional', 'ascendance' ); ?>
					</a>
				</div>

				<!-- Enterprise -->
				<div class="tier-card reveal reveal-delay-2">
					<div class="tier-name"><?php esc_html_e( 'Enterprise', 'ascendance' ); ?></div>
					<div class="tier-price">$249<span>/mo</span></div>
					<p class="tier-desc"><?php esc_html_e( 'Institutional-grade intelligence for large enterprise, government, and investment teams.', 'ascendance' ); ?></p>
					<ul class="tier-features">
						<li><?php esc_html_e( 'Everything in Professional', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'Full Dossier library access', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'REST API access', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'Bespoke intelligence requests', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'Dedicated analyst relationship', 'ascendance' ); ?></li>
						<li><?php esc_html_e( 'White-label report exports', 'ascendance' ); ?></li>
					</ul>
					<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn btn-secondary" id="tier-enterprise-cta">
						<?php esc_html_e( 'Contact for Enterprise', 'ascendance' ); ?>
					</a>
				</div>
			</div>
		</div>
	</section>

	<!-- ═══ NEWSLETTER CTA ════════════════════════════════════ -->
	<?php
	get_template_part( 'template-parts/cta-strip', null, array(
		'heading'    => __( 'Get Intelligence in Your Inbox', 'ascendance' ),
		'body'       => __( 'Subscribe to the weekly Ascendance Brief — a curated digest of the most significant geopolitical, economic, and technology developments. Free for the first 30 days.', 'ascendance' ),
		'btn_label'  => __( 'Subscribe Free', 'ascendance' ),
		'btn_url'    => home_url( '/newsletter/' ),
		'btn2_label' => __( 'View Pricing', 'ascendance' ),
		'btn2_url'   => home_url( '/services/' ),
	) );
	?>

</main><!-- #primary -->

<?php get_footer(); ?>
