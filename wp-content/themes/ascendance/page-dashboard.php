<?php
/**
 * Template Name: Subscriber Account Dashboard
 *
 * @package Ascendance
 */

get_header();



$current_user = wp_get_current_user();
$display_name = $current_user->exists() ? $current_user->display_name : 'Guest';
$initials     = $current_user->exists()
	? strtoupper( substr( $current_user->first_name, 0, 1 ) . substr( $current_user->last_name, 0, 1 ) )
	: 'AS';
if ( empty( trim( $initials ) ) ) { $initials = 'AS'; }

/* ------------------------------------------------------------------ */
/*  Load saved posts                                                   */
/* ------------------------------------------------------------------ */
$saved_ids = [];
$saved_posts_data = [];

if ( $current_user->exists() ) {
	$saved_ids = array_filter( array_map( 'intval', (array) get_user_meta( $current_user->ID, 'as_saved_posts', true ) ) );
	if ( ! empty( $saved_ids ) ) {
		$q = new WP_Query( [
			'post__in'       => $saved_ids,
			'post_type'      => [ 'post', 'brief', 'update', 'dossier' ],
			'posts_per_page' => -1,
			'orderby'        => 'post__in',
			'post_status'    => 'publish',
		] );
		$saved_posts_data = $q->posts;
	}
}

$saved_count = count( $saved_ids );

/* ------------------------------------------------------------------ */
/*  Save time helper                                                   */
/* ------------------------------------------------------------------ */
function as_human_time( $post_id ) {
	$meta   = get_post_meta( $post_id, 'as_saved_timestamp', true );
	$ts     = $meta ? intval( $meta ) : get_post_time( 'U', true, $post_id );
	$diff   = time() - $ts;
	if ( $diff < 3600 )   return 'Saved just now';
	if ( $diff < 86400 )  return 'Saved today';
	if ( $diff < 172800 ) return 'Saved yesterday';
	$days   = floor( $diff / 86400 );
	if ( $days < 7 )      return "Saved {$days} days ago";
	if ( $days < 14 )     return 'Saved last week';
	return 'Saved ' . human_time_diff( $ts ) . ' ago';
}
?>

