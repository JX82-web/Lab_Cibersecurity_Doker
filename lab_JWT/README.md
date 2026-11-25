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
- [x] Cosa impareremo in questo nuovo lab?
-[x]  Manipolazione del token e furto di sessione privilegiata
- [x]  Esfiltrazioni dei dati degli utenti di sistema
- [x]  Cracking delle password
- [x]  Reverseshell script
- [X]  Privilege escalation

</details>
