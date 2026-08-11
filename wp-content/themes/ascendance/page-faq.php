<?php
/**
 * Template Name: FAQ Page
 *
 * @package Ascendance
 */

get_header();
?>

<!-- FAQPage structured data (AEO / GEO / rich results). Keep in sync with the visible Q&A below. -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {"@type":"Question","name":"What is the SAR and why does it matter to us?","acceptedAnswer":{"@type":"Answer","text":"The Strategic Asset Reserve is the SPA's core investment mechanism. The DRC designated an initial list of critical-mineral assets, gold assets, and unlicensed exploration areas. Under Article VII, US persons hold the right of first offer on these assets before aligned or non-aligned persons can compete. The process runs on strict clocks: three months to submit a proposal once the DRC notifies the Joint Steering Committee of the opportunity; a three-month negotiation window, renewable once; and a maximum of nine months from notification before aligned persons, including qualifying Congolese persons, gain the right to submit. These clocks are running now, so SAR positioning is time-sensitive for any organization with an interest in DRC mineral assets."}},
    {"@type":"Question","name":"What is a QSP and how is it different from the SAR?","acceptedAnswer":{"@type":"Answer","text":"A Qualifying Strategic Project is a DRC project not majority-owned by the DRC or its state-owned enterprises that meets every criterion in Annex 1 of the SPA. A project becomes a QSP on notification to the Joint Steering Committee, by either Party or by the US Ambassador. Annex 1 sets conditions across ownership, offtake and project type, and all must be met. Ownership requires either at least 51 percent US-person equity, or at least 40 percent held by US or aligned persons with effective control over governance. Separately, no more than 40 percent of equity may be held outside the US-person or aligned-person definition, a ceiling that ratchets to 30 percent at five years, 20 percent at ten, and 10 percent at twenty. Offtake must meet the JSC's guidelines and move on the Lobito Corridor rail where feasible. SAR is about securing new assets; QSP is about structuring an existing or target investment to qualify for SPA protections and incentives. They are complementary, not interchangeable."}},
    {"@type":"Question","name":"Do you serve non-US clients?","acceptedAnswer":{"@type":"Answer","text":"Yes. The SPA extends benefits to aligned persons. An aligned person is a non-US person that is not a national of, or organized under the laws of, a covered nation; not owned one third or more by covered-nation interests; and not controlled by covered-nation nationals at the CEO or board level. Covered nations under 10 U.S.C. 4872(f)(2) are principally China, Russia, Iran, North Korea, Cuba and Venezuela. A European, Canadian, Australian or Japanese company can fail the aligned-person test on a shareholding or board composition it has not examined in this light, because the threshold is one third, not a majority. Aligned persons gain access to the SAR process, QSP fiscal incentives and Lobito Corridor treatment; non-aligned persons do not. Eligibility screening is usually the first thing we run."}},
    {"@type":"Question","name":"Do you work with Congolese companies?","acceptedAnswer":{"@type":"Answer","text":"Yes. Congolese nationals and Congolese-registered companies are not excluded from the SPA. Article VII expressly contemplates DRC persons who meet the aligned-person definition in Annex 2 entering the SAR process. A Congolese entity that is not one-third-or-more owned or controlled by covered-nation interests can qualify as an aligned person and participate alongside US and other aligned investors. We offer an Aligned Person Eligibility Assessment for Congolese operators, and SPA analysis for Kinshasa-based law firms whose clients are asking SPA questions the firm is not yet equipped to answer in-house."}},
    {"@type":"Question","name":"What makes Ascendance Strategies different from other DRC advisors?","acceptedAnswer":{"@type":"Answer","text":"Three things. Exclusive SPA specialization: we work only on the US-DRC Strategic Partnership and its direct implications, not generic Africa or emerging-markets advisory. Depth over breadth: we produce analysis that is verified, not estimated, and every claim carries a confidence rating; when we do not know something with sufficient certainty, we say so in writing. Timing: the Joint Steering Committee is operational, the SAR list is live, and the twelve-month reform clock is running, so the window for first-mover positioning is measured in months."}},
    {"@type":"Question","name":"Are you affiliated with the DRC government, US government, or any political party?","acceptedAnswer":{"@type":"Answer","text":"No. We maintain strict independence from all governments, political parties, commercial interests and advocacy organizations. We advise clients on navigating the DRC's political landscape; we do not serve it. We hold working relationships with DRC officials, ministry staff and state-enterprise leadership as sources of context, not mandates. Our advice serves client interests exclusively."}},
    {"@type":"Question","name":"Where are you based and how does that affect your DRC coverage?","acceptedAnswer":{"@type":"Answer","text":"Paris is the primary base, with regular travel to Kinshasa for client engagements, source relationships and stakeholder meetings. The decision chain on a major DRC transaction does not sit in one capital: Kinshasa sets the terms, while financing, diplomatic positioning and corporate approvals run through Paris, Brussels, Washington and London. Being Europe-based with a working Kinshasa presence puts us on both ends of that chain."}},
    {"@type":"Question","name":"How do engagements typically begin?","acceptedAnswer":{"@type":"Answer","text":"With a phone call, not a proposal. We run a thirty-minute diagnostic conversation to understand your situation, your timeline and whether our work is the right fit. If it is, we scope a proposal from that conversation; if it is not, we tell you directly. We do not send generic capability decks to cold inquiries."}},
    {"@type":"Question","name":"How are you paid? Do you take success fees or equity?","acceptedAnswer":{"@type":"Answer","text":"Professional advisory fees only, as project fees or monthly retainers. We do not take equity stakes, success fees, profit participation or any performance-linked compensation. That structure exists so that our analysis serves your interests and nothing else."}},
    {"@type":"Question","name":"Can you help us find investment opportunities or originate deals?","acceptedAnswer":{"@type":"Answer","text":"No. We provide analysis and advisory, not deal origination, project development or investment facilitation. We help you assess opportunities you have identified, understand the political and regulatory dynamics, navigate stakeholder complexity and manage risk. The assessment is analytical; the decisions remain yours."}},
    {"@type":"Question","name":"Do you take on work that is not directly related to the SPA?","acceptedAnswer":{"@type":"Answer","text":"Yes, depending on capacity. Our primary focus is the US-DRC Strategic Partnership, but DRC advisory needs do not always map onto a single framework. A political-risk question, an operator background check or a conflict-zone assessment outside the strict SPA perimeter: we consider it against what we are already running. We are a capped-capacity practice by design; if we are at or near capacity we tell you directly and either refer you or propose a timeline."}},
    {"@type":"Question","name":"Do you sign NDAs?","acceptedAnswer":{"@type":"Answer","text":"Yes, for all client engagements. Confidentiality is standard. We also maintain strict information barriers between clients operating in overlapping sectors or geographies."}},
    {"@type":"Question","name":"Do you work with law firms and consulting firms as a subcontractor?","acceptedAnswer":{"@type":"Answer","text":"Yes. We hold three collaboration models: subcontractor, where we deliver under your brand; acknowledged partner, a co-delivery with dual credit; and referral, where you introduce the client and we serve them directly with credit to your firm. Flexible on structure, inflexible on quality."}}
  ]
}
</script>

