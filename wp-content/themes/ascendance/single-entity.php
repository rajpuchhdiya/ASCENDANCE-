<?php
/**
 * Single Entity Profile Template — Ascendance Intelligence Platform
 *
 * @package Ascendance
 */

get_header();
$post_id = get_the_ID();
$entity_mgr = \Ascendance\Core\Entity_Intelligence::get_instance();
$paywall    = \Ascendance\Core\Paywall::get_instance();
$user_id    = get_current_user_id();

$types      = wp_get_post_terms( $post_id, 'entity_type', array( 'fields' => 'names' ) );
$type_label = ( ! is_wp_error( $types ) && ! empty( $types ) ) ? $types[0] : 'Entity';

$off_name   = get_post_meta( $post_id, 'official_name', true );
$aliases    = get_post_meta( $post_id, 'alternate_names', true );
$desc       = get_post_meta( $post_id, 'short_description', true ) ?: get_the_excerpt();
$country    = get_post_meta( $post_id, 'country', true );
$website    = get_post_meta( $post_id, 'website', true );
$status     = get_post_meta( $post_id, 'entity_status', true ) ?: 'active';
$est_date   = get_post_meta( $post_id, 'established_date', true );

$alias_list = ! empty( $aliases ) ? array_filter( array_map( 'trim', explode( "\n", $aliases ) ) ) : array();

$status_colors = array(
    'active'    => '#27AE60',
    'inactive'  => '#7F8C8D',
    'proposed'  => '#2980B9',
    'suspended' => '#C0392B',
    'completed' => '#8E44AD',
);
$status_color = $status_colors[ $status ] ?? '#27AE60';

$related_content = $entity_mgr->get_entity_content( $post_id );
$relationships   = $entity_mgr->get_entity_relationships( $post_id );
$predicates      = $entity_mgr->get_relationship_types();

// Sort Related Content chronologically (post_date descending)
usort( $related_content, function( $a, $b ) {
    return strtotime( $b->post_date ) - strtotime( $a->post_date );
} );

// Calculate Aggregate Breakdown Statistics
$count_briefs   = 0;
$count_updates  = 0;
$count_dossiers = 0;

foreach ( $related_content as $item ) {
    if ( 'brief' === $item->post_type ) $count_briefs++;
    elseif ( 'update' === $item->post_type ) $count_updates++;
    elseif ( 'dossier' === $item->post_type ) $count_dossiers++;
}

$total_content = count( $related_content );
$total_entities_linked = ( isset( $relationships['direct'] ) ? count( $relationships['direct'] ) : 0 ) + ( isset( $relationships['inverse'] ) ? count( $relationships['inverse'] ) : 0 );

$latest_item = ! empty( $related_content ) ? $related_content[0] : null;
?>

