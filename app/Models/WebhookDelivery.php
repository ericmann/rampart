<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['webhook_id', 'direction', 'payload', 'result'])]
class WebhookDelivery extends Model
{
    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }
}
