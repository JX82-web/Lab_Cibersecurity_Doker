# Lab: SQLi Union to Local JWT Bypass 🚀 (HTB Style)

Benvenuto in questo scenario CTF avanzato a catena. L'obiettivo finale è compromettere l'applicazione web principale, effettuare un movimento laterale sul sistema operativo del container e completare un'escalation di privilegi fino a ottenere l'utente `root`.

---

## 📌 Indizi e Fasi del Laboratorio (Hints)

### 🔹 Fase 1: Foothold (SQL Injection)
* **Indizio 1:** Analizza l'URL quando filtri le categorie del catalogo. Il database risponde con messaggi d'errore dettagliati se provi a inserire caratteri di escape malformati.
* **Indizio 2:** Calcola il numero esatto di colonne necessarie per l'operatore `UNION`. Ricorda che la query originaria seleziona 4 campi dalla tabella `store_items`.
* **Indizio 3:** Usa l'attacco UNION per enumerare lo schema del database. C'è una tabella insolita chiamata `internal_secrets`. Cosa contiene al suo interno?
* **Indizio 4:** Il server Apache ti permette di eseguire codice? Controlla se puoi usare funzioni di scrittura file di MySQL (`INTO OUTFILE`) per stabilire una web shell (RCE) sfruttando i permessi di scrittura nella cartella `/var/www/html/uploads` o nelle cartelle adiacenti, così da ottenere l'accesso come `www-data`.

### 🔹 Fase 2: Movimento Laterale (Da www-data a user)
* **Indizio 5:** Una volta ottenuta una shell come utente `www-data`, ispeziona la directory indicata precedentemente nel database dei segreti (`/var/www/html/backup_tasks`).
* **Indizio 6:** C'è un'attività pianificata (cronjob) che gira in background ed esegue periodicamente un comando d'archivio (`tar`) utilizzando un carattere jolly (`*`). Fai una ricerca sul web digitando *"Tar Wildcard Injection Privilege Escalation"*.
* **Indizio 7:** Sfrutta i parametri di esecuzione avanzati di `tar` (come `--checkpoint` e `--checkpoint-action`) creando file con nomi specifici nella directory dei compiti per forzare l'utente proprietario del cronjob (`user`) ad eseguire comandi arbitrari o una reverse shell per te. Una volta dentro, stabilizza la sessione o accedi via SSH sulla porta `222`.

### 🔹 Fase 3: Privilege Escalation Finale (JWT Manipulation & Cracking)
* **Indizio 8:** Ora che hai ottenuto la shell come utente `user`, controlla i servizi attivi localmente sulla macchina che non sono esposti all'esterno. C'è un server web che risponde su una porta locale specifica (es. `8080`)?
* **Indizio 9:** Interroga il servizio locale usando strumenti come `curl`. Naviga verso l'endpoint di autenticazione iniziale `/api/auth/guest` per ottenere un JSON Web Token (JWT) di test ed esamina la sua struttura su strumenti come *jwt.io*.
* **Indizio 10:** L'applicazione soffre di un controllo insufficiente sulla firma del token. Ispeziona i meccanismi di validazione o testa le vulnerabilità note dello standard JWT. Riusciresti a modificare il payload impostando il campo del ruolo amministrativo (`"role": "admin"`) bypassando o alterando la firma (es. sfruttando l'algoritmo `"none"` o brute-force)?
* **Indizio 11:** Invia il token forgiato all'endpoint amministrativo protetto `/api/admin/credentials`. Se l'attacco ha successo, l'applicazione ti restituirà un cookie o un hash crittografico associato all'utente root di sistema.
* **Indizio 12:** Prendi l'hash estratto ed esegui un attacco a dizionario locale usando strumenti di password cracking come `john` o `hashcat` insieme a una wordlist comune. Una volta recuperata la password in chiaro, usa il comando `su root` per conquistare definitivamente il container e leggere il flag finale.
