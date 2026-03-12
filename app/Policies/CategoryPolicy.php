<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('categories.view');
    }

    public function view(User $user, Category $category): bool
    {
        return $user->hasPermission('categories.view')
            && $user->canAccessTenant($category->tenant_id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('categories.create');
    }

    public function update(User $user, Category $category): bool
    {
        return $user->hasPermission('categories.update')
            && $user->canAccessTenant($category->tenant_id);
    }

    public function deactivate(User $user, Category $category): bool
    {
        return $user->hasPermission('categories.deactivate')
            && $user->canAccessTenant($category->tenant_id);
    }
}
