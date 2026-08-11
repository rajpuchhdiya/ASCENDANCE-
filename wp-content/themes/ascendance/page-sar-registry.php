<?php
/**
 * Template Name: Strategic Asset Reserve Registry
 *
 * @package Ascendance
 */

global $ascendance_custom_seo_title, $ascendance_custom_seo_meta;
$ascendance_custom_seo_title = 'SAR Registry | Ascendance Strategies';
$ascendance_custom_seo_meta = [
    
];

get_header();
?>

<div class="as-page-wrap ref-page-wrap">
<style>

@import url('https://cdn.jsdelivr.net/npm/@fontsource/cooper-hewitt@5.2.5/600.css');@import url('https://cdn.jsdelivr.net/npm/@fontsource/cooper-hewitt@5.2.5/700.css');

:root{
  --accent:#BC1B1D; --accent-light:#E04B4B;
  --navy:#0F1E35; --deep-navy:#0A1628; --terminal-bg:#0D1626; --mid-navy:#182D4A;
  --cream:#F7F4EF; --white:#FFFFFF;
  --divider-light:#E8E4DC; --divider-dark:#2A3A55;
  --ink:#1A1A2E; --text-dark:#3A3A4A; --text-muted:#6B6B7A; --stone:#56514B;
  --on-dark:#FFFFFF; --sub-dark:#8899BB; --muted-dark:#556677;
  --warning:#E67E22; --success:#27AE60; --info:#2980B9;
  --font-serif:'Noto Serif',Georgia,serif;
  --font-sans:'Cooper Hewitt','Barlow','Roboto',sans-serif;
  --font-mono:'JetBrains Mono',monospace;
}
/* LIGHT THEME: terminal-on-cream mapping so the register follows the platform theme */
:root[data-theme="light"]{
  --navy:#FFFFFF; --deep-navy:#EFEBE4; --terminal-bg:#F7F4EF; --mid-navy:#FFFFFF;
  --divider-dark:#E8E4DC;
  --on-dark:#1A1A2E; --sub-dark:#56514B; --muted-dark:#7A7568;
  /* editorial (profile) tokens hold their cream identity in light */
  --cream:#F7F4EF; --white:#FFFFFF; --ink:#1A1A2E; --text-dark:#3A3A4A; --text-muted:#6B6B7A; --divider-light:#E8E4DC;
}
/* DARK THEME: editorial (profile) tokens flip so the profile view follows too */
:root[data-theme="dark"]{
  --cream:#17140f; --white:#221e17; --ink:#f3eee4; --text-dark:#cec7b8; --text-muted:#98917f; --divider-light:#322c23;
}
*{box-sizing:border-box;margin:0;padding:0;}
body{background:var(--terminal-bg);color:var(--on-dark);font-family:var(--font-sans);font-size:13px;line-height:1.5;-webkit-font-smoothing:antialiased;transition:background .25s ease,color .25s ease;}
.hidden{display:none !important;}

