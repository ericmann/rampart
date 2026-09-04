<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['organization_id', 'requester_id', 'assigned_agent_id', 'subject', 'body', 'status', 'priority'])]
class Ticket extends Model
{
    use HasFactory;

    public const STATUSES = ['open', 'pending', 'resolved', 'closed'];

    public const PRIORITIES = ['low', 'normal', 'high', 'urgent'];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->oldest();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }
}
