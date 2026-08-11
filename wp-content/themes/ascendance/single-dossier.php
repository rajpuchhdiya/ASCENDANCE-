<?php
/**
 * Single Dossier Template — Ascendance Intelligence Platform
 *
 * Dossiers are living reference extractions.
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="site-main">

<?php
while ( have_posts() ) :
	the_post();
	$post_id = get_the_ID();
	$subhead = get_field( 'subhead', $post_id ) ?: get_the_excerpt();
	$user_has_access = class_exists( 'Ascendance\Core\Paywall' )
		&& Ascendance\Core\Paywall::get_instance()->user_has_access( get_the_ID(), get_current_user_id() ); // Full tier + category gate
	
	// Front matter fields
	$dossier_version = function_exists('get_field') ? get_field('dossier_version', $post_id) : '';
	$source_of_truth = function_exists('get_field') ? get_field('source_of_truth', $post_id) : '';
	$vintage         = function_exists('get_field') ? get_field('vintage', $post_id) : '';
	$maintained_by   = function_exists('get_field') ? get_field('maintained_by', $post_id) : '';
	$companion       = function_exists('get_field') ? get_field('companion_register', $post_id) : '';
	
	// Fallbacks
	$dossier_version = $dossier_version ?: '1.0';
	$source_of_truth = $source_of_truth ?: 'Ascendance Strategies';
	$vintage         = $vintage ?: get_the_date( 'j F Y' );
	$maintained_by   = $maintained_by ?: 'Ascendance Strategies';
	?>

	<article id="post-<?php the_ID(); ?>" class="as-article-page as-dossier-layout">
		<style>
		/* --- Dossier Article Layout (Inlined for Cache Busting) --- */
		@import url('https://cdn.jsdelivr.net/npm/@fontsource/cooper-hewitt@5.2.5/600.css');
		@import url('https://cdn.jsdelivr.net/npm/@fontsource/cooper-hewitt@5.2.5/700.css');

		.as-dossier-layout {
		  --d-red: #BC1B1D; --d-red-deep: #8E1416;
		  --d-navy: #0F1E35; --d-navy-deep: #0A1628;
		  --d-cream: #F7F4EF; --d-paper: #FFFFFF;
		  --d-ink: #1A1A2E; --d-stone: #56514B; --d-muted: #6B6B7A;
		  --d-rule: #E8E4DC;
		  --d-serif: "Noto Serif", "Iowan Old Style", Georgia, serif;
		  --d-sans: "Cooper Hewitt", "Barlow", "Helvetica Neue", Arial, sans-serif;
		  --d-mono: "JetBrains Mono", "SFMono-Regular", Menlo, monospace;
		  
		  background: var(--d-cream) !important;
		  color: var(--d-ink) !important;
		  font-family: var(--d-serif) !important;
		  line-height: 1.62;
		  -webkit-font-smoothing: antialiased;
		  padding: 40px 28px 90px !important;
		}
		html.dark .as-dossier-layout,
		html[data-theme="dark"] .as-dossier-layout {
		  --d-navy: #f3eee4; --d-navy-deep: #17140f;
		  --d-cream: #17140f; --d-paper: #221e17;
		  --d-ink: #f3eee4; --d-stone: #cec7b8; --d-muted: #98917f;
		  --d-rule: #322c23;
		}

		.as-dossier-layout .fm { background: var(--d-navy-deep); color: #C8D0DC; border-left: 3px solid var(--d-red); padding: 20px 24px; margin: 34px 0 0; font-family: var(--d-mono); font-size: 11.5px; line-height: 1.85; }
		.as-dossier-layout .fm b { color: var(--d-red); font-weight: 600; letter-spacing: .06em; }
		.as-dossier-layout .fm a { color: #C8D0DC; }
		.as-dossier-layout .eyebrow { font-family: var(--d-sans); font-size: 11px; font-weight: 700; letter-spacing: .2em; text-transform: uppercase; color: var(--d-red); margin: 44px 0 10px; }
		.as-dossier-layout h1 { font-family: var(--d-serif); font-weight: 400; font-size: 44px; line-height: 1.14; letter-spacing: -.015em; margin: 0 0 16px; color: var(--d-navy); }
		html.dark .as-dossier-layout h1 { color: var(--d-ink); }
		.as-dossier-layout .standfirst { font-size: 20px; line-height: 1.55; color: var(--d-stone); font-style: italic; margin: 0 0 26px; }
		.as-dossier-layout .byline { font-family: var(--d-mono); font-size: 11.5px; color: var(--d-muted); border-top: 1px solid var(--d-rule); border-bottom: 1px solid var(--d-rule); padding: 11px 0; margin-bottom: 38px; display: flex; gap: 18px; flex-wrap: wrap; }
		.as-dossier-layout p { margin: 0 0 18px; font-size: 17.5px; }
		.as-dossier-layout .lede,
		.as-dossier-layout .as-dossier-content > p:first-of-type { font-size: 19px; }
		.as-dossier-layout .as-dossier-content > p:first-of-type::first-letter { color: var(--d-red); float: left; font-size: 84px; line-height: 60px; padding-top: 4px; padding-right: 8px; font-family: var(--d-serif); font-weight: 600; }
		.as-dossier-layout h2 { font-family: var(--d-serif); font-weight: 400; font-size: 27px; line-height: 1.25; color: var(--d-navy); margin: 52px 0 4px; padding-left: 16px; border-left: 3px solid var(--d-red); }
		html.dark .as-dossier-layout h2 { color: var(--d-ink); }
		.as-dossier-layout h2 .num { font-family: var(--d-mono); font-size: 12px; font-weight: 600; color: var(--d-red); display: block; margin-bottom: 4px; }
		.as-dossier-layout .meta { margin: 24px 0 42px; font-family: var(--d-sans); font-size: 11px; letter-spacing: .03em; color: var(--d-muted); display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
		.as-dossier-layout .chip { border: 1px solid; border-radius: 20px; padding: 2px 10px; font-weight: 600; }
		.as-dossier-layout .chip.hi { border-color: #2F6D3B; color: #23542C; }
		html.dark .as-dossier-layout .chip.hi { border-color: #3d8c4c; color: #51b563; }
		.as-dossier-layout .chip.med { border-color: #B08A2E; color: #8A6D24; }
		.as-dossier-layout .chip.low { border-color: var(--d-muted); color: var(--d-muted); }
		.as-dossier-layout ul { padding-left: 20px; margin: 0 0 18px; }
		.as-dossier-layout li { margin-bottom: 9px; font-size: 17px; }
		.as-dossier-layout li::marker { color: var(--d-red); }
		
		/* GATE CSS */
		.as-dossier-layout .gate { margin: 46px 0; border-top: 2px solid var(--d-red); background: var(--d-paper); padding: 24px 26px; }
		.as-dossier-layout .gate .g-label { font-family: var(--d-sans); font-size: 11px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--d-red); margin-bottom: 8px; }
		.as-dossier-layout .gate p { font-size: 16px; color: var(--d-stone); margin: 0 0 6px; }
		.as-dossier-layout .gate .tier { font-family: var(--d-mono); font-size: 12px; color: var(--d-navy); margin-top: 12px; }
		html.dark .as-dossier-layout .gate .tier { color: var(--d-ink); }
		
		.as-dossier-layout .xlink { display: block; background: var(--d-navy-deep); color: #E8ECF2; text-decoration: none; padding: 22px 26px; margin: 40px 0; border-left: 3px solid var(--d-red); }
		.as-dossier-layout .xlink .k { font-family: var(--d-sans); font-size: 10.5px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--d-red); }
		.as-dossier-layout .xlink .t { font-family: var(--d-serif); font-size: 21px; margin: 6px 0 4px; }
		.as-dossier-layout .xlink .d { font-family: var(--d-mono); font-size: 11.5px; color: #9FB0C4; }
		.as-dossier-layout table { width: 100%; border-collapse: collapse; margin: 22px 0; font-size: 14.5px; }
		.as-dossier-layout th { background: var(--d-navy); color: var(--d-cream); font-family: var(--d-sans); font-size: 10.5px; font-weight: 700; letter-spacing: .09em; text-transform: uppercase; text-align: left; padding: 9px 12px; }
		.as-dossier-layout td { padding: 9px 12px; border-bottom: 1px solid var(--d-rule); vertical-align: top; }
		.as-dossier-layout tbody tr:nth-child(odd) { background: var(--d-paper); }
		.as-dossier-layout td:first-child { border-left: 3px solid var(--d-red); font-weight: 600; color: var(--d-navy); }
		html.dark .as-dossier-layout td:first-child { color: var(--d-ink); }
		.as-dossier-layout .advisory { background: var(--d-paper); border: 1px solid var(--d-rule); border-left: 3px solid var(--d-red); border-radius: 2px; padding: 22px 24px; margin: 40px 0; }
		.as-dossier-layout .advisory .g-label { font-family: var(--d-sans); font-size: 11px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--d-red); margin-bottom: 8px; }
		.as-dossier-layout .advisory p { font-size: 15.5px; color: var(--d-stone); margin: 0 0 14px; }
		.as-dossier-layout .advisory a.cta { display: inline-block; font-family: var(--d-sans); font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #fff; background: var(--d-red); padding: 10px 20px; border-radius: 2px; text-decoration: none; }
		.as-dossier-layout .advisory a.cta:hover { background: var(--d-red-deep); }
		.as-dossier-layout .signoff { margin: 64px 0 0; padding-top: 26px; border-top: 1px solid var(--d-rule); text-align: center; font-style: italic; color: var(--d-red); letter-spacing: .04em; font-size: 16px; }
		</style>
		<a href="<?php echo esc_url( home_url( '/us-drc-partnership/' ) ); ?>" class="as-back">&larr; Back to Partnership Hub</a>

		<!-- Article Header -->
		<header class="as-article-head">
			<span class="as-kicker-slab big" style="color:var(--gold, #b08d48);">
				Flagship Dossier &middot; Living File &middot; <?php echo esc_html( get_the_date( 'd M Y' ) ); ?>
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
						<span class="as-byline-name">Ascendance Research Desk</span>
						<span class="as-byline-role">Paris &middot; Washington &middot; Kinshasa</span>
					</div>
				</div>
				<div class="as-byline-meta">
					<span>Updated <?php echo esc_html( get_the_date( 'M Y' ) ); ?></span>
					<span>&middot;</span>
					<span>Living Extraction</span>
					<span>&middot;</span>
					<span class="as-access"><span class="as-dot" style="background:var(--red);"></span>Professional Tier</span>
					<?php get_template_part( 'template-parts/save-button' ); ?>
				</div>
			</div>
		</header>

		<div class="as-article-grid">
			<!-- Left Main Column -->
			<div class="as-article-main">
				<div class="as-dateline">WASHINGTON / KINSHASA</div>

				<div class="fm">
					<b>DOSSIER</b> <?php the_title(); ?><br>
					<b>VERSION</b> <?php echo esc_html( $dossier_version ); ?>, published <?php echo get_the_date( 'j F Y' ); ?><br>
					<b>SOURCE OF TRUTH</b> <?php echo esc_html( $source_of_truth ); ?><br>
					<b>VINTAGE</b> <?php echo esc_html( $vintage ); ?><br>
					<b>MAINTAINED BY</b> <?php echo esc_html( $maintained_by ); ?><br>
					<?php if ( $companion ) : ?>
					<b>COMPANION</b> <a href="<?php echo esc_url( is_array($companion) ? $companion['url'] : '#' ); ?>"><?php echo esc_html( is_array($companion) ? $companion['title'] : 'Corridor Project Register' ); ?></a>
					<?php endif; ?>
				</div>

				<div class="as-dossier-content">
					<?php if ( $user_has_access ) : ?>
						<?php the_content(); ?>
					<?php else : ?>
						<p class="lede"><?php echo esc_html( get_the_excerpt() ); ?></p>
						<!-- Paywall Gate for Non-subscribers -->
						<div class="gate">
							<div class="g-label">Public extract ends here</div>
							<p>The full Dossier continues with the legal and displacement analysis, the ownership and financing map, the provincial fiscal position, the traceability assessment, and the closing read on what could make the corridor fail.</p>
							<div class="tier">Professional tier. <a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>">Subscribe</a> or <a href="<?php echo esc_url( home_url( '/login/' ) ); ?>">Log in</a></div>
						</div>
					<?php endif; ?>
				</div>
				
				
			</div>

			<!-- Right Sticky Rail -->
			<aside class="as-rail-sticky">
				<div class="as-rail-panel">
					<div class="as-rail-head">Dossier Details</div>
					<dl class="as-keyfacts">
						<div><dt>Type</dt><dd>Living Dossier</dd></div>
						<div><dt>Tier</dt><dd>Professional</dd></div>
						<div><dt>Last Update</dt><dd><?php echo esc_html( get_the_date( 'M Y' ) ); ?></dd></div>
					</dl>
					<?php
					$post_id_val = get_the_ID();
					$user_id_val = get_current_user_id();
					if ( class_exists( 'Ascendance\Core\Paywall' ) && Ascendance\Core\Paywall::get_instance()->user_has_access( $post_id_val, $user_id_val ) ) :
					?>
					<div style="margin-top: 12px; border-top: 1px solid var(--hairline, #eee); padding-top: 10px;">
						<button type="button" class="btn-export-pdf btn btn-ghost" data-post-id="<?php echo $post_id_val; ?>" style="width: 100%; font-size: 11px; padding: 6px 12px; display: flex; align-items: center; justify-content: center; gap: 6px;">
							<i class="fa-solid fa-file-pdf" style="color: #C0392B;"></i> Export Dossier PDF
						</button>
					</div>
					<?php endif; ?>
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
            btn.innerHTML = '<i class="fa-solid fa-file-pdf" style="color: #C0392B;"></i> Export Dossier PDF';
            if (res.success && res.data && res.data.download_url) {
                window.location.href = res.data.download_url;
            } else {
                alert(res.data && res.data.message ? res.data.message : 'Unable to generate PDF. Authorization required.');
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-file-pdf" style="color: #C0392B;"></i> Export Dossier PDF';
            alert('Connection error while generating PDF.');
        });
});
</script>

<?php
get_footer();
