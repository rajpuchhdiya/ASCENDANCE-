<?php
/**
 * Template Name: CAMI Registry
 *
 * @package Ascendance
 */

global $ascendance_custom_seo_title, $ascendance_custom_seo_meta;
$ascendance_custom_seo_title = 'CAMI Registry | Ascendance Strategies';
$ascendance_custom_seo_meta = [
    
];

get_header();
?>

<div class="as-page-wrap cami-app-wrap">
<style>

@import url('https://cdn.jsdelivr.net/npm/@fontsource/cooper-hewitt@5.2.5/600.css');@import url('https://cdn.jsdelivr.net/npm/@fontsource/cooper-hewitt@5.2.5/700.css');
@import url('https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,400;0,600;1,400&family=JetBrains+Mono:wght@400;500;600&family=Barlow:wght@400;500;600&display=swap');

/* ========= DYNAMIC TOKEN LAYER (single source of truth) ========= */
:root{
  --accent:#BC1B1D;
  --accent-light:#E04B4B;
  --font-serif:'Noto Serif',Georgia,serif;
  --font-sans:'Cooper Hewitt','Barlow','Roboto',sans-serif;
  --font-mono:'JetBrains Mono',monospace;
  --warning:#E67E22;
  --success:#27AE60;
  --info:#2980B9;
}
/* DARK THEME (Data Terminal register) */
[data-theme="dark"]{
  --bg:#0D1626;
  --surface:#0F1E35;
  --surface-2:#0A1628;
  --surface-3:#182D4A;
  --divider:#2A3A55;
  --text:#FFFFFF;
  --text-sub:#8899BB;
  --text-muted:#556677;
  --row-hover:rgba(188,27,29,.06);
  --chip-bg:transparent;
  --input-bg:#0D1626;
  --st-actif-bg:rgba(39,174,96,.12);
  --st-dechu-bg:rgba(188,27,29,.12);
  --st-warn-bg:rgba(230,126,34,.12);
  --st-info-bg:rgba(41,128,185,.12);
  --st-neutral-bg:rgba(136,153,187,.1);
}
/* LIGHT THEME (Editorial register on cream) */
[data-theme="light"]{
  --bg:#F7F4EF;
  --surface:#FFFFFF;
  --surface-2:#FFFFFF;
  --surface-3:#EFEBE4;
  --divider:#E8E4DC;
  --text:#1A1A2E;
  --text-sub:#56514B;
  --text-muted:#6B6B7A;
  --row-hover:rgba(188,27,29,.04);
  --chip-bg:#FFFFFF;
  --input-bg:#F7F4EF;
  --st-actif-bg:rgba(39,174,96,.1);
  --st-dechu-bg:rgba(188,27,29,.08);
  --st-warn-bg:rgba(230,126,34,.1);
  --st-info-bg:rgba(41,128,185,.1);
  --st-neutral-bg:rgba(107,107,122,.08);
}
*{box-sizing:border-box;margin:0;padding:0;}
html{transition:none;}
body{background:var(--bg);color:var(--text);font-family:var(--font-sans);font-size:13px;transition:background .25s ease,color .25s ease;}

.masthead{background:var(--surface-2);border-bottom:1px solid var(--divider);padding:26px 36px 20px;}
.mast-top{display:flex;justify-content:space-between;align-items:flex-start;gap:20px;flex-wrap:wrap;}
.eyebrow{font-size:10px;font-weight:600;letter-spacing:.18em;text-transform:uppercase;color:var(--accent);margin-bottom:10px;}
.masthead h1{font-family:var(--font-serif);font-size:30px;font-weight:600;color:var(--text);margin-bottom:6px;}
.dek{font-family:var(--font-serif);font-style:italic;font-size:14px;color:var(--text-sub);}

