#!/bin/bash
set -e
for f in README.md docker-compose.yml Dockerfile composer.json config/config.php src/Core/Token.php Models/LegacySession.php plugins/LegacyMaintenance.php public/index.php public/api/v1/status.php maintenance/backup.sh ssh/sshd_config; do [ -f "$f" ] || { echo "missing: $f"; exit 1; }; done
echo 'structure OK'
