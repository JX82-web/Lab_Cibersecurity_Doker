#!/bin/bash

# Avvia il servizio OpenSSH
service ssh start

# Configura l'app Node.js interna (solo per l'IDE della porta 9000)
cd /var/www/html/app_internal
if [ ! -d "node_modules" ]; then
    npm init -y
    npm install express
fi
node server.js &

# Applica i permessi ed esegue il Bot in Bash
chmod +x bot_admin.sh
./bot_admin.sh &

# Avvia Apache in primo piano per tenere attivo il container
cd /var/www/html
apache2-foreground
