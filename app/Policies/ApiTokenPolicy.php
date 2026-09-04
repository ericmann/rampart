<?php

namespace App\Policies;

use App\Models\ApiToken;
use App\Models\User;

class ApiTokenPolicy
{
    public function delete(User $user, ApiToken $apiToken): bool
    {
        return $user->id === $apiToken->user_id;
    }
}
