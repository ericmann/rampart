<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

/**
 * Fetches a user-supplied URL server-side. Backs the KB "preview link" feature and the
 * admin "test webhook" button.
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
