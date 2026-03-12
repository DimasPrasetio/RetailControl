<?php

namespace App\Traits;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait TenantScoped
{
    public static function bootTenantScoped(): void
    {
        static::creating(function ($model) {
            if (! empty($model->tenant_id)) {
                return;
            }

            $user = Auth::user();
            if ($user && ! empty($user->tenant_id) && ! $user->isPlatformAdmin()) {
                $model->tenant_id = $user->tenant_id;

                return;
            }

            if (! empty($model->branch_id)) {
                $tenantId = Branch::query()
                    ->whereKey($model->branch_id)
                    ->value('tenant_id');

                if ($tenantId) {
                    $model->tenant_id = $tenantId;

                    return;
                }
            }

            if (method_exists($model, 'allowsMissingTenantOnCreate') && $model->allowsMissingTenantOnCreate()) {
                return;
            }

            throw new \LogicException(sprintf(
                '%s requires an explicit tenant_id before creation.',
                class_basename($model)
            ));
        });
    }

    public function scopeForTenant(Builder $query, ?int $tenantId): Builder
    {
        if ($tenantId === null) {
            return $query;
        }

        return $query->where($this->getTable() . '.tenant_id', $tenantId);
    }
}
