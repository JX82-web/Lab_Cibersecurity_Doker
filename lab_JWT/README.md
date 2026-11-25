###  lab_JWT - Catena completa
___

<h1>Enterprise Cloud Portal & Infrastructure Security Lab</h1>

<h2>Per completare il laboratorio: ottenere  `user.txt`, è `root.txt`</h2>

## Hints:

- [x] Inspect the authentication mechanism, token signing structure, and session handling.
- [x] Review application features and administrative endpoints thoroughly.
- [x] Enumerate internal services, user accounts, and system configurations post-access.

Build: `docker compose up --build -d`
Web: `http://<IP-LAB>`  SSH: `localhost:22`

- [x] Reset: `docker compose down -v`
- [x] remove and clean container: `docker system prune -a --volumes -f`

<details>
    <summary>⚠️ Expand to read⚠️</summary>
```lua
    Flags use `openssl rand -hex 16` and mode 600. Required tools: vim, nano, netcat-openbsd.
```
</details>

<details>
	<summary>⚠️ README ⚠️<summary>
```lua
+ Cosa impareremo in questo nuovo lab?
+ Manipolazione del token e furto di sessione privilegiata
+ Esfiltrazioni dei dati degli utenti di sistema
+ Cracking delle password
+ Reverseshell script
+ Privilege escalation

```
</details>
