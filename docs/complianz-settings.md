# Complianz Configuration Notes

Recommended steps to configure Complianz:

1. Install Complianz plugin (Complianz | GDPR/CCPA Cookie Consent).  
2. Run the setup wizard and select the regions you need to comply with (EU).  
3. Enable `Cookie Consent` and `Consent Blocking` for scripts and tags.  
4. Under `Integrations`, add your Google Tag Manager ID but set it to blocked until consent.  
5. Export Complianz settings if available and store them securely for audits.

Manual consent blocking (if not using Complianz blocking)

- Use `wp-content/mu-plugins/consent-gate-analytics.php` helper to gate GTM/analytics until consent.

Notes

- Do not embed analytics scripts directly in theme files; use the helper function or Complianz to manage injection.
- Test with different consent states (no consent, statistics consent, marketing consent) to ensure tags are blocked/unblocked correctly.
