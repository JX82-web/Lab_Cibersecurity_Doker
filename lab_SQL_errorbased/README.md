# lab_SQL_errorbased

SQLi series — error-based SQL injection.

Goal: obtain `user.txt`, then `root.txt`.

Hints:
- The reporting endpoint deliberately exposes database errors.
- Study the error behavior and the database context.
- Follow the operational secret to SSH.
- Enumerate SUID programs on the host.

Build: `docker compose up --build -d`
Web: `http://localhost`  SSH: `localhost:2225`
Reset: `docker compose down -v`

Flags use `openssl rand -hex 16` and mode 600. Required tools: vim, nano, netcat-openbsd.
