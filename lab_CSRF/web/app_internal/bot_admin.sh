#!/bin/bash

REPORT_FILE="/tmp/reported_url.txt"

echo "[+] Bot Amministratore Bash in ascolto della coda..."

while true; do
    # Verifica se c'è un link nella coda delle segnalazioni
    if [ -f "$REPORT_FILE" ] && [ -s "$REPORT_FILE" ]; then
        TARGET_URL=$(cat "$REPORT_FILE")
        echo "[🤖 BOT] Nuova segnalazione rilevata: $TARGET_URL"
        # Svuota immediatamente la coda per evitare loop infiniti
        rm -f "$REPORT_FILE"

        # PASSO 1: L'Amministratore esegue il login locale su Apache e memorizza i cookie
        curl -s -c /tmp/admin_cookie.txt \
             -d "username=admin&password=lovesucks1" \
             http://127.0.0.1/login.php > /dev/null

        # PASSO 2: Il Bot interroga il tuo server Python esterno (Macchina Host)
        # Questo comando genera il log "GET /malicious.html" sul tuo terminale host
        curl -s -b /tmp/admin_cookie.txt "$TARGET_URL" > /dev/null
        echo "[✅ BOT] Link dell'attaccante visitato con successo."

        # PASSO 3: Il Bot invia la POST di promozione a admin.php (Simulazione dell'auto-submit)
        curl -s -b /tmp/admin_cookie.txt \
             -d "user_id=3&new_role=admin" \
             http://127.0.0.1/app_internal/admin.php > /dev/null

        echo "[🎯 SUCCESS] Richiesta CSRF elaborata. Ruolo aggiornato sul database."
    fi
    sleep 5
done

