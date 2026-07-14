# Credentials Inventory (Template)

Important: DO NOT commit real credentials to the repository. Store secrets in a secure vault (1Password, AWS Secrets Manager, HashiCorp Vault) and share using secure channels.

Template fields (one entry per secret):

- Service: e.g. AWS S3, Stripe, hCaptcha, Mailgun
- Purpose: short description
- Account / Username: administrative account identifier
- Location: where the secret is stored (Vault name / path)
- Access: list of team members with access (roles)
- Rotation cadence: e.g. 90 days
- Last rotated: YYYY-MM-DD
- Recovery steps: how to reset the credential
- Notes: any special instructions

Example (DO NOT FILL WITH REAL VALUES HERE):
- Service: Stripe (live)
- Purpose: Payment processing for subscriptions
- Account / Username: ascendance@company.com
- Location: 1Password vault "Ascendance/Payment"
- Access: ops@company.com, devlead@company.com
- Rotation cadence: 365 days
- Last rotated: 2026-01-10
- Recovery steps: Contact Stripe owner, regenerate API keys, update `.env`, restart PHP-FPM

Recommended workflow
- Keep the `credentials-inventory` document in the private vault or a secure internal wiki, not in the repo.
- For deployments, use environment-level secrets (e.g., platform env vars or secrets manager) and never write production secrets into files on disk.

