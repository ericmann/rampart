# Hardening Checklist

One secure-default takeaway per OWASP Top 10:2025 category — the "if you remember nothing
else" slide. Each line is the fix; the hunt itself is in `docs/LAB-GUIDE.md`.

## What moved since 2021

Most people arrived on the 2021 list. Here's how the ten categories map:

| 2025 | Category | Since 2021 |
|---|---|---|
| A01 | Broken Access Control | Still #1 — now **absorbs SSRF**, which was its own category (A10:2021). |
| A02 | Security Misconfiguration | Climbed to #2 (was A05:2021) as config/CD drives more behavior. |
| A03 | Software Supply Chain Failures | Broadened and renamed from "Vulnerable and Outdated Components" (A06:2021) — the whole build/distribution chain, not just old libraries. |
| A04 | Cryptographic Failures | Same category, was #2 (A02:2021). |
| A05 | Injection | Was A03:2021; XSS lives here (merged into Injection in 2021). |
| A06 | Insecure Design | Was A04:2021. |
| A07 | Authentication Failures | Renamed from "Identification and Authentication Failures" (A07:2021). |
| A08 | Software & Data Integrity Failures | Same (A08:2021) — still the home of insecure deserialization. |
| A09 | Security Logging & Alerting Failures | Renamed from "…Logging and Monitoring Failures" (A09:2021) to stress **alerting**, not just recording. |
| A10 | Mishandling of Exceptional Conditions | **Brand new in 2025.** No 2021 equivalent. |

## The checklist

| # | Category | Do this by default |
|---|---|---|
| A01 | Broken Access Control | Check ownership/role on **every** record access, every time — a route being reachable is not authorization. For any server-side fetch of a user-supplied URL (webhooks, link previews), allowlist the scheme and destination and block private/link-local ranges. |
| A02 | Security Misconfiguration | `APP_DEBUG=false` everywhere but local dev, full stop. Never let an error page — custom or framework — render config, env, or secrets. No default credentials, ever, not even for a demo. |
| A03 | Software Supply Chain Failures | Run `composer audit` / `npm audit` in CI and fail the build on findings. Add Subresource Integrity to every third-party `<script>`/`<link>`. Pin CI actions to a commit SHA, not a tag, and install with a `--locked`/frozen lockfile so drift fails loudly. |
| A04 | Cryptographic Failures | Hash passwords with the framework's modern default (bcrypt/argon2id) and never hand-roll it. Hash tokens/API keys at rest; compare in constant time. Generate anything security-sensitive from a CSPRNG — never derive it from an email or a timestamp. |
| A05 | Injection | Parameter bindings, always — string-built SQL is never acceptable, even for "just a search box." Escape output by default; treat raw/unescaped rendering as an opt-in you justify. `HttpOnly` on session cookies, no exceptions. Add a Content-Security-Policy. |
| A06 | Insecure Design | Return identical responses whether or not a lookup (email, username) matched — enumeration is a design smell. Rate-limit on more than one dimension (account **and** IP **and** a global ceiling). Never accept an authorization decision as request input; recompute it server-side. |
| A07 | Authentication Failures | Regenerate the session id on every privilege change (login, logout, role change). Use the framework's signed remember-me, not a hand-rolled cookie. Reset tokens: high-entropy, short TTL, single-use, invalidated after use. |
| A08 | Software & Data Integrity Failures | Never `unserialize()` data that crossed a trust boundary — use JSON. If you must, pass `allowed_classes => false`. Verify a signature (HMAC or similar) on every inbound webhook/callback before acting on it. |
| A09 | Security Logging & Alerting Failures | Log every security-relevant event (auth success/failure, authorization denial, privilege change, admin action) with enough context to investigate — and **alert** on the ones that matter. Redact secrets from logs by policy, enforced in review, not by hoping. |
| A10 | Mishandling of Exceptional Conditions | Default-deny: any exception inside a security decision must deny, not allow. Catch narrowly — a broad `catch (\Throwable)` around an authorization check is itself the smell. When a control's dependency is down, fail closed or make the degraded mode an explicit, alerting decision — never a silent no-op. |

## Cross-framework cheat sheet

The principle is universal; the tools differ. If you arrived from one ecosystem, here's the
equivalent in the others.

| # | Laravel | Symfony | WordPress |
|---|---|---|---|
| A01 | Policies/Gates + `Gate::authorize`; `$fillable` (never `$guarded=[]`); form-request DTOs. SSRF: validate the host and block private ranges before `Http::get`. | Voters + `#[IsGranted]`/`denyAccessUnlessGranted`; hydrate DTOs from forms, not entities. | `current_user_can()` on every privileged action. SSRF: `wp_safe_remote_get()` (blocks internal hosts by design), never `wp_remote_get()` on user input. |
| A02 | `APP_DEBUG=false`, `config:cache`, don't ship Telescope/Ignition to prod; security-header middleware. | `APP_ENV=prod`; disable the profiler & web-debug-toolbar in prod. | `WP_DEBUG=false`, `DISALLOW_FILE_EDIT`, secrets out of the webroot, remove default admin. |
| A03 | `composer audit` in CI, `--locked` installs, Dependabot/Renovate; SRI on CDN assets. | Same Composer tooling; `composer audit` gate. | Keep core/plugins/themes updated, drop abandoned plugins, `wp plugin verify-checksums`. |
| A04 | `Hash` (bcrypt/argon2id); `Str::random()`/`random_bytes()`; encrypted casts / `Crypt`; hash API tokens (Sanctum stores hashes). | `PasswordHasher` component; the `secrets` vault. | `wp_hash_password()`/`wp_check_password()` (bcrypt as of 6.8); `wp_generate_password()`/`wp_rand()`. |
| A05 | Query bindings / Eloquent; Blade `{{ }}` auto-escapes (reserve `{!! !!}`); set a CSP. | Doctrine parameterized queries; Twig auto-escaping. | `$wpdb->prepare()`; `esc_html()`/`esc_attr()`/`esc_url()`/`wp_kses_post()` on output. |
| A06 | `RateLimiter`/`throttle`; the Password broker's reset responses are uniform — don't undo that. | `rate_limiter` component; login throttling. | Throttle at the edge or via a plugin; note core login errors *do* leak validity — harden with a filter. |
| A07 | `session()->regenerate()` on login; the built-in signed remember-me recaller; expiring single-use reset tokens; MFA via Fortify. | Security component; `migrate` the session on login; signed remember-me. | `wp_set_auth_cookie()` (HMAC-signed) — don't roll your own; MFA via plugin. |
| A08 | JSON not `serialize()`; `unserialize($x, ['allowed_classes'=>false])` if unavoidable; signed URLs; HMAC-verify webhooks. | Avoid PHP serialization of untrusted input; the Serializer with explicit formats. | `maybe_unserialize()` only on trusted DB data, never request input; verify webhook signatures. |
| A09 | Structured log channels + a redaction processor; an audit-log package; alert on critical events. | Monolog channels + processors; security event listeners. | An audit-log plugin or `do_action` hooks; scrub secrets from `debug.log`. |
| A10 | *Framework-agnostic.* Default-deny on any exception in a security decision, catch narrowly, and fail closed when a dependency (cache/DB/queue) is unavailable. The same discipline applies identically in Laravel, Symfony, and WordPress. |  |  |
