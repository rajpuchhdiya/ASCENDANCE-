-- =============================================================================
-- Seed Content Script — Ascendance Intelligence Platform
-- Version: 1.0 | Date: 2026-07-15
-- Spec reference: Ascendance_Master_Document.txt Sections 8 & 9
--
-- Run this ONLY on a fresh/development WordPress install.
-- These are sample posts. Editorial team replaces them before public launch.
-- All sample post titles are prefixed [SAMPLE] — remove prefix before launch.
--
-- Prerequisites: WordPress tables exist, taxonomy terms restored (wp_terms),
--               ascendance-core plugin activated.
-- =============================================================================

USE ascendance;
SET FOREIGN_KEY_CHECKS = 0;

-- =============================================================================
-- SECTION 1: Ensure core taxonomy terms exist (idempotent inserts)
-- =============================================================================

-- Topics
INSERT IGNORE INTO wp_terms (term_id, name, slug, term_group) VALUES
  (101, 'US-DRC Strategic Partnership', 'us-drc-strategic-partnership', 0),
  (102, 'Critical Minerals', 'critical-minerals', 0),
  (103, 'Sakania-Lobito Corridor', 'sakania-lobito-corridor', 0),
  (104, 'Strategic Asset Reserve', 'strategic-asset-reserve', 0),
  (105, 'Geopolitics', 'geopolitics', 0),
  (106, 'Infrastructure', 'infrastructure', 0),
  (107, 'Investment', 'investment', 0);

INSERT IGNORE INTO wp_term_taxonomy (term_id, taxonomy, description, parent, count) VALUES
  (101, 'topic', '', 0, 0),
  (102, 'topic', '', 0, 0),
  (103, 'topic', '', 0, 0),
  (104, 'topic', '', 0, 0),
  (105, 'topic', '', 0, 0),
  (106, 'topic', '', 0, 0),
  (107, 'topic', '', 0, 0);

-- Regions (supplement existing restored terms)
INSERT IGNORE INTO wp_terms (term_id, name, slug, term_group) VALUES
  (110, 'Democratic Republic of Congo', 'democratic-republic-of-congo', 0),
  (111, 'United States', 'united-states', 0),
  (112, 'Zambia', 'zambia-2', 0),
  (113, 'Angola', 'angola-2', 0);

INSERT IGNORE INTO wp_term_taxonomy (term_id, taxonomy, description, parent, count) VALUES
  (110, 'region', '', 0, 0),
  (111, 'region', '', 0, 0),
  (112, 'region', '', 0, 0),
  (113, 'region', '', 0, 0);

-- Tiers (ensure present)
INSERT IGNORE INTO wp_terms (term_id, name, slug, term_group) VALUES
  (120, 'public', 'public', 0),
  (121, 'essential', 'essential-tier', 0),
  (122, 'professional', 'professional-tier', 0);

INSERT IGNORE INTO wp_term_taxonomy (term_id, taxonomy, description, parent, count) VALUES
  (120, 'tier', '', 0, 0),
  (121, 'tier', '', 0, 0),
  (122, 'tier', '', 0, 0);

-- Intelligence Tags (entity-level)
INSERT IGNORE INTO wp_terms (term_id, name, slug, term_group) VALUES
  (130, 'Lobito Atlantic Railway', 'lobito-atlantic-railway', 0),
  (131, 'KoBold Metals', 'kobold-metals', 0),
  (132, 'Gécamines', 'gecamines', 0),
  (133, 'US DFC', 'us-dfc', 0),
  (134, 'Africa Finance Corporation', 'africa-finance-corporation', 0),
  (135, 'Sicomines', 'sicomines', 0),
  (136, 'Sakania', 'sakania', 0),
  (137, 'Kolwezi', 'kolwezi', 0),
  (138, 'Tenke Fungurume', 'tenke-fungurume', 0),
  (139, 'CMOC Group', 'cmoc-group', 0);