<main>
<section class="section" style="padding-bottom:30px;"><div class="wrap">
  <div class="faq-hero">
    <span class="kicker"><?php esc_html_e( 'Frequently asked questions', 'ascendance' ); ?></span>
    <h1><?php esc_html_e( 'Every question about our practice, answered.', 'ascendance' ); ?></h1>
    <p><?php esc_html_e( "Exclusive focus on the US-DRC Strategic Partnership Agreement. Can't find it?", 'ascendance' ); ?> <a href="#ask" style="color:var(--red);font-weight:600;"><?php esc_html_e( 'Book a diagnostic call.', 'ascendance' ); ?></a></p>
  </div>

  <div class="faq-layout">
    <nav class="faq-toc">
      <a href="#framework"><?php esc_html_e( 'The framework', 'ascendance' ); ?></a>
      <a href="#firm"><?php esc_html_e( 'The firm', 'ascendance' ); ?></a>
      <a href="#ask"><?php esc_html_e( 'Still have a question?', 'ascendance' ); ?></a>
      <a class="faq-toc-aside" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>"><?php esc_html_e( 'Subscription & pricing →', 'ascendance' ); ?></a>
    </nav>

    <div>
      <!-- THE FRAMEWORK -->
      <div class="faq-group" id="framework">
        <h2><?php esc_html_e( 'The framework', 'ascendance' ); ?></h2>

        <details class="faq-q" open><summary><?php esc_html_e( 'What is the SAR, and why does it matter to us?', 'ascendance' ); ?><span class="pm">+</span></summary>
          <div class="ans">
            <p><?php esc_html_e( "The Strategic Asset Reserve is the SPA's core investment mechanism. The DRC designated an initial list of critical-mineral assets, gold assets, and unlicensed exploration areas. Under", 'ascendance' ); ?> <strong>Article VII</strong>, <?php esc_html_e( 'US persons hold the right of first offer on these assets before aligned or non-aligned persons can compete.', 'ascendance' ); ?></p>
            <p><?php esc_html_e( 'The process runs on strict clocks. Three months to submit a proposal once the DRC notifies the Joint Steering Committee of the opportunity. A three-month negotiation window, renewable once. A maximum of nine months from notification before aligned persons, including qualifying Congolese persons, gain the right to submit.', 'ascendance' ); ?></p>
            <p><?php esc_html_e( 'These clocks are running now. If your organization has any interest in DRC mineral assets, your SAR positioning is time-sensitive.', 'ascendance' ); ?></p>
          </div></details>

        <details class="faq-q"><summary><?php esc_html_e( 'What is a QSP, and how is it different from the SAR?', 'ascendance' ); ?><span class="pm">+</span></summary>
          <div class="ans">
            <p><?php esc_html_e( 'A Qualifying Strategic Project is a DRC project not majority-owned by the DRC or its state-owned enterprises that meets every criterion in', 'ascendance' ); ?> <strong>Annex 1</strong> <?php esc_html_e( 'of the SPA. A project becomes a QSP on notification to the Joint Steering Committee, by either Party or by the US Ambassador. Meeting the criteria is necessary. Notification is the operative step.', 'ascendance' ); ?></p>
            <p><?php esc_html_e( 'Annex 1 sets conditions across ownership, offtake, and project type. All must be met.', 'ascendance' ); ?></p>
            <p><strong><?php esc_html_e( 'Ownership.', 'ascendance' ); ?></strong> <?php esc_html_e( 'Either at least 51 percent equity held by a US person or persons, or at least 40 percent held by US or aligned persons together with effective control over project governance, meaning a board majority or veto rights over strategic decisions, or guaranteed critical-mineral offtake rights.', 'ascendance' ); ?></p>
            <p><strong><?php esc_html_e( 'The equity cap most investors miss.', 'ascendance' ); ?></strong> <?php esc_html_e( 'Separately from the ownership condition, no more than 40 percent of project equity may be held by anyone outside the US-person or aligned-person definition. That ceiling drops to 30 percent five years after entry into force, 20 percent at ten years, and 10 percent at twenty. If your DRC project has non-aligned co-investors, your position against that ratchet is a structuring question you need answered now, not at renewal.', 'ascendance' ); ?></p>
            <p><strong><?php esc_html_e( 'One discretionary path.', 'ascendance' ); ?></strong> <?php esc_html_e( 'Annex 1 allows the Parties to permit a higher non-aligned ownership percentage for projects evaluated in a given year. It is a narrow door and it is not automatic, but it exists, and most investors do not know it does.', 'ascendance' ); ?></p>
            <p><strong><?php esc_html_e( 'Offtake.', 'ascendance' ); ?></strong> <?php esc_html_e( "The project must meet the JSC's offtake guidelines, or demonstrate to the JSC how its offtake advances the Agreement's objectives, and must be designed so that exported critical-mineral offtake moves on the Lobito Corridor rail infrastructure where geographically feasible. A project that clears the ownership test and fails the offtake test is not a QSP.", 'ascendance' ); ?></p>
            <p><?php esc_html_e( 'SAR is about securing new assets. QSP is about structuring an existing or target investment to qualify for SPA protections and fiscal incentives. They are complementary, not interchangeable.', 'ascendance' ); ?></p>
          </div></details>

        <details class="faq-q"><summary><?php esc_html_e( 'Do you serve non-US clients?', 'ascendance' ); ?><span class="pm">+</span></summary>
          <div class="ans">
            <p><?php esc_html_e( 'Yes, and the definition matters more than most investors assume.', 'ascendance' ); ?></p>
            <p><?php esc_html_e( 'The SPA extends benefits to', 'ascendance' ); ?> <strong>aligned persons</strong>. <?php esc_html_e( 'An aligned person is a non-US person that is not a national of a covered nation, not an entity organized under the laws of a covered nation, not an entity owned one third or more, directly or indirectly, by covered-nation nationals or entities, and not an entity in which covered-nation nationals hold the chief executive position or can appoint or remove one third of the board or otherwise direct its vote. Covered nations are defined by 10 U.S.C. 4872(f)(2): principally China, Russia, Iran, North Korea, Cuba, and Venezuela.', 'ascendance' ); ?></p>
            <p><?php esc_html_e( 'The practical consequence: a European, Canadian, Australian, or Japanese company can fail the aligned-person test on a shareholding or a board composition it has not examined in this light. The threshold is one third, not a majority. Aligned persons gain access to the SAR investment process, QSP fiscal incentives, and Lobito Corridor treatment. Non-aligned persons do not.', 'ascendance' ); ?></p>
            <p><?php esc_html_e( 'We serve clients from any jurisdiction with legitimate DRC exposure, and eligibility screening is usually the first thing we run.', 'ascendance' ); ?></p>
          </div></details>

        <details class="faq-q"><summary><?php esc_html_e( 'Do you work with Congolese companies?', 'ascendance' ); ?><span class="pm">+</span></summary>
          <div class="ans">
            <p><?php esc_html_e( 'Yes, and this is one of the most misunderstood provisions in the entire framework.', 'ascendance' ); ?></p>
            <p><?php esc_html_e( 'Congolese nationals and Congolese-registered companies are not excluded from the SPA. Article VII expressly contemplates DRC persons who meet the aligned-person definition in', 'ascendance' ); ?> <strong>Annex 2</strong> <?php esc_html_e( 'entering the SAR process. A Congolese entity that is not one-third-or-more owned or controlled by covered-nation interests can qualify as an aligned person and participate alongside US and other aligned investors.', 'ascendance' ); ?></p>
            <p><?php esc_html_e( 'We offer an', 'ascendance' ); ?> <strong>Aligned Person Eligibility Assessment</strong> <?php esc_html_e( 'for Congolese operators who need to understand their positioning, what ownership structuring the definition may require, and which SAR assets or Designated Strategic Projects are most relevant. We also provide SPA analysis for Kinshasa-based law firms whose clients are asking SPA questions the firm is not yet equipped to answer in-house.', 'ascendance' ); ?></p>
          </div></details>
      </div>

      <!-- THE FIRM -->
      <div class="faq-group" id="firm">
        <h2><?php esc_html_e( 'The firm', 'ascendance' ); ?></h2>

        <details class="faq-q"><summary><?php esc_html_e( 'What makes Ascendance Strategies different from other DRC advisors?', 'ascendance' ); ?><span class="pm">+</span></summary>
          <div class="ans">
            <p><?php esc_html_e( 'Three things.', 'ascendance' ); ?></p>
            <p><strong><?php esc_html_e( 'Exclusive SPA specialization.', 'ascendance' ); ?></strong> <?php esc_html_e( 'We work only on the US-DRC Strategic Partnership and its direct implications. Not generic Africa advisory. Not emerging-markets consulting. Not extractive-sector consulting broadly. Every framework we have built serves one purpose: making your DRC engagement under the SPA succeed.', 'ascendance' ); ?></p>
            <p><strong><?php esc_html_e( 'Depth over breadth.', 'ascendance' ); ?></strong> <?php esc_html_e( 'We produce analysis that is verified, not estimated. Every claim carries a confidence rating. When we do not know something with sufficient certainty, we say so, in the deliverable, in writing.', 'ascendance' ); ?></p>
            <p><strong><?php esc_html_e( 'Timing.', 'ascendance' ); ?></strong> <?php esc_html_e( 'The Joint Steering Committee is operational. The SAR list is live. The twelve-month reform clock is running. The window for first-mover positioning is measured in months.', 'ascendance' ); ?></p>
          </div></details>

        <details class="faq-q"><summary><?php esc_html_e( 'Are you affiliated with the DRC government, US government, or any political party?', 'ascendance' ); ?><span class="pm">+</span></summary>
          <div class="ans">
            <p><?php esc_html_e( "No. We maintain strict independence from all governments, political parties, commercial interests, and advocacy organizations. We advise clients on navigating the DRC's political landscape. We do not serve it.", 'ascendance' ); ?></p>
            <p><?php esc_html_e( 'We hold working relationships with DRC government officials, ministry staff, and state-enterprise leadership. These relationships are sources of context, not mandates. Our advice serves client interests exclusively.', 'ascendance' ); ?></p>
          </div></details>

        <details class="faq-q"><summary><?php esc_html_e( 'Where are you based, and how does that affect your DRC coverage?', 'ascendance' ); ?><span class="pm">+</span></summary>
          <div class="ans">
            <p><?php esc_html_e( 'Paris is the primary base, with regular travel to Kinshasa for client engagements, source relationships, and stakeholder meetings.', 'ascendance' ); ?></p>
            <p><?php esc_html_e( 'Paris is deliberate. The decision chain on a major DRC transaction does not sit in one capital. Kinshasa sets the terms. The financing, the diplomatic positioning, and the corporate approvals run through Paris, Brussels, Washington, and London. Being Europe-based with a working Kinshasa presence puts us on both ends of that chain rather than one.', 'ascendance' ); ?></p>
          </div></details>

        <details class="faq-q"><summary><?php esc_html_e( 'How do engagements typically begin?', 'ascendance' ); ?><span class="pm">+</span></summary>
          <div class="ans">
            <p><?php esc_html_e( 'With a phone call, not a proposal. We run a thirty-minute diagnostic conversation to understand your situation, your timeline, and whether our work is the right fit for what you need. If it is, we scope a proposal from that conversation. If it is not, we tell you directly.', 'ascendance' ); ?></p>
            <p><?php esc_html_e( 'We do not send generic capability decks to cold inquiries. Every proposal is scoped against a specific need.', 'ascendance' ); ?></p>
          </div></details>

        <details class="faq-q"><summary><?php esc_html_e( 'How are you paid? Do you take success fees or equity?', 'ascendance' ); ?><span class="pm">+</span></summary>
          <div class="ans">
            <p><?php esc_html_e( 'Professional advisory fees only, as project fees or monthly retainers. We do not take equity stakes, success fees, profit participation, or any form of performance-linked compensation. That structure exists so that our analysis serves your interests and nothing else.', 'ascendance' ); ?></p>
          </div></details>

        <details class="faq-q"><summary><?php esc_html_e( 'Can you help us find investment opportunities or originate deals?', 'ascendance' ); ?><span class="pm">+</span></summary>
          <div class="ans">
            <p><?php esc_html_e( 'No. We provide analysis and advisory, not deal origination, project development, or investment facilitation. We help you assess opportunities you have identified, understand the political and regulatory dynamics around them, navigate stakeholder complexity, and manage risk. The assessment is analytical. The decisions remain yours.', 'ascendance' ); ?></p>
          </div></details>

        <details class="faq-q"><summary><?php esc_html_e( 'Do you take on work that is not directly related to the SPA?', 'ascendance' ); ?><span class="pm">+</span></summary>
          <div class="ans">
            <p><?php esc_html_e( 'Yes, depending on capacity. Our primary focus is the US-DRC Strategic Partnership, but DRC advisory needs do not always map neatly onto a single framework. A political-risk question, an operator background check, a conflict-zone assessment that sits outside the strict SPA perimeter: we consider it against what we are already running.', 'ascendance' ); ?></p>
            <p><?php esc_html_e( 'We are a capped-capacity practice by design. If we are at or near capacity, we tell you directly and either refer you or propose a timeline when we can engage properly. We do not overcommit.', 'ascendance' ); ?></p>
          </div></details>

        <details class="faq-q"><summary><?php esc_html_e( 'Do you sign NDAs?', 'ascendance' ); ?><span class="pm">+</span></summary>
          <div class="ans">
            <p><?php esc_html_e( 'Yes, for all client engagements. Confidentiality is standard. We also maintain strict information barriers between clients operating in overlapping sectors or geographies.', 'ascendance' ); ?></p>
          </div></details>

        <details class="faq-q"><summary><?php esc_html_e( 'Do you work with law firms and consulting firms as a subcontractor?', 'ascendance' ); ?><span class="pm">+</span></summary>
          <div class="ans">
            <p><?php esc_html_e( 'Yes. We hold three collaboration models for firms that need DRC and SPA expertise on a client engagement.', 'ascendance' ); ?></p>
            <ul class="faq-list">
              <li><strong><?php esc_html_e( 'Subcontractor.', 'ascendance' ); ?></strong> <?php esc_html_e( 'We deliver under your brand.', 'ascendance' ); ?></li>
              <li><strong><?php esc_html_e( 'Acknowledged partner.', 'ascendance' ); ?></strong> <?php esc_html_e( 'Co-delivery with dual credit.', 'ascendance' ); ?></li>
              <li><strong><?php esc_html_e( 'Referral.', 'ascendance' ); ?></strong> <?php esc_html_e( 'You introduce the client, we serve them directly, with credit to your firm.', 'ascendance' ); ?></li>
            </ul>
            <p><?php esc_html_e( 'Flexible on structure. Inflexible on quality.', 'ascendance' ); ?></p>
          </div></details>
      </div>

      <!-- CLOSING -->
      <div class="faq-close" id="ask">
        <div class="faq-close-main">
          <h2><?php esc_html_e( 'Still have a question?', 'ascendance' ); ?></h2>
          <p><?php esc_html_e( 'We open every engagement with a thirty-minute diagnostic call, not a proposal. If we are not the right fit, we say so.', 'ascendance' ); ?></p>
          <a class="btn-primary" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Book a diagnostic call', 'ascendance' ); ?></a>
        </div>
        <div class="faq-close-contact">
          <div class="fc-row"><span class="fc-k"><?php esc_html_e( 'Direct line', 'ascendance' ); ?></span><span class="fc-v">+33 7 51 53 43 77</span></div>
          <div class="fc-row"><span class="fc-k"><?php esc_html_e( 'Paris', 'ascendance' ); ?></span><span class="fc-v">15 allée d'Andrezieux, 75018</span></div>
          <div class="fc-row"><span class="fc-k"><?php esc_html_e( 'Email', 'ascendance' ); ?></span><span class="fc-v"><a href="mailto:contact@ascendance-strategies.com">contact@ascendance-strategies.com</a></span></div>
        </div>
      </div>
    </div>
  </div>
</div></section>
</main>

<?php
get_footer();

