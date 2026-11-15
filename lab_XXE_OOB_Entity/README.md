# E-Commerce XXE OOB + Lateral Movement + CVE-2026 Lab

> **Local training lab only.** Run this stack on an isolated Docker host. Do not expose it to the public Internet.

## Chain

1. Discover the PHP e-commerce application.
2. Register/login and inspect the dashboard.
3. Find the XML product-import functionality.
4. Identify the **XXE** weakness and practice **blind/out-of-band XXE**.
5. Use the web foothold as `www-data`.
6. Enumerate the container and pivot to the SSH account **jack**.
7. From `jack`, investigate the intentionally vulnerable `uutils coreutils` `chroot` binary.
8. The final escalation is based on **CVE-2026-35368**.

CVE-2026-35368 affects uutils/coreutils versions <= 0.8.0. The issue is in `chroot --userspec`: NSS resolution can occur after entering a writable chroot and before privileges are dropped, allowing an attacker-controlled NSS module to execute with elevated privileges. See the official CVE record and advisory linked below.

- CVE record: https://www.cve.org/CVERecord?id=CVE-2026-35368
- Advisory: https://github.com/advisories/GHSA-mh5c-xrmh-m794

## Credentials

Do not rely on a hard-coded solution path. The lab intentionally requires enumeration.

- Web registration: create your own account.
- SSH user: `jack`
- SSH password: `J4ck-Lab-2026!`

## Ports

- HTTP: `80`
- SSH: `2222`
- OOB listener: `8000`

## Flags

- `/home/jack/user.txt`
- `/root/root.txt`

Flags are generated at image build time and have mode `0600`.

## XXE hints

The import endpoint accepts XML. Look for a parser configuration that permits external entities.

The OOB service is reachable from the web container as:

    http://oob:8000/

A useful first-stage question is: **Can the XML parser be made to request a remote DTD?**

The included OOB service logs incoming requests, so you can observe blind XXE callbacks without exposing another network service.

## Lateral movement hints

Once you have a shell as `www-data`:

- inspect `/etc/passwd`
- inspect SSH configuration
- inspect application configuration files
- search for credentials and reusable secrets
- enumerate listening services and SUID files

The intended pivot is from `www-data` to `jack` over SSH.

## Privilege escalation hints

The intended final target is a setuid-root copy of the vulnerable uutils `chroot`.

Questions to answer:

- What version is installed?
- Is the binary setuid?
- What does `--userspec` do?
- Can the NEWROOT be controlled by your current user?
- What does glibc do when resolving a user name?
- Can an NSS module be loaded from inside the new root?

Do not use the solution files as a substitute for enumeration; they are deliberately limited to lab setup notes.

## Reset

    docker compose down -v
    docker compose build --no-cache
    docker compose up -d

## Safety

This lab intentionally contains vulnerable software, weak credentials, SSH access, a blind XXE sink, and a privilege-boundary weakness. Keep it isolated and never bind it to an untrusted network.