INSERT IGNORE INTO wp_term_taxonomy (term_id, taxonomy, description, parent, count) VALUES
  (130, 'intelligence_tag', '', 0, 0),
  (131, 'intelligence_tag', '', 0, 0),
  (132, 'intelligence_tag', '', 0, 0),
  (133, 'intelligence_tag', '', 0, 0),
  (134, 'intelligence_tag', '', 0, 0),
  (135, 'intelligence_tag', '', 0, 0),
  (136, 'intelligence_tag', '', 0, 0),
  (137, 'intelligence_tag', '', 0, 0),
  (138, 'intelligence_tag', '', 0, 0),
  (139, 'intelligence_tag', '', 0, 0);

-- =============================================================================
-- SECTION 2: Seed Posts (6 sample posts per spec Section 8.5 and 9)
-- =============================================================================

-- Post 1: Public Brief — FAQ-style, AEO-optimised (Sample 1)
INSERT IGNORE INTO wp_posts (
  ID, post_author, post_date, post_date_gmt, post_content, post_title,
  post_excerpt, post_status, comment_status, ping_status, post_name,
  post_modified, post_modified_gmt, post_type, post_mime_type, comment_count
) VALUES (
  1001, 1,
  NOW(), UTC_TIMESTAMP(),
  '<!-- wp:paragraph -->
<p>The US-DRC Strategic Partnership is a bilateral framework between the United States and the Democratic Republic of Congo, announced in 2022 and formalised through subsequent agreements, focused on critical minerals supply chains, infrastructure (notably the Sakania-Lobito Corridor), and governance cooperation. It is now the principal counterweight to Chinese economic engagement in central Africa.</p>
<!-- /wp:paragraph -->

<!-- wp:group {"className":"asc-key-takeaways"} -->
<div class="wp-block-group asc-key-takeaways">
  <ul>
    <li>Bilateral framework, not a multilateral institution</li>
    <li>Centred on critical minerals (cobalt, copper) and infrastructure</li>
    <li>Routed via Atlantic (Lobito), not Chinese-financed eastern ports</li>
    <li>Distinctive in requiring US-aligned environmental and labour standards</li>
    <li>Implementation phase began 2024; full effects unfolding through 2027</li>
  </ul>
</div>
<!-- /wp:group -->

<!-- wp:heading -->
<h2>What does the partnership cover?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The partnership covers four broad areas: critical minerals supply chain development (cobalt and copper extraction, processing, and export); infrastructure investment centred on the Lobito Corridor railway; governance and anti-corruption capacity building; and defence cooperation agreements signed in 2023.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Who are the key stakeholders?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Key stakeholders include the US Development Finance Corporation (DFC), Africa Finance Corporation (AFC), the DRC government, and private mining consortia including KoBold Metals. EU partners joined via the Global Gateway framework in 2024.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>How is it different from the Belt and Road model?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Unlike Belt and Road projects, the US-DRC framework requires transparent procurement, local content thresholds, and environmental compliance as conditions for financing. It is structured as equity investment rather than sovereign debt, shifting the risk profile for the DRC government.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>What has happened so far?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The Lobito Atlantic Railway concession was awarded in 2026 to a US-EU-backed consortium. DFC committed $250 million in project finance. Governance reforms in the DRC mining code are ongoing. Further investment tranches are expected in Q3 2026.</p>
<!-- /wp:paragraph -->',
  '[SAMPLE] What is the US-DRC Strategic Partnership?',
  'A primer on the framework, its stakeholders, and how it differs from the Belt and Road model.',
  'publish', 'open', 'closed',
  'us-drc-strategic-partnership',
  NOW(), UTC_TIMESTAMP(),
  'brief', '', 0
);

