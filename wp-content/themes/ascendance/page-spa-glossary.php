<?php
/**
 * Template Name: SPA Glossary
 *
 * @package Ascendance
 */

get_header();
?>
<div class="as-page-wrap ref-page-wrap">
<style>

@import url('https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,400;0,500;1,400&family=Barlow:wght@300;400;500;600&family=Roboto:wght@300;400;500&family=JetBrains+Mono:wght@400;500;600&display=swap');
@import url('https://cdn.jsdelivr.net/npm/@fontsource/cooper-hewitt@5.2.5/600.css');@import url('https://cdn.jsdelivr.net/npm/@fontsource/cooper-hewitt@5.2.5/700.css');
:root{
 --serif:'Noto Serif',Georgia,serif;--sans:'Cooper Hewitt','Barlow','Roboto',sans-serif;--mono:'JetBrains Mono','Consolas',monospace;
 --navy:#0F1E35;--terminal:#0D1626;--navy-mid:#182D4A;
 --accent:#BC1B1D;--accent-light:#E04B4B;--stone:#56514B;--white:#FFFFFF;--fg:#FFFFFF;
 --text-on-dark:#E8ECF3;--sub-dark:#8899BB;--muted-dark:#556677;--div-dark:#2A3A55;
 --row:#111C30;--row-alt:#0F1A2C;
}
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:var(--sans);background:var(--terminal);color:var(--text-on-dark);-webkit-font-smoothing:antialiased;}

/* TERMINAL HEADER BAR */
.as-thead{background:var(--navy);border-top:3px solid var(--accent);border-bottom:1px solid var(--div-dark);padding:20px 0;}
.as-thead-inner{width:100%;max-width:var(--maxw, 1240px);margin:0 auto;padding:0 24px;display:flex;justify-content:space-between;align-items:baseline;flex-wrap:wrap;gap:12px;}
.as-thead-title{font-family:var(--mono);font-size:12px;font-weight:600;letter-spacing:0.14em;text-transform:uppercase;color:var(--accent-light);}
.as-thead-meta{font-family:var(--mono);font-size:10px;color:var(--muted-dark);letter-spacing:0.06em;}
.thead-meta b{color:var(--sub-dark);font-weight:500;}

/* HERO strip */
.sg-hero{padding:40px 0 34px;border-bottom:1px solid var(--div-dark);}
.sg-hero-inner{width:100%;max-width:var(--maxw, 1240px);margin:0 auto;padding:0 24px;}
.eyebrow{font-family:var(--mono);font-size:10px;font-weight:600;letter-spacing:0.16em;text-transform:uppercase;color:var(--accent);margin-bottom:14px;}
.sg-hero-title{font-family:var(--serif);font-size:38px;font-weight:400;color:var(--fg);line-height:1.14;letter-spacing:-0.01em;}
.sg-hero-sub{font-family:var(--serif);font-size:15px;color:var(--sub-dark);margin-top:14px;max-width:620px;line-height:1.7;}
.hero-sub a{color:var(--accent-light);text-decoration:none;border-bottom:1px solid rgba(224,75,75,0.3);}
.sg-hero-meta{font-family:var(--mono);font-size:10px;letter-spacing:0.06em;text-transform:uppercase;color:var(--muted-dark);margin-top:14px;padding-top:12px;border-top:1px solid var(--div-dark);}
.hero-meta b{color:var(--accent-light);font-weight:600;}

/* CONTROL BAR: search + count + filters */
.as-controls{position:sticky;top:0;z-index:20;background:var(--terminal);border-bottom:1px solid var(--div-dark);padding:18px 0;}
.as-controls-inner{width:100%;max-width:var(--maxw, 1240px);margin:0 auto;padding:0 24px;}
.search-row{display:flex;gap:14px;align-items:center;margin-bottom:14px;}
.as-gsearch{flex:1;font-family:var(--mono);font-size:13px;padding:11px 14px;background:var(--navy);border:1px solid var(--div-dark);color:var(--text-on-dark);border-radius:2px;outline:none;}
.as-gsearch::placeholder{color:var(--muted-dark);}
.as-gsearch:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(188,27,29,0.12);}
.as-gsearch:focus-visible{outline:none;}
.cat-btn:focus-visible{outline:2px solid var(--accent-light);outline-offset:2px;}
.count{font-family:var(--mono);font-size:11px;font-weight:500;color:var(--accent-light);white-space:nowrap;letter-spacing:0.04em;display:flex;align-items:center;gap:8px;}
.count b{color:var(--fg);font-weight:600;}
.kbd{font-family:var(--mono);font-size:10px;color:var(--muted-dark);border:1px solid var(--div-dark);border-radius:2px;padding:3px 7px;white-space:nowrap;letter-spacing:0.04em;}
.kbd b{color:var(--sub-dark);font-weight:600;}
.count .count-filter{color:var(--sub-dark);border-left:1px solid var(--div-dark);padding-left:8px;text-transform:uppercase;letter-spacing:0.08em;font-size:10px;}
.count .count-clear{color:var(--accent-light);cursor:pointer;border:none;background:none;font-family:var(--mono);font-size:10px;letter-spacing:0.06em;text-transform:uppercase;padding:0;}
.count .count-clear:hover{color:var(--white);}
.cats{display:flex;gap:6px;flex-wrap:wrap;}
.cat-btn{font-family:var(--mono);font-size:10px;font-weight:500;letter-spacing:0.06em;text-transform:uppercase;padding:6px 12px;border:1px solid var(--div-dark);background:transparent;color:var(--sub-dark);cursor:pointer;border-radius:2px;transition:all 0.12s;}
.cat-btn.active{background:var(--accent);color:var(--white);border-color:var(--accent);}
.cat-btn:hover:not(.active){border-color:var(--accent-light);color:var(--white);}