/* THEME TOGGLE */
.theme-toggle{display:flex;gap:2px;border:1px solid var(--divider);border-radius:2px;padding:2px;background:var(--input-bg);}
.theme-btn{font-family:var(--font-mono);font-size:10px;padding:6px 12px;background:transparent;color:var(--text-muted);border:none;cursor:pointer;border-radius:1px;letter-spacing:.05em;text-transform:uppercase;transition:all .15s;}
.theme-btn:hover{color:var(--text);}
.theme-btn.on{background:var(--accent);color:#fff;}

.vintage-bar{display:flex;gap:24px;flex-wrap:wrap;margin-top:16px;padding-top:14px;border-top:1px solid var(--divider);}
.vintage-item{font-family:var(--font-mono);font-size:10px;color:var(--text-muted);letter-spacing:.04em;}
.vintage-item b{color:var(--accent-light);font-weight:500;}
.vintage-critical{border-left:3px solid var(--accent);padding-left:10px;}

/* TABS */
.tabs{display:flex;background:var(--surface);border-bottom:1px solid var(--divider);overflow-x:auto;}
.tab{font-family:var(--font-sans);font-size:11px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:var(--text-muted);padding:14px 22px;cursor:pointer;white-space:nowrap;border-right:1px solid var(--divider);transition:all .15s;}
.tab:hover{color:var(--text);}
.tab.on{color:var(--accent);border-bottom:2px solid var(--accent);margin-bottom:-1px;}

.panel{display:none;}
.panel.on{display:block;}

.controls{background:var(--surface);border-bottom:1px solid var(--divider);padding:16px 36px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;position:sticky;top:0;z-index:10;}
.controls input,.controls select{font-family:var(--font-mono);font-size:12px;background:var(--input-bg);color:var(--text);border:1px solid var(--divider);padding:8px 12px;border-radius:2px;outline:none;}
.controls input:focus,.controls select:focus{border-color:var(--accent);}
.controls input{flex:1;min-width:240px;}
.ctrl-label{font-size:9px;font-weight:600;letter-spacing:.16em;text-transform:uppercase;color:var(--text-muted);}
.counter{font-family:var(--font-mono);font-size:12px;font-weight:500;color:var(--accent-light);margin-left:auto;white-space:nowrap;}
.chip{font-family:var(--font-mono);font-size:10px;padding:5px 12px;border:1px solid var(--divider);background:var(--chip-bg);color:var(--text-sub);cursor:pointer;border-radius:2px;letter-spacing:.05em;}
.chip:hover{border-color:var(--accent);color:var(--text);}
.chip.on{background:var(--accent);border-color:var(--accent);color:#fff;}

.tbl-zone{padding:0 36px 20px;overflow-x:auto;}
table{width:100%;border-collapse:collapse;font-size:12px;}
thead th{font-family:var(--font-mono);font-size:9px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:var(--accent);background:var(--bg);padding:12px 10px;text-align:left;border-bottom:1px solid var(--divider);position:sticky;top:62px;cursor:pointer;user-select:none;white-space:nowrap;z-index:5;}
thead th:hover{color:var(--accent-light);}
tbody tr{border-bottom:1px solid var(--divider);cursor:pointer;}
tbody tr:hover{background:var(--row-hover);}
tbody tr.changed{border-left:3px solid var(--accent);}
td{padding:9px 10px;color:var(--text);vertical-align:top;}
td.mono{font-family:var(--font-mono);font-size:11px;color:var(--text-sub);}
td.holder{font-weight:500;max-width:260px;}
td.holder:hover{color:var(--accent);}
td.right-no{font-family:var(--font-mono);font-weight:500;color:var(--accent-light);}
td.loc{font-family:var(--font-mono);font-size:10px;color:var(--text-sub);max-width:230px;}

.st{display:inline-block;font-family:var(--font-mono);font-size:9px;font-weight:500;letter-spacing:.06em;padding:3px 8px;border-radius:2px;text-transform:uppercase;white-space:nowrap;}
.st-actif{background:var(--st-actif-bg);color:var(--success);border:1px solid rgba(39,174,96,.3);}
.st-dechu{background:var(--st-dechu-bg);color:var(--accent-light);border:1px solid rgba(188,27,29,.35);}
.st-warn{background:var(--st-warn-bg);color:var(--warning);border:1px solid rgba(230,126,34,.3);}
.st-info{background:var(--st-info-bg);color:var(--info);border:1px solid rgba(41,128,185,.3);}
.st-neutral{background:var(--st-neutral-bg);color:var(--text-sub);border:1px solid rgba(136,153,187,.25);}
.chg-flag{display:inline-block;font-family:var(--font-mono);font-size:9px;font-weight:600;letter-spacing:.08em;padding:3px 8px;background:var(--accent);color:#fff;border-radius:2px;text-transform:uppercase;}
.chg-detail{font-family:var(--font-mono);font-size:9px;color:var(--text-sub);display:block;margin-top:4px;}

.pager{display:flex;gap:6px;align-items:center;padding:16px 36px;background:var(--surface);border-top:1px solid var(--divider);flex-wrap:wrap;}
.pg{font-family:var(--font-mono);font-size:11px;padding:6px 12px;border:1px solid var(--divider);background:var(--chip-bg);color:var(--text-sub);cursor:pointer;border-radius:2px;}
.pg:hover{border-color:var(--accent);color:var(--text);}
.pg.cur{background:var(--accent);border-color:var(--accent);color:#fff;}
.pg-info{font-family:var(--font-mono);font-size:10px;color:var(--text-muted);margin-left:12px;}

/* EXPLORE PANEL */
.explore-zone{padding:28px 36px 40px;}
.explore-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px;}
.exp-card{background:var(--surface);border:1px solid var(--divider);border-left:3px solid var(--accent);padding:20px 22px;}
.exp-title{font-size:10px;font-weight:600;letter-spacing:.15em;text-transform:uppercase;color:var(--text-muted);margin-bottom:14px;}
.bar-row{display:flex;align-items:center;gap:10px;margin-bottom:7px;cursor:pointer;}
.bar-row:hover .bar-label{color:var(--accent);}
.bar-label{font-size:11px;color:var(--text);width:150px;flex-shrink:0;transition:color .15s;}
.bar-track{flex:1;background:var(--surface-3);height:14px;border-radius:1px;overflow:hidden;}
.bar-fill{height:100%;background:var(--accent);}
.bar-count{font-family:var(--font-mono);font-size:11px;color:var(--text-muted);width:44px;text-align:right;}

/* DETAIL DRAWER */
.drawer-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:100;}
.drawer-overlay.on{display:block;}
.drawer{position:fixed;top:0;right:0;width:min(520px,92vw);height:100%;background:var(--surface);border-left:3px solid var(--accent);z-index:101;transform:translateX(100%);transition:transform .25s ease;overflow-y:auto;padding:28px 30px;}
.drawer.on{transform:translateX(0);}
.drawer-close{position:absolute;top:20px;right:24px;font-family:var(--font-mono);font-size:18px;color:var(--text-muted);cursor:pointer;background:none;border:none;}
.drawer h2{font-family:var(--font-serif);font-size:22px;color:var(--text);margin-bottom:4px;padding-right:30px;}
.drawer-sub{font-family:var(--font-mono);font-size:11px;color:var(--accent-light);margin-bottom:20px;}
.drawer-stats{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px;}
.drawer-stat{background:var(--bg);border:1px solid var(--divider);padding:12px 14px;border-radius:2px;}
.drawer-stat .lbl{font-size:9px;letter-spacing:.12em;text-transform:uppercase;color:var(--text-muted);margin-bottom:4px;}
.drawer-stat .val{font-family:var(--font-mono);font-size:18px;font-weight:500;color:var(--text);}
.drawer-stat .val.red{color:var(--accent);}
.drawer-section-title{font-size:10px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:var(--text-muted);margin:20px 0 10px;border-bottom:1px solid var(--divider);padding-bottom:6px;}
.dr-titre{font-family:var(--font-mono);font-size:11px;padding:8px 0;border-bottom:1px solid var(--divider);display:flex;justify-content:space-between;gap:10px;}
.dr-titre .rn{color:var(--accent-light);font-weight:500;}
.dr-field{margin-bottom:10px;}
.dr-field .k{font-size:9px;letter-spacing:.1em;text-transform:uppercase;color:var(--text-muted);}
.dr-field .v{font-size:13px;color:var(--text);margin-top:2px;}
.dr-note{background:var(--st-dechu-bg);border-left:2px solid var(--accent);padding:10px 12px;font-size:11px;color:var(--text-sub);margin-top:10px;line-height:1.5;}
.dr-advisory{margin-top:22px;padding:16px 18px;background:var(--surface-2);border-left:3px solid var(--accent);border-radius:2px;}
.dr-adv-eyebrow{font-family:var(--font-mono);font-size:9px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:var(--accent-light);margin-bottom:6px;}
.dr-adv-title{font-family:var(--font-serif);font-size:16px;color:var(--text);margin-bottom:6px;}
.dr-adv-text{font-size:12px;color:var(--text-sub);line-height:1.55;margin-bottom:14px;}
.dr-adv-cta{display:inline-block;font-family:var(--font-mono);font-size:10px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:#fff;background:var(--accent);padding:9px 16px;border-radius:2px;text-decoration:none;}
.dr-adv-cta:hover{background:var(--accent-light);}

footer{background:var(--surface-2);border-top:1px solid var(--divider);padding:26px 36px;text-align:center;}
.signoff{font-family:var(--font-serif);font-style:italic;font-size:12px;color:var(--accent);letter-spacing:.2em;text-transform:uppercase;}
.ftr-meta{font-family:var(--font-mono);font-size:9px;color:var(--text-muted);margin-top:10px;line-height:1.7;}

/* AS monogram lockup (canonical brand logo) */
.as-lockup{display:inline-flex;flex-direction:column;gap:3px;text-decoration:none;line-height:1;--ll:20px;margin-bottom:12px;}
.as-lockup .ll-row{display:flex;align-items:stretch;}
.as-lockup .ll-box{width:var(--ll);height:var(--ll);background:var(--accent);color:#fff;display:flex;align-items:center;justify-content:center;font-family:var(--font-sans);font-weight:700;font-size:calc(var(--ll)*0.72);line-height:1;}
.as-lockup .ll-s{background:var(--text);color:var(--bg);}
.as-lockup .ll-word{font-family:var(--font-sans);font-weight:700;font-size:var(--ll);letter-spacing:0.005em;color:var(--text);height:var(--ll);display:flex;align-items:center;padding-left:calc(var(--ll)*0.05);}
.as-lockup .ll-tag{font-family:var(--font-sans);font-weight:600;font-size:calc(var(--ll)*0.275);letter-spacing:0.13em;text-transform:uppercase;color:var(--text-sub);margin-top:calc(var(--ll)*0.2);white-space:nowrap;align-self:stretch;text-align:center;}
footer .as-lockup{margin:0 auto 14px;align-items:center;}
/* platform primary nav (reach other categories) */
.plat-nav{background:var(--surface-2);border-bottom:1px solid var(--divider);display:flex;gap:26px;align-items:center;padding:0 36px;overflow-x:auto;}
.plat-nav a{font-family:var(--font-sans);font-weight:600;font-size:13px;color:var(--text-sub);text-decoration:none;padding:13px 0;border-bottom:2px solid transparent;white-space:nowrap;letter-spacing:.02em;}
.plat-nav a:hover{color:var(--text);}
.plat-nav a.on{color:var(--accent);border-bottom-color:var(--accent);}
.plat-nav .plat-actions{margin-left:auto;display:flex;align-items:center;gap:18px;}
.plat-nav .plat-actions a{padding:13px 0;border-bottom:none;}
.plat-nav .plat-sub{background:var(--accent);color:#fff;padding:8px 16px;border-radius:2px;}
.plat-nav .plat-sub:hover{color:#fff;opacity:.9;}
.plat-theme{display:inline-flex;border:1px solid var(--divider);border-radius:2px;overflow:hidden;}
.plat-theme .theme-btn{width:30px;height:28px;display:flex;align-items:center;justify-content:center;background:transparent;color:var(--text-muted);border:none;cursor:pointer;padding:0;}
.plat-theme .theme-btn:hover{color:var(--text);}
.plat-theme .theme-btn.on{background:var(--text);color:var(--bg);}
.plat-theme .theme-btn svg{width:15px;height:15px;}
/* primary footer columns */
.ftr-cols{display:grid;grid-template-columns:repeat(5,1fr);gap:22px;max-width:1080px;margin:0 auto 26px;text-align:left;}
.ftr-col h6{font-family:var(--font-sans);font-weight:600;font-size:11px;letter-spacing:0.12em;text-transform:uppercase;color:var(--text-muted);margin:0 0 12px;}
.ftr-col a{display:block;font-family:var(--font-sans);font-size:13px;color:var(--text-sub);text-decoration:none;padding:4px 0;}
.ftr-col a:hover{color:var(--accent);}
@media(max-width:820px){.ftr-cols{grid-template-columns:1fr 1fr;}}

</style>

<div class="cami-app"></div>
</div>

<?php
get_footer();
