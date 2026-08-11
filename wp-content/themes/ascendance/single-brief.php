<?php
/**
 * Single Brief Template — Ascendance Intelligence Platform
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="as-main">

<?php
while ( have_posts() ) :
	the_post();
	$post_id = get_the_ID();
	$subhead = get_field( 'subhead', $post_id ) ?: get_the_excerpt();
	$user_has_access = class_exists( 'Ascendance\Core\Paywall' )
		&& Ascendance\Core\Paywall::get_instance()->user_has_access( get_the_ID(), get_current_user_id() ); // Full tier + category gate
	?>

	<article id="post-<?php the_ID(); ?>" class="as-article-page">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="as-back">&larr; Back to Latest Feed</a>

		<!-- Article Header -->
		<header class="as-article-head">
			<span class="as-kicker-slab big">
				SPA Intelligence Brief &middot; <?php echo esc_html( get_the_date( 'd M Y' ) ); ?>
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
						<span class="as-byline-name">Ascendance Intelligence Desk</span>
						<span class="as-byline-role">Paris &middot; Washington &middot; Kinshasa</span>
					</div>
				</div>
				<div class="as-byline-meta">
					<span><?php echo esc_html( get_the_date( 'd F Y' ) ); ?></span>
					<span>&middot;</span>
					<span>5 min read</span>
					<span>&middot;</span>
					<span class="as-access"><span class="as-dot"></span>Subscribers</span>
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
				<div class="as-dateline">WASHINGTON / KINSHASA</div>
				
				<div class="as-prose as-prose-lead">
					<?php if ( $user_has_access ) : ?>
						<?php the_content(); ?>
					<?php else : ?>
						<p><?php echo esc_html( get_the_excerpt() ); ?></p>

						<!-- Paywall Gate for Non-subscribers -->
						<div class="as-gate">
							<div class="as-gate-fade"></div>
							<div class="as-gate-card">
								<span class="as-gate-lock">
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
									Subscribers Only
								</span>
								<h3>Unlock the Full SPA Briefing</h3>
								<p>This intelligence brief is reserved for subscribers of Ascendance Strategies.</p>
								<div style="display:flex; gap:12px; justify-content:center; margin-top:16px;">
									<a class="as-subscribe" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>">Subscribe to Read Full Brief</a>
									<a class="as-ghost sm" href="<?php echo esc_url( home_url( '/login/' ) ); ?>">Subscriber Log in</a>
								</div>
							</div>
						</div>
					<?php endif; ?>
				</div>

				<!-- Advisory Rail Callout -->
				<div class="as-advisory-card">
					<span class="ac-eyebrow">Advisory Rail &middot; Engagement Desk</span>
					<div class="ac-title">Weighing an investment affected by this briefing?</div>
					<a class="ac-cta" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Request an advisory assessment &rarr;</a>
				</div>

				<!-- Article Tags -->
				<?php get_template_part( 'template-parts/article-tags' ); ?>
			</div>

			<!-- Right Sticky Rail -->
			<aside class="as-rail-sticky">
				<div class="as-rail-panel">
					<div class="as-rail-head">Key Metadata</div>
					<dl class="as-keyfacts">
						<div><dt>Category</dt><dd>SPA Brief</dd></div>
						<div><dt>Framework</dt><dd>Article VII</dd></div>
						<div><dt>Publication</dt><dd><?php echo esc_html( get_the_date( 'd M Y' ) ); ?></dd></div>
						<div><dt>Access</dt><dd>Subscribers</dd></div>
					</dl>
					<?php
					$post_id_val = get_the_ID();
					$user_id_val = get_current_user_id();
					if ( class_exists( 'Ascendance\Core\Paywall' ) && Ascendance\Core\Paywall::get_instance()->user_has_access( $post_id_val, $user_id_val ) ) :
					?>
					<div style="margin-top: 12px; border-top: 1px solid var(--hairline, #eee); padding-top: 10px;">
						<button type="button" class="btn-export-pdf btn btn-ghost" data-post-id="<?php echo $post_id_val; ?>" style="width: 100%; font-size: 11px; padding: 6px 12px; display: flex; align-items: center; justify-content: center; gap: 6px;">
							<i class="fa-solid fa-file-pdf" style="color: #C0392B;"></i> Export PDF Brief
						</button>
					</div>
					<?php endif; ?>
				</div>

				<div class="as-rail-panel">
					<div class="as-rail-head">Core Desks</div>
					<div class="as-entities">
						<a class="as-entity" href="<?php echo esc_url( home_url( '/spa-glossary/' ) ); ?>">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
							SPA Glossary Data Terminal
						</a>
						<a class="as-entity" href="<?php echo esc_url( home_url( '/lobito-corridor/' ) ); ?>">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
							Lobito Corridor Dossier
						</a>
					</div>
				</div>

				<?php get_template_part( 'template-parts/private-notes' ); ?>
			</aside>
		</div>
	</article>

<?php endwhile; ?>

<script>
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.btn-export-pdf');
    if (!btn) return;
    var postId = btn.dataset.postId;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating PDF...';

    var fd = new FormData();
    fd.append('action', 'asc_generate_pdf_token');
    fd.append('security', '<?php echo esc_js( wp_create_nonce( 'as_save_nonce' ) ); ?>');
    fd.append('post_id', postId);

    fetch('<?php echo esc_url_raw( admin_url( 'admin-ajax.php' ) ); ?>', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-file-pdf" style="color: #C0392B;"></i> Export PDF Brief';
            if (res.success && res.data && res.data.download_url) {
                window.location.href = res.data.download_url;
            } else {
                alert(res.data && res.data.message ? res.data.message : 'Unable to generate PDF. Authorization required.');
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-file-pdf" style="color: #C0392B;"></i> Export PDF Brief';
            alert('Connection error while generating PDF.');
        });
});
</script>

<?php
get_footer();
