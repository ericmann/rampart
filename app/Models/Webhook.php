<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'target_url', 'event', 'inbound_token', 'secret', 'is_active'])]
class Webhook extends Model
{
    use HasFactory;

    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class);
    }
}
