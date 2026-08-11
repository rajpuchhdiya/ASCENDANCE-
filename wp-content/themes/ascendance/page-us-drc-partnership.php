<?php
/**
 * Template Name: US-DRC Partnership Hub
 *
 * @package Ascendance
 */

global $ascendance_custom_seo_title, $ascendance_custom_seo_meta;
$ascendance_custom_seo_title = 'The US-DRC Strategic Partnership, Explained | Ascendance Strategies';
$ascendance_custom_seo_meta = [
    '<meta name="description" content="The US-DRC Strategic Partnership Agreement, explained: what it is, who runs it, how the Strategic Asset Reserve and the reform clock work, and where investment decisions are actually made.">'
];

get_header();
?>

<div class="as-page-wrap ref-page-wrap">
<style>

@import url('https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,300;0,400;0,600;1,400&family=Barlow:wght@300;400;500;600&family=Roboto:wght@300;400;500&family=JetBrains+Mono:wght@400;500;600&display=swap');
@import url('https://cdn.jsdelivr.net/npm/@fontsource/cooper-hewitt@5.2.5/600.css');@import url('https://cdn.jsdelivr.net/npm/@fontsource/cooper-hewitt@5.2.5/700.css');
:root{
 --serif:'Noto Serif',Georgia,serif;--sans:'Cooper Hewitt','Barlow','Roboto',sans-serif;--mono:'JetBrains Mono','Consolas',monospace;
 --navy:#0F1E35;--navy-deep:#0A1628;--navy-mid:#182D4A;
 --accent:#BC1B1D;--accent-light:#E04B4B;--stone:#56514B;--cream:#F7F4EF;--white:#FFFFFF;--headfg:#0F1E35;
 --text:#1A1A2E;--text-dark:#3A3A4A;--text-muted:#6B6B7A;
 --sub-dark:#8899BB;--muted-dark:#556677;--div-light:#E8E4DC;--div-dark:#2A3A55;
}
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:var(--sans);background:var(--cream);color:var(--text);-webkit-font-smoothing:antialiased;}
.hero{position:relative;background:var(--navy);border-top:3px solid var(--accent);padding:72px 48px 56px;overflow:hidden;}
.hero::before{content:"";position:absolute;inset:0;background-image:repeating-linear-gradient(0deg,rgba(188,27,29,0.025) 0 1px,transparent 1px 60px),repeating-linear-gradient(90deg,rgba(188,27,29,0.025) 0 1px,transparent 1px 60px);pointer-events:none;}
.hero-inner{position:relative;max-width:1080px;margin:0 auto;}
.eyebrow{font-family:var(--sans);font-size:10px;font-weight:600;letter-spacing:0.2em;text-transform:uppercase;color:var(--accent-light);margin-bottom:22px;}
.hero-title{font-family:var(--serif);font-size:clamp(32px,5.2vw,52px);font-weight:400;color:var(--white);line-height:1.12;letter-spacing:-0.02em;max-width:900px;}
.hero-dek{font-family:var(--serif);font-style:italic;font-size:clamp(17px,2.1vw,20px);font-weight:400;color:var(--sub-dark);margin-top:22px;max-width:720px;line-height:1.6;}
.hero-dek strong{color:var(--accent-light);font-style:normal;font-weight:400;}
.hero-meta{display:flex;gap:44px;margin-top:40px;flex-wrap:wrap;padding-top:26px;border-top:1px solid var(--div-dark);}
.meta-i{display:flex;flex-direction:column;gap:4px;}
.meta-l{font-family:var(--sans);font-size:9px;font-weight:600;letter-spacing:0.14em;text-transform:uppercase;color:var(--muted-dark);}
.meta-v{font-family:var(--mono);font-size:13px;color:var(--accent-light);}
.wrap{max-width:1080px;margin:0 auto;padding:0 48px;}
.section{padding:56px 0;border-bottom:1px solid var(--div-light);}
.section:last-of-type{border-bottom:none;}
.sec-eyebrow{font-family:var(--sans);font-size:10px;font-weight:600;letter-spacing:0.18em;text-transform:uppercase;color:var(--accent);margin-bottom:10px;}
.sec-title{font-family:var(--serif);font-size:30px;font-weight:400;color:var(--headfg);line-height:1.2;letter-spacing:-0.01em;}
.sec-sub{font-family:var(--serif);font-style:italic;font-size:16px;color:var(--stone);margin-top:8px;line-height:1.6;max-width:640px;}
.sec-sub a{color:var(--accent);text-decoration:none;border-bottom:1px solid rgba(188,27,29,0.3);}
.prose{font-family:var(--serif);font-size:17px;font-weight:400;line-height:1.75;color:var(--text-dark);max-width:680px;margin-top:26px;}
.prose p{margin-bottom:20px;}
.prose p:first-of-type::first-letter{font-family:var(--serif);float:left;font-size:64px;line-height:0.82;font-weight:600;padding:6px 10px 0 0;color:var(--headfg);letter-spacing:-0.04em;}
.prose strong{font-weight:600;color:var(--headfg);}
.pillars{margin-top:32px;background:var(--navy);border-left:4px solid var(--accent);padding:26px 30px;max-width:820px;}
.pillars-label{font-family:var(--mono);font-size:10px;font-weight:600;letter-spacing:0.14em;text-transform:uppercase;color:var(--accent-light);margin-bottom:14px;}
.pillars p{font-family:var(--serif);font-size:15px;line-height:1.75;color:var(--sub-dark);}
.pillars strong{color:var(--white);font-weight:600;}
.toc{margin-top:30px;border-top:1px solid var(--navy);}
.toc-row{display:flex;align-items:baseline;gap:20px;padding:17px 8px;border-bottom:1px solid var(--div-light);text-decoration:none;transition:background 0.18s ease,padding-left 0.18s ease;position:relative;}
.toc-row::before{content:"";position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--accent);transform:scaleY(0);transform-origin:top;transition:transform 0.22s ease;}
.toc-row:hover,.toc-row:focus-visible{background:var(--white);padding-left:18px;outline:none;}
.toc-row:hover::before,.toc-row:focus-visible::before{transform:scaleY(1);}
.toc-num{font-family:var(--mono);font-size:13px;font-weight:500;color:var(--accent);min-width:30px;flex-shrink:0;font-variant-numeric:tabular-nums;}
.toc-q{font-family:var(--serif);font-size:19px;font-weight:400;color:var(--headfg);line-height:1.35;flex:1;}
.toc-tag{font-family:var(--sans);font-size:9px;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);align-self:center;flex-shrink:0;}
.toc-row.ref .toc-num{color:var(--stone);}
.toc-row.ref .toc-q{font-style:italic;color:var(--stone);}
.mech-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1px;margin-top:30px;background:var(--div-light);border:1px solid var(--div-light);}
.mech-card{background:var(--white);border-left:3px solid var(--accent);padding:24px 26px;}
.mech-art{font-family:var(--mono);font-size:10px;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;color:var(--accent);margin-bottom:9px;}
.mech-title{font-family:var(--serif);font-size:19px;font-weight:400;color:var(--headfg);margin-bottom:10px;}
.mech-body{font-family:var(--serif);font-size:14px;font-weight:400;line-height:1.68;color:var(--text-dark);}
.mech-link{margin-top:12px;font-family:var(--sans);font-size:11px;font-weight:600;letter-spacing:0.04em;}
.mech-link a{color:var(--accent);text-decoration:none;}
.mech-link a:focus-visible{outline:2px solid var(--accent);outline-offset:2px;}
.rail-cta:focus-visible,.toc-row:focus-visible{outline-offset:2px;}
.mech-link a::after{content:" \2192";}
.rail{margin-top:44px;background:var(--navy);border-left:4px solid var(--accent);padding:30px 34px;display:flex;justify-content:space-between;align-items:center;gap:22px;flex-wrap:wrap;}
.rail-eyebrow{font-family:var(--mono);font-size:10px;font-weight:600;letter-spacing:0.14em;text-transform:uppercase;color:var(--accent-light);margin-bottom:8px;}
.rail-text{font-family:var(--serif);font-size:15px;line-height:1.65;color:var(--sub-dark);max-width:600px;}
.rail-cta{font-family:var(--sans);font-size:12px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;padding:13px 26px;background:#fff;color:#0F1E35;text-decoration:none;border:2px solid #fff;white-space:nowrap;transition:background 0.15s,color 0.15s;}
.rail-cta:hover{background:transparent;color:#fff;}
.signoff{background:var(--navy-deep);border-top:2px solid var(--accent);padding:30px 48px;text-align:center;}
.signoff span{font-family:var(--serif);font-style:italic;font-size:12px;color:var(--accent);text-transform:uppercase;letter-spacing:0.2em;}
/* platform chrome */
.pf-head{background:var(--white);border-bottom:1px solid var(--div-light);}
.pf-top{max-width:1080px;margin:0 auto;display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:20px;padding:14px 48px;}
.pf-wm{grid-column:2;font-family:var(--sans);font-weight:700;font-size:19px;letter-spacing:0.06em;color:var(--navy);text-decoration:none;text-align:center;white-space:nowrap;}
.pf-wm b{color:var(--accent);}
.pf-actions{grid-column:3;justify-self:end;display:flex;align-items:center;gap:16px;}
.pf-actions a{font-family:var(--sans);font-weight:600;font-size:13px;color:var(--navy);text-decoration:none;}
.pf-actions a:hover{color:var(--accent);}
.pf-sub{background:var(--accent);color:#fff !important;padding:9px 18px;border-radius:2px;}
.pf-nav{border-top:1px solid var(--div-light);}
.pf-nav-inner{max-width:1080px;margin:0 auto;display:flex;gap:24px;padding:0 48px;}
.pf-nav-inner a{font-family:var(--sans);font-weight:600;font-size:13.5px;color:var(--navy);text-decoration:none;padding:13px 0;border-bottom:2px solid transparent;}
.pf-nav-inner a:hover{color:var(--accent);}
.pf-nav-inner a.on{color:var(--accent);border-bottom-color:var(--accent);}
.pf-foot{background:var(--navy-deep);padding:30px 48px;}
.pf-foot-inner{max-width:1080px;margin:0 auto;display:flex;justify-content:space-between;gap:20px;flex-wrap:wrap;font-family:var(--sans);font-size:12px;color:var(--sub-dark);}
.pf-foot-inner a{color:var(--sub-dark);text-decoration:none;}
.pf-foot-inner a:hover{color:var(--accent-light);}
.pf-nav-inner a:focus-visible,.pf-actions a:focus-visible{outline:2px solid var(--accent);outline-offset:2px;}
@media(max-width:820px){.pf-top{padding:12px 24px;}.pf-nav-inner{padding:0 24px;overflow-x:auto;}.pf-foot{padding:24px 24px;}}
@media(max-width:820px){
 .hero{padding:48px 24px 40px;}.hero-dek{margin-top:16px;}
 .hero-meta{gap:24px 28px;margin-top:30px;}
 .wrap{padding:0 24px;}.section{padding:40px 0;}
 .mech-grid{grid-template-columns:1fr;}.toc-q{font-size:17px;}
 .toc-row{gap:14px;}
 .rail{flex-direction:column;align-items:stretch;}.rail-cta{text-align:center;}
}

/* themeable platform chrome (shares as-theme with the portal) */
:root{--pf-bg:#fff;--pf-text:#0F1E35;--pf-border:#E8E4DC;--pf-muted:#56514B;--pf-foot-bg:#0A1628;--pf-foot-text:#8899BB;--pf-foot-link:#cfd6e2;--pf-foot-border:rgba(255,255,255,0.12);}
:root[data-theme="dark"]{--pf-bg:#17140f;--pf-text:#f3eee4;--pf-border:#322c23;--pf-muted:#98917f;--pf-foot-bg:#0d0b08;--pf-foot-text:#9a9280;--pf-foot-link:#d7d0c2;--pf-foot-border:rgba(243,238,228,0.1);}
.pf-head{background:var(--pf-bg);border-bottom:1px solid var(--pf-border);}
.pf-wm{color:var(--pf-text);} .pf-wm b{color:var(--accent);}
.pf-actions a{color:var(--pf-text);} .pf-actions a:hover{color:var(--accent);}
.pf-sub{background:var(--accent);color:#fff !important;}
.pf-nav{border-top:1px solid var(--pf-border);}
.pf-nav-inner a{color:var(--pf-text);} .pf-nav-inner a:hover{color:var(--accent);}
.pf-nav-inner a.on{color:var(--accent);border-bottom-color:var(--accent);}
.pf-theme{display:inline-flex;border:1px solid var(--pf-border);border-radius:999px;overflow:hidden;}
.pf-theme button{width:30px;height:28px;display:flex;align-items:center;justify-content:center;color:var(--pf-muted);background:none;border:none;cursor:pointer;transition:all .15s;}
.pf-theme button:hover{color:var(--pf-text);}
.pf-theme button.on{background:var(--pf-text);color:var(--pf-bg);}
.pf-theme button:focus-visible{outline:2px solid var(--accent);outline-offset:-2px;}
.pf-foot{background:var(--pf-foot-bg);padding:44px 40px 26px;}
.pf-foot-top{max-width:1080px;margin:0 auto;display:grid;grid-template-columns:1.1fr 2fr;gap:40px;}
.pf-fbrand .pf-wm{color:#fff;}
.pf-fbrand p{font-family:var(--sans);font-size:14px;line-height:1.55;color:var(--pf-foot-text);margin-top:16px;max-width:300px;}
.pf-fcols{display:grid;grid-template-columns:repeat(4,1fr);gap:22px;}
.pf-fcol h6{font-family:var(--sans);font-weight:700;font-size:11px;letter-spacing:0.12em;text-transform:uppercase;color:var(--pf-foot-text);margin:0 0 12px;}
.pf-fcol a{display:block;font-family:var(--sans);font-size:13.5px;color:var(--pf-foot-link);text-decoration:none;padding:4px 0;}
.pf-fcol a:hover{color:var(--accent-light);}
.pf-foot-herald{max-width:1080px;margin:22px auto 0;}
.pf-foot-herald a{font-family:var(--sans);font-weight:600;font-size:13px;color:var(--accent-light);text-decoration:none;}
.pf-foot-legal{max-width:1080px;margin:18px auto 0;padding-top:18px;border-top:1px solid var(--pf-foot-border);display:flex;flex-direction:column;gap:3px;font-family:var(--sans);font-size:12px;color:var(--pf-foot-text);}
.pf-foot-legal a{color:var(--pf-foot-text);}
.pf-foot-legal a:hover{color:var(--accent-light);}
.pf-foot-base{max-width:1080px;margin:14px auto 0;padding-top:14px;border-top:1px solid var(--pf-foot-border);display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;font-family:var(--sans);font-size:12px;color:var(--pf-foot-text);}
@media(max-width:820px){.pf-foot{padding:30px 20px 22px;}.pf-foot-top{grid-template-columns:1fr;gap:24px;}.pf-fcols{grid-template-columns:1fr 1fr;}}
.pf-top{gap:14px;}.pf-actions{gap:13px;}.pf-actions a{white-space:nowrap;}
:root[data-theme="dark"]{--cream:#17140f;--white:#221e17;--text:#f3eee4;--text-dark:#cec7b8;--text-muted:#98917f;--div-light:#322c23;--stone:#9a9280;--headfg:#f3eee4;--navy:#0d0b08;--navy-deep:#08060a;--navy-mid:#1a1610;--sub-dark:#9a9280;--div-dark:#322c23;}

/* AS monogram lockup (canonical brand logo, themeable via page tokens) */
.as-lockup{display:inline-flex;flex-direction:column;gap:3px;text-decoration:none;line-height:1;--ll:20px;}
.as-lockup .ll-row{display:flex;align-items:stretch;}
.as-lockup .ll-box{width:var(--ll);height:var(--ll);background:var(--accent);color:#fff;display:flex;align-items:center;justify-content:center;font-family:var(--font-sans);font-weight:700;font-size:calc(var(--ll)*0.72);line-height:1;}
.as-lockup .ll-s{background:var(--text);color:var(--bg);}
.as-lockup .ll-word{font-family:var(--font-sans);font-weight:700;font-size:var(--ll);letter-spacing:0.005em;color:var(--text);height:var(--ll);display:flex;align-items:center;padding-left:calc(var(--ll)*0.05);}
.as-lockup .ll-tag{font-family:var(--font-sans);font-weight:600;font-size:calc(var(--ll)*0.275);letter-spacing:0.13em;text-transform:uppercase;color:var(--text-sub);margin-top:calc(var(--ll)*0.2);white-space:nowrap;align-self:stretch;text-align:center;}
.pf-top .as-lockup{grid-column:2;justify-self:center;}
.pf-fbrand .as-lockup .ll-word{color:#fff;}
.pf-fbrand .as-lockup .ll-s{background:#fff;color:#0D1626;}
.pf-fbrand .as-lockup .ll-tag{color:var(--pf-foot-text);}

</style>

<div class="hero">
 <div class="hero-inner">
 <div class="eyebrow">Explainer &nbsp;/&nbsp; US-DRC Partnership</div>
 <h1 class="hero-title">The US-DRC Strategic Partnership, Explained</h1>
 <p class="hero-dek">American capital is moving into the Congo's mineral sector faster than most advisers can track. <strong>The distance between what is publicly understood and what is happening on the ground is where investment decisions are made.</strong> This page covers the public framework.</p>
 <div class="hero-meta">
  <div class="meta-i"><span class="meta-l">SPA signed</span><span class="meta-v">2025.12.04</span></div>
  <div class="meta-i"><span class="meta-l">In force</span><span class="meta-v">On signature</span></div>
  <div class="meta-i"><span class="meta-l">Explainers</span><span class="meta-v">11, open</span></div>
  <div class="meta-i"><span class="meta-l">Reviewed</span><span class="meta-v">2026.07</span></div>
 </div>
 </div>
</div>
<div class="wrap">
 <section class="section">
 <div class="sec-eyebrow">Start here</div>
 <h2 class="sec-title">The agreement in brief</h2>
 <div class="prose">
  <p>The Democratic Republic of the Congo holds roughly three-quarters of the world's mined cobalt and is the second-largest copper producer on earth. For two decades, Chinese firms acquired controlling positions across its largest mines. By the time the US-DRC Strategic Partnership was signed, they held an estimated 80 percent of Congolese mining output.</p>
  <p>The SPA is the US response. It does not hand American companies existing assets. It builds a <strong>structured preference</strong>: when Congolese state-linked assets become available, US and allied investors get first access, favourable fiscal treatment, and the backing of US government financing institutions. The whole design is a twenty-year attempt to change who controls where the metal goes.</p>
 </div>
 <div class="pillars">
  <div class="pillars-label">Four cooperation pillars :: Article III</div>
  <p><strong>Economic:</strong> critical minerals, energy, infrastructure, technology, beneficiation. <strong>Security and defence:</strong> stability and state authority across the DRC. <strong>Scientific and educational:</strong> exchanges, training, capacity. <strong>Institutional and governance:</strong> judicial reform, anti-corruption, public administration. The economic pillar is the one investors watch. The other three shape the environment every transaction lands in.</p>
 </div>
 </section>
 <section class="section">
 <div class="sec-eyebrow">The full set</div>
 <h2 class="sec-title">Eleven explainers</h2>
 <p class="sec-sub">Each answers a single question against the treaty text. Open to read, no registration.</p>
 <div class="toc">
  <a class="toc-row" href="<?php echo esc_url( home_url( '/what-does-the-us-drc-spa-cover/' ) ); ?>"><span class="toc-num">01</span><span class="toc-q">What does the SPA cover?</span><span class="toc-tag">Scope</span></a>
  <a class="toc-row" href="<?php echo esc_url( home_url( '/who-signed-us-drc-strategic-partnership-key-players/' ) ); ?>"><span class="toc-num">02</span><span class="toc-q">Who signed it, and who are the key players?</span><span class="toc-tag">Actors</span></a>
  <a class="toc-row" href="<?php echo esc_url( home_url( '/how-the-us-drc-spa-differs-from-china/' ) ); ?>"><span class="toc-num">03</span><span class="toc-q">How is it different from China's approach?</span><span class="toc-tag">Geopolitics</span></a>
  <a class="toc-row" href="<?php echo esc_url( home_url( '/what-is-the-strategic-asset-reserve-sar/' ) ); ?>"><span class="toc-num">04</span><span class="toc-q">What is the Strategic Asset Reserve?</span><span class="toc-tag">Mechanism</span></a>
  <a class="toc-row" href="<?php echo esc_url( home_url( '/what-is-a-qualifying-strategic-project-qsp/' ) ); ?>"><span class="toc-num">05</span><span class="toc-q">What is a Qualifying Strategic Project?</span><span class="toc-tag">Mechanism</span></a>
  <a class="toc-row" href="<?php echo esc_url( home_url( '/what-does-article-xii-require-us-drc-spa/' ) ); ?>"><span class="toc-num">06</span><span class="toc-q">What does Article XII require, and how does the reform clock work?</span><span class="toc-tag">Reform</span></a>
  <a class="toc-row" href="<?php echo esc_url( home_url( '/what-is-the-joint-steering-committee-jsc/' ) ); ?>"><span class="toc-num">07</span><span class="toc-q">What is the Joint Steering Committee?</span><span class="toc-tag">Governance</span></a>
  <a class="toc-row" href="<?php echo esc_url( home_url( '/what-is-the-lobito-corridor/' ) ); ?>"><span class="toc-num">08</span><span class="toc-q">What is the Lobito Corridor, and why does the SPA depend on it?</span><span class="toc-tag">Infrastructure</span></a>
  <a class="toc-row" href="<?php echo esc_url( home_url( '/where-is-the-money-us-drc-strategic-partnership/' ) ); ?>"><span class="toc-num">09</span><span class="toc-q">Where is the money in the SPA?</span><span class="toc-tag">Finance</span></a>
  <a class="toc-row" href="<?php echo esc_url( home_url( '/how-is-the-us-drc-spa-enforced/' ) ); ?>"><span class="toc-num">10</span><span class="toc-q">How is the SPA enforced?</span><span class="toc-tag">Enforcement</span></a>
  <a class="toc-row ref" href="<?php echo esc_url( home_url( '/spa-glossary/' ) ); ?>"><span class="toc-num">REF</span><span class="toc-q">The SPA Glossary, 42 terms defined</span><span class="toc-tag">Register</span></a>
  <a class="toc-row ref" href="<?php echo esc_url( home_url( '/cami-registry/' ) ); ?>"><span class="toc-num">REF</span><span class="toc-q">CAMI Registry, 3,448 mining titles</span><span class="toc-tag">Register</span></a>
  <a class="toc-row ref" href="<?php echo esc_url( home_url( '/regulatory-reform-tracker/' ) ); ?>"><span class="toc-num">REF</span><span class="toc-q">Regulatory Reform Tracker, 29 obligations</span><span class="toc-tag">Tracker</span></a>
  <a class="toc-row ref" href="<?php echo esc_url( home_url( '/sar-registry/' ) ); ?>"><span class="toc-num">REF</span><span class="toc-q">SAR Registry, 13 public designations</span><span class="toc-tag">Register</span></a>
  <a class="toc-row ref" href="<?php echo esc_url( home_url( '/drc-sovereign-rating/' ) ); ?>"><span class="toc-num">REF</span><span class="toc-q">DRC Sovereign &amp; Institutional Rating</span><span class="toc-tag">Rating</span></a>
 </div>
 </section>
 <section class="section">
 <div class="sec-eyebrow">The architecture</div>
 <h2 class="sec-title">Six mechanisms that carry the weight</h2>
 <p class="sec-sub">The investment machinery in brief. Each links to its full explainer.</p>
 <div class="mech-grid">
  <div class="mech-card"><div class="mech-art">Article VII</div><div class="mech-title">Strategic Asset Reserve</div><div class="mech-body">The Congo's designated list of critical mineral assets reserved for US-first access. A nine-month right of first offer, negotiation window renewable once. Allied investors follow. Strategic rivals permanently excluded. A living list, built to grow.</div><div class="mech-link"><a href="<?php echo esc_url( home_url( '/what-is-the-strategic-asset-reserve-sar/' ) ); ?>">Read the explainer</a></div></div>
  <div class="mech-card"><div class="mech-art">Article VI</div><div class="mech-title">Joint Steering Committee</div><div class="mech-body">The bilateral body that runs implementation. Five officials per side, meeting at least twice a year, deciding by consensus. It maintains the reserve, reviews QSP notifications, tracks the reforms. Inaugural meeting February 5, 2026.</div><div class="mech-link"><a href="<?php echo esc_url( home_url( '/what-is-the-joint-steering-committee-jsc/' ) ); ?>">Read the explainer</a></div></div>
  <div class="mech-card"><div class="mech-art">Article VIII</div><div class="mech-title">Qualifying Strategic Projects</div><div class="mech-body">Projects meeting the ownership, control, and offtake tests gain the SPA's benefits. Non-aligned ownership caps decline from 40 percent to 10 over twenty years. The clock that reshapes the sector's ownership lives here.</div><div class="mech-link"><a href="<?php echo esc_url( home_url( '/what-is-a-qualifying-strategic-project-qsp/' ) ); ?>">Read the explainer</a></div></div>
  <div class="mech-card"><div class="mech-art">Article IX</div><div class="mech-title">Lobito Corridor</div><div class="mech-body">The designated export spine. The Congo commits to route at least 50 percent of copper, 90 percent of zinc, and 30 percent of cobalt state volumes west through it within five years. The Angola segment runs. The Congolese segment is under tender.</div><div class="mech-link"><a href="<?php echo esc_url( home_url( '/what-is-the-lobito-corridor/' ) ); ?>">Read the explainer</a></div></div>
  <div class="mech-card"><div class="mech-art">Article XII</div><div class="mech-title">Fiscal stabilisation</div><div class="mech-body">The Congo's only hard, dated obligation: fiscal stabilisation, a 90-day VAT refund, a single window, and centralised mining-sector tax administration, enacted within twelve months. The first real test of the Congolese side.</div><div class="mech-link"><a href="<?php echo esc_url( home_url( '/what-does-article-xii-require-us-drc-spa/' ) ); ?>">Read the explainer</a></div></div>
  <div class="mech-card"><div class="mech-art">Article IV</div><div class="mech-title">Compliance safeguard</div><div class="mech-body">The Congo cannot place an asset in the reserve if doing so breaches its own law or its international obligations. Assets under active arbitration, with title irregularities, or with sanctioned counterparties may be ineligible, whatever the commercial pressure.</div><div class="mech-link"><a href="<?php echo esc_url( home_url( '/how-is-the-us-drc-spa-enforced/' ) ); ?>">Read the explainer</a></div></div>
 </div>
 <div class="rail">
  <div>
  <div class="rail-eyebrow">Beyond the framework</div>
  <div class="rail-text">This page covers the SPA as written. Ascendance advises investors, law firms, and development finance institutions on the SPA as it is implemented, at the transaction level. Every engagement opens with a diagnostic call.</div>
  </div>
  <a class="rail-cta" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Explore advisory</a>
 </div>
 </section>
</div>
</div>

<?php
get_footer();