-- Post 2: Essential-gated Brief (Sample 2)
INSERT IGNORE INTO wp_posts (
  ID, post_author, post_date, post_date_gmt, post_content, post_title,
  post_excerpt, post_status, comment_status, ping_status, post_name,
  post_modified, post_modified_gmt, post_type, post_mime_type, comment_count
) VALUES (
  1002, 1,
  NOW(), UTC_TIMESTAMP(),
  '<!-- wp:paragraph -->
<p>The May 2026 concession award restructures the risk model of the Sakania-Lobito Corridor in ways that matter for any investor with exposure to Central African infrastructure. The concession assigns operational liability to a US-EU backed consortium, shifting sovereign risk from the DRC state for an initial 30-year term.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>[SAMPLE — Full analysis continues for Essential subscribers. Replace with real content before launch.]</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Implications for Project Finance Terms</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>This section analyses the financing structure and what the concession award means for debt pricing on the remaining capital stack.</p>
<!-- /wp:paragraph -->',
  '[SAMPLE] Lobito Atlantic Railway: Strategic Implications of the May 2026 Concession Award',
  'The May 2026 concession award shifts operational risk from the consortium onto host states, with implications for project finance terms over the next 18 months.',
  'publish', 'closed', 'closed',
  'lobito-atlantic-railway-may-2026-concession',
  NOW(), UTC_TIMESTAMP(),
  'brief', '', 0
);

-- Post 3: Public Brief — Pillar content
INSERT IGNORE INTO wp_posts (
  ID, post_author, post_date, post_date_gmt, post_content, post_title,
  post_excerpt, post_status, comment_status, ping_status, post_name,
  post_modified, post_modified_gmt, post_type, post_mime_type, comment_count
) VALUES (
  1003, 1,
  NOW(), UTC_TIMESTAMP(),
  '<!-- wp:paragraph -->
<p>The Sakania-Lobito Corridor is a 1,344-kilometre railway system connecting the copper-cobalt mining belt of the DRC and Zambia to the Atlantic port of Lobito in Angola. It is the most significant logistics infrastructure project in Central Africa in a generation, and the primary physical expression of the US-DRC Strategic Partnership.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Why does the corridor matter?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The corridor reduces the transit time for copper and cobalt from the Copperbelt to export markets by 60% compared with eastern routes via Dar es Salaam. It positions Atlantic-facing supply chains as the default route for US and European battery manufacturers.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Who is building it?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>[SAMPLE — Replace with real content before launch.]</p>
<!-- /wp:paragraph -->',
  '[SAMPLE] The Sakania-Lobito Corridor Explained',
  'The Sakania-Lobito Corridor connects the DRC-Zambia Copperbelt to the Atlantic. Here is what it is, who is building it, and why it matters.',
  'publish', 'open', 'closed',
  'sakania-lobito-corridor-explained',
  NOW(), UTC_TIMESTAMP(),
  'brief', '', 0
);

-- Post 4: Essential Update (Sample 3 from spec)
INSERT IGNORE INTO wp_posts (
  ID, post_author, post_date, post_date_gmt, post_content, post_title,
  post_excerpt, post_status, comment_status, ping_status, post_name,
  post_modified, post_modified_gmt, post_type, post_mime_type, comment_count
) VALUES (
  1004, 1,
  NOW(), UTC_TIMESTAMP(),
  '<!-- wp:paragraph -->
<p>The Lobito Atlantic Railway concession was formally awarded on 12 May 2026 to a consortium led by Trafigura and Mota-Engil, backed by DFC financing of $250 million. The 30-year concession covers operations from Kolwezi (DRC) through Zambia to the port of Lobito (Angola).</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><strong>Key terms:</strong> 30-year concession · DFC: $250M · Equity: Trafigura 40%, Mota-Engil 35%, AFC 25% · Operational start: Q1 2027</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>[SAMPLE — Replace with real update content before launch.]</p>
<!-- /wp:paragraph -->',
  '[SAMPLE] Lobito Atlantic Railway: Consortium Award — May 2026',
  'The Lobito Atlantic Railway concession awarded to Trafigura/Mota-Engil consortium. Key terms, financing structure, and implications.',
  'publish', 'closed', 'closed',
  'lobito-atlantic-railway-consortium-award',
  NOW(), UTC_TIMESTAMP(),
  'update', '', 0
);

