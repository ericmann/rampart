<?php

namespace App\Support;

/**
 * Deliberately reachable PHP object-injection gadget for the A08 demo.
 *
 * SavedViewController@show calls unserialize() on the `preferences` column with no
 * `allowed_classes` restriction, so a crafted payload naming this class gets instantiated
 * and __wakeup() runs automatically. The side effect here is intentionally inert — it only
 * writes a marker file — never shell/exec/delete. See docs/VULN-MAP.md (A08).
 */
class SavedViewGadget
{
    public string $marker = 'default';

    public function __wakeup(): void
    {
        file_put_contents(
            storage_path('app/PWNED.txt'),
            "Object injection via SavedView::preferences unserialize() — marker: {$this->marker} — ".now()->toIso8601String().PHP_EOL,
            FILE_APPEND
        );
    }
}
