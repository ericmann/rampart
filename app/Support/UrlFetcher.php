<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

/**
 * Fetches a user-supplied URL server-side. Backs the KB "preview link" feature and the
 * admin "test webhook" button.
 */
class UrlFetcher
{
    /**
     * A01:2025 — Broken Access Control (SSRF, folded into this category in 2025). The old
     * implementation fetched whatever URL it was given: a `file://` scheme read arbitrary
     * files off the server's disk, and an `http(s)://` URL could reach hosts that should
     * never be reachable from outside the network — loopback, RFC1918 private ranges,
     * link-local addresses, and the cloud "instance metadata" endpoint at 169.254.169.254
     * (the classic SSRF-to-credential-theft chain). The fix allowlists the scheme to
     * http/https only, resolves the host, and rejects anything that isn't a public IP —
     * enforced once here, at the single choke point both callers (KbController and the
     * admin webhook "test" action) go through, rather than re-validated at each call site.
     */
    public function fetch(string $url): string
    {
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if (! in_array($scheme, ['http', 'https'], true)) {
            return 'Blocked: only http and https URLs may be fetched.';
        }

        $host = parse_url($url, PHP_URL_HOST);

        if (! is_string($host) || $host === '' || ! $this->isPublicHost($host)) {
            return 'Blocked: this host is not a public address.';
        }

        $response = Http::timeout(5)->get($url);

        return $response->body();
    }

    private function isPublicHost(string $host): bool
    {
        $ip = filter_var($host, FILTER_VALIDATE_IP) ? $host : gethostbyname($host);

        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }
}