/* TERM ROWS */
.as-list{width:100%;max-width:var(--maxw, 1240px);margin:0 auto;padding:8px 24px 40px;}
.as-row{width:100%;border-bottom:1px solid var(--div-dark);overflow:hidden;}
.as-row.hidden{display:none;}
.row-head{display:grid;grid-template-columns:1fr auto auto;align-items:center;gap:16px;padding:15px 8px;cursor:pointer;transition:background 0.1s;}
.row-head:hover{background:var(--row);}
.row-head:focus-visible{outline:2px solid var(--accent-light);outline-offset:-2px;background:var(--row);}
.as-row-left{display:flex;align-items:baseline;gap:12px;flex-wrap:wrap;}
.term{font-family:var(--serif);font-size:17px;font-weight:400;color:var(--fg);}
.abbr{font-family:var(--mono);font-size:10px;font-weight:600;letter-spacing:0.06em;color:var(--accent-light);background:rgba(188,27,29,0.14);padding:2px 7px;border-radius:2px;}
.artref{font-family:var(--mono);font-size:10px;font-weight:500;letter-spacing:0.06em;color:var(--muted-dark);text-align:right;white-space:nowrap;}
.cat-chip{font-family:var(--mono);font-size:9px;font-weight:500;letter-spacing:0.08em;text-transform:uppercase;color:var(--sub-dark);border:1px solid var(--div-dark);padding:2px 7px;border-radius:2px;white-space:nowrap;}
.chev{font-size:11px;color:var(--muted-dark);transition:transform 0.2s;justify-self:end;}
.as-row.open .chev{transform:rotate(180deg);}
.row-body{display:none;padding:0 8px 20px 8px;border-left:3px solid var(--accent);margin-left:2px;}
.as-row.open .row-body{display:block;}
.def{font-family:var(--serif);font-size:14px;font-weight:400;line-height:1.75;color:var(--text-on-dark);margin-top:14px;padding-left:16px;}
.def-ref{font-family:var(--mono);font-size:9px;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;color:var(--accent);margin-top:12px;padding-left:16px;}
.note{margin:14px 0 0 16px;background:rgba(188,27,29,0.06);border:1px solid rgba(188,27,29,0.2);border-left:3px solid var(--accent);padding:12px 16px;font-family:var(--serif);font-size:13px;font-weight:400;color:var(--text-on-dark);line-height:1.65;}
.note-l{font-family:var(--mono);font-size:9px;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;color:var(--accent-light);display:block;margin-bottom:5px;}
.note a{color:var(--accent-light);}
.no-results{padding:44px 8px;font-family:var(--mono);font-size:13px;color:var(--muted-dark);line-height:1.8;}
.no-results .nr-line{color:var(--sub-dark);}
.no-results .nr-q{color:var(--accent-light);}
.no-results a{color:var(--accent-light);}

