<?php

namespace App\Observers;

use App\Models\Category;
use Illuminate\Support\Collection;

class CategoryObserver
{
    /**
     * Cascade deactivation to all descendants when a category is deactivated.
     */
    public function updating(Category $category): void
    {
        if ($category->isDirty('is_active') && ! $category->is_active) {
            $descendantIds = $this->descendantIds($category);
            if ($descendantIds->isNotEmpty()) {
                Category::whereIn('id', $descendantIds)->update(['is_active' => false]);
            }
        }
    }

    /**
     * Prevent deletion when children exist.
     */
    public function deleting(Category $category): void
    {
        if ($category->children()->exists()) {
            throw new \RuntimeException('Kategori yang memiliki sub-kategori tidak dapat dihapus.');
        }
    }

    private function descendantIds(Category $category): Collection
    {
        $all = Category::query()
            ->forTenant($category->tenant_id)
            ->get()
            ->keyBy('id');

        return $this->collectIds($all, $category->id);
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
