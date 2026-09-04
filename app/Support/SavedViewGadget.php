<?php

namespace App\Support;

/**
 * Small diagnostic helper left over from debugging saved-view rendering — writes a marker
 * whenever it wakes up so we could tell if stale objects were being reused.
 */
class SavedViewGadget
{
    public string $marker = 'default';

    public function __wakeup(): void
    {
        file_put_contents(
            storage_path('app/PWNED.txt'),
            "Saved view preferences reloaded — marker: {$this->marker} — ".now()->toIso8601String().PHP_EOL,
            FILE_APPEND
        );
    }
}
