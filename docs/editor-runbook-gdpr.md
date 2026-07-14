# Editor Runbook: GDPR & SAR Handling

Purpose: Quick operational steps for editors and support staff when handling privacy requests and maintaining site compliance.

Receiving a SAR

1. Triage: Log the request in the issue tracker and assign to Privacy Officer.  
2. Verify identity: Request two pieces of evidence (email confirmation + ID) as per internal policy.  
3. Acknowledge receipt within 7 days and provide expected timeline (max 30 days).

Exporting user data

- Use WP-CLI to export personal data for a user:

```bash
wp export --post_type=any --start_date=1970-01-01 --user=<user_email_or_id> --filename_format=userdata-<id>.xml
```

- Or use the built-in Personal Data Export tool (Tools → Export Personal Data).

Responding

- Provide exported data via a secure transfer (password-protected file or secure link).  
- Record the transfer in the compliance log with timestamp and method.

Deletion requests

- Validate and then use Tools → Erase Personal Data or WP-CLI `wp user delete <id> --reassign=<id>` after confirming backups.

Logging & retention

- Keep an internal log of SARs for at least 3 years.  
- Note the identity verification steps taken and the response timestamp.
