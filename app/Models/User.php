<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// `role` is intentionally mass-assignable here — see docs/VULN-MAP.md (A01b). ProfileController
// updates the model straight from request input, so posting role=admin self-promotes.
#[Fillable(['name', 'email', 'password', 'role', 'organization_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_CUSTOMER = 'customer';

    public const ROLE_AGENT = 'agent';

    public const ROLE_ADMIN = 'admin';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            // The `hashed` cast just calls Hash::make()/Hash::check() — the app-wide default
            // hasher is swapped to md5 in AppServiceProvider, so this still "works" while
            // being cryptographically broken. See docs/VULN-MAP.md (A04).
            'password' => 'hashed',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function requestedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'requester_id');
    }

    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_agent_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'author_id');
    }

    public function savedViews(): HasMany
    {
        return $this->hasMany(SavedView::class);
    }

    public function apiTokens(): HasMany
    {
        return $this->hasMany(ApiToken::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isAgent(): bool
    {
        return $this->role === self::ROLE_AGENT;
    }

    public function isCustomer(): bool
    {
        return $this->role === self::ROLE_CUSTOMER;
    }

    public function isStaff(): bool
    {
        return $this->isAgent() || $this->isAdmin();
    }
}
