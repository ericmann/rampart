<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * `preferences` is stored as a serialized PHP value rather than JSON so arbitrary filter
 * shapes round-trip without needing a schema change every time a new filter is added.
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
