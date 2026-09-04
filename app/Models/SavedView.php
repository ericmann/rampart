<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * `preferences` is a raw serialize()'d blob, written and read verbatim (no json casting)
 * so the unserialize() call in SavedViewController stays reachable. See docs/VULN-MAP.md (A08).
 */
#[Fillable(['user_id', 'name', 'preferences'])]
class SavedView extends Model
{
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
