#!/bin/sh
# Nightly backups: a gzipped MySQL dump and an archive of uploaded files,
# written to ./backups on the server. Old backups are pruned after BACKUP_KEEP_DAYS.
set -eu

INTERVAL="${BACKUP_INTERVAL_SECONDS:-86400}"
KEEP_DAYS="${BACKUP_KEEP_DAYS:-14}"

mkdir -p /backups

while true; do
    stamp="$(date +%Y%m%d-%H%M%S)"

    if mysqldump -h db -u "$DB_USERNAME" --single-transaction --quick --routines --no-tablespaces "$DB_DATABASE" | gzip > "/backups/db-$stamp.sql.gz.tmp"; then
        mv "/backups/db-$stamp.sql.gz.tmp" "/backups/db-$stamp.sql.gz"
        echo "[backup] database saved: db-$stamp.sql.gz"
    else
        rm -f "/backups/db-$stamp.sql.gz.tmp"
        echo "[backup] database dump FAILED" >&2
    fi

    if [ -d /storage/app/public ]; then
        tar -czf "/backups/uploads-$stamp.tar.gz" -C /storage/app public && echo "[backup] uploads saved: uploads-$stamp.tar.gz"
    fi

    find /backups -type f \( -name 'db-*.sql.gz' -o -name 'uploads-*.tar.gz' \) -mtime +"$KEEP_DAYS" -delete

    sleep "$INTERVAL"
done
