<?php
/**
 * Template Name: DRC Sovereign Rating Desk
 *
 * @package Ascendance
 */

global $ascendance_custom_seo_title, $ascendance_custom_seo_meta;
$ascendance_custom_seo_title = 'DRC Sovereign &amp; Institutional Rating | Ascendance Strategies';
$ascendance_custom_seo_meta = [
    
];

get_header();
?>

<div class="as-page-wrap ref-page-wrap">
<style>

@import url('https://cdn.jsdelivr.net/npm/@fontsource/cooper-hewitt@5.2.5/600.css');@import url('https://cdn.jsdelivr.net/npm/@fontsource/cooper-hewitt@5.2.5/700.css');
:root {
  --accent:#BC1B1D; --accent-light:#E04B4B; --navy:#0F1E35; --deep-navy:#0A1628;
  --terminal-bg:#0D1626; --mid-navy:#182D4A; --cream:#F7F4EF; --white:#FFFFFF;
  --divider-light:#E8E4DC; --divider-dark:#2A3A55; --text-primary:#1A1A2E;
  --text-dark:#3A3A4A; --text-muted:#6B6B7A; --dek-stone:#56514B; --text-on-dark:#FFFFFF;
  --text-subdued-dark:#8899BB; --text-muted-dark:#556677; --elevated:#E67E22; --active:#27AE60; --info:#2980B9;
  --font-serif:'Noto Serif',Georgia,serif; --font-sans:'Cooper Hewitt','Barlow','Roboto',sans-serif; --font-mono:'JetBrains Mono','Menlo',monospace;
  --border-radius-max:2px; --border-signature:3px;
}
:root[data-theme="dark"]{
  --cream:#17140f; --white:#221e17; --divider-light:#322c23;
  --text-primary:#f3eee4; --text-dark:#cec7b8; --text-muted:#98917f; --dek-stone:#98917f;
  --navy:#0d0b08; --deep-navy:#080604; --mid-navy:#1a1610;
}
* { box-sizing:border-box; margin:0; padding:0; }
html,body { background:var(--cream); color:var(--text-primary); font-family:var(--font-serif); line-height:1.5; -webkit-font-smoothing:antialiased; transition:background .25s ease,color .25s ease; }
a { color:inherit; text-decoration:none; }
button { cursor:pointer; font-family:inherit; border:none; background:none; }

