<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait untuk model yang memiliki kolom branch_id.
 * Memudahkan filter per cabang di controller.
 *
 * Penggunaan:
 *   $branchId = auth()->user()->getAccessibleBranchId();
 *   Transaction::forBranch($branchId)->get();
 */
trait BranchScoped
{
    /**
     * Scope query ke cabang tertentu.
     * Jika $branchId = null (super_admin/owner), tidak ada filter.
     */
    public function scopeForBranch(Builder $query, ?int $branchId): Builder
    {
        if ($branchId === null) {
            return $query;
        }

        return $query->where($this->getTable() . '.branch_id', $branchId);
    }
}
