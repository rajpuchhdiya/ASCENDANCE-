# Ascendance Strategies — Subscription Intelligence Platform

Phase 1 build of the subscription-gated intelligence publication for Ascendance Strategies. Serves government bodies, investors, and corporates with niche market intelligence on the US-DRC Strategic Partnership, critical minerals, and the Sakania-Lobito Corridor.

---

## 1. Architecture Overview

- **CMS Engine**: WordPress 6.6+ running on PHP 8.2+.
- **Database**: MySQL 8.0+ or MariaDB 10.6+.
- **Membership & Commerce**: Paid Memberships Pro (PMPro) Free core handles user registrations, accounts, and Stripe payment gateway connections.
- **Custom Post Types**:
  - `brief`: Flagship intelligence briefs (1,500 - 3,000 words).
  - `update`: Real-time short-form updates linked to parent briefs.
  - `dossier`: Living version-controlled documents with stakeholder tracking.
- **Custom Modules (Ascendance Core Plugin)**:
  - **AI Editorial Studio**: Switchable Anthropic Claude / GPT-4o / Gemini 1.5 Pro drafting helper with block-level regeneration.
  - **Mission Control Dashboard**: Custom console widget page in dark Data Terminal register style displaying subscriber stats, AI logs, recent activity, and site health.
  - **Custom Paywall**: Elegant server-side HTML-safe gating (preserving the first paragraph and rendering the paywall block) tied to PMPro subscription tiers.
  - **Weighted Search**: Natively weighted search scoring (Title 10x, Excerpt 7x, Content 3x) inside `posts_clauses` filters.
  - **SEO & Schema**: Custom JSON-LD schema generation for Google NewsArticle and FAQPage structures.

---

## 2. Project Directory Structure

```
ascendance-platform/                      (Git repository root)
├── .env.example                          (Template for credentials)
├── .gitignore                            (Excludes config/uploads/dependencies)
├── README.md                             (This guide)
├── wp-config.php                         (WordPress config - excluded from Git)
├── wp-content/
│   ├── plugins/
│   │   ├── ascendance-core/              (Our custom integration plugin)
│   │   │   ├── ascendance-core.php       (Plugin bootstrap)
│   │   │   └── includes/
│   │   │       ├── class-acf-fields.php  (ACF programmatic settings)
│   │   │       ├── class-ai-studio.php   (AI adapter, logging, endpoints)
│   │   │       ├── class-cpt-taxonomy.php(CPT & Taxonomy registrations)
│   │   │       ├── class-mission-control.php(Console dashboard & log tables)
│   │   │       ├── class-paywall.php     (Gating logic & CTA container)
│   │   │       └── class-search-seo.php  (Weighted search & LD-JSON schema)
│   │   ├── paid-memberships-pro/         (Free core dependency)
│   │   └── advanced-custom-fields/       (Free core dependency)
│   └── themes/
│       └── ascendance/                   (Custom theme)
│           ├── style.css                 (Theme styles with brand custom properties)
│           ├── functions.php             (Enqueues & theme setups)
│           ├── single-brief.php          (Editorial register brief layout)
│           └── single-dossier.php        (Dossier register layout)
```

---

## 3. Git Branching Model

We maintain three environment-aligned branches:

1. **`main` (Production)**:
   - Holds live production code.
   - Deployments to `main` must come only via Pull Request from `staging`.
   - Never commit directly to `main`.
2. **`staging` (UAT & Testing)**:
   - Deployed on `staging.ascendance-strategies.com` for client testing.
   - Synchronized with `main` before starting new feature cycles.
3. **`feature/*` (Development)**:
   - Offshoots of `staging` for specific development updates.
   - Merged back to `staging` via PR once verified.

---

## 4. Local Setup Guide

Follow these steps to initialize your local development environment:

1. **Clone the repository**:
   ```bash
   git clone <repo-url> ascendance
   ```
2. **Create local configuration**:
   - Copy `.env.example` to `.env`
   - Fill in your local database credentials, Stripe credentials, and AI keys.
3. **Initialize the Database**:
   - Create a clean MySQL database named `ascendance`.
   - Ensure XAMPP/LocalWP is running.
4. **Compile Frontend Assets**:
   - Navigate to the custom theme:
     ```bash
     cd wp-content/themes/ascendance
     npm install
     npm run build

---

## Repository Bootstrap (first-time setup)

- Create branches locally and push:
```bash
git checkout -b staging origin/staging || git checkout -b staging
git checkout -b feature/your-feature
git push -u origin staging
git push -u origin feature/your-feature
```
- Ensure `.gitignore` exists (see [.gitignore](.gitignore)).
- Add project-wide deploy notes in [DEPLOY.md](DEPLOY.md).
     ```

---

## 5. Deployment Workflow

### Deploying to Staging:
1. Push your completed feature branch to the remote origin.
2. Open a Pull Request from `feature/your-feature` to `staging`.
3. Once the PR is merged, SSH to the staging server:
   ```bash
   cd /var/www/staging
   git pull origin staging
   composer install --no-dev
   wp cache flush
   ```
4. Verify functionality in the browser and complete user acceptance testing (UAT).

### Deploying to Production:
1. Open a Pull Request from `staging` to `main`.
2. Merge the PR once approved by the project lead.
3. SSH to the production server:
   ```bash
   cd /var/www/production
   git pull origin main
   composer install --no-dev --optimize-autoloader
   wp cache flush
   ```
4. Perform smoke tests for 15 minutes to verify checkout flows and dashboard connectivity.

---

## 6. Secrets Management & Rotation

- **No Secrets in Code**: API keys (Stripe, Anthropic, Brevo, salts) must never be committed to git. Always use `.env` files.
- **Key Rotation Protocol**:
  - API keys should be rotated annually.
  - Update values in the server's local `.env` file (mode `600`).
  - Restart PHP-FPM to clear variables cache if necessary:
    ```bash
    sudo systemctl restart php8.2-fpm
    ```
