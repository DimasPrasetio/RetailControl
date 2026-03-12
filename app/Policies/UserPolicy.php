<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $authUser): bool
    {
        return $authUser->hasPermission('users.view');
    }

    public function view(User $authUser, User $target): bool
    {
        if (! $authUser->hasPermission('users.view')) {
            return false;
        }

        return $this->canAccessTarget($authUser, $target);
    }

    public function create(User $authUser): bool
    {
        return $authUser->hasPermission('users.create');
    }

    public function update(User $authUser, User $target): bool
    {
        if (! $authUser->hasPermission('users.update')) {
            return false;
        }

        if ($target->isSuperAdmin() && $authUser->id !== $target->id) {
            return false;
        }

        return $this->canAccessTarget($authUser, $target);
    }

    public function deactivate(User $authUser, User $target): bool
    {
        if (! $authUser->hasPermission('users.deactivate')) {
            return false;
        }

        if ($authUser->id === $target->id) {
            return false;
        }

        if ($target->isSuperAdmin()) {
            return false;
        }

        return $this->canAccessTarget($authUser, $target);
    }

    public function delete(User $authUser, User $target): bool
    {
        if (! $authUser->hasPermission('users.delete')) {
            return false;
        }

        if ($authUser->id === $target->id) {
            return false;
        }

        if ($target->isSuperAdmin()) {
            return false;
        }

        return $this->canAccessTarget($authUser, $target);
    }

    private function canAccessTarget(User $authUser, User $target): bool
    {
        if (! $authUser->canAccessTenant($target->tenant_id)) {
            return false;
        }

        if ($authUser->isGlobal()) {
            return true;
        }

        return $target->branch_id !== null && $authUser->canAccessBranch($target->branch_id);
    }
}
