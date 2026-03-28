<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\InteractsWithTenantContext;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    use InteractsWithTenantContext;

    public function index(): View
    {
        Gate::authorize('viewAny', Category::class);

        $allCategories = Category::query()
            ->forTenant(request()->user()->getAccessibleTenantId())
            ->orderBy('parent_id')
            ->orderBy('name')
            ->get()
            ->keyBy('id');

        $roots = $allCategories->whereNull('parent_id')->values();

        return view('admin.categories.index', compact('roots', 'allCategories'));
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Category::class);

        $selectedTenantId = $this->selectedTenantId($request);
        $parents = $selectedTenantId
            ? $this->buildParentOptions($selectedTenantId)
            : collect();

        return view('admin.categories.create', [
            'parents'          => $parents,
            'tenants'          => $this->availableTenants($request->user(), $selectedTenantId),
            'selectedTenantId' => $selectedTenantId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Category::class);

        $tenantId = $this->resolveTenantId($request);
        $data = $request->validate([
            'parent_id' => ['nullable', Rule::exists('categories', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId))],
            'name'      => ['required', 'string', 'max:100'],
            'code'      => [
                'nullable', 'string', 'max:20',
                Rule::unique('categories', 'code')->where(fn ($q) => $q->where('tenant_id', $tenantId))->withoutTrashed(),
            ],
            'is_active' => ['boolean'],
        ]);

        $data['tenant_id'] = $tenantId;
        Category::create($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category): View
    {
        Gate::authorize('update', $category);

        $parents = $this->buildParentOptions($category->tenant_id, $category->id);

        return view('admin.categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        Gate::authorize('update', $category);

        $data = $request->validate([
            'parent_id' => ['nullable', Rule::exists('categories', 'id')->where(fn ($q) => $q->where('tenant_id', $category->tenant_id))],
            'name'      => ['required', 'string', 'max:100'],
            'code'      => [
                'nullable', 'string', 'max:20',
                Rule::unique('categories', 'code')
                    ->ignore($category->id)
                    ->where(fn ($q) => $q->where('tenant_id', $category->tenant_id))
                    ->withoutTrashed(),
            ],
            'is_active' => ['boolean'],
        ]);

        // Guard against circular reference
        if ($data['parent_id'] ?? null) {
            $descendantIds = $this->collectDescendantIds(
                Category::query()->forTenant($category->tenant_id)->get()->keyBy('id'),
                $category->id
            );
            if ($descendantIds->contains($data['parent_id']) || $data['parent_id'] == $category->id) {
                return back()->withErrors(['parent_id' => 'Parent tidak boleh merupakan turunan dari kategori ini.'])->withInput();
            }
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function deactivate(Category $category): RedirectResponse
    {
        Gate::authorize('deactivate', $category);

        $category->update(['is_active' => false]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil dinonaktifkan.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        Gate::authorize('delete', $category);

        try {
            $category->delete();
        } catch (\RuntimeException $e) {
            return redirect()->route('admin.categories.index')
                ->with('error', $e->getMessage());
        }

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }

    /**
     * Build a flat, depth-aware list of categories for <select> options.
     * Excludes $excludeId and all its descendants (used when editing to
     * prevent circular parent assignments).
     */
    private function buildParentOptions(int $tenantId, ?int $excludeId = null): Collection
    {
        $all = Category::query()
            ->forTenant($tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->keyBy('id');

        $excludedIds = collect();
        if ($excludeId) {
            $excludedIds = $this->collectDescendantIds($all, $excludeId);
            $excludedIds->push($excludeId);
        }

        $result = collect();
        $this->flattenForSelect($all, null, 0, $excludedIds, $result);
        return $result;
    }

    /** Recursively collect all descendant IDs of a given node. */
    private function collectDescendantIds(Collection $all, int $parentId): Collection
    {
        $ids = collect();
        foreach ($all->where('parent_id', $parentId) as $child) {
            $ids->push($child->id);
            $ids = $ids->merge($this->collectDescendantIds($all, $child->id));
        }
        return $ids;
    }

    /** Recursively build a flat list with depth for <select> rendering. */
    private function flattenForSelect(Collection $all, ?int $parentId, int $depth, Collection $excludedIds, Collection &$result): void
    {
        foreach ($all->where('parent_id', $parentId)->sortBy('name') as $cat) {
            if ($excludedIds->contains($cat->id)) {
                continue;
            }
            $result->push((object) ['id' => $cat->id, 'name' => $cat->name, 'depth' => $depth]);
            $this->flattenForSelect($all, $cat->id, $depth + 1, $excludedIds, $result);
        }
    }
}
