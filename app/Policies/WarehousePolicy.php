<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Warehouse;

class WarehousePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('warehouses.view');
    }

    public function view(User $user, Warehouse $warehouse): bool
    {
        if (! $user->hasPermission('warehouses.view')) {
            return false;
        }

        return $user->canAccessTenant($warehouse->tenant_id)
            && ($user->isGlobal() || $user->canAccessBranch($warehouse->branch_id));
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('warehouses.create');
    }

    public function update(User $user, Warehouse $warehouse): bool
    {
        if (! $user->hasPermission('warehouses.update')) {
            return false;
        }

        return $user->canAccessTenant($warehouse->tenant_id)
            && ($user->isGlobal() || $user->canAccessBranch($warehouse->branch_id));
    }

    public function deactivate(User $user, Warehouse $warehouse): bool
    {
        if (! $user->hasPermission('warehouses.deactivate')) {
            return false;
        }

        return $user->canAccessTenant($warehouse->tenant_id)
            && ($user->isGlobal() || $user->canAccessBranch($warehouse->branch_id));
    }

    public function delete(User $user, Warehouse $warehouse): bool
    {
        if (! $user->hasPermission('warehouses.delete')) {
            return false;
        }

        return $user->canAccessTenant($warehouse->tenant_id)
            && ($user->isGlobal() || $user->canAccessBranch($warehouse->branch_id));
    }
}
