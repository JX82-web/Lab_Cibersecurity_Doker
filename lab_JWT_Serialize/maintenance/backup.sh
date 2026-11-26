#!/bin/bash
set -e
HOOK="/opt/maintenance/hooks/pre_backup.sh"
if [ -x "$HOOK" ] || [ -f "$HOOK" ]; then
    "$HOOK"
fi
mkdir -p /var/backups/cyber_site
date -Is > /var/backups/cyber_site/last-backup.txt
echo "backup completed"
