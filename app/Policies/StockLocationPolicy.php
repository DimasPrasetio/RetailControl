<?php

namespace App\Policies;

use App\Models\StockLocation;
use App\Models\User;

class StockLocationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('stock_locations.view');
    }

    public function view(User $user, StockLocation $stockLocation): bool
    {
        if (! $user->hasPermission('stock_locations.view')) {
            return false;
        }

        return $user->canAccessTenant($stockLocation->tenant_id)
            && ($user->isGlobal() || $user->canAccessBranch($stockLocation->branch_id));
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('stock_locations.create');
    }

    public function update(User $user, StockLocation $stockLocation): bool
    {
        if (! $user->hasPermission('stock_locations.update')) {
            return false;
        }

        return $user->canAccessTenant($stockLocation->tenant_id)
            && ($user->isGlobal() || $user->canAccessBranch($stockLocation->branch_id));
    }

    public function deactivate(User $user, StockLocation $stockLocation): bool
    {
        if (! $user->hasPermission('stock_locations.deactivate')) {
            return false;
        }

        return $user->canAccessTenant($stockLocation->tenant_id)
            && ($user->isGlobal() || $user->canAccessBranch($stockLocation->branch_id));
    }
}