-- Post 5: Professional Dossier (Sample 4 from spec)
INSERT IGNORE INTO wp_posts (
  ID, post_author, post_date, post_date_gmt, post_content, post_title,
  post_excerpt, post_status, comment_status, ping_status, post_name,
  post_modified, post_modified_gmt, post_type, post_mime_type, comment_count
) VALUES (
  1005, 1,
  NOW(), UTC_TIMESTAMP(),
  '<!-- wp:paragraph -->
<p>The Strategic Asset Reserve (SAR) framework is Ascendance''s proprietary analytical model for assessing the strategic value of African mineral assets to Western supply chains. This dossier applies the SAR framework to cobalt, the mineral most critical to the US-DRC Strategic Partnership and the Lobito Corridor investment thesis.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Executive Summary</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Cobalt''s position in the SAR framework is unique: it is simultaneously the most geographically concentrated critical mineral (70%+ in the DRC) and the most strategically contested. This dossier scores cobalt on four SAR dimensions — Supply Concentration, Substitutability, Strategic Demand Trajectory, and Geopolitical Alignment Risk — and derives an investment thesis for institutional subscribers.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>[SAMPLE — Full 8,000-word dossier. Replace with real research content before launch.]</p>
<!-- /wp:paragraph -->',
  '[SAMPLE] Strategic Asset Reserve: Application to Cobalt — Full Dossier',
  'Applying the SAR framework to cobalt. Supply concentration, substitutability, demand trajectory, and the investment thesis for institutional subscribers.',
  'publish', 'closed', 'closed',
  'strategic-asset-reserve-cobalt-dossier',
  NOW(), UTC_TIMESTAMP(),
  'dossier', '', 0
);

-- Post 6: Public Brief — SAR Framework Introduction
INSERT IGNORE INTO wp_posts (
  ID, post_author, post_date, post_date_gmt, post_content, post_title,
  post_excerpt, post_status, comment_status, ping_status, post_name,
  post_modified, post_modified_gmt, post_type, post_mime_type, comment_count
) VALUES (
  1006, 1,
  NOW(), UTC_TIMESTAMP(),
  '<!-- wp:paragraph -->
<p>The Strategic Asset Reserve (SAR) framework is a proprietary analytical model developed by Ascendance Strategies to assess the strategic value of African mineral and infrastructure assets to Western supply chains. It provides a systematic scoring method across four dimensions: Supply Concentration, Substitutability, Strategic Demand Trajectory, and Geopolitical Alignment Risk.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>What is the SAR framework?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>[SAMPLE — Replace with real SAR framework introduction before launch.]</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>How is it applied?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>[SAMPLE — Describe the four scoring dimensions and scoring methodology.]</p>
<!-- /wp:paragraph -->',
  '[SAMPLE] The Strategic Asset Reserve Framework: Introduction',
  'An introduction to the SAR framework — Ascendance''s proprietary model for assessing the strategic value of African mineral assets.',
  'publish', 'open', 'closed',
  'strategic-asset-reserve-framework-introduction',
  NOW(), UTC_TIMESTAMP(),
  'brief', '', 0
);

-- =============================================================================
-- SECTION 3: Assign Taxonomy Terms to Posts
-- =============================================================================

-- Post 1001 (Public Brief — US-DRC): tier=public, topic=us-drc-strategic-partnership, region=drc+usa, tags=lobito-atlantic-railway,us-dfc
INSERT IGNORE INTO wp_term_relationships (object_id, term_taxonomy_id) VALUES
  (1001, (SELECT tt.term_taxonomy_id FROM wp_term_taxonomy tt JOIN wp_terms t ON t.term_id = tt.term_id WHERE t.slug = 'public' AND tt.taxonomy = 'tier' LIMIT 1)),
  (1001, (SELECT tt.term_taxonomy_id FROM wp_term_taxonomy tt JOIN wp_terms t ON t.term_id = tt.term_id WHERE t.slug = 'us-drc-strategic-partnership' AND tt.taxonomy = 'topic' LIMIT 1)),
  (1001, (SELECT tt.term_taxonomy_id FROM wp_term_taxonomy tt JOIN wp_terms t ON t.term_id = tt.term_id WHERE t.slug = 'democratic-republic-of-congo' AND tt.taxonomy = 'region' LIMIT 1)),
  (1001, (SELECT tt.term_taxonomy_id FROM wp_term_taxonomy tt JOIN wp_terms t ON t.term_id = tt.term_id WHERE t.slug = 'united-states' AND tt.taxonomy = 'region' LIMIT 1));

