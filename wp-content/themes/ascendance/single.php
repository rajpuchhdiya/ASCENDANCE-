<?php
/**
 * Single Post Template — Ascendance Intelligence Platform
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="as-main">

<?php
while ( have_posts() ) :
	the_post();
	?>

	<article id="post-<?php the_ID(); ?>" class="as-article-page">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="as-back">&larr; Back to Latest Feed</a>

		<!-- Article Header -->
		<header class="as-article-head">
			<span class="as-kicker-slab big">
				<?php echo esc_html( get_the_category_list( ', ' ) ?: 'Intelligence' ); ?> &middot; <?php echo esc_html( get_the_date( 'd M Y' ) ); ?>
			</span>
			<h1 class="as-article-title"><?php the_title(); ?></h1>
			<p class="as-article-dek"><?php echo esc_html( get_the_excerpt() ); ?></p>

			<!-- Byline -->
			<div class="as-byline">
				<div class="as-byline-id">
					<div class="as-avatar">AS</div>
					<div class="as-byline-txt">
						<span class="as-byline-name"><?php the_author(); ?></span>
						<span class="as-byline-role">Ascendance Intelligence Desk</span>
					</div>
				</div>
				<div class="as-byline-meta">
					<span><?php echo esc_html( get_the_date( 'd F Y' ) ); ?></span>
					<span>&middot;</span>
					<span class="as-access"><span class="as-dot"></span>Articles</span>
				</div>
			</div>
		</header>

		<!-- Lead Figure Image if present -->
		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="as-article-figure">
				<div class="as-photo" style="padding-bottom:56.25%;">
					<div class="as-photo-in">
						<?php the_post_thumbnail( 'large', array( 'style' => 'width:100%; height:100%; object-fit:cover;' ) ); ?>
					</div>
				</div>
				<figcaption><?php echo esc_html( get_the_post_thumbnail_caption() ?: get_the_title() ); ?></figcaption>
			</figure>
		<?php endif; ?>

		<!-- Reading Grid -->
		<div class="as-article-grid">
			<!-- Left Main Column -->
			<div class="as-article-main">
				<div class="as-prose as-prose-lead">
					<?php the_content(); ?>
				</div>

				<!-- Advisory Rail Callout -->
				<div class="as-advisory-card">
					<span class="ac-eyebrow">Advisory Rail &middot; Direct Engagement</span>
					<div class="ac-title">Questions about how this analysis impacts your assets?</div>
					<a class="ac-cta" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Schedule a consultation &rarr;</a>
				</div>
			</div>

			<!-- Right Sticky Rail -->
			<aside class="as-rail-sticky">
				<div class="as-rail-panel">
					<div class="as-rail-head">Key Metadata</div>
					<dl class="as-keyfacts">
						<div><dt>Date</dt><dd><?php echo esc_html( get_the_date( 'd M Y' ) ); ?></dd></div>
						<div><dt>Author</dt><dd><?php the_author(); ?></dd></div>
					</dl>
				</div>
			</aside>
		</div>
	</article>

<?php endwhile; ?>

</main>

<?php
get_footer();