/* platform chrome */
.plat-head{background:var(--deep-navy);border-bottom:1px solid var(--divider-dark);}
.plat-top{max-width:1240px;margin:0 auto;display:flex;align-items:center;padding:16px 22px;}
.as-lockup{display:inline-flex;flex-direction:column;gap:3px;text-decoration:none;line-height:1;--ll:20px;}
.as-lockup .ll-row{display:flex;align-items:stretch;}
.as-lockup .ll-box{width:var(--ll);height:var(--ll);background:var(--accent);color:#fff;display:flex;align-items:center;justify-content:center;font-family:var(--font-sans);font-weight:700;font-size:calc(var(--ll)*0.72);line-height:1;}
.as-lockup .ll-s{background:var(--text-on-dark);color:var(--terminal-bg);}
.as-lockup .ll-word{font-family:var(--font-sans);font-weight:700;font-size:var(--ll);letter-spacing:0.005em;color:var(--text-on-dark);height:var(--ll);display:flex;align-items:center;padding-left:calc(var(--ll)*0.05);}
.as-lockup .ll-tag{font-family:var(--font-sans);font-weight:600;font-size:calc(var(--ll)*0.275);letter-spacing:0.13em;text-transform:uppercase;color:var(--text-subdued-dark);margin-top:calc(var(--ll)*0.2);white-space:nowrap;align-self:stretch;text-align:center;}
.plat-nav{background:var(--navy);border-bottom:1px solid var(--divider-dark);display:flex;gap:26px;align-items:center;padding:0 22px;overflow-x:auto;max-width:1240px;margin:0 auto;}
.plat-nav a{font-family:var(--font-sans);font-weight:600;font-size:13px;color:var(--text-subdued-dark);text-decoration:none;padding:13px 0;border-bottom:2px solid transparent;white-space:nowrap;letter-spacing:.02em;}
.plat-nav a:hover{color:var(--text-on-dark);}
.plat-nav a.on{color:var(--accent-light);border-bottom-color:var(--accent);}
.plat-actions{margin-left:auto;display:flex;align-items:center;gap:18px;}
.plat-actions>a{padding:13px 0;}
.plat-sub{background:var(--accent);color:#fff;padding:8px 16px;border-radius:2px;}
.plat-sub:hover{color:#fff;opacity:.9;}
.plat-theme{display:inline-flex;border:1px solid var(--divider-dark);border-radius:2px;overflow:hidden;}
.plat-theme .theme-btn{width:30px;height:28px;display:flex;align-items:center;justify-content:center;background:transparent;color:var(--text-muted-dark);border:none;cursor:pointer;padding:0;}
.plat-theme .theme-btn:hover{color:var(--text-on-dark);}
.plat-theme .theme-btn.on{background:var(--text-on-dark);color:var(--terminal-bg);}
.plat-theme .theme-btn svg{width:15px;height:15px;}

.page { max-width:1240px; margin:0 auto; padding:40px 40px 96px; position:relative; }
.masthead { border-bottom:1px solid var(--divider-light); padding-bottom:28px; margin-bottom:40px; }
.eyebrow { font-family:var(--font-sans); font-size:10px; font-weight:600; letter-spacing:0.18em; text-transform:uppercase; color:var(--accent); margin-bottom:12px; }
.masthead h1 { font-family:var(--font-serif); font-size:44px; font-weight:500; color:var(--text-primary); letter-spacing:-0.01em; line-height:1.1; margin-bottom:12px; }
.dek { font-family:var(--font-serif); font-style:italic; font-size:18px; color:var(--dek-stone); max-width:780px; line-height:1.45; }
.masthead-meta { display:flex; gap:32px; margin-top:20px; font-family:var(--font-mono); font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.06em; flex-wrap:wrap; }
.masthead-meta span strong { color:var(--text-primary); font-weight:500; }

.section-signpost { border-top:2px solid var(--navy); padding-top:20px; margin-top:64px; margin-bottom:32px; }
.section-signpost .eyebrow { margin-bottom:8px; }
.section-signpost h2 { font-family:var(--font-serif); font-size:32px; font-weight:500; color:var(--text-primary); letter-spacing:-0.005em; margin-bottom:10px; }
.section-signpost .signpost-note { font-family:var(--font-serif); font-style:italic; color:var(--dek-stone); font-size:15px; max-width:720px; }

.sovereign-hero { background:var(--terminal-bg); color:#fff; padding:40px 44px 44px; border-radius:2px; margin-bottom:32px; }
.hero-grid { display:grid; grid-template-columns:380px 1fr; gap:48px; align-items:start; }
.composite-block { border-left:3px solid var(--accent); padding-left:24px; }
.composite-label { font-family:var(--font-mono); font-size:10px; font-weight:600; letter-spacing:0.18em; color:var(--accent-light); text-transform:uppercase; margin-bottom:12px; }
.composite-score { font-family:var(--font-serif); font-size:92px; font-weight:500; color:#fff; line-height:1; letter-spacing:-0.02em; }
.composite-score .of-ten { font-size:32px; color:var(--text-subdued-dark); font-weight:400; }
.composite-band { font-family:var(--font-mono); font-size:12px; font-weight:600; letter-spacing:0.12em; color:var(--accent-light); text-transform:uppercase; margin-top:14px; padding:6px 10px; border:1px solid var(--accent); display:inline-block; }
.composite-delta { font-family:var(--font-mono); font-size:12px; color:var(--text-subdued-dark); margin-top:18px; line-height:1.6; }
.composite-delta .up { color:var(--active); font-weight:500; } .composite-delta .down { color:var(--accent-light); font-weight:500; } .composite-delta strong { color:#fff; font-weight:500; }
.hero-narrative { display:flex; flex-direction:column; gap:20px; }
.terminal-eyebrow { font-family:var(--font-mono); font-size:10px; font-weight:600; letter-spacing:0.18em; color:var(--accent-light); text-transform:uppercase; }
.anchor-sentence { font-family:var(--font-serif); font-size:17px; line-height:1.5; color:#fff; }
.peer-anchors { display:grid; grid-template-columns:repeat(2,1fr); gap:12px 24px; padding:20px 0; border-top:1px solid var(--divider-dark); border-bottom:1px solid var(--divider-dark); }
.peer-item { display:flex; justify-content:space-between; font-family:var(--font-mono); font-size:12px; }
.peer-item .k { color:var(--text-muted-dark); text-transform:uppercase; letter-spacing:0.08em; } .peer-item .v { color:#fff; font-weight:500; }

.pillar-cluster { margin-top:40px; }
.cluster-header { display:flex; justify-content:space-between; align-items:baseline; padding-bottom:10px; border-bottom:1px solid var(--divider-light); margin-bottom:20px; }
.cluster-header h3 { font-family:var(--font-serif); font-size:20px; font-weight:500; color:var(--text-primary); }
.cluster-weight { font-family:var(--font-mono); font-size:12px; color:var(--text-muted); letter-spacing:0.08em; text-transform:uppercase; }
.cluster-weight strong { color:var(--accent); font-weight:600; }
.pillar-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }
.pillar-card { background:var(--white); border-left:3px solid var(--accent); padding:18px 20px 20px; border-radius:2px; transition:transform 0.15s ease; position:relative; }
.pillar-card:hover { transform:translateY(-2px); }
.pillar-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px; }
.pillar-id-name { display:flex; flex-direction:column; gap:4px; }
.pillar-id { font-family:var(--font-mono); font-size:10px; color:var(--text-muted); letter-spacing:0.1em; }
.pillar-name { font-family:var(--font-serif); font-size:15px; font-weight:500; color:var(--text-primary); line-height:1.25; }
.pillar-score-block { text-align:right; }
.pillar-score { font-family:var(--font-mono); font-size:26px; font-weight:500; color:var(--accent); line-height:1; }
.pillar-weight-badge { font-family:var(--font-mono); font-size:10px; color:var(--text-muted); letter-spacing:0.06em; margin-top:4px; }
.pillar-delta { font-family:var(--font-mono); font-size:11px; letter-spacing:0.06em; padding:3px 6px; border-radius:2px; display:inline-block; margin-bottom:8px; }
.pillar-delta.up { background:rgba(39,174,96,0.1); color:var(--active); } .pillar-delta.down { background:rgba(188,27,29,0.08); color:var(--accent); } .pillar-delta.flat { background:rgba(107,107,122,0.1); color:var(--text-muted); }
.pillar-driver { font-family:var(--font-serif); font-size:13px; color:var(--text-dark); line-height:1.4; }
.pillar-vintage { font-family:var(--font-mono); font-size:9px; color:var(--text-muted); letter-spacing:0.06em; margin-top:10px; padding-top:8px; border-top:1px solid var(--divider-light); text-transform:uppercase; display:flex; justify-content:space-between; }
.pillar-vintage .conf { color:var(--accent); font-weight:500; }

.subindex-callout { margin-top:40px; background:var(--navy); color:#fff; border-left:4px solid var(--accent); padding:28px 32px; border-radius:2px; display:grid; grid-template-columns:240px 1fr; gap:32px; align-items:center; }
.subindex-callout .score { font-family:var(--font-serif); font-size:56px; font-weight:500; color:var(--accent-light); line-height:1; }
.subindex-callout .score .ten { font-size:20px; color:var(--text-subdued-dark); }
.subindex-callout .subindex-label { font-family:var(--font-mono); font-size:10px; color:var(--accent-light); letter-spacing:0.18em; text-transform:uppercase; margin-bottom:10px; }
.subindex-callout h4 { font-family:var(--font-serif); font-size:20px; font-weight:500; color:#fff; margin-bottom:10px; }
.subindex-callout p { font-family:var(--font-serif); font-size:14px; line-height:1.55; color:var(--text-subdued-dark); }
.subindex-callout p strong { color:#fff; font-weight:500; }

.peer-bar-block,.peer-table-block,.heatmap-block { background:var(--white); border-left:3px solid var(--accent); padding:28px 32px 32px; margin-top:32px; border-radius:2px; }
.peer-bar-block h4,.peer-table-block h4,.heatmap-block h4 { font-family:var(--font-serif); font-size:18px; font-weight:500; color:var(--text-primary); margin-bottom:4px; }
.block-note { font-family:var(--font-serif); font-style:italic; font-size:13px; color:var(--dek-stone); margin-bottom:20px; line-height:1.5; }
.peer-bar-track { position:relative; height:44px; margin:44px 0 24px; }
.peer-bar-line { position:absolute; top:50%; left:0; right:0; height:2px; background:var(--divider-light); transform:translateY(-50%); }
.peer-bar-median { position:absolute; top:12px; bottom:12px; width:2px; background:var(--text-muted); left:50%; }
.peer-bar-marker { position:absolute; top:50%; transform:translate(-50%,-50%); }
.peer-bar-marker .dot { width:12px; height:12px; border-radius:50%; background:var(--text-muted); border:2px solid var(--white); box-shadow:0 0 0 1px var(--divider-light); }
.peer-bar-marker.drc .dot { width:18px; height:18px; background:var(--accent); box-shadow:0 0 0 1px var(--accent); }
.peer-bar-marker .peer-label { position:absolute; top:16px; left:50%; transform:translateX(-50%); font-family:var(--font-mono); font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.08em; white-space:nowrap; }
.peer-bar-marker.drc .peer-label { color:var(--accent); font-weight:600; font-size:11px; top:18px; }
.peer-bar-marker .peer-value { position:absolute; bottom:16px; left:50%; transform:translateX(-50%); font-family:var(--font-mono); font-size:11px; color:var(--text-dark); font-weight:500; white-space:nowrap; }
.peer-bar-marker.drc .peer-value { color:var(--accent); font-weight:600; font-size:13px; }
.peer-bar-scale { display:flex; justify-content:space-between; font-family:var(--font-mono); font-size:10px; color:var(--text-muted); letter-spacing:0.08em; text-transform:uppercase; margin-top:6px; }
.peer-bar-read { font-family:var(--font-serif); font-size:13px; color:var(--text-dark); line-height:1.5; margin-top:28px; padding-top:20px; border-top:1px solid var(--divider-light); }
.peer-bar-read strong { color:var(--text-primary); font-weight:600; }
.peer-table { width:100%; border-collapse:collapse; margin-top:16px; font-family:var(--font-mono); font-size:12px; }
.peer-table thead th { background:var(--navy); color:#fff; text-align:right; padding:12px 14px; font-weight:600; font-size:10px; letter-spacing:0.12em; text-transform:uppercase; }
.peer-table thead th:first-child { text-align:left; }
.peer-table tbody td { padding:12px 14px; border-bottom:1px solid var(--divider-light); text-align:right; color:var(--text-dark); }
.peer-table tbody td:first-child { text-align:left; font-family:var(--font-serif); font-size:14px; font-weight:500; color:var(--text-primary); }
.peer-table tbody tr.drc-row { background:rgba(188,27,29,0.04); }
.peer-table tbody tr.drc-row td { color:var(--accent); font-weight:500; }
.peer-table tbody tr.drc-row td:first-child { color:var(--accent); border-left:3px solid var(--accent); padding-left:12px; }
.peer-table-caveat { font-family:var(--font-mono); font-size:10px; color:var(--text-muted); margin-top:12px; letter-spacing:0.04em; }

.scorecard-block { margin-top:32px; background:var(--terminal-bg); color:#fff; padding:28px 32px 32px; border-radius:2px; }
.scorecard-block .terminal-eyebrow { margin-bottom:6px; }
.scorecard-block h4 { font-family:var(--font-serif); font-size:20px; font-weight:500; color:#fff; margin-bottom:6px; }
.scorecard-block .block-note { color:var(--text-subdued-dark); }
.scorecard { width:100%; border-collapse:collapse; font-family:var(--font-mono); font-size:12px; }
.scorecard thead th { border-bottom:1px solid var(--divider-dark); padding:10px 12px; text-align:right; color:var(--accent-light); font-weight:600; font-size:10px; letter-spacing:0.12em; text-transform:uppercase; }
.scorecard thead th:first-child { text-align:left; } .scorecard thead th:nth-child(2) { text-align:center; }
.scorecard tbody td { padding:8px 12px; border-bottom:1px solid rgba(42,58,85,0.5); text-align:right; color:var(--text-subdued-dark); }
.scorecard tbody td:first-child { text-align:left; color:#fff; font-family:var(--font-serif); font-size:13px; font-weight:400; }
.scorecard tbody td:nth-child(2) { text-align:center; color:var(--text-muted-dark); }
.scorecard tbody td.contribution { color:var(--accent-light); font-weight:500; }
.scorecard tbody tr.cluster-row { background:var(--mid-navy); }
.scorecard tbody tr.cluster-row td { color:var(--accent-light); font-weight:600; border-bottom:2px solid var(--divider-dark); padding:12px; }
.scorecard tbody tr.cluster-row td:first-child { text-transform:uppercase; letter-spacing:0.1em; font-family:var(--font-mono); font-size:11px; }
.scorecard tfoot td { padding:14px 12px 4px; border-top:2px solid var(--accent); color:#fff; font-weight:600; text-align:right; font-size:14px; }
.scorecard tfoot td:first-child { text-align:left; font-family:var(--font-serif); font-weight:500; }
.scorecard tfoot tr.adj td { border-top:none; padding:4px 12px; color:var(--text-subdued-dark); font-weight:400; font-size:12px; }
.scorecard tfoot tr.final td { border-top:1px solid var(--divider-dark); padding-top:10px; color:var(--accent-light); font-size:16px; }

.method-summary-inline { display:flex; align-items:baseline; justify-content:space-between; gap:20px; margin-top:16px; padding:14px 18px; background:var(--white); border-left:2px solid var(--accent); border-radius:2px; flex-wrap:wrap; }
.method-summary-inline .summary-text { font-family:var(--font-serif); font-size:13px; color:var(--text-dark); line-height:1.45; flex:1; min-width:240px; }
.method-summary-inline .summary-text strong { color:var(--text-primary); font-weight:600; }
.method-summary-inline .method-link { font-family:var(--font-mono); font-size:10px; color:var(--accent); letter-spacing:0.12em; text-transform:uppercase; padding:6px 10px; border:1px solid rgba(188,27,29,0.3); border-radius:2px; cursor:pointer; background:transparent; white-space:nowrap; }
.method-summary-inline .method-link:hover { background:var(--accent); color:#fff; }

.soe-controls { display:flex; flex-wrap:wrap; gap:8px; align-items:center; margin-bottom:20px; padding:16px 20px; background:var(--white); border-left:3px solid var(--accent); border-radius:2px; }
.filter-label { font-family:var(--font-mono); font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.12em; margin-right:8px; }
.filter-btn { font-family:var(--font-mono); font-size:11px; padding:6px 12px; border:1px solid var(--divider-light); color:var(--text-dark); background:var(--cream); text-transform:uppercase; letter-spacing:0.06em; border-radius:2px; }
.filter-btn:hover { border-color:var(--accent); color:var(--accent); }
.filter-btn.active { background:var(--accent); color:#fff; border-color:var(--accent); }
.entity-search-wrap { position:relative; flex:1; min-width:200px; max-width:280px; }
.entity-search { width:100%; font-family:var(--font-mono); font-size:11px; padding:7px 12px 7px 30px; border:1px solid var(--divider-light); background:var(--cream); color:var(--text-primary); border-radius:2px; letter-spacing:0.04em; }
.entity-search:focus { outline:none; border-color:var(--accent); background:var(--white); }
.entity-search::placeholder { color:var(--text-muted); text-transform:uppercase; letter-spacing:0.08em; font-size:10px; }
.entity-search-wrap::before { content:'\2315'; position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:13px; pointer-events:none; }
.filter-count { margin-left:auto; font-family:var(--font-mono); font-size:11px; color:var(--text-muted); }
.filter-count strong { color:var(--text-primary); font-weight:500; }

.inst-table { background:var(--white); border-radius:2px; overflow:hidden; }
.inst-row { display:grid; grid-template-columns:190px 90px 80px 60px 60px 1fr 100px 100px; gap:16px; padding:16px 20px; border-bottom:1px solid var(--divider-light); align-items:start; border-left:3px solid transparent; transition:background 0.15s; }
.inst-row:hover { background:rgba(188,27,29,0.03); border-left-color:var(--accent); }
.inst-row.hdr { background:var(--navy); color:#fff; font-family:var(--font-mono); font-size:10px; letter-spacing:0.1em; text-transform:uppercase; padding:12px 20px; border-left:3px solid var(--accent); }
.inst-row.hdr:hover { background:var(--navy); } .inst-row.hdr>div { align-self:center; }
.inst-entity { font-family:var(--font-serif); font-size:14px; font-weight:500; color:var(--text-primary); line-height:1.3; }
.inst-entity .subtitle { display:block; font-family:var(--font-mono); font-size:9px; color:var(--text-muted); font-weight:400; text-transform:uppercase; letter-spacing:0.06em; margin-top:3px; }
.inst-sector { font-family:var(--font-mono); font-size:10px; color:var(--text-dark); text-transform:uppercase; letter-spacing:0.04em; padding-top:2px; line-height:1.4; }
.inst-composite { font-family:var(--font-mono); font-size:20px; font-weight:500; color:var(--accent); line-height:1; }
.inst-prior { font-family:var(--font-mono); font-size:12px; color:var(--text-muted); padding-top:5px; }
.inst-prior .delta-up { color:var(--active); font-weight:500; } .inst-prior .delta-down { color:var(--accent); font-weight:500; } .inst-prior .delta-flat { color:var(--text-muted); }
.inst-rank { font-family:var(--font-mono); font-size:12px; color:var(--text-dark); padding-top:5px; font-weight:500; }
.inst-rank .universe { display:block; font-size:9px; color:var(--text-muted); font-weight:400; margin-top:2px; letter-spacing:0.04em; }
.inst-notes { font-family:var(--font-serif); font-size:12px; color:var(--text-dark); line-height:1.45; }
.inst-notes .leadership { display:block; font-family:var(--font-mono); font-size:9px; color:var(--text-muted); margin-bottom:3px; text-transform:uppercase; letter-spacing:0.04em; }
.inst-confidence { font-family:var(--font-mono); font-size:10px; color:var(--accent); text-transform:uppercase; letter-spacing:0.06em; padding-top:4px; font-weight:500; }
.inst-confidence.med { color:var(--elevated); } .inst-confidence.low { color:var(--text-muted); }
.inst-last-action { font-family:var(--font-mono); font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em; padding-top:4px; line-height:1.5; }
.inst-last-action strong { display:block; color:var(--text-primary); font-weight:600; font-size:11px; margin-bottom:2px; }
.inst-row.recently-changed { border-left-color:var(--accent); background:rgba(188,27,29,0.02); }
.inst-row.category-hdr { background:var(--cream); padding:20px 20px 10px; border-bottom:none; border-left:none; display:block; }
.inst-row.category-hdr:hover { background:var(--cream); }
.inst-row.category-hdr h4 { font-family:var(--font-serif); font-size:17px; font-weight:500; color:var(--text-primary); margin-bottom:4px; }
.inst-row.category-hdr .cat-eyebrow { font-family:var(--font-mono); font-size:10px; color:var(--accent); letter-spacing:0.14em; text-transform:uppercase; margin-bottom:6px; }
.inst-row.category-hdr .cat-note { font-family:var(--font-serif); font-size:12px; color:var(--text-muted); font-style:italic; }
.no-results-row { padding:32px 20px; text-align:center; font-family:var(--font-serif); font-style:italic; font-size:14px; color:var(--text-muted); border-left:3px solid var(--divider-light); display:none; }
.no-results-row.visible { display:block; }

.heatmap { width:100%; border-collapse:collapse; font-family:var(--font-mono); font-size:11px; }
.heatmap thead th { background:var(--cream); padding:10px 12px; text-align:center; color:var(--text-muted); font-weight:600; font-size:10px; letter-spacing:0.08em; text-transform:uppercase; border-bottom:2px solid var(--navy); }
.heatmap thead th:first-child { text-align:left; }
.heatmap tbody td { padding:12px 8px; text-align:center; border-bottom:1px solid var(--divider-light); color:var(--text-primary); font-weight:500; }
.heatmap tbody td:first-child { text-align:left; font-family:var(--font-serif); font-size:13px; font-weight:500; color:var(--text-primary); padding-left:12px; }
.heatmap tbody td.hm-0-2 { background:#7A1315; color:#fff; } .heatmap tbody td.hm-2-4 { background:#BC1B1D; color:#fff; } .heatmap tbody td.hm-4-6 { background:#E67E22; color:#fff; } .heatmap tbody td.hm-6-8 { background:#C8B948; color:var(--text-primary); } .heatmap tbody td.hm-8-10 { background:#27AE60; color:#fff; }
.heatmap tbody td.hm-composite { background:var(--navy); color:#fff; font-weight:600; font-size:13px; }
.heatmap-legend { display:flex; gap:12px; margin-top:16px; font-family:var(--font-mono); font-size:10px; color:var(--text-muted); letter-spacing:0.06em; text-transform:uppercase; align-items:center; flex-wrap:wrap; }
.heatmap-legend .swatch { display:inline-block; width:16px; height:12px; margin-right:6px; vertical-align:middle; }
.heatmap-legend .swatch.s02 { background:#7A1315; } .heatmap-legend .swatch.s24 { background:#BC1B1D; } .heatmap-legend .swatch.s46 { background:#E67E22; } .heatmap-legend .swatch.s68 { background:#C8B948; } .heatmap-legend .swatch.s810 { background:#27AE60; }

.sector-strip { display:grid; grid-template-columns:repeat(5,1fr); gap:12px; margin-top:24px; }
.sector-cell { background:var(--white); border-left:3px solid var(--accent); padding:14px 16px; border-radius:2px; }
.sector-cell .sec-name { font-family:var(--font-mono); font-size:10px; color:var(--text-muted); letter-spacing:0.08em; text-transform:uppercase; line-height:1.3; margin-bottom:6px; }
.sector-cell .sec-score { font-family:var(--font-mono); font-size:22px; font-weight:500; color:var(--accent); line-height:1; }
.sector-cell .sec-weight { font-family:var(--font-mono); font-size:10px; color:var(--text-muted); margin-top:4px; }
.sector-cell.tbd .sec-score { color:var(--elevated); }

.advisory-gate { margin-top:40px; padding:24px 28px; background:var(--white); border-left:3px solid var(--accent); border-radius:2px; display:flex; gap:24px; align-items:center; justify-content:space-between; }
.advisory-gate .gate-copy { font-family:var(--font-serif); font-size:14px; color:var(--text-dark); line-height:1.5; max-width:640px; }
.advisory-gate .gate-copy strong { color:var(--text-primary); font-weight:600; }
.gate-cta { font-family:var(--font-sans); font-size:11px; letter-spacing:0.14em; text-transform:uppercase; color:#fff; background:var(--accent); padding:12px 22px; border-radius:2px; white-space:nowrap; font-weight:600; }
.gate-cta:hover { background:var(--accent-light); }

.coverage-roadmap { background:var(--white); border-left:3px solid var(--accent); padding:32px 36px; margin-top:48px; border-radius:2px; }
.coverage-roadmap .roadmap-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px; gap:20px; }
.coverage-roadmap h3 { font-family:var(--font-serif); font-size:22px; font-weight:500; color:var(--text-primary); margin-bottom:6px; }
.coverage-roadmap .roadmap-metric { font-family:var(--font-mono); font-size:12px; color:var(--text-dark); letter-spacing:0.06em; text-align:right; white-space:nowrap; }
.coverage-roadmap .roadmap-metric strong { color:var(--accent); font-size:16px; font-weight:600; }
.coverage-roadmap .roadmap-intro { font-family:var(--font-serif); font-style:italic; font-size:14px; color:var(--dek-stone); margin-bottom:24px; max-width:780px; line-height:1.55; }
.roadmap-tier { margin-bottom:24px; padding-bottom:24px; border-bottom:1px solid var(--divider-light); }
.roadmap-tier:last-child { border-bottom:none; margin-bottom:0; padding-bottom:0; }
.roadmap-tier-header { display:flex; justify-content:space-between; align-items:baseline; margin-bottom:12px; gap:12px; flex-wrap:wrap; }
.roadmap-tier-header h4 { font-family:var(--font-serif); font-size:15px; font-weight:500; color:var(--text-primary); }
.roadmap-tier-header .target-cycle { font-family:var(--font-mono); font-size:10px; color:var(--accent); letter-spacing:0.1em; text-transform:uppercase; background:rgba(188,27,29,0.08); padding:4px 8px; border-radius:2px; }
.roadmap-entities { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:8px; font-family:var(--font-mono); font-size:11px; color:var(--text-dark); }
.roadmap-entity { padding:6px 10px; background:var(--cream); border-left:2px solid var(--accent); border-radius:2px; }
.roadmap-entity strong { color:var(--text-primary); font-weight:500; }
.roadmap-entity .sector-tag { display:block; font-size:9px; color:var(--text-muted); margin-top:2px; letter-spacing:0.06em; text-transform:uppercase; }

/* section nav */
.section-nav { position:sticky; top:0; z-index:50; background:var(--deep-navy); border-bottom:1px solid var(--divider-dark); }
.section-nav-inner { max-width:1240px; margin:0 auto; display:flex; align-items:center; gap:4px; padding:10px 22px; flex-wrap:wrap; }
.section-nav .nav-brand { font-family:var(--font-mono); font-size:10px; color:var(--accent-light); letter-spacing:0.14em; text-transform:uppercase; margin-right:16px; font-weight:600; white-space:nowrap; }
.section-nav a { font-family:var(--font-mono); font-size:10px; color:var(--text-subdued-dark); letter-spacing:0.1em; text-transform:uppercase; padding:6px 10px; border-radius:2px; white-space:nowrap; border-bottom:2px solid transparent; }
.section-nav a:hover { color:var(--accent-light); background:rgba(188,27,29,0.12); }
.section-nav a.active { color:var(--accent-light); border-bottom-color:var(--accent); font-weight:600; }
.section-nav .nav-spacer { flex:1; min-width:12px; }
.section-nav .nav-composite { font-family:var(--font-mono); font-size:11px; color:#fff; letter-spacing:0.06em; white-space:nowrap; padding:5px 10px; border-left:2px solid var(--accent); }
.section-nav .nav-composite strong { color:var(--accent-light); font-weight:600; font-size:13px; }
[id^="sec-"] { scroll-margin-top:64px; }

.back-to-top { position:fixed; bottom:28px; right:28px; width:44px; height:44px; background:var(--navy); color:#fff; border-radius:2px; font-size:16px; display:flex; align-items:center; justify-content:center; opacity:0; pointer-events:none; transition:all 0.2s; z-index:60; border-left:3px solid var(--accent); box-shadow:0 2px 12px rgba(15,30,53,0.18); }
.back-to-top.visible { opacity:1; pointer-events:auto; }
.back-to-top:hover { background:var(--accent); }
.read-progress { position:fixed; top:0; left:0; height:2px; background:var(--accent); z-index:70; width:0%; }

/* see also + footer */
.seealso-zone{max-width:1240px;margin:0 auto;padding:8px 40px 4px;}
.sa-head{font-family:var(--font-mono);font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:0.14em;color:var(--accent);border-bottom:1px solid var(--divider-light);padding-bottom:8px;margin:32px 0 16px;}
.seealso{display:grid;grid-template-columns:repeat(3,1fr);gap:1px;background:var(--divider-light);border:1px solid var(--divider-light);}
.seealso a{background:var(--white);padding:14px 16px;text-decoration:none;display:flex;flex-direction:column;gap:5px;border-left:3px solid var(--accent);}
.seealso a:hover{background:var(--cream);}
.seealso .sa-k{font-family:var(--font-mono);font-size:9px;font-weight:600;text-transform:uppercase;letter-spacing:0.12em;color:var(--accent);}
.seealso .sa-t{font-family:var(--font-serif);font-size:14px;color:var(--text-primary);}
footer.plat-foot{background:var(--deep-navy);border-top:1px solid var(--divider-dark);padding:40px 22px 26px;text-align:center;margin-top:40px;}
.ftr-cols{display:grid;grid-template-columns:repeat(5,1fr);gap:22px;max-width:1240px;margin:0 auto 26px;text-align:left;}
.ftr-col h6{font-family:var(--font-sans);font-weight:600;font-size:11px;letter-spacing:0.12em;text-transform:uppercase;color:var(--text-muted-dark);margin:0 0 12px;}
.ftr-col a{display:block;font-family:var(--font-sans);font-size:13px;color:var(--text-subdued-dark);text-decoration:none;padding:4px 0;}
.ftr-col a:hover{color:var(--accent-light);}
footer.plat-foot .as-lockup{margin:0 auto 14px;align-items:center;}
.signoff { text-align:center; font-family:var(--font-serif); font-style:italic; font-size:12px; letter-spacing:0.2em; color:var(--accent-light); text-transform:uppercase; }

/* drawer */
.drawer-overlay { position:fixed; inset:0; background:rgba(15,30,53,0.45); z-index:100; opacity:0; pointer-events:none; transition:opacity 0.2s; }
.drawer-overlay.open { opacity:1; pointer-events:auto; }
.drawer { position:fixed; top:0; right:0; bottom:0; width:460px; max-width:100%; background:var(--cream); z-index:101; transform:translateX(100%); transition:transform 0.25s cubic-bezier(0.4,0,0.2,1); overflow-y:auto; box-shadow:-8px 0 24px rgba(15,30,53,0.15); }
.drawer.open { transform:translateX(0); }
.drawer-header { position:sticky; top:0; background:var(--navy); color:#fff; padding:20px 28px; display:flex; justify-content:space-between; align-items:center; border-bottom:3px solid var(--accent); z-index:5; }
.drawer-eyebrow { font-family:var(--font-mono); font-size:10px; color:var(--accent-light); letter-spacing:0.16em; text-transform:uppercase; margin-bottom:4px; }
.drawer-title { font-family:var(--font-serif); font-size:20px; font-weight:500; color:#fff; line-height:1.2; max-width:320px; }
.drawer-close { background:transparent; border:1px solid rgba(255,255,255,0.3); color:#fff; width:32px; height:32px; border-radius:2px; font-size:18px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.drawer-close:hover { background:var(--accent); border-color:var(--accent); }
.drawer-body { padding:28px; }
.drawer-headline-block { background:var(--white); border-left:3px solid var(--accent); padding:20px 24px; border-radius:2px; margin-bottom:20px; }
.drawer-headline-block .lead-metric { font-family:var(--font-serif); font-size:44px; font-weight:500; color:var(--accent); line-height:1; }
.drawer-headline-block .lead-metric .of { font-size:18px; color:var(--text-muted); font-weight:400; }
.drawer-headline-block .lead-band { font-family:var(--font-mono); font-size:11px; font-weight:600; letter-spacing:0.12em; color:var(--accent); text-transform:uppercase; margin-top:10px; padding:4px 8px; border:1px solid var(--accent); display:inline-block; }
.drawer-headline-block .lead-context { font-family:var(--font-serif); font-size:13px; color:var(--text-dark); line-height:1.5; margin-top:14px; }
.drawer-section-label { font-family:var(--font-mono); font-size:10px; color:var(--accent); letter-spacing:0.14em; text-transform:uppercase; margin-bottom:12px; padding-bottom:8px; border-bottom:1px solid var(--divider-light); }
.drawer-preview-body { font-family:var(--font-serif); font-size:14px; color:var(--text-dark); line-height:1.55; margin-bottom:20px; }
.drawer-rail { background:var(--terminal-bg); color:#fff; margin:0 -28px -28px; padding:28px; }
.drawer-rail-eyebrow { font-family:var(--font-mono); font-size:10px; color:var(--accent-light); letter-spacing:0.16em; text-transform:uppercase; }
.drawer-rail-title { font-family:var(--font-serif); font-size:16px; font-weight:500; color:#fff; margin:4px 0 12px; }
.drawer-rail-preview { font-family:var(--font-serif); font-size:13px; color:var(--text-subdued-dark); line-height:1.55; margin-bottom:20px; padding-bottom:20px; border-bottom:1px solid var(--divider-dark); }
.drawer-rail-cta { font-family:var(--font-sans); font-size:11px; letter-spacing:0.14em; text-transform:uppercase; color:#fff; background:var(--accent); padding:12px 22px; border-radius:2px; font-weight:600; display:inline-block; }
.drawer-rail-cta:hover { background:var(--accent-light); }
[data-drawer-trigger] { cursor:pointer; }
.drawer-methodology-link { font-family:var(--font-mono); font-size:9px; color:var(--accent-light); letter-spacing:0.12em; text-transform:uppercase; background:transparent; border:1px solid rgba(224,75,75,0.4); padding:6px 9px; border-radius:2px; margin-right:10px; white-space:nowrap; }
.drawer-methodology-link:hover { background:var(--accent); border-color:var(--accent); color:#fff; }
.drawer-header-actions { display:flex; gap:8px; align-items:center; flex-shrink:0; }
.d-metagrid { display:grid; grid-template-columns:repeat(2,1fr); gap:1px; background:var(--divider-light); border:1px solid var(--divider-light); border-radius:2px; margin:16px 0 20px; }
.d-metacell { background:var(--white); padding:10px 12px; }
.d-metacell .k { font-family:var(--font-mono); font-size:9px; color:var(--text-muted); letter-spacing:0.1em; text-transform:uppercase; margin-bottom:3px; }
.d-metacell .v { font-family:var(--font-mono); font-size:13px; color:var(--text-primary); font-weight:500; }
.d-metacell .v.up { color:var(--active); } .d-metacell .v.down { color:var(--accent); }
.drawer-rail-gated-header { font-family:var(--font-mono); font-size:9px; color:var(--text-muted-dark); letter-spacing:0.14em; text-transform:uppercase; margin:4px 0 12px; padding-bottom:8px; border-bottom:1px solid var(--divider-dark); }
.rail-list { list-style:none; padding:0; margin:0 0 20px; }
.rail-list li { display:flex; gap:12px; padding:12px 0; border-bottom:1px solid rgba(42,58,85,0.5); }
.rail-list li:last-child { border-bottom:none; }
.rail-list .lock { color:var(--accent-light); flex-shrink:0; font-size:12px; padding-top:2px; }
.rail-list .rt { font-family:var(--font-serif); font-weight:500; color:#fff; font-size:14px; display:block; margin-bottom:3px; }
.rail-list .rd { font-family:var(--font-serif); font-size:12px; color:var(--text-subdued-dark); line-height:1.45; }
.rail-cta-copy { font-family:var(--font-serif); font-size:12px; color:var(--text-subdued-dark); line-height:1.5; margin-bottom:14px; }

@media (max-width:1120px){ .inst-row { grid-template-columns:1fr; gap:8px; } .inst-row.hdr { display:none; } }
@media (max-width:960px){ .hero-grid{grid-template-columns:1fr;gap:32px;} .pillar-grid{grid-template-columns:repeat(2,1fr);} .sector-strip{grid-template-columns:repeat(2,1fr);} .subindex-callout{grid-template-columns:1fr;} .seealso{grid-template-columns:1fr;} .ftr-cols{grid-template-columns:1fr 1fr;} }
@media (max-width:640px){ .page{padding:32px 20px 64px;} .masthead h1{font-size:32px;} .composite-score{font-size:72px;} .pillar-grid{grid-template-columns:1fr;} .advisory-gate{flex-direction:column;align-items:flex-start;} .seealso-zone{padding:8px 20px;} .drawer{width:100%;} }

</style>

<div class="wrap">
	<?php
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
	?>
</div>
</div>

<?php
get_footer();
