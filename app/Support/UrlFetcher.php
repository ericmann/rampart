<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

/**
 * Fetches an arbitrary, user-supplied URL server-side with NO allowlist, no scheme
 * restriction, and no block on private/link-local addresses. Backs the KB "preview link"
 * feature and the admin "test webhook" button. See docs/VULN-MAP.md (A01c, SSRF).
 *
 * file:// is deliberately supported (not just http/https) so the SSRF demo can also show
 * local-file read of a planted marker file.
 */
class UrlFetcher
{
    public function fetch(string $url): string
    {
        if (str_starts_with($url, 'file://')) {
            $path = substr($url, strlen('file://'));
            $contents = @file_get_contents($path);

            return $contents === false ? "Could not read {$path}" : $contents;
        }

        $response = Http::timeout(5)->get($url);

        return $response->body();
    }
}
