<?php
/**
 * Front Page — Ascendance Intelligence Portal
 *
 * News-Led Intelligence Portal Layout
 * 
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="as-main">

<?php
// Helper functions for dynamic data mapping
if ( ! function_exists( 'as_get_access_label' ) ) {
	function as_get_access_label( $post_id ) {
		$tiers = ascendance_get_post_tiers( $post_id );
		if ( in_array( 'free', $tiers ) || in_array( 'open', $tiers ) ) {
			return 'Open';
		}
		return 'Subscribers';
	}
}

if ( ! function_exists( 'as_get_post_kicker' ) ) {
	function as_get_post_kicker( $post_id, $default = '' ) {
		$post = get_post( $post_id );
		if ( 'brief' === $post->post_type ) {
			$topics = get_the_terms( $post_id, 'topic' );
			if ( ! empty( $topics ) && ! is_wp_error( $topics ) ) {
				return $topics[0]->name;
			}
			return 'SPA Intelligence Brief';
		} elseif ( 'update' === $post->post_type ) {
			$access = as_get_access_label( $post_id );
			return 'Update &middot; ' . $access;
		}
		return ! empty( $default ) ? $default : 'Analysis';
	}
}
?>

<main id="primary" class="as-main">

	<!-- Hero Section -->
	<section class="as-hero">
		<!-- Lead Story Left -->
		<div class="as-lead hoverable">
			<?php
			$lead_query = new WP_Query( array(
				'post_type'      => array( 'brief', 'update', 'post' ),
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			) );

			if ( $lead_query->have_posts() ) :
				while ( $lead_query->have_posts() ) : $lead_query->the_post();
					$lead_id = get_the_ID();
					?>
					<a href="<?php the_permalink(); ?>" style="text-decoration:none; color:inherit;">
						<div class="as-photo" style="padding-bottom:56.25%;">
							<div class="as-photo-in">
								<?php if ( has_post_thumbnail() ) : ?>
									<?php the_post_thumbnail( 'large', array( 'style' => 'width:100%; height:100%; object-fit:cover;' ) ); ?>
								<?php else : ?>
									<span>[ lead photo &middot; 16:9 ]</span>
								<?php endif; ?>
							</div>
						</div>
						<div class="as-kicker-slab big">
							<?php echo esc_html( as_get_post_kicker( $lead_id, 'SPA Intelligence Brief' ) ); ?> &middot; <?php echo esc_html( get_the_date( 'd M Y' ) ); ?>
						</div>
						<h1 class="as-headline as-lead-title"><?php the_title(); ?></h1>
						<p class="as-dek"><?php echo esc_html( get_the_excerpt() ); ?></p>
						<div class="as-metarow">
							<span class="as-access"><i class="as-dot"></i><?php echo esc_html( as_get_access_label( $lead_id ) ); ?></span>
							<span class="as-sep">|</span>
							<span><?php echo esc_html( as_get_post_kicker( $lead_id, 'Governance' ) ); ?></span>
							<span class="as-sep">&middot;</span>
							<span><?php echo esc_html( get_the_date( 'd M' ) ); ?></span>
						</div>
					</a>
					<?php
				endwhile;
				wp_reset_postdata();
			else :
				?>
				<a href="<?php echo esc_url( home_url( '/us-drc-partnership/' ) ); ?>" style="text-decoration:none; color:inherit;">
					<div class="as-photo" style="padding-bottom:56.25%;">
						<div class="as-photo-in"><span>[ lead photo &middot; 16:9 ]</span></div>
					</div>
					<div class="as-kicker-slab big">
						SPA Intelligence Brief &middot; 29 May 2026
					</div>
					<h1 class="as-headline as-lead-title">Washington Accords clear the Senate: what ratification sets in motion</h1>
					<p class="as-dek">The DRC Senate's authorization moves the partnership from framework to obligation. The next hundred days decide whether reform commitments are matched on the ground.</p>
					<div class="as-metarow">
						<span class="as-access"><i class="as-dot"></i>Subscribers</span>
						<span class="as-sep">|</span>
						<span>Governance</span>
						<span class="as-sep">&middot;</span>
						<span>29 May</span>
					</div>
				</a>
			<?php endif; ?>
		</div>

		<!-- Secondary Story Right -->
		<div class="as-hero-right">
			<?php
			$sec_query = new WP_Query( array(
				'post_type'      => array( 'brief', 'update', 'post' ),
				'posts_per_page' => 1,
				'post_status'    => 'publish',
				'offset'         => 1,
			) );

			if ( $sec_query->have_posts() ) :
				while ( $sec_query->have_posts() ) : $sec_query->the_post();
					$sec_id = get_the_ID();
					?>
					<article class="as-secondary hoverable">
						<a href="<?php the_permalink(); ?>" style="text-decoration:none; color:inherit;">
							<div class="as-photo" style="padding-bottom:56.25%; margin-bottom:14px;">
								<div class="as-photo-in">
									<?php if ( has_post_thumbnail() ) : ?>
										<?php the_post_thumbnail( 'medium', array( 'style' => 'width:100%; height:100%; object-fit:cover;' ) ); ?>
									<?php else : ?>
										<span>[ secondary photo &middot; 16:9 ]</span>
									<?php endif; ?>
								</div>
							</div>
							<div class="as-kicker-slab"><?php echo esc_html( as_get_post_kicker( $sec_id, 'Strategic Asset Reserve' ) ); ?></div>
							<h2 class="as-headline as-sec-title"><?php the_title(); ?></h2>
							<p class="as-dek"><?php echo esc_html( get_the_excerpt() ); ?></p>
							<div class="as-metarow">
								<span class="as-access"><i class="as-dot"></i><?php echo esc_html( as_get_access_label( $sec_id ) ); ?></span>
								<span class="as-sep">|</span>
								<span><?php echo esc_html( as_get_post_kicker( $sec_id, 'Mining, Policy' ) ); ?></span>
								<span class="as-sep">&middot;</span>
								<span><?php echo esc_html( get_the_date( 'd M' ) ); ?></span>
							</div>
						</a>
					</article>
					<?php
				endwhile;
				wp_reset_postdata();
			else :
				?>
				<article class="as-secondary hoverable">
					<a href="<?php echo esc_url( home_url( '/sar-registry/' ) ); ?>" style="text-decoration:none; color:inherit;">
						<div class="as-photo" style="padding-bottom:56.25%; margin-bottom:14px;">
							<div class="as-photo-in"><span>[ portrait &middot; 4:3 ]</span></div>
						</div>
						<div class="as-kicker-slab">Strategic Asset Reserve</div>
						<h2 class="as-headline as-sec-title">The reserve's operating mandate takes shape behind closed doors</h2>
						<p class="as-dek">As the SAR moves from concept to institution, the terms of access, and who sets them, are being negotiated now.</p>
						<div class="as-metarow">
							<span class="as-access"><i class="as-dot"></i>Subscribers</span>
							<span class="as-sep">|</span>
							<span>Mining, Policy</span>
							<span class="as-sep">&middot;</span>
							<span>28 May</span>
						</div>
					</a>
				</article>
			<?php endif; ?>
		</div>
	</section>

	<!-- On the ground Section -->
	<section class="as-section">
		<div class="as-sechead">
			<h2>On the ground</h2>
			<span class="as-rule"></span>
			<a class="as-more" href="<?php echo esc_url( home_url( '/us-drc-partnership/' ) ); ?>">View all &rarr;</a>
		</div>
		<div class="as-grid3">
			<?php
			$west_query = new WP_Query( array(
				'post_type'      => array( 'brief', 'update', 'post' ),
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				'offset'         => 2,
			) );

			if ( $west_query->have_posts() ) :
				while ( $west_query->have_posts() ) : $west_query->the_post();
					$w_id = get_the_ID();
					?>
					<article class="as-card hoverable">
						<a href="<?php the_permalink(); ?>" style="text-decoration:none; color:inherit;">
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="as-photo" style="padding-bottom:62.5%; margin-bottom:14px;">
									<div class="as-photo-in">
										<?php the_post_thumbnail( 'medium', array( 'style' => 'width:100%; height:100%; object-fit:cover;' ) ); ?>
									</div>
								</div>
							<?php endif; ?>
							<div class="as-kicker-slab"><?php echo esc_html( as_get_post_kicker( $w_id, 'Analysis' ) ); ?></div>
							<h3 class="as-headline as-card-title"><?php the_title(); ?></h3>
							<p class="as-dek"><?php echo esc_html( get_the_excerpt() ); ?></p>
							<div class="as-metarow">
								<span class="as-access"><i class="as-dot"></i><?php echo esc_html( as_get_access_label( $w_id ) ); ?></span>
								<span class="as-sep">|</span>
								<span><?php echo esc_html( get_the_date( 'd M' ) ); ?></span>
							</div>
						</a>
					</article>
					<?php
				endwhile;
				wp_reset_postdata();
			else :
				?>
				<!-- Fallback 1: Phase II financing advances -->
				<article class="as-card hoverable">
					<a href="<?php echo esc_url( home_url( '/lobito-corridor/' ) ); ?>" style="text-decoration:none; color:inherit;">
						<div class="as-photo" style="padding-bottom:62.5%; margin-bottom:14px;">
							<div class="as-photo-in"><span>[ corridor &middot; 16:10 ]</span></div>
						</div>
						<div class="as-kicker-slab">Lobito Corridor</div>
						<h3 class="as-headline as-card-title">Phase II financing advances as the corridor clears its next hurdle</h3>
						<p class="as-dek">A funding line long stalled has moved; the build-out timeline tightens and the logistics map with it.</p>
						<div class="as-metarow">
							<span class="as-access"><i class="as-dot"></i>Subscribers</span>
							<span class="as-sep">|</span>
							<span>27 May</span>
						</div>
					</a>
				</article>
				<!-- Fallback 2: Reform calendar published -->
				<article class="as-card hoverable">
					<a href="<?php echo esc_url( home_url( '/regulatory-reform-tracker/' ) ); ?>" style="text-decoration:none; color:inherit;">
						<div class="as-kicker-slab">Governance</div>
						<h3 class="as-headline as-card-title">Reform calendar published: the first deadline is closer than it looks</h3>
						<p class="as-dek">Commitments now carry dates. The earliest tests the partnership's appetite for delivery over declaration.</p>
						<div class="as-metarow">
							<span class="as-access"><i class="as-dot"></i>Subscribers</span>
							<span class="as-sep">|</span>
							<span>22 May</span>
						</div>
					</a>
				</article>
				<!-- Fallback 3: Critical-minerals operators recalculate -->
				<article class="as-card hoverable">
					<a href="<?php echo esc_url( home_url( '/sar-registry/' ) ); ?>" style="text-decoration:none; color:inherit;">
						<div class="as-kicker-slab">Mining</div>
						<h3 class="as-headline as-card-title">Critical-minerals operators recalculate under the new reserve terms</h3>
						<p class="as-dek">A qualification bar set higher than expected reshuffles who benefits from the reserve, and when.</p>
						<div class="as-metarow">
							<span class="as-access"><i class="as-dot"></i>Subscribers</span>
							<span class="as-sep">|</span>
							<span>24 May</span>
						</div>
					</a>
				</article>
			<?php endif; ?>
		</div>
	</section>

	<!-- The reference desk Section -->
	<section class="as-section as-refband">
		<div class="as-sechead">
			<h2>The reference desk</h2>
			<span class="as-rule"></span>
			<a class="as-more" href="<?php echo esc_url( home_url( '/us-drc-partnership/' ) ); ?>">All registers &rarr;</a>
		</div>
		<div class="as-refgrid">
			<!-- Card 1: SAR Registry -->
			<a class="as-refcard flag" href="<?php echo esc_url( home_url( '/sar-registry/' ) ); ?>">
				<span class="as-refkind">Register &middot; Live</span>
				<span class="as-reftitle">SAR Registry</span>
				<span class="as-refdek">Every publicly reported Strategic Asset Reserve designation and transaction, with sourcing, confidence, and clickable asset profiles.</span>
				<span class="as-refmeta">13 rows &middot; 3 profiles</span>
			</a>
			<!-- Card 2: Regulatory Reform Tracker -->
			<a class="as-refcard flag" href="<?php echo esc_url( home_url( '/regulatory-reform-tracker/' ) ); ?>">
				<span class="as-refkind">Tracker &middot; Live</span>
				<span class="as-reftitle">Regulatory Reform Tracker</span>
				<span class="as-refdek">Every SPA obligation, both parties, held to the treaty text clause by clause, with verdicts.</span>
				<span class="as-refmeta">29 obligations &middot; 12 articles</span>
			</a>
			<!-- Card 3: DRC Sovereign & Institutional Rating -->
			<a class="as-refcard flag" href="<?php echo esc_url( home_url( '/drc-sovereign-rating/' ) ); ?>">
				<span class="as-refkind">Rating &middot; Live</span>
				<span class="as-reftitle">DRC Sovereign &amp; Institutional Rating</span>
				<span class="as-refdek">A sovereign-alternative composite across 14 pillars, plus per-entity ratings for 28 state enterprises.</span>
				<span class="as-refmeta">14 pillars &middot; 28 entities</span>
			</a>
			<!-- Card 4: CAMI Registry -->
			<a class="as-refcard" href="<?php echo esc_url( home_url( '/cami-registry/' ) ); ?>">
				<span class="as-refkind">Register &middot; Live</span>
				<span class="as-reftitle">CAMI Registry</span>
				<span class="as-refdek">The complete DRC mining-title cadastre, made searchable.</span>
				<span class="as-refmeta">3,448 titles &middot; 984 holders</span>
			</a>
			<!-- Card 5: SPA Glossary -->
			<a class="as-refcard" href="<?php echo esc_url( home_url( '/spa-glossary/' ) ); ?>">
				<span class="as-refkind">Register &middot; Live</span>
				<span class="as-reftitle">SPA Glossary</span>
				<span class="as-refdek">Every mechanism and legal term in the agreement, defined against the treaty text.</span>
				<span class="as-refmeta">42 terms</span>
			</a>
			<!-- Card 6: Dossier 1 - Strategic Asset Reserve -->
			<a class="as-refcard dossier" href="<?php echo esc_url( home_url( '/us-drc-partnership/' ) ); ?>">
				<span class="as-refkind">Dossier &middot; Active</span>
				<span class="as-reftitle">Strategic Asset Reserve</span>
				<span class="as-refdek">The definitive living file on the SAR: mandate drafting, qualification criteria, allocation politics and every operator in contention.</span>
				<span class="as-refmeta">14 briefs &middot; Updated 2 days ago</span>
			</a>
			<!-- Card 7: Dossier 2 - The Washington Accords -->
			<a class="as-refcard dossier" href="<?php echo esc_url( home_url( '/us-drc-partnership/' ) ); ?>">
				<span class="as-refkind">Dossier &middot; Active</span>
				<span class="as-reftitle">The Washington Accords</span>
				<span class="as-refdek">Ratification, implementation and the hundred-day test. The complete record of the instrument at the center of the partnership.</span>
				<span class="as-refmeta">11 briefs &middot; Updated 5 days ago</span>
			</a>
			<!-- Empty slots to prevent dark background blocks -->
			<div class="as-refcard-empty"></div>
			<div class="as-refcard-empty"></div>
		</div>
	</section>

	<!-- Investment & Markets Section -->
	<section class="as-section">
		<div class="as-sechead">
			<h2>Investment &amp; Markets</h2>
			<span class="as-rule"></span>
			<a class="as-more" href="<?php echo esc_url( home_url( '/us-drc-partnership/' ) ); ?>">View all &rarr;</a>
		</div>
		<div class="as-grid3">
			<?php
			$capital_query = new WP_Query( array(
				'post_type'      => array( 'brief', 'update', 'post' ),
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				'offset'         => 5,
			) );

			if ( $capital_query->have_posts() ) :
				while ( $capital_query->have_posts() ) : $capital_query->the_post();
					$c_id = get_the_ID();
					?>
					<article class="as-card hoverable">
						<a href="<?php the_permalink(); ?>" style="text-decoration:none; color:inherit;">
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="as-photo" style="padding-bottom:62.5%; margin-bottom:14px;">
									<div class="as-photo-in">
										<?php the_post_thumbnail( 'medium', array( 'style' => 'width:100%; height:100%; object-fit:cover;' ) ); ?>
									</div>
								</div>
							<?php endif; ?>
							<div class="as-kicker-slab"><?php echo esc_html( as_get_post_kicker( $c_id, 'Analysis' ) ); ?></div>
							<h3 class="as-headline as-card-title"><?php the_title(); ?></h3>
							<p class="as-dek"><?php echo esc_html( get_the_excerpt() ); ?></p>
							<div class="as-metarow">
								<span class="as-access"><i class="as-dot"></i><?php echo esc_html( as_get_access_label( $c_id ) ); ?></span>
								<span class="as-sep">|</span>
								<span><?php echo esc_html( get_the_date( 'd M' ) ); ?></span>
							</div>
						</a>
					</article>
					<?php
				endwhile;
				wp_reset_postdata();
			else :
				?>
				<!-- Fallback 1: Capital waits -->
				<article class="as-card hoverable">
					<a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>" style="text-decoration:none; color:inherit;">
						<div class="as-kicker-slab">Investment Funds</div>
						<h3 class="as-headline as-card-title">Capital waits on the hundred-day test before committing</h3>
						<p class="as-dek">Funds that pre-positioned for ratification now want delivery before they deploy at scale.</p>
						<div class="as-metarow">
							<span class="as-access"><i class="as-dot"></i>Subscribers</span>
							<span class="as-sep">|</span>
							<span>26 May</span>
						</div>
					</a>
				</article>
				<!-- Fallback 2: Power commitments -->
				<article class="as-card hoverable">
					<a href="<?php echo esc_url( home_url( '/lobito-corridor/' ) ); ?>" style="text-decoration:none; color:inherit;">
						<div class="as-kicker-slab">Energy &amp; Infrastructure</div>
						<h3 class="as-headline as-card-title">Power commitments lag the corridor they are meant to serve</h3>
						<p class="as-dek">Generation timelines have not kept pace with the transport build-out, opening a gap the partnership must close.</p>
						<div class="as-metarow">
							<span class="as-access"><i class="as-dot"></i>Subscribers</span>
							<span class="as-sep">|</span>
							<span>25 May</span>
						</div>
					</a>
				</article>
				<!-- Fallback 3: Three signals -->
				<article class="as-card hoverable">
					<a href="<?php echo esc_url( home_url( '/us-drc-partnership/' ) ); ?>" style="text-decoration:none; color:inherit;">
						<div class="as-photo" style="padding-bottom:62.5%; margin-bottom:14px;">
							<div class="as-photo-in"><span>[ floor vote &middot; 16:9 ]</span></div>
						</div>
						<div class="as-kicker-slab">Update &middot; Open</div>
						<h3 class="as-headline as-card-title">Three signals in the ratification vote that ran beneath the headline</h3>
						<p class="as-dek">The margin, the abstentions and the floor statements tell a story the result alone does not.</p>
						<div class="as-metarow">
							<span class="as-access"><i class="as-dot"></i>Open</span>
							<span class="as-sep">|</span>
							<span>29 May</span>
						</div>
					</a>
				</article>
			<?php endif; ?>
		</div>
	</section>

	<!-- Paywall / Subscription Teaser -->
	<?php if ( ! is_user_logged_in() ) : ?>
		<section class="as-gate" style="margin-bottom:40px;">
			<div class="as-gate-card">
				<span class="as-gate-lock">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
					Subscriber Access Platform
				</span>
				<h3>Unlock Complete US-DRC SPA Intelligence</h3>
				<p>Subscribe for full access to weekly briefs, gated analysis, reference registers, and flagship dossiers.</p>
				<div style="display:flex; gap:16px; justify-content:center;">
					<a class="as-subscribe lg" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>">Explore Subscription Tiers</a>
					<a class="as-ghost" href="<?php echo esc_url( home_url( '/advisory/' ) ); ?>">Advisory Services</a>
				</div>
			</div>
		</section>
	<?php endif; ?>

</main>

<?php
get_footer();

