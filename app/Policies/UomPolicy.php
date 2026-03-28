<?php

namespace App\Policies;

use App\Models\Uom;
use App\Models\User;

class UomPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('uoms.view');
    }

    public function view(User $user, Uom $uom): bool
    {
        return $user->hasPermission('uoms.view')
            && $user->canAccessTenant($uom->tenant_id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('uoms.create');
    }

    public function update(User $user, Uom $uom): bool
    {
        return $user->hasPermission('uoms.update')
            && $user->canAccessTenant($uom->tenant_id);
    }

    public function deactivate(User $user, Uom $uom): bool
    {
        return $user->hasPermission('uoms.deactivate')
            && $user->canAccessTenant($uom->tenant_id);
    }

    public function delete(User $user, Uom $uom): bool
    {
        return $user->hasPermission('uoms.delete')
            && $user->canAccessTenant($uom->tenant_id);
    }
}
