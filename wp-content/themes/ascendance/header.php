<?php
/**
 * Header for the Ascendance Intelligence Platform
 *
 * @package Ascendance
 */

$is_logged_in = is_user_logged_in();
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<script>
	/* Pre-paint theme apply (no FOUC). Modes: light | dark | auto. */
	(function(){
	  var m = localStorage.getItem('as-theme') || 'auto';
	  function resolve(mode){
	    if(mode==='auto') return window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light';
	    return mode;
	  }
	  window.__asApplyTheme=function(mode){
	    localStorage.setItem('as-theme',mode);
	    document.documentElement.dataset.theme=resolve(mode);
	    document.documentElement.dataset.themeMode=mode;
	    
	    var updateButtons = function() {
	      var container = document.querySelector('.as-theme');
	      if (container) {
	        var buttons = container.querySelectorAll('button');
	        buttons.forEach(function(btn) {
	          btn.classList.remove('on');
	        });
	        var activeBtn = container.querySelector('.theme-btn-' + mode);
	        if (activeBtn) {
	          activeBtn.classList.add('on');
	        }
	      }
	    };
	    
	    if (document.readyState === 'loading') {
	      document.addEventListener('DOMContentLoaded', updateButtons);
	    } else {
	      updateButtons();
	    }
	  };
	  document.documentElement.dataset.theme=resolve(m);
	  document.documentElement.dataset.themeMode=m;
	  try{ window.matchMedia('(prefers-color-scheme:dark)').addEventListener('change',function(){
	    if((localStorage.getItem('as-theme')||'auto')==='auto') window.__asApplyTheme('auto');
	  }); }catch(e){}
	  
	  document.addEventListener('DOMContentLoaded', function() {
	    var currentMode = localStorage.getItem('as-theme') || 'auto';
	    var container = document.querySelector('.as-theme');
	    if (container) {
	      var buttons = container.querySelectorAll('button');
	      buttons.forEach(function(btn) {
	        btn.classList.remove('on');
	      });
	      var activeBtn = container.querySelector('.theme-btn-' + currentMode);
	      if (activeBtn) {
	        activeBtn.classList.add('on');
	      }
	    }
	  });
	})();
	</script>
	<link rel="icon" href="<?php echo esc_url( home_url( '/favicon.svg' ) ); ?>" type="image/svg+xml" />
	<link rel="shortcut icon" href="<?php echo esc_url( home_url( '/favicon.svg' ) ); ?>" type="image/svg+xml" />
	<link rel="manifest" href="<?php echo esc_url( home_url( '/site.webmanifest' ) ); ?>" />
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="as-app site min-h-screen flex flex-col">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'ascendance' ); ?></a>

	<!-- Masthead -->
	<header id="masthead" class="as-masthead">
		<div class="as-topbar">
			<!-- Left: Edition Info -->
			<div class="as-edition">
				<div class="as-lang">
					<?php
					if ( function_exists( 'pll_the_languages' ) ) {
						$languages = pll_the_languages( array( 'raw' => 1 ) );
						if ( ! empty( $languages ) && is_array( $languages ) ) {
							// Sort languages by order or slug (e.g., EN before FR)
							uasort( $languages, function( $a, $b ) {
								$ord_a = isset( $a['order'] ) ? (int) $a['order'] : 0;
								$ord_b = isset( $b['order'] ) ? (int) $b['order'] : 0;
								if ( $ord_a !== $ord_b ) {
									return $ord_a - $ord_b;
								}
								return strcmp( $a['slug'], $b['slug'] );
							});
							$output_langs = array();
							foreach ( $languages as $lang ) {
								$slug       = strtoupper( $lang['slug'] );
								$is_current = ! empty( $lang['current_lang'] );
								$class      = $is_current ? 'class="on"' : '';
								if ( $is_current ) {
									$output_langs[] = '<span ' . $class . '>' . esc_html( $slug ) . '</span>';
								} else {
									$output_langs[] = '<a href="' . esc_url( $lang['url'] ) . '" ' . $class . '>' . esc_html( $slug ) . '</a>';
								}
							}
							echo implode( '<span class="as-lang-slash">/</span>', $output_langs );
						} else {
							echo '<span class="on">EN</span><span class="as-lang-slash">/</span><a href="#">FR</a>';
						}
					} else {
						echo '<span class="on">EN</span><span class="as-lang-slash">/</span><a href="#">FR</a>';
					}
					?>
				</div>
				<a class="as-edtext" href="<?php echo esc_url( home_url( '/latest/' ) ); ?>" style="text-decoration:none; color:inherit;">
					<span class="as-edtext-top">
						<?php echo 'Edition of ' . date_i18n( 'l j F Y' ); ?>
						<svg class="as-navchev" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
					</span>
					<span class="as-edtext-sub">Browse the edition</span>
				</a>
			</div>

			<!-- Center: Brand Lockup -->
			<a class="as-logo as-lockup" href="<?php echo esc_url( home_url( '/' ) ); ?>" style="--ll:19px;">
				<span class="ll-row"><i class="ll-box ll-a">A</i><i class="ll-word">SCENDANCE</i></span>
				<span class="ll-row"><i class="ll-box ll-s">S</i><i class="ll-word">TRATEGIES</i></span>
				<i class="ll-tag">The Art of Strategic Positioning</i>
			</a>

			<!-- Right: Actions -->
			<div class="as-actions">
				<a class="as-action as-advisory" href="<?php echo esc_url( home_url( '/advisory/' ) ); ?>">Advisory</a>
				<span class="as-actions-div"></span>
				
				<!-- Theme Switcher -->
				<div class="as-theme" aria-label="Theme switcher">
					<button type="button" title="Light theme" onclick="window.__asApplyTheme('light')" class="theme-btn-light">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M20 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
					</button>
					<button type="button" title="Auto (system)" onclick="window.__asApplyTheme('auto')" class="theme-btn-auto">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
					</button>
					<button type="button" title="Dark theme" onclick="window.__asApplyTheme('dark')" class="theme-btn-dark">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
					</button>
				</div>
				<span class="as-actions-div"></span>

				<a class="as-action" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>" style="white-space:nowrap;"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg> The Brief</a>

				<?php if ( $is_logged_in ) : ?>
					<a class="as-action" href="<?php echo esc_url( home_url( '/account/' ) ); ?>">
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
						Account
					</a>
					<a class="as-ghost sm" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>">Log out</a>
				<?php else : ?>
					<a class="as-action" href="<?php echo esc_url( home_url( '/login/' ) ); ?>" style="white-space:nowrap;"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> Sign in</a>
					<a class="as-subscribe" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>">Subscribe</a>
				<?php endif; ?>
			</div>
		</div>

		<!-- Nav Row -->
		<?php
		/* Fetch latest brief for Briefs mega panel featured card */
		$mm_brief = new WP_Query( array(
			'post_type'      => 'brief',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );
		?>
		<nav class="as-nav" id="as-nav">
			<button class="as-burger" aria-label="Toggle navigation" id="as-burger-btn">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
			</button>
			<div class="as-navgroup as-navgroup-primary" id="as-navgroup">

				<!-- ===== LATEST MEGA ===== -->
				<div class="as-megawrap" id="mmw-latest">
					<a href="<?php echo esc_url( home_url( '/latest/' ) ); ?>" class="as-megatrig <?php echo ( is_page( 'latest' ) || is_home() ) ? 'active' : ''; ?>" aria-haspopup="true" aria-expanded="false">
						Latest
						<svg class="as-navchev" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
					</a>
					<div class="as-megapanel" role="region" aria-label="Latest navigation">
						<div class="as-megapanel-inner">
							<style>
								.mm-cols-4 { display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:0; }
								@media (max-width:960px) { .mm-cols-4 { grid-template-columns:1fr; } }
							</style>
							<div class="mm-cols mm-cols-4">

								<div class="mm-col">
									<span class="mm-head">Recent Intelligence</span>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/latest/' ) ); ?>">All Latest Releases</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/latest/?type=brief' ) ); ?>">Latest Briefs</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/latest/?type=update' ) ); ?>">Latest Updates</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/latest/?type=dossier' ) ); ?>">Latest Dossiers</a>
								</div>

								<div class="mm-col">
									<span class="mm-head">Core Topics</span>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/intelligence/?topic=geopolitics' ) ); ?>">Geopolitics</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/intelligence/?topic=economics' ) ); ?>">Economics &amp; Markets</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/intelligence/?topic=security' ) ); ?>">Security</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/intelligence/?topic=infrastructure' ) ); ?>">Infrastructure</a>
								</div>

								<div class="mm-col">
									<span class="mm-head">Regions</span>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/intelligence/?region=drc' ) ); ?>">DR Congo</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/intelligence/?region=united-states' ) ); ?>">United States</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/intelligence/?region=angola-2' ) ); ?>">Angola</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/intelligence/?region=zambia-2' ) ); ?>">Zambia</a>
								</div>

								<div class="mm-col">
									<span class="mm-head">Featured</span>
									<?php if ( $mm_brief->have_posts() ) : $mm_brief->the_post(); ?>
									<a class="mm-featured" href="<?php the_permalink(); ?>">
										<div class="mm-featured-kicker">SPA Brief &middot; <?php echo esc_html( get_the_date( 'd M Y' ) ); ?></div>
										<div class="mm-featured-title"><?php echo esc_html( get_the_title() ); ?></div>
										<div class="mm-featured-meta">Read full briefing &rarr;</div>
									</a>
									<?php wp_reset_postdata(); else : ?>
									<a class="mm-featured" href="<?php echo esc_url( home_url( '/#briefings' ) ); ?>">
										<div class="mm-featured-kicker">SPA Brief &middot; Intelligence Portal</div>
										<div class="mm-featured-title">US-DRC Strategic Partnership: Article VII Framework Analysis</div>
										<div class="mm-featured-meta">Read full briefing &rarr;</div>
									</a>
									<?php endif; ?>
								</div>

							</div>
						</div>
					</div>
				</div>

				<!-- ===== BRIEFS MEGA ===== -->
				<div class="as-megawrap" id="mmw-briefs">
					<a href="<?php echo esc_url( get_post_type_archive_link( 'brief' ) ?: home_url( '/briefs/' ) ); ?>" class="as-megatrig" aria-haspopup="true" aria-expanded="false">
						Briefs
						<svg class="as-navchev" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
					</a>
					<div class="as-megapanel" role="region" aria-label="Briefs navigation">
						<div class="as-megapanel-inner">
							<div class="mm-cols mm-cols-3">

								<div class="mm-col">
									<span class="mm-head">SPA Briefs</span>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/briefs/' ) ); ?>">All SPA Briefs</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/briefs/' ) ); ?>">Latest Releases</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>">Subscriber Archive</a>
									<a class="mm-link mm-link-sub" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>">
										<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
										Subscribers only
									</a>
								</div>

								<div class="mm-col">
									<span class="mm-head">Intelligence Scope</span>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/us-drc-partnership/' ) ); ?>">Washington Accords</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/sar-registry/' ) ); ?>">Strategic Asset Reserve</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/lobito-corridor/' ) ); ?>">Lobito Corridor</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/regulatory-reform-tracker/' ) ); ?>">Governance &amp; Reform</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/drc-sovereign-rating/' ) ); ?>">DRC Sovereign Rating</a>
								</div>

								<div class="mm-col">
									<span class="mm-head">Featured Brief</span>
									<?php if ( $mm_brief->have_posts() ) : $mm_brief->the_post(); ?>
									<a class="mm-featured" href="<?php the_permalink(); ?>">
										<div class="mm-featured-kicker">SPA Brief &middot; <?php echo esc_html( get_the_date( 'd M Y' ) ); ?></div>
										<div class="mm-featured-title"><?php echo esc_html( get_the_title() ); ?></div>
										<div class="mm-featured-meta">Read full briefing &rarr;</div>
									</a>
									<?php wp_reset_postdata(); else : ?>
									<a class="mm-featured" href="<?php echo esc_url( home_url( '/#briefings' ) ); ?>">
										<div class="mm-featured-kicker">SPA Brief &middot; Intelligence Portal</div>
										<div class="mm-featured-title">US-DRC Strategic Partnership: Article VII Framework Analysis</div>
										<div class="mm-featured-meta">Read full briefing &rarr;</div>
									</a>
									<?php endif; ?>
								</div>

							</div>
						</div>
					</div>
				</div>

				<!-- ===== DOSSIERS MEGA ===== -->
				<div class="as-megawrap" id="mmw-dossiers">
					<a href="<?php echo esc_url( get_post_type_archive_link( 'dossier' ) ?: home_url( '/latest/' ) ); ?>" class="as-megatrig" aria-haspopup="true" aria-expanded="false">
						Dossiers
						<svg class="as-navchev" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
					</a>
					<div class="as-megapanel" role="region" aria-label="Dossiers navigation">
						<div class="as-megapanel-inner">
							<div class="mm-cols mm-cols-3">

								<div class="mm-col">
									<span class="mm-head">Flagship Dossiers</span>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/dossiers/' ) ); ?>">All Dossiers</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/us-drc-partnership/' ) ); ?>">US-DRC Partnership Hub</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/lobito-corridor/' ) ); ?>">Lobito Corridor Dossier</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/drc-sovereign-rating/' ) ); ?>">DRC Sovereign Rating</a>
								</div>

								<div class="mm-col">
									<span class="mm-head">Registers &amp; Trackers</span>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/sar-registry/' ) ); ?>">Strategic Asset Reserve Registry</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/cami-registry/' ) ); ?>">CAMI Registry</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/regulatory-reform-tracker/' ) ); ?>">Regulatory Reform Tracker</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/spa-glossary/' ) ); ?>">SPA Glossary</a>
								</div>

								<div class="mm-col">
									<span class="mm-head">About the Platform</span>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/methodology/' ) ); ?>">Methodology &amp; Disclosures</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/advisory/' ) ); ?>">Advisory Rail</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>">Subscribe for Full Access</a>
								</div>

							</div>
						</div>
					</div>
				</div>

				<!-- ===== UPDATES MEGA ===== -->
				<div class="as-megawrap" id="mmw-updates">
					<a href="<?php echo esc_url( get_post_type_archive_link( 'update' ) ?: home_url( '/updates/' ) ); ?>" class="as-megatrig" aria-haspopup="true" aria-expanded="false">
						Updates
						<svg class="as-navchev" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
					</a>
					<div class="as-megapanel" role="region" aria-label="Updates navigation">
						<div class="as-megapanel-inner">
							<div class="mm-cols mm-cols-2">

								<div class="mm-col">
									<span class="mm-head">Regional Updates</span>
									<a class="mm-link" href="<?php echo esc_url( get_post_type_archive_link( 'update' ) ?: home_url( '/updates/' ) ); ?>">US-DRC Partnership</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/lobito-corridor/' ) ); ?>">Corridor Updates</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/regulatory-reform-tracker/' ) ); ?>">Governance Updates</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/sar-registry/' ) ); ?>">SAR Registry Updates</a>
								</div>

								<div class="mm-col">
									<span class="mm-head">Partnership Hubs</span>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/us-drc-partnership/' ) ); ?>">Washington Accords Hub</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/sar-registry/' ) ); ?>">SAR Registry</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/lobito-corridor/' ) ); ?>">Lobito Corridor Dossier</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/advisory/' ) ); ?>">Advisory Desk</a>
								</div>

							</div>
						</div>
					</div>
				</div>

				<!-- ===== TOPICS MEGA ===== -->
				<div class="as-megawrap" id="mmw-topics">
					<a href="<?php echo esc_url( home_url( '/intelligence/' ) ); ?>" class="as-megatrig" aria-haspopup="true" aria-expanded="false">
						Topics
						<svg class="as-navchev" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
					</a>
					<div class="as-megapanel" role="region" aria-label="Topics navigation">
						<div class="as-megapanel-inner">
							<div class="mm-cols mm-cols-3">

								<div class="mm-col">
									<span class="mm-head">Geopolitics</span>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/us-drc-partnership/' ) ); ?>">Washington Accords</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/regulatory-reform-tracker/' ) ); ?>">Governance</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/lobito-corridor/' ) ); ?>">Corridor</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/drc-sovereign-rating/' ) ); ?>">DRC Sovereign Rating</a>
								</div>

								<div class="mm-col">
									<span class="mm-head">Economics &amp; Markets</span>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/sar-registry/' ) ); ?>">Strategic Asset Reserve</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/advisory/' ) ); ?>">Investment Intelligence</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/sar-registry/' ) ); ?>">Mining &amp; Minerals</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/cami-registry/' ) ); ?>">CAMI Registry</a>
								</div>

								<div class="mm-col">
									<span class="mm-head">Reference Desk</span>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/spa-glossary/' ) ); ?>">SPA Glossary</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/advisory/' ) ); ?>">Advisory</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/methodology/' ) ); ?>">Methodology</a>
									<a class="mm-link" href="<?php echo esc_url( home_url( '/latest/' ) ); ?>">Latest Intelligence</a>
								</div>

							</div>
						</div>
					</div>
				</div>

				<span class="as-nav-sep"></span>

				<a href="<?php echo esc_url( home_url( '/us-drc-partnership/' ) ); ?>">Washington Accords</a>
				<a href="<?php echo esc_url( home_url( '/sar-registry/' ) ); ?>">Strategic Asset Reserve</a>
				<a href="<?php echo esc_url( home_url( '/lobito-corridor/' ) ); ?>">Corridor</a>
				<a href="<?php echo esc_url( home_url( '/regulatory-reform-tracker/' ) ); ?>">Governance</a>

				<button type="button" class="as-nav-search" title="Search" onclick="var sf = document.querySelector('.search-field'); if(sf){ sf.focus(); } else { window.location.href='<?php echo esc_url( home_url( '/?s=' ) ); ?>'; }" style="margin-left:auto; background:none; border:none; color:inherit; cursor:pointer; display:flex; align-items:center; padding:0 4px;">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
				</button>

			</div>

			<!-- Mobile Actions (only visible on mobile) -->
			<div class="as-actions-mobile-nav">
				<a class="as-action" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>" aria-label="The Brief"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg></a>

				<?php if ( $is_logged_in ) : ?>
					<a class="as-action" href="<?php echo esc_url( home_url( '/account/' ) ); ?>" aria-label="Account">
						<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
					</a>
				<?php else : ?>
					<a class="as-action" href="<?php echo esc_url( home_url( '/login/' ) ); ?>" aria-label="Sign in"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></a>
				<?php endif; ?>

				<a class="as-subscribe" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>">Subscribe</a>
			</div>
		</nav>

		<!-- Mega Menu JS -->
		<script>
		(function(){
			var wraps   = document.querySelectorAll('.as-megawrap');
			var timers  = [];
			var chevs   = [];

			wraps.forEach(function(wrap, i){
				timers[i] = null;
				var panel = wrap.querySelector('.as-megapanel');
				var trig  = wrap.querySelector('.as-megatrig');
				var chev  = trig ? trig.querySelector('.as-navchev') : null;
				if(!panel || !trig) return;

				function openPanel(){
					closeAll();
					panel.classList.add('open');
					trig.setAttribute('aria-expanded','true');
					if(chev) chev.style.transform = 'rotate(180deg)';
				}
				function closePanel(){
					panel.classList.remove('open');
					trig.setAttribute('aria-expanded','false');
					if(chev) chev.style.transform = '';
				}

				/* Hover intent — desktop */
				wrap.addEventListener('mouseenter', function(){
					clearTimeout(timers[i]);
					timers[i] = setTimeout(openPanel, 80);
				});
				wrap.addEventListener('mouseleave', function(){
					clearTimeout(timers[i]);
					timers[i] = setTimeout(closePanel, 150);
				});

				/* Click — toggle (mobile / keyboard) */
				trig.addEventListener('click', function(e){
					e.preventDefault();
					panel.classList.contains('open') ? closePanel() : openPanel();
				});

				/* Track for closeAll */
				chevs[i] = { panel:panel, trig:trig, chev:chev, timer:i };
			});

			function closeAll(){
				wraps.forEach(function(wrap, i){
					clearTimeout(timers[i]);
					var p = wrap.querySelector('.as-megapanel');
					var t = wrap.querySelector('.as-megatrig');
					var c = t ? t.querySelector('.as-navchev') : null;
					if(p){ p.classList.remove('open'); }
					if(t){ t.setAttribute('aria-expanded','false'); }
					if(c){ c.style.transform = ''; }
				});
			}

			/* ESC to close */
			document.addEventListener('keydown', function(e){
				if(e.key === 'Escape') closeAll();
			});

			/* Click outside to close */
			document.addEventListener('click', function(e){
				var inside = false;
				wraps.forEach(function(wrap){ if(wrap.contains(e.target)) inside = true; });
				if(!inside) closeAll();
			});

			/* Mobile Burger Menu Toggle */
			var burger = document.getElementById('as-burger-btn');
			var navgroup = document.getElementById('as-navgroup');
			if(burger && navgroup) {
				burger.addEventListener('click', function(e) {
					e.preventDefault();
					navgroup.classList.toggle('open');
				});
				/* Close mobile menu when clicking outside */
				document.addEventListener('click', function(e){
					if(navgroup.classList.contains('open') && !burger.contains(e.target) && !navgroup.contains(e.target)) {
						navgroup.classList.remove('open');
					}
				});
			}
		})();
		</script>
	</header>

	<div id="content" class="site-content flex-grow">
