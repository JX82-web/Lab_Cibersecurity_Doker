___

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
    <summary> ⚠️ Expand to read⚠️ </summary>

```lua
    Flags use `openssl rand -hex 16` and mode 600. Required tools: vim, nano, netcat-openbsd.
```
</details>

<details><summary> ⚠️ #### Cosa impareremo in questo nuovo lab? ⚠️ </summary>

```json
1) Cosa impareremo in questo nuovo lab?
2) Manipolazione del token e furto di sessione privilegiata
3) Esfiltrazioni dei dati degli utenti di sistema
4) Cracking delle password
5) Reverseshell script
6) Privilege escalation
```
</details>

<details><summary>ℹ️ Info e contact ℹ️ </summary>

    [Foolow me on my channel Youtuve](https://www.youtube.com/@salvo8253?app=desktop)
    
    Info  & contact: <salvo8253@live.it>
</details>

