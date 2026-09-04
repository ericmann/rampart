<?php

namespace App\Policies;

use App\Models\SavedView;
use App\Models\User;

class SavedViewPolicy
{
    public function view(User $user, SavedView $savedView): bool
    {
        return $user->id === $savedView->user_id;
    }

    public function delete(User $user, SavedView $savedView): bool
    {
        return $user->id === $savedView->user_id;
    }
}
