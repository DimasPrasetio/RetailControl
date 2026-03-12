<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;

class BranchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('branches.view');
    }

    public function view(User $user, Branch $branch): bool
    {
        if (! $user->hasPermission('branches.view')) {
            return false;
        }

        return $user->canAccessTenant($branch->tenant_id)
            && ($user->isGlobal() || $user->canAccessBranch($branch->id));
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('branches.create');
    }

    public function update(User $user, Branch $branch): bool
    {
        if (! $user->hasPermission('branches.update')) {
            return false;
        }

        return $user->canAccessTenant($branch->tenant_id)
            && ($user->isGlobal() || $user->canAccessBranch($branch->id));
    }

    public function deactivate(User $user, Branch $branch): bool
    {
        if (! $user->hasPermission('branches.deactivate')) {
            return false;
        }

        return $user->canAccessTenant($branch->tenant_id)
            && ($user->isGlobal() || $user->canAccessBranch($branch->id));
    }
}
