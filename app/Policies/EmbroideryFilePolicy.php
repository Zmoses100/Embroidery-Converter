<?php

namespace App\Policies;

use App\Models\EmbroideryFile;
use App\Models\User;

class EmbroideryFilePolicy
{
    public function view(User $user, EmbroideryFile $file): bool
    {
        return $user->id === $file->user_id || $user->isAdmin();
    }

    public function delete(User $user, EmbroideryFile $file): bool
    {
        return $user->id === $file->user_id || $user->isAdmin();
    }
}