-- Post 1002 (Essential Brief — Concession): tier=essential, topic=sakania-lobito-corridor, region=drc+angola
INSERT IGNORE INTO wp_term_relationships (object_id, term_taxonomy_id) VALUES
  (1002, (SELECT tt.term_taxonomy_id FROM wp_term_taxonomy tt JOIN wp_terms t ON t.term_id = tt.term_id WHERE t.slug = 'essential-tier' AND tt.taxonomy = 'tier' LIMIT 1)),
  (1002, (SELECT tt.term_taxonomy_id FROM wp_term_taxonomy tt JOIN wp_terms t ON t.term_id = tt.term_id WHERE t.slug = 'sakania-lobito-corridor' AND tt.taxonomy = 'topic' LIMIT 1)),
  (1002, (SELECT tt.term_taxonomy_id FROM wp_term_taxonomy tt JOIN wp_terms t ON t.term_id = tt.term_id WHERE t.slug = 'democratic-republic-of-congo' AND tt.taxonomy = 'region' LIMIT 1));

-- Post 1003 (Public Brief — Corridor): tier=public, topic=sakania-lobito-corridor, region=drc
INSERT IGNORE INTO wp_term_relationships (object_id, term_taxonomy_id) VALUES
  (1003, (SELECT tt.term_taxonomy_id FROM wp_term_taxonomy tt JOIN wp_terms t ON t.term_id = tt.term_id WHERE t.slug = 'public' AND tt.taxonomy = 'tier' LIMIT 1)),
  (1003, (SELECT tt.term_taxonomy_id FROM wp_term_taxonomy tt JOIN wp_terms t ON t.term_id = tt.term_id WHERE t.slug = 'sakania-lobito-corridor' AND tt.taxonomy = 'topic' LIMIT 1)),
  (1003, (SELECT tt.term_taxonomy_id FROM wp_term_taxonomy tt JOIN wp_terms t ON t.term_id = tt.term_id WHERE t.slug = 'democratic-republic-of-congo' AND tt.taxonomy = 'region' LIMIT 1));

-- Post 1004 (Essential Update — Railway award): tier=essential, topic=infrastructure, region=drc+angola
INSERT IGNORE INTO wp_term_relationships (object_id, term_taxonomy_id) VALUES
  (1004, (SELECT tt.term_taxonomy_id FROM wp_term_taxonomy tt JOIN wp_terms t ON t.term_id = tt.term_id WHERE t.slug = 'essential-tier' AND tt.taxonomy = 'tier' LIMIT 1)),
  (1004, (SELECT tt.term_taxonomy_id FROM wp_term_taxonomy tt JOIN wp_terms t ON t.term_id = tt.term_id WHERE t.slug = 'infrastructure' AND tt.taxonomy = 'topic' LIMIT 1)),
  (1004, (SELECT tt.term_taxonomy_id FROM wp_term_taxonomy tt JOIN wp_terms t ON t.term_id = tt.term_id WHERE t.slug = 'democratic-republic-of-congo' AND tt.taxonomy = 'region' LIMIT 1));

