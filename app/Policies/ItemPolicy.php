<?php

namespace App\Policies;

use App\Models\Item;
use App\Models\User;

class ItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('items.view');
    }

    public function view(User $user, Item $item): bool
    {
        return $user->hasPermission('items.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('items.create');
    }

    public function update(User $user, Item $item): bool
    {
        return $user->hasPermission('items.update');
    }

    public function deactivate(User $user, Item $item): bool
    {
        return $user->hasPermission('items.deactivate');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('items.import');
    }
}
