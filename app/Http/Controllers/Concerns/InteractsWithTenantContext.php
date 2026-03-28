<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

trait InteractsWithTenantContext
{
    protected function resolveTenantId(Request $request, string $field = 'tenant_id'): int
    {
        if (! $request->user()->isPlatformAdmin()) {
            if (! $request->user()->tenant_id) {
                throw ValidationException::withMessages([
                    $field => 'User ini belum terikat ke perusahaan aktif.',
                ]);
            }

            return (int) $request->user()->tenant_id;
        }

        $tenantId = $request->integer($field);
        if (! $tenantId) {
            throw ValidationException::withMessages([
                $field => 'Perusahaan wajib dipilih.',
            ]);
        }

        $tenant = Tenant::query()->find($tenantId);
        if (! $tenant) {
            throw ValidationException::withMessages([
                $field => 'Perusahaan yang dipilih tidak ditemukan.',
            ]);
        }

        if (! $tenant->is_active) {
            throw ValidationException::withMessages([
                $field => 'Perusahaan yang dipilih tidak aktif.',
            ]);
        }

        return (int) $tenant->id;
    }

    protected function selectedTenantId(Request $request, ?int $fallbackTenantId = null): ?int
    {
        if (! $request->user()->isPlatformAdmin()) {
            return $request->user()->tenant_id ? (int) $request->user()->tenant_id : null;
        }

        $oldTenantId = old('tenant_id');
        if ($oldTenantId) {
            return (int) $oldTenantId;
        }

        $queryTenantId = $request->integer('tenant_id');
        if ($queryTenantId) {
            return $queryTenantId;
        }

        return $fallbackTenantId;
    }

    protected function availableTenants(User $user, ?int $selectedTenantId = null): Collection
    {
        $query = Tenant::query()->orderBy('name');

        if (! $user->isPlatformAdmin()) {
            return $query->whereKey($user->tenant_id)->get();
        }

        $query->where(function ($inner) use ($selectedTenantId) {
            $inner->where('is_active', true);

            if ($selectedTenantId) {
                $inner->orWhere($inner->getModel()->getQualifiedKeyName(), $selectedTenantId);
            }
        });

        return $query->get();
    }
}
