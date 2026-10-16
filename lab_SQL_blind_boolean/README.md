# lab_SQL_blind_boolean

SQLi series — boolean-based blind SQL injection.

Goal: obtain `user.txt`, then `root.txt`.

Hints:
- The application exposes only a true/false state.
- Compare a true predicate with a false predicate.
- Recover the minimum database information needed to continue.
- After SSH access, enumerate the host rather than assuming the intended escalation.

Build: `docker compose up --build -d`
Web: `http://localhost`  SSH: `localhost:22`
Reset: `docker compose down -v`

Flags use `openssl rand -hex 16` and mode 600. Required tools: vim, nano, netcat-openbsd.
