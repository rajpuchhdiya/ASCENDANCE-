#!/usr/bin/env bash
# Upload latest DB backups to S3. Requires AWS CLI configured and AWS_S3_BUCKET env var.

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
BACKUP_DIR="$ROOT_DIR/wp-content/backups"

if [ -z "${AWS_S3_BUCKET:-}" ]; then
  echo "AWS_S3_BUCKET not set. Export it and rerun. Example: export AWS_S3_BUCKET=my-bucket/path" >&2
  exit 2
fi

if [ ! -d "$BACKUP_DIR" ]; then
  echo "Backup dir not found: $BACKUP_DIR" >&2
  exit 2
fi

echo "Uploading backups from $BACKUP_DIR to s3://$AWS_S3_BUCKET/" 

for f in "$BACKUP_DIR"/*.sql.gz; do
  [ -e "$f" ] || continue
  echo "Uploading $f..."
  aws s3 cp "$f" "s3://$AWS_S3_BUCKET/" --acl private
done

echo "Upload complete. Consider lifecycle policies on S3 to expire old backups." 