<main id="primary" class="as-main">

	<article id="post-<?php the_ID(); ?>" class="as-article-page">
		<a href="<?php echo esc_url( home_url( '/entities/' ) ); ?>" class="as-back">&larr; Back to Entity Directory</a>

		<!-- Entity Header -->
		<header class="as-article-head">
			<div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
				<span class="as-kicker-slab big">// ENTITY PROFILE &middot; <?php echo esc_html( strtoupper( $type_label ) ); ?></span>
				<span style="font-size:10px; font-family:var(--font-mono); font-weight:bold; color:<?php echo esc_attr( $status_color ); ?>; text-transform:uppercase; background:rgba(0,0,0,0.04); padding:2px 8px; border-radius:3px;">
					● <?php echo esc_html( ucfirst( $status ) ); ?>
				</span>
			</div>

			<h1 class="as-article-title"><?php the_title(); ?></h1>

			<!-- Identity & Alias Badges -->
			<div style="display:flex; flex-wrap:wrap; align-items:center; gap:8px; margin-top:12px;">
				<?php if ( ! empty( $off_name ) && 0 !== strcasecmp( $off_name, get_the_title() ) ) : ?>
					<span style="font-size:12px; color:var(--ink-2); font-weight:600;">
						Official Name: <strong><?php echo esc_html( $off_name ); ?></strong>
					</span>
				<?php endif; ?>

				<?php if ( ! empty( $alias_list ) ) : ?>
					<span style="font-size:11px; font-family:var(--font-mono); color:var(--ink-3);">Also Known As:</span>
					<?php foreach ( $alias_list as $alias ) : ?>
						<span style="font-size:11px; font-family:var(--font-mono); background:var(--bg-2, #f5f5f5); border:1px solid var(--hairline, #ddd); padding:2px 6px; border-radius:3px;">
							<?php echo esc_html( $alias ); ?>
						</span>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</header>

		<!-- Layout Grid -->
		<div class="as-article-grid">
			<!-- Main Column -->
			<div class="as-article-main">

				<!-- Short Description -->
				<section class="as-section" style="margin-bottom:32px;">
					<h2 style="font-size:18px; font-weight:700; border-bottom:2px solid var(--accent, #BC1B1D); padding-bottom:6px; margin-bottom:14px;">Overview</h2>
					<div class="as-prose" style="font-size:15px; line-height:1.6;">
						<?php if ( ! empty( $desc ) ) : ?>
							<p><?php echo esc_html( $desc ); ?></p>
						<?php else : ?>
							<p style="color:var(--ink-3); font-style:italic;">Profile information is currently being developed by the Ascendance Analyst Desk.</p>
						<?php endif; ?>
					</div>
				</section>

				<!-- Latest Development Highlight Card -->
				<?php if ( $latest_item ) :
					$l_id       = $latest_item->ID;
					$l_has      = $paywall->user_has_access( $l_id, $user_id );
					$l_type     = get_post_type_labels( get_post_type_object( $latest_item->post_type ) )->singular_name ?? ucfirst( $latest_item->post_type );
					$l_date     = get_the_date( 'F Y', $l_id );
					$l_req_tier = get_field( 'tier_access', $l_id ) ?: 'essential';
				?>
				<section class="as-section" style="margin-bottom:32px;">
					<div style="background:var(--bg-2, #f9f9f9); border:1px solid var(--accent, #BC1B1D); border-left:5px solid var(--accent, #BC1B1D); padding:20px; border-radius:4px;">
						<div style="font-size:10px; font-family:var(--font-mono); text-transform:uppercase; color:var(--accent, #BC1B1D); font-weight:bold; margin-bottom:6px;">
							⚡ LATEST DEVELOPMENT &middot; <?php echo esc_html( $l_date ); ?>
						</div>
						<h3 style="font-size:18px; font-weight:700; margin:0 0 8px 0;">
							<a href="<?php echo esc_url( get_permalink( $l_id ) ); ?>" style="color:inherit; text-decoration:none;">
								<?php echo esc_html( get_the_title( $l_id ) ); ?>
							</a>
						</h3>
						<p style="font-size:13px; color:var(--ink-2); margin:0 0 12px 0;">
							<?php echo esc_html( get_the_excerpt( $l_id ) ); ?>
						</p>
						<a href="<?php echo esc_url( get_permalink( $l_id ) ); ?>" class="btn btn-primary" style="font-size:12px; padding:6px 14px; background:var(--accent, #BC1B1D); color:#fff; text-decoration:none; border-radius:3px; display:inline-block;">
							<?php echo $l_has ? 'Read Report &rarr;' : 'Clearance Required &rarr;'; ?>
						</a>
					</div>
				</section>
				<?php endif; ?>

				<!-- Intelligence Timeline Section -->
				<section class="as-section" style="margin-bottom:40px;">
					<div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid var(--accent, #BC1B1D); padding-bottom:6px; margin-bottom:16px;">
						<h2 style="font-size:18px; font-weight:700; margin:0;">
							Intelligence Timeline
						</h2>

						<!-- Timeline Type Filters -->
						<?php if ( ! empty( $related_content ) ) : ?>
						<div class="as-timeline-filters" style="display:flex; gap:6px;">
							<button type="button" class="btn-filter active" data-type="all" style="font-size:11px; padding:3px 8px; border:1px solid #ccc; background:#eee; cursor:pointer; border-radius:3px;">All (<?php echo $total_content; ?>)</button>
							<button type="button" class="btn-filter" data-type="brief" style="font-size:11px; padding:3px 8px; border:1px solid #ccc; background:#fff; cursor:pointer; border-radius:3px;">Briefs (<?php echo $count_briefs; ?>)</button>
							<button type="button" class="btn-filter" data-type="update" style="font-size:11px; padding:3px 8px; border:1px solid #ccc; background:#fff; cursor:pointer; border-radius:3px;">Updates (<?php echo $count_updates; ?>)</button>
							<button type="button" class="btn-filter" data-type="dossier" style="font-size:11px; padding:3px 8px; border:1px solid #ccc; background:#fff; cursor:pointer; border-radius:3px;">Dossiers (<?php echo $count_dossiers; ?>)</button>
						</div>
						<?php endif; ?>
					</div>

					<?php if ( empty( $related_content ) ) : ?>
						<div style="background:rgba(0,0,0,0.02); border:1px dashed var(--hairline, #ccc); padding:20px; text-align:center; border-radius:4px;">
							<p style="margin:0; color:var(--ink-3); font-size:13px;">No published intelligence is currently associated with this entity.</p>
						</div>
					<?php else : ?>
						<div class="as-brief-list" id="as-timeline-list" style="display:flex; flex-direction:column; gap:16px;">
							<?php foreach ( $related_content as $item ) :
								$item_id   = $item->ID;
								$has_access = $paywall->user_has_access( $item_id, $user_id );
								$item_type  = get_post_type_labels( get_post_type_object( $item->post_type ) )->singular_name ?? ucfirst( $item->post_type );
								$item_date  = get_the_date( 'd M Y', $item_id );
								$req_tier   = get_field( 'tier_access', $item_id ) ?: 'essential';
							?>
							<article class="as-brief-card timeline-card" data-post-type="<?php echo esc_attr( $item->post_type ); ?>" style="padding:16px; border:1px solid var(--hairline, #eee); border-radius:4px; background:#fff;">
								<div style="display:flex; justify-content:space-between; align-items:center; font-size:10px; font-family:var(--font-mono); text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">
									<span><?php echo esc_html( $item_type ); ?> &middot; <?php echo esc_html( $item_date ); ?></span>
									<?php if ( ! $has_access ) : ?>
										<span style="background:#C0392B; color:#fff; padding:2px 6px; border-radius:2px; font-weight:bold;">
											<?php echo esc_html( ucfirst( $req_tier ) ); ?> Clearance Required
										</span>
									<?php else : ?>
										<span style="color:#27AE60; font-weight:bold;">✔ Authorized</span>
									<?php endif; ?>
								</div>

								<h3 style="font-size:16px; font-weight:700; margin:0 0 8px 0;">
									<a href="<?php echo esc_url( get_permalink( $item_id ) ); ?>" style="color:inherit; text-decoration:none;">
										<?php echo esc_html( get_the_title( $item_id ) ); ?>
									</a>
								</h3>

								<p style="font-size:13px; color:var(--ink-2); margin:0 0 12px 0;">
									<?php echo esc_html( get_the_excerpt( $item_id ) ); ?>
								</p>

								<div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--hairline, #f0f0f0); padding-top:8px;">
									<span style="font-size:11px; font-family:var(--font-mono); color:var(--ink-3);">
										<?php the_terms( $item_id, 'topic', 'Desk: ', ', ' ); ?>
									</span>
									<a href="<?php echo esc_url( get_permalink( $item_id ) ); ?>" class="btn btn-ghost" style="font-size:11px; padding:4px 10px;">
										<?php echo $has_access ? 'Read Intelligence &rarr;' : 'Subscriber Access Required &rarr;'; ?>
									</a>
								</div>
							</article>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</section>

				<!-- Related Entities Graph Section -->
				<section class="as-section">
					<h2 style="font-size:18px; font-weight:700; border-bottom:2px solid var(--accent, #BC1B1D); padding-bottom:6px; margin-bottom:16px;">
						Connected Intelligence Graph
					</h2>

					<?php
					$has_direct  = ! empty( $relationships['direct'] );
					$has_inverse = ! empty( $relationships['inverse'] );

					if ( ! $has_direct && ! $has_inverse ) :
					?>
						<div style="background:rgba(0,0,0,0.02); border:1px dashed var(--hairline, #ccc); padding:20px; text-align:center; border-radius:4px;">
							<p style="margin:0; color:var(--ink-3); font-size:13px;">No direct entity relationships created yet.</p>
						</div>
					<?php else : ?>

						<!-- Direct Relationships -->
						<?php if ( $has_direct ) : ?>
							<div style="display:flex; flex-direction:column; gap:16px; margin-bottom:24px;">
								<?php
								$grouped = array();
								foreach ( $relationships['direct'] as $r ) {
									$p_key = $r['relationship_type'] ?? 'connected_to';
									$grouped[ $p_key ][] = $r;
								}

								foreach ( $grouped as $p_slug => $r_list ) :
									$p_name = $predicates[ $p_slug ] ?? ucfirst( str_replace( '_', ' ', $p_slug ) );
								?>
								<div style="background:#fff; border:1px solid var(--hairline, #eee); border-radius:4px; padding:16px;">
									<h3 style="font-size:12px; font-family:var(--font-mono); text-transform:uppercase; color:var(--accent, #BC1B1D); margin:0 0 12px 0; font-weight:bold;">
										// <?php echo esc_html( $p_name ); ?>
									</h3>
									<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:12px;">
										<?php foreach ( $r_list as $rel_item ) :
											$target_id = $rel_item['target_id'];
											$t_types   = wp_get_post_terms( $target_id, 'entity_type', array( 'fields' => 'names' ) );
											$t_type_lbl = ( ! is_wp_error( $t_types ) && ! empty( $t_types ) ) ? $t_types[0] : 'Entity';
										?>
										<a href="<?php echo esc_url( get_permalink( $target_id ) ); ?>" style="display:block; padding:10px; border:1px solid var(--hairline, #eee); border-radius:4px; text-decoration:none; color:inherit; background:var(--bg-2, #fafafa);">
											<span style="font-size:9px; font-family:var(--font-mono); text-transform:uppercase; color:var(--ink-3); font-weight:bold; display:block; margin-bottom:4px;">
												<?php echo esc_html( $t_type_lbl ); ?>
											</span>
											<h4 style="font-size:13px; font-weight:700; margin:0;"><?php echo esc_html( get_the_title( $target_id ) ); ?></h4>
											<?php if ( ! empty( $rel_item['notes'] ) ) : ?>
												<p style="font-size:11px; color:var(--ink-3); margin:4px 0 0 0;"><?php echo esc_html( $rel_item['notes'] ); ?></p>
											<?php endif; ?>
										</a>
										<?php endforeach; ?>
									</div>
								</div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

						<!-- Inverse Relationships -->
						<?php if ( $has_inverse ) : ?>
							<div style="background:#fff; border:1px solid var(--hairline, #eee); border-radius:4px; padding:16px;">
								<h3 style="font-size:12px; font-family:var(--font-mono); text-transform:uppercase; color:var(--ink-3); margin:0 0 12px 0; font-weight:bold;">
									// INBOUND ENTITY CONNECTIONS
								</h3>
								<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:12px;">
									<?php foreach ( $relationships['inverse'] as $inv ) :
										$src_id   = $inv['source_id'];
										$src_type = $predicates[ $inv['relationship_type'] ] ?? $inv['relationship_type'];
									?>
									<a href="<?php echo esc_url( get_permalink( $src_id ) ); ?>" style="display:block; padding:10px; border:1px solid var(--hairline, #eee); border-radius:4px; text-decoration:none; color:inherit; background:var(--bg-2, #fafafa);">
										<span style="font-size:9px; font-family:var(--font-mono); text-transform:uppercase; color:var(--accent, #BC1B1D); font-weight:bold; display:block; margin-bottom:4px;">
											<?php echo esc_html( $src_type ); ?> &rarr; This Entity
										</span>
										<h4 style="font-size:13px; font-weight:700; margin:0;"><?php echo esc_html( $inv['source_title'] ); ?></h4>
									</a>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>

					<?php endif; ?>
				</section>

			</div>

			<!-- Right Sidebar Rail -->
			<aside class="as-rail-sticky">
				<!-- Intelligence Activity Summary Panel -->
				<div class="as-rail-panel" style="margin-bottom:20px;">
					<div class="as-rail-head">Intelligence Breakdown</div>
					<dl class="as-keyfacts">
						<div><dt>Total Reports</dt><dd><strong><?php echo $total_content; ?></strong></dd></div>
						<div><dt>Briefings</dt><dd><?php echo $count_briefs; ?></dd></div>
						<div><dt>Updates</dt><dd><?php echo $count_updates; ?></dd></div>
						<div><dt>Dossiers</dt><dd><?php echo $count_dossiers; ?></dd></div>
						<div><dt>Graph Links</dt><dd><?php echo $total_entities_linked; ?></dd></div>
					</dl>
				</div>

				<!-- Entity Metadata Panel -->
				<div class="as-rail-panel">
					<div class="as-rail-head">Entity Metadata</div>
					<dl class="as-keyfacts">
						<div><dt>Type</dt><dd><?php echo esc_html( $type_label ); ?></dd></div>
						<div><dt>Status</dt><dd style="color:<?php echo esc_attr( $status_color ); ?>; font-weight:bold;"><?php echo esc_html( ucfirst( $status ) ); ?></dd></div>
						<?php if ( ! empty( $country ) ) : ?>
							<div><dt>Country</dt><dd><?php echo esc_html( $country ); ?></dd></div>
						<?php endif; ?>
						<?php if ( ! empty( $est_date ) ) : ?>
							<div><dt>Established</dt><dd><?php echo esc_html( $est_date ); ?></dd></div>
						<?php endif; ?>
						<?php if ( ! empty( $website ) ) : ?>
							<div><dt>Website</dt><dd><a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer" style="color:var(--accent, #BC1B1D); font-weight:bold;">Official Site &rarr;</a></dd></div>
						<?php endif; ?>
					</dl>
				</div>
			</aside>
		</div>
	</article>

</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
	var buttons = document.querySelectorAll('.btn-filter');
	var cards   = document.querySelectorAll('.timeline-card');

	buttons.forEach(function(btn) {
		btn.addEventListener('click', function() {
			var type = this.getAttribute('data-type');
			buttons.forEach(function(b) {
				b.classList.remove('active');
				b.style.background = '#fff';
			});
			this.classList.add('active');
			this.style.background = '#eee';

			cards.forEach(function(card) {
				if (type === 'all' || card.getAttribute('data-post-type') === type) {
					card.style.display = 'block';
				} else {
					card.style.display = 'none';
				}
			});
		});
	});
});
</script>

<?php
get_footer();