/* ============ PLATFORM CHROME (shared) ============ */
.plat-head{background:var(--deep-navy);border-bottom:1px solid var(--divider-dark);}
.plat-top{max-width:1240px;margin:0 auto;display:flex;align-items:center;padding:16px 22px;}
.as-lockup{display:inline-flex;flex-direction:column;gap:3px;text-decoration:none;line-height:1;--ll:20px;}
.as-lockup .ll-row{display:flex;align-items:stretch;}
.as-lockup .ll-box{width:var(--ll);height:var(--ll);background:var(--accent);color:#fff;display:flex;align-items:center;justify-content:center;font-family:var(--font-sans);font-weight:700;font-size:calc(var(--ll)*0.72);line-height:1;}
.as-lockup .ll-s{background:var(--on-dark);color:var(--terminal-bg);}
.as-lockup .ll-word{font-family:var(--font-sans);font-weight:700;font-size:var(--ll);letter-spacing:0.005em;color:var(--on-dark);height:var(--ll);display:flex;align-items:center;padding-left:calc(var(--ll)*0.05);}
.as-lockup .ll-tag{font-family:var(--font-sans);font-weight:600;font-size:calc(var(--ll)*0.275);letter-spacing:0.13em;text-transform:uppercase;color:var(--sub-dark);margin-top:calc(var(--ll)*0.2);white-space:nowrap;align-self:stretch;text-align:center;}
.plat-nav{background:var(--navy);border-bottom:1px solid var(--divider-dark);display:flex;gap:26px;align-items:center;padding:0 22px;overflow-x:auto;max-width:1240px;margin:0 auto;}
.plat-nav a{font-family:var(--font-sans);font-weight:600;font-size:13px;color:var(--sub-dark);text-decoration:none;padding:13px 0;border-bottom:2px solid transparent;white-space:nowrap;letter-spacing:.02em;}
.plat-nav a:hover{color:var(--on-dark);}
.plat-nav a.on{color:var(--accent);border-bottom-color:var(--accent);}
.plat-actions{margin-left:auto;display:flex;align-items:center;gap:18px;}
.plat-actions>a{padding:13px 0;}
.plat-sub{background:var(--accent);color:#fff;padding:8px 16px;border-radius:2px;}
.plat-sub:hover{color:#fff;opacity:.9;}
.plat-theme{display:inline-flex;border:1px solid var(--divider-dark);border-radius:2px;overflow:hidden;}
.plat-theme .theme-btn{width:30px;height:28px;display:flex;align-items:center;justify-content:center;background:transparent;color:var(--muted-dark);border:none;cursor:pointer;padding:0;}
.plat-theme .theme-btn:hover{color:var(--on-dark);}
.plat-theme .theme-btn.on{background:var(--on-dark);color:var(--terminal-bg);}
.plat-theme .theme-btn svg{width:15px;height:15px;}

/* ================= REGISTRY VIEW (terminal) ================= */
.masthead{background:var(--deep-navy);border-bottom:1px solid var(--divider-dark);padding:28px max(24px, calc((100% - 1240px) / 2 + 24px)) 22px;}
.eyebrow{font-size:10px;font-weight:600;letter-spacing:.18em;text-transform:uppercase;color:var(--accent);margin-bottom:10px;}
.masthead h1{font-family:var(--font-serif);font-size:30px;font-weight:600;color:var(--on-dark);margin-bottom:6px;}
.dek{font-family:var(--font-serif);font-style:italic;font-size:14px;color:var(--sub-dark);}
.vintage-bar{display:flex;gap:24px;flex-wrap:wrap;margin-top:16px;padding-top:14px;border-top:1px solid var(--divider-dark);}
.vintage-item{font-family:var(--font-mono);font-size:10px;color:var(--muted-dark);letter-spacing:.04em;}
.vintage-item b{color:var(--accent-light);font-weight:500;}
.vintage-critical{border-left:3px solid var(--accent);padding-left:10px;}

.controls{background:var(--navy);border-bottom:1px solid var(--divider-dark);padding:16px max(24px, calc((100% - 1240px) / 2 + 24px));display:flex;gap:10px;flex-wrap:wrap;align-items:center;position:sticky;top:0;z-index:10;}
.controls input,.controls select{font-family:var(--font-mono);font-size:12px;background:var(--terminal-bg);color:var(--on-dark);border:1px solid var(--divider-dark);padding:8px 12px;border-radius:2px;outline:none;}
.controls input:focus,.controls select:focus{border-color:var(--accent);}
.controls input{flex:1;min-width:220px;}
.ctrl-label{font-size:9px;font-weight:600;letter-spacing:.16em;text-transform:uppercase;color:var(--muted-dark);}
.chip{font-family:var(--font-mono);font-size:10px;padding:5px 12px;border:1px solid var(--divider-dark);background:transparent;color:var(--sub-dark);cursor:pointer;border-radius:2px;letter-spacing:.05em;text-transform:uppercase;}
.chip:hover{border-color:var(--accent);color:var(--on-dark);}
.chip.on{background:var(--accent);border-color:var(--accent);color:#fff;}
.counter{font-family:var(--font-mono);font-size:12px;font-weight:500;color:var(--accent-light);margin-left:auto;white-space:nowrap;}

.mechanism{margin:20px max(24px, calc((100% - 1240px) / 2 + 24px)) 8px;background:var(--deep-navy);border:1px solid var(--divider-dark);border-left:3px solid var(--accent);border-radius:2px;padding:18px 22px;}
.mechanism h2{font-family:var(--font-mono);font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.12em;color:var(--accent);margin-bottom:14px;}
.mech-grid{display:grid;grid-template-columns:1fr;gap:11px;}
.mech-item{font-size:12.5px;color:var(--sub-dark);line-height:1.6;}
.mech-item b{color:var(--on-dark);font-weight:600;}
.mech-item .art{font-family:var(--font-mono);font-size:10px;color:var(--accent-light);text-transform:uppercase;letter-spacing:.06em;}

.tbl-zone{padding:8px max(24px, calc((100% - 1240px) / 2 + 24px)) 20px;}
.reg-head{display:grid;grid-template-columns:2.4fr 1.5fr 1fr 1.1fr 64px 96px;gap:12px;padding:12px 14px;border-bottom:1px solid var(--divider-dark);}
.reg-head span{font-family:var(--font-mono);font-size:9px;font-weight:600;text-transform:uppercase;letter-spacing:.1em;color:var(--accent);}
.reg-head .r{text-align:right;}

.row{border-bottom:1px solid var(--divider-dark);}
.row-main{display:grid;grid-template-columns:2.4fr 1.5fr 1fr 1.1fr 64px 96px;gap:12px;padding:14px 14px;cursor:pointer;align-items:center;border-left:3px solid transparent;transition:background .12s ease,border-color .12s ease;}
.row-main:hover{background:rgba(188,27,29,.06);border-left-color:var(--accent);}
.row.moved .row-main{border-left:3px solid var(--accent);}

.asset-name{font-family:var(--font-sans);font-size:13.5px;font-weight:600;color:var(--on-dark);}
.asset-id{font-family:var(--font-mono);font-size:9.5px;color:var(--muted-dark);letter-spacing:.06em;margin-top:2px;}
.holder{font-size:12px;color:var(--sub-dark);max-width:260px;}
.mineral{font-family:var(--font-mono);font-size:10.5px;color:var(--sub-dark);}

.st{display:inline-block;font-family:var(--font-mono);font-size:9px;font-weight:500;letter-spacing:.06em;padding:3px 8px;border-radius:2px;text-transform:uppercase;white-space:nowrap;}
.st-actif{background:rgba(39,174,96,.12);color:var(--success);border:1px solid rgba(39,174,96,.3);}
.st-warn{background:rgba(230,126,34,.12);color:var(--warning);border:1px solid rgba(230,126,34,.3);}
.st-info{background:rgba(41,128,185,.12);color:var(--info);border:1px solid rgba(41,128,185,.3);}
.chg-flag{display:inline-block;font-family:var(--font-mono);font-size:8.5px;font-weight:600;letter-spacing:.08em;padding:2px 6px;background:var(--accent);color:#fff;border-radius:2px;text-transform:uppercase;margin-left:6px;}

.conf{font-family:var(--font-mono);font-size:9.5px;font-weight:600;text-align:right;text-transform:uppercase;letter-spacing:.05em;}
.conf-high{color:#3ddc84;} .conf-medhigh{color:#8fd14f;} .conf-med{color:var(--warning);}

.open-cell{text-align:right;}
.open-tag{font-family:var(--font-mono);font-size:9px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;white-space:nowrap;}
.open-full{color:var(--accent-light);}
.open-stub{color:var(--muted-dark);}
.open-arrow{color:inherit;margin-left:5px;}

.empty{padding:32px;text-align:center;color:var(--muted-dark);font-family:var(--font-mono);font-size:12px;}

/* ================= PROFILE VIEW (hybrid) ================= */
.p-topbar{background:var(--deep-navy);padding:12px max(24px, calc((100% - 1240px) / 2 + 24px));display:flex;gap:8px;flex-wrap:wrap;align-items:center;border-bottom:1px solid var(--divider-dark);position:sticky;top:0;z-index:20;}
.p-back{font-family:var(--font-mono);font-size:11px;padding:6px 14px;border:1px solid var(--accent);background:var(--accent);color:#fff;cursor:pointer;border-radius:2px;letter-spacing:.04em;text-transform:uppercase;font-weight:600;}
.p-back:hover{background:var(--accent-light);border-color:var(--accent-light);}
.p-topbar .lbl{font-family:var(--font-mono);font-size:9px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:var(--muted-dark);margin-left:8px;}
.tab{font-family:var(--font-mono);font-size:11px;padding:6px 14px;border:1px solid var(--divider-dark);background:transparent;color:var(--sub-dark);cursor:pointer;border-radius:2px;letter-spacing:.04em;}
.tab:hover{border-color:var(--accent);color:var(--on-dark);}
.tab.on{background:var(--accent);border-color:var(--accent);color:#fff;}

.terminal{background:var(--terminal-bg);border-bottom:3px solid var(--accent);padding:22px max(24px, calc((100% - 1240px) / 2 + 24px)) 20px;}
.t-back{font-family:var(--font-mono);font-size:10px;letter-spacing:.06em;color:var(--muted-dark);margin-bottom:14px;}
.t-name{font-family:var(--font-serif);font-size:28px;font-weight:600;color:var(--on-dark);line-height:1.15;margin-bottom:4px;}
.t-sub{font-family:var(--font-serif);font-style:italic;font-size:14px;color:var(--sub-dark);margin-bottom:16px;}
.t-ident{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px 24px;padding-top:14px;border-top:1px solid var(--divider-dark);}
.t-field .k{font-family:var(--font-mono);font-size:9px;font-weight:600;text-transform:uppercase;letter-spacing:.1em;color:var(--muted-dark);margin-bottom:3px;}
.t-field .v{font-family:var(--font-mono);font-size:12px;color:var(--on-dark);}
.t-field .v.accent{color:var(--accent-light);}
.st-des{background:rgba(39,174,96,.14);color:#3ddc84;border:1px solid rgba(39,174,96,.4);}
.st-short{background:rgba(230,126,34,.14);color:#f0964a;border:1px solid rgba(230,126,34,.4);}
.st-closed{background:rgba(41,128,185,.16);color:#4aa8e0;border:1px solid rgba(41,128,185,.45);}
.flag-tag{display:inline-block;font-family:var(--font-mono);font-size:9px;font-weight:600;letter-spacing:.08em;padding:3px 8px;border-radius:2px;text-transform:uppercase;background:rgba(136,153,187,.12);color:var(--sub-dark);border:1px solid var(--divider-dark);}

.p-editorial{background:var(--cream);}
.body-wrap{max-width:800px;margin:0 auto;padding:40px 28px 20px;}

.section{background:var(--white);border:1px solid var(--divider-light);border-left:3px solid var(--accent);border-radius:2px;padding:24px 28px;margin-bottom:22px;}
.sec-eyebrow{font-family:var(--font-mono);font-size:10px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:var(--accent);margin-bottom:8px;}
.section h2{font-family:var(--font-serif);font-size:21px;font-weight:600;color:var(--navy);line-height:1.2;margin-bottom:12px;}
:root[data-theme="dark"] .section h2{color:var(--on-dark);}
.section p{color:var(--text-dark);margin-bottom:12px;font-size:15px;line-height:1.6;}
.section p:last-child{margin-bottom:0;}

.meta-line{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;}
.conf-pill{font-family:var(--font-mono);font-size:9px;font-weight:600;letter-spacing:.06em;padding:3px 9px;border-radius:2px;text-transform:uppercase;}
.cp-high{background:rgba(39,174,96,.12);color:#1e7a3d;border:1px solid rgba(39,174,96,.3);}
.cp-medhigh{background:rgba(120,170,50,.12);color:#4a7c1f;border:1px solid rgba(120,170,50,.35);}
.cp-med{background:rgba(230,126,34,.12);color:#b5651d;border:1px solid rgba(230,126,34,.3);}
.vintage{font-family:var(--font-mono);font-size:9px;font-weight:500;letter-spacing:.05em;padding:3px 9px;border-radius:2px;text-transform:uppercase;background:var(--cream);color:var(--text-muted);border:1px solid var(--divider-light);}

.facts{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px 22px;margin:6px 0 14px;padding:14px;background:var(--cream);border-radius:2px;}
.facts .k{font-family:var(--font-mono);font-size:9px;font-weight:600;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);margin-bottom:3px;}
.facts .v{font-size:13px;color:var(--ink);}
.facts .src{font-family:var(--font-mono);font-size:10px;color:var(--text-muted);margin-top:2px;}

.txn{border-left:2px solid var(--divider-light);padding:2px 0 2px 14px;margin-bottom:12px;}
.txn.moved{border-left-color:var(--accent);}
.txn .d{font-family:var(--font-mono);font-size:11px;color:var(--accent);font-weight:600;letter-spacing:.04em;margin-bottom:2px;}
.txn .desc{font-size:13.5px;color:var(--text-dark);}
.txn .src{font-family:var(--font-mono);font-size:10px;color:var(--text-muted);margin-top:3px;}

.gaps{background:var(--navy);border-left:3px solid var(--accent);border-radius:2px;padding:22px 26px;margin-bottom:22px;}
.gaps .sec-eyebrow{color:var(--accent-light);}
.gaps h2{font-family:var(--font-serif);font-size:19px;color:var(--on-dark);margin-bottom:6px;}
.gaps .lead{font-size:13px;color:var(--sub-dark);margin-bottom:16px;}
.gap-item{border-top:1px solid var(--divider-dark);padding:12px 0;}
.gap-item:last-child{padding-bottom:0;}
.gap-q{font-size:13.5px;color:var(--on-dark);margin-bottom:5px;}
.gap-meta{display:flex;gap:10px;flex-wrap:wrap;}
.gap-layer{font-family:var(--font-mono);font-size:9px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--accent-light);}
.gap-closes{font-family:var(--font-mono);font-size:10px;color:var(--muted-dark);}

.advisory{background:var(--deep-navy);border:1px solid var(--divider-dark);border-left:3px solid var(--accent);border-radius:2px;padding:26px 28px;margin-bottom:22px;}
.advisory .sec-eyebrow{color:var(--accent-light);}
.advisory h2{font-family:var(--font-serif);font-size:19px;color:var(--on-dark);margin-bottom:10px;}
.advisory p{font-size:13.5px;color:var(--sub-dark);margin-bottom:16px;}
.advisory a.cta{display:inline-block;font-family:var(--font-mono);font-size:11px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:#fff;background:var(--accent);padding:10px 20px;border-radius:2px;text-decoration:none;}
.advisory a.cta:hover{background:var(--accent-light);}
.related{margin-top:14px;font-family:var(--font-mono);font-size:11px;color:var(--muted-dark);}
.related a{color:var(--accent-light);text-decoration:none;}

/* ================= FOOTER (shared platform) ================= */






.signoff{font-family:var(--font-serif);font-style:italic;font-size:12px;color:var(--accent-light);letter-spacing:.2em;text-transform:uppercase;text-align:center;}
.ftr-meta{max-width:1240px;margin:14px auto 0;font-family:var(--font-mono);font-size:9px;color:var(--muted-dark);line-height:1.8;}
.ftr-meta b{color:var(--sub-dark);font-weight:500;}

@media(max-width:760px){
  .masthead,.controls,.tbl-zone,.p-topbar,.terminal,footer{padding-left:18px;padding-right:18px;}
  .mechanism{margin-left:18px;margin-right:18px;}
  .body-wrap{padding:28px 16px 16px;}
  .reg-head{display:none;}
  .row-main{grid-template-columns:1fr auto;gap:5px 12px;align-items:start;}
  .row-main .holder,.row-main .mineral{grid-column:1 / -1;}
  .row-main .state-cell{grid-column:1;}
  .row-main .conf{grid-column:2;text-align:right;}
  .row-main .open-cell{grid-column:1 / -1;text-align:left;}
  .counter{margin-left:0;width:100%;}
  .ftr-cols{grid-template-columns:1fr 1fr;}
}

</style>

<!-- ==================== REGISTRY VIEW ==================== -->
<div id="view-registry">


  <div class="masthead">
    <div class="eyebrow">Ascendance Strategies | Registers</div>
    <h1>SAR Registry</h1>
    <div class="dek">The Strategic Asset Reserve of the US-DRC Strategic Partnership, rendered consultable: publicly reported designations and transactions. Select a row to open its life-cycle profile.</div>
    <div class="vintage-bar">
      <div class="vintage-item vintage-critical">LAST UPDATED: <b>16 July 2026</b></div><div class="vintage-item">NEXT SCHEDULED REVIEW: <b>Q3 2026</b></div>
      <div class="vintage-item">FULL DESIGNATED LIST: <b>NOT PUBLIC. PUBLIC ROWS ONLY</b></div>
      <div class="vintage-item">INITIAL DESIGNATION: <b>5 FEB 2026 (INAUGURAL JSC)</b></div>
      <div class="vintage-item">REPORTED LAYER: <b>REUTERS 18 FEB / 2 MAR / 21 APR 2026</b></div>
      <div class="vintage-item">REPORTED AGGREGATE: <b>~44 ASSETS (MAY 2026)</b></div>
      <div class="vintage-item">PUBLIC ROWS: <b>13</b></div>
      <div class="vintage-item">CONSULTE: <b>16 JUILLET 2026</b></div>
      <div class="vintage-item">VERSION: <b>v2.0</b></div>
    </div>
  </div>

  <div class="controls">
    <input type="text" id="q" placeholder="Search an asset, a holder, a mineral..." oninput="renderRows()">
    <span class="ctrl-label">Mineral</span>
    <select id="fMin" onchange="renderRows()"><option value="">All</option></select>
    <button class="chip" id="chipDes" onclick="toggleState('Designated')">Designated</button>
    <button class="chip" id="chipShort" onclick="toggleState('Reported shortlist')">Reported shortlist</button>
    <button class="chip" id="chipClosed" onclick="toggleState('Closed')">Closed</button>
    <button class="chip" id="chipMoved" onclick="toggleMoved()">Movement only</button>
    <button class="chip" id="chipProfile" onclick="toggleProfileOnly()">Full profile</button>
    <div class="counter" id="counter"></div>
  </div>

  <div class="mechanism">
    <h2>The mechanism</h2>
    <div class="mech-grid">
      <div class="mech-item"><span class="art">Article IV / VII &middot;</span> <b>What SAR is.</b> The DRC designates strategic assets for US-first access. Governance sits with the Joint Steering Committee.</div>
      <div class="mech-item"><span class="art">The waterfall &middot;</span> <b>US persons first.</b> A 3 month proposal window, then a 3 month negotiation window, renewable once. Roughly 6 months of exclusivity inside a 9 month total before aligned persons enter. Anything unresolved at 12 months returns to the JSC.</div>
      <div class="mech-item"><span class="art">Article IV &middot;</span> <b>The prohibition.</b> No asset may be designated if it breaches DRC domestic law or an international obligation. This is the live Rubaya and Manono test.</div>
      <div class="mech-item"><span class="art">Ownership ratchet &middot;</span> <b>Non-aligned equity cap.</b> Steps down 40, 30, 20, 10 percent across years 5, 10 and 20.</div>
      <div class="mech-item"><span class="art">Article VI &middot;</span> <b>Governance.</b> Joint Steering Committee. Inaugural meeting 5 February 2026.</div>
    </div>
  </div>

  <div class="tbl-zone">
    <div class="reg-head">
      <span>Asset</span><span>Holder</span><span>Mineral</span><span>State</span><span class="r">Conf.</span><span class="r">Profile</span>
    </div>
    <div id="rows"></div>
  </div>

  
<div style="padding: 20px max(24px, calc((100% - 1240px) / 2 + 24px));">

    <div class="ftr-meta">
      <b>Sources:</b> SPA text (Art. IV, VI, VII); JSC inaugural designation, 5 February 2026; Reuters reporting (Kasongo and Adombila, 18 Feb 2026; Adombila, 2 Mar 2026; Bonnerot, 21 Apr 2026).<br>
      <b>Reported vs designated:</b> "Offered" or "shortlisted" is not "formally designated." The State Department confirmed a list was presented on 5 February 2026 but did not disclose its contents; reported-shortlist rows carry that caveat.<br>
      <b>Boundary:</b> this Register carries publicly reported designations and transactions only. Full asset assessment, including ownership verification and risk scoring, is an advisory engagement, routed to Advisory, not a subscription tier.<br>
      <b>Record:</b> SAR Registry (Public) + Asset Profiles, v2.0 (11 July 2026), consulté 16 juillet 2026, Active. Registry at Professional tier with public preview; full profiles at Enterprise depth. Maintained by Ascendance Strategies.
    </div>
</div>
</div>

<!-- ==================== PROFILE VIEW ==================== -->
<div id="view-profile" class="hidden">

  <div class="p-topbar">
    <button class="p-back" onclick="showRegistry()">&#8592; Registry</button>
    <span class="lbl">Flagship profiles</span>
    <button class="tab" data-slug="rubaya" onclick="openProfileBySlug('rubaya')">Rubaya</button>
    <button class="tab" data-slug="manono" onclick="openProfileBySlug('manono')">Manono</button>
    <button class="tab" data-slug="chemaf" onclick="openProfileBySlug('chemaf')">Chemaf</button>
  </div>
  <div class="terminal" id="p-terminal"></div>
  <div class="p-editorial"><div class="body-wrap" id="p-body"></div></div>
  
<div style="padding: 20px max(24px, calc((100% - 1240px) / 2 + 24px));">

    <div class="ftr-meta">
      <b>Public sourcing only.</b> Every fact on this profile traces to a public source with attribution and vintage. Full asset assessment, including ownership verification and risk scoring, is an advisory engagement, not a subscription tier.<br>
      <b>Legal-buffer:</b> where an asset touches a contested title, a politically exposed person, or a sanctioned party, statements are dated, attributed to a public source, carry the presumption of innocence, and route any right-of-reply to counsel. No unpublished assertion appears here.<br>
      <b>Record:</b> SAR Asset Profiles (Public), v2.0 (11 July 2026), Enterprise depth. Destination /registers/sar-registry/[asset-slug]. Maintained by Ascendance Strategies.
    </div>
</div>
</div>



<script>

/* ---------- Registry rows (index) ---------- */
const DATA = [
  {id:"SAR-001", asset:"Kisenge manganese licenses", holder:"State", mineral:"Manganese", minerals:["Manganese"], state:"Designated", stCls:"st-actif", moved:false, conf:"HIGH", confClass:"conf-high", slug:null, flag:"clean (queued)"},
  {id:"SAR-002", asset:"Mutoshi copper-cobalt-germanium venture", holder:"Gecamines", mineral:"Cu / Co / Ge", minerals:["Copper","Cobalt","Germanium"], state:"Designated", stCls:"st-actif", moved:false, conf:"HIGH", confClass:"conf-high", slug:null, flag:"mixed"},
  {id:"SAR-003", asset:"Four gold permits", holder:"Sokimo", mineral:"Gold", minerals:["Gold"], state:"Designated", stCls:"st-actif", moved:false, conf:"HIGH", confClass:"conf-high", slug:null, flag:"clean (queued)"},
  {id:"SAR-004", asset:"Manono lithium licenses", holder:"Cominiere", mineral:"Lithium", minerals:["Lithium"], state:"Designated", stCls:"st-actif", moved:false, conf:"HIGH", confClass:"conf-high", slug:"manono", flag:"full profile"},
  {id:"SAR-005", asset:"Coltan, gold and wolframite block", holder:"Sakima", mineral:"Coltan / Au / W", minerals:["Coltan","Gold","Tungsten"], state:"Designated", stCls:"st-actif", moved:false, conf:"HIGH", confClass:"conf-high", slug:null, flag:"mixed"},
  {id:"SAR-006", asset:"Rubaya coltan mine", holder:"Private title holder (contested)", mineral:"Coltan / tantalum", minerals:["Coltan","Tantalum"], state:"Designated", stCls:"st-actif", moved:false, conf:"MED-HIGH", confClass:"conf-medhigh", slug:"rubaya", flag:"full profile"},
  {id:"SAR-007", asset:"STL germanium-gallium expansion (Lubumbashi)", holder:"STL", mineral:"Ge / Ga", minerals:["Germanium","Gallium"], state:"Reported shortlist", stCls:"st-warn", moved:false, conf:"MEDIUM", confClass:"conf-med", slug:null, flag:"clean (queued)"},
  {id:"SAR-008", asset:"Three proposed cobalt refineries", holder:"Proposed", mineral:"Cobalt (refining)", minerals:["Cobalt"], state:"Reported shortlist", stCls:"st-warn", moved:false, conf:"MEDIUM", confClass:"conf-med", slug:null, flag:"clean (queued)"},
  {id:"SAR-009", asset:"Gecamines-linked hydro projects", holder:"Gecamines", mineral:"Hydro / energy", minerals:["Energy"], state:"Reported shortlist", stCls:"st-warn", moved:false, conf:"MEDIUM", confClass:"conf-med", slug:null, flag:"clean (queued)"},
  {id:"SAR-010", asset:"Lobito Corridor, DRC segment", holder:"State / infrastructure", mineral:"Infrastructure", minerals:["Infrastructure"], state:"Reported shortlist", stCls:"st-warn", moved:false, conf:"MEDIUM", confClass:"conf-med", slug:null, flag:"clean (queued)"},
  {id:"SAR-011", asset:"Kibali South gold prospect", holder:"Kibali-adjacent", mineral:"Gold", minerals:["Gold"], state:"Reported shortlist", stCls:"st-warn", moved:false, conf:"MEDIUM", confClass:"conf-med", slug:null, flag:"clean (queued)"},
  {id:"SAR-012", asset:"Moku Beverendi gold prospect", holder:"State", mineral:"Gold", minerals:["Gold"], state:"Reported shortlist", stCls:"st-warn", moved:false, conf:"MEDIUM", confClass:"conf-med", slug:null, flag:"clean (queued)"},
  {id:"SAR-013", asset:"Chemaf Etoile and Mutoshi (Chemaf / Virtus)", holder:"Shalina / Chemaf to Virtus (51%) + Lloyds Metals (49%)", mineral:"Cu / Co", minerals:["Copper","Cobalt"], state:"Closed", stCls:"st-info", moved:true, conf:"HIGH", confClass:"conf-high", slug:"chemaf", flag:"full profile"}
];

/* ---------- Flagship profiles (public layer) ---------- */
const ASSETS = {
  rubaya:{
    slug:"rubaya", registryRow:"SAR-006", name:"Rubaya coltan mine",
    sub:"Designated as leverage: the reserve's clearest case of diplomacy before commerce.",
    ident:{operator:"Historically Societe Miniere de Bisunzu (SMB); area under de facto M23 / AFC control", province:"Masisi territory, North Kivu", minerals:"Coltan (columbite-tantalite), tin, tungsten", refs:"CAMI title, private holder (number to verify)", sarStatus:'<span class="st st-des">Designated (initial shortlist)</span>', flag:'<span class="flag-tag">Public sourcing: clean</span>'},
    sections:[
      {n:"02", eb:"Asset profile", conf:"MEDIUM", confCls:"cp-med", vin:"Baseline pre-2026 + Feb 2026 reporting",
       facts:[{k:"Location", v:"Rubaya, Masisi territory, North Kivu", src:"Public record"},{k:"Resource", v:"Coltan-rich hills; one of the world's larger coltan sources", src:"Press / UN panel, MEDIUM"},{k:"Current holder", v:"Private title (SMB historically); contested under M23 control", src:"Reuters, 18 Feb 2026"},{k:"Export route", v:"Regional routes via the eastern border; artisanal and semi-industrial", src:"Public record"}],
       body:["Rubaya sits on the coltan hills of Masisi in North Kivu, worked for decades first artisanally and later under a formalized concession. It is repeatedly described in public reporting as one of the world's most significant coltan sites, though a precise global tantalum share is not fixed in the public record and is treated here as a data gap.","License status under current control is not verifiable from public sources at this vintage and is flagged below."]},
      {n:"03", eb:"Origin and life cycle", conf:"MEDIUM", confCls:"cp-med", vin:"Historical to 2024",
       body:["The Rubaya hills were mined informally for years before the concession was formalized under Societe Miniere de Bisunzu in the 2010s, with ITSCI traceability layered on to satisfy downstream buyers. Ownership around SMB has been publicly entangled with the case of Edouard Mwangachuchu, a Congolese figure tried in 2023 on national-security charges; that matter is noted only as dated public reporting, with the presumption of innocence, and any right-of-reply routes to counsel.","During its 2024 advance across North Kivu, the M23 / AFC coalition took control of the Rubaya area, which is the event that reframes the asset's entire SAR story."]},
      {n:"04", eb:"Ownership, public layer", conf:"MEDIUM", confCls:"cp-med", vin:"Feb 2026",
       body:["The named public layer is the private concession historically operated by SMB, now overlaid by de facto M23 / AFC control of the ground. No beneficial-ownership chain is asserted here. Kinshasa itself frames Rubaya as evidence that it does not physically control every designated site, which is the honest and publicly attributable read.","Where ownership is contested, it is stated as contested and attributed. Verification of the current title beyond the public record is an advisory engagement, not a platform claim."]},
      {n:"05", eb:"Transactions", conf:"MEDIUM", confCls:"cp-med", vin:"To 2026",
       txns:[{d:"2024", desc:"M23 / AFC assumes de facto control of the Rubaya area during the North Kivu advance.", src:"Public reporting", moved:true},{d:"No SAR close", desc:"No publicly reported SAR-mechanism transaction. The asset sits in the reserve as a leverage listing, not a deal.", src:"Public record", moved:false}]},
      {n:"06", eb:"Operational status", conf:"MEDIUM", confCls:"cp-med", vin:"UN panel reporting, 2024 to 2025",
       body:["Coltan production has reportedly continued under the controlling party, with the UN Group of Experts describing revenue captured through taxation of output. Specific monthly figures cited in that reporting are treated here as reported estimates rather than verified fact. Public reporting cites a restart cost of roughly 50 to 150 million USD to bring the site to a SAR-grade operation."]},
      {n:"07", eb:"Security and conflict overlay", conf:"MEDIUM", confCls:"cp-med", vin:"HDX / ACLED / OCHA, live",
       body:["Masisi has been an active conflict theatre since the 2024 M23 advance, with large-scale displacement recorded across North Kivu in the public humanitarian datasets. This overlay draws only on the public HDX, ACLED and UN OCHA layers, never on a confidential engagement pull. The current control status is fast-moving and is flagged as a live data gap requiring a fresh pass."]},
      {n:"08", eb:"SAR and SPA status", conf:"MED-HIGH", confCls:"cp-medhigh", vin:"5 Feb 2026",
       body:["Rubaya was placed on the initial SAR shortlist presented at the inaugural Joint Steering Committee on 5 February 2026, and was included despite M23 control. Reuters reporting frames the inclusion as leverage: a sovereignty assertion and a pull to draw the United States into backing recovery of the area.","The Article IV question is live, not resolved. The prohibition on designating an asset that breaches domestic law or an international obligation sits uneasily with a designation over ground the state does not physically hold and a title held by a private party. Any US-first access under the waterfall is theoretical until control is restored."]},
      {n:"09", eb:"Current read", conf:"MEDIUM", confCls:"cp-med", vin:"11 Jul 2026",
       body:["Rubaya is the reserve's clearest example of designation as leverage rather than designation as transaction. Its near-term value in the SAR is diplomatic before it is commercial. The signal to watch is US posture on eastern DRC security, not a permit or a production number."]}
    ],
    dataGaps:[{q:"Current CAMI title status and PE / PR number under present control.", layer:"L1 Legal", closes:"CAMI extract + RCCM check"},{q:"Verified production and revenue under the controlling party.", layer:"L3 Operational", closes:"Latest UN Group of Experts report"},{q:"Precise global tantalum share attributable to Rubaya.", layer:"L3 Operational", closes:"USGS / industry supply data"},{q:"Live M23 / AFC control status.", layer:"L2 Security", closes:"ACLED + OCHA current pass"}],
    related:null
  },
  manono:{
    slug:"manono", registryRow:"SAR-004", name:"Manono lithium licenses",
    sub:"Where the reserve's US-first promise meets an incumbent Chinese interest and a live arbitration.",
    ident:{operator:"Cominiere (SOE) license anchor; JV history via Dathcom (AVZ + Cominiere); Zijin-linked interest on a disputed portion", province:"Manono territory, Tanganyika", minerals:"Lithium (spodumene), tin, tantalum", refs:"Multiple PR / PE via Cominiere and JV structures", sarStatus:'<span class="st st-des">Designated</span>', flag:'<span class="flag-tag">Public sourcing: clean</span>'},
    sections:[
      {n:"02", eb:"Asset profile", conf:"MED-HIGH", confCls:"cp-medhigh", vin:"Public reporting to 2026",
       facts:[{k:"Location", v:"Manono, Tanganyika province", src:"Public record"},{k:"Resource", v:"Among the world's largest undeveloped hard-rock lithium deposits", src:"AVZ disclosures / press, MED-HIGH"},{k:"License anchor", v:"Cominiere (state)", src:"CAMI / public filings"},{k:"Status", v:"Pre-production; development stalled by dispute", src:"Public reporting, HIGH"}],
       body:["Manono is a very large hard-rock spodumene deposit in Tanganyika, with legacy infrastructure from its colonial-era tin history. It is one of the most extensively reported undeveloped lithium assets in Africa, which is why its public backbone is clean even though its legal state is contested."]},
      {n:"03", eb:"Origin and life cycle", conf:"MED-HIGH", confCls:"cp-medhigh", vin:"Colonial era to 2026",
       body:["Manono was a major tin centre under Geomines in the colonial period. Its tailings and hard-rock spodumene were reappraised in the 2010s, and AVZ Minerals advanced the project through the Dathcom vehicle held jointly with Cominiere. A subsequent move of a southern portion toward a Zijin-linked interest triggered a dispute, and AVZ pursued ICSID arbitration against the DRC while contesting the entry.","The arc from tin town to contested lithium prize is documented across AVZ disclosures and named press reporting."]},
      {n:"04", eb:"Ownership, public layer", conf:"MEDIUM", confCls:"cp-med", vin:"2026",
       body:["The named public parties are Cominiere as the state license anchor, AVZ via Dathcom holding development rights it says were wrongly stripped, a Zijin-linked interest on a disputed southern portion, and KoBold Metals, publicly reported as positioning a US-aligned pathway. No beneficial-ownership chain is asserted. The precise license split among these parties is contested and is treated as a data gap."]},
      {n:"05", eb:"Transactions", conf:"MEDIUM", confCls:"cp-med", vin:"2023 to 2026",
       txns:[{d:"Disputed", desc:"Transfer of a southern portion toward a Zijin-linked interest, contested by AVZ.", src:"Named press / AVZ disclosures", moved:false},{d:"ICSID", desc:"AVZ arbitration against the DRC over the Manono rights. No SAR-mechanism close reported.", src:"Public arbitration record", moved:false}]},
      {n:"06", eb:"Operational status", conf:"HIGH", confCls:"cp-high", vin:"2026",
       body:["Manono is pre-production. There is no commercial output, and development has been stalled by the ownership dispute rather than by geology or grade."]},
      {n:"07", eb:"Security and conflict overlay", conf:"MEDIUM", confCls:"cp-med", vin:"2026",
       body:["Tanganyika is not an active M23 theatre, so the security overlay is materially lighter than for the Kivu assets. This section is retained for template consistency; the salient risk at Manono is legal and geopolitical, not kinetic."]},
      {n:"08", eb:"SAR and SPA status", conf:"MED-HIGH", confCls:"cp-medhigh", vin:"5 Feb 2026",
       body:["Manono is the structural-contradiction test case of the whole reserve. An asset already encumbered by a Chinese-linked interest and a live ICSID arbitration has been designated for US-first access. The international-obligation limb of the Article IV prohibition is directly in play, since arbitration exposure is itself an international-obligation question.","The publicly reported KoBold pathway is the US-aligned play, and it is the real test of whether designation can override an incumbent foothold."]},
      {n:"09", eb:"Current read", conf:"MEDIUM", confCls:"cp-med", vin:"11 Jul 2026",
       body:["Manono is where the SAR's promise collides with a pre-existing position and a live dispute. The signals to watch are the ICSID track and any Cominiere settlement, because those, not the designation itself, decide who actually builds the mine."]}
    ],
    dataGaps:[{q:"Current ICSID status or award.", layer:"L1 Legal", closes:"ICSID case docket"},{q:"Exact Cominiere / Zijin / AVZ license split.", layer:"L1 Legal", closes:"CAMI + RCCM + filings"},{q:"KoBold's formal standing on the asset.", layer:"L5 Geopolitical", closes:"Named public confirmation"},{q:"Confirmed reserve and resource tonnage.", layer:"L3 Operational", closes:"Published resource statement"}],
    related:null
  },
  chemaf:{
    slug:"chemaf", registryRow:"SAR-013", name:"Chemaf: Etoile and Mutoshi",
    sub:"The reserve's first proof of concept: distressed asset, blocked Chinese bid, US-aligned close.",
    ident:{operator:"Acquired by Virtus (51%, US-aligned) and Lloyds Metals (49%) from Shalina / Chemaf", province:"Etoile: Haut-Katanga (near Lubumbashi). Mutoshi: Lualaba (near Kolwezi)", minerals:"Copper, cobalt", refs:"License via Chemaf entities", sarStatus:'<span class="st st-closed">Closed, 13 Mar 2026</span>', flag:'<span class="flag-tag">Public sourcing: clean</span>'},
    sections:[
      {n:"02", eb:"Asset profile", conf:"MED-HIGH", confCls:"cp-medhigh", vin:"2026",
       facts:[{k:"Etoile", v:"Oxide copper-cobalt, Haut-Katanga, near Lubumbashi", src:"Public record"},{k:"Mutoshi", v:"Copper-cobalt, Lualaba, near Kolwezi", src:"Public record"},{k:"Holder post-close", v:"Virtus (51%) + Lloyds Metals (49%)", src:"Reuters, 2026, HIGH"},{k:"Export route", v:"Katangan copperbelt toward southern and Lobito routes", src:"Public record"}],
       body:["Etoile and Mutoshi are core Katangan copper-cobalt assets long operated by Chemaf. They sit on the established copperbelt with existing infrastructure, which is part of why a distressed sale drew competing bids."]},
      {n:"03", eb:"Origin and life cycle", conf:"MED-HIGH", confCls:"cp-medhigh", vin:"Long-standing to Mar 2026",
       body:["Chemaf Resources, part of the Shalina group, operated in Katanga for years with Etoile and Mutoshi as anchor assets. The company entered financial distress, and a 2024 sale toward a Chinese-linked buyer was blocked amid state pressure to avoid further Chinese consolidation of Congolese copper-cobalt. A renewed process followed.","That renewed process closed on 13 March 2026 with a US-aligned buyer, which is the event that made the asset the first SAR precedent."]},
      {n:"04", eb:"Ownership, public layer", conf:"HIGH", confCls:"cp-high", vin:"Mar 2026",
       body:["The named public layer post-close is Virtus at 51 percent, described as US-aligned, alongside Lloyds Metals at 49 percent, acquiring from Shalina and Chemaf. All parties named here are drawn from named press reporting; no beneficial-ownership chain is asserted."]},
      {n:"05", eb:"Transactions", conf:"HIGH", confCls:"cp-high", vin:"Mar to Apr 2026",
       txns:[{d:"13 Mar 2026", desc:"Virtus (51%) and Lloyds Metals (49%) acquire Chemaf Etoile and Mutoshi from Shalina / Chemaf for roughly 30 million USD in equity plus assumed debt. First reported closed transaction in the SAR thread.", src:"Reuters (Adombila, 2 Mar 2026; Bonnerot, 21 Apr 2026)", moved:true},{d:"Context", desc:"A competing Buenassa offer sat in the background of the process.", src:"Migrated Analysis", moved:false}]},
      {n:"06", eb:"Operational status", conf:"MEDIUM", confCls:"cp-med", vin:"2026",
       body:["Both assets carry copper-cobalt production affected by the period of distress, with a restart and ramp expected under the new owners. Current tonnage figures are not fixed in the public record at this vintage and are flagged below. Mutoshi also has a documented history as an artisanal-mining formalization pilot, which is relevant public context for its social profile."]},
      {n:"07", eb:"Security and conflict overlay", conf:"MEDIUM", confCls:"cp-med", vin:"2026",
       body:["Haut-Katanga and Lualaba are not M23 theatres, so the overlay is light. The salient social dynamic is artisanal cobalt activity around Kolwezi, which is a governance and human-terrain question rather than an armed-conflict one."]},
      {n:"08", eb:"SAR and SPA status", conf:"MED-HIGH", confCls:"cp-medhigh", vin:"Mar 2026",
       body:["Chemaf is the first close in the SAR thread and the reserve's clearest working example: a distressed Katangan copper-cobalt asset that a Chinese buyer had been blocked from, taken by a US-aligned acquirer at 51 percent. Whether the deal formally ran the right-of-first-offer waterfall is not publicly confirmed, so it is framed as the first close in the thread rather than a confirmed mechanism transaction.","On the ownership ratchet, a 51 percent US-aligned position sits comfortably inside the non-aligned equity caps."]},
      {n:"09", eb:"Current read", conf:"MED-HIGH", confCls:"cp-medhigh", vin:"11 Jul 2026",
       body:["Chemaf and Virtus is the model working as designed: distressed asset, blocked Chinese bid, US-aligned close. It is the template the reserve wants to repeat. The signal to watch is whether more distressed Katangan assets follow the same path, and how the integration performs."]}
    ],
    dataGaps:[{q:"Whether the deal formally used the SAR right-of-first-offer waterfall.", layer:"L1 Legal", closes:"JSC record / official confirmation"},{q:"Current production at Etoile and Mutoshi.", layer:"L3 Operational", closes:"Company disclosure / press"},{q:"Quantum of debt assumed in the close.", layer:"L2 Financial", closes:"Filing / named reporting"},{q:"Buenassa competing-offer terms.", layer:"L2 Financial", closes:"Named reporting"}],
    related:{label:"Analysis: The Fifth Model (Chemaf)", href:"<?php echo esc_url( home_url( '/us-drc-partnership/the-fifth-model-chemaf/' ) ); ?>"}
  }
};

/* ---------- Registry filters + render ---------- */
let stateFilter="All", movedOnly=false, profileOnly=false;

function buildMineralSelect(){
  const mins=Array.from(new Set(DATA.flatMap(d=>d.minerals))).sort();
  const sel=document.getElementById('fMin');
  mins.forEach(m=>{const o=document.createElement('option');o.value=m;o.textContent=m;sel.appendChild(o);});
}
function toggleState(s){stateFilter=(stateFilter===s)?"All":s;syncChips();renderRows();}
function toggleMoved(){movedOnly=!movedOnly;syncChips();renderRows();}
function toggleProfileOnly(){profileOnly=!profileOnly;syncChips();renderRows();}
function syncChips(){
  document.getElementById('chipDes').classList.toggle('on',stateFilter==="Designated");
  document.getElementById('chipShort').classList.toggle('on',stateFilter==="Reported shortlist");
  document.getElementById('chipClosed').classList.toggle('on',stateFilter==="Closed");
  document.getElementById('chipMoved').classList.toggle('on',movedOnly);
  document.getElementById('chipProfile').classList.toggle('on',profileOnly);
}
function renderRows(){
  const q=document.getElementById('q').value.trim().toLowerCase();
  const min=document.getElementById('fMin').value;
  const el=document.getElementById('rows');
  const filtered=DATA.filter(d=>{
    if(stateFilter!=="All"&&d.state!==stateFilter)return false;
    if(movedOnly&&!d.moved)return false;
    if(profileOnly&&!d.slug)return false;
    if(min&&!d.minerals.includes(min))return false;
    if(q){const hay=(d.asset+" "+d.holder+" "+d.mineral+" "+d.id).toLowerCase();if(!hay.includes(q))return false;}
    return true;
  });
  const desN=DATA.filter(d=>d.state==="Designated").length, shN=DATA.filter(d=>d.state==="Reported shortlist").length, clN=DATA.filter(d=>d.state==="Closed").length;
  document.getElementById('counter').textContent=filtered.length+" of "+DATA.length+" public rows | "+desN+" designated / "+shN+" shortlist / "+clN+" closed";
  if(filtered.length===0){el.innerHTML='<div class="empty">No public rows match this filter.</div>';return;}
  el.innerHTML=filtered.map(d=>{
    const openTag=d.slug?`<span class="open-tag open-full">Profile<span class="open-arrow">&#8594;</span></span>`:`<span class="open-tag open-stub">${d.flag==='mixed'?'Stub':'Queued'}<span class="open-arrow">&#8594;</span></span>`;
    return `
    <div class="row ${d.moved?'moved':''}">
      <div class="row-main" onclick="openProfileById('${d.id}')">
        <div><div class="asset-name">${d.asset}</div><div class="asset-id">${d.id}</div></div>
        <div class="holder">${d.holder}</div>
        <div class="mineral">${d.mineral}</div>
        <div class="state-cell"><span class="st ${d.stCls}">${d.state}</span>${d.moved?'<span class="chg-flag">Moved</span>':''}</div>
        <div class="conf ${d.confClass}">${d.conf}</div>
        <div class="open-cell">${openTag}</div>
      </div>
    </div>`;
  }).join('');
}

/* ---------- View switching ---------- */
function showRegistry(){
  document.getElementById('view-profile').classList.add('hidden');
  document.getElementById('view-registry').classList.remove('hidden');
  document.body.style.background='var(--terminal-bg)';
  window.scrollTo({top:0,behavior:'instant'});
}
function openProfileById(id){
  const row=DATA.find(d=>d.id===id);
  if(row&&row.slug){ renderProfile(ASSETS[row.slug]); }
  else { renderStub(row); }
  showProfileView();
}
function openProfileBySlug(slug){ renderProfile(ASSETS[slug]); showProfileView(); }
function showProfileView(){
  document.getElementById('view-registry').classList.add('hidden');
  document.getElementById('view-profile').classList.remove('hidden');
  document.body.style.background='var(--cream)';
  window.scrollTo({top:0,behavior:'instant'});
}

/* ---------- Profile render (flagship) ---------- */
function renderProfile(a){
  document.querySelectorAll('#view-profile .tab').forEach(t=>t.classList.toggle('on',t.dataset.slug===a.slug));
  document.getElementById('p-terminal').innerHTML=`
    <div class="t-back"><span onclick="showRegistry()" style="cursor:pointer;color:var(--sub-dark)">SAR Registry</span> / ${a.name} <span style="color:var(--muted-dark)">&middot; row ${a.registryRow}</span></div>
    <div class="eyebrow">Ascendance Strategies | Registers &middot; Asset profile</div>
    <div class="t-name">${a.name}</div>
    <div class="t-sub">${a.sub}</div>
    <div class="t-ident">
      <div class="t-field"><div class="k">Operator / holder</div><div class="v">${a.ident.operator}</div></div>
      <div class="t-field"><div class="k">Province</div><div class="v">${a.ident.province}</div></div>
      <div class="t-field"><div class="k">Mineral</div><div class="v accent">${a.ident.minerals}</div></div>
      <div class="t-field"><div class="k">Title reference</div><div class="v">${a.ident.refs}</div></div>
      <div class="t-field"><div class="k">SAR status</div><div class="v">${a.ident.sarStatus}</div></div>
      <div class="t-field"><div class="k">Sourcing</div><div class="v">${a.ident.flag}</div></div>
    </div>`;
  let html="";
  a.sections.forEach(s=>{
    html+=`<div class="section"><div class="sec-eyebrow">Section ${s.n} &middot; ${s.eb}</div><h2>${s.eb}</h2><div class="meta-line"><span class="conf-pill ${s.confCls}">Confidence ${s.conf}</span><span class="vintage">Vintage ${s.vin}</span></div>`;
    if(s.facts){html+=`<div class="facts">`;s.facts.forEach(f=>{html+=`<div><div class="k">${f.k}</div><div class="v">${f.v}</div><div class="src">${f.src}</div></div>`;});html+=`</div>`;}
    if(s.txns){s.txns.forEach(t=>{html+=`<div class="txn ${t.moved?'moved':''}"><div class="d">${t.d}</div><div class="desc">${t.desc}</div><div class="src">${t.src}</div></div>`;});}
    if(s.body){s.body.forEach(p=>{html+=`<p>${p}</p>`;});}
    html+=`</div>`;
  });
  html+=`<div class="gaps"><div class="sec-eyebrow">Section 10a &middot; Data gap register</div><h2>What is not yet closed</h2><div class="lead">The gaps are a feature. Each names the open question, the verification layer it sits in, and what would close it.</div>`;
  a.dataGaps.forEach(g=>{html+=`<div class="gap-item"><div class="gap-q">${g.q}</div><div class="gap-meta"><span class="gap-layer">${g.layer}</span><span class="gap-closes">Closes with: ${g.closes}</span></div></div>`;});
  html+=`</div>`;
  html+=advisoryBlock(a.related);
  document.getElementById('p-body').innerHTML=html;
}

/* ---------- Profile render (stub for non-flagship rows) ---------- */
function renderStub(d){
  document.querySelectorAll('#view-profile .tab').forEach(t=>t.classList.remove('on'));
  const stCls = d.state==="Designated"?"st-des":(d.state==="Closed"?"st-closed":"st-short");
  document.getElementById('p-terminal').innerHTML=`
    <div class="t-back"><span onclick="showRegistry()" style="cursor:pointer;color:var(--sub-dark)">SAR Registry</span> / ${d.asset} <span style="color:var(--muted-dark)">&middot; row ${d.id}</span></div>
    <div class="eyebrow">Ascendance Strategies | Registers &middot; Asset profile</div>
    <div class="t-name">${d.asset}</div>
    <div class="t-sub">Public identity confirmed. Full life-cycle profile is queued.</div>
    <div class="t-ident">
      <div class="t-field"><div class="k">Holder</div><div class="v">${d.holder}</div></div>
      <div class="t-field"><div class="k">Mineral</div><div class="v accent">${d.mineral}</div></div>
      <div class="t-field"><div class="k">SAR state</div><div class="v"><span class="st ${stCls}">${d.state}</span></div></div>
      <div class="t-field"><div class="k">Confidence</div><div class="v">${d.conf}</div></div>
      <div class="t-field"><div class="k">Sourcing</div><div class="v"><span class="flag-tag">${d.flag}</span></div></div>
    </div>`;
  let html=`<div class="section"><div class="sec-eyebrow">Public read</div><h2>Where this asset stands</h2><div class="meta-line"><span class="conf-pill ${d.confClass==='conf-high'?'cp-high':(d.confClass==='conf-medhigh'?'cp-medhigh':'cp-med')}">Confidence ${d.conf}</span><span class="vintage">Vintage per SAR Registry row</span></div>`;
  if(d.state==="Designated"){
    html+=`<p>This asset is on the initial designated list presented at the inaugural Joint Steering Committee on 5 February 2026. The contents of that list were not officially disclosed, so the designation is carried on multi-source public reporting rather than an official published list.</p>`;
  } else if(d.state==="Reported shortlist"){
    html+=`<p>This asset was named in the Reuters shortlist reporting of 18 February 2026 as offered or shortlisted. That is not the same as formally designated: the State Department confirmed a list was presented on 5 February but did not disclose its contents.</p>`;
  } else if(d.state==="Closed"){
    html+=`<p>This asset carries a publicly reported closed transaction. See the flagship profile for the full life cycle.</p>`;
  }
  if(d.flag==='mixed'){
    html+=`<p>Public sourcing here is mixed: the backbone is public, but the key ownership turn either sits in a confidential engagement or names a party that requires the legal-buffer reflex. The full life-cycle profile is held until the public layer can carry it cleanly, and depth routes to advisory.</p>`;
  } else {
    html+=`<p>The full ten-section life-cycle profile is queued and will be built when the public sourcing is rich enough to carry it with attribution and vintage throughout.</p>`;
  }
  html+=`</div>`;
  html+=advisoryBlock(null);
  document.getElementById('p-body').innerHTML=html;
}

function advisoryBlock(related){
  let h=`<div class="advisory"><div class="sec-eyebrow">Advisory rail</div><h2>Beyond the public layer</h2><p>This profile is built from public sources only. Full asset assessment, including ownership verification and risk scoring, is an advisory engagement, not a subscription tier.</p><a class="cta" href="<?php echo esc_url( home_url( '/advisory/' ) ); ?>">Request an assessment</a>`;
  if(related){h+=`<div class="related">Related public analysis: <a href="${related.href}">${related.label}</a></div>`;}
  h+=`</div>`;
  return h;
}

/* ---------- init ---------- */
buildMineralSelect();
renderRows();

</script>
</div>

<?php
get_footer();
