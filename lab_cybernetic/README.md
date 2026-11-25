# cyber_site (nome del laboratorio scopo didattico ed accademico "reale") oggetto: introduzione di vulnerabilità "voluta"  di insecure deserializzazzion, deve permettere RCE tramite token

cyber-site/
├── composer.json                   # Dipendenze PHP (utile per simulare librerie vulnerabili)
├── config/
│   ├── app_config.json             # Configurazioni generali (es. modalità debug, percorsi)
│   └── security.json               # Impostazioni sessione/token (es. flag serializzazione)
│
├── public/                         # Root accessibile dal web server (Apache/Nginx)
│   ├── index.html                  # Vetrina prodotti e catalogo e-commerce
│   ├── login.html                  # Pagina di accesso
│   ├── dashboard.html              # Area personale utente (ordini e profilo)
│   │
│   ├── admin/                      # Area Amministrativa
│   │   ├── index.html              # Pannello di controllo admin
│   │   └── plugins.html            # Interfaccia gestione/caricamento plugin
│   │
│   ├── assets/
│   │   ├── css/
│   │   │   └── style.css           # Fogli di stile
│   │   └── js/
│   │       ├── auth.js             # Gestione login, logout e invio token/cookie
│   │       ├── shop.js             # Carrello e chiamata catalogo prodotti
│   │       └── admin.js            # Script per gestione utenti e upload plugin
│   │
│   └── uploads/                    # Cartella pubblica per file caricati
│       ├── avatars/                # Immagini profilo utenti
│       └── products/               # Immagini dei prodotti
│
├── plugins/                        # Directory esterna per i moduli estensibili
│   ├── installed.json              # Registro dei plugin attivi e relativi percorsi
│   └── discount-calc/              # Esempio di plugin installato
│       ├── manifest.json           # Metadati del plugin
│       └── plugin.php              # Codice eseguibile del plugin
│
└── src/                            # Codice sorgente backend (non accessibile direttamente)
    ├── Core/
    │   ├── SessionHandler.php      # Gestione token di sessione (punto critico per lab)
    │   ├── Database.php            # Connessione al DB (es. SQLite o MySQL)
    │   └── PluginLoader.php        # Caricamento ed esecuzione dinamica dei plugin
    │
    ├── Models/
    │   ├── User.php                # Modello utente e ruoli (user/admin)
    │   └── AuditLog.php            # Classe di logging (utile per gadget chain)
    │
    └── api/
        └── v1/                     # Endpoint REST API v1
            ├── auth/
            │   ├── login.php       # POST: verifica credenziali e rilascia token
            │   └── logout.php      # POST: distrugge la sessione
            ├── user/
            │   ├── dashboard.php   # GET: restituisce dati profilo e storico ordini
            │   └── upload.php      # POST: gestisce upload avatar/documenti
            ├── shop/
            │   └── products.php    # GET: lista prodotti in formato JSON
            └── admin/
                ├── users.php       # GET/DELETE: gestione utenti (richiede ruolo admin)
                └── install_plugin.php # POST: estrae e registra nuovi plugin (.zip/.php)



Come collegare i componenti nel laboratorio

• Flusso Autenticazione (login.html -> auth.js -> api/v1/auth/login.php):
Il form HTML invia le credenziali via fetch() in auth.js. L'endpoint login.php valida l'utente, istanzia session_andler.php per generare il token (o impostare il cookie) e risponde in JSON. logout.php invalida il token lato server o cancella il cookie.

• Dashboard & API v1 (dashboard.html -> api/v1/user/dashboard.php):
All'apertura della dashboard, auth.js interroga l'API passando il token di sessione. L'API usa session_handler.php per identificare l'utente prima di restituire i dati.

• Gestione Uploads (api/v1/user/upload.php -> public/uploads/):
Riceve file multipart dal frontend e li salva nelle sottocartelle di public/uploads/. Per il laboratorio, testa qui la validazione delle estensioni o dei MIME-type.

• Sistema Plugin & Area Admin (admin/plugins.html -> api/v1/admin/install_plugin.php -> plugins/):
L'admin carica un pacchetto plugin. L'API lo salva in plugins/, aggiorna installed.json e plugin_loader.php include dinamicamente i file .php attivi durante l'esecuzione dell'app.
Se vuoi approfondire, posso mostrarti il codice d'esempio per:

• Il file session_handler.php (con la logica di deserializzazione insicura (permette "rce" via token))

• L'endpoint install_plugin.php e il plugin_loader.php

• La configurazione del server (es. Docker o Nginx/Apache) per isolare la cartella src/



