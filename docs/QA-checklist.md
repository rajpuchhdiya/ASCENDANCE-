# QA Checklist — Critical User Flows

Objective: Validate anonymous browsing, subscriber onboarding & return, editor/admin workflows, and payment edge cases so there are no launch-blocking issues.

How to use:
- Follow each flow manually in a staging environment.
- Mark "Pass/Fail" and add short reproduction steps for failures.
- Attach screenshots or log snippets to the ticket created for any blocker.

1. Anonymous Visitor
- Visit homepage and >3 content pages: Pass/Fail
- Verify public assets (CSS/JS) load with correct cache headers: Pass/Fail
- Confirm no PII or admin endpoints exposed: Pass/Fail
- Verify cookie banner displays and blocking for analytics until consent: Pass/Fail

2. New Subscriber Sign-up
- Register with email (no 2FA): Pass/Fail
- Receive expected welcome email and account activation (if any): Pass/Fail
- Confirm subscription product page, checkout, and receipt pages render: Pass/Fail
- Verify subscriber role and content gating works (premium content hidden): Pass/Fail

3. Returning Subscriber
- Login flow: username/password → second factor if set: Pass/Fail
- Remember-me/session resume behavior: Pass/Fail
- Verify access to previously purchased content: Pass/Fail
- Test expired/renewal flows: Pass/Fail

4. Editor / Admin Flows
- Admin login enforces 2FA and works with Wordfence protections: Pass/Fail
- Editor publishes and updates posts; revisions saved: Pass/Fail
- Media uploads work (file types and limits): Pass/Fail
- Admin AJAX endpoints respond normally under WAF: Pass/Fail

5. Payment Edge Cases
- Successful payment checkout flow: Pass/Fail
- Failed payment (card declined) error path and retry: Pass/Fail
- Refund processed and user access revoked if applicable: Pass/Fail
- Subscription cancellation and webhook handling: Pass/Fail
- Idempotency of webhooks / duplicate notifications: Pass/Fail

6. Security / Ops Checks
- Wordfence scan report: no critical findings (list any): Pass/Fail
- Scheduled backups present and latest backup < 24h old: Pass/Fail
- Salts rotation tool runs and creates backup: Pass/Fail
- hCaptcha on login (once keys provided) blocks automated login attempts: Pass/Fail

7. Acceptance Criteria (Launch Blockers)
- No critical or high severity Wordfence issues unresolved.
- Admin login and 2FA enforced and tested for at least two admin accounts.
- Payment flows successful with correct entitlement mapping.
- Backups are scheduled and offsite upload configured.
- GDPR consent is blocking analytics until explicit consent.

Notes / Tickets:
- Create a ticket per failing item and link it here with reproduction steps and rollback plan.
