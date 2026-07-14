# Deployment, Branch Strategy & Secrets Management

This document details the environment promotion workflow, deployment checklists, CI/CD pipeline, and secrets management protocol for the Ascendance Intelligence Platform.

---

## 1. Branching Strategy & Environment Promotion

The project uses three distinct environments to promote changes safely:

| Environment | URL | Git Branch | Database / Keys |
| :--- | :--- | :--- | :--- |
| **Local Development** | `ascendance.local` | `feature/*` | Local DB, Stripe **TEST** keys, Sandbox APIs |
| **Staging (UAT)** | `staging.ascendance-strategies.com` | `staging` | Staging DB, Stripe **TEST** keys, Sandbox APIs |
| **Production** | `ascendance-strategies.com` | `main` | Production DB, Stripe **LIVE** keys, Production APIs |

### Promotion Workflow
1. **Develop**: Create a branch `feature/your-feature-name` off the `staging` branch. Write code and test locally.
2. **Pull Request**: Open a PR from `feature/*` to `staging`. GitHub Actions automatically runs the CI syntax verification and builds the theme assets.
3. **Merge & Staging QA**: After passing CI and code review, merge into `staging`. Staging server pulls code, compiles assets, and runs tests. Stakeholders perform User Acceptance Testing (UAT).
4. **Production Release**: Open a PR from `staging` to `main`. After final approval, merge to `main` to trigger the production deployment.

---

## 2. Secrets Management & Environment Configuration

Secrets (database passwords, Stripe API keys, Brevo tokens) **must never** be committed to the Git repository. The platform loads configuration variables from a secure `.env` file located outside the web root (or inside the root with server-level protection).

### The `.env` Configuration Template
Ensure the following variables are populated:

```ini
# Environment Mode
WP_ENVIRONMENT_TYPE="production" # local | staging | production

# Database Connection
DB_NAME="ascendance_db"
DB_USER="db_user"
DB_PASSWORD="secure_db_password"
DB_HOST="localhost"

# Site URLs
WP_HOME="https://ascendance-strategies.com"
WP_SITEURL="https://ascendance-strategies.com"

# WordPress Security Salts (Generate via api.wordpress.org/secret-key/1.1/salt/)
AUTH_KEY="your-unique-key"
SECURE_AUTH_KEY="your-unique-key"
LOGGED_IN_KEY="your-unique-key"
NONCE_KEY="your-unique-key"
AUTH_SALT="your-unique-key"
SECURE_AUTH_SALT="your-unique-key"
LOGGED_IN_SALT="your-unique-key"
NONCE_SALT="your-unique-key"

# Integrations
ASCENDANCE_GTM_ID="GTM-XXXXXXX"
ASCENDANCE_NEWSLETTER_API_KEY="your-brevo-api-key"
ASCENDANCE_NEWSLETTER_LIST_ID="your-brevo-list-id"

# Payments (Stripe API)
STRIPE_PUBLISHABLE_KEY="pk_live_..."
STRIPE_SECRET_KEY="sk_live_..."
STRIPE_WEBHOOK_SECRET="whsec_..."
```

### File Security & Permissions
To prevent unauthorized read access, the server's `.env` file should have strict file permissions:
```bash
chmod 600 .env
```
Ensure the web server configuration denies access to `.env` files. (The `security-hardening.php` MU plugin includes rewrite rules that block requests targeting `.env` or `wp-config.php`).

---

## 3. Server Deployment Checklist

Follow these steps when performing a manual deployment or configuring CD scripts:

1. **Verify `.env`**: Confirm all secrets are set and active.
2. **Environment Checks**: Ensure the server is running PHP 8.2+.
3. **Pull Changes**:
   ```bash
   git pull origin staging # (or main for production)
   ```
4. **Install Dependencies**:
   ```bash
   # Root directory
   composer install --no-dev --optimize-autoloader --no-progress
   
   # Custom Theme directory
   cd wp-content/themes/ascendance
   npm ci
   npm run build
   ```
5. **Run DB Updates**: Perform any necessary schema updates via WP-CLI or database migrations.
6. **Flush Cache**:
   ```bash
   wp cache flush
   wp rewrite flush
   ```
7. **Verify Systems**: Run smoke tests (User logins, premium paywall triggers, checkout, API reachability).

---

## 4. Key Rotation Procedure

All API keys and credentials should be rotated at least **annually**, or immediately if a leak is suspected.

### Step 1 — Stripe Key Rotation
1. Log in to the [Stripe Dashboard](https://dashboard.stripe.com/).
2. Navigate to **Developers -> API Keys**.
3. Click **Roll Key** for the Secret Key, and specify an expiration window (e.g., 24 hours).
4. Copy the new Secret Key, paste it into the `.env` file on the server, and restart the PHP-FPM service to reload the environment.
5. If changing webhook urls, register the new webhook endpoint, copy the new Webhook Secret (`whsec_...`), update it in `.env`, and test checkout.

### Step 2 — Brevo (Newsletter) Key Rotation
1. Log in to your [Brevo Account](https://www.brevo.com/).
2. Navigate to **SMTP & API -> API Keys**.
3. Create a new API key, name it "Ascendance Prod", and copy it.
4. Update `ASCENDANCE_NEWSLETTER_API_KEY` in the server's `.env` file.
5. Revoke/delete the old API key.

### Step 3 — WordPress Salts Rotation
Use the provided automation utility to rotate security salts without manually editing the `.env` file:
```bash
php scripts/rotate-salts.php
```
*(This script automatically fetches fresh cryptographic salts from the official WordPress API, backs up your existing `.env` file, and updates the keys in place).*
