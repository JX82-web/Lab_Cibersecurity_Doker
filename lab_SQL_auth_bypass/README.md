# lab_SQL_auth_bypass

## Scenario

Northbridge Systems mantiene un vecchio portale interno usato durante le
migrazioni dei server.

Il portale espone soltanto una pagina di autenticazione. Le informazioni
fornite dal team sono volutamente minime.

## Obiettivo

Ottieni:

- `user.txt`
- `root.txt`

Il laboratorio è completato quando riesci a raggiungere `root` e leggere
entrambe le flag.

## Avvio

```bash
docker compose up --build
```

Web:

```text
http://172.0.0.1:80
```

SSH:

```text
127.0.0.1:22
```

## Regole

- Il laboratorio è intenzionalmente vulnerabile.
- Tutto deve essere eseguito nel laboratorio Docker locale.
- Non sono necessarie tecniche contro sistemi esterni.

## Learning objectives

- Authentication SQL Injection
- Web enumeration
- Session/role discovery
- Post-authentication enumeration
- Credential discovery
- SSH lateral movement
- SUID enumeration
- Privilege escalation tramite un binario locale vulnerabile

## Hints

<details>
<summary>🟢 Hint 1 — Authentication</summary>

La pagina di login dice di verificare username e password. Chiediti se
l'applicazione sta davvero usando un meccanismo di autenticazione
parametrizzato oppure se i dati finiscono direttamente in una query.

</details>

<details>
<summary>🟡 Hint 2 — SQL logic</summary>

Prova a ragionare sulla struttura logica della clausola `WHERE`.
L'obiettivo non è conoscere una password valida, ma capire se puoi
alterare la condizione che decide se esiste un utente.

</details>

<details>
<summary>🟠 Hint 3 — After authentication</summary>

Una volta ottenuto un account amministrativo, non fermarti alla dashboard.
Cerca funzionalità riservate agli amministratori e considera che un
vecchio archivio di migrazione potrebbe contenere informazioni operative.

</details>

<details>
<summary>🔴 Hint 4 — Lateral movement</summary>

Se trovi una credenziale relativa a un utente Linux, verifica dove quella
credenziale può essere utilizzata. Enumerare i servizi esposti localmente
e quelli raggiungibili dall'attacker è parte del percorso.

</details>

<details>
<summary>🔴 Hint 5 — Privilege escalation</summary>

Dopo il login come utente Linux, non cercare soltanto `sudo`. Controlla
anche i file con bit SUID e analizza con attenzione eventuali programmi
custom in `/usr/local/bin`.

</details>

## Reset

Per ricreare l'ambiente da zero:

```bash
docker compose down -v
docker compose up --build
```

Le flag vengono generate durante la build dell'immagine e non sono
inserite nel repository in chiaro.

## Cleanup

```bash
docker compose down -v
```

## Author notes

Questo README volutamente non contiene la soluzione completa. Gli hint sono
progressivi e devono essere sufficienti per guidare il giocatore senza
trasformare il laboratorio in una lista di comandi da copiare.
