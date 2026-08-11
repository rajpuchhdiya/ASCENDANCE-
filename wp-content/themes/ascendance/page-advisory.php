<?php
/**
 * Template Name: Advisory Homepage
 *
 * @package Ascendance
 */

get_header();
?>

<main id="top">

<!-- Hero Section -->
<section class="m-hero">
	<div class="wrap">
		<div>
			<span class="kicker">US-DRC Strategic Partnership Agreement</span>
			<h1>Championing the US-DRC Strategic Partnership, everywhere.</h1>
			<p>A Paris-based firm guiding partnership success across mining, energy, infrastructure, agriculture, governance and defense.</p>
			<div class="m-actions">
				<a class="btn-primary" href="#contact">Schedule a consultation</a>
				<a class="link-arrow" href="<?php echo esc_url( home_url( '/' ) ); ?>">Read the latest brief &rarr;</a>
			</div>
		</div>
		<aside class="m-dossier" aria-label="Latest intelligence brief">
			<div class="d-top">
				<span class="lbl">&bull; SPA Intelligence Brief</span>
				<span class="num">29 MAY 2026</span>
			</div>
			<div class="d-body">
				<div class="d-kick">This week on the partnership</div>
				<h3 class="d-title">Washington Accords advance as ratification clears the Senate</h3>
				<div class="d-rows">
					<div class="d-row"><span>Washington Accords</span><span>Ratification cleared</span></div>
					<div class="d-row"><span>Governance reform</span><span>On track</span></div>
					<div class="d-row"><span>Lobito Corridor</span><span>Phase II</span></div>
					<div class="d-row"><span>Strategic Asset Reserve</span><span class="d-rating">Active</span></div>
				</div>
			</div>
		</aside>
	</div>
</section>

<!-- Trust Band -->
<div class="m-trust">
	<div class="wrap">
		<span class="lab">We support</span>
		<div class="items">
			<span>Governments</span>
			<span>Corporations</span>
			<span>Investors</span>
			<span>Multilaterals</span>
			<span>Operators</span>
		</div>
	</div>
</div>

<!-- Story & About -->
<section class="section" id="about">
	<div class="wrap">
		<div class="sec-head" style="max-width:780px;">
			<span class="kicker">Our story</span>
			<h2>Bridging public and private interests across the transatlantic&ndash;African corridor.</h2>
			<p>Ascendance Strategies is a Paris-based firm focused on high-level political analysis, strategic advisory and institutional engagement across Africa and transatlantic markets. We support governments, corporations and investors navigating complex political, regulatory and economic environments through intelligence-driven strategy and trusted relationships.</p>
		</div>
		<blockquote class="m-quote">
			"The United States should transition from an aid-focused relationship with Africa to a trade- and investment-focused relationship, favoring partnerships with capable, reliable states committed to opening their markets to US goods and services."
			<cite>U.S. National Security Strategy</cite>
		</blockquote>
		<div class="m-speaking">
			<span class="kicker">Speaking &amp; engagements</span>
			<p>Our principals appear regularly at policy conferences and industry panels on US-Africa trade, critical minerals and the partnership. For speaking requests, <a href="#contact">contact the desk</a>.</p>
		</div>
	</div>
</section>

<!-- Services Section -->
<section class="section" id="services">
	<div class="wrap">
		<div class="sec-head">
			<span class="kicker">Our practices</span>
			<h2>Three practices tailored to SPA decision-makers.</h2>
			<p>Direct counsel for principals operating within or alongside the Strategic Partnership Agreement framework.</p>
		</div>
		<div class="m-services">
			<div class="svc">
				<h3>Political Advisory &amp; Institutional Engagement</h3>
				<p>Navigating executive, legislative and regulatory bodies across Washington, Paris and Kinshasa.</p>
				<ul>
					<li>Stakeholder mapping &amp; policy intelligence</li>
					<li>Bilateral agreement alignment</li>
					<li>High-level protocol &amp; diplomatic strategy</li>
				</ul>
			</div>
			<div class="svc">
				<h3>Strategic Partnership Intelligence</h3>
				<p>Deep-dive monitoring of treaty implementation, asset reserve allocation, and transit corridor projects.</p>
				<ul>
					<li>Strategic Asset Reserve (SAR) monitoring</li>
					<li>Concession &amp; operator due diligence</li>
					<li>Transatlantic trade barrier analysis</li>
				</ul>
			</div>
			<div class="svc">
				<h3>Governance Reform Tracking</h3>
				<p>Evaluating legislative progress, transparency benchmarks, and anti-corruption mandates under the SPA.</p>
				<ul>
					<li>Compliance &amp; reform verification</li>
					<li>Independent sovereign rating metrics</li>
					<li>Regulatory risk scenario planning</li>
				</ul>
			</div>
		</div>
	</div>
</section>

<!-- Contact Band -->
<section class="section dark" id="contact">
	<div class="wrap">
		<div class="m-contact">
			<div class="lead">
				<span class="kicker" style="color:#ec6a6b;">Direct Desk Access</span>
				<h2>Initiate an Advisory Engagement</h2>
				<p>Request an initial consultation with our principals in Paris, Washington, or Kinshasa.</p>
			</div>
			<div class="acts">
				<a class="btn-light" href="mailto:contact@ascendance-strategies.com">Email Advisory Desk</a>
				<a class="btn-outline" href="<?php echo esc_url( home_url( '/advisory/methodology' ) ); ?>">View Methodology</a>
			</div>
		</div>
	</div>
</section>

</main>

<?php
get_footer();
