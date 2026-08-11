<?php
/**
 * Footer for the Ascendance Intelligence Platform
 * Adopts full layout from as/project/ui_kits/marketing/index.html
 *
 * @package Ascendance
 */
?>
	</div><!-- #content -->

	<!-- newsletter / contact band -->
	<section class="section dark" id="contact"><div class="wrap">
		<div class="m-contact">
			<div class="lead">
				<h2><?php esc_html_e( 'Stay informed on the US-DRC Strategic Partnership.', 'ascendance' ); ?></h2>
				<p><?php esc_html_e( 'A monthly briefing on Strategic Asset Reserve developments, governance-reform implementation, political-risk analysis and Lobito Corridor progress. Or schedule a confidential consultation with the desk.', 'ascendance' ); ?></p>
			</div>
			<div class="acts">
				<a class="btn-light" href="<?php echo esc_url( home_url( '/newsletter/' ) ); ?>"><?php esc_html_e( 'Subscribe to the briefing', 'ascendance' ); ?></a>
				<a class="btn-outline" href="mailto:contact@ascendance-strategies.com"><?php esc_html_e( 'Schedule a consultation', 'ascendance' ); ?></a>
			</div>
		</div>
	</div></section>

	<footer class="m-footer">
		<div class="wrap">
			<div class="m-foot-top">
				<div>
					<a class="as-lockup on-dark" href="<?php echo esc_url( home_url( '/' ) ); ?>" style="--ll:21px;">
						<span class="ll-row"><i class="ll-box ll-a">A</i><i class="ll-word">SCENDANCE</i></span>
						<span class="ll-row"><i class="ll-box ll-s">S</i><i class="ll-word">TRATEGIES</i></span>
						<i class="ll-tag">The Art of Strategic Positioning</i>
					</a>
					<p><?php esc_html_e( 'Paris-based international firm focused exclusively on the US-DRC Strategic Partnership Agreement.', 'ascendance' ); ?></p>
				</div>
				<div class="m-foot-cols">
					<div>
						<h6><?php esc_html_e( 'Content', 'ascendance' ); ?></h6>
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Latest', 'ascendance' ); ?></a>
						<a href="<?php echo esc_url( home_url( '/us-drc-partnership/' ) ); ?>"><?php esc_html_e( 'Explainers', 'ascendance' ); ?></a>
						<a href="<?php echo esc_url( home_url( '/intelligence/' ) ); ?>"><?php esc_html_e( 'Analysis', 'ascendance' ); ?></a>
						<a href="<?php echo esc_url( get_post_type_archive_link( 'brief' ) ?: home_url( '/briefs/' ) ); ?>"><?php esc_html_e( 'Dossiers', 'ascendance' ); ?></a>
						<a href="<?php echo esc_url( home_url( '/cami-registry/' ) ); ?>"><?php esc_html_e( 'Registers', 'ascendance' ); ?></a>
						<a href="<?php echo esc_url( home_url( '/newsletter/' ) ); ?>"><?php esc_html_e( 'Briefings', 'ascendance' ); ?></a>
					</div>
					<div>
						<h6><?php esc_html_e( 'Coverage', 'ascendance' ); ?></h6>
						<a href="<?php echo esc_url( home_url( '/intelligence/' ) ); ?>"><?php esc_html_e( 'Energy', 'ascendance' ); ?></a>
						<a href="<?php echo esc_url( home_url( '/intelligence/' ) ); ?>"><?php esc_html_e( 'Critical Minerals', 'ascendance' ); ?></a>
						<a href="<?php echo esc_url( home_url( '/lobito-corridor/' ) ); ?>"><?php esc_html_e( 'Lobito Corridor', 'ascendance' ); ?></a>
						<a href="<?php echo esc_url( home_url( '/regulatory-reform-tracker/' ) ); ?>"><?php esc_html_e( 'Governance', 'ascendance' ); ?></a>
						<a href="<?php echo esc_url( home_url( '/intelligence/' ) ); ?>"><?php esc_html_e( 'Geopolitics', 'ascendance' ); ?></a>
						<a href="<?php echo esc_url( home_url( '/intelligence/' ) ); ?>"><?php esc_html_e( 'Security', 'ascendance' ); ?></a>
					</div>
					<div>
						<h6><?php esc_html_e( 'Firm', 'ascendance' ); ?></h6>
						<a href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'About', 'ascendance' ); ?></a>
						<a href="<?php echo esc_url( home_url( '/methodology/' ) ); ?>"><?php esc_html_e( 'Methodology', 'ascendance' ); ?></a>
						<a href="<?php echo esc_url( home_url( '/services/' ) ); ?>"><?php esc_html_e( 'Services', 'ascendance' ); ?></a>
						<a href="<?php echo esc_url( home_url( '/industries/' ) ); ?>"><?php esc_html_e( 'Industries', 'ascendance' ); ?></a>
						<a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>"><?php esc_html_e( 'FAQ', 'ascendance' ); ?></a>
						<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact', 'ascendance' ); ?></a>
					</div>
					<div>
						<h6><?php esc_html_e( 'Access', 'ascendance' ); ?></h6>
						<a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>"><?php esc_html_e( 'Subscribe', 'ascendance' ); ?></a>
						<a href="<?php echo esc_url( home_url( '/login/' ) ); ?>"><?php esc_html_e( 'Log in', 'ascendance' ); ?></a>
						<a href="<?php echo esc_url( home_url( '/account/' ) ); ?>"><?php esc_html_e( 'Manage account', 'ascendance' ); ?></a>
						<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Request invitation', 'ascendance' ); ?></a>
					</div>
					<div>
						<h6><?php esc_html_e( 'Follow', 'ascendance' ); ?></h6>
						<a href="https://x.com/AscenStrategies" target="_blank" rel="noopener"><?php esc_html_e( 'Twitter', 'ascendance' ); ?></a>
						<a href="https://www.facebook.com/ascendancestrategies" target="_blank" rel="noopener"><?php esc_html_e( 'Facebook', 'ascendance' ); ?></a>
						<a href="https://www.linkedin.com/company/98380854" target="_blank" rel="noopener"><?php esc_html_e( 'LinkedIn', 'ascendance' ); ?></a>
						<a href="#" target="_blank" rel="noopener"><?php esc_html_e( 'Instagram', 'ascendance' ); ?></a>
						<a href="<?php echo esc_url( home_url( '/newsletter/' ) ); ?>"><?php esc_html_e( 'Newsletter', 'ascendance' ); ?></a>
					</div>
				</div>
			</div>

			<div class="m-foot-herald">
				<a href="https://congo-herald.com" target="_blank" rel="noopener"><?php esc_html_e( 'Congo Herald (français)', 'ascendance' ); ?></a>
			</div>
			<div class="m-foot-legal">
				<span><?php esc_html_e( 'Ascendance Strategies is a brand of Expats Paris SAS', 'ascendance' ); ?></span>
				<span><?php esc_html_e( "15 Allée d'Andrezieux, 75018 Paris, France", 'ascendance' ); ?></span>
				<span>SIREN 830 082 848 &nbsp;|&nbsp; contact@expats-paris.com</span>
				<span class="m-legal-links">
					<a href="<?php echo esc_url( home_url( '/legal/#mentions-legales' ) ); ?>"><?php esc_html_e( 'Mentions légales', 'ascendance' ); ?></a> &nbsp;|&nbsp; 
					<a href="<?php echo esc_url( home_url( '/legal/#privacy' ) ); ?>"><?php esc_html_e( 'Privacy', 'ascendance' ); ?></a> &nbsp;|&nbsp; 
					<a href="<?php echo esc_url( home_url( '/legal/#terms-of-use' ) ); ?>"><?php esc_html_e( 'Terms of Use', 'ascendance' ); ?></a> &nbsp;|&nbsp; 
					<a href="<?php echo esc_url( home_url( '/legal/#terms-of-service' ) ); ?>"><?php esc_html_e( 'Terms of Service', 'ascendance' ); ?></a>
				</span>
			</div>
			<div class="m-foot-base">
				<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> Ascendance Strategies. All rights reserved.</span>
				
			</div>
		</div>
	</footer>

</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>