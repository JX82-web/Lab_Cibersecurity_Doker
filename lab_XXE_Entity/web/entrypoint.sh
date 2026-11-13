#!/bin/bash
set -e

mkdir -p /run/sshd
chmod 755 /run/sshd

# Keep SSH intentionally available for the lateral-movement stage.
# Password authentication is lab-only.
/usr/sbin/sshd

exec apache2-foreground