/* ADVISORY RAIL */
.as-rail{width:100%;max-width:var(--maxw, 1240px);margin:0 auto;padding:0 24px 44px;}
.as-rail-box{background:#0F1E35;border-left:4px solid var(--accent);padding:26px 30px;display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap;}
.as-rail-eyebrow{font-family:var(--mono);font-size:10px;font-weight:600;letter-spacing:0.14em;text-transform:uppercase;color:var(--accent-light);margin-bottom:7px;}
.as-rail-text{font-family:var(--serif);font-size:14px;line-height:1.6;color:var(--sub-dark);max-width:560px;}
.as-rail-cta{font-family:var(--sans);font-size:11px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;padding:12px 24px;background:#fff;color:#0F1E35;text-decoration:none;border:2px solid #fff;white-space:nowrap;transition:background 0.15s,color 0.15s;}
.as-rail-cta:hover{background:transparent;color:#fff;}

.signoff{background:var(--navy);border-top:2px solid var(--accent);padding:28px 0;text-align:center;}
.signoff span{font-family:var(--serif);font-style:italic;font-size:12px;color:var(--accent);text-transform:uppercase;letter-spacing:0.2em;}
/* platform chrome */
.pf-head{background:var(--white);border-bottom:1px solid var(--div-light,#E8E4DC);}
.pf-top{max-width:var(--maxw);margin:0 auto;display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:20px;padding:14px 24px;}
.pf-wm{grid-column:2;font-family:var(--sans);font-weight:700;font-size:19px;letter-spacing:0.06em;color:var(--navy);text-decoration:none;text-align:center;white-space:nowrap;}
.pf-wm b{color:var(--accent);}
.pf-actions{grid-column:3;justify-self:end;display:flex;align-items:center;gap:16px;}
.pf-actions a{font-family:var(--sans);font-weight:600;font-size:13px;color:var(--navy);text-decoration:none;}
.pf-actions a:hover{color:var(--accent);}
.pf-sub{background:var(--accent);color:#fff !important;padding:9px 18px;border-radius:2px;}
.pf-nav{border-top:1px solid var(--div-light,#E8E4DC);}
.pf-nav-inner{max-width:var(--maxw);margin:0 auto;display:flex;gap:24px;padding:0 24px;}
.pf-nav-inner a{font-family:var(--sans);font-weight:600;font-size:13.5px;color:var(--navy);text-decoration:none;padding:13px 0;border-bottom:2px solid transparent;}
.pf-nav-inner a:hover{color:var(--accent);}
.pf-nav-inner a.on{color:var(--accent);border-bottom-color:var(--accent);}
.pf-foot{background:var(--navy);padding:30px 24px;}
.pf-foot-inner{max-width:var(--maxw);margin:0 auto;display:flex;justify-content:space-between;gap:20px;flex-wrap:wrap;font-family:var(--sans);font-size:12px;color:var(--sub-dark);}
.pf-foot-inner a{color:var(--sub-dark);text-decoration:none;}
.pf-foot-inner a:hover{color:var(--accent-light);}
@media(max-width:820px){.pf-top{padding:12px 20px;}.pf-nav-inner{padding:0 20px;overflow-x:auto;}.pf-foot{padding:24px 20px;}}

@media(max-width:820px){
 .as-thead-inner,.sg-hero-inner,.as-controls-inner,.as-list,.as-rail{padding-left:20px;padding-right:20px;}
 .sg-hero-title{font-size:28px;}
 .row-head{grid-template-columns:1fr auto;}
 .artref{display:none;}
 .rail-box{flex-direction:column;align-items:stretch;}.rail-cta{text-align:center;}
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
.pf-foot{background:var(--pf-foot-bg);padding:44px 24px 26px;}
.pf-foot-top{max-width:var(--maxw);margin:0 auto;display:grid;grid-template-columns:1.1fr 2fr;gap:40px;}
.pf-fbrand .pf-wm{color:#fff;}
.pf-fbrand p{font-family:var(--sans);font-size:14px;line-height:1.55;color:var(--pf-foot-text);margin-top:16px;max-width:300px;}
.pf-fcols{display:grid;grid-template-columns:repeat(4,1fr);gap:22px;}
.pf-fcol h6{font-family:var(--sans);font-weight:700;font-size:11px;letter-spacing:0.12em;text-transform:uppercase;color:var(--pf-foot-text);margin:0 0 12px;}
.pf-fcol a{display:block;font-family:var(--sans);font-size:13.5px;color:var(--pf-foot-link);text-decoration:none;padding:4px 0;}
.pf-fcol a:hover{color:var(--accent-light);}
.pf-foot-herald{max-width:var(--maxw);margin:22px auto 0;padding:0 24px;}
.pf-foot-herald a{font-family:var(--sans);font-weight:600;font-size:13px;color:var(--accent-light);text-decoration:none;}
.pf-foot-legal{max-width:var(--maxw);margin:18px auto 0;padding:18px 24px 0;border-top:1px solid var(--pf-foot-border);display:flex;flex-direction:column;gap:3px;font-family:var(--sans);font-size:12px;color:var(--pf-foot-text);}
.pf-foot-legal a{color:var(--pf-foot-text);}
.pf-foot-legal a:hover{color:var(--accent-light);}
.pf-foot-base{max-width:var(--maxw);margin:14px auto 0;padding:14px 24px 0;border-top:1px solid var(--pf-foot-border);display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;font-family:var(--sans);font-size:12px;color:var(--pf-foot-text);}
@media(max-width:820px){.pf-foot{padding:30px 20px 22px;}.pf-foot-top{grid-template-columns:1fr;gap:24px;}.pf-fcols{grid-template-columns:1fr 1fr;}}
.pf-top{gap:14px;}.pf-actions{gap:13px;}.pf-actions a{white-space:nowrap;}
:root[data-theme="light"]{--terminal:#f4f1ea;--navy:#ffffff;--row:#efe9df;--row-alt:#f0ebe1;--text-on-dark:#33302a;--sub-dark:#6b6456;--muted-dark:#9a9384;--div-dark:#e2dbcc;--fg:#1f1c18;}

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





<div class="as-thead">
 <div class="as-thead-inner">
 <div class="as-thead-title">SPA GLOSSARY :: REGISTER</div>
 <div class="as-thead-meta">TERMS <b>42</b> &nbsp;/&nbsp; SOURCE <b>US-DRC SPA, 2025.12.04</b> &nbsp;/&nbsp; REVIEWED <b>2026.07</b></div>
 </div>
</div>

<div class="sg-hero">
 <div class="sg-hero-inner">
 <div class="eyebrow">Register / Reference</div>
 <h1 class="sg-hero-title">The SPA Glossary</h1>
 <p class="sg-hero-sub">Every mechanism, institution, and legal term in the US-DRC Strategic Partnership Agreement, defined against the treaty text. Read <a href="<?php echo esc_url( home_url( '/us-drc-partnership/' ) ); ?>">The SPA, Explained</a> for how the mechanisms fit together.</p>
 <div class="sg-hero-meta">Last updated <b>15 July 2026</b> &nbsp;|&nbsp; Next scheduled review &middot; <b>Q3 2026</b></div>
 </div>
</div>

<div class="as-controls">
 <div class="as-controls-inner">
 <div class="search-row">
  <input class="as-gsearch" id="gsearch" placeholder="query: SAR, Article IV, amodiation, aligned person..." oninput="filterGloss()" aria-label="Search glossary terms">
  <span class="kbd" aria-hidden="true">press <b>/</b> to search</span>
  <div class="count" id="count" aria-live="polite"><b>42</b> / 42 terms</div>
 </div>
 <div class="cats">
  <button class="cat-btn active" onclick="filterCat('all',this)">All</button>
  <button class="cat-btn" onclick="filterCat('mechanism',this)">Mechanisms</button>
  <button class="cat-btn" onclick="filterCat('institution',this)">Institutions</button>
  <button class="cat-btn" onclick="filterCat('legal',this)">Legal</button>
  <button class="cat-btn" onclick="filterCat('financial',this)">Financial</button>
  <button class="cat-btn" onclick="filterCat('sector',this)">Sector</button>
 </div>
 </div>
</div>

<div class="as-list" id="as-list"></div>

<div class="as-rail">
 <div class="as-rail-box">
 <div>
  <div class="as-rail-eyebrow">Term not here?</div>
  <div class="as-rail-text">The SPA introduces new concepts faster than any public glossary can track. Ascendance analyses the framework at the transaction level.</div>
 </div>
 <a class="as-rail-cta" href="<?php echo esc_url( home_url( '/advisory/' ) ); ?>">Explore advisory</a>
 </div>
</div>







<script>




const terms=[
 {term:'Strategic Partnership Agreement',abbr:'SPA',cat:'legal',article:'Preamble + Art. I',
 def:'The bilateral treaty signed December 4, 2025 between the United States and the Democratic Republic of the Congo. It grants US companies preferential access to Congolese critical mineral assets and establishes a structured framework for building American presence in the DRC mining sector. The French text (Accord de Partenariat Strategique) carries equal legal weight. Duration: indefinite, with a five-year notice period for termination.',note:null},
 {term:'Strategic Asset Reserve',abbr:'SAR',cat:'mechanism',article:'Article IV',
 def:'The DRC\'s designated list of critical mineral assets, gold assets, and unlicensed exploration areas reserved for preferential US investor access. US investors hold a nine-month right of first offer: three months to submit a proposal, then a three-month negotiation window renewable once. After the US-exclusive window, aligned investors may bid, and anything unresolved at twelve months returns to the JSC. Where a SAR project is at exploration stage, completing exploration carries a three year exclusive window to apply for the exploitation licence. Nations Washington considers strategic rivals are permanently excluded. The SAR is an evolving list. The full list is not publicly available; eligible private sector entities may request it from either government.',
 note:'The SAR is a right of first offer mechanism, not a right of first refusal. The DRC retains full discretion over whether to accept any offer. The gap between the public shortlist and the full designated list is the most significant information asymmetry in the current market. Ascendance maintains an asset-level analysis of the reserve. <a href="/advisory/">Request access.</a>'},
 {term:'Joint Steering Committee',abbr:'JSC',cat:'institution',article:'Article VI',
 def:'The bilateral governance body overseeing SPA implementation. Composed of five representatives from each government. Meets at least twice a year, plus ad hoc sessions. Functions include: maintaining the SAR list designated by the DRC, reviewing QSP notifications, monitoring Article XII reform progress, and resolving compliance disputes. Inaugural meeting: February 5, 2026. JSC meeting cycles are the primary signal source for SPA implementation status.',note:null},
 {term:'Qualifying Strategic Project',abbr:'QSP',cat:'mechanism',article:'Article VIII',
 def:'A project designation that unlocks the full US government financing and support stack. QSPs must meet US alignment criteria regarding ownership structure. Benefits include DFC financing, EXIM Bank credit support, expedited permitting, and fiscal protections. QSPs must route production through the Lobito Corridor where geographically feasible. Ownership thresholds for non-aligned entities decline over time: 40% at entry, 30% after Year 5, 20% after Year 10, 10% after Year 20.',note:null},
 {term:'Aligned Person',abbr:null,cat:'legal',article:'Annex 2',
 def:'A person or entity from a country designated as aligned with US strategic interests under Annex 2 of the SPA. Aligned persons receive the second tier of SAR access: after the US exclusive window closes, aligned persons may submit proposals before any other party. The definition is restrictive. An entity owned one third or more by covered-nation interests, or with a covered-nation CEO or one-third board control, does not qualify. The current Annex 2 list is not fully public. EU member states, Australia, Japan, South Korea, and select Gulf states are generally understood to qualify, but investors should verify the status of their specific jurisdiction.',
 note:'The distinction between US persons and aligned persons is critical for SAR window timing. A European or Australian fund does not have access during the nine-month US exclusive window, only after it expires. A one-third covered-nation shareholding can disqualify an entity that assumed it was aligned. This timing and eligibility difference can be decisive in fast-moving transactions.'},
 {term:'Right of First Offer',abbr:'ROFO',cat:'legal',article:'Article VII',
 def:'The core SAR investment window: US investors receive exclusive access to bid on designated assets for nine months before any other party. This is a right of first offer, not a right of first refusal. The DRC is not obligated to accept any offer. Within that window, a US proposal opens a three-month negotiation period, renewable once. After the full window expires, aligned persons under Annex 2 may submit proposals. Strategic rivals are permanently excluded.',note:null},
 {term:'Sakania-Lobito Corridor',abbr:'Lobito Corridor',cat:'sector',article:'Article IX',
 def:'The designated strategic export infrastructure under the SPA. The treaty titles Article IX the Sakania-Lobito Corridor; in general use it is the Lobito Corridor. The route runs from Kolwezi in the DRC through Zambia to Angola\'s Lobito port. The DRC is committed to routing at least 50% of copper state-owned volumes, 90% of zinc, and 30% of cobalt through the corridor within five years. The Angola segment is operational. The DRC segment is under active PPP tender as of April 2026. All QSPs must use the corridor where geographically feasible.',note:null},
 {term:'DRC Designated Strategic Projects',abbr:'DSPs',cat:'mechanism',article:'Article V',
 def:'Transformative infrastructure and industrial initiatives identified by the DRC as central to its long-term development vision. The DRC submitted its initial DSP list in January 2026. DSP status provides access to the same US financing stack as QSPs. Grand Inga III is the flagship DSP. DSP designation is initiated by the DRC, unlike SAR assets which are managed through the JSC.',note:null},
 {term:'Fiscal Stabilisation Clause',abbr:null,cat:'financial',article:'Article XII',
 def:'A 10-year guarantee that fiscal conditions for SAR project operators will not change after project entry. Tax rates, royalties, permit fees, and administrative charges are locked at entry levels. Backed by DFC and EXIM Bank financing conditionality. Implementation deadline: December 2026.',
 note:'Fiscal stabilisation clauses have a mixed track record in the DRC. The SPA version is backed by multilateral financing conditionality that raises the cost of political override. Investors should nonetheless model a range of fiscal scenarios and monitor the December 2026 implementation deadline as the first real stress test. <a href="/updates/">Follow the reform tracking.</a>'},
 {term:'90-Day VAT Refund',abbr:null,cat:'financial',article:'Article XII',
 def:'Article XII mandates that VAT refunds for SAR project operators must be processed within 90 days, with automatic credit offset if the deadline is missed. One of the most operationally significant near-term reforms. Administrative infrastructure must be in place by December 2026. Verify refund mechanism status at the provincial level before project entry; national mandates and provincial execution are not the same thing.',note:null},
 {term:'US Development Finance Corporation',abbr:'DFC',cat:'institution',article:'Article V',
 def:'The primary US government financing vehicle for SPA implementation. Mandated to mobilise capital for DRC SAR and QSP projects. DFC operates through the private sector and does not take direct equity positions in Congolese assets. Africa is DFC\'s second-largest portfolio globally. DFC commitments to Lobito Corridor infrastructure are the most significant public signals of US government financing intent to date.',note:null},
 {term:'US Export-Import Bank',abbr:'EXIM',cat:'institution',article:'Article V',
 def:'The US government export credit agency providing financing, loan guarantees, and insurance for US companies with DRC exposure. EXIM and DFC form the two-pillar US government financing stack for SPA projects. EXIM engagement in DRC energy and minerals infrastructure was confirmed at the Powering Africa Summit in March 2026.',note:null},
 {term:'Gecamines',abbr:null,cat:'institution',article:'Multiple',
 def:'La Generale des Carrieres et des Mines, the DRC\'s primary state-owned mining company and the central counterparty in most SAR asset transactions. Holds a large portfolio of exploitation permits across the Copperbelt. Typically retains title to assets while granting operational rights through amodiation arrangements. Gecamines\' institutional capacity and governance are material risk factors for all SAR transactions involving its assets.',
 note:'Gecamines institutional health is one of the most frequently underestimated risk factors in SAR due diligence. Permit status, outstanding obligations, and internal governance changes require independent assessment. <a href="/advisory/">Ascendance provides a current assessment.</a>'},
 {term:'Article IV Prohibition',abbr:null,cat:'legal',article:'Article IV',
 def:'The DRC may not add an asset to the SAR if doing so would violate the DRC\'s domestic law or would be contrary to international legal obligations between the Parties. Assets subject to active international arbitration, confirmed legal irregularities, or sanctioned counterparties may be ineligible for SAR designation regardless of commercial or political interest.',
 note:'Article IV is the most important compliance checkpoint in SAR due diligence. An asset designated in potential violation carries legal challenge risk that could affect transaction certainty. Independent analysis of title chain, arbitration status, and counterparty sanctions exposure is essential, and cannot be delegated to the DRC government\'s own representations. <a href="/advisory/">Request an Article IV compliance assessment.</a>'},
 {term:'ARECOMS',abbr:null,cat:'institution',article:'Article XII',
 def:'Autorite de Regulation et de Controle des Marches des Substances Minerales Strategiques, the DRC\'s strategic minerals market regulator, established November 2019. Sits on the JSC as a DRC representative. Core powers: cobalt export quota allocation and strategic reserve management. Annual cobalt export quota: 96,600 tonnes (2026-2027). ARECOMS decisions on export quotas directly affect the economics of cobalt-producing SAR projects.',note:null},
 {term:'CAMI',abbr:null,cat:'institution',article:'Operational',
 def:'Cadastre Minier, the DRC\'s mining registry. Issues, records, and tracks all mining permits. Essential for SAR due diligence: CAMI records confirm whether a designated asset has active status, clean title, no pending forfeiture proceedings, and no undisclosed permit transfers. CAMI records should be checked directly, not relied upon through SOE representations.',note:null},
 {term:"Permis d'Exploitation",abbr:'PE',cat:'legal',article:'Mining Code 2018',
 def:"An exploitation permit, the highest-order mining right in the DRC, authorising industrial-scale extraction. SAR designations operate at the PE level; the SAR list maps to specific PE numbers in the CAMI register. Duration: 25-30 years, renewable. Granted by the Minister of Mines. Any SAR due diligence must confirm the specific PE numbers, their status, expiry dates, and any encumbrances recorded at CAMI.",note:null},
 {term:'Cession Totale',abbr:null,cat:'legal',article:'Mining Code',
 def:'A full permit transfer, the complete assignment of an exploitation permit from one legal entity to another, recorded at CAMI. In the SAR context, cession totale transactions may signal pre-SAR positioning activity. Any cession recorded in the period preceding SAR designation warrants beneficial ownership analysis to confirm the acquiring entity\'s alignment with SPA eligibility criteria.',
 note:'A cession registered close to a SAR designation date may indicate legitimate pre-positioning or a structuring issue that affects US investor access. Beneficial ownership analysis is not optional. <a href="/advisory/">Ascendance analyses cession activity against SAR-designated assets.</a>'},
 {term:'Amodiation',abbr:null,cat:'legal',article:'Mining Code',
 def:'A lease arrangement under which a permit holder (typically Gecamines) grants operational rights to a third-party operator while retaining title. The standard transaction structure for SAR assets involving Gecamines. Gecamines typically retains an equity participation. SAR investors should carefully review amodiation terms, particularly regarding operational autonomy, cost recovery, and dispute resolution.',note:null},
 {term:'Decheance',abbr:null,cat:'legal',article:'Mining Code Art. 47',
 def:'Permit forfeiture, the formal cancellation of a mining right for non-payment of annual surface fees. A permit "en decheance pour non-paiement" is in active forfeiture proceedings. "Droit Dechu" means the permit has been fully cancelled. SAR due diligence must confirm decheance status at CAMI: a permit in forfeiture proceedings cannot be transferred and may not be validly included in the SAR until the underlying obligation is resolved.',note:null},
 {term:'ITIE-RDC',abbr:'ITIE',cat:'institution',article:'Transparency',
 def:'Initiative pour la Transparence dans les Industries Extractives, the DRC chapter of the Extractive Industries Transparency Initiative. Publishes reconciled payment data covering thousands of companies in DRC extractives. Data typically lags 6-12 months from the reporting year.',note:null},
 {term:'Lobito Atlantic Railway',abbr:'LAR',cat:'sector',article:'Article IX',
 def:'The concession vehicle operating the Angola segment of the Lobito Corridor. The LAR is operational on the Angola segment. The DRC segment is under PPP tender as of April 2026. First US-bound Congolese copper shipped January 2026 under a Mercuria-backed venture, establishing proof of operational concept. Consistent throughput at commercial scale remains to be demonstrated.',note:null},
 {term:'Grand Inga',abbr:null,cat:'sector',article:'Article X',
 def:'The Congo River hydroelectric complex, the largest undeveloped hydroelectric potential on earth. SPA Article X creates a joint Inga governance committee activating on ratification. Inga III is the flagship DRC Designated Strategic Project (4,800-11,000 MW capacity options). World Bank re-engagement programme active. For Copperbelt SAR assets, the energy deficit is the binding operational constraint; Inga does not address this within any near-term investment horizon.',
 note:'The energy deficit in Haut-Katanga (approximately 540MW) and Lualaba is not reflected in most SAR asset project economics. Before any SAR commitment in the Copperbelt, verify energy access at the specific site level. <a href="/advisory/">Ascendance maps energy availability by SAR asset.</a>'},
 {term:'Chinese Equity Cap',abbr:null,cat:'legal',article:'Article VIII Annex 1',
 def:'The mechanism by which the SPA progressively limits non-aligned ownership in QSP and SAR assets over time. Non-aligned ownership may not exceed 40% at inception, declining to 30% after Year 5, 20% after Year 10, and 10% after Year 20. Applies to new transactions structured under the SPA framework. The primary instrument through which the agreement seeks to shift the DRC mineral sector\'s ownership balance over time.',note:null},
 {term:'Article XVIII, Termination',abbr:null,cat:'legal',article:'Article XVIII',
 def:'The SPA\'s structural durability provision: either party must give five years\' notice to terminate the agreement. Joint reviews occur every three years. The framework survives normal political transition cycles in both countries. Investors entering SAR positions operate within a treaty framework with meaningful longevity protection, though political continuity within that window is not guaranteed.',note:null},
 {term:'Guichet Unique',abbr:null,cat:'institution',article:'Article XII',
 def:'The one-stop administrative window for investment approvals, a single entry point for all permits, licences, and regulatory procedures. Article XII mandates an operational Guichet Unique by December 2026. Provincial implementation is uneven. Verify Guichet Unique functionality at the specific provincial level relevant to your project; do not assume national announcement equals provincial delivery.',note:null},
 {term:'ICSID',abbr:null,cat:'legal',article:'Dispute resolution',
 def:'International Centre for Settlement of Investment Disputes, the World Bank arbitration body. The SPA strengthens ICSID jurisdiction for US investors in the DRC. An active ICSID case against the DRC involving a SAR-designated asset creates an Article IV compliance question that investors must assess before any commitment.',
 note:'Any SAR asset with an active ICSID proceeding against the DRC carries a material Article IV eligibility question. Active arbitration does not automatically preclude SAR designation, but it creates legal challenge risk that must be priced into transaction structure and timing. <a href="/advisory/">Request an ICSID exposure assessment.</a>'},
 {term:'Strategic Minerals Reserve',abbr:'SMR',cat:'mechanism',article:'Article XI',
 def:'A coordinated physical stockpile of critical minerals to be established within the DRC, distinct from the SAR, which is a list of investable assets. The SMR is intended to stabilise cobalt supply for the US market during disruptions and anchor US offtake. State-owned enterprises are required to provide a right of first offer on marketed critical minerals to US and allied persons. The SMR and SAR are complementary instruments operating on different timeframes.',note:null},
 {term:"Permis de Recherches",abbr:'PR',cat:'legal',article:'Mining Code 2018',
 def:"A research (exploration) permit, the entry-level mining right in the DRC, authorising geological exploration. PRs precede exploitation permits in the mining title lifecycle. The SAR can include unlicensed exploration areas as well as active PEs, meaning some SAR entries may be at PR stage or pre-application greenfield. The rights and investor protections attached to a PR differ significantly from those attached to a PE. Verify title type before any SAR commitment.",note:null},
 {term:'Pas de Porte',abbr:null,cat:'financial',article:'SOE practice',
 def:'An upfront signature payment required when entering a joint venture or amodiation arrangement with a Congolese state-owned enterprise, particularly Gecamines. Not codified in the SPA but a standard feature of DRC SOE transactions. Amounts are negotiated case by case and are distinct from the fiscal terms stabilised under Article XII. Budget for this as a transaction cost separate from stated project economics.',note:null},
 {term:'US Person',abbr:null,cat:'legal',article:'Annex 2',
 def:'The eligibility category that holds the SAR right of first offer. Under Annex 2, a US person is a US national, an entity organised under US law, an entity at least 50 percent owned by US nationals, or an entity with at least 5 percent US government equity or more than 25 percent US government debt financing. The US person test anchors both the SAR first tier and the QSP ownership thresholds.',
 note:'Whether an investor qualifies as a US person is the single most consequential eligibility determination under the SPA. It decides first tier versus second tier SAR access and the ownership maths for QSP status. Structuring and beneficial ownership analysis are what settle it. <a href="/advisory/">Request an eligibility structuring review.</a>'},
 {term:'Covered Nation',abbr:null,cat:'legal',article:'Annex 2',
 def:'The statutory exclusion category referenced in Annex 2, defined through 10 U.S.C. 4872(f)(2). It covers, in effect, China, Russia, Iran, North Korea, Cuba, and Venezuela. Entities from covered nations are the strategic rivals permanently excluded from the SAR and progressively capped in QSPs. This statute is the legal engine behind the Chinese equity cap: the exclusion rests on US law, not discretion.',note:null},
 {term:'Binational Economic Partnership Forum',abbr:'BEPF',cat:'institution',article:'Article III',
 def:'The government to government economic dialogue established under Article III. It convenes every two years, alternating between Washington and Kinshasa, with the inaugural forum due within 365 days of entry into force. The BEPF is distinct from the JSC: the forum sets political and economic direction, while the JSC runs implementation.',note:null},
 {term:'Article XIII, Mining SOEs',abbr:null,cat:'legal',article:'Article XIII',
 def:'The SPA commits the DRC to review the beneficial ownership and leadership structures of its mining state owned enterprises and to share information on SOE stakes with the JSC and USTR. Gecamines underwent a leadership change in February 2026. SOE governance and ownership transparency are the operative tests of this article, and they bear directly on any SAR transaction where the counterparty is a state enterprise.',note:null},
 {term:'Article XIV, Technical Assistance and Governance',abbr:null,cat:'legal',article:'Article XIV',
 def:'The US commitment to technical assistance across mining development, processing and refining capacity, legislative and regulatory reform, and training for tax and customs officials. Article XIV also carries the DRC commitment to prioritise judicial reform for a predictable investment climate. This is the governance and judicial reform track that runs alongside the fiscal reforms of Article XII.',note:null},
 {term:'Article XV, Artisanal and Small-Scale Mining',abbr:'ASM',cat:'sector',article:'Article XV',
 def:'The SPA commits both governments to formalise artisanal and small scale mining in partnership with the Entreprise Generale du Cobalt, building traceable supply chains through cooperatives, certified traders, and monitored trading centres. ASM formalisation is where cobalt provenance and conflict mineral compliance are decided, and it is central to the responsible sourcing objectives of the agreement.',note:null},
 {term:'Entreprise Generale du Cobalt',abbr:'EGC',cat:'institution',article:'Article XV',
 def:'The DRC state enterprise holding the mandate over artisanal cobalt and the designated SPA partner for ASM formalisation under Article XV. EGC is tasked with channelling artisanal cobalt into traceable, compliant supply chains destined for approved markets. Its operational capacity is central to whether the responsible sourcing objectives of the SPA are met in practice.',note:null},
 {term:'Offtake',abbr:null,cat:'financial',article:'Annex 2',
 def:'Mining products designated for sale to buyers under commercial arrangements. Under Article XI, state enterprises must give US and aligned persons a right of first offer on offtake from SAR projects and QSPs destined for the US market. Offtake rights, not only equity, are a core lever of the SPA: an investor can secure supply without holding the asset.',note:null},
 {term:'Beneficiation',abbr:null,cat:'sector',article:'Annex 2',
 def:'The processing of mined ore to improve its grade, recovery, or form for end user needs. Local beneficiation and in country value addition are among the stated objectives of the SPA and a criterion for QSP project types. The push from raw export toward domestic processing is a structural theme of the agreement and a live policy priority in Kinshasa.',note:null},
 {term:'Critical Minerals',abbr:null,cat:'legal',article:'Annex 2',
 def:'Minerals identified as critical by US authorities, the USGS, the Department of Energy, or the Department of War, together with minerals the DRC designates as strategic. This scope definition determines which assets can enter the SAR. Copper, cobalt, lithium, tantalum from coltan, and germanium are the DRC minerals most in view under the agreement.',note:null},
 {term:'Greenfield and Brownfield',abbr:null,cat:'sector',article:'Annex 2',
 def:'Greenfield refers to mining on a site with no prior mining activity; brownfield to a site with existing or prior mining infrastructure. Both qualify as QSP project types under Annex 1. The SPA explicitly targets greenfield exploration and development: the intent is to expand DRC production, not only to reallocate existing assets.',note:null},
 {term:'UAE-DRC APEG',abbr:'APEG',cat:'legal',article:'Related instrument',
 def:'The Accord de Partenariat Economique Global, a comprehensive economic partnership between the DRC and the United Arab Emirates, ratified by the Congolese Senate in July 2026. It is a separate instrument from the US SPA and should not be conflated with it. The SPA is the US framework; the APEG is the Emirati one. Kinshasa is pursuing parallel strategic partnerships rather than a single alignment.',
 note:'The coexistence of the US SPA and the UAE APEG is the clearest public signal that Kinshasa is running a multi alignment strategy. The two frameworks can create overlapping, and occasionally competing, claims on the same assets. Reading DRC minerals strategy through the SPA alone now misses half the board.'},
];

let cc='all',cs='';
function render(){
 const list=document.getElementById('as-list');
 const q=cs.toLowerCase();
 let vis=0,html='';
 terms.forEach((t,i)=>{
 const mc=cc==='all'||t.cat===cc;
 const ms=!q||t.term.toLowerCase().includes(q)||(t.abbr&&t.abbr.toLowerCase().includes(q))||t.article.toLowerCase().includes(q)||t.def.toLowerCase().includes(q);
 const show=mc&&ms;
 if(show)vis++;
 html+=`<div class="as-row${show?'':' hidden'}" id="r-${i}">
  <div class="row-head" role="button" tabindex="0" aria-expanded="false" aria-controls="b-${i}" onclick="tog(${i})" onkeydown="rowKey(event,${i})">
   <div class="as-row-left"><span class="term">${t.term}</span>${t.abbr?`<span class="abbr">${t.abbr}</span>`:''}<span class="cat-chip">${t.cat}</span></div>
   <span class="artref">${t.article}</span>
   <span class="chev" aria-hidden="true">&#9662;</span>
  </div>
  <div class="row-body" id="b-${i}">
   <div class="def">${t.def}</div>
   <div class="def-ref">SPA reference :: ${t.article}</div>
   ${t.note?`<div class="note"><span class="note-l">Investor note</span>${t.note}</div>`:''}
  </div>
 </div>`;
 });
 if(!vis)html=`<div class="no-results"><div class="nr-line">query returned <span class="nr-q">0</span> of ${terms.length} terms</div><div>no match for “${cs||cc}”. <a href="/advisory/">Ask Ascendance directly.</a></div></div>`;
 list.innerHTML=html;
 const catLabel=cc==='all'?'':`<span class="count-filter">${cc}</span>`;
 const clearBtn=(cc!=='all'||cs)?`<button class="count-clear" onclick="clearAll()">clear</button>`:'';
 document.getElementById('count').innerHTML=`<span><b>${vis}</b> / ${terms.length} terms</span>${catLabel}${clearBtn}`;
}
function tog(i){const r=document.getElementById('r-'+i);const open=r.classList.toggle('open');r.querySelector('.row-head').setAttribute('aria-expanded',open?'true':'false');}
function rowKey(e,i){if(e.key==='Enter'||e.key===' '){e.preventDefault();tog(i);}}
function clearAll(){cs='';cc='all';document.getElementById('gsearch').value='';document.querySelectorAll('.cat-btn').forEach(b=>b.classList.remove('active'));document.querySelector('.cat-btn').classList.add('active');render();}
function filterCat(cat,btn){cc=cat;document.querySelectorAll('.cat-btn').forEach(b=>b.classList.remove('active'));btn.classList.add('active');render();}
function filterGloss(){cs=document.getElementById('gsearch').value;render();}
document.addEventListener('keydown',function(e){
 const g=document.getElementById('gsearch');
 if(e.key==='/'&&document.activeElement!==g){e.preventDefault();g.focus();g.select();}
 else if(e.key==='Escape'&&document.activeElement===g){g.value='';cs='';render();g.blur();}
});
render();






</script>
</div>
<?php get_footer(); ?>
