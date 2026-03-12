<?php

namespace App\Policies;

use App\Models\AttributeDefinition;
use App\Models\User;

class AttributeDefinitionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('attribute_definitions.view');
    }

    public function view(User $user, AttributeDefinition $attributeDefinition): bool
    {
        return $user->hasPermission('attribute_definitions.view')
            && $user->canAccessTenant($attributeDefinition->tenant_id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('attribute_definitions.create');
    }

    public function update(User $user, AttributeDefinition $attributeDefinition): bool
    {
        return $user->hasPermission('attribute_definitions.update')
            && $user->canAccessTenant($attributeDefinition->tenant_id);
    }
}
