<?php
/**
 * Template Name: Lobito Corridor Intelligence Dossier
 *
 * @package Ascendance
 */

global $ascendance_custom_seo_title, $ascendance_custom_seo_meta;
$ascendance_custom_seo_title = 'The Lobito Corridor: the Congolese segment | Ascendance Strategies';
$ascendance_custom_seo_meta = [
    
];

get_header();
?>

<div class="as-page-wrap ref-page-wrap">
<style>

@import url('https://cdn.jsdelivr.net/npm/@fontsource/cooper-hewitt@5.2.5/600.css');@import url('https://cdn.jsdelivr.net/npm/@fontsource/cooper-hewitt@5.2.5/700.css');
:root{
  --red:#BC1B1D; --red-deep:#8E1416;
  --navy:#0F1E35; --navy-deep:#0A1628;
  --cream:#F7F4EF; --paper:#FFFFFF;
  --ink:#1A1A2E; --stone:#56514B; --muted:#6B6B7A;
  --rule:#E8E4DC;
  --serif:"Noto Serif","Iowan Old Style",Georgia,serif;
  --sans:"Cooper Hewitt","Barlow","Helvetica Neue",Arial,sans-serif;
  --mono:"JetBrains Mono","SFMono-Regular",Menlo,monospace;
}
:root[data-theme="dark"]{
  --navy:#f3eee4; --navy-deep:#17140f;
  --cream:#17140f; --paper:#221e17;
  --ink:#f3eee4; --stone:#cec7b8; --muted:#98917f;
  --rule:#322c23;
}
*{box-sizing:border-box}
body{margin:0;background:var(--cream);color:var(--ink);font-family:var(--serif);line-height:1.62;-webkit-font-smoothing:antialiased;transition:background .25s ease,color .25s ease;}
.wrap{max-width:760px;margin:0 auto;padding:0 28px 90px}
/* platform chrome */
.plat-head{background:var(--paper);border-bottom:1px solid var(--rule);}
.plat-top{max-width:1240px;margin:0 auto;display:flex;align-items:center;padding:16px 28px;}
.as-lockup{display:inline-flex;flex-direction:column;gap:3px;text-decoration:none;line-height:1;--ll:20px;}
.as-lockup .ll-row{display:flex;align-items:stretch;}
.as-lockup .ll-box{width:var(--ll);height:var(--ll);background:var(--red);color:#fff;display:flex;align-items:center;justify-content:center;font-family:var(--sans);font-weight:700;font-size:calc(var(--ll)*0.72);line-height:1;}
.as-lockup .ll-s{background:var(--ink);color:var(--cream);}
.as-lockup .ll-word{font-family:var(--sans);font-weight:700;font-size:var(--ll);letter-spacing:0.005em;color:var(--ink);height:var(--ll);display:flex;align-items:center;padding-left:calc(var(--ll)*0.05);}
.as-lockup .ll-tag{font-family:var(--sans);font-weight:600;font-size:calc(var(--ll)*0.275);letter-spacing:0.13em;text-transform:uppercase;color:var(--muted);margin-top:calc(var(--ll)*0.2);white-space:nowrap;align-self:stretch;text-align:center;}
.plat-nav{background:var(--cream);border-bottom:1px solid var(--rule);display:flex;gap:26px;align-items:center;padding:0 28px;overflow-x:auto;max-width:1240px;margin:0 auto;}
.plat-nav a{font-family:var(--sans);font-weight:600;font-size:13px;color:var(--stone);text-decoration:none;padding:13px 0;border-bottom:2px solid transparent;white-space:nowrap;letter-spacing:.02em;}
.plat-nav a:hover{color:var(--ink);}
.plat-nav a.on{color:var(--red);border-bottom-color:var(--red);}
.plat-actions{margin-left:auto;display:flex;align-items:center;gap:18px;}
.plat-actions>a{padding:13px 0;color:var(--stone);text-decoration:none;font-family:var(--sans);font-weight:600;font-size:13px;}
.plat-actions>a:hover{color:var(--ink);}
.plat-sub{background:var(--red);color:#fff !important;padding:8px 16px;border-radius:2px;}
.plat-sub:hover{opacity:.9;color:#fff !important;}
.plat-theme{display:inline-flex;border:1px solid var(--rule);border-radius:2px;overflow:hidden;}
.plat-theme .theme-btn{width:30px;height:28px;display:flex;align-items:center;justify-content:center;background:transparent;color:var(--muted);border:none;cursor:pointer;padding:0;}
.plat-theme .theme-btn:hover{color:var(--ink);}
.plat-theme .theme-btn.on{background:var(--ink);color:var(--cream);}
.plat-theme .theme-btn svg{width:15px;height:15px;}
/* front matter */
.fm{background:var(--navy-deep);color:#C8D0DC;border-left:3px solid var(--red);padding:20px 24px;margin:34px 0 0;font-family:var(--mono);font-size:11.5px;line-height:1.85}
.fm b{color:var(--red);font-weight:600;letter-spacing:.06em}
.fm a{color:#C8D0DC;}
/* head */
.eyebrow{font-family:var(--sans);font-size:11px;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:var(--red);margin:44px 0 10px}
h1{font-family:var(--serif);font-weight:400;font-size:44px;line-height:1.14;letter-spacing:-.015em;margin:0 0 16px;color:var(--navy)}
:root[data-theme="dark"] h1{color:var(--ink);}
.standfirst{font-size:20px;line-height:1.55;color:var(--stone);font-style:italic;margin:0 0 26px}
.byline{font-family:var(--mono);font-size:11.5px;color:var(--muted);border-top:1px solid var(--rule);border-bottom:1px solid var(--rule);padding:11px 0;margin-bottom:38px;display:flex;gap:18px;flex-wrap:wrap}
/* body */
p{margin:0 0 18px;font-size:17.5px}
.lede{font-size:19px}
.lede::first-letter{float:left;font-size:64px;line-height:.84;padding:6px 10px 0 0;color:var(--red);font-weight:400}
h2{font-family:var(--serif);font-weight:400;font-size:27px;line-height:1.25;color:var(--navy);margin:52px 0 4px;padding-left:16px;border-left:3px solid var(--red)}
:root[data-theme="dark"] h2{color:var(--ink);}
h2 .num{color:var(--red);font-family:var(--mono);font-size:15px;display:block;margin-bottom:4px;letter-spacing:.08em}
h3{font-family:var(--sans);font-weight:700;font-size:14px;letter-spacing:.1em;text-transform:uppercase;color:var(--navy);margin:30px 0 8px}
:root[data-theme="dark"] h3{color:var(--ink);}
.pull{border-left:3px solid var(--red);background:var(--paper);padding:20px 24px;margin:28px 0;font-size:18.5px;line-height:1.5;color:var(--navy)}
:root[data-theme="dark"] .pull{color:var(--ink);}
.note{background:var(--paper);border:1px solid var(--rule);border-left:3px solid var(--red);padding:16px 20px;margin:24px 0;font-size:15.5px;color:var(--stone)}
.note b{color:var(--navy)}
:root[data-theme="dark"] .note b{color:var(--ink);}
.meta{font-family:var(--mono);font-size:11.5px;color:var(--stone);border-top:1px solid var(--rule);padding-top:9px;margin:6px 0 0;display:flex;gap:14px;flex-wrap:wrap}
.chip{display:inline-block;font-family:var(--sans);font-size:10.5px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;padding:3px 9px;border:1px solid var(--rule);background:var(--paper);color:var(--stone);border-radius:2px}
.chip.hi{border-color:var(--red);color:var(--red)}
.chip.med{border-color:#B08A2E;color:#8A6D24}
.chip.low{border-color:var(--muted);color:var(--muted)}
ul{padding-left:20px;margin:0 0 18px}
li{margin-bottom:9px;font-size:17px}
li::marker{color:var(--red)}
.gate{margin:46px 0;border-top:2px solid var(--red);background:var(--paper);padding:24px 26px}
.gate .g-label{font-family:var(--sans);font-size:11px;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--red);margin-bottom:8px}
.gate p{font-size:16px;color:var(--stone);margin:0 0 6px}
.gate .tier{font-family:var(--mono);font-size:12px;color:var(--navy);margin-top:12px}
:root[data-theme="dark"] .gate .tier{color:var(--ink);}
.xlink{display:block;background:var(--navy-deep);color:#E8ECF2;text-decoration:none;padding:22px 26px;margin:40px 0;border-left:3px solid var(--red)}
.xlink .k{font-family:var(--sans);font-size:10.5px;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--red)}
.xlink .t{font-family:var(--serif);font-size:21px;margin:6px 0 4px}
.xlink .d{font-family:var(--mono);font-size:11.5px;color:#9FB0C4}
table{width:100%;border-collapse:collapse;margin:22px 0;font-size:14.5px}
th{background:var(--navy);color:var(--cream);font-family:var(--sans);font-size:10.5px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;text-align:left;padding:9px 12px}
td{padding:9px 12px;border-bottom:1px solid var(--rule);vertical-align:top}
tbody tr:nth-child(odd){background:var(--paper)}
td:first-child{border-left:3px solid var(--red);font-weight:600;color:var(--navy)}
:root[data-theme="dark"] td:first-child{color:var(--ink);}
.advisory{background:var(--paper);border:1px solid var(--rule);border-left:3px solid var(--red);border-radius:2px;padding:22px 24px;margin:40px 0;}
.advisory .g-label{font-family:var(--sans);font-size:11px;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--red);margin-bottom:8px}
.advisory p{font-size:15.5px;color:var(--stone);margin:0 0 14px}
.advisory a.cta{display:inline-block;font-family:var(--sans);font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#fff;background:var(--red);padding:10px 20px;border-radius:2px;text-decoration:none;}
.advisory a.cta:hover{background:var(--red-deep);}
.signoff{margin:64px 0 0;padding-top:26px;border-top:1px solid var(--rule);text-align:center;font-style:italic;color:var(--red);letter-spacing:.04em;font-size:16px}
/* footer */
footer{background:var(--navy-deep);border-top:1px solid var(--rule);padding:40px 28px 30px;}
.ftr-cols{display:grid;grid-template-columns:repeat(5,1fr);gap:22px;max-width:1240px;margin:0 auto 26px;}
.ftr-col h6{font-family:var(--sans);font-weight:600;font-size:11px;letter-spacing:0.12em;text-transform:uppercase;color:#8899BB;margin:0 0 12px;}
.ftr-col a{display:block;font-family:var(--sans);font-size:13px;color:#C8D0DC;text-decoration:none;padding:4px 0;}
.ftr-col a:hover{color:var(--red-deep);}
footer .as-lockup{margin:0 auto 14px;align-items:center;justify-content:center;width:max-content;}
footer .as-lockup .ll-word{color:#fff;}
footer .as-lockup .ll-s{background:#fff;color:var(--navy-deep);}
footer .as-lockup .ll-tag{color:#8899BB;}
.ftr-legal{max-width:1240px;margin:14px auto 0;font-family:var(--mono);font-size:9px;color:#8899BB;line-height:1.8;text-align:center;}
.ftr-legal a{color:#C8D0DC;}
@media(max-width:820px){.ftr-cols{grid-template-columns:1fr 1fr;}}

</style>

<div class="wrap">

<div class="fm">
  <b>DOSSIER</b> The Lobito Corridor, Congolese segment<br>
  <b>VERSION</b> 1.1, published 19 July 2026<br>
  <b>SOURCE OF TRUTH</b> The Lobito File v32 (internal). This Dossier is an extraction, not the File.<br>
  <b>VINTAGE</b> Corridor status current to 19 July 2026. Revenue data 2023 (ITIE and EMAPE, 6 to 12 month lag).<br>
  <b>MAINTAINED BY</b> Ascendance Strategies. Regenerated when the File increments.<br>
  <b>COMPANION</b> <a href="<?php echo esc_url( home_url( '/regulatory-reform-tracker/' ) ); ?>">Corridor Project Register</a>
</div>

<div class="eyebrow">Dossier</div>
<h1>The Lobito Corridor: what the Congolese segment actually is</h1>
<p class="standfirst">Four hundred and fifty kilometres of track carry the argument that the West can build a mineral supply chain outside China. The argument is not wrong. It is also not yet proven, and the places it is weakest are not the ones being discussed.</p>
<div class="byline">
  <span>19 July 2026</span><span>Dossier</span><span>DRC segment</span><span>Reading time 14 min</span>
</div>

<p class="lede">On 10 July 2026 the Congolese Council of Ministers approved, through a public-private partnership, the rehabilitation of the railway running from the Angolan border through Kolwezi, Tenke and Lubumbashi. The decision was read out from cabinet minutes on state television the same day. One week earlier, on 3 July, the financing package for the Angolan side of the same corridor reached financial close. After two years in which the Lobito Corridor was mostly a diplomatic proposition, the Congolese segment now has a decision behind it and a contractor in negotiation.</p>

<p>That makes this the right moment to be precise about what the corridor is, because the public account of it has drifted in two directions at once. One version treats a rail rehabilitation as a settled realignment of global mineral flows. The other treats it as a press release. Neither survives contact with the Congolese segment.</p>

<div class="pull">The corridor is not a rail project with a geopolitical narrative attached. It is a political economy problem that happens to move on rails, and the rails are the least contested part of it.</div>

<p>This Dossier sets out the segment as it stands: the physical reality against the announcements, the legal regime that governs displacement along the line, the state enterprises and funds that own and finance it, the two provinces it crosses, and the traceability regime on which its entire commercial premise rests. Every section carries a confidence rating and a data vintage. Where a fact cannot be verified from the public record, that is stated rather than smoothed over.</p>

<div class="gate">
  <div class="g-label">Public extract ends here</div>
  <p>The full Dossier continues with the legal and displacement analysis, the ownership and financing map, the provincial fiscal position, the traceability assessment, and the closing read on what could make the corridor fail.</p>
  <div class="tier">Professional tier. Continue reading, or view the companion Register.</div>
</div>

<h2><span class="num">01</span>Scope, and why the Congolese segment is the one that matters</h2>
<p>This Dossier covers the DRC segment only: from Dilolo at the Angolan border through Kolwezi and Tenke to Lubumbashi and on toward Sakania. Angola and Zambia appear where they bear on the Congolese piece and not otherwise. The reason is analytical rather than editorial. The Angolan segment is a concession with a closed financing package and a running operator. The Zambian line is greenfield and years from close. The Congolese segment is where the cargo originates, where the ownership is contested, where the regulatory regime moves prices, and where the corridor can actually fail.</p>
<p>Lualaba and Haut-Katanga together generated about 85% of Congolese mining revenue in 2023. Every question about whether this corridor delivers what is claimed for it resolves inside those two provinces.</p>
<div class="meta"><span class="chip hi">Confidence high</span><span>Revenue data: ITIE 2023, published early 2026</span></div>

<h2><span class="num">02</span>The physical reality, against the announcements</h2>
<p>Two corrections to the common account. First, the Congolese segment is not a concession. Lobito Atlantic Railway operates it under a track access agreement with the state rail company SNCC, running its own locomotives and wagons over track that SNCC continues to own. That is a materially different arrangement from Angola, where the same consortium holds a thirty year concession over 1,289 kilometres plus the mineral terminal.</p>
<p>Second, there is no gauge problem. Both networks run Cape gauge at 1,067 millimetres and wagons run through without transshipment. Accounts citing a break of gauge are wrong. The binding constraints are elsewhere and they are more serious: the line moves at 10 to 15 kilometres per hour at under 5% of capacity, SNCC runs at roughly one tenth of its 1970 traffic, and the wagon fleet has fallen from more than three thousand historically to a few hundred working units.</p>
<p>What the corridor genuinely offers is time. Officials put transit from Kolwezi to Lobito at five to eight days against about twenty five days to Durban, with claimed logistics cost reductions of up to 30%. That is the real proposition, and it does not depend on any treaty. Service currently runs at about twelve trains a week against a target of twenty by 2027, into a minerals terminal with a 13.5 metre draft that takes Panamax vessels.</p>

<div class="note"><b>The resilience question is now on the record.</b> Freight traffic was suspended for roughly two months after severe regional flooding in April 2026 and resumed only once emergency works were completed. The corridor lost close to a sixth of a year to weather before rehabilitation has begun. Any throughput projection that does not price weather interruption on this terrain is optimistic by construction, and this is the first hard evidence of how the line behaves under stress.</div>

<p>There is also a competitor. China's upgrade of the Tanzania and Zambia railway, reported at about 1.4 billion dollars, offers Copperbelt producers an Indian Ocean route through Dar es Salaam. The two corridors are competing for long term freight loyalty, and execution speed is the determinant. That is what makes the interval between a cabinet decision and steel on the ground a commercial variable rather than a procedural one.</p>

<h3>Three projects, routinely conflated</h3>
<table>
  <thead><tr><th>Project</th><th>Status as of 19 July 2026</th><th>Financing</th></tr></thead>
  <tbody>
    <tr><td>Angola brownfield</td><td>Financial close 3 July 2026, operating</td><td>753M USD. DFC 553M fifteen year senior secured loan, DBSA 200M facility</td></tr>
    <tr><td>DRC Dilolo to Kolwezi and Tenke</td><td>Cabinet approval 10 July 2026, contract still to be finalised</td><td>400M to 410M USD capex, 180M maintenance over ten years. World Bank 500M committed, DFC letter of interest up to 1B</td></tr>
    <tr><td>Tenke to Lubumbashi and Sakania</td><td>Study stage, financing gap</td><td>About 690M USD additional</td></tr>
    <tr><td>Zambia greenfield</td><td>Pre-close, fundraising expected Q3 2026</td><td>About 4B to 5B USD, close targeted Q4 2027</td></tr>
  </tbody>
</table>
<div class="meta"><span class="chip hi">High on constraints, Angola close and the cabinet decision</span><span class="chip med">Medium-high on the flooding interruption</span><span>Cabinet decision: Tier 1 reporting from minutes read on state television</span></div>

<h2><span class="num">03</span>The legal layer, and the strongest finding in this Dossier</h2>
<p>The governing instrument is the Mining Code of 2018 with its application decree. On paper the displacement regime is protective: expropriation requires expert valuation, compensation must be paid at least six months before execution, and displacement legally requires compensation and resettlement first. Article 285 bis goes further and reverses the burden of proof, making operators strictly liable for environmental and property damage absent proof of no fault.</p>
<p>The fault line is land tenure. Surface and mining rights are severable, occupants hold only a right to enjoy the surface, and customary rights are largely unsecured. That combination is what makes displacement along a rail corridor legally ambiguous rather than plainly unlawful, and it is the gap through which the corridor's social cost passes.</p>
<div class="pull">A displacement regime that does not meet the standards of the institutions financing the corridor is not a compliance detail. It is a financing risk with a date on it.</div>
<p>West bound cargo carries an overlay the domestic regime was never built to satisfy: IFC Performance Standard 5 on resettlement, EIB environmental and social standards, and the EU corporate sustainability due diligence regime phasing in toward the end of the decade. The lenders financing this corridor are bound by standards the corridor's own legal framework does not reach.</p>
<p>The published evidence on the ground is consistent with that gap. Global Witness documented between 700 and 1,200 buildings and up to 6,500 people at eviction risk along the Kolwezi works, with a buffer zone disputed between ten metres under the lender's land acquisition standard, twenty in law and twenty five as asserted by authorities. Those five metres roughly double the affected population. A large mining operator cancelled a ten village relocation in August 2025 after compensation exceeded estimates, with consultation found to have been skipped. Amnesty documented people compensated but not resettled.</p>

<h3>The constitutional question, still open</h3>
<p>In January 2026 a collective of Congolese lawyers and human rights defenders petitioned the Constitutional Court to annul the US and DRC strategic partnership agreement, arguing that a treaty engaging strategic minerals and questions of peace required parliamentary approval or a referendum, and that the joint committee's consensus decision rule would give American members an effective veto over which mining projects qualify. The petition invokes Articles 9, 162, 214, 215 and 217 of the Constitution.</p>
<p>The petition's procedural ground has been partly overtaken by events. Ratification bills were submitted to Parliament in March 2026, adopted by the National Assembly in April and by the Senate in May, with promulgation still pending in mid July 2026. The Court has not ruled. A ruling either way is the single largest movable variable in the corridor's legal layer.</p>
<div class="meta"><span class="chip hi">Confidence high</span><span>Mining Code, petition grounds and parliamentary sequence: primary and multiple sources</span><span>Corridor tender terms and the environmental and social impact assessment are not public</span></div>

<h2><span class="num">04</span>Who owns the corridor, who collects from it, who finances it</h2>
<p>The Congolese state sits on every link of this corridor and owns the value of none of it. SNCC owns the track and cannot run it. Gécamines holds minority positions in the joint ventures that produce the cargo, 18% of Tenke Fungurume against a Chinese majority of 72%, and a minority alongside Glencore at Kamoto. Its board was removed in February 2026. The Entreprise Générale du Cobalt, a Gécamines subsidiary financed by the mining fund for future generations, holds the state monopoly on buying, processing and exporting artisanal cobalt, and made the first United States bound shipment through Lobito in February 2026.</p>
<p>The regulator matters more than any of them. The strategic minerals authority imposed an export suspension and then a quota regime that moved the cobalt price from below 20,000 dollars a tonne in early 2025 to roughly 56,000 by January 2026, a swing of about 167% driven by policy rather than demand. The current quota runs at 96,600 tonnes a year.</p>

<h3>The financing stack, and the point about conditionality</h3>
<p>The US development finance institution is the connective tissue across the whole structure: the loan that closed the Angolan package, a loan to the Africa Finance Corporation, a letter of interest of up to a billion dollars toward the Congolese rehabilitation, a letter of interest in the state trading joint venture, and 600 million dollars into the Orion Critical Mineral Consortium. That consortium, 1.8 billion dollars split evenly between the US institution, Orion Resource Partners and Abu Dhabi's ADQ, signed a non binding memorandum in February 2026 to acquire 40% of Glencore's two Congolese assets at a combined enterprise value of about nine billion dollars.</p>
<div class="note"><b>Why that transaction matters beyond its size.</b> The announced terms give the acquiring consortium the right to appoint non executive directors and to direct the sale of its share of production to nominated buyers in accordance with the strategic partnership agreement. That is the clearest instance so far of the treaty functioning as a live commercial mechanism rather than a diplomatic frame. It remains subject to due diligence, binding documentation and regulatory approval.</div>
<p>Alongside it sit the Africa Finance Corporation, the African Development Bank, the World Bank and a European package exceeding two billion euro weighted toward water, skills and agriculture rather than track. The analytical point is not the total. It is that these funders carry different conditionality, and the European and multilateral money brings environmental and social standards that the domestic legal regime does not meet. The corridor is being financed by institutions whose own rules are stricter than the law it runs under.</p>
<div class="meta"><span class="chip hi">High on structures</span><span class="chip med">Medium on completion of the announced transaction</span><span>Terms as announced 3 February 2026</span></div>

<h2><span class="num">05</span>The two provinces, and a fiscal grievance with no legal exit</h2>
<p>The corridor crosses two provinces that are economically central and fiscally peripheral. Lualaba holds the largest provincial budget of the twenty six, about 982 million dollars in 2025, and its 2026 budget fell by 10.34%, a contraction the provincial executive attributed directly to the cobalt export restrictions. A single regulatory decision taken in Kinshasa moved the richest province's budget by double digits.</p>
<p>Two redistribution mechanisms are routinely conflated and should not be. The mining royalty itself, levied at 3.5% on copper and 10% on cobalt, splits under the Code with 25% to the province and 15% to the host local entity. Separately, the Constitution provides for a general retention of 40% of certain nationally collected revenue. It is the second that is not delivered: independent analysis puts 2023 receipts at about 10.2% for Haut-Katanga and about 4.2% for Lualaba of what should have been retained at source.</p>
<p>There is no judicial route to enforce it. Both provinces filed formal complaints in 2021 and received silence, there being no functioning framework for fiscal disputes between institutions. A grievance that cannot be litigated does not disappear. It resurfaces as provincial assertion, as pressure on the artisanal revenue that state cobalt policy depends on, and at the bottom of the structure as unrest.</p>
<div class="meta"><span class="chip hi">High on the retrocession gap and budgets</span><span>Provincial budget data 2025 and 2026. Retrocession analysis published November 2025</span></div>

<h2><span class="num">06</span>Traceability, the load bearing layer</h2>
<p>The corridor's commercial case rests on one claim: that Congolese copper and cobalt moving west is clean and traceable, against an opaque flow refined in China. Everything else, the premium, the policy support, the alignment of the financing, rests on that claim holding. It deserves examination rather than assertion.</p>
<p>The demand side is now hard law. The European battery regulation makes mine to market traceability over cobalt, lithium, nickel and graphite a market access condition, with due diligence enforcement running to August 2027, the Commission's due diligence guidelines due at the end of July 2026, and a digital battery passport mandatory from February 2027 for batteries above two kilowatt hours.</p>
<p>The supply side is the weak link, and it has a documented record of failure. The flagship upstream scheme for tin, tungsten and tantalum was removed from the recognised list by the downstream industry assurance body in October 2022, following an investigation that found it validating material from sites under armed group control and admitting smuggled material into certified chains. The scheme operator rejected the findings. Its subsequent independent audit assessed operations in a neighbouring country rather than in the DRC.</p>
<div class="pull">A traceability scheme funded by the volume it certifies has a structural incentive to certify volume. That flaw is generic, not specific to any one scheme, and the Congolese state cobalt vehicle sits in exactly the same position.</div>
<p>There is also a catch at origin that no tag resolves. Congolese law confines artisanal diggers to designated zones that diggers consistently report as unviable, so most work on industrial concessions where their presence is legally contested. A chain of custody cannot certify a clean point of origin for material whose origin is itself contested in law. The state monopoly on artisanal cobalt is the bet that this can be resolved administratively. The bet is live and unproven: the first traceable tonnage dates to November 2025 and no independent audit of it has been published.</p>
<p>One nuance cuts the other way and belongs in the record. The two industrial assets at the centre of the largest announced transaction both hold The Copper Mark. Certification at industrial scale is real. The unresolved problem is artisanal, and artisanal cobalt is precisely where the strategic partnership leans hardest.</p>
<div class="meta"><span class="chip hi">High on the regulatory architecture and the certification record</span><span class="chip low">Low on independently verified field performance</span><span>Regulatory dates current to July 2026</span></div>

<h2><span class="num">07</span>The layers that are not track</h2>
<p>A corridor is not only rails, and two layers sit alongside the line that rarely appear in accounts of it.</p>
<p>The first is trade facilitation. The World Bank funded Great Lakes trade facilitation programme was extended to the Lobito and Banana corridors at the sixth session of its interministerial steering committee, with the 2026 budget set at 17.5 million dollars. Its directions are procedural rather than physical: digitalisation of one stop border posts, making the simplified trade regime effective, and a standing instruction to maintain projects in occupied zones for execution once security permits. The sum is modest and the function is load bearing. Rehabilitated track feeding unreformed customs produces a faster queue, not a faster corridor. The extension to Banana is a signal in itself, placing the eastern Atlantic facing route and the western river port inside a single national logistics frame.</p>
<p>The second is local spillover: the programme connecting corridor spending to Congolese small and medium enterprises, currently a cohort of thirty nine firms. It is the smallest layer in the analysis and the one most frequently cited in political defence of the project, which is precisely why its delivery rather than its announcements is the thing to track.</p>
<div class="meta"><span class="chip med">Confidence medium</span><span>Institutional decisions documented, delivery not independently verified</span></div>

<h2><span class="num">08</span>What could make the corridor fail</h2>
<p>The corridor can fail commercially even if every treaty holds and every court rules in its favour. That is the honest closing position, and it follows from the preceding sections rather than from scepticism.</p>
<ul>
  <li><b>Capacity and power.</b> A line at under 5% of capacity, a rail operator at a tenth of its historic traffic, and an electricity utility carrying most of the state enterprise debt. The rehabilitation addresses the first. It does not address the third.</li>
  <li><b>Weather and single route dependency.</b> Two months of freight suspended by flooding in April 2026 on a corridor whose entire proposition is reliability against the southern routes. A route that closes is not a route that wins freight loyalty from a competing corridor.</li>
  <li><b>Traceability against a deadline.</b> If the European due diligence guidelines and the first independent audit of the state cobalt chain do not validate artisanal traceability at volume, the differentiation from China narrows from clean against dirty to differently audited. That is a far weaker basis for a premium.</li>
  <li><b>Distribution.</b> A fiscal grievance in the two producing provinces that has no judicial remedy and therefore expresses itself politically and, at the margin, physically.</li>
  <li><b>The refining chokepoint.</b> For copper, roughly 55% of refining already sits outside China and a western route is plausible. For cobalt, refining remains China dominated regardless of which railway carries the ore. No rehabilitation of track changes that.</li>
</ul>
<p>Set against those: the time saving is real and independent of politics, the copper case is structurally sound, and in country value addition at LME specification already exists at the margin. The corridor is neither the settled victory of one account nor the empty gesture of the other. It is a contested route whose outcome turns on capacity, traceability and distribution rather than on treaty text.</p>
<div class="meta"><span class="chip med">Confidence medium-high</span><span>Assessment as of 19 July 2026</span></div>

<h2><span class="num">09</span>What to watch</h2>
<ul>
  <li>The tender award and signature of the thirty year operating agreement for the Congolese segment.</li>
  <li>The Constitutional Court ruling on the January 2026 petition, and promulgation of the ratification law.</li>
  <li>The European due diligence guidelines, due at the end of July 2026.</li>
  <li>Conversion of the February 2026 mining transaction memorandum into binding documentation.</li>
  <li>The outcome of the audit into the incumbent Chinese infrastructure for minerals arrangement, launched March 2026.</li>
  <li>Any revision to the cobalt export quota, which moves provincial budgets directly.</li>
  <li>The first deployment of the new mining security force, targeted for December 2026, whose published mandate includes logistics corridors for mineral evacuation.</li>
</ul>

<a class="xlink" href="<?php echo esc_url( home_url( '/regulatory-reform-tracker/' ) ); ?>">
  <div class="k">Companion register</div>
  <div class="t">Corridor Project Register</div>
  <div class="d">Twelve tracked projects and instruments, with status, financing, vintage and confidence. Extracted from the same source.</div>
</a>

<h2><span class="num">10</span>What cannot be verified from outside</h2>
<p>Stated rather than hidden, because the boundary of the knowable is part of the analysis.</p>
<ul>
  <li>The corridor tender terms and the award are unpublished. The July 2026 cabinet approval is reported from minutes, not from a published communique.</li>
  <li>The environmental and social impact assessment for the works is not public.</li>
  <li>The track access agreement between the rail operator and the state rail company is not public.</li>
  <li>The balance, governance and disbursement record of the mining fund for future generations are not transparently published.</li>
  <li>Binding documentation, price mechanics and regulatory conditions for the announced 40% mining transaction remain unpublished.</li>
  <li>No independent audit of the state cobalt traceability chain has been published. The claim of full traceability is self asserted.</li>
  <li>The funding mechanism for the new mining security force is asserted by the issuing body with no public detail.</li>
  <li>Sources conflict on whether a 150 million dollar development bank loan associated with the Kamoa complex financed the rail extension or expansion of the mine. Neither reading is carried here until it is resolved against the lender's own release.</li>
  <li>Delivery records for the trade facilitation and enterprise spillover programmes are not independently verified. The institutional decisions are documented; the outcomes are not.</li>
</ul>

<div class="advisory">
  <div class="g-label">Advisory rail</div>
  <p>This Dossier draws on the public layer of the Lobito File. Asset-level exposure, counterparty diligence on the financing stack, and scenario work tied to your own position on the corridor are an advisory engagement, not a subscription tier.</p>
  <a class="cta" href="<?php echo esc_url( home_url( '/advisory/' ) ); ?>">Request an assessment</a>
</div>



</div>
</div>

<?php
get_footer();