<div class="d-wrap">

	<!-- Sidebar -->
	<aside class="d-side">
		<div class="d-acct">
			<div class="nm"><?php echo esc_html( $display_name ); ?></div>
			<div class="role">Ascendance Subscriber</div>
			<div class="d-badge">Subscriber &middot; Professional Tier</div>
		</div>
		<nav class="d-nav" id="dash-nav">
			<button type="button" data-view="overview" class="active">Overview</button>
			<button type="button" data-view="history">My Reading</button>
			<button type="button" data-view="saved">Saved briefs <span class="ct" id="saved-count-badge"><?php echo $saved_count; ?></span></button>
			<button type="button" data-view="notes">Private Notes</button>
			<button type="button" data-view="recs">Recommendations</button>
			<button type="button" data-view="prefs">Reading preferences</button>
			<button type="button" data-view="addons">Add-ons</button>
			<button type="button" data-view="billing">Billing &amp; plan</button>
			<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="out" style="display:block; padding:11px 12px; font-weight:600; color:var(--ink-3);">Sign out</a>
		</nav>
	</aside>

	<!-- Content -->
	<main>

		<!-- OVERVIEW TAB -->
		<section class="view show" id="v-overview">
			<div class="view-head">
				<h1>Subscriber Overview</h1>
				<p>Your private workspace and real-time intelligence feed.</p>
			</div>

			<!-- Continue Reading Hero Card -->
			<?php
			$continue_item = null;
			if ( class_exists( 'Ascendance\Core\Member_Dashboard' ) && $current_user->exists() ) {
				$continue_item = \Ascendance\Core\Member_Dashboard::get_instance()->get_continue_reading( $current_user->ID );
			}
			if ( $continue_item && ! empty( $continue_item['post_id'] ) ) :
				$c_post = get_post( $continue_item['post_id'] );
				if ( $c_post ) :
					$c_prog = isset( $continue_item['progress'] ) ? round( $continue_item['progress'] ) : 0;
			?>
			<div class="panel" style="border-left: 3px solid var(--accent, #BC1B1D); background: rgba(188,27,29,0.03); margin-bottom: 24px;">
				<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
					<span style="font-size: 9px; font-family: var(--font-mono); text-transform: uppercase; letter-spacing: 1px; color: var(--accent, #BC1B1D); font-weight: bold;">CONTINUE READING // <?php echo esc_html( strtoupper( get_post_type( $c_post ) ) ); ?></span>
					<span style="font-size: 10px; font-family: var(--font-mono); color: var(--ink-3);"><?php echo esc_html( $c_prog ); ?>% COMPLETED</span>
				</div>
				<h3 style="font-size: 16px; font-weight: 700; margin: 0 0 10px 0;"><a href="<?php echo esc_url( get_permalink( $c_post ) ); ?>" style="color: inherit; text-decoration: none;"><?php echo esc_html( get_the_title( $c_post ) ); ?></a></h3>
				<div style="width: 100%; height: 4px; background: rgba(0,0,0,0.1); border-radius: 2px; overflow: hidden; margin-bottom: 12px;">
					<div style="width: <?php echo esc_attr( $c_prog ); ?>%; height: 100%; background: var(--accent, #BC1B1D);"></div>
				</div>
				<a href="<?php echo esc_url( get_permalink( $c_post ) ); ?>" class="btn btn-primary" style="font-size: 11px; padding: 6px 14px;">Resume Reading &rarr;</a>
			</div>
			<?php endif; else : ?>
			<div class="panel" style="border-left: 3px solid #27AE60; background: rgba(39,174,96,0.03); margin-bottom: 24px; padding: 16px;">
				<div style="display: flex; align-items: center; gap: 12px;">
					<i class="fa-solid fa-check-circle" style="color: #27AE60; font-size: 20px;"></i>
					<div>
						<div style="font-size: 13px; font-weight: 700; color: var(--ink-1);">You're all caught up.</div>
						<div style="font-size: 11px; color: var(--ink-3);">No in-progress intelligence briefings requiring completion.</div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<!-- Quick Stats Grid -->
			<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px;">
				<div class="panel" style="padding: 16px; text-align: center;">
					<div style="font-size: 20px; font-weight: 800; color: var(--accent, #BC1B1D); font-family: var(--font-mono);"><?php echo $saved_count; ?></div>
					<div style="font-size: 11px; text-transform: uppercase; font-family: var(--font-mono); color: var(--ink-3); margin-top: 4px;">Saved Briefs</div>
				</div>
				<div class="panel" style="padding: 16px; text-align: center;">
					<div style="font-size: 20px; font-weight: 800; color: #2980B9; font-family: var(--font-mono);">
						<?php 
						$history_count = 0;
						if ( class_exists( 'Ascendance\Core\Member_Dashboard' ) && $current_user->exists() ) {
							$history_count = count( \Ascendance\Core\Member_Dashboard::get_instance()->get_reading_history( $current_user->ID, 50 ) );
						}
						echo $history_count;
						?>
					</div>
					<div style="font-size: 11px; text-transform: uppercase; font-family: var(--font-mono); color: var(--ink-3); margin-top: 4px;">Articles Read</div>
				</div>
				<div class="panel" style="padding: 16px; text-align: center;">
					<div style="font-size: 20px; font-weight: 800; color: #27AE60; font-family: var(--font-mono);">
						<?php
						$pref_topics = (array) get_user_meta( $current_user->ID, 'preferred_topics', true );
						echo count( array_filter( $pref_topics ) );
						?>
					</div>
					<div style="font-size: 11px; text-transform: uppercase; font-family: var(--font-mono); color: var(--ink-3); margin-top: 4px;">Subscribed Topics</div>
				</div>
			</div>

			<!-- Recent Activity Feed -->
			<div class="panel">
				<h2>Latest Intelligence Briefings</h2>
				<?php
				$overview_q = new WP_Query( array(
					'post_type'      => array( 'brief', 'dossier', 'update' ),
					'posts_per_page' => 4,
					'post_status'    => 'publish',
				) );
				if ( $overview_q->have_posts() ) :
					while ( $overview_q->have_posts() ) : $overview_q->the_post();
						get_template_part( 'template-parts/intelligence-card' );
					endwhile;
					wp_reset_postdata();
				endif;
				?>
			</div>
		</section>

		<!-- MY READING TAB (Reading History) -->
		<section class="view" id="v-history">
			<div class="view-head" style="display: flex; justify-content: space-between; align-items: flex-end;">
				<div>
					<h1>My Reading History</h1>
					<p>Your recent reading telemetry and progress tracking.</p>
				</div>
				<button type="button" id="btn-clear-reading-history" class="btn btn-ghost" style="font-size: 11px; color: #C0392B; padding: 6px 12px; margin-bottom: 8px;">
					<i class="fa-solid fa-trash" style="margin-right: 4px;"></i> Clear All History
				</button>
			</div>

			<!-- History Filter Bar -->
			<div class="history-filter-bar" style="margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 12px; align-items: center; background: rgba(0,0,0,0.02); padding: 12px 16px; border-radius: 4px; border: 1px solid var(--hairline, #eee);">
				<div>
					<label style="font-size: 10px; font-family: var(--font-mono); text-transform: uppercase; color: var(--ink-3); font-weight: bold; display: block; margin-bottom: 2px;">Content Type</label>
					<select id="filter-history-type" style="font-size: 12px; padding: 4px 8px; border-radius: 3px; border: 1px solid var(--hairline, #ccc);">
						<option value="all">All Intelligence Types</option>
						<option value="brief">Briefs</option>
						<option value="update">Updates</option>
						<option value="dossier">Dossiers</option>
					</select>
				</div>
				<div>
					<label style="font-size: 10px; font-family: var(--font-mono); text-transform: uppercase; color: var(--ink-3); font-weight: bold; display: block; margin-bottom: 2px;">Topic Desk</label>
					<select id="filter-history-topic" style="font-size: 12px; padding: 4px 8px; border-radius: 3px; border: 1px solid var(--hairline, #ccc);">
						<option value="0">All Topics</option>
						<?php
						$db_topics = get_terms( array( 'taxonomy' => 'topic', 'hide_empty' => false ) );
						if ( isset( $db_topics ) && ! is_wp_error( $db_topics ) && ! empty( $db_topics ) ) {
							foreach ( $db_topics as $t ) {
								echo '<option value="' . esc_attr( $t->term_id ) . '">' . esc_html( $t->name ) . '</option>';
							}
						}
						?>
					</select>
				</div>
				<div>
					<label style="font-size: 10px; font-family: var(--font-mono); text-transform: uppercase; color: var(--ink-3); font-weight: bold; display: block; margin-bottom: 2px;">Region</label>
					<select id="filter-history-region" style="font-size: 12px; padding: 4px 8px; border-radius: 3px; border: 1px solid var(--hairline, #ccc);">
						<option value="0">All Regions</option>
						<?php
						$db_regions = get_terms( array( 'taxonomy' => 'region', 'hide_empty' => false ) );
						if ( isset( $db_regions ) && ! is_wp_error( $db_regions ) && ! empty( $db_regions ) ) {
							foreach ( $db_regions as $r ) {
								echo '<option value="' . esc_attr( $r->term_id ) . '">' . esc_html( $r->name ) . '</option>';
							}
						}
						?>
					</select>
				</div>
			</div>

			<div class="panel" id="history-list">
				<?php
				$history_items = array();
				if ( class_exists( 'Ascendance\Core\Member_Dashboard' ) && $current_user->exists() ) {
					$history_items = \Ascendance\Core\Member_Dashboard::get_instance()->get_reading_history( $current_user->ID, 50 );
				}
				if ( ! empty( $history_items ) ) :
					foreach ( $history_items as $h_item ) :
						$h_post_id = isset( $h_item['post_id'] ) ? (int) $h_item['post_id'] : 0;
						$h_post    = get_post( $h_post_id );
						if ( ! $h_post ) continue;
						$h_prog    = isset( $h_item['progress'] ) ? round( (float) $h_item['progress'] ) : 0;
						$h_ts      = isset( $h_item['timestamp'] ) ? (int) $h_item['timestamp'] : get_post_time( 'U', true, $h_post_id );
				?>
				<div class="history-item-row" data-post-id="<?php echo $h_post_id; ?>" style="padding: 14px 0; border-bottom: 1px solid var(--hairline, #eee); display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;">
					<div style="flex: 1;">
						<div style="font-size: 9px; font-family: var(--font-mono); text-transform: uppercase; color: var(--ink-3); font-weight: bold; margin-bottom: 4px;">
							<?php echo esc_html( get_post_type( $h_post ) ); ?> &middot; Read <?php echo esc_html( human_time_diff( $h_ts ) ); ?> ago
							<?php if ( $h_prog >= 95 ) : ?>
								&middot; <span style="color:#27AE60; font-weight:bold;">✔ COMPLETED</span>
							<?php endif; ?>
						</div>
						<h3 style="margin: 0 0 8px 0; font-size: 14px; font-weight: 700;"><a href="<?php echo esc_url( get_permalink( $h_post ) ); ?>" style="color: inherit; text-decoration: none;"><?php echo esc_html( get_the_title( $h_post ) ); ?></a></h3>
						<div style="display: flex; align-items: center; gap: 10px; max-width: 300px;">
							<div style="flex: 1; height: 4px; background: rgba(0,0,0,0.08); border-radius: 2px; overflow: hidden;">
								<div style="width: <?php echo esc_attr( $h_prog ); ?>%; height: 100%; background: <?php echo $h_prog >= 95 ? '#27AE60' : 'var(--accent, #BC1B1D)'; ?>;"></div>
							</div>
							<span style="font-size: 10px; font-family: var(--font-mono); color: var(--ink-3); font-weight: bold;"><?php echo esc_html( $h_prog ); ?>%</span>
						</div>
					</div>
					<div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0; margin-top: 4px;">
						<a href="<?php echo esc_url( get_permalink( $h_post ) ); ?>" class="btn btn-ghost" style="font-size: 11px; padding: 6px 12px;">
							<?php echo $h_prog >= 95 ? 'Revisit' : 'Continue'; ?> &rarr;
						</a>
						<button type="button" class="btn-remove-history-item btn btn-ghost" data-post-id="<?php echo $h_post_id; ?>" style="font-size: 11px; padding: 6px 8px; color: #C0392B;" title="Remove from history">&times;</button>
					</div>
				</div>
				<?php
					endforeach;
				else :
				?>
				<div class="saved-empty" id="history-empty-state">
					<svg width="40" height="40" fill="none" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
					<p>No reading history recorded yet.</p>
					<p class="hint">Open any Intelligence Brief, Update, or Dossier while signed in to track your reading progress automatically.</p>
				</div>
				<?php endif; ?>
			</div>
		</section>

		<!-- SAVED BRIEFS (dynamic workspace) -->
		<section class="view" id="v-saved">
			<div class="view-head">
				<h1>Saved briefs</h1>
				<p id="saved-summary">
					<?php if ( $saved_count > 0 ) : ?>
						<?php echo $saved_count; ?> briefing<?php echo $saved_count !== 1 ? 's' : ''; ?> saved to your reading list.
					<?php else : ?>
						Your reading list is empty. Hit the <strong>Save</strong> button on any brief or article.
					<?php endif; ?>
				</p>
			</div>

			<!-- Filter & Sort Controls Bar -->
			<div class="saved-filter-bar" style="margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 12px; align-items: center; background: rgba(0,0,0,0.02); padding: 12px 16px; border-radius: 4px; border: 1px solid var(--hairline, #eee);">
				<div>
					<label style="font-size: 10px; font-family: var(--font-mono); text-transform: uppercase; color: var(--ink-3); font-weight: bold; display: block; margin-bottom: 2px;">Content Type</label>
					<select id="filter-saved-type" style="font-size: 12px; padding: 4px 8px; border-radius: 3px; border: 1px solid var(--hairline, #ccc);">
						<option value="all">All Intelligence Types</option>
						<option value="brief">Briefs</option>
						<option value="update">Updates</option>
						<option value="dossier">Dossiers</option>
					</select>
				</div>
				<div>
					<label style="font-size: 10px; font-family: var(--font-mono); text-transform: uppercase; color: var(--ink-3); font-weight: bold; display: block; margin-bottom: 2px;">Topic Desk</label>
					<select id="filter-saved-topic" style="font-size: 12px; padding: 4px 8px; border-radius: 3px; border: 1px solid var(--hairline, #ccc);">
						<option value="0">All Topics</option>
						<?php
						$db_topics = get_terms( array( 'taxonomy' => 'topic', 'hide_empty' => false ) );
						if ( ! is_wp_error( $db_topics ) && ! empty( $db_topics ) ) {
							foreach ( $db_topics as $t ) {
								echo '<option value="' . esc_attr( $t->term_id ) . '">' . esc_html( $t->name ) . '</option>';
							}
						}
						?>
					</select>
				</div>
				<div>
					<label style="font-size: 10px; font-family: var(--font-mono); text-transform: uppercase; color: var(--ink-3); font-weight: bold; display: block; margin-bottom: 2px;">Region</label>
					<select id="filter-saved-region" style="font-size: 12px; padding: 4px 8px; border-radius: 3px; border: 1px solid var(--hairline, #ccc);">
						<option value="0">All Regions</option>
						<?php
						$db_regions = get_terms( array( 'taxonomy' => 'region', 'hide_empty' => false ) );
						if ( ! is_wp_error( $db_regions ) && ! empty( $db_regions ) ) {
							foreach ( $db_regions as $r ) {
								echo '<option value="' . esc_attr( $r->term_id ) . '">' . esc_html( $r->name ) . '</option>';
							}
						}
						?>
					</select>
				</div>
				<div>
					<label style="font-size: 10px; font-family: var(--font-mono); text-transform: uppercase; color: var(--ink-3); font-weight: bold; display: block; margin-bottom: 2px;">Sort Order</label>
					<select id="filter-saved-order" style="font-size: 12px; padding: 4px 8px; border-radius: 3px; border: 1px solid var(--hairline, #ccc);">
						<option value="recently_saved">Recently Saved</option>
						<option value="oldest_saved">Oldest Saved</option>
						<option value="recently_published">Recently Published</option>
						<option value="oldest_published">Oldest Published</option>
					</select>
				</div>
			</div>

			<div class="saved" id="saved-list">
				<?php if ( ! empty( $saved_posts_data ) ) : ?>
					<?php foreach ( $saved_posts_data as $sp ) :
						$post_id   = $sp->ID;
						$title     = get_the_title( $sp );
						$permalink = get_permalink( $sp );
						$terms     = get_the_terms( $post_id, 'topic' );
						$topic_str = ( $terms && ! is_wp_error( $terms ) )
							? implode( ', ', wp_list_pluck( $terms, 'name' ) )
							: ucfirst( str_replace( '-', ' ', $sp->post_type ) );
						$type_label = get_post_type_labels( get_post_type_object( $sp->post_type ) )->singular_name ?? ucfirst( $sp->post_type );
						$date_label = get_the_date( 'j M Y', $sp );
					?>
					<div class="saved-item" data-post-id="<?php echo $post_id; ?>">
						<div class="body">
							<div class="k"><?php echo esc_html( $type_label ); ?> &middot; <?php echo esc_html( $date_label ); ?></div>
							<h3><a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a></h3>
							<div class="meta">
								<span class="dot"></span><?php echo esc_html( $topic_str ); ?>
								<span class="sep">&middot;</span>
								<span class="save-time"><?php echo esc_html( as_human_time( $post_id ) ); ?></span>
							</div>
						</div>
						<div style="display: flex; flex-direction: column; gap: 6px; align-items: flex-end;">
							<?php if ( in_array( $sp->post_type, array( 'brief', 'dossier' ), true ) && class_exists( 'Ascendance\Core\Paywall' ) && Ascendance\Core\Paywall::get_instance()->user_has_access( $post_id, $user_id ) ) : ?>
							<button type="button" class="btn-export-pdf btn btn-ghost" data-post-id="<?php echo $post_id; ?>" style="font-size: 11px; padding: 4px 8px;" title="Export PDF">
								<i class="fa-solid fa-file-pdf" style="color:#C0392B;"></i> PDF
							</button>
							<?php endif; ?>
							<button
								type="button"
								class="unsave-btn"
								data-post-id="<?php echo $post_id; ?>"
								title="Remove from reading list"
								aria-label="Remove from saved">
								&times; Remove
							</button>
						</div>
					</div>
					<?php endforeach; ?>
				<?php else : ?>
					<div class="saved-empty" id="saved-empty-state">
						<svg width="40" height="40" fill="none" viewBox="0 0 24 24"><path d="M5 5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16l-7-3.5L5 21V5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>
						<p>No saved briefs yet.</p>
						<p class="hint">Click the <strong>Save for later</strong> button on any brief, update, or dossier to add it here.</p>
						<a href="<?php echo esc_url( home_url( '/latest/' ) ); ?>" class="btn btn-primary" style="margin-top:16px;">Browse latest briefs</a>
					</div>
				<?php endif; ?>
			</div>
		</section>

		<!-- PRIVATE NOTES TAB -->
		<section class="view" id="v-notes">
			<div class="view-head">
				<h1>Private Subscriber Notes</h1>
				<p>Your encrypted research takeaways and private briefing annotations.</p>
			</div>
			<div class="panel">
				<?php
				$user_notes = array();
				if ( class_exists( 'Ascendance\Core\Member_Dashboard' ) && $current_user->exists() ) {
					$user_notes = \Ascendance\Core\Member_Dashboard::get_instance()->get_user_notes( $current_user->ID );
				}
				if ( ! empty( $user_notes ) ) :
					foreach ( $user_notes as $n_post_id => $n_data ) :
						$n_title = isset( $n_data['title'] ) ? $n_data['title'] : get_the_title( $n_post_id );
						$n_link  = isset( $n_data['permalink'] ) ? $n_data['permalink'] : get_permalink( $n_post_id );
						$n_text  = isset( $n_data['note'] ) ? $n_data['note'] : '';
						$n_time  = isset( $n_data['updated_at'] ) ? human_time_diff( $n_data['updated_at'] ) . ' ago' : '';
				?>
				<div class="note-item-card" data-post-id="<?php echo esc_attr( $n_post_id ); ?>" style="padding: 16px 0; border-bottom: 1px solid var(--hairline, #eee);">
					<div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px;">
						<div>
							<div style="font-size: 9px; font-family: var(--font-mono); text-transform: uppercase; color: var(--accent, #BC1B1D); font-weight: bold;">
								PRIVATE NOTE &middot; Updated <?php echo esc_html( $n_time ); ?>
							</div>
							<h3 style="margin: 4px 0 8px 0; font-size: 15px; font-weight: 700;">
								<a href="<?php echo esc_url( $n_link ); ?>" style="color: inherit; text-decoration: none;"><?php echo esc_html( $n_title ); ?></a>
							</h3>
						</div>
						<div style="display: flex; gap: 6px;">
							<a href="<?php echo esc_url( $n_link ); ?>" class="btn btn-ghost" style="font-size: 10px; padding: 4px 10px;">View Brief &rarr;</a>
							<button type="button" class="btn-delete-dash-note btn btn-ghost" data-post-id="<?php echo esc_attr( $n_post_id ); ?>" style="font-size: 10px; padding: 4px 8px; color: #C0392B;">Delete</button>
						</div>
					</div>
					<div style="background: rgba(0,0,0,0.03); padding: 12px; border-left: 3px solid var(--accent, #BC1B1D); font-size: 13px; color: var(--ink-2); line-height: 1.5; border-radius: 2px; margin-top: 6px;">
						<?php echo nl2br( esc_html( $n_text ) ); ?>
					</div>
				</div>
				<?php
					endforeach;
				else :
				?>
				<div class="saved-empty">
					<svg width="40" height="40" fill="none" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
					<p>No private subscriber notes recorded yet.</p>
					<p class="hint">Open any Intelligence Briefing and use the <strong>Private Subscriber Notes</strong> panel in the sidebar to jot down encrypted research notes.</p>
				</div>
				<?php endif; ?>
			</div>
		</section>

		<!-- RECOMMENDATIONS / FOR YOU TAB -->
		<section class="view" id="v-recs">
			<div class="view-head">
				<h1>Personalized "For You" Intelligence Feed</h1>
				<p>Tailored briefings generated from your topic preferences, region focus, reading history, and clearance tier.</p>
			</div>
			<div class="panel">
				<?php
				if ( class_exists( 'Ascendance\Core\Recommendation_Engine' ) && $current_user->exists() ) {
					$recs = \Ascendance\Core\Recommendation_Engine::get_instance()->get_ranked_recommendations( $current_user->ID, array( 'brief', 'dossier', 'update' ), 6 );
					if ( ! empty( $recs ) ) {
						foreach ( $recs as $r_item ) {
							$r_post = $r_item['post'];
							$r_score = isset( $r_item['score_details']['total_score'] ) ? $r_item['score_details']['total_score'] : 0;
							$r_reason = isset( $r_item['reason'] ) ? $r_item['reason'] : __( 'Recommended for your tier', 'ascendance-core' );
							$r_reason_type = isset( $r_item['reason_type'] ) ? $r_item['reason_type'] : 'trending';
							$r_prog = isset( $r_item['progress'] ) ? round( (float) $r_item['progress'] ) : 0;
							$r_is_saved = ! empty( $r_item['is_saved'] );
							$r_is_locked = ! empty( $r_item['is_locked'] );
				?>
				<div class="rec-card" style="padding: 16px 0; border-bottom: 1px solid var(--hairline, #eee); display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;">
					<div style="flex: 1;">
						<div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 6px;">
							<span style="font-size: 9px; font-family: var(--font-mono); text-transform: uppercase; color: var(--accent, #BC1B1D); font-weight: bold; background: rgba(188,27,29,0.06); padding: 2px 8px; border-radius: 3px;">
								<?php echo esc_html( get_post_type( $r_post ) ); ?>
							</span>
							<span style="font-size: 10px; font-family: var(--font-mono); color: #27AE60; background: rgba(39,174,96,0.08); padding: 2px 8px; border-radius: 10px; font-weight: 600;">
								&bull; <?php echo esc_html( $r_reason ); ?>
							</span>
							<span style="font-size: 9px; font-family: var(--font-mono); color: var(--ink-3);">
								Relevancy: <?php echo esc_html( $r_score ); ?> pts
							</span>
						</div>
						<h3 style="margin: 4px 0 6px 0; font-size: 15px; font-weight: 700; line-height: 1.4;">
							<a href="<?php echo esc_url( get_permalink( $r_post ) ); ?>" style="color: inherit; text-decoration: none;">
								<?php echo esc_html( get_the_title( $r_post ) ); ?>
							</a>
						</h3>
						<div style="font-size: 11px; color: var(--ink-3); font-family: var(--font-mono); display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
							<span>Desk: <strong><?php echo esc_html( $r_item['topic_name'] ); ?></strong></span>
							<span>&middot;</span>
							<span>Region: <strong><?php echo esc_html( $r_item['region_name'] ); ?></strong></span>
							<span>&middot;</span>
							<span><?php echo esc_html( $r_item['date_label'] ); ?></span>
						</div>

						<?php if ( $r_prog > 0 && $r_prog < 95 ) : ?>
						<div style="margin-top: 10px; max-width: 280px; display: flex; align-items: center; gap: 8px;">
							<div style="flex: 1; height: 4px; background: rgba(0,0,0,0.08); border-radius: 2px; overflow: hidden;">
								<div style="width: <?php echo esc_attr( $r_prog ); ?>%; height: 100%; background: var(--accent, #BC1B1D);"></div>
							</div>
							<span style="font-size: 9px; font-family: var(--font-mono); color: var(--ink-3); font-weight: bold;"><?php echo esc_html( $r_prog ); ?>%</span>
						</div>
						<?php endif; ?>
					</div>

					<div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px; flex-shrink: 0; margin-top: 4px;">
						<?php if ( $r_is_locked ) : ?>
							<span style="font-size: 9px; font-family: var(--font-mono); color: #C0392B; text-transform: uppercase; font-weight: bold;">Locked (Requires <?php echo esc_html( ucfirst( $r_item['required_tier'] ) ); ?>)</span>
							<a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>" class="btn btn-primary" style="font-size: 10px; padding: 5px 12px; background: #C0392B; border-color: #C0392B;">Upgrade Tier &rarr;</a>
						<?php else : ?>
							<a href="<?php echo esc_url( get_permalink( $r_post ) ); ?>" class="btn btn-primary" style="font-size: 11px; padding: 6px 14px;">
								<?php echo $r_prog > 0 ? 'Resume Reading &rarr;' : 'Read Brief &rarr;'; ?>
							</a>
							<button type="button" class="unsave-btn btn-toggle-save-rec" data-post-id="<?php echo esc_attr( $r_post->ID ); ?>" style="font-size: 10px; padding: 3px 8px; border: 1px solid var(--hairline, #ccc); border-radius: 3px; background: transparent; cursor: pointer;">
								<?php echo $r_is_saved ? '&check; Saved' : '+ Save'; ?>
							</button>
						<?php endif; ?>
					</div>
				</div>
				<?php
						}
					} else {
						echo '<p style="font-size: 13px; color: var(--ink-3);">No personalized recommendations generated yet. Customize your reading preferences in the <strong>Reading preferences</strong> tab to prime the recommendation engine.</p>';
					}
				}
				?>
			</div>
		</section>

		<!-- PREFERENCES -->
		<section class="view" id="v-prefs">
			<div class="view-head">
				<h1>Reading preferences</h1>
				<p>Tune what surfaces first and how often we reach you.</p>
			</div>
			<div class="panel">
				<h2>Topics</h2>
				<p class="hint">Briefings on these desks are prioritised in your feed.</p>
				<div class="field">
					<div class="chips" id="topic-chips" data-toggle>
						<?php
						$user_saved_topics = (array) get_user_meta( $current_user->ID, 'preferred_topics', true );
						$db_topics = get_terms( array( 'taxonomy' => 'topic', 'hide_empty' => false ) );
						if ( ! is_wp_error( $db_topics ) && ! empty( $db_topics ) ) {
							foreach ( $db_topics as $term ) {
								$is_on = in_array( (int) $term->term_id, array_map( 'intval', $user_saved_topics ), true ) ? ' on' : '';
								echo '<button type="button" class="chip' . esc_attr( $is_on ) . '" data-id="' . esc_attr( $term->term_id ) . '">' . esc_html( $term->name ) . '</button> ';
							}
						}
						?>
					</div>
				</div>
			</div>
			<div class="panel">
				<h2>Focus areas</h2>
				<p class="hint">Watch the places where the partnership moves.</p>
				<div class="field">
					<div class="chips" id="region-chips" data-toggle>
						<?php
						$user_saved_regions = (array) get_user_meta( $current_user->ID, 'preferred_regions', true );
						$db_regions = get_terms( array( 'taxonomy' => 'region', 'hide_empty' => false ) );
						if ( ! is_wp_error( $db_regions ) && ! empty( $db_regions ) ) {
							foreach ( $db_regions as $term ) {
								$is_on = in_array( (int) $term->term_id, array_map( 'intval', $user_saved_regions ), true ) ? ' on' : '';
								echo '<button type="button" class="chip' . esc_attr( $is_on ) . '" data-id="' . esc_attr( $term->term_id ) . '">' . esc_html( $term->name ) . '</button> ';
							}
						}
						?>
					</div>
				</div>
				<div class="row-actions" style="margin-top:20px;">
					<button type="button" class="btn btn-primary" id="save-prefs-btn">Save preferences</button>
					<span id="save-prefs-msg" style="margin-left: 12px; font-size: 12px; font-family: var(--font-mono); color: var(--accent, #BC1B1D);"></span>
				</div>
			</div>
		</section>

		<!-- ADD-ONS TAB -->
		<section class="view" id="v-addons">
			<div class="view-head">
				<h1>Intelligence Add-on Desks &amp; Subscriptions</h1>
				<p>Expand your coverage with specialist sector and regional intelligence add-ons.</p>
			</div>

			<!-- Checkout Return Notification Banner -->
			<div id="addon-checkout-banner" style="display:none; margin-bottom:20px; padding:14px 18px; border-radius:4px; font-size:13px; font-weight:600;"></div>

			<div class="panel">
				<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
					<h2 style="margin:0;">Specialist Category Add-ons</h2>
					<button type="button" id="btn-refresh-addons" class="btn btn-ghost" style="font-size:11px; padding:6px 12px;">
						<i class="fa-solid fa-sync" style="margin-right:4px;"></i> Refresh Status
					</button>
				</div>

				<div id="addons-grid" class="addons-grid">
					<div style="padding:40px; text-align:center; color:var(--ink-3); font-size:13px; grid-column: 1 / -1;">
						<i class="fa-solid fa-spinner fa-spin" style="margin-right:8px;"></i> Loading intelligence add-ons...
					</div>
				</div>
			</div>
		</section>

		<!-- BILLING -->
		<section class="view" id="v-billing">
			<div class="view-head">
				<h1>Billing &amp; plan</h1>
				<p>Subscription details and Stripe portal access.</p>
			</div>
			<div class="panel">
				<span class="plan-tag">Current plan</span>
				<?php
				$billing_tier_name = 'Free Guest';
				$pmpro_active = function_exists( 'pmpro_getMembershipLevelForUser' );
				if ( $pmpro_active ) {
					$u_level = pmpro_getMembershipLevelForUser( $current_user->ID );
					if ( ! empty( $u_level ) ) {
						$billing_tier_name = $u_level->name;
					}
				}
				?>
				<h2 style="margin-top:8px;"><?php echo esc_html( $billing_tier_name ); ?> Subscription</h2>
				<div class="bill-grid" style="margin-top:14px;">
					<div>
						<div class="bill-row"><span>Status</span><span>Active</span></div>
						<div class="bill-row"><span>Security Clearance</span><span>Verified</span></div>
					</div>
				</div>
				<div class="row-actions" style="margin-top:16px;">
					<?php
					$cust_id = get_user_meta( $current_user->ID, 'ascendance_stripe_customer_id', true );
					if ( ! empty( $cust_id ) ) :
					?>
					<button type="button" class="btn btn-primary" id="btn-stripe-portal-dash">Manage Billing via Stripe Portal</button>
					<script>
					document.getElementById('btn-stripe-portal-dash').addEventListener('click', function(e) {
						e.preventDefault();
						const btn = this;
						btn.disabled = true;
						btn.textContent = 'Connecting to Stripe...';
						fetch('<?php echo esc_url_raw( get_rest_url( null, 'ascendance/v1/billing/portal-session' ) ); ?>', { method: 'POST' })
							.then(res => res.json())
							.then(data => {
								if (data.url) {
									window.location.href = data.url;
								} else {
									alert(data.error || 'Failed to connect to billing portal.');
									btn.disabled = false;
									btn.textContent = 'Manage Billing via Stripe Portal';
								}
							})
							.catch(err => {
								console.error(err);
								alert('An error occurred.');
								btn.disabled = false;
								btn.textContent = 'Manage Billing via Stripe Portal';
							});
					});
					</script>
					<?php else : ?>
					<a class="btn btn-primary" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>">Upgrade Membership Tier</a>
					<?php endif; ?>
				</div>
			</div>
		</section>

	</main>
