<?php

namespace App\Observers;

use App\Models\StockLocation;
use Illuminate\Support\Collection;

class StockLocationObserver
{
    /**
     * Cascade deactivation to all descendant locations.
     */
    public function updating(StockLocation $location): void
    {
        if ($location->isDirty('is_active') && ! $location->is_active) {
            $descendantIds = $this->descendantIds($location);
            if ($descendantIds->isNotEmpty()) {
                StockLocation::whereIn('id', $descendantIds)->update(['is_active' => false]);
            }
        }
    }

    /**
     * Prevent deletion when children exist.
     */
    public function deleting(StockLocation $location): void
    {
        if ($location->children()->exists()) {
            throw new \RuntimeException('Lokasi yang memiliki sub-lokasi tidak dapat dihapus.');
        }
    }

    private function descendantIds(StockLocation $location): Collection
    {
        $all = StockLocation::query()
            ->forTenant($location->tenant_id)
            ->where('warehouse_id', $location->warehouse_id)
            ->get()
            ->keyBy('id');

        return $this->collectIds($all, $location->id);
    }

    private function collectIds(Collection $all, int $parentId): Collection
    {
        $ids = collect();
        foreach ($all->where('parent_id', $parentId) as $child) {
            $ids->push($child->id);
            $ids = $ids->merge($this->collectIds($all, $child->id));
        }
        return $ids;
    }
}
