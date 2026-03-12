<?php

namespace App\Policies;

use App\Models\Brand;
use App\Models\User;

class BrandPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('brands.view');
    }

    public function view(User $user, Brand $brand): bool
    {
        return $user->hasPermission('brands.view')
            && $user->canAccessTenant($brand->tenant_id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('brands.create');
    }

    public function update(User $user, Brand $brand): bool
    {
        return $user->hasPermission('brands.update')
            && $user->canAccessTenant($brand->tenant_id);
    }

    public function deactivate(User $user, Brand $brand): bool
    {
        return $user->hasPermission('brands.deactivate')
            && $user->canAccessTenant($brand->tenant_id);
    }
}
