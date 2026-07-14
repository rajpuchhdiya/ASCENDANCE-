# Backup Runbook — Ascendance

Purpose: Ensure database and uploads are backed up, verified, and stored offsite.

Existing scripts
- `bin/db-backup.php` — creates a gzipped mysqldump and rotates backups (keeps last 14).
- `scripts/backup-to-s3.sh` — uploads backups to S3 (requires AWS CLI and `AWS_S3_BUCKET` env var).

Daily Backup Procedure
1. Confirm `bin/db-backup.php` runs successfully via cron/Task Scheduler.
2. Verify new backup file exists in `backups/` with timestamp and permissions set.
3. Check offsite upload: confirm file appears in S3 bucket or remote storage.
4. Retention: backups older than 14 days are rotated out by script.

Restore Procedure (DB)
1. Put site in maintenance mode.
2. Stop writes (quiesce background jobs if any).
3. Locate desired backup file: `backups/db_YYYYmmdd_HHMM.sql.gz`.
4. Decompress and import:
   - On Windows with WAMP/XAMPP:
     ```powershell
     gzip -d db_YYYYmmdd_HHMM.sql.gz
     mysql -u DB_USER -p DB_NAME < db_YYYYmmdd_HHMM.sql
     ```
5. Verify site starts, test admin login and sample pages.
6. Exit maintenance mode.

Restore Procedure (Files)
- If uploads missing, fetch `wp-content/uploads/YYYY/...` from S3 or backup archive and copy to `wp-content/uploads/` with correct ownership and permissions.
- Run `wp media regenerate` if you restore older files and thumbnails need rebuilding.

Monitoring & Alerts
- Configure monitoring to alert if daily backup fails or offsite upload returns error.
- Periodically (weekly) test full restore on staging.

Security
- Store backups encrypted at rest (S3 server-side encryption or client-side GPG).
- Limit access to backup storage to operations team only.

