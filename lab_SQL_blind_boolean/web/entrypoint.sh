#!/bin/sh

openssl rand -hex 16 > /home/richard/user.txt
chown richard:richard /home/richard/user.txt
chmod 600 /home/richard/user.txt

openssl rand -hex 16 > /root/root.txt
chown root:root /root/root.txt
chmod 600 /root/root.txt

python3 /web/internal_web/app.py > /dev/null 2>&1 &

/usr/sbin/sshd

exec apache2-foreground
