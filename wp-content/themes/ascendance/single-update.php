<?php
/**
 * Single Update Template — Ascendance Intelligence Platform
 *
 * Updates are Open analysis pieces for SEO and AEO.
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="as-main">

<?php
while ( have_posts() ) :
	the_post();
	$subhead = get_field( 'subhead', get_the_ID() ) ?: get_the_excerpt();
	?>

	<article id="post-<?php the_ID(); ?>" class="as-article-page">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="as-back">&larr; Back to Latest Feed</a>

		<!-- Article Header -->
		<header class="as-article-head">
			<span class="as-kicker-slab big" style="color:var(--success);">
				Update &middot; Open Analysis &middot; <?php echo esc_html( get_the_date( 'd M Y' ) ); ?>
			</span>
			<h1 class="as-article-title"><?php the_title(); ?></h1>
			<?php if ( $subhead ) : ?>
				<p class="as-article-dek"><?php echo esc_html( $subhead ); ?></p>
			<?php endif; ?>

			<!-- Byline -->
			<div class="as-byline">
				<div class="as-byline-id">
					<div class="as-avatar">AS</div>
					<div class="as-byline-txt">
						<span class="as-byline-name">Ascendance Analysis Desk</span>
						<span class="as-byline-role">Paris &middot; Washington &middot; Kinshasa</span>
					</div>
				</div>
				<div class="as-byline-meta">
					<span><?php echo esc_html( get_the_date( 'd F Y' ) ); ?></span>
					<span>&middot;</span>
					<span>4 min read</span>
					<span>&middot;</span>
					<span class="as-access" style="color:var(--success);"><span class="as-dot" style="background:var(--success);"></span>Open Access</span>
					<?php get_template_part( 'template-parts/save-button' ); ?>
				</div>
			</div>
		</header>

		<!-- Lead Figure Image if present -->
		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="as-article-figure">
				<div class="as-photo" style="padding-bottom:56.25%;">
					<div class="as-photo-in">
						<?php the_post_thumbnail( 'large', array(
							'style'         => 'width:100%; height:100%; object-fit:cover;',
							'loading'       => 'eager',
							'fetchpriority' => 'high',
							'decoding'      => 'sync',
						) ); ?>
					</div>
				</div>
				<figcaption><?php echo esc_html( get_the_post_thumbnail_caption() ?: get_the_title() ); ?></figcaption>
			</figure>
		<?php endif; ?>

		<!-- Reading Grid -->
		<div class="as-article-grid">
			<!-- Left Main Column -->
			<div class="as-article-main">
				<div class="as-dateline">KINSHASA / PARIS</div>
				
				<div class="as-prose as-prose-lead">
					<?php the_content(); ?>
				</div>

				<!-- Advisory Rail Callout -->
				<div class="as-advisory-card">
					<span class="ac-eyebrow">Advisory Rail &middot; Direct Engagement</span>
					<div class="ac-title">Questions about how this update impacts your assets?</div>
					<a class="ac-cta" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Schedule a consultation &rarr;</a>
				</div>

				<!-- Article Tags -->
				<?php get_template_part( 'template-parts/article-tags' ); ?>
			</div>

			<!-- Right Sticky Rail -->
			<aside class="as-rail-sticky">
				<div class="as-rail-panel">
					<div class="as-rail-head">Key Metadata</div>
					<dl class="as-keyfacts">
						<div><dt>Type</dt><dd>Open Update</dd></div>
						<div><dt>Date</dt><dd><?php echo esc_html( get_the_date( 'd M Y' ) ); ?></dd></div>
						<div><dt>Access</dt><dd>Open</dd></div>
					</dl>
				</div>

				<div class="as-rail-panel">
					<div class="as-rail-head">Core Registers</div>
					<div class="as-entities">
						<a class="as-entity" href="<?php echo esc_url( home_url( '/spa-glossary/' ) ); ?>">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
							SPA Glossary
						</a>
						<a class="as-entity" href="<?php echo esc_url( home_url( '/regulatory-reform-tracker/' ) ); ?>">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
							Regulatory Reform Tracker
						</a>
					</div>
				</div>

				<?php get_template_part( 'template-parts/private-notes' ); ?>
			</aside>
		</div>
	</article>

<?php endwhile; ?>

</main>

<?php
get_footer();
