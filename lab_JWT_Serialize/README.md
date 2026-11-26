# Lab Cybernetic — Token RCE → tajani → Root

> Local CTF/lab only. Run inside the isolated Docker/VMware environment used for the lab.

## Attack path

1. Discover the `cyber_site` web application and its API v1.
2. Obtain a normal application token.
3. Identify the deliberately unsafe token deserialization.
4. Forge a serialized PHP object and obtain command execution as `www-data`.
5. Enumerate the container and recover the lab-only SSH material for `tajani`.
6. Move laterally to `tajani` through the local SSH service.
7. Enumerate `sudo` and the maintenance backup chain.
8. Abuse the intentionally writable maintenance hook to execute as root.
9. Read `/root/flags/root.txt`.

The intended initial vulnerability is the **token**. Authentication uses prepared statements and is not intended to be an SQL injection route.

## Structure

- `cyber_site` application under `public/`, `src/`, `Models/`, `plugins/`, `config/`
- API v1
- PHP/MySQL
- SSH
- maintenance tooling
- user/root flags
- `vim`, `nano`, and BSD `netcat`
- Docker Compose

## Start

```bash
docker compose build
docker compose up -d
docker compose ps
```

Web: `http://127.0.0.1:8080`

SSH: `127.0.0.1:2222`

Lab credentials are intentionally fake and local to this container.

## Intended foothold

The application issues a base64-encoded serialized PHP object as a cookie. The decoder deliberately calls `unserialize()` without an integrity check.

The gadget chain is implemented by:

- `Models/LegacySession.php`
- `plugins/LegacyMaintenance.php`

The normal token is harmless. A modified serialized object can invoke the command sink when the object lifecycle completes.

## Intended privilege escalation

`tajani` is allowed to run:

```text
sudo /opt/maintenance/backup.sh
```

The backup script loads a maintenance hook that is intentionally writable by the `operator` group, and `tajani` belongs to that group. The hook is executed by root.

## Reset

```bash
docker compose down -v
docker compose build --no-cache
docker compose up -d
```

Do not expose the published ports to an untrusted network.
