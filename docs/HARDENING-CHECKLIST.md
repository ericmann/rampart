# Hardening Checklist

One secure-default takeaway per OWASP Top 10:2025 category — the "if you remember nothing
else" slide. Each line is the fix; details and file locations are in
`docs/VULN-MAP.md` (instructor) and the hunt itself is in `docs/LAB-GUIDE.md` (attendee).

| # | Category | Do this by default |
|---|---|---|
| A01 | Broken Access Control | Check ownership/role on **every** record access, every time — never assume a route being reachable means the request is authorized. For outbound fetches (webhooks, link previews, anything server-side that takes a URL), allowlist schemes and destinations, and block private/link-local address ranges. |
| A02 | Security Misconfiguration | `APP_DEBUG=false` everywhere except local dev, full stop. Never let an error page (custom or framework) render config, env vars, or secrets. No default credentials, ever — not even for a demo. |
| A03 | Software or Data Integrity Failures | Run `composer audit` / `npm audit` in CI and fail the build on it. Add Subresource Integrity to every third-party `<script>`/`<link>`. Pin CI actions to a commit SHA, not a tag — and install dependencies with a `--locked`/frozen-lockfile flag so drift fails loudly instead of silently. |
| A04 | Cryptographic Failures | Hash passwords with your framework's modern default (bcrypt/argon2id) and never touch it. Hash tokens/API keys at rest, compare with a constant-time function. Generate anything security-sensitive (reset tokens, session ids) from a CSPRNG — never derive it from predictable input like an email or a timestamp. |
| A05 | Injection | Parameter bindings, always — string-concatenated SQL is never acceptable, even for "just a search box." Escape all output by default (`{{ }}` not `{!! !!}`); treat raw/unescaped rendering as an opt-in you have to justify. `HttpOnly` on session cookies, no exceptions. |
| A06 | Insecure Design | Give identical responses regardless of whether a lookup (email, username) matched — enumeration is a design smell, not a bug. Rate-limit by more than one dimension (account *and* IP *and* a global ceiling). Never accept an authorization decision as request input; always recompute it server-side from the authenticated principal. |
| A07 | Authentication Failures | Regenerate the session id on every privilege change (login, logout, role change). Use your framework's built-in remember-me/recaller mechanism instead of rolling your own. Reset tokens: short TTL, single-use, invalidate on success. |
| A08 | Software or Data Integrity Failures | Never `unserialize()` data that crossed a trust boundary — use JSON. If you must accept serialized PHP, pass `allowed_classes => false`. Verify signatures (HMAC or similar) on every inbound webhook/callback before acting on the payload. |
| A09 | Security Logging & Alerting Failures | Log every security-relevant event (auth success/failure, authorization denial, privilege change, admin action) with enough context to investigate later — and alert on the ones that matter. Redact secrets (passwords, tokens, cookies) from logs by policy, checked in code review, not by hoping no one logs the wrong thing. |
| A10 | Mishandling of Exceptional Conditions | Default-deny: any exception inside a security decision should deny, not allow. Catch narrowly — catching `\Throwable` broadly around an authorization check is itself a smell. When a dependency a security control relies on is unavailable, fail closed (or make the degraded mode an explicit, alerting, logged decision) — never silently no-op. |
