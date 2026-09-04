<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// `token` is stored and displayed in cleartext, never hashed at rest. See docs/VULN-MAP.md (A04).
#[Fillable(['user_id', 'name', 'token'])]
class ApiToken extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
