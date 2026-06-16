<?php
/**
 * The template for displaying all single Dossier CPT posts
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="site-main register-dossier">

	<?php
	while ( have_posts() ) :
		the_post();
		
		$post_id = get_the_ID();
		$summary = get_field( 'executive_summary', $post_id );
		$pdf = get_field( 'download_pdf', $post_id );
		$related = get_field( 'related_briefs', $post_id );
		$stakeholders = get_field( 'stakeholders', $post_id );
		$tier = get_field( 'tier_access', $post_id ) ?: 'professional';
		?>

		<article id="post-<?php the_ID(); ?>" <?php post_class( 'container' ); ?>>
			<!-- Dossier Header -->
			<header style="padding: var(--space-40) 0; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: var(--space-20);">
				<div>
					<div style="display: flex; gap: 8px; margin-bottom: var(--space-10);">
						<span class="paywall-badge" style="background-color: var(--color-red); color: var(--color-white);"><?php echo esc_html( ucfirst( $tier ) ); ?> <?php esc_html_e( 'Dossier', 'ascendance' ); ?></span>
						<?php
						$industries = get_the_terms( $post_id, 'industry' );
						if ( ! empty( $industries ) && ! is_wp_error( $industries ) ) :
							foreach ( $industries as $industry ) :
								?>
								<span style="border: 1px solid var(--border-color); color: var(--text-secondary); font-size: 11px; padding: 2px 8px; border-radius: var(--radius-sm);"><?php echo esc_html( $industry->name ); ?></span>
								<?php
							endforeach;
						endif;
						?>
					</div>
					<h1 style="margin-bottom: 0; color: var(--color-white); font-family: var(--font-heading);"><?php the_title(); ?></h1>
				</div>
				<div style="font-family: var(--font-heading); font-size: var(--font-size-xs); color: var(--text-muted);">
					<span><i class="fa-regular fa-folder" style="color: var(--color-red); margin-right: 6px;"></i> <?php esc_html_e( 'Dossier Ledger', 'ascendance' ); ?></span>
					<span style="margin-left: 20px;"><i class="fa-regular fa-calendar" style="color: var(--color-red); margin-right: 6px;"></i> <?php echo get_the_date(); ?></span>
				</div>
			</header>

			<!-- Dossier Grid -->
			<div class="register-dossier-grid">
				
				<!-- Sidebar (Dossier Meta, PDF, Stakeholders) -->
				<div class="register-dossier-meta">
					<h3 style="font-size: var(--font-size-sm); text-transform: uppercase; letter-spacing: 1px; color: var(--color-white); border-bottom: 1px dashed var(--border-color); padding-bottom: 8px; margin-bottom: var(--space-20);"><i class="fa-solid fa-circle-info" style="color: var(--color-red); margin-right: 8px;"></i><?php esc_html_e( 'Dossier Index', 'ascendance' ); ?></h3>
					
					<!-- Metadata -->
					<div class="register-dossier-meta-item">
						<span class="register-dossier-meta-label"><?php esc_html_e( 'Security', 'ascendance' ); ?></span>
						<span style="font-weight: bold; color: var(--color-white);"><?php echo esc_html( ucfirst( $tier ) ); ?></span>
					</div>
					<div class="register-dossier-meta-item">
						<span class="register-dossier-meta-label"><?php esc_html_e( 'Geographic', 'ascendance' ); ?></span>
						<span style="font-weight: bold; color: var(--color-white);"><?php the_terms( $post_id, 'region', '', ', ', '' ); ?></span>
					</div>
					<div class="register-dossier-meta-item" style="border-bottom: none; margin-bottom: var(--space-30);">
						<span class="register-dossier-meta-label"><?php esc_html_e( 'Ref Code', 'ascendance' ); ?></span>
						<span style="font-family: var(--font-mono); font-size: 12px; color: var(--color-red);">DOS-<?php echo esc_html( $post_id ); ?></span>
					</div>

					<!-- PDF Download Action -->
					<?php if ( class_exists( 'Ascendance\Core\Paywall' ) && Ascendance\Core\Paywall::get_instance()->user_has_access() ) : ?>
						<?php if ( ! empty( $pdf ) ) : ?>
							<a href="<?php echo esc_url( $pdf['url'] ); ?>" download class="btn btn-primary" style="width: 100%; margin-bottom: var(--space-40); gap: 10px;">
								<i class="fa-solid fa-file-pdf"></i>
								<?php esc_html_e( 'Download Dossier PDF', 'ascendance' ); ?>
							</a>
						<?php else : ?>
							<button class="btn btn-secondary" disabled style="width: 100%; margin-bottom: var(--space-40); cursor: not-allowed; opacity: 0.5;">
								<i class="fa-solid fa-triangle-exclamation"></i>
								<?php esc_html_e( 'PDF Pending Release', 'ascendance' ); ?>
							</button>
						<?php endif; ?>
					<?php else : ?>
						<button class="btn btn-primary" disabled style="width: 100%; margin-bottom: var(--space-40); cursor: not-allowed; opacity: 0.5;">
							<i class="fa-solid fa-lock"></i>
							<?php esc_html_e( 'Unlock PDF Download', 'ascendance' ); ?>
						</button>
					<?php endif; ?>

					<!-- Stakeholders list -->
					<?php if ( ! empty( $stakeholders ) && class_exists( 'Ascendance\Core\Paywall' ) && Ascendance\Core\Paywall::get_instance()->user_has_access() ) : ?>
						<h3 style="font-size: var(--font-size-sm); text-transform: uppercase; letter-spacing: 1px; color: var(--color-white); border-bottom: 1px dashed var(--border-color); padding-bottom: 8px; margin-bottom: var(--space-20); margin-top: var(--space-30);"><i class="fa-solid fa-network-wired" style="color: var(--color-red); margin-right: 8px;"></i><?php esc_html_e( 'Actors Tracked', 'ascendance' ); ?></h3>
						<div style="display: flex; flex-direction: column; gap: 12px;">
							<?php foreach ( $stakeholders as $sh ) : ?>
								<div style="background-color: rgba(255,255,255,0.02); border: 1px solid var(--border-color); padding: 10px 12px; border-radius: var(--radius-sm);">
									<span style="font-weight: bold; color: var(--color-white); font-size: 13px; display: block;"><?php echo esc_html( $sh['name'] ); ?></span>
									<span style="color: var(--text-muted); font-size: 11px; text-transform: uppercase;"><?php echo esc_html( $sh['role'] ); ?></span>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>

				<!-- Main Content Area -->
				<div>
					<!-- Executive Summary Section -->
					<?php if ( ! empty( $summary ) ) : ?>
						<div style="background-color: var(--color-navy); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: var(--space-30); margin-bottom: var(--space-30);">
							<h3 style="font-size: var(--font-size-sm); text-transform: uppercase; letter-spacing: 1px; color: var(--color-white); border-bottom: 1px dashed var(--border-color); padding-bottom: 8px; margin-bottom: var(--space-20);"><i class="fa-solid fa-paragraph" style="color: var(--color-red); margin-right: 8px;"></i><?php esc_html_e( 'Executive Intelligence Summary', 'ascendance' ); ?></h3>
							<p style="color: var(--text-secondary); line-height: 1.7; font-size: var(--font-size-sm); margin-bottom: 0;">
								<?php echo esc_html( $summary ); ?>
							</p>
						</div>
					<?php endif; ?>

					<!-- Content (Paywall Filtered) -->
					<div class="entry-content" style="color: var(--text-secondary); margin-bottom: var(--space-40);">
						<?php the_content(); ?>
					</div>

					<!-- Related Briefs -->
					<?php if ( ! empty( $related ) && class_exists( 'Ascendance\Core\Paywall' ) && Ascendance\Core\Paywall::get_instance()->user_has_access() ) : ?>
						<div style="margin-top: var(--space-50); border-top: 1px solid var(--border-color); padding-top: var(--space-30);">
							<h3 style="font-size: var(--font-size-sm); text-transform: uppercase; letter-spacing: 1px; color: var(--color-white); margin-bottom: var(--space-20);"><i class="fa-solid fa-link" style="color: var(--color-red); margin-right: 8px;"></i><?php esc_html_e( 'Cross-Referenced Briefings', 'ascendance' ); ?></h3>
							<div style="display: grid; grid-template-columns: 1fr; gap: 12px;">
								<?php foreach ( $related as $brief ) : ?>
									<a href="<?php echo esc_url( get_permalink( $brief->ID ) ); ?>" class="card" style="padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; border-radius: var(--radius-sm);">
										<span style="font-weight: bold; color: var(--color-white); font-size: var(--font-size-sm);"><?php echo esc_html( $brief->post_title ); ?></span>
										<i class="fa-solid fa-chevron-right" style="color: var(--color-red); font-size: 11px;"></i>
									</a>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>
				</div>

			</div>
		</article>

	<?php endwhile; ?>

</main>

<?php
get_footer();
