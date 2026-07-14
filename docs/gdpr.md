# GDPR Compliance Overview

This document outlines steps to reach GDPR compliance for Ascendance: install and configure Complianz, enable consent blocking for analytics, publish privacy & cookie policies, and implement a Subject Access Request (SAR) workflow.

High-level tasks

- Install and configure Complianz (or equivalent CMP) with Consent Blocking enabled. Use server `.env` for keys and set cookie retention defaults.
- Ensure analytics and marketing tags are blocked until explicit consent (see `wp-content/mu-plugins/consent-gate-analytics.php`).
- Publish Privacy Policy and Cookie Policy pages from the templates in this repo and link them in the site footer and Complianz configuration.
- Implement SAR workflow: provide a contact endpoint, verify requestor identity, export user data and log the request, respond within 30 days.

Notes on Complianz

- Complianz can generate tailored policy pages and enable automatic cookie blocking. Prefer Complianz Premium for advanced cookie blocking and geolocation features, otherwise configure manually.
- Export Complianz settings and keep a copy in a secure internal location (do not commit secrets).

Consent blocking guidance

- Do not initialize Google Tag Manager, GA4, or other marketing pixels until consent for `statistics` and/or `marketing` categories is present.
- Use the helper `ascendance_enqueue_analytics()` provided in `wp-content/mu-plugins/consent-gate-analytics.php` to gate tag insertion.

Data retention & minimization

- Minimize personal data retention: set database retention for logs and backups to a reasonable period (e.g., 1 year) and document retention rules.
- Use hashed identifiers where possible for analytics and anonymize IPs in GA4.

SAR workflow summary (details in `editor-runbook-gdpr.md`)

- Receive request via email/form and create an internal ticket.
- Verify identity using two-factor or government ID as defined by privacy officer.
- Export data using WP-CLI export or custom plugin tools and provide secure transfer to requester.
- Log the action and retain copy for compliance records.
