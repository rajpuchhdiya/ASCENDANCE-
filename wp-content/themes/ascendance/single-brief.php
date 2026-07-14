<?php
/**
 * The template for displaying all single Brief CPT posts
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="site-main py-16 md:py-24 bg-cream dark:bg-navy-deep border-b border-brand-divider-light dark:border-brand-divider-dark">

	<?php
	while ( have_posts() ) :
		the_post();
		
		$post_id = get_the_ID();
		$subhead = get_field( 'subhead', $post_id );
		$claim = get_field( 'analytical_claim', $post_id );
		$summary = get_field( 'executive_summary', $post_id );
		$takeaways = get_field( 'key_takeaways', $post_id );
		$findings = get_field( 'key_findings', $post_id );
		$references = get_field( 'source_references', $post_id );
		$sources_details = get_field( 'sources', $post_id );
		$version = get_field( 'brief_version', $post_id ) ?: 1;
		$tiers = ascendance_get_post_tiers( $post_id, 'essential' );
		$user_has_access = class_exists( 'Ascendance\Core\Paywall' ) && Ascendance\Core\Paywall::get_instance()->user_has_access();
		
		// Query related updates linking to this brief
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

		// Query related briefs (with fallback to same topic)
		$related_briefs = get_field( 'related_briefs', $post_id );
		if ( empty( $related_briefs ) ) {
			$topics = wp_get_post_terms( $post_id, 'topic', array( 'fields' => 'ids' ) );
			if ( ! empty( $topics ) && ! is_wp_error( $topics ) ) {
				$related_query = new WP_Query( array(
					'post_type'      => 'brief',
					'posts_per_page' => 3,
					'post__not_in'   => array( $post_id ),
					'tax_query'      => array(
						array(
							'taxonomy' => 'topic',
							'field'    => 'term_id',
							'terms'    => $topics,
						),
					),
				) );
				if ( $related_query->have_posts() ) {
					$related_briefs = $related_query->posts;
				}
				wp_reset_postdata();
			}
		}
		?>

		<article id="post-<?php the_ID(); ?>" <?php post_class( 'container mx-auto px-6 md:px-8 max-w-[800px]' ); ?>>
			<!-- Content Metadata: AI Generated = <?php echo get_post_meta( get_the_ID(), 'ai_generated', true ) ? 'Yes' : 'No'; ?> -->
			
			<!-- Post Header -->
			<header class="editorial-header mb-12 border-b border-brand-divider-light dark:border-brand-divider-dark/20 pb-8">
				<div class="editorial-header-badges flex flex-wrap gap-2.5 items-center mb-6">
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
							<span class="editorial-topic-badge"><?php echo esc_html( $topic->name ); ?></span>
							<?php
						endforeach;
					endif;
					?>
					<span class="editorial-version-badge text-[10px] font-sans font-bold uppercase tracking-wider px-2 py-0.5 rounded-sm bg-navy text-white dark:bg-cream dark:text-navy-deep"><?php esc_html_e( 'VERSION', 'ascendance' ); ?> <?php echo esc_html( $version ); ?></span>
				</div>

				<h1 class="editorial-title text-3xl md:text-5xl font-sans font-bold text-brand-text-primary dark:text-white leading-tight mb-4"><?php the_title(); ?></h1>
				
				<?php if ( ! empty( $subhead ) ) : ?>
					<p class="editorial-subhead text-base md:text-lg text-brand-text-muted dark:text-cream/80 italic leading-relaxed mb-6"><?php echo esc_html( $subhead ); ?></p>
				<?php endif; ?>

				<div class="editorial-meta-row flex items-center gap-6 text-xs text-brand-text-muted dark:text-cream/50 font-sans">
					<span><i class="fa-regular fa-calendar text-brand-red mr-1.5"></i> <?php echo get_the_date(); ?></span>
					<span><i class="fa-regular fa-user text-brand-red mr-1.5"></i> <?php the_author(); ?></span>
					<span><i class="fa-solid fa-earth-americas text-brand-red mr-1.5"></i> <?php the_terms( $post_id, 'region', '', ', ', '' ); ?></span>
				</div>
			</header>

			<div class="entry-content">
				
				<!-- Analytical Claim Block -->
				<?php if ( ! empty( $claim ) ) : ?>
					<div class="register-terminal bg-[#030810] text-[#00FF66] font-mono p-6 rounded-sm border border-brand-red shadow-[0_0_15px_rgba(188,27,29,0.3)] mb-8">
						<div class="register-terminal-header border-b border-dashed border-brand-red pb-2 mb-4 flex justify-between text-xs text-brand-red">
							<span><?php esc_html_e( 'ANALYTICAL CLAIM FEED // SECURE_ACCESS', 'ascendance' ); ?></span>
							<span>ID: <?php echo esc_html( $post_id ); ?></span>
						</div>
						<div class="register-terminal-row flex mb-0">
							<span class="register-terminal-prompt text-brand-red mr-2.5">&gt;</span>
							<span><strong class="text-white"><?php esc_html_e( 'THESIS:', 'ascendance' ); ?></strong> <?php echo esc_html( $claim ); ?></span>
						</div>
					</div>
				<?php endif; ?>

				<!-- Key Takeaways (Public Callout Box) -->
				<?php if ( ! empty( $takeaways ) && is_array( $takeaways ) ) : ?>
					<div class="takeaways-callout-box bg-white dark:bg-navy-mid border-l-[3px] border-l-brand-red border-y border-r border-brand-divider-light dark:border-brand-divider-dark p-6 rounded-sm shadow-sm my-8">
						<h4 class="takeaways-title text-base font-sans font-bold text-brand-text-primary dark:text-white mb-4 flex items-center gap-2">
							<i class="fa-solid fa-bullseye text-brand-red"></i>
							<?php esc_html_e( 'Executive Takeaways', 'ascendance' ); ?>
						</h4>
						<ul class="takeaways-list list-none p-0 m-0 flex flex-col gap-3 text-sm text-brand-text-muted dark:text-cream/70">
							<?php foreach ( $takeaways as $row ) : ?>
								<li class="flex items-start gap-2">
									<span class="takeaways-marker text-brand-red font-bold">✦</span>
									<span><?php echo esc_html( $row['takeaway'] ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<!-- Executive Summary (Visible to all) -->
				<?php if ( ! empty( $summary ) ) : ?>
					<div class="executive-summary-block text-base md:text-lg text-brand-text-primary dark:text-cream font-serif leading-relaxed mb-8 italic border-l-2 border-brand-divider-light dark:border-brand-divider-dark/20 pl-4">
						<?php echo esc_html( $summary ); ?>
					</div>
				<?php endif; ?>

				<!-- Gated content area wrapper for search engine crawlers (Google NewsArticle paywall guidelines) -->
				<?php if ( $user_has_access ) : ?>
					<div class="paywall-gated-content">
				<?php endif; ?>

				<!-- Main Content (Subject to paywall logic) -->
				<div class="main-body-content text-brand-text-primary dark:text-cream leading-relaxed mb-8">
					<?php the_content(); ?>
				</div>

				<!-- Key Findings Section (Gated) -->
				<?php if ( ! empty( $findings ) && $user_has_access ) : ?>
					<div class="key-findings-section bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-6 rounded-sm shadow-sm my-8">
						<h3 class="key-findings-title text-lg font-sans font-bold text-brand-text-primary dark:text-white mb-4 flex items-center gap-2 border-b border-brand-divider-light dark:border-brand-divider-dark/25 pb-3">
							<i class="fa-solid fa-list-check text-brand-red"></i>
							<?php esc_html_e( 'Key Findings & Analytical Breakdown', 'ascendance' ); ?>
						</h3>
						<div class="findings-content text-sm text-brand-text-muted dark:text-cream/70 leading-relaxed">
							<?php echo wp_kses_post( $findings ); ?>
						</div>
					</div>
				<?php endif; ?>

				<!-- Source References & Citations Table (Gated) -->
				<?php if ( ( ( ! empty( $references ) && is_array( $references ) ) || ( ! empty( $sources_details ) && is_array( $sources_details ) ) ) && $user_has_access ) : ?>
					<div class="sources-references-ledger bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-6 rounded-sm shadow-sm my-8">
						<h3 class="sources-ledger-title text-lg font-sans font-bold text-brand-text-primary dark:text-white mb-4 flex items-center gap-2 border-b border-brand-divider-light dark:border-brand-divider-dark/25 pb-3"><i class="fa-solid fa-feather-pointed text-brand-red"></i><?php esc_html_e( 'Source Ledger & References', 'ascendance' ); ?></h3>
						
						<table class="sources-table w-full border-collapse text-left text-sm text-brand-text-primary dark:text-cream">
							<thead>
								<tr class="border-b border-brand-divider-light dark:border-brand-divider-dark/20">
									<th class="pb-2.5 font-sans font-bold text-xs uppercase tracking-wider text-brand-text-muted dark:text-cream/50"><?php esc_html_e( 'Source Agency / Document', 'ascendance' ); ?></th>
									<th style="text-align: right;" class="pb-2.5 font-sans font-bold text-xs uppercase tracking-wider text-brand-text-muted dark:text-cream/50"><?php esc_html_e( 'Reference Route', 'ascendance' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php 
								// Use references repeater if populated, otherwise fallback to detailed sources
								$display_refs = ( ! empty( $references ) && is_array( $references ) ) ? $references : array();
								if ( empty( $display_refs ) && ! empty( $sources_details ) ) {
									foreach ( $sources_details as $src ) {
										$display_refs[] = array(
											'name' => $src['source_name'] . ( ! empty( $src['source_date'] ) ? ' (' . date( 'F j, Y', strtotime( $src['source_date'] ) ) . ')' : '' ),
											'url'  => $src['source_url']
										);
									}
								}

								foreach ( $display_refs as $ref ) : 
								?>
									<tr class="border-b border-brand-divider-light dark:border-brand-divider-dark/10 last:border-b-0">
										<td class="source-title-cell py-3.5 font-sans font-medium text-brand-text-primary dark:text-cream"><?php echo esc_html( $ref['name'] ); ?></td>
										<td class="source-route-cell py-3.5 text-right font-sans font-medium">
											<?php if ( ! empty( $ref['url'] ) ) : ?>
												<a href="<?php echo esc_url( $ref['url'] ); ?>" target="_blank" rel="noopener" class="text-brand-red hover:text-brand-red-light transition-colors flex items-center gap-1 justify-end"><i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i><?php esc_html_e( 'Access Source', 'ascendance' ); ?></a>
											<?php else : ?>
												<span class="source-classified text-brand-text-muted/50 dark:text-cream/30 italic"><?php esc_html_e( 'Classified / Internal Wire', 'ascendance' ); ?></span>
											<?php endif; ?>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>

				<!-- Real-time Timeline Updates Section (Gated) -->
				<?php if ( $updates_query->have_posts() && $user_has_access ) : ?>
					<div class="brief-timeline-updates my-8">
						<h3 class="timeline-title text-lg font-sans font-bold text-brand-text-primary dark:text-white mb-6 flex items-center gap-2">
							<i class="fa-solid fa-clock-rotate-left text-brand-red"></i>
							<?php esc_html_e( 'Real-time Timeline Updates', 'ascendance' ); ?>
						</h3>
						
						<div class="timeline-trail flex flex-col gap-6 relative before:content-[''] before:absolute before:left-3 before:top-2 before:bottom-2 before:w-0.5 before:bg-brand-divider-light dark:before:bg-brand-divider-dark/20 pl-8">
							<?php 
							while ( $updates_query->have_posts() ) :
								$updates_query->the_post();
								$up_id = get_the_ID();
								$up_impact = get_field( 'impact_assessment', $up_id ) ?: 'medium';
								$up_summary = get_field( 'one_line_summary', $up_id );
								?>
								<div class="timeline-node-item relative">
									<!-- Dot node -->
									<div class="timeline-node-dot absolute -left-[27px] top-1.5 w-2.5 h-2.5 bg-brand-red rounded-sm"></div>
									
									<div class="timeline-meta flex items-center gap-3 text-xs text-brand-text-muted dark:text-cream/50 font-sans mb-1">
										<span class="timeline-date"><?php echo get_the_date( 'd M Y, H:i' ); ?></span>
										<?php echo ascendance_impact_badge( $up_impact ); ?>
									</div>
									<h4 class="timeline-node-title text-sm font-sans font-bold text-brand-text-primary dark:text-white mb-1 hover:text-brand-red">
										<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
									</h4>
									<?php if ( ! empty( $up_summary ) ) : ?>
										<p class="timeline-node-summary text-xs text-brand-text-muted dark:text-cream/60 leading-relaxed"><?php echo esc_html( $up_summary ); ?></p>
									<?php endif; ?>
								</div>
							<?php 
							endwhile; 
							wp_reset_postdata();
							?>
						</div>
					</div>
				<?php endif; ?>

				<!-- Cross-referenced Briefings Section (Gated) -->
				<?php if ( ! empty( $related_briefs ) && $user_has_access ) : ?>
					<div class="brief-related-section bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-6 rounded-sm shadow-sm my-8">
						<h3 class="related-section-title text-lg font-sans font-bold text-brand-text-primary dark:text-white mb-6 flex items-center gap-2 border-b border-brand-divider-light dark:border-brand-divider-dark/25 pb-3">
							<i class="fa-solid fa-link text-brand-red"></i>
							<?php esc_html_e( 'Cross-Referenced Briefings', 'ascendance' ); ?>
						</h3>
						
						<div class="related-grid flex flex-col gap-3">
							<?php 
							foreach ( $related_briefs as $rel ) :
								$rel_id = $rel->ID;
								$rel_tiers = ascendance_get_post_tiers( $rel_id, 'essential' );
								$rel_tiers_labels = array_map( 'ucfirst', $rel_tiers );
								$rel_tiers_str = implode( ' / ', $rel_tiers_labels );
								$rel_topics = get_the_terms( $rel_id, 'topic' );
								$rel_topic_name = ( ! empty( $rel_topics ) && ! is_wp_error( $rel_topics ) ) ? $rel_topics[0]->name : 'Intelligence';
								?>
								<a href="<?php echo esc_url( get_permalink( $rel_id ) ); ?>" class="related-brief-link-card flex justify-between items-center p-4 bg-cream dark:bg-navy border border-brand-divider-light dark:border-brand-divider-dark/20 rounded-sm hover:border-brand-red transition-all duration-150">
									<div>
										<span class="related-brief-card-meta block text-[10px] font-mono tracking-widest text-brand-text-muted uppercase mb-1"><?php echo esc_html( $rel_topic_name ); ?> // <?php echo esc_html( $rel_tiers_str ); ?></span>
										<strong class="related-brief-card-title text-sm font-sans font-bold text-brand-text-primary dark:text-white"><?php echo esc_html( $rel->post_title ); ?></strong>
									</div>
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

			<!-- Footer tags and comment thread -->
			<footer class="editorial-footer border-t border-brand-divider-light dark:border-brand-divider-dark/20 pt-8 mt-12">
				<div class="editorial-tags-row flex flex-col sm:flex-row justify-between gap-4 text-xs font-sans text-brand-text-muted dark:text-cream/50 mb-8 [&_a]:text-brand-red hover:[&_a]:text-brand-red-light">
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
					<div class="comments-thread">
						<?php comments_template(); ?>
					</div>
				<?php endif; ?>
			</footer>
		</article>

		<?php
	endwhile;
	?>

</main>

<?php
get_footer();
