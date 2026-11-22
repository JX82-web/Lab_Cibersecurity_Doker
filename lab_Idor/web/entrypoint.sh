#!/bin/bash

set -e

echo "=================================="
echo " Docker Container - Startup"
echo "=================================="

echo "[+] Preparing Cron..."
mkdir -p /var/run

echo "[+] Starting Cron..."
cron

echo "[+] sshd prepare"

mkdir -p /var/run/sshd

echo "[+] Start sshd"
/usr/sbin/sshd

echo "[+] Starting Apache..."
exec apache2-foreground
