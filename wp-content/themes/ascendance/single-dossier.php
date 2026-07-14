<?php
/**
 * The template for displaying all single Dossier CPT posts
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="site-main register-dossier py-16 md:py-24 bg-cream dark:bg-navy-deep border-b border-brand-divider-light dark:border-brand-divider-dark">

	<?php
	while ( have_posts() ) :
		the_post();
		
		$post_id = get_the_ID();
		$subhead = get_field( 'subhead', $post_id );
		$claim = get_field( 'analytical_claim', $post_id );
		$summary = get_field( 'executive_summary', $post_id );
		$takeaways = get_field( 'key_takeaways', $post_id );
		$pdf = get_field( 'download_pdf', $post_id );
		$stakeholders = get_field( 'stakeholders', $post_id );
		$findings = get_field( 'key_findings', $post_id );
		$data_blocks = get_field( 'data_blocks', $post_id );
		$revision_log = get_field( 'revision_log', $post_id );
		$last_revised = get_field( 'last_revised', $post_id );
		$version = get_field( 'brief_version', $post_id ) ?: 1;
		$tiers = ascendance_get_post_tiers( $post_id, 'professional' );
		$user_has_access = class_exists( 'Ascendance\Core\Paywall' ) && Ascendance\Core\Paywall::get_instance()->user_has_access();

		// Query related updates linking to this dossier
		$updates_query = new WP_Query( array(
			'post_type'      => 'update',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'meta_query'     => array(
				array(
					'key'     => 'parent_brief',
					'value'   => $post_id,
					'compare' => '=',
				),
			),
			'orderby'        => 'date',
			'order'          => 'ASC',
		) );

		// Query related briefings (relationship field)
		$related_briefs = get_field( 'related_briefs', $post_id );
		?>

		<article id="post-<?php the_ID(); ?>" <?php post_class( 'container mx-auto px-6 md:px-8 max-w-[1200px]' ); ?>>
			<!-- Content Metadata: AI Generated = <?php echo get_post_meta( get_the_ID(), 'ai_generated', true ) ? 'Yes' : 'No'; ?> -->
			
			<!-- Dossier Header -->
			<header class="dossier-header mb-12 border-b border-brand-divider-light dark:border-brand-divider-dark/20 pb-8 flex flex-col md:flex-row justify-between items-start md:items-end gap-6">
				<div>
					<div class="dossier-header-badges flex flex-wrap gap-2.5 items-center mb-4">
						<?php
						foreach ( $tiers as $t ) {
							echo ascendance_tier_badge( $t );
						}
						?>
						<?php
						$topics_list = get_the_terms( $post_id, 'topic' );
						if ( ! empty( $topics_list ) && ! is_wp_error( $topics_list ) ) :
							foreach ( $topics_list as $topic ) :
								?>
								<span class="dossier-topic-badge"><?php echo esc_html( $topic->name ); ?></span>
								<?php
							endforeach;
						endif;
						?>
					</div>
					<h1 class="dossier-title text-3xl md:text-5xl font-sans font-bold text-brand-text-primary dark:text-white leading-tight mb-4"><?php the_title(); ?></h1>
					<?php if ( ! empty( $subhead ) ) : ?>
						<p class="dossier-subhead text-base md:text-lg text-brand-text-muted dark:text-cream/80 italic leading-relaxed mb-4"><?php echo esc_html( $subhead ); ?></p>
					<?php endif; ?>
				</div>
				<div class="dossier-header-meta flex items-center gap-6 text-xs text-brand-text-muted dark:text-cream/50 font-sans md:mb-1">
					<span><i class="fa-regular fa-folder text-brand-red mr-1.5"></i> <?php esc_html_e( 'Dossier Ledger', 'ascendance' ); ?></span>
					<span class="dossier-date"><i class="fa-regular fa-calendar text-brand-red mr-1.5"></i> <?php echo get_the_date(); ?></span>
				</div>
			</header>

			<!-- Dossier Content Grid -->
			<div class="register-dossier-grid grid grid-cols-1 lg:grid-cols-[1fr_2.5fr] gap-12 items-start">
				
				<!-- Left Sidebar -->
				<aside class="dossier-sidebar lg:sticky lg:top-24 flex flex-col gap-8">
					<div class="dossier-sidebar-box bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-6 rounded-sm shadow-sm flex flex-col gap-8">
						
						<!-- Section: Dossier Index -->
						<section class="dossier-sidebar-section border-b border-brand-divider-light dark:border-brand-divider-dark/20 pb-6 last:border-b-0 last:pb-0">
							<h3 class="dossier-sidebar-heading text-sm font-sans font-bold text-brand-text-primary dark:text-white mb-4 flex items-center gap-2">
								<i class="fa-solid fa-circle-info text-brand-red"></i>
								<span><?php esc_html_e( 'Dossier Index', 'ascendance' ); ?></span>
							</h3>
							
							<!-- Index List -->
							<div class="register-dossier-meta-list flex flex-col gap-3.5">
								<div class="register-dossier-meta-item flex justify-between items-center gap-4 text-xs font-sans border-b border-brand-divider-light dark:border-brand-divider-dark/10 pb-2.5 last:border-b-0 last:pb-0">
									<span class="register-dossier-meta-label text-brand-text-muted dark:text-cream/50"><?php esc_html_e( 'Classification', 'ascendance' ); ?></span>
									<span class="register-dossier-meta-value font-bold text-brand-text-primary dark:text-cream text-right"><?php 
										$tiers_labels = array_map( 'ucfirst', $tiers );
										echo esc_html( implode( ', ', $tiers_labels ) ); 
									?></span>
								</div>
								<div class="register-dossier-meta-item flex justify-between items-center gap-4 text-xs font-sans border-b border-brand-divider-light dark:border-brand-divider-dark/10 pb-2.5 last:border-b-0 last:pb-0">
									<span class="register-dossier-meta-label text-brand-text-muted dark:text-cream/50"><?php esc_html_e( 'Region Tracked', 'ascendance' ); ?></span>
									<span class="register-dossier-meta-value font-bold text-brand-text-primary dark:text-cream text-right value-right"><?php the_terms( $post_id, 'region', '', ', ', '' ); ?></span>
								</div>
								<div class="register-dossier-meta-item flex justify-between items-center gap-4 text-xs font-sans border-b border-brand-divider-light dark:border-brand-divider-dark/10 pb-2.5 last:border-b-0 last:pb-0">
									<span class="register-dossier-meta-label text-brand-text-muted dark:text-cream/50"><?php esc_html_e( 'Version Code', 'ascendance' ); ?></span>
									<span class="register-dossier-meta-value font-mono text-[10px] text-brand-red text-right value-code">DOS-<?php echo esc_html( $post_id ); ?>-v<?php echo esc_html( $version ); ?></span>
								</div>
								<div class="register-dossier-meta-item flex justify-between items-center gap-4 text-xs font-sans border-b border-brand-divider-light dark:border-brand-divider-dark/10 pb-2.5 last:border-b-0 last:pb-0">
									<span class="register-dossier-meta-label text-brand-text-muted dark:text-cream/50"><?php esc_html_e( 'Last Revised', 'ascendance' ); ?></span>
									<span class="register-dossier-meta-value font-bold text-brand-text-primary dark:text-cream text-right"><?php echo esc_html( ! empty( $last_revised ) ? date( 'd F Y', strtotime( $last_revised ) ) : get_the_modified_date() ); ?></span>
								</div>
							</div>

							<!-- PDF Download Block (Gated) -->
							<div class="pdf-download-panel mt-6">
								<?php if ( $user_has_access ) : ?>
									<?php if ( ! empty( $pdf ) ) : ?>
										<a href="<?php echo esc_url( $pdf['url'] ); ?>" download class="dossier-pdf-btn w-full flex items-center justify-center gap-2 text-sm font-sans font-bold py-3 px-4 rounded-sm bg-brand-red text-white hover:bg-brand-red-light border-none transition-all duration-150">
											<i class="fa-solid fa-file-pdf"></i>
											<?php esc_html_e( 'Download Dossier PDF', 'ascendance' ); ?>
										</a>
									<?php else : ?>
										<button class="dossier-pdf-btn w-full flex items-center justify-center gap-2 text-sm font-sans font-bold py-3 px-4 rounded-sm border border-brand-divider-light dark:border-brand-divider-dark text-brand-text-muted dark:text-cream/50 cursor-not-allowed" disabled>
											<i class="fa-solid fa-triangle-exclamation"></i>
											<?php esc_html_e( 'PDF Release Pending', 'ascendance' ); ?>
										</button>
									<?php endif; ?>
								<?php else : ?>
									<a href="<?php echo esc_url( home_url( '/services/' ) ); ?>" class="dossier-pdf-btn w-full flex items-center justify-center gap-2 text-sm font-sans font-bold py-3 px-4 rounded-sm bg-brand-red text-white hover:bg-brand-red-light border-none transition-all duration-150">
										<i class="fa-solid fa-lock"></i>
										<?php esc_html_e( 'Unlock PDF Download', 'ascendance' ); ?>
									</a>
								<?php endif; ?>
							</div>
						</section>

						<!-- Tracked Stakeholders List (Gated) -->
						<?php if ( ! empty( $stakeholders ) && $user_has_access ) : ?>
							<section class="dossier-sidebar-section border-b border-brand-divider-light dark:border-brand-divider-dark/20 pb-6 last:border-b-0 last:pb-0 paywall-gated-content">
								<h3 class="dossier-sidebar-heading text-sm font-sans font-bold text-brand-text-primary dark:text-white mb-4 flex items-center gap-2">
									<i class="fa-solid fa-network-wired text-brand-red"></i>
									<span><?php esc_html_e( 'Actors Tracked', 'ascendance' ); ?></span>
								</h3>
								<div class="dossier-actors-list flex flex-col gap-3">
									<?php foreach ( $stakeholders as $sh ) : ?>
										<div class="dossier-actor-badge flex flex-col gap-0.5 p-3 bg-cream dark:bg-navy border border-brand-divider-light dark:border-brand-divider-dark/20 rounded-sm">
											<span class="actor-name text-xs font-sans font-bold text-brand-text-primary dark:text-white"><?php echo esc_html( $sh['name'] ); ?></span>
											<span class="actor-role text-[10px] font-sans text-brand-text-muted dark:text-cream/50"><?php echo esc_html( $sh['role'] ); ?></span>
										</div>
									<?php endforeach; ?>
								</div>
							</section>
						<?php endif; ?>

						<!-- Revision Changelog Log (Gated) -->
						<?php if ( ! empty( $revision_log ) && $user_has_access ) : ?>
							<section class="dossier-sidebar-section border-b border-brand-divider-light dark:border-brand-divider-dark/20 pb-6 last:border-b-0 last:pb-0 paywall-gated-content">
								<h3 class="dossier-sidebar-heading text-sm font-sans font-bold text-brand-text-primary dark:text-white mb-4 flex items-center gap-2">
									<i class="fa-solid fa-history text-brand-red"></i>
									<span><?php esc_html_e( 'Revision Changelog', 'ascendance' ); ?></span>
								</h3>
								<div class="dossier-changelog-list flex flex-col gap-3">
									<?php foreach ( $revision_log as $rev ) : ?>
										<div class="dossier-changelog-item flex flex-col gap-1 text-xs border-b border-brand-divider-light dark:border-brand-divider-dark/10 pb-2.5 last:border-b-0 last:pb-0">
											<span class="changelog-date font-mono text-[10px] text-brand-red font-bold"><?php echo date( 'd M Y', strtotime( $rev['revision_date'] ) ); ?></span>
											<span class="changelog-summary text-brand-text-muted dark:text-cream/70 leading-normal"><?php echo esc_html( $rev['revision_summary'] ); ?></span>
										</div>
									<?php endforeach; ?>
								</div>
							</section>
						<?php endif; ?>
					</div>
				</aside>

				<!-- Right Main Area -->
				<div class="dossier-main-content">
					
					<!-- Analytical Claim terminal block -->
					<?php if ( ! empty( $claim ) ) : ?>
						<div class="register-terminal bg-[#030810] text-[#00FF66] font-mono p-6 rounded-sm border border-brand-red shadow-[0_0_15px_rgba(188,27,29,0.3)] mb-8">
							<div class="register-terminal-header border-b border-dashed border-brand-red pb-2 mb-4 flex justify-between text-xs text-brand-red">
								<span><?php esc_html_e( 'INTELLIGENCE CLAIM THESIS // DECLASSIFIED', 'ascendance' ); ?></span>
								<span>DOS-<?php echo esc_html( $post_id ); ?></span>
							</div>
							<div class="register-terminal-row flex">
								<span class="register-terminal-prompt text-brand-red mr-2.5">&gt;</span>
								<span><strong class="text-white"><?php esc_html_e( 'CORE THESIS:', 'ascendance' ); ?></strong> <?php echo esc_html( $claim ); ?></span>
							</div>
						</div>
					<?php endif; ?>

					<!-- Executive Summary block -->
					<?php if ( ! empty( $summary ) ) : ?>
						<div class="dossier-summary-block bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-6 rounded-sm shadow-sm mb-8">
							<h3 class="text-lg font-sans font-bold text-brand-text-primary dark:text-white mb-4 flex items-center gap-2 border-b border-brand-divider-light dark:border-brand-divider-dark/20 pb-3"><i class="fa-solid fa-paragraph text-brand-red"></i><?php esc_html_e( 'Executive Summary', 'ascendance' ); ?></h3>
							<p class="dossier-summary-text text-sm text-brand-text-muted dark:text-cream/80 leading-relaxed font-serif">
								<?php echo esc_html( $summary ); ?>
							</p>
						</div>
					<?php endif; ?>

					<!-- Key Takeaways (Public Callout Box) -->
					<?php if ( ! empty( $takeaways ) && is_array( $takeaways ) ) : ?>
						<div class="dossier-takeaways-block bg-white dark:bg-navy-mid border-l-[3px] border-l-brand-red border-y border-r border-brand-divider-light dark:border-brand-divider-dark p-6 rounded-sm shadow-sm mb-8">
							<h4 class="text-base font-sans font-bold text-brand-text-primary dark:text-white mb-4 flex items-center gap-2">
								<i class="fa-solid fa-bullseye text-brand-red"></i>
								<?php esc_html_e( 'Key Takeaways', 'ascendance' ); ?>
							</h4>
							<ul class="takeaways-list list-none p-0 m-0 flex flex-col gap-3 text-sm text-brand-text-muted dark:text-cream/70">
								<?php foreach ( $takeaways as $row ) : ?>
									<li class="takeaway-item flex items-start gap-2">
										<span class="bullet text-brand-red font-bold">✦</span>
										<span><?php echo esc_html( $row['takeaway'] ); ?></span>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>

					<!-- Main Content (Subject to paywall logic) -->
					<?php if ( $user_has_access ) : ?>
						<div class="paywall-gated-content">
					<?php endif; ?>

					<div class="entry-content text-brand-text-primary dark:text-cream leading-relaxed mb-8">
						<?php the_content(); ?>
					</div>

					<!-- Key Findings Section (Gated) -->
					<?php if ( ! empty( $findings ) && $user_has_access ) : ?>
						<div class="dossier-findings-block bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-6 rounded-sm shadow-sm mb-8">
							<h3 class="findings-title text-lg font-sans font-bold text-brand-text-primary dark:text-white mb-4 flex items-center gap-2 border-b border-brand-divider-light dark:border-brand-divider-dark/25 pb-3"><i class="fa-solid fa-rectangle-list text-brand-red"></i><?php esc_html_e( 'Analytical Breakdown', 'ascendance' ); ?></h3>
							<div class="findings-content text-sm text-brand-text-muted dark:text-cream/70 leading-relaxed">
								<?php echo wp_kses_post( $findings ); ?>
							</div>
						</div>
					<?php endif; ?>

					<!-- Structured Data Blocks (Gated) -->
					<?php if ( ! empty( $data_blocks ) && $user_has_access ) : ?>
						<div class="dossier-table-block flex flex-col gap-8 mb-8">
							<?php foreach ( $data_blocks as $block ) : ?>
								<div class="dossier-data-card bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-6 rounded-sm shadow-sm">
									<h4 class="data-card-title text-base font-sans font-bold text-brand-text-primary dark:text-white mb-4 flex items-center gap-2 border-b border-brand-divider-light dark:border-brand-divider-dark/20 pb-3"><i class="fa-solid fa-chart-column text-brand-red"></i><?php echo esc_html( $block['block_title'] ); ?></h4>
									<?php if ( ! empty( $block['structured_rows'] ) ) : ?>
										<table class="dossier-data-table w-full border-collapse text-left text-sm text-brand-text-primary dark:text-cream">
											<thead>
												<tr class="border-b border-brand-divider-light dark:border-brand-divider-dark/20">
													<th class="pb-2.5 font-sans font-bold text-xs uppercase tracking-wider text-brand-text-muted dark:text-cream/50"><?php esc_html_e( 'Variable / Index Key', 'ascendance' ); ?></th>
													<th class="pb-2.5 font-sans font-bold text-xs uppercase tracking-wider text-brand-text-muted dark:text-cream/50"><?php esc_html_e( 'Value / Metric', 'ascendance' ); ?></th>
													<th class="text-right pb-2.5 font-sans font-bold text-xs uppercase tracking-wider text-brand-text-muted dark:text-cream/50"><?php esc_html_e( 'Analytical Status', 'ascendance' ); ?></th>
												</tr>
											</thead>
											<tbody>
												<?php foreach ( $block['structured_rows'] as $row ) : ?>
													<tr class="border-b border-brand-divider-light dark:border-brand-divider-dark/10 last:border-b-0">
														<td class="py-3.5 font-sans font-medium text-brand-text-primary dark:text-cream"><?php echo esc_html( $row['column_1'] ); ?></td>
														<td class="value-highlight py-3.5 font-mono text-xs text-brand-red dark:text-[#00FF66] font-bold"><?php echo esc_html( $row['column_2'] ); ?></td>
														<td class="text-right status-italic py-3.5 italic text-brand-text-muted/65 dark:text-cream/40"><?php echo esc_html( $row['column_3'] ?: '—' ); ?></td>
													</tr>
												<?php endforeach; ?>
											</tbody>
										</table>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<!-- Real-time Timeline Updates Section (Gated) -->
					<?php if ( $updates_query->have_posts() && $user_has_access ) : ?>
						<div class="dossier-timeline-block mb-8">
							<h3 class="text-lg font-sans font-bold text-brand-text-primary dark:text-white mb-6 flex items-center gap-2">
								<i class="fa-solid fa-clock-rotate-left text-brand-red"></i>
								<?php esc_html_e( 'Dossier Brief Timeline Updates', 'ascendance' ); ?>
							</h3>
							
							<div class="dossier-timeline-trail flex flex-col gap-6 relative before:content-[''] before:absolute before:left-3 before:top-2 before:bottom-2 before:w-0.5 before:bg-brand-divider-light dark:before:bg-brand-divider-dark/20 pl-8">
								<?php 
								while ( $updates_query->have_posts() ) :
									$updates_query->the_post();
									$up_id = get_the_ID();
									$up_impact = get_field( 'impact_assessment', $up_id ) ?: 'medium';
									$up_summary = get_field( 'one_line_summary', $up_id );
									?>
									<div class="dossier-timeline-node relative">
										<!-- Dot node -->
										<div class="dossier-timeline-dot absolute -left-[27px] top-1.5 w-2.5 h-2.5 bg-brand-red rounded-sm"></div>
										
										<div class="dossier-timeline-meta flex items-center gap-3 text-xs text-brand-text-muted dark:text-cream/50 font-sans mb-1">
											<span class="timeline-date"><?php echo get_the_date( 'd M Y, H:i' ); ?></span>
											<?php echo ascendance_impact_badge( $up_impact ); ?>
										</div>
										<h4 class="timeline-title text-sm font-sans font-bold text-brand-text-primary dark:text-white mb-1 hover:text-brand-red">
											<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
										</h4>
										<?php if ( ! empty( $up_summary ) ) : ?>
											<p class="timeline-summary text-xs text-brand-text-muted dark:text-cream/60 leading-relaxed"><?php echo esc_html( $up_summary ); ?></p>
										<?php endif; ?>
									</div>
								<?php 
								endwhile; 
								wp_reset_postdata();
								?>
							</div>
						</div>
					<?php endif; ?>

					<!-- Related Briefs (Gated) -->
					<?php if ( ! empty( $related_briefs ) && $user_has_access ) : ?>
						<div class="dossier-related-section bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-6 rounded-sm shadow-sm mb-8">
							<h3 class="text-lg font-sans font-bold text-brand-text-primary dark:text-white mb-6 flex items-center gap-2 border-b border-brand-divider-light dark:border-brand-divider-dark/25 pb-3"><i class="fa-solid fa-link text-brand-red"></i><?php esc_html_e( 'Cross-Referenced Briefings', 'ascendance' ); ?></h3>
							<div class="dossier-related-grid grid grid-cols-1 sm:grid-cols-2 gap-4">
								<?php foreach ( $related_briefs as $brief ) : ?>
									<a href="<?php echo esc_url( get_permalink( $brief->ID ) ); ?>" class="dossier-brief-link-card flex justify-between items-center p-4 bg-cream dark:bg-navy border border-brand-divider-light dark:border-brand-divider-dark/20 rounded-sm hover:border-brand-red transition-all duration-150 text-sm font-sans font-bold text-brand-text-primary dark:text-white">
										<span><?php echo esc_html( $brief->post_title ); ?></span>
										<i class="fa-solid fa-chevron-right text-brand-red"></i>
									</a>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( $user_has_access ) : ?>
						</div>
					<?php endif; ?>

				</div>

			</div>

			<!-- Footer Tags -->
			<footer class="dossier-footer border-t border-brand-divider-light dark:border-brand-divider-dark/20 pt-8 mt-12">
				<div class="dossier-footer-meta flex flex-col sm:flex-row justify-between gap-4 text-xs font-sans text-brand-text-muted dark:text-cream/50 mb-8 [&_a]:text-brand-red hover:[&_a]:text-brand-red-light">
					<span>
						<?php esc_html_e( 'Regions Tracked: ', 'ascendance' ); ?>
						<strong><?php the_terms( $post_id, 'region', '', ', ', '' ); ?></strong>
					</span>
					<span>
						<?php esc_html_e( 'Tags: ', 'ascendance' ); ?>
						<strong><?php the_terms( $post_id, 'intelligence_tag', '', ', ', '' ); ?></strong>
					</span>
				</div>
				
				<?php if ( comments_open() || get_comments_number() ) : ?>
					<div class="dossier-comments-thread">
						<?php comments_template(); ?>
					</div>
				<?php endif; ?>
			</footer>
		</article>

	<?php endwhile; ?>

</main>

<?php
get_footer();
