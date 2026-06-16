<?php
/**
 * The template for displaying all single Brief CPT posts
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="site-main register-editorial">

	<?php
	while ( have_posts() ) :
		the_post();
		
		$post_id = get_the_ID();
		$claim = get_field( 'analytical_claim', $post_id );
		$summary = get_field( 'executive_summary', $post_id );
		$findings = get_field( 'key_findings', $post_id );
		$references = get_field( 'source_references', $post_id );
		$tier = get_field( 'tier_access', $post_id ) ?: 'essential';
		?>

		<article id="post-<?php the_ID(); ?>" <?php post_class( 'container' ); ?>>
			<!-- Post Header -->
			<header style="max-width: 800px; margin: 0 auto var(--space-40) auto; text-align: center;">
				<div style="display: flex; justify-content: center; gap: 10px; margin-bottom: var(--space-10); flex-wrap: wrap;">
					<span class="paywall-badge"><?php echo esc_html( ucfirst( $tier ) ); ?> <?php esc_html_e( 'Briefing', 'ascendance' ); ?></span>
					<?php
					$topics = get_the_terms( $post_id, 'topic' );
					if ( ! empty( $topics ) && ! is_wp_error( $topics ) ) :
						foreach ( $topics as $topic ) :
							?>
							<span style="border: 1px solid var(--color-red); color: var(--color-red); font-family: var(--font-heading); font-size: 11px; font-weight: bold; text-transform: uppercase; padding: 2px 8px; border-radius: var(--radius-sm);"><?php echo esc_html( $topic->name ); ?></span>
							<?php
						endforeach;
					endif;
					?>
				</div>

				<h1 style="margin-bottom: var(--space-20); color: var(--color-deep-navy); font-family: var(--font-heading); font-weight: var(--weight-bold); line-height: 1.25;"><?php the_title(); ?></h1>
				
				<div style="display: flex; justify-content: center; gap: var(--space-30); color: var(--text-muted); font-size: var(--font-size-sm); font-family: var(--font-heading);">
					<span><i class="fa-regular fa-calendar" style="color: var(--color-red); margin-right: 6px;"></i> <?php echo get_the_date(); ?></span>
					<span><i class="fa-regular fa-user" style="color: var(--color-red); margin-right: 6px;"></i> <?php the_author(); ?></span>
				</div>
			</header>

			<div class="entry-content">
				<!-- Analytical Claim Block -->
				<?php if ( ! empty( $claim ) ) : ?>
					<div class="register-terminal" style="margin-bottom: var(--space-40);">
						<div class="register-terminal-header">
							<span><?php esc_html_e( 'ANALYTICAL CLAIM FEED // SECURE_ACCESS', 'ascendance' ); ?></span>
							<span>ID: <?php echo esc_html( $post_id ); ?></span>
						</div>
						<div class="register-terminal-row">
							<span class="register-terminal-prompt">&gt;</span>
							<span><strong style="color: var(--color-white);"><?php esc_html_e( 'THESIS:', 'ascendance' ); ?></strong> <?php echo esc_html( $claim ); ?></span>
						</div>
					</div>
				<?php endif; ?>

				<!-- Executive Summary -->
				<?php if ( ! empty( $summary ) ) : ?>
					<div style="font-size: 1.25rem; font-weight: 500; line-height: 1.7; color: var(--color-deep-navy); margin-bottom: var(--space-40); border-bottom: 1px solid rgba(10, 22, 40, 0.1); padding-bottom: var(--space-30);">
						<?php echo esc_html( $summary ); ?>
					</div>
				<?php endif; ?>

				<!-- Main Content (Paywall Filtered) -->
				<div class="main-body-content" style="color: rgba(10, 22, 40, 0.85); font-family: var(--font-body);">
					<?php the_content(); ?>
				</div>

				<!-- Key Findings Section -->
				<?php if ( ! empty( $findings ) && class_exists( 'Ascendance\Core\Paywall' ) && Ascendance\Core\Paywall::get_instance()->user_has_access() ) : ?>
					<div style="margin-top: var(--space-50); background-color: rgba(10, 22, 40, 0.03); border: 1px solid rgba(10, 22, 40, 0.08); border-radius: var(--radius-md); padding: var(--space-30);">
						<h3 style="color: var(--color-deep-navy); font-family: var(--font-heading); margin-bottom: var(--space-20); display: flex; align-items: center; gap: 10px;">
							<i class="fa-solid fa-list-check" style="color: var(--color-red); font-size: var(--font-size-md);"></i>
							<?php esc_html_e( 'Key Findings & Analytics', 'ascendance' ); ?>
						</h3>
						<div style="color: rgba(10, 22, 40, 0.85); font-size: var(--font-size-sm); line-height: 1.7;">
							<?php echo wp_kses_post( $findings ); ?>
						</div>
					</div>
				<?php endif; ?>

				<!-- Source References Table -->
				<?php if ( ! empty( $references ) && class_exists( 'Ascendance\Core\Paywall' ) && Ascendance\Core\Paywall::get_instance()->user_has_access() ) : ?>
					<div style="margin-top: var(--space-40);">
						<h3 style="color: var(--color-deep-navy); font-family: var(--font-heading); margin-bottom: var(--space-20); font-size: var(--font-size-md);"><i class="fa-solid fa-feather-pointed" style="color: var(--color-red); margin-right: 8px;"></i><?php esc_html_e( 'Source Ledger & References', 'ascendance' ); ?></h3>
						<table style="width: 100%; border-collapse: collapse; font-family: var(--font-heading); font-size: var(--font-size-sm); text-align: left;">
							<thead>
								<tr style="border-bottom: 2px solid var(--color-deep-navy);">
									<th style="padding: 10px 0;"><?php esc_html_e( 'Source Agency / Document', 'ascendance' ); ?></th>
									<th style="padding: 10px 0; text-align: right;"><?php esc_html_e( 'Reference Route', 'ascendance' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $references as $ref ) : ?>
									<tr style="border-bottom: 1px solid rgba(10, 22, 40, 0.08);">
										<td style="padding: 12px 0; font-weight: bold; color: var(--color-deep-navy);"><?php echo esc_html( $ref['name'] ); ?></td>
										<td style="padding: 12px 0; text-align: right;">
											<?php if ( ! empty( $ref['url'] ) ) : ?>
												<a href="<?php echo esc_url( $ref['url'] ); ?>" target="_blank" rel="noopener" style="color: var(--color-red);"><i class="fa-solid fa-arrow-up-right-from-square"></i> <?php esc_html_e( 'Access Source', 'ascendance' ); ?></a>
											<?php else : ?>
												<span style="color: var(--text-muted); font-style: italic;"><?php esc_html_e( 'Classified Document', 'ascendance' ); ?></span>
											<?php endif; ?>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>

			</div>

			<!-- Footer Tags -->
			<footer style="max-width: 800px; margin: var(--space-40) auto 0 auto; padding-top: var(--space-20); border-top: 1px solid rgba(10, 22, 40, 0.08); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
				<span style="font-family: var(--font-heading); font-size: var(--font-size-xs); color: var(--text-muted);">
					<?php esc_html_e( 'Regions: ', 'ascendance' ); ?>
					<?php the_terms( $post_id, 'region', '', ', ', '' ); ?>
				</span>
				
				<?php if ( comments_open() || get_comments_number() ) : ?>
					<div style="font-family: var(--font-heading);">
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
