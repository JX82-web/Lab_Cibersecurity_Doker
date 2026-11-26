#!/bin/bash
set -e
mkdir -p /run/sshd
chown root:root /run/sshd
chmod 755 /run/sshd
apache2-foreground &
APACHE_PID=$!
/usr/sbin/sshd
wait "$APACHE_PID"
