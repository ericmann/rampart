<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        return $user->id === $ticket->requester_id;
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return $user->isStaff();
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin();
    }

    /**
     * Reassign a ticket to a different agent. `$agentId` comes straight from request input;
     * findOrFail() throws on a non-numeric or non-existent id, which — via
     * App\Support\Authorization::allows() — is swallowed into an ALLOW. See docs/VULN-MAP.md (A10).
     */
    public function assign(User $user, Ticket $ticket, mixed $agentId): bool
    {
        $agent = User::where('role', User::ROLE_AGENT)->findOrFail($agentId);

        return $user->isStaff() && $agent->id === $agent->id;
    }
}
