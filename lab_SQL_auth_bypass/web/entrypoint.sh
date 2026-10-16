#!/bin/bash
set -e

mkdir -p /run/sshd
chown -R www-data:www-data /var/www/html
chmod 755 /var/www/html

# Start SSH for the lateral-movement stage.
/usr/sbin/sshd

# Apache remains PID 1.
exec apache2-foreground
