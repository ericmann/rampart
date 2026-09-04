# Rampart

A support-desk / ticketing SaaS that works exactly like a real product — and is broken in
exactly ten curated ways, one per [OWASP Top 10:2025](https://owasp.org/Top10/) category.
Rampart is the companion app for **"Break It, Then Fix It" — An OWASP Top 10 Workshop for
PHP Developers**, a 3-hour hands-on tutorial at LonghornPHP.

It's built to survive a room full of people actively trying to break it: `docker compose up`
brings up a fully populated, browsable product with no second command and no internet
access required, and `make reset` puts it right back the way it started in seconds.

## Quick start

```
docker compose up
```

Then open <http://localhost:8080>. First boot migrates and seeds automatically — give it
a minute the first time.

| Role | Email | Password |
|---|---|---|
| Admin | `admin@rampart.test` | `admin` |
| Agent | `priya@rampart.test` | *(see `docs/wordlist.txt`)* |

**Broke something?**

```
make reset       # puts the database back to the exact seeded state, in seconds
make reset-hard  # nuclear option: wipes and rebuilds everything
```

## What's actually in here

- **Laravel 13 / PHP 8.4**, MySQL 8, Redis, Blade + Tailwind — a normal-looking Laravel app
- ~6 organizations, 30 users, 120 tickets with realistic threaded replies, a knowledge
  base, webhooks, and API tokens — committed as deterministic fixture JSON
  (`database/fixtures/`), not a database dump, so every clone gets identical data
- A tiny fourth container (`metadata-mock`) simulating a cloud "instance metadata"
  endpoint, reachable only from the app container, so the SSRF demo works fully offline
- **Ten intentional vulnerabilities**, exactly one per 2025 category, each real,
  demonstrable offline, and asserted by a hidden test suite

## Two test suites

```
composer test            # public suite — asserts the app WORKS. Must stay green.
composer test:exploits   # hidden suite — asserts the vulnerabilities are PRESENT.
```

The public suite (`tests/Unit`, `tests/Feature`) is what attendees extend as they patch
each bug — see `tests/Feature/RegressionExamples.md`. The hidden suite
(`tests/Exploits`, gated behind `ALLOW_EXPLOIT_TESTS=1`) is the instructor's own QA
harness: green means every plant is correctly in place; it goes red, category by category,
as fixes land on a patched branch.

## Documentation

- **[docs/LAB-GUIDE.md](docs/LAB-GUIDE.md)** — attendee-facing hunt instructions with a
  three-rung hint ladder per category. Start here if you're doing the workshop.
- **[docs/HARDENING-CHECKLIST.md](docs/HARDENING-CHECKLIST.md)** — one secure-default
  line per category, for the closing recap.
- **`docs/VULN-MAP.md`** — instructor-only answer key (file, exploit, fix, hidden test for
  every plant). Not included in `git archive` output; if you're an attendee reading this
  from a distributed copy, you may not have this file, and that's on purpose.

## Safety

Everything here is confined to the Docker Compose network: no real credentials, no calls
to real external hosts, no destructive gadgets (the one deserialization "exploit" writes
an inert marker file, nothing else). Run it only against your own copy, only in the
provided sandbox — never expose it to a network you don't own.

## License

MIT.
