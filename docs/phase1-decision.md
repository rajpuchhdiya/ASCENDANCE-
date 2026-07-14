# Phase 1 Scope Decision Memo

Date: 2026-06-17

Purpose: Resolve whether Phase 1 follows the custom-build Master scope, the PMPro budget scope, or a hybrid MVP. This memo summarizes tradeoffs, recommended baseline, risks, and required approvals.

Options
- Master (Custom-build): Full-featured, bespoke membership, custom paywall, advanced templates, integrations. High engineering effort, longer schedule, higher cost.
- PMPro Budget Scope: Use Paid Memberships Pro (PMPro) + plugins for membership, Stripe, basic account pages and paywall. Faster, lower cost, constrained UX and extensibility.
- Hybrid MVP: Core editorial content types + lightweight custom theme and PMPro for payments/membership; implement server-side paywall hooks for critical gating later.

Evaluation Criteria
- Time-to-market / budget
- Security & compliance (paywall, billing, user data)
- Extensibility for Tasks 4–21
- Operational overhead (maintenance, hosting)

Recommendation (Baseline)
- Adopt the Hybrid MVP as Phase 1 baseline.
  - Reason: balances launch-time risk, budget, and future extensibility. Allows launching a gated subscription product quickly using PMPro for billing while delivering custom content types, theme foundation, and server-side paywall hooks that can be iterated into a full custom system in Phase 2.

Key Deliverables for Phase 1 (Hybrid MVP)
- PMPro configured for Essential/Professional/Enterprise tiers with Stripe test/live keys. (Task 11/12)
- Custom CPTs (Brief/Update/Dossier) and ACF field groups exportable to Git. (Task 6/7)
- Theme skeleton with token system and article templates (Task 4/5/10)
- Server-side paywall filters that never expose gated content to unauthorized users (Task 13)
- Subscriber account pages linking to Stripe portal (Task 14)

Risks & Mitigations
- Plugin lock-in (PMPro): Mitigate by abstracting membership logic via a small compatibility layer and storing access metadata in CPTs/usermeta.
- Security & PCI: Use Stripe-hosted checkout, enforce HTTPS, rotate keys, and require audits before production.
- Scope creep: Enforce minimal MVP acceptance criteria and defer advanced UX to Phase 2.

Approvals Required
- Client or leadership sign-off on baseline (Hybrid MVP).  
- Budget approval for PMPro licenses and Stripe transactions.  

Next Steps
1. Get sign-off from client/leadership on Hybrid MVP baseline.  
2. If approved, proceed to Task 2 (repo + branches) and Task 3 runtime hardening (already in progress).  
3. Create PMPro configuration plan and migration/rollback strategy.

Prepared by: Engineering
