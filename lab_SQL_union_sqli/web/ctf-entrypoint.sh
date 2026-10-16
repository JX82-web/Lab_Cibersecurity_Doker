#!/bin/bash

# 1. Genera la cartella dei backup DOPO il montaggio del volume e assegna i permessi corretti
mkdir -p /var/www/html/backup_tasks
chown user:user /var/www/html/backup_tasks
chmod 777 /var/www/html/backup_tasks

# 2. Assicura la presenza e i permessi della cartella uploads
mkdir -p /var/www/html/uploads
chmod 777 /var/www/html/uploads

# 3. Avvio dei servizi di background
/usr/sbin/sshd
cron
/usr/bin/supervisord -c /etc/supervisor/supervisord.conf

# 4. Avvia Apache in primo piano
exec apache2-foreground
