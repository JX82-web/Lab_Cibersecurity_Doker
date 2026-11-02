# 🧪 Lab — Log Poisoning & LFI (ShopCore Enterprise)

Lab didattico containerizzato basato su Docker Compose, pensato per esercitarsi su
**Local File Inclusion (LFI)** e **Log Poisoning** in un ambiente realistico a più
container, con un servizio interno "nascosto" raggiungibile solo dalla rete
interna del lab.

> ⚠️ **Disclaimer**
> Questo ambiente è stato costruito **esclusivamente a scopo didattico e di
> auto-formazione** su tematiche di sicurezza offensiva (stile CTF / HackTheBox).
> Contiene **vulnerabilità introdotte intenzionalmente**, credenziali deboli e
> servizi volutamente non sicuri (SSH con `PermitRootLogin`/`PasswordAuthentication`
> abilitati, password hardcoded, LFI non filtrato, ecc.).
> **Non esporre mai questo lab su reti pubbliche o internet.** Usalo solo in
> locale o in un ambiente isolato di cui hai il controllo.

---

## 📐 Architettura

Il lab simula un piccolo portale e-commerce aziendale ("ShopCore Enterprise")
composto da tre servizi containerizzati, collegati da un'unica rete bridge interna:

```
                     ┌───────────────────────────┐
   Host (tu)  ──────▶│   web_server (Apache+PHP)  │  porte esposte: 80, 2222
                     │   htb_ecommerce_web        │
                     └───────────────┬────────────┘
                                     │  rete interna: dmz_network
                     ┌───────────────┼────────────┐
                     ▼                             ▼
      ┌───────────────────────┐      ┌───────────────────────────┐
      │ htb_mysql_internal    │      │ internal_app               │
      │ MySQL 5.7             │      │ htb_python_internal         │
      │ (nessuna porta esposta│      │ servizio Python interno     │
      │  verso l'host)        │      │ (nessuna porta esposta      │
      │                       │      │  verso l'host)              │
      └───────────────────────┘      └───────────────────────────┘
```

| Servizio               | Container               | Immagine base      | Porte pubblicate verso l'host |
|-------------------------|--------------------------|---------------------|-------------------------------|
| `web_server`            | `htb_ecommerce_web`     | `php:8.1-apache`   | `80 → 80`, `2222 → 22`         |
| `htb_mysql_internal`    | `htb_mysql_internal`    | `mysql:5.7`         | nessuna (solo rete interna)   |
| `internal_app`          | `htb_python_internal`   | `python:3.9-slim`   | nessuna (solo rete interna)   |

Solo il `web_server` è raggiungibile dall'host; gli altri due servizi vivono
esclusivamente dentro la rete Docker `dmz_network` e sono pensati per essere
raggiunti **dall'interno** del container web, una volta ottenuto accesso ad esso.

---

## 🎯 Obiettivi del lab

Il lab copre concetti legati a:

- Enumerazione di un'applicazione web PHP con routing basato su parametro GET
- **Local File Inclusion (LFI)**
- **Log Poisoning** (sia via log applicativi del webserver, sia via log di sistema)
- Pivoting verso servizi interni non esposti pubblicamente
- Concatenazione di più vulnerabilità per ottenere esecuzione di codice

Non è presente alcuna soluzione, walkthrough o writeup in questo repository:
il percorso di attacco va scoperto autonomamente.

---

## ✅ Prerequisiti

- [Docker](https://docs.docker.com/get-docker/) (versione recente)
- [Docker Compose](https://docs.docker.com/compose/) (plugin `docker compose`, non il vecchio `docker-compose` standalone)
- Porte **80** e **2222** libere sull'host
- ~1 GB di spazio libero su disco per le immagini
- Su **Mac Apple Silicon / ARM**: il lab forza l'emulazione `linux/amd64` per
  MySQL 5.7 (non disponibile in versione nativa ARM), quindi assicurati che
  Rosetta/emulazione QEMU sia abilitata in Docker Desktop.

---

## 🚀 Installazione e avvio

1. **Clona il repository**

   ```bash
   git clone <url-del-tuo-repo>
   cd lab_log_poisoning
   ```

2. **Build delle immagini e avvio dei container**

   ```bash
   docker compose up --build -d
   ```

   Il primo avvio può richiedere qualche minuto (build delle immagini,
   inizializzazione del database MySQL).

3. **Verifica che tutti i container siano up e sani**

   ```bash
   docker compose ps
   ```

   Il servizio `htb_mysql_internal` deve risultare `healthy` prima che
   `web_server` parta correttamente (dipendenza gestita automaticamente via
   healthcheck).

4. **Accedi al portale web**

   Apri il browser su:

   ```
   http://localhost/
   ```

5. **(Opzionale) Verifica rapida dei log interni ai container**

   ```bash
   docker compose logs web_server --tail=50
   docker exec -it htb_ecommerce_web ls -la /var/log/apache2/
   ```

---

## 🧰 Comandi utili

| Azione                                   | Comando                                      |
|-------------------------------------------|-----------------------------------------------|
| Avviare il lab                            | `docker compose up -d`                        |
| Avviare forzando il rebuild delle immagini| `docker compose up --build -d`                |
| Fermare il lab (mantenendo i volumi)      | `docker compose stop`                         |
| Fermare e rimuovere container/rete        | `docker compose down`                         |
| Fermare e rimuovere **anche i volumi**    | `docker compose down -v`                      |
| Vedere i log di un servizio in tempo reale| `docker compose logs -f web_server`           |
| Entrare in una shell del container web    | `docker exec -it htb_ecommerce_web bash`      |
| Ricostruire da zero senza cache           | `docker compose build --no-cache`             |

---

## 🩺 Troubleshooting (operativo, non relativo alle vulnerabilità)

- **La pagina web risponde con errore di connessione al database al primo avvio**
  → attendi qualche secondo: l'healthcheck su MySQL garantisce che il web server
  parta solo a database pronto, ma se stai ricostruendo da zero il primo
  provisioning di MySQL può richiedere un po' di tempo.

- **`docker compose up` non applica le modifiche fatte al codice/Dockerfile**
  → Compose non ricostruisce automaticamente le immagini già presenti. Usa
  sempre `docker compose up --build -d` oppure `docker compose build --no-cache`
  dopo ogni modifica ai Dockerfile.

- **Porta 80 o 2222 già in uso sull'host**
  → modifica il mapping delle porte in `docker-compose.yml` (es. `"8080:80"`).

- **Su Mac Apple Silicon il container MySQL non parte**
  → verifica che l'emulazione `linux/amd64` sia abilitata in Docker Desktop
  (Settings → General → "Use Rosetta for x86/amd64 emulation").

---

## 📁 Struttura del progetto

```
lab_log_poisoning/
├── db/
│   └── init.sql              # Schema e dati iniziali del database
├── internal_service/
│   ├── Dockerfile
│   └── internal_app.py       # Servizio Python interno
├── web/
│   ├── Dockerfile
│   ├── config.php
│   ├── index.php             # Router centralizzato dell'applicazione
│   ├── logout.php
│   └── pages/
│       ├── dashboard.php
│       ├── documentazione.php
│       ├── home.php
│       ├── login.php
│       ├── register.php
│       └── prod_*.html
└── docker-compose.yml
```

---

## 📄 Licenza

Progetto distribuito a scopo puramente didattico/formativo. Utilizzalo in modo
responsabile e solo in ambienti isolati sotto il tuo controllo.
