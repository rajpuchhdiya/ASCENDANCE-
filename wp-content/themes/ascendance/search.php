<?php
/**
 * The template for displaying search results pages
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="site-main as-search-page">

	<!-- Search Page Header -->
	<header class="search-header">
		<div class="search-header-inner">
			<span class="search-eyebrow"><?php esc_html_e( 'Search Engine', 'ascendance' ); ?></span>
			<h1 class="search-title">
				<?php
				/* translators: %s: search query. */
				printf( esc_html__( 'Results for: %s', 'ascendance' ), '<span>' . esc_html( get_search_query() ) . '</span>' );
				?>
			</h1>
			
			<!-- Re-search bar form -->
			<form role="search" method="get" class="search-page-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<input type="search" class="search-input" placeholder="<?php echo esc_attr_x( 'Search briefings, dossiers...', 'placeholder', 'ascendance' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
				<button type="submit" class="search-btn">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
				</button>
			</form>
		</div>
	</header>

	<div class="search-results-container">
		<div class="search-results-inner">
			<?php if ( have_posts() ) : ?>

				<div class="search-cards-list">
					<?php
					while ( have_posts() ) :
						the_post();
						$post_id = get_the_ID();
						$post_type = get_post_type();
						$claim = get_field( 'analytical_claim', $post_id );
						$tier = get_field( 'tier_access', $post_id ) ?: 'essential';
						
						$user_has_access = ( 'entity' === $post_type ) ? true : ( class_exists( 'Ascendance\Core\Paywall' ) && Ascendance\Core\Paywall::get_instance()->user_has_access( $post_id ) );
						?>
						<article id="post-<?php the_ID(); ?>" class="search-card <?php echo $user_has_access ? 'access-granted' : 'access-required'; ?>">
							<div class="search-card-top">
								<div class="search-card-meta">
									<span class="search-card-type"><?php echo ( 'entity' === $post_type ) ? 'ENTITY PROFILE' : esc_html( ucfirst( $post_type ) ); ?></span>
									<?php 
									$topics = get_the_terms( $post_id, 'topic' );
									if ( ! empty( $topics ) && ! is_wp_error( $topics ) ) :
										$topic_names = wp_list_pluck( $topics, 'name' );
										?>
										<span class="search-card-tag"><?php echo esc_html( implode( ', ', $topic_names ) ); ?></span>
									<?php endif; ?>
									<span class="search-card-date"><?php echo get_the_date(); ?></span>
								</div>
								
								<!-- Lock State Indicator -->
								<div class="search-card-lock">
									<?php if ( $user_has_access ) : ?>
										<span class="lock-indicator granted">
											<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
											<?php esc_html_e( 'Access Granted', 'ascendance' ); ?>
										</span>
									<?php else : ?>
										<span class="lock-indicator required">
											<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/></svg>
											<?php echo esc_html( ucfirst( $tier ) ); ?> <?php esc_html_e( 'Required', 'ascendance' ); ?>
										</span>
									<?php endif; ?>
								</div>
							</div>

							<h2 class="search-card-title">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h2>

							<?php if ( ! empty( $claim ) ) : ?>
								<div class="search-card-claim">
									<span class="claim-label">CLAIM //</span>
									<?php echo esc_html( $claim ); ?>
								</div>
							<?php endif; ?>

							<div class="search-card-excerpt">
								<?php the_excerpt(); ?>
							</div>

							<div class="search-card-action">
								<?php if ( $user_has_access ) : ?>
									<a href="<?php the_permalink(); ?>" class="search-read-btn">
										<?php esc_html_e( 'Read Analysis', 'ascendance' ); ?> &rarr;
									</a>
								<?php else : ?>
									<a href="<?php the_permalink(); ?>" class="search-unlock-btn">
										<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/></svg>
										<?php esc_html_e( 'Unlock Analytical Report', 'ascendance' ); ?>
									</a>
								<?php endif; ?>
							</div>
						</article>
						<?php
					endwhile;
					?>
				</div>

				<!-- Pagination -->
				<div class="search-pagination">
					<?php
					the_posts_pagination(
						array(
							'mid_size'  => 2,
							'prev_text' => sprintf( '&larr; %s', esc_html__( 'Previous', 'ascendance' ) ),
							'next_text' => sprintf( '%s &rarr;', esc_html__( 'Next', 'ascendance' ) ),
						)
					);
					?>
				</div>

			<?php else : ?>

				<!-- No Results State -->
				<div class="search-empty-state">
					<span class="empty-icon">&empty;</span>
					<h2><?php esc_html_e( 'No Intelligence Found', 'ascendance' ); ?></h2>
					<p>
						<?php esc_html_e( 'We could not locate any intelligence reports, briefs, or dossiers matching your query. Verify the spelling or try alternative keywords.', 'ascendance' ); ?>
					</p>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="search-home-btn"><?php esc_html_e( 'Return to Home Feed', 'ascendance' ); ?></a>
				</div>

			<?php endif; ?>
		</div>
	</div>

</main>

<?php
get_footer();