-- Post 1005 (Professional Dossier — Cobalt SAR): tier=professional, topic=critical-minerals+strategic-asset-reserve
INSERT IGNORE INTO wp_term_relationships (object_id, term_taxonomy_id) VALUES
  (1005, (SELECT tt.term_taxonomy_id FROM wp_term_taxonomy tt JOIN wp_terms t ON t.term_id = tt.term_id WHERE t.slug = 'professional-tier' AND tt.taxonomy = 'tier' LIMIT 1)),
  (1005, (SELECT tt.term_taxonomy_id FROM wp_term_taxonomy tt JOIN wp_terms t ON t.term_id = tt.term_id WHERE t.slug = 'critical-minerals' AND tt.taxonomy = 'topic' LIMIT 1)),
  (1005, (SELECT tt.term_taxonomy_id FROM wp_term_taxonomy tt JOIN wp_terms t ON t.term_id = tt.term_id WHERE t.slug = 'strategic-asset-reserve' AND tt.taxonomy = 'topic' LIMIT 1)),
  (1005, (SELECT tt.term_taxonomy_id FROM wp_term_taxonomy tt JOIN wp_terms t ON t.term_id = tt.term_id WHERE t.slug = 'democratic-republic-of-congo' AND tt.taxonomy = 'region' LIMIT 1));

-- Post 1006 (Public Brief — SAR intro): tier=public, topic=strategic-asset-reserve
INSERT IGNORE INTO wp_term_relationships (object_id, term_taxonomy_id) VALUES
  (1006, (SELECT tt.term_taxonomy_id FROM wp_term_taxonomy tt JOIN wp_terms t ON t.term_id = tt.term_id WHERE t.slug = 'public' AND tt.taxonomy = 'tier' LIMIT 1)),
  (1006, (SELECT tt.term_taxonomy_id FROM wp_term_taxonomy tt JOIN wp_terms t ON t.term_id = tt.term_id WHERE t.slug = 'strategic-asset-reserve' AND tt.taxonomy = 'topic' LIMIT 1));

-- =============================================================================
-- SECTION 4: ACF Post Meta (Analytical Claim, Public Excerpt, etc.)
-- =============================================================================

-- Post 1001 meta
INSERT IGNORE INTO wp_postmeta (post_id, meta_key, meta_value) VALUES
  (1001, 'analytical_claim', 'The US-DRC Strategic Partnership is the primary counterweight to Chinese economic engagement in Central Africa, with infrastructure and minerals supply chains as its central instruments.'),
  (1001, 'public_excerpt', 'A primer on the bilateral framework between the United States and the Democratic Republic of Congo — its scope, stakeholders, and how it differs structurally from the Belt and Road model.');

-- Post 1002 meta
INSERT IGNORE INTO wp_postmeta (post_id, meta_key, meta_value) VALUES
  (1002, 'analytical_claim', 'The May 2026 concession award shifts operational risk from the consortium onto host states, with implications for project finance terms over the next 18 months.'),
  (1002, 'public_excerpt', 'The concession award restructures the risk model of the Sakania-Lobito Corridor in ways that matter for any investor with exposure to Central African infrastructure. The Brief lays out the implications for financing terms, sovereign exposure, and partner alignment in the 2026–2028 window.');

-- Post 1003 meta
INSERT IGNORE INTO wp_postmeta (post_id, meta_key, meta_value) VALUES
  (1003, 'analytical_claim', 'The Sakania-Lobito Corridor is the most significant logistics infrastructure project in Central Africa in a generation, and the primary physical expression of the US-DRC Strategic Partnership.'),
  (1003, 'public_excerpt', 'A comprehensive explainer on the 1,344-kilometre railway system connecting the DRC-Zambia Copperbelt to the Atlantic port of Lobito — what it is, who is building it, and why it matters for critical minerals supply chains.');

-- Post 1004 meta
INSERT IGNORE INTO wp_postmeta (post_id, meta_key, meta_value) VALUES
  (1004, 'analytical_claim', 'The May 2026 concession award to a US-EU backed consortium marks a decisive shift in control of the Lobito Corridor away from state actors.'),
  (1004, 'linked_brief', '1002');

-- Post 1005 meta
INSERT IGNORE INTO wp_postmeta (post_id, meta_key, meta_value) VALUES
  (1005, 'analytical_claim', 'Cobalt scores highest in the SAR framework''s Supply Concentration dimension, making it the most strategically contested mineral in the US-DRC partnership context.'),
  (1005, 'public_excerpt', 'The Strategic Asset Reserve framework applied to cobalt: scoring supply concentration, substitutability, demand trajectory, and geopolitical alignment risk. Essential reading for institutional investors with African minerals exposure.');

