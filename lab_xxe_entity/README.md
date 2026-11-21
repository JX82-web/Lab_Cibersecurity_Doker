### XXE Entity Vulnerabile - Catena di Attacco Completa

- [x] Suggerimenti
- [x] Avvia il Container con `docker compose up --build -d` e aspetta che carichi il tutto.
- [x] Registrati se vuoi scoprire  la parte vulnerabile guarde nei product...
- [x] enumerazione della web e la chiave giusta da seguire
- [x] Attento/a alle trappole ne e pieno il percorso

#### Segui la guida se vuoi risolverlo

<h2><strong>scarica il laboratorio</strong></h2>

<details>
    <summary><li>🌄 expland to clicl here</li></summary>

    ```xml
    xml version="1.0" encoding="UTF-8"
    DOCTYPE foo [ ENTITY xxe SYSTEM "file:///etc/passwd" ]
    foo &xxe; /foo
    ```
</details>

#### Privilege escalation

<details>
    <summary><li>🌄Click to expand</li></summary>

- [x] Una volta dentro con ssh controlla il cronjob.
- [x] Ad ogni minuto c'e sempre una sorpresa in `/etc/cron.d`
- [x] Buoona caccia.

</details>
