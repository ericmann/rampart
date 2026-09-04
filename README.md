# Rampart

A support-desk / ticketing SaaS that works exactly like a real product — and is broken in
exactly ten curated ways, one per [OWASP Top 10:2025](https://owasp.org/Top10/) category.
Rampart is the companion app for **"Break It, Then Fix It" — An OWASP Top 10 Workshop for
PHP Developers**, a 3-hour hands-on tutorial at LonghornPHP, and the basis for a follow-up
blog series you can work through solo.

> ## ⚠️ This application is deliberately insecure. Do not deploy it.
>
> Rampart ships with real, working vulnerabilities on purpose. **Never** put it on a public
> server, a shared host, or any network you don't fully control. Run it **only** on your own
> machine, through the provided Docker Compose sandbox, which binds to `127.0.0.1` so nothing
> on your wifi/LAN can reach it. Attack **only** your own copy. Treat every credential,
> token, and "cloud secret" in here as fake (it is) and every exploit as something that stays
> on your laptop.

## Prerequisites

- **Docker Engine 24+** and the **Docker Compose v2** plugin (`docker compose`, not the old
  `docker-compose`). Docker Desktop on macOS/Windows includes both.
- **~4 GB free disk** for the images (app + MySQL 8 + Redis + a tiny mock service) and
  **~2 GB free RAM** for the running stack.
- **Port 8080 free on localhost** (the app) — nothing else needs to be published.
- A `make` is handy for the shortcuts below but optional; every target is a one-line
  `docker compose …` you can run by hand.

## Quick start

```
docker compose up          # or: make up
```

The **first** run builds the images (this one time needs internet). Subsequent runs, and
any machine you `make load` the prebuilt bundle onto (see *Workshop distribution*), need no
network at all. First boot migrates and seeds automatically — give it a minute.

Then open <http://localhost:8080>. Seeded sign-ins:

| Role | Email | Password |
|---|---|---|
| Admin | `admin@rampart.test` | `admin` |
| Agent | `priya@rampart.test` | *`password123`* (or crack any hash with `docs/wordlist.txt`) |

**Broke something?** (You will — SQL injection and privilege escalation aren't gentle.)

```
make reset       # re-seed the DB to the exact starting state, in seconds (no rebuild)
make reset-hard  # wipe the DB volume and re-provision from scratch (no image rebuild)
```

## What's actually in here

- **Laravel 13 / PHP 8.4**, MySQL 8, Redis, Blade + Tailwind — a normal-looking Laravel app.
- ~6 organizations, 30 users, 120 tickets with realistic threaded replies, a knowledge
  base, webhooks, and API tokens — committed as deterministic fixture JSON
  (`database/fixtures/`), not a database dump, so every clone gets identical data.
- A tiny fourth container (`metadata-mock`) simulating a cloud "instance metadata"
  endpoint, reachable only from the app container, so the SSRF demo works fully offline.
- **Ten planted vulnerabilities**, exactly one per 2025 category, each real, demonstrable
  offline, and asserted by a hidden test suite.

## Running the tests

Both suites run **inside the app container** (that's where PHP and the dependencies live):

```
make test        # public suite  — asserts the app WORKS (must stay green)
make exploits    # hidden suite  — asserts the vulnerabilities are PRESENT

# equivalently, without make:
docker compose exec app composer test
docker compose exec app composer test:exploits
```

The public suite (`tests/Unit`, `tests/Feature`) is what you extend with a regression test
each time you fix a bug — see `tests/Feature/RegressionExamples.md`. The hidden suite
(`tests/Exploits`, gated behind `ALLOW_EXPLOIT_TESTS=1`) proves every plant is present:
green here means "still correctly broken," and it flips to red, category by category, as
fixes land.

## Working through it solo

Start with **[docs/LAB-GUIDE.md](docs/LAB-GUIDE.md)** — ten self-contained exercises, each
with a scenario, where to start looking, a three-rung hint ladder, and a concrete
"you succeeded when…" check. Nobody to ask? That's fine: **rung 3 of every ladder spells
out the exploit**, and if you want to see the mechanical proof, the matching test in
`tests/Exploits/` demonstrates each flaw in code. A patched reference lives on the
`solutions` branch. **[docs/HARDENING-CHECKLIST.md](docs/HARDENING-CHECKLIST.md)** is the
one-line-per-category takeaway.

## Workshop distribution

A room full of laptops all running `docker compose build` on conference wifi at once will
not go well. Build once, bundle the images, hand out the bundle:

```
make dist   # builds everything, then writes rampart-images.tar.gz (a few hundred MB)
```

Copy `rampart-images.tar.gz` to a USB stick (alongside a clone/zip of this repo). On each
machine:

```
make load          # docker load < rampart-images.tar.gz — no network needed
docker compose up
```

`docker-compose.yml` sets `pull_policy: never` on every service, so once the images are
loaded `docker compose up` never contacts a registry — it uses the local image or fails
fast, it never hangs on a pull.

## Safety recap

No real credentials, no calls to real external hosts, no destructive payloads: the one
deserialization "exploit" appends a line to a marker file and nothing else, the mock cloud
metadata returns obviously-fake keys, and only port 8080 is published — to `127.0.0.1`.
Still: **your own machine, your own copy, never exposed.**

## License & attribution

MIT. Built by Eric Mann for the LonghornPHP workshop *"Break It, Then Fix It."* If you use
it to train your own team, a nod back is appreciated but not required.