-- Post 1006 meta
INSERT IGNORE INTO wp_postmeta (post_id, meta_key, meta_value) VALUES
  (1006, 'analytical_claim', 'The SAR framework provides a systematic, four-dimension scoring model for assessing which African assets should command strategic premium pricing from Western buyers.'),
  (1006, 'public_excerpt', 'An introduction to the Strategic Asset Reserve framework — the proprietary analytical model used in all Ascendance Dossiers and assessments. Explains the four scoring dimensions and how they generate investment theses.');

-- =============================================================================
-- SECTION 5: Two Test Subscriber Users
-- =============================================================================

-- Test Essential Subscriber
INSERT IGNORE INTO wp_users (
  ID, user_login, user_pass, user_nicename, user_email,
  user_registered, user_status, display_name
) VALUES (
  2001, 'test-essential',
  MD5('TestEssential2026!'),
  'test-essential',
  'test.essential@ascendance-test.invalid',
  NOW(), 0, 'Test Essential Subscriber'
);

INSERT IGNORE INTO wp_usermeta (user_id, meta_key, meta_value) VALUES
  (2001, 'wp_capabilities', 'a:1:{s:23:"ascendance_essential";b:1;}'),
  (2001, 'wp_user_level', '0'),
  (2001, 'ascendance_tier', 'essential'),
  (2001, 'ascendance_stripe_customer_id', 'cus_TEST_ESSENTIAL_001'),
  (2001, 'ascendance_subscription_status', 'active');

-- Test Professional Subscriber
INSERT IGNORE INTO wp_users (
  ID, user_login, user_pass, user_nicename, user_email,
  user_registered, user_status, display_name
) VALUES (
  2002, 'test-professional',
  MD5('TestProfessional2026!'),
  'test-professional',
  'test.professional@ascendance-test.invalid',
  NOW(), 0, 'Test Professional Subscriber'
);

INSERT IGNORE INTO wp_usermeta (user_id, meta_key, meta_value) VALUES
  (2002, 'wp_capabilities', 'a:1:{s:27:"ascendance_professional";b:1;}'),
  (2002, 'wp_user_level', '0'),
  (2002, 'ascendance_tier', 'professional'),
  (2002, 'ascendance_stripe_customer_id', 'cus_TEST_PROFESSIONAL_001'),
  (2002, 'ascendance_subscription_status', 'active');

-- =============================================================================
-- SECTION 6: Update AUTO_INCREMENT values
-- =============================================================================

ALTER TABLE wp_posts AUTO_INCREMENT = 2000;
ALTER TABLE wp_users AUTO_INCREMENT = 3000;
ALTER TABLE wp_usermeta AUTO_INCREMENT = 10000;
ALTER TABLE wp_postmeta AUTO_INCREMENT = 10000;
ALTER TABLE wp_terms AUTO_INCREMENT = 200;
ALTER TABLE wp_term_taxonomy AUTO_INCREMENT = 200;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- VERIFICATION QUERY — run after import to confirm
-- =============================================================================

SELECT
  p.ID,
  p.post_title,
  p.post_type,
  p.post_status,
  GROUP_CONCAT(DISTINCT CASE WHEN tt.taxonomy = 'tier' THEN t.name END) AS tier,
  GROUP_CONCAT(DISTINCT CASE WHEN tt.taxonomy = 'topic' THEN t.name END) AS topics,
  GROUP_CONCAT(DISTINCT CASE WHEN tt.taxonomy = 'region' THEN t.name END) AS regions
FROM wp_posts p
LEFT JOIN wp_term_relationships tr ON tr.object_id = p.ID
LEFT JOIN wp_term_taxonomy tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
LEFT JOIN wp_terms t ON t.term_id = tt.term_id
WHERE p.ID BETWEEN 1001 AND 1006
GROUP BY p.ID, p.post_title, p.post_type, p.post_status
ORDER BY p.ID;
