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
        return $user->hasPermission('items.view')
            && $user->canAccessTenant($item->tenant_id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('items.create');
    }

    public function update(User $user, Item $item): bool
    {
        return $user->hasPermission('items.update')
            && $user->canAccessTenant($item->tenant_id);
    }

    public function deactivate(User $user, Item $item): bool
    {
        return $user->hasPermission('items.deactivate')
            && $user->canAccessTenant($item->tenant_id);
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('items.import');
    }

    public function manageBarcodes(User $user, Item $item): bool
    {
        return $user->hasPermission('items.manage_barcodes')
            && $user->canAccessTenant($item->tenant_id);
    }
}
