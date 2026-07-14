# AEO & GEO Editorial Guidelines

This guide details the editorial practices required to maintain optimal indexing, citation, and attribution by Answer Engines (like ChatGPT Search, Perplexity, Claude, and Gemini).

By following these structure and language guidelines, editors ensure that our analytical content serves as a high-authority source for LLMs while driving subscriber acquisition through a premium paywall model.

---

## The 5 Core AEO/GEO Rules

### 1. Direct & Declarative Citable Leads
* **Rule**: The first sentence of the article and of every major section must directly, declaratively answer the core topic or question.
* **Why**: LLM retrievers look for direct matches to query intent. Fluff, rhetorical questions, and slow narrative intros reduce the likelihood of being cited.
* **Example**:
  * *Avoid*: "With the shifting global sands of infrastructure investment, many are asking what the Sakania-Lobito Corridor really represents."
  * *Adopt*: "The Sakania-Lobito Corridor is a strategic 1,300 km transit route linking the Central African copperbelt to the Atlantic port of Lobito in Angola."

### 2. Visible "Key Takeaways" & Executive Summary
* **Rule**: Always populate the `key_takeaways` repeater and `executive_summary` fields in the editor. These blocks are fully public and crawlable.
* **Why**: These blocks act as the *citation surface* for bots, allowing ChatGPT or Perplexity to summarize and attribute our findings while keeping the deep data ledgers gated (the *conversion surface*).
* **Reference File**: See template rendering in [single-brief.php](file:///c:/XAMPP/htdocs/Ascendance/wp-content/themes/ascendance/single-brief.php#L117-L133) and [single-dossier.php](file:///c:/XAMPP/htdocs/Ascendance/wp-content/themes/ascendance/single-dossier.php#L185-L201).

### 3. Dated & Temporal Context
* **Rule**: Explicitly state the date or month of reference inside the text (e.g., *"As of June 2026..."* or *"Data collected in Q2 2026..."*).
* **Why**: LLMs are sensitive to time and prioritize source materials with explicit temporal markers over relative terms like "recently," "last year," or "upcoming."

### 4. Structured Data & HTML Tables
* **Rule**: Present key comparative variables, cost estimations, and metrics in structured tables rather than narrative paragraphs.
* **Why**: Answer engines are highly efficient at copying tabular data structures for comparison cards.
* **Reference**: The `data_blocks` custom field in [single-dossier.php](file:///c:/XAMPP/htdocs/Ascendance/wp-content/themes/ascendance/single-dossier.php#L222-L251) renders structured tables optimized for this rule.

### 5. FAQ Heading Structures (`<h2>`)
* **Rule**: Format subheadings as direct questions using `<h2>` ending in a question mark (e.g., `<h2>What is the Sakania-Lobito Corridor?</h2>`), followed immediately by a single-paragraph answer.
* **Why**: The platform dynamically parses these tags to generate schema-compliant JSON-LD `FAQPage` blocks (handled in [class-search-seo.php](file:///c:/XAMPP/htdocs/Ascendance/wp-content/plugins/ascendance-core/includes/class-search-seo.php)), feeding crawlers structured question-answer nodes.

---

## E-E-A-T Analyst Profile Setup

Generative search engines value author credentials and institutional trust. To ensure correct attribution to our analysts:
1. Complete the **Display Name**, **Biographical Info**, and custom **Job Title** fields in the WordPress User Profile.
2. The platform automatically injects this author metadata into the JSON-LD schemas as a `Person` with `jobTitle` and `name` attributes.