</div>

<style>
/* ---- Add-ons Workspace Styling ---- */
.addons-grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
	gap: 20px;
}
.addon-card {
	border: 1px solid var(--hairline, #e2e8f0);
	border-radius: 4px;
	padding: 20px;
	background: #fff;
	display: flex;
	flex-direction: column;
	justify-content: space-between;
	transition: border-color 0.2s, box-shadow 0.2s;
}
.addon-card:hover {
	border-color: #cbd5e1;
	box-shadow: 0 4px 12px rgba(0,0,0,0.04);
}
.addon-card-header {
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
	gap: 12px;
	margin-bottom: 10px;
}
.addon-card-title {
	font-size: 16px;
	font-weight: 700;
	margin: 0;
	color: var(--ink-1, #0f172a);
}
.addon-badge {
	font-size: 9px;
	font-family: var(--font-mono, monospace);
	font-weight: 700;
	text-transform: uppercase;
	padding: 3px 8px;
	border-radius: 3px;
	letter-spacing: 0.05em;
	white-space: nowrap;
}
.addon-badge-active { background: rgba(39, 174, 96, 0.1); color: #27ae60; }
.addon-badge-canceling { background: rgba(217, 119, 6, 0.1); color: #d97706; }
.addon-badge-expired { background: rgba(100, 116, 139, 0.1); color: #64748b; }
.addon-badge-issue { background: rgba(192, 57, 43, 0.1); color: #c0392b; }
.addon-badge-enterprise { background: rgba(139, 92, 246, 0.1); color: #7c3aed; }
.addon-badge-admin { background: rgba(14, 165, 233, 0.1); color: #0284c7; }
.addon-badge-inactive { background: rgba(148, 163, 184, 0.1); color: #94a3b8; }
.addon-card-desc {
	font-size: 13px;
	color: var(--ink-2, #475569);
	line-height: 1.5;
	margin-bottom: 16px;
	flex-grow: 1;
}
.addon-card-footer {
	border-top: 1px solid var(--hairline, #f1f5f9);
	padding-top: 14px;
	display: flex;
	justify-content: space-between;
	align-items: center;
	gap: 12px;
}
.addon-price-tag {
	font-size: 14px;
	font-weight: 800;
	font-family: var(--font-mono, monospace);
	color: var(--ink-1, #0f172a);
}
.addon-price-sub {
	font-size: 10px;
	font-weight: 400;
	color: var(--ink-3, #94a3b8);
	display: block;
}

/* ---- Saved item remove button ---- */
.saved-item { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; }
.saved-item .body { flex:1; }
.saved-item .body h3 a { color:inherit; text-decoration:none; }
.saved-item .body h3 a:hover { color:var(--red, #c52225); }
.unsave-btn {
	flex-shrink:0;
	font-size:11px;
	font-family:var(--font-mono,'monospace');
	letter-spacing:.06em;
	text-transform:uppercase;
	color:var(--ink-3,#888);
	background:none;
	border:1px solid var(--hairline,#ddd);
	border-radius:2px;
	padding:4px 10px;
	cursor:pointer;
	transition:color .15s, border-color .15s;
	white-space:nowrap;
	margin-top:4px;
}
.unsave-btn:hover { color:var(--red,#c52225); border-color:var(--red,#c52225); }
/* Empty state */
.saved-empty {
	display:flex; flex-direction:column; align-items:center;
	text-align:center; padding:60px 20px; color:var(--ink-3,#888);
}
.saved-empty svg { margin-bottom:16px; opacity:.35; }
.saved-empty p { margin:4px 0; }
.saved-empty .hint { font-size:13px; }
/* Remove animation */
.saved-item.removing {
	opacity:0;
	transform:translateX(20px);
	transition:opacity .25s ease, transform .25s ease;
	pointer-events:none;
}
/* ---- Profile Modal ---- */
.modal-overlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,30,53,0.8); z-index:9999; align-items:center; justify-content:center; }
.modal-overlay.show { display:flex; }
.modal-content { background:#fff; width:100%; max-width:440px; border-radius:2px; padding:32px; box-shadow:0 4px 24px rgba(0,0,0,0.15); }
.modal-header { display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--hairline,#ddd); padding-bottom:16px; margin-bottom:24px; }
.modal-header h2 { font-size:22px; margin:0; font-family:var(--font-serif); }
.modal-close { font-size:24px; cursor:pointer; color:#888; line-height:1; }
.modal-close:hover { color:#000; }
.input-group { margin-bottom:20px; }
.input-group label { display:block; font-size:11px; font-weight:600; color:#555; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.06em; font-family:var(--font-mono,'monospace'); }
.input-group input { width:100%; padding:12px; border:1px solid var(--hairline,#ccc); border-radius:2px; font-size:14px; box-sizing:border-box; font-family:var(--font-sans); }
.input-group input:focus { border-color:var(--accent,#BC1B1D); outline:none; }
</style>

<!-- PROFILE MODAL -->
<div id="profileModal" class="modal-overlay">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Edit Profile</h2>
      <span class="modal-close" onclick="closeProfileModal()">&times;</span>
    </div>
    <div class="modal-body">
      <div class="input-group">
        <label>Full Name</label>
        <input type="text" id="profileName" value="<?php echo esc_attr( $current_user->display_name ); ?>">
      </div>
      <div class="input-group">
        <label>Email Address</label>
        <input type="email" id="profileEmail" value="<?php echo esc_attr( $current_user->user_email ); ?>">
      </div>
      <button class="btn btn-primary" id="saveProfileBtn" onclick="saveProfile();" style="margin-top:8px; width:100%;">Save Changes</button>
    </div>
  </div>
</div>

<script>
(function () {
	var AJAX_URL = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';
	var NONCE    = '<?php echo esc_js( wp_create_nonce( 'as_save_nonce' ) ); ?>';

	/* ---------- Modal Logic ---------- */
	window.openProfileModal = function() { document.getElementById('profileModal').classList.add('show'); };
	window.closeProfileModal = function() { document.getElementById('profileModal').classList.remove('show'); };
	window.saveProfile = function() {
		var btn = document.getElementById('saveProfileBtn');
		var name = document.getElementById('profileName').value;
		var email = document.getElementById('profileEmail').value;
		
		btn.disabled = true;
		btn.textContent = 'Saving...';
		
		var fd = new FormData();
		fd.append('action', 'as_update_profile');
		fd.append('nonce', NONCE);
		fd.append('full_name', name);
		fd.append('email', email);
		
		fetch(AJAX_URL, { method:'POST', body:fd, credentials:'same-origin' })
			.then(function(r) { return r.json(); })
			.then(function(res) {
				btn.disabled = false;
				btn.textContent = 'Save Changes';
				if(res.success) {
					alert(res.data.message);
					closeProfileModal();
					location.reload();
				} else {
					alert('Error: ' + res.data);
				}
			})
			.catch(function(e) {
				btn.disabled = false;
				btn.textContent = 'Save Changes';
				alert('An error occurred.');
			});
	};

	/* ---------- Dashboard tabs ---------- */
	var nav = document.getElementById('dash-nav');
	if (nav) {
		var map = { overview:'v-overview', history:'v-history', saved:'v-saved', notes:'v-notes', recs:'v-recs', prefs:'v-prefs', addons:'v-addons', billing:'v-billing' };
		nav.addEventListener('click', function (e) {
			var b = e.target.closest('button[data-view]');
			if (!b) return;
			nav.querySelectorAll('button').forEach(function (x) { x.classList.remove('active'); });
			b.classList.add('active');
			Object.values(map).forEach(function (id) {
				var el = document.getElementById(id);
				if (el) el.classList.remove('show');
			});
			var target = document.getElementById(map[b.dataset.view]);
			if (target) {
				target.classList.add('show');
				if (b.dataset.view === 'addons') {
					fetchCategoryAddons();
				}
			}
		});
		document.querySelectorAll('[data-toggle]').forEach(function (group) {
			group.addEventListener('click', function (e) {
				var c = e.target.closest('.chip');
				if (c) c.classList.toggle('on');
			});
		});
	}

	/* ---------- Filter & Sort Saved Posts ---------- */
	function filterSavedPosts() {
		var savedList = document.getElementById('saved-list');
		if (!savedList) return;

		var type = document.getElementById('filter-saved-type') ? document.getElementById('filter-saved-type').value : 'all';
		var topic = document.getElementById('filter-saved-topic') ? document.getElementById('filter-saved-topic').value : '0';
		var region = document.getElementById('filter-saved-region') ? document.getElementById('filter-saved-region').value : '0';
		var order = document.getElementById('filter-saved-order') ? document.getElementById('filter-saved-order').value : 'recently_saved';

		savedList.style.opacity = '0.5';

		var fd = new FormData();
		fd.append('action', 'asc_filter_saved_posts');
		fd.append('security', NONCE);
		fd.append('post_type', type);
		fd.append('topic', topic);
		fd.append('region', region);
		fd.append('orderby', order);

		fetch(AJAX_URL, { method: 'POST', body: fd, credentials: 'same-origin' })
			.then(function(r) { return r.json(); })
			.then(function(res) {
				savedList.style.opacity = '1';
				if (res.success && res.data && res.data.items) {
					if (res.data.items.length === 0) {
						savedList.innerHTML = '<div class="saved-empty"><svg width="40" height="40" fill="none" viewBox="0 0 24 24"><path d="M5 5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16l-7-3.5L5 21V5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg><p>No saved intelligence matching your filter criteria.</p></div>';
					} else {
						var html = '';
						res.data.items.forEach(function(item) {
							html += '<div class="saved-item" data-post-id="' + item.post_id + '">';
							html += '<div class="body">';
							html += '<div class="k">' + item.type_label + ' &middot; ' + item.date_label + '</div>';
							html += '<h3><a href="' + item.permalink + '">' + item.title + '</a></h3>';
							html += '<div class="meta">';
							html += '<span class="dot"></span>' + item.topic_str + ' &middot; ' + item.region_str;
							if (item.progress > 0) {
								html += ' &middot; <span style="color:var(--accent,#BC1B1D); font-weight:bold;">' + item.progress + '% read</span>';
							}
							html += '</div></div>';
							html += '<button type="button" class="unsave-btn" data-post-id="' + item.post_id + '" title="Remove">&times; Remove</button>';
							html += '</div>';
						});
						savedList.innerHTML = html;
					}
				}
			})
			.catch(function() { savedList.style.opacity = '1'; });
	}

	['filter-saved-type', 'filter-saved-topic', 'filter-saved-region', 'filter-saved-order'].forEach(function(id) {
		var el = document.getElementById(id);
		if (el) el.addEventListener('change', filterSavedPosts);
	});

	/* ---------- History Filtering & Clearing Handlers ---------- */
	function filterReadingHistory() {
		var historyList = document.getElementById('history-list');
		if (!historyList) return;

		var type = document.getElementById('filter-history-type') ? document.getElementById('filter-history-type').value : 'all';
		var topic = document.getElementById('filter-history-topic') ? document.getElementById('filter-history-topic').value : '0';
		var region = document.getElementById('filter-history-region') ? document.getElementById('filter-history-region').value : '0';

		historyList.style.opacity = '0.5';

		var fd = new FormData();
		fd.append('action', 'asc_filter_reading_history');
		fd.append('security', NONCE);
		fd.append('post_type', type);
		fd.append('topic', topic);
		fd.append('region', region);

		fetch(AJAX_URL, { method: 'POST', body: fd, credentials: 'same-origin' })
			.then(function(r) { return r.json(); })
			.then(function(res) {
				historyList.style.opacity = '1';
				if (res.success && res.data && res.data.items) {
					if (res.data.items.length === 0) {
						historyList.innerHTML = '<div class="saved-empty"><svg width="40" height="40" fill="none" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg><p>No reading history matching your filter criteria.</p></div>';
					} else {
						var html = '';
						res.data.items.forEach(function(item) {
							html += '<div class="history-item-row" data-post-id="' + item.post_id + '" style="padding: 14px 0; border-bottom: 1px solid var(--hairline, #eee); display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;">';
							html += '<div style="flex: 1;">';
							html += '<div style="font-size: 9px; font-family: var(--font-mono); text-transform: uppercase; color: var(--ink-3); font-weight: bold; margin-bottom: 4px;">';
							html += item.post_type + ' &middot; Read ' + item.last_read;
							if (item.is_completed) {
								html += ' &middot; <span style="color:#27AE60; font-weight:bold;">✔ COMPLETED</span>';
							}
							html += '</div>';
							html += '<h3 style="margin: 0 0 8px 0; font-size: 14px; font-weight: 700;"><a href="' + item.permalink + '" style="color: inherit; text-decoration: none;">' + item.title + '</a></h3>';
							html += '<div style="display: flex; align-items: center; gap: 10px; max-width: 300px;">';
							html += '<div style="flex: 1; height: 4px; background: rgba(0,0,0,0.08); border-radius: 2px; overflow: hidden;">';
							html += '<div style="width: ' + item.progress + '%; height: 100%; background: ' + (item.is_completed ? '#27AE60' : 'var(--accent, #BC1B1D)') + ';"></div>';
							html += '</div>';
							html += '<span style="font-size: 10px; font-family: var(--font-mono); color: var(--ink-3); font-weight: bold;">' + item.progress + '%</span>';
							html += '</div></div>';
							html += '<div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0; margin-top: 4px;">';
							html += '<a href="' + item.permalink + '" class="btn btn-ghost" style="font-size: 11px; padding: 6px 12px;">' + (item.is_completed ? 'Revisit' : 'Continue') + ' &rarr;</a>';
							html += '<button type="button" class="btn-remove-history-item btn btn-ghost" data-post-id="' + item.post_id + '" style="font-size: 11px; padding: 6px 8px; color: #C0392B;" title="Remove">&times;</button>';
							html += '</div></div>';
						});
						historyList.innerHTML = html;
					}
				}
			})
			.catch(function() { historyList.style.opacity = '1'; });
	}

	['filter-history-type', 'filter-history-topic', 'filter-history-region'].forEach(function(id) {
		var el = document.getElementById(id);
		if (el) el.addEventListener('change', filterReadingHistory);
	});

	/* Remove Single History Item Handler */
	var historyList = document.getElementById('history-list');
	if (historyList) {
		historyList.addEventListener('click', function(e) {
			var btn = e.target.closest('.btn-remove-history-item');
			if (!btn) return;
			var postId = btn.dataset.postId;
			var row = btn.closest('.history-item-row');
			if (!confirm('Remove this article from your reading history?')) return;

			btn.disabled = true;
			var fd = new FormData();
			fd.append('action', 'asc_remove_history_item');
			fd.append('security', NONCE);
			fd.append('post_id', postId);

			fetch(AJAX_URL, { method: 'POST', body: fd, credentials: 'same-origin' })
				.then(function(r) { return r.json(); })
				.then(function(res) {
					if (res.success && row) {
						row.remove();
					} else {
						btn.disabled = false;
					}
				})
				.catch(function() { btn.disabled = false; });
		});
	}

	/* Clear All Reading History Handler */
	var clearHistoryBtn = document.getElementById('btn-clear-reading-history');
	if (clearHistoryBtn) {
		clearHistoryBtn.addEventListener('click', function() {
			if (!confirm('Are you sure you want to clear your ENTIRE reading history? This cannot be undone.')) return;
			clearHistoryBtn.disabled = true;

			var fd = new FormData();
			fd.append('action', 'asc_clear_reading_history');
			fd.append('security', NONCE);

			fetch(AJAX_URL, { method: 'POST', body: fd, credentials: 'same-origin' })
				.then(function(r) { return r.json(); })
				.then(function(res) {
					clearHistoryBtn.disabled = false;
					if (res.success && historyList) {
						historyList.innerHTML = '<div class="saved-empty" id="history-empty-state"><svg width="40" height="40" fill="none" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg><p>No reading history recorded yet.</p></div>';
					}
				})
				.catch(function() { clearHistoryBtn.disabled = false; });
		});
	}

	/* ---------- Export PDF Handler ---------- */
	document.addEventListener('click', function(e) {
		var btn = e.target.closest('.btn-export-pdf');
		if (!btn) return;
		var postId = btn.dataset.postId;
		var originalText = btn.innerHTML;
		btn.disabled = true;
		btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Exporting...';

		var fd = new FormData();
		fd.append('action', 'asc_generate_pdf_token');
		fd.append('security', NONCE);
		fd.append('post_id', postId);

		fetch(AJAX_URL, { method: 'POST', body: fd, credentials: 'same-origin' })
			.then(function(r) { return r.json(); })
			.then(function(res) {
				btn.disabled = false;
				btn.innerHTML = originalText;
				if (res.success && res.data && res.data.download_url) {
					window.location.href = res.data.download_url;
				} else {
					alert(res.data && res.data.message ? res.data.message : 'Unable to generate PDF. Authorization required.');
				}
			})
			.catch(function() {
				btn.disabled = false;
				btn.innerHTML = originalText;
				alert('Connection error while exporting PDF.');
			});
	});

	/* ---------- Save Preferences AJAX ---------- */
	var savePrefsBtn = document.getElementById('save-prefs-btn');
	if (savePrefsBtn) {
		savePrefsBtn.addEventListener('click', function() {
			var msg = document.getElementById('save-prefs-msg');
			var topicChips = document.querySelectorAll('#topic-chips .chip.on');
			var regionChips = document.querySelectorAll('#region-chips .chip.on');
			var topics = [];
			var regions = [];
			topicChips.forEach(function(c) { if (c.dataset.id) topics.push(parseInt(c.dataset.id, 10)); });
			regionChips.forEach(function(c) { if (c.dataset.id) regions.push(parseInt(c.dataset.id, 10)); });

			savePrefsBtn.disabled = true;
			if (msg) msg.textContent = 'Saving preferences...';

			var fd = new FormData();
			fd.append('action', 'asc_save_dashboard_preferences');
			fd.append('security', NONCE);
			topics.forEach(function(id) { fd.append('topics[]', id); });
			regions.forEach(function(id) { fd.append('regions[]', id); });

			fetch(AJAX_URL, { method: 'POST', body: fd, credentials: 'same-origin' })
				.then(function(r) { return r.json(); })
				.then(function(res) {
					savePrefsBtn.disabled = false;
					if (msg) {
						msg.textContent = res.success ? (res.data.message || 'Preferences saved!') : 'Error saving preferences.';
						setTimeout(function() { msg.textContent = ''; }, 4000);
					}
				})
				.catch(function() {
					savePrefsBtn.disabled = false;
					if (msg) msg.textContent = 'Connection error.';
				});
		});
	}

	/* ---------- Save/Unsave Handler ---------- */
	var savedList = document.getElementById('saved-list');
	if (savedList) {
		savedList.addEventListener('click', function (e) {
			var btn = e.target.closest('.unsave-btn');
			if (!btn) return;
			var postId = btn.dataset.postId;
			var item   = btn.closest('.saved-item');

			btn.disabled = true;
			btn.textContent = 'Removing…';

			var fd = new FormData();
			fd.append('action',  'as_toggle_saved');
			fd.append('nonce',   NONCE);
			fd.append('post_id', postId);

			fetch(AJAX_URL, { method:'POST', body:fd, credentials:'same-origin' })
				.then(function (r) { return r.json(); })
				.then(function (res) {
					if (!res.success) { btn.disabled = false; btn.textContent = '× Remove'; return; }
					item.classList.add('removing');
					setTimeout(function () {
						item.remove();
						var count = res.data.count;
						var badge = document.getElementById('saved-count-badge');
						var summary = document.getElementById('saved-summary');
						if (badge) badge.textContent = count;
						if (summary) summary.textContent = count > 0
							? count + ' briefing' + (count !== 1 ? 's' : '') + ' saved to your reading list.'
							: 'Your reading list is empty. Hit the Save button on any brief or article.';
						var sideCount = nav ? nav.querySelector('button[data-view="saved"] .ct') : null;
						if (sideCount) sideCount.textContent = count;
						if (count === 0) {
							savedList.innerHTML = '<div class="saved-empty"><svg width="40" height="40" fill="none" viewBox="0 0 24 24"><path d="M5 5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16l-7-3.5L5 21V5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg><p>No saved briefs yet.</p><p class="hint">Click <strong>Save for later</strong> on any article to add it here.</p></div>';
						}
					}, 280);
				})
				.catch(function () { btn.disabled = false; btn.textContent = '× Remove'; });
		});
	}

	/* ---------- Handle Save Toggle on Recommendation Cards ---------- */
	document.addEventListener('click', function(e) {
		var btn = e.target.closest('.btn-toggle-save-rec');
		if (!btn) return;
		var postId = btn.dataset.postId;
		btn.disabled = true;

		var fd = new FormData();
		fd.append('action', 'as_toggle_saved');
		fd.append('nonce', NONCE);
		fd.append('post_id', postId);

		fetch(AJAX_URL, { method: 'POST', body: fd, credentials: 'same-origin' })
			.then(function(r) { return r.json(); })
			.then(function(res) {
				btn.disabled = false;
				if (res.success) {
					var isSaved = res.data.saved;
					btn.textContent = isSaved ? '✔ Saved' : '🔖 Save';
					btn.style.background = isSaved ? 'rgba(39, 174, 96, 0.1)' : '';
					btn.style.color = isSaved ? '#27AE60' : '';
					/* Update badge counts */
					var count = res.data.count;
					var badge = document.getElementById('saved-count-badge');
					if (badge) badge.textContent = count;
					var sideCount = nav ? nav.querySelector('button[data-view="saved"] .ct') : null;
					if (sideCount) sideCount.textContent = count;
				}
			})
			.catch(function() { btn.disabled = false; });
	});

	/* ---------- Add-ons Workspace Loader & Checkout Logic ---------- */
	var addonsGrid = document.getElementById('addons-grid');
	var refreshAddonsBtn = document.getElementById('btn-refresh-addons');
	var checkoutApiUrl = '<?php echo esc_url_raw( get_rest_url( null, 'ascendance/v1/category-checkout' ) ); ?>';
	var categoryAddonsUrl = '<?php echo esc_url_raw( get_rest_url( null, 'ascendance/v1/user/category-addons' ) ); ?>';
	var portalApiUrl = '<?php echo esc_url_raw( get_rest_url( null, 'ascendance/v1/billing/portal-session' ) ); ?>';

	function fetchCategoryAddons() {
		if (!addonsGrid) return;
		addonsGrid.style.opacity = '0.6';

		fetch(categoryAddonsUrl, { credentials: 'same-origin' })
			.then(function(r) { return r.json(); })
			.then(function(res) {
				addonsGrid.style.opacity = '1';
				if (res.ok && res.addons) {
					if (res.addons.length === 0) {
						addonsGrid.innerHTML = '<div style="padding:40px; text-align:center; color:var(--ink-3); font-size:13px; grid-column:1/-1;"><p>No additional intelligence categories are currently available.</p></div>';
						return;
					}

					var html = '';
					var allOwned = true;

					res.addons.forEach(function(ad) {
						var isEntitled = ad.is_entitled;
						var status = ad.entitlement_status || 'none';
						var source = ad.entitlement_source || 'none';
						var topicStatus = ad.status || 'active';
						var isEnterprise = res.is_enterprise;

						if (!isEntitled && topicStatus === 'active') {
							allOwned = false;
						}

						var badgeHtml = '';
						var buttonHtml = '';
						var priceHtml = '<div class="addon-price-tag">$' + (ad.price_amount ? ad.price_amount.toFixed(2) : '49.00') + '<span class="addon-price-sub">/ month recurring</span></div>';

						if (isEnterprise) {
							badgeHtml = '<span class="addon-badge addon-badge-enterprise">Included with Enterprise</span>';
							buttonHtml = '<button type="button" class="btn btn-ghost" disabled style="font-size:11px; padding:6px 12px; cursor:default;">✓ Included</button>';
							priceHtml = '<div class="addon-price-tag">Enterprise Clearance<span class="addon-price-sub">All desks included</span></div>';
						} else if (source === 'admin' && isEntitled) {
							badgeHtml = '<span class="addon-badge addon-badge-admin">✓ Granted by Advisory Desk</span>';
							buttonHtml = '<button type="button" class="btn btn-ghost" disabled style="font-size:11px; padding:6px 12px; cursor:default;">✓ Active Access</button>';
							priceHtml = '<div class="addon-price-tag">Complimentary<span class="addon-price-sub">Specialist entitlement</span></div>';
						} else if (status === 'canceling') {
							var expDate = ad.expires_formatted || 'period end';
							badgeHtml = '<span class="addon-badge addon-badge-canceling">⚠ Access ends ' + expDate + '</span>';
							buttonHtml = '<button type="button" class="btn btn-secondary btn-portal-addon" style="font-size:11px; padding:6px 12px;">Manage Billing</button>';
						} else if (isEntitled) {
							var renDate = ad.expires_formatted ? ('Renews ' + ad.expires_formatted) : 'Active Subscription';
							badgeHtml = '<span class="addon-badge addon-badge-active">✓ Active</span>';
							buttonHtml = '<button type="button" class="btn btn-secondary btn-portal-addon" style="font-size:11px; padding:6px 12px;">Manage Billing</button>';
						} else if (status === 'expired') {
							badgeHtml = '<span class="addon-badge addon-badge-expired">Expired</span>';
							buttonHtml = '<button type="button" class="btn btn-primary btn-purchase-addon" data-slug="' + ad.slug + '" style="font-size:11px; padding:6px 14px;">Renew Access &rarr;</button>';
						} else if (status === 'payment_issue') {
							badgeHtml = '<span class="addon-badge addon-badge-issue">⚠ Payment Issue</span>';
							buttonHtml = '<button type="button" class="btn btn-secondary btn-portal-addon" style="font-size:11px; padding:6px 12px;">Manage Billing</button>';
						} else if (status === 'revoked') {
							badgeHtml = '<span class="addon-badge addon-badge-expired">Access Revoked</span>';
							buttonHtml = '<button type="button" class="btn btn-primary btn-purchase-addon" data-slug="' + ad.slug + '" style="font-size:11px; padding:6px 14px;">Re-purchase &rarr;</button>';
						} else if (topicStatus !== 'active') {
							badgeHtml = '<span class="addon-badge addon-badge-inactive">Unavailable</span>';
							buttonHtml = '<button type="button" class="btn btn-ghost" disabled style="font-size:11px; padding:6px 12px; cursor:not-allowed;">Unavailable</button>';
						} else {
							badgeHtml = '<span class="addon-badge addon-badge-expired">Add-on Required</span>';
							buttonHtml = '<button type="button" class="btn btn-primary btn-purchase-addon" data-slug="' + ad.slug + '" style="font-size:11px; padding:6px 14px;">Add ' + ad.name + ' &rarr;</button>';
						}

						html += '<div class="addon-card" data-slug="' + ad.slug + '">';
						html += '<div>';
						html += '<div class="addon-card-header">';
						html += '<h3 class="addon-card-title">' + ad.name + '</h3>';
						html += badgeHtml;
						html += '</div>';
						html += '<div class="addon-card-desc">' + (ad.description || 'Specialist intelligence coverage and analytical reports.') + '</div>';
						html += '</div>';
						html += '<div class="addon-card-footer">';
						html += priceHtml;
						html += buttonHtml;
						html += '</div>';
						html += '</div>';
					});

					if (allOwned && !res.is_enterprise) {
						html = '<div style="padding:16px 20px; background:rgba(39,174,96,0.06); border:1px solid rgba(39,174,96,0.2); border-radius:4px; font-size:13px; color:#27ae60; font-weight:600; grid-column:1/-1; margin-bottom:12px;">✓ You have active entitlements for all available intelligence category add-ons.</div>' + html;
					}

					addonsGrid.innerHTML = html;
				}
			})
			.catch(function(err) {
				console.error(err);
				addonsGrid.style.opacity = '1';
				addonsGrid.innerHTML = '<div style="padding:30px; text-align:center; color:#c0392b; font-size:13px; grid-column:1/-1;">Error loading category add-ons.</div>';
			});
	}

	if (refreshAddonsBtn) {
		refreshAddonsBtn.addEventListener('click', function() {
			fetchCategoryAddons();
		});
	}

	/* Purchase Add-on Click Handler (strict server-side pricing resolution) */
	document.addEventListener('click', function(e) {
		var btn = e.target.closest('.btn-purchase-addon');
		if (!btn) return;
		var slug = btn.dataset.slug;
		if (!slug) return;

		var origText = btn.textContent;
		btn.disabled = true;
		btn.textContent = 'Redirecting to secure checkout...';

		fetch(checkoutApiUrl, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({ category_slug: slug }),
			credentials: 'same-origin'
		})
		.then(function(r) { return r.json(); })
		.then(function(res) {
			if (res.url) {
				window.location.href = res.url;
			} else {
				alert(res.error || 'Failed to initiate category checkout session.');
				btn.disabled = false;
				btn.textContent = origText;
			}
		})
		.catch(function(err) {
			console.error(err);
			alert('Connection error while initiating checkout.');
			btn.disabled = false;
			btn.textContent = origText;
		});
	});

	/* Portal Button Click Handler */
	document.addEventListener('click', function(e) {
		var btn = e.target.closest('.btn-portal-addon');
		if (!btn) return;

		var origText = btn.textContent;
		btn.disabled = true;
		btn.textContent = 'Connecting to Stripe...';

		fetch(portalApiUrl, { method: 'POST', credentials: 'same-origin' })
		.then(function(r) { return r.json(); })
		.then(function(res) {
			if (res.url) {
				window.location.href = res.url;
			} else {
				alert(res.error || 'Failed to open billing portal.');
				btn.disabled = false;
				btn.textContent = origText;
			}
		})
		.catch(function(err) {
			console.error(err);
			alert('Connection error while opening billing portal.');
			btn.disabled = false;
			btn.textContent = origText;
		});
	});

	/* URL Return Handler for Stripe Checkout Success / Cancel Banners */
	(function handleCheckoutReturn() {
		var params = new URLSearchParams(window.location.search);
		var banner = document.getElementById('addon-checkout-banner');
		var checkoutState = params.get('checkout');
		var catParam = params.get('category');

		if (checkoutState && banner) {
			if (checkoutState === 'success') {
				banner.style.display = 'block';
				banner.style.background = 'rgba(39, 174, 96, 0.1)';
				banner.style.color = '#27ae60';
				banner.style.border = '1px solid rgba(39, 174, 96, 0.3)';
				banner.innerHTML = '✓ Payment received. Your access is being confirmed by our servers...';
				
				// Switch to Add-ons tab if present
				var navBtn = document.querySelector('button[data-view="addons"]');
				if (navBtn) navBtn.click();

				// Clean query string
				params.delete('checkout');
				params.delete('category');
				params.delete('session_id');
				var cleanUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
				window.history.replaceState({}, document.title, cleanUrl);

			} else if (checkoutState === 'cancelled') {
				banner.style.display = 'block';
				banner.style.background = 'rgba(217, 119, 6, 0.1)';
				banner.style.color = '#d97706';
				banner.style.border = '1px solid rgba(217, 119, 6, 0.3)';
				banner.innerHTML = '⚠ Checkout canceled. No changes were made to your account.';

				var navBtn = document.querySelector('button[data-view="addons"]');
				if (navBtn) navBtn.click();

				params.delete('checkout');
				var cleanUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
				window.history.replaceState({}, document.title, cleanUrl);
			}
		}
	})();

})();
</script>

<?php
get_footer();
