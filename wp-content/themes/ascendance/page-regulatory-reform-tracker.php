<?php
/**
 * Template Name: Regulatory Reform Tracker
 *
 * @package Ascendance
 */

get_header();
?>
<div class="as-page-wrap ref-page-wrap">
<style>

  @import url('https://cdn.jsdelivr.net/npm/@fontsource/cooper-hewitt@5.2.5/600.css');@import url('https://cdn.jsdelivr.net/npm/@fontsource/cooper-hewitt@5.2.5/700.css');
  :root{
    --accent:#BC1B1D; --accent-light:#E04B4B;
    --navy:#0F1E35; --deep-navy:#0A1628; --terminal-bg:#0D1626; --mid-navy:#182D4A;
    --divider-dark:#2A3A55;
    --text-on-dark:#FFFFFF; --text-subdued:#8899BB; --text-muted-dark:#556677;
    --st-delivered:#27AE60; --st-progress:#2980B9; --st-partial:#E67E22;
    --st-gap:#BC1B1D; --st-muted:#8899BB; --st-unverified:#556677;
    --font-serif:'Noto Serif',Georgia,serif;
    --font-sans:'Cooper Hewitt','Barlow','Roboto','Helvetica Neue',sans-serif;
    --font-mono:'JetBrains Mono','SF Mono',Consolas,monospace;
  }
  /* LIGHT THEME (Editorial-on-cream mapping of the terminal palette) */
  :root[data-theme="light"]{
    --navy:#FFFFFF; --deep-navy:#EFEBE4; --terminal-bg:#F7F4EF; --mid-navy:#FFFFFF;
    --divider-dark:#E8E4DC;
    --text-on-dark:#1A1A2E; --text-subdued:#56514B; --text-muted-dark:#7A7568;
    --st-muted:#7A7568; --st-unverified:#9A9384;
  }
  *{box-sizing:border-box;margin:0;padding:0}
  body{background:var(--terminal-bg);color:var(--text-on-dark);font-family:var(--font-sans);font-size:14px;line-height:1.5;-webkit-font-smoothing:antialiased;transition:background .25s ease,color .25s ease;}
  .wrap{max-width:1240px;margin:0 auto;padding:28px 22px 64px;}
  .eyebrow{font-family:var(--font-sans);font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.18em;color:var(--accent);}
  h1{font-family:var(--font-serif);font-weight:600;font-size:30px;margin:6px 0 4px;color:var(--text-on-dark);}
  .subtitle{font-family:var(--font-serif);font-style:italic;color:var(--text-subdued);font-size:14px;}
  .frontmatter{display:flex;flex-wrap:wrap;gap:0 24px;margin-top:16px;padding:12px 16px;border-left:3px solid var(--accent);background:var(--deep-navy);font-family:var(--font-mono);font-size:11px;}
  .frontmatter span{color:var(--text-muted-dark);}
  .frontmatter span b{color:var(--text-on-dark);font-weight:500;}
  section{margin-top:26px;}
  .sec-head::before{content:':: ';color:var(--text-muted-dark);}
  .sec-head{font-family:var(--font-mono);font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:0.14em;color:var(--accent);border-bottom:1px solid var(--divider-dark);padding-bottom:8px;margin-bottom:16px;}
  .sub-head{font-family:var(--font-mono);font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.14em;color:var(--text-subdued);margin:22px 0 8px;}
  .finding{background:var(--navy);border-left:4px solid var(--accent);padding:20px 22px;border-radius:2px;}
  .finding .ft{font-family:var(--font-mono);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.12em;color:var(--accent-light);margin-bottom:10px;}
  .finding p{font-size:15px;line-height:1.6;color:var(--text-on-dark);max-width:96ch;}
  .finding p+p{margin-top:10px;color:var(--text-subdued);font-size:13.5px;}
  .finding .big{font-family:var(--font-mono);font-weight:700;color:var(--accent-light);}
  .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:1px;background:var(--divider-dark);border:1px solid var(--divider-dark);}
  .stat{border-left:3px solid var(--accent);background:var(--mid-navy);padding:14px 16px;border-radius:2px;}
  .stat .label{font-family:var(--font-mono);font-size:9.5px;text-transform:uppercase;letter-spacing:0.12em;color:var(--text-muted-dark);}
  .stat .value{font-family:var(--font-mono);font-weight:700;font-size:23px;color:var(--accent-light);margin-top:6px;line-height:1.05;}
  .stat .value.sm{font-size:14px;}
  .stat .note{font-family:var(--font-mono);font-size:10px;color:var(--text-subdued);margin-top:6px;}
  .clock{background:var(--navy);border-left:4px solid var(--accent);padding:16px 20px;border-radius:2px;}
  .clock-row{display:flex;flex-wrap:wrap;gap:6px 40px;padding:7px 0;border-bottom:1px solid var(--divider-dark);}
  .clock-row:last-child{border-bottom:none;}
  .clock-row .k{font-family:var(--font-mono);font-size:11px;text-transform:uppercase;letter-spacing:0.1em;color:var(--accent);min-width:150px;}
  .clock-row .v{font-family:var(--font-mono);font-size:12px;color:var(--text-on-dark);}
  .clock-row .v b{color:var(--accent-light);}
  table{width:100%;border-collapse:collapse;font-size:12px;}
  th{font-family:var(--font-mono);font-size:9px;font-weight:600;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-muted-dark);text-align:left;padding:8px 9px;border-bottom:1px solid var(--divider-dark);vertical-align:bottom;}
  td{padding:11px 9px;border-bottom:1px solid var(--divider-dark);vertical-align:top;color:var(--text-on-dark);}
  tbody tr:nth-child(even) td{background:rgba(136,153,187,0.05);}
  tr:hover td{background:rgba(188,27,29,0.06);}
  td.ob{font-family:var(--font-mono);font-size:10.5px;color:var(--text-subdued);white-space:nowrap;}
  td.trig{font-family:var(--font-mono);font-size:10.5px;color:var(--text-subdued);white-space:nowrap;}
  td.obl{max-width:260px;}
  td.real{max-width:360px;color:var(--text-subdued);font-size:11.5px;}
  td.real b{color:var(--text-on-dark);font-weight:600;}
  .lb{color:var(--accent);font-family:var(--font-mono);font-weight:700;font-size:11px;}
  .conf{font-family:var(--font-mono);font-size:10px;color:var(--text-subdued);white-space:nowrap;}
  .badge::before{content:'';display:inline-block;width:6px;height:6px;border-radius:1px;background:currentColor;margin-right:5px;vertical-align:baseline;}
  .badge{font-family:var(--font-mono);font-size:9px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;padding:3px 6px;border-radius:2px;border:1px solid;white-space:nowrap;display:inline-block;}
  .b-delivered{color:var(--st-delivered);border-color:var(--st-delivered);}
  .b-progress{color:var(--st-progress);border-color:var(--st-progress);}
  .b-partial{color:var(--st-partial);border-color:var(--st-partial);}
  .b-gap{color:var(--st-gap);border-color:var(--st-gap);}
  .b-muted{color:var(--st-muted);border-color:var(--st-muted);}
  .b-unv{color:var(--st-unverified);border-color:var(--st-unverified);border-style:dashed;}
  .crux{background:var(--deep-navy);border-left:4px solid var(--accent);padding:6px 18px 14px;border-radius:2px;}
  .crux .verdict-line{font-family:var(--font-mono);font-size:12.5px;color:var(--accent-light);padding:12px 0 4px;font-weight:600;}
  .queue{background:var(--deep-navy);border-left:3px solid var(--accent);padding:14px 18px;border-radius:2px;}
  .queue ol{margin:0;padding-left:20px;}
  .queue li{font-size:12px;color:var(--text-subdued);padding:3px 0;font-family:var(--font-sans);}
  .queue li b{color:var(--text-on-dark);font-family:var(--font-mono);font-size:11px;}
  .legend{font-family:var(--font-mono);font-size:10px;color:var(--text-muted-dark);margin-top:12px;}
  .provenance{display:flex;flex-wrap:wrap;justify-content:space-between;gap:6px 24px;margin-top:40px;padding-top:14px;border-top:1px solid var(--divider-dark);font-family:var(--font-mono);font-size:10px;color:var(--text-muted-dark);}
  .signoff{font-family:var(--font-serif);font-style:italic;font-size:12px;text-transform:uppercase;letter-spacing:0.2em;color:var(--accent);text-align:center;margin-top:48px;}
  .seealso{display:grid;grid-template-columns:repeat(3,1fr);gap:1px;background:var(--divider-dark);border:1px solid var(--divider-dark);}
  .seealso a{background:var(--navy);padding:14px 16px;text-decoration:none;display:flex;flex-direction:column;gap:5px;border-left:3px solid var(--accent);transition:background .15s ease;}
  .seealso a:hover{background:var(--mid-navy);}
  .seealso .sa-k{font-family:var(--font-mono);font-size:9px;font-weight:600;text-transform:uppercase;letter-spacing:0.12em;color:var(--accent-light);}
  .seealso .sa-t{font-family:var(--font-serif);font-size:14px;color:var(--text-on-dark);}
  /* platform chrome */
  .plat-head{background:var(--deep-navy);border-bottom:1px solid var(--divider-dark);}
  .plat-top{max-width:1240px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;gap:20px;padding:16px 22px;}
  .as-lockup{display:inline-flex;flex-direction:column;gap:3px;text-decoration:none;line-height:1;--ll:20px;}
  .as-lockup .ll-row{display:flex;align-items:stretch;}
  .as-lockup .ll-box{width:var(--ll);height:var(--ll);background:var(--accent);color:#fff;display:flex;align-items:center;justify-content:center;font-family:var(--font-sans);font-weight:700;font-size:calc(var(--ll)*0.72);line-height:1;}
  .as-lockup .ll-s{background:var(--text-on-dark);color:var(--terminal-bg);}
  .as-lockup .ll-word{font-family:var(--font-sans);font-weight:700;font-size:var(--ll);letter-spacing:0.005em;color:var(--text-on-dark);height:var(--ll);display:flex;align-items:center;padding-left:calc(var(--ll)*0.05);}
  .as-lockup .ll-tag{font-family:var(--font-sans);font-weight:600;font-size:calc(var(--ll)*0.275);letter-spacing:0.13em;text-transform:uppercase;color:var(--text-subdued);margin-top:calc(var(--ll)*0.2);white-space:nowrap;align-self:stretch;text-align:center;}
  .plat-nav{background:var(--navy);border-bottom:1px solid var(--divider-dark);display:flex;gap:26px;align-items:center;padding:0 22px;overflow-x:auto;max-width:1240px;margin:0 auto;}
  .plat-nav a{font-family:var(--font-sans);font-weight:600;font-size:13px;color:var(--text-subdued);text-decoration:none;padding:13px 0;border-bottom:2px solid transparent;white-space:nowrap;letter-spacing:.02em;}
  .plat-nav a:hover{color:var(--text-on-dark);}
  .plat-nav a.on{color:var(--accent);border-bottom-color:var(--accent);}
  .plat-actions{margin-left:auto;display:flex;align-items:center;gap:18px;}
  .plat-actions>a{padding:13px 0;border-bottom:none;}
  .plat-sub{background:var(--accent);color:#fff;padding:8px 16px;border-radius:2px;}
  .plat-sub:hover{color:#fff;opacity:.9;}
  .plat-theme{display:inline-flex;border:1px solid var(--divider-dark);border-radius:2px;overflow:hidden;}
  .plat-theme .theme-btn{width:30px;height:28px;display:flex;align-items:center;justify-content:center;background:transparent;color:var(--text-muted-dark);border:none;cursor:pointer;padding:0;}
  .plat-theme .theme-btn:hover{color:var(--text-on-dark);}
  .plat-theme .theme-btn.on{background:var(--text-on-dark);color:var(--terminal-bg);}
  .plat-theme .theme-btn svg{width:15px;height:15px;}
  footer.plat-foot{background:var(--deep-navy);border-top:1px solid var(--divider-dark);padding:40px 22px 26px;text-align:center;}
  .ftr-cols{display:grid;grid-template-columns:repeat(5,1fr);gap:22px;max-width:1240px;margin:0 auto 26px;text-align:left;}
  .ftr-col h6{font-family:var(--font-sans);font-weight:600;font-size:11px;letter-spacing:0.12em;text-transform:uppercase;color:var(--text-muted-dark);margin:0 0 12px;}
  .ftr-col a{display:block;font-family:var(--font-sans);font-size:13px;color:var(--text-subdued);text-decoration:none;padding:4px 0;}
  .ftr-col a:hover{color:var(--accent);}
  footer.plat-foot .as-lockup{margin:0 auto 14px;align-items:center;}
  /* browse controls */
  .trk-controls{position:sticky;top:0;z-index:15;background:var(--deep-navy);border-bottom:1px solid var(--divider-dark);padding:12px 36px;display:flex;gap:8px;flex-wrap:wrap;align-items:center;}
  .trk-controls .lab{font-family:var(--font-mono);font-size:9px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:var(--text-muted-dark);margin-right:2px;}
  .trk-chip{font-family:var(--font-mono);font-size:10px;padding:5px 11px;border:1px solid var(--divider-dark);background:transparent;color:var(--text-subdued);cursor:pointer;border-radius:2px;letter-spacing:.04em;text-transform:uppercase;display:inline-flex;align-items:center;gap:6px;}
  .trk-chip:hover{border-color:var(--accent);color:var(--text-on-dark);}
  .trk-chip.on{background:var(--accent);border-color:var(--accent);color:#fff;}
  .trk-chip .n{font-weight:700;opacity:.85;}
  .trk-count{font-family:var(--font-mono);font-size:11px;color:var(--accent-light);margin-left:auto;white-space:nowrap;}
  tr.trk-hide{display:none;}
  td.obl a,td.real a,.verdict-line a{color:var(--accent-light);text-decoration:none;border-bottom:1px dotted var(--divider-dark);}
  td.obl a:hover,td.real a:hover{border-bottom-color:var(--accent-light);}
  .table-empty{padding:20px 14px;font-family:var(--font-mono);font-size:11px;color:var(--text-muted-dark);}
  @media(max-width:820px){.stats{grid-template-columns:repeat(2,1fr);gap:1px;}.wrap{padding:20px 14px 50px;}td.obl,td.real{max-width:none;}.ftr-cols{grid-template-columns:1fr 1fr;}.seealso{grid-template-columns:1fr;}.trk-controls{padding:12px 18px;}.trk-count{margin-left:0;width:100%;}}

</style>
<div class="wrap">
  <header>
    <div class="eyebrow">Registers &middot; SPA Obligations &middot; Professional</div>
    <h1>Regulatory Reform Tracker</h1>
    <div class="subtitle">Every SPA obligation, both parties, held to the treaty text, clause by clause.</div>
    <div class="frontmatter">
      <span>ASSET <b>AS-REF</b></span>
      <span>CLASS <b>Tracker</b></span>
      <span>VERSION <b>v2.0</b></span>
      <span>BASIS <b>Bilateral, 29 obligations, 12 articles</b></span>
      <span>LAST UPDATED <b>16 July 2026</b></span>
      <span>NEXT SCHEDULED REVIEW <b>Q3 2026</b></span>
    </div>
  </header>

  <section>
    <div class="finding">
      <div class="ft">Reading of Record</div>
      <p>The DRC is delivering fiscal reform, but not the SPA's fiscal reform. Against Article XII's six named obligations, squarely delivered is <span class="big">zero of six</span>. Kinshasa is reforming on its own agenda (IS and IRPP effective 1 Jan 2026; a Law 14/005 amendment tabled 3 Jul 2026), not the treaty's list.</p>
      <p>The one binding US obligation, Article XIV technical assistance, is <span class="big">undeployed</span>, and it is the capacity-building the DRC needs to reform on-spec. The treaty binds Kinshasa to dated statutory change and binds Washington mostly to best-efforts financing with no deadlines. The fair sentence is not that the DRC is behind; it is that the DRC is reforming off-spec while the US has not delivered the one thing that would help it reform on-spec.</p>
    </div>
  </section>

  <section>
    <div class="sec-head">Vitals</div>
    <div class="stats">
      <div class="stat"><div class="label">Article XII, delivered as written</div><div class="value">0 / 6</div><div class="note">fiscal reform active but off-spec</div></div>
      <div class="stat"><div class="label">US binding obligation (Art XIV)</div><div class="value sm">UNDEPLOYED</div><div class="note">upstream of DRC delivery</div></div>
      <div class="stat"><div class="label">Literal deadline (from EIF)</div><div class="value sm">4 DEC 2026</div><div class="note" id="daysleft">calculating</div></div>
      <div class="stat"><div class="label">Domestic deadline</div><div class="value sm">~ MID 2027</div><div class="note">anchors to promulgation, pending</div></div>
    </div>
  </section>

  <section>
    <div class="sec-head">The Clock</div>
    <div class="clock">
      <div class="clock-row"><div class="k">Literal reading</div><div class="v">12 months from entry into force (4 Dec 2025). Deadline <b>4 December 2026</b>. Client-advisory default.</div></div>
      <div class="clock-row"><div class="k">Domestic reading</div><div class="v">12 months from promulgation of the ratification law. Promulgation <b>pending</b> as of 16 Jul 2026. Deadline approximately <b>mid 2027</b>.</div></div>
      <div class="clock-row"><div class="k">Ratification path</div><div class="v">Bills submitted 7 Mar 2026. National Assembly recevabilite 346-7-2 of 355 (13 Apr), final adoption 27 Apr. Senate 76 yes of 109 (19 May). Promulgation pending.</div></div>
    </div>
  </section>

  <div class="trk-controls">
    <span class="lab">Filter by verdict</span>
    <button class="trk-chip on" data-v="all" onclick="trkFilter('all')">All <span class="n" id="c-all"></span></button>
    <button class="trk-chip" data-v="b-delivered" onclick="trkFilter('b-delivered')">Delivered / on track <span class="n" id="c-b-delivered"></span></button>
    <button class="trk-chip" data-v="b-progress" onclick="trkFilter('b-progress')">In progress <span class="n" id="c-b-progress"></span></button>
    <button class="trk-chip" data-v="b-partial" onclick="trkFilter('b-partial')">Partial / framework <span class="n" id="c-b-partial"></span></button>
    <button class="trk-chip" data-v="b-gap" onclick="trkFilter('b-gap')">Gap / not started <span class="n" id="c-b-gap"></span></button>
    <button class="trk-chip" data-v="b-muted" onclick="trkFilter('b-muted')">Not due <span class="n" id="c-b-muted"></span></button>
    <button class="trk-chip" data-v="b-unv" onclick="trkFilter('b-unv')">Unverified <span class="n" id="c-b-unv"></span></button>
    <button class="trk-chip" data-v="lb" onclick="trkFilter('lb')">Load-bearing <span class="n" id="c-lb"></span></button>
    <span class="trk-count" id="trk-count"></span>
  </div>

  <section>
    <div class="sec-head">Article XII, The Crux</div>
    <div class="crux">
      <table>
        <thead><tr><th>Clause</th><th>Obligation (DRC, 12-month clock)</th><th>Verified-operative reality</th><th>Verdict</th><th>Conf.</th></tr></thead>
        <tbody>
          <tr><td class="ob">OB-XII-1</td><td class="obl">10-year renewable fiscal stabilization clause</td><td class="real">Mining Code offers only a 5-year clause (cut from 10 in 2018). No SPA-grade clause enacted.</td><td><span class="badge b-gap">Not started</span></td><td class="conf">MED-HIGH</td></tr>
          <tr><td class="ob">OB-XII-2</td><td class="obl">90-day binding VAT reimbursement <span class="lb">&bull; probe T3</span></td><td class="real">Finance Law 25/060 (promulgated) carries e-invoicing and group changes, not a binding 90-day refund.</td><td><span class="badge b-gap">Not started</span></td><td class="conf">MED-HIGH</td></tr>
          <tr><td class="ob">OB-XII-3</td><td class="obl">VAT offset mechanisms</td><td class="real">Possibly touched by DGI 2026 changes; not confirmed.</td><td><span class="badge b-unv">Unverified</span></td><td class="conf">LOW</td></tr>
          <tr><td class="ob">OB-XII-4</td><td class="obl">Simplified VAT documentation</td><td class="real">E-invoicing (E-DEF) modernizes but does not obviously simplify.</td><td><span class="badge b-partial">Partial</span></td><td class="conf">LOW</td></tr>
          <tr><td class="ob">OB-XII-5</td><td class="obl">Guichet Unique (ANAPI single window)</td><td class="real"><b>ANAPI window pre-exists (2002).</b> The 3 Jul 2026 Law 14/005 bill adds a convention-revenue guichet unique, tabled not enacted. Neither is the SPA cross-cutting single window.</td><td><span class="badge b-partial">Framework</span></td><td class="conf">MED-HIGH</td></tr>
          <tr><td class="ob">OB-XII-6</td><td class="obl">Centralized corporate tax authority for mining</td><td class="real">The IS reform lets miners opt into common-law IS or stay under the Code. Not a centralized mining tax authority.</td><td><span class="badge b-gap">Not started</span></td><td class="conf">MEDIUM</td></tr>
        </tbody>
      </table>
      <div class="verdict-line">Verdict: 0 of 6 delivered as written, confirmed against the promulgated Finance Law 25/060 and the tabled Law 14/005 bill. Fiscal reform is in motion but off-spec. Load-bearing: the entire SPA fiscal-predictability promise sits here.</div>
    </div>
  </section>

  <section>
    <div class="sec-head">Bilateral Obligation Matrix</div>

    <div class="sub-head">DRC obligations</div>
    <table>
      <thead><tr><th>OB / Art</th><th>Obligation</th><th>Trigger</th><th>Verified-operative reality</th><th>Verdict</th><th>Conf.</th></tr></thead>
      <tbody>
        <tr><td class="ob">OB-V-1 &middot; V</td><td class="obl">Initial Designated Strategic Projects list</td><td class="trig">30 days</td><td class="real">Delivered Jan 2026, on time.</td><td><span class="badge b-delivered">Delivered</span></td><td class="conf">HIGH</td></tr>
        <tr><td class="ob">OB-IV-1 &middot; IV</td><td class="obl">Initial SAR list <span class="lb">&bull; probe T2</span></td><td class="trig">30 days</td><td class="real">Shortlist presented at the 5 Feb JSC, but <a href="/sar-registry/#manono">Manono</a> is on it despite ICSID and ICC encumbrance; the clean bar is not met at list level. <a href="/sar-registry/">See the SAR Registry</a>.</td><td><span class="badge b-partial">Delivered, contested</span></td><td class="conf">MEDIUM</td></tr>
        <tr><td class="ob">OB-IV-2 / VII-1 &middot; IV/VII</td><td class="obl">First clean SAR designation, verified unencumbered title <span class="lb">&bull; LB</span></td><td class="trig">Per designation</td><td class="real"><a href="/sar-registry/#chemaf">Chemaf/Virtus</a> approved as first concrete transaction, but on leased Gecamines permits with roughly $900M debt; not a clean Article IV designation.</td><td><span class="badge b-progress">In progress</span></td><td class="conf">MEDIUM</td></tr>
        <tr><td class="ob">OB-XIII-1 &middot; XIII</td><td class="obl">Review SOE beneficial ownership and leadership <span class="lb">&bull; LB, probes T1 T5</span></td><td class="trig">Standing</td><td class="real">Gecamines board purge 24 Feb; Sicomines audit launched 5 Mar; Law 14/005 amendment tabled 3 Jul; a 70 percent Sicomines target stated with no formal instrument.</td><td><span class="badge b-progress">In progress</span></td><td class="conf">MED-HIGH</td></tr>
        <tr><td class="ob">OB-XV-1 &middot; XV</td><td class="obl">ASM formalization via EGC</td><td class="trig">Standing</td><td class="real">EGC pivot active: ERG MoU Feb 2026, Trafigura offtake, EVelution channel.</td><td><span class="badge b-progress">In progress</span></td><td class="conf">MED-HIGH</td></tr>
        <tr><td class="ob">OB-IX-1 &middot; IX</td><td class="obl">Modernize SNCC rail, Dilolo to Sakania</td><td class="trig">Standing</td><td class="real">Corridor runs under 5 percent capacity; emergency works underway.</td><td><span class="badge b-partial">In progress, weak</span></td><td class="conf">MEDIUM</td></tr>
        <tr><td class="ob">OB-IX-2 &middot; IX</td><td class="obl">Route &gt;=50% Cu, &gt;=90% Zn, &gt;=30% Co via Lobito</td><td class="trig">5 years</td><td class="real">Routing far below thresholds; will collide with Chinese offtake and eastbound flows.</td><td><span class="badge b-muted">Not due (2030)</span></td><td class="conf">MEDIUM</td></tr>
        <tr><td class="ob">OB-XI-2 &middot; XI</td><td class="obl">Right of first offer on SAR/QSP minerals to US market</td><td class="trig">Standing</td><td class="real">Mechanism on paper; no confirmed operative ROFO exercised.</td><td><span class="badge b-partial">Framework</span></td><td class="conf">LOW</td></tr>
        <tr><td class="ob">OB-XII-7 &middot; XII</td><td class="obl">Notify US of ARECOMS quota changes; quarterly briefings</td><td class="trig">Per change</td><td class="real">ARECOMS active (10 Apr decree, 96,600t quota); treaty-notice compliance not public.</td><td><span class="badge b-unv">Unverified</span></td><td class="conf">LOW</td></tr>
        <tr><td class="ob">OB-VIII-1 &middot; VIII</td><td class="obl">Inform JSC of additional incentives per QSP</td><td class="trig">12 months</td><td class="real">Not confirmed.</td><td><span class="badge b-unv">Unverified</span></td><td class="conf">LOW</td></tr>
        <tr><td class="ob">OB-VIII-2 &middot; VIII</td><td class="obl">Non-aligned equity caps, 40 to 30 to 20 to 10 percent</td><td class="trig">Yr 5/10/20</td><td class="real">First step falls 2030.</td><td><span class="badge b-muted">Not due</span></td><td class="conf">HIGH</td></tr>
        <tr><td class="ob">OB-XI-1 &middot; XI</td><td class="obl">Establish the Strategic Minerals Reserve</td><td class="trig">No deadline</td><td class="real">No confirmation the reserve is established.</td><td><span class="badge b-unv">Unverified</span></td><td class="conf">LOW</td></tr>
        <tr><td class="ob">OB-XIV-1 &middot; XIV</td><td class="obl">Prioritize judicial and anti-corruption reforms</td><td class="trig">Standing</td><td class="real">No SPA-attributable judicial reform identified.</td><td><span class="badge b-unv">Unverified</span></td><td class="conf">LOW</td></tr>
        <tr><td class="ob">OB-III-1 &middot; III</td><td class="obl">Inaugural BEPF date and location</td><td class="trig">365 days</td><td class="real">No date confirmed selected.</td><td><span class="badge b-unv">Not due, unverified</span></td><td class="conf">LOW</td></tr>
      </tbody>
    </table>

    <div class="sub-head">US obligations (best-efforts vocabulary)</div>
    <table>
      <thead><tr><th>OB / Art</th><th>Obligation</th><th>Character</th><th>Verified-operative reality</th><th>Verdict</th><th>Conf.</th></tr></thead>
      <tbody>
        <tr><td class="ob">OB-XIV-US &middot; XIV</td><td class="obl">Technical assistance, 7 areas <span class="lb">&bull; LB</span></td><td class="trig">Concrete</td><td class="real">The single most consequential gap. No TA program stood up; a Dynamic Aviation mineral-mapping proposal is a signal, not deployment.</td><td><span class="badge b-gap">Undeployed</span></td><td class="conf">HIGH</td></tr>
        <tr><td class="ob">OB-IX-US &middot; IX</td><td class="obl">Mobilize Lobito Corridor financing</td><td class="trig">Best-efforts</td><td class="real">DFC and DBSA $753M facility, Dec 2025.</td><td><span class="badge b-progress">Mobilizing</span></td><td class="conf">MED-HIGH</td></tr>
        <tr><td class="ob">OB-V-US / XI-US &middot; V/XI</td><td class="obl">Mobilize project and mineral finance</td><td class="trig">Best-efforts</td><td class="real">Project Vault $12B announced, Pax Silica $250M seed, OSC and DoD $984M; DRC-directed disbursement limited.</td><td><span class="badge b-progress">Mobilizing</span></td><td class="conf">MEDIUM</td></tr>
        <tr><td class="ob">OB-VII-US / V-US2 &middot; VII/V</td><td class="obl">Operate the US-person right of first offer and preferential access</td><td class="trig">US-operated</td><td class="real">Exercised in practice around Chemaf/Virtus (as change of control).</td><td><span class="badge b-progress">Operating</span></td><td class="conf">MEDIUM</td></tr>
        <tr><td class="ob">OB-X-US &middot; X</td><td class="obl">Mobilize Grand Inga capital</td><td class="trig">Best-efforts</td><td class="real">No confirmed US Inga financing motion.</td><td><span class="badge b-unv">Stalled, unverified</span></td><td class="conf">LOW</td></tr>
      </tbody>
    </table>

    <div class="sub-head">Joint obligations</div>
    <table>
      <thead><tr><th>OB / Art</th><th>Obligation</th><th>Cadence</th><th>Verified-operative reality</th><th>Verdict</th><th>Conf.</th></tr></thead>
      <tbody>
        <tr><td class="ob">OB-VI-J1 &middot; VI</td><td class="obl">Establish and run the JSC, twice yearly</td><td class="trig">Recurring</td><td class="real">Inaugural meeting 5 Feb 2026; second-half meeting to confirm.</td><td><span class="badge b-delivered">On track</span></td><td class="conf">MED-HIGH</td></tr>
        <tr><td class="ob">OB-VI-J2 &middot; VI</td><td class="obl">Develop SAR offtake guidelines</td><td class="trig">JSC output</td><td class="real">Treaty text implies not yet adopted.</td><td><span class="badge b-unv">Unverified</span></td><td class="conf">LOW</td></tr>
        <tr><td class="ob">OB-X-J &middot; X</td><td class="obl">Grand Inga Coordination and Governance Committee</td><td class="trig">One-time</td><td class="real">Checked: not established as an SPA bilateral body. National ADPI/ADEPI agency plus World Bank PDI3 exist, not the Article X committee.</td><td><span class="badge b-unv">Not established</span></td><td class="conf">LOW</td></tr>
        <tr><td class="ob">OB-XVIII-1 &middot; XVIII</td><td class="obl">JSC joint implementation review</td><td class="trig">Every 3 yrs</td><td class="real">First due 2028.</td><td><span class="badge b-muted">Not due</span></td><td class="conf">HIGH</td></tr>
      </tbody>
    </table>
    <div class="legend">Verdict vocabulary is bearer-specific. DRC and dated obligations: delivered / framework / partial / in progress / not started / not due. US best-efforts obligations: mobilizing / operating / stalled / undeployed. LB marks a load-bearing obligation. Dashed badge marks an unverified row.</div>
  </section>

  <section>
    <div class="sec-head">Verification (closed, 15 Jul deep pass)</div>
    <div class="queue">
      <ol>
        <li><b>Article XII fiscal texts</b> confirmed. Finance Law 25/060 (promulgated) carries no 90-day VAT and no stabilization clause; the 3 Jul Law 14/005 bill creates a convention-revenue guichet (GURCC), tabled not enacted. Zero of six as written stands on primary sources.</li>
        <li><b>Grand Inga committee</b> checked, not established as an SPA bilateral body. What exists is the national ADPI/ADEPI agency plus World Bank PDI3 and a France MoU.</li>
        <li><b>Strategic Minerals Reserve</b> checked, no public confirmation of establishment.</li>
        <li><b>JSC offtake guidelines</b> checked, no public confirmation of adoption.</li>
        <li><b>BEPF</b> not due; no date or location confirmed selected.</li>
        <li><b>ARECOMS</b> notice compliance is non-public; not confirmable.</li>
        <li><b>27 April count</b> not publicly reported. Verified: recevabilite 346-7-2 of 355 (13 Apr), Senate 76 yes of 109 (19 May). The 370/371 figure was a conflation with a separate 6 Jul batch, dropped.</li>
      </ol>
      <div class="legend">Establishment rows now read "checked, not established," a stronger statement than an open gap. The SPA institutional build-out beyond the JSC and the two initial lists is largely not yet stood up.</div>
    </div>
  </section>

  <section>
    <div class="sec-head">See also</div>
    <div class="seealso">
      <a href="/explainers/what-does-article-xii-require-us-drc-spa"><span class="sa-k">Explainer</span><span class="sa-t">What does Article XII require?</span></a>
      <a href="/explainers/what-is-the-strategic-asset-reserve-sar"><span class="sa-k">Explainer</span><span class="sa-t">What is the Strategic Asset Reserve?</span></a>
      <a href="/sar-registry/"><span class="sa-k">Register</span><span class="sa-t">SAR Registry, 13 designations</span></a>
      <a href="/drc-sovereign-rating-desk/"><span class="sa-k">Rating</span><span class="sa-t">DRC Sovereign &amp; Institutional Rating</span></a>
      <a href="/explainers/what-is-the-joint-steering-committee-jsc"><span class="sa-k">Explainer</span><span class="sa-t">What is the Joint Steering Committee?</span></a>
      <a href="/explainers/what-is-the-lobito-corridor"><span class="sa-k">Explainer</span><span class="sa-t">What is the Lobito Corridor?</span></a>
      <a href="/spa-glossary/"><span class="sa-k">Register</span><span class="sa-t">The SPA Glossary, 42 terms</span></a>
      <a href="/cami-registry/"><span class="sa-k">Register</span><span class="sa-t">CAMI Registry, 3,448 titles</span></a>
    </div>
  </section>

  <div class="provenance">
    <span>AS-REF &middot; Regulatory Reform Tracker &middot; v2.0</span>
    <span>Basis: US-DRC Strategic Partnership Agreement, treaty text. 29 obligations, 12 articles.</span>
    <span>Ascendance Strategies &middot; contact@ascendance-strategies.com</span>
  </div>
  
</div>
<script>

/* Pre-paint theme apply (shared platform key). Modes: light | dark | auto. */



  (function(){
    var target=new Date('2026-12-04T00:00:00');
    var days=Math.ceil((target-new Date())/(1000*60*60*24));
    var el=document.getElementById('daysleft');
    if(!el){return;}
    if(days<=0){el.textContent='deadline reached';return;}
    var start=null,dur=1100;
    function ease(t){return 1-Math.pow(1-t,3);}
    function step(ts){if(!start){start=ts;}var p=Math.min((ts-start)/dur,1);el.textContent=Math.round(ease(p)*days)+' days remaining';if(p<1){requestAnimationFrame(step);}}
    requestAnimationFrame(step);
  })();
  /* browse filter */
  (function(){
    var rows=[].slice.call(document.querySelectorAll('.crux tbody tr, .bilateral tbody tr'));
    document.querySelectorAll('table').forEach(function(t){
      if(t.closest('.trk-controls'))return;
      [].slice.call(t.querySelectorAll('tbody tr')).forEach(function(tr){
        if(rows.indexOf(tr)<0)rows.push(tr);
      });
    });
    window.__trkRows=rows;
    rows.forEach(function(tr){
      var g=[];
      var b=tr.querySelector('.badge');
      if(b){b.classList.forEach(function(c){if(c.indexOf('b-')===0)g.push(c);});}
      if(tr.querySelector('.lb'))g.push('lb');
      tr.dataset.vg=g.join(' ');
    });
    ['all','b-delivered','b-progress','b-partial','b-gap','b-muted','b-unv','lb'].forEach(function(k){
      var n=k==='all'?rows.length:rows.filter(function(tr){return (' '+tr.dataset.vg+' ').indexOf(' '+k+' ')>-1;}).length;
      var el=document.getElementById('c-'+k);if(el)el.textContent=n;
    });
    trkFilter('all');
  })();
  function trkFilter(v){
    document.querySelectorAll('.trk-chip').forEach(function(c){c.classList.toggle('on',c.dataset.v===v);});
    var rows=window.__trkRows||[],shown=0;
    rows.forEach(function(tr){
      var ok=(v==='all')||((' '+tr.dataset.vg+' ').indexOf(' '+v+' ')>-1);
      tr.classList.toggle('trk-hide',!ok);if(ok)shown++;
    });
    document.getElementById('trk-count').textContent=shown+' of '+rows.length+' obligations';
  }



</script>
</div>
<?php get_footer(); ?>
