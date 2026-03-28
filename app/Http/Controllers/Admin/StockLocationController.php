<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\StockLocation;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StockLocationController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', StockLocation::class);

        $isSearch         = $request->filled('q');
        $selectedBranchId = $request->integer('branch_id') ?: null;
        $selectedWarehouseId = $request->integer('warehouse_id') ?: null;

        $base = StockLocation::query()
            ->forTenant($request->user()->getAccessibleTenantId())
            ->forBranch($request->user()->getAccessibleBranchId());

        if ($selectedBranchId && $request->user()->isGlobal()) {
            $base->where('branch_id', $selectedBranchId);
        }

        if ($selectedWarehouseId) {
            $base->where('warehouse_id', $selectedWarehouseId);
        }

        if ($request->filled('active')) {
            $base->where('is_active', $request->boolean('active'));
        }

        $branches         = $this->availableBranches($request->user(), $selectedBranchId);
        $filterWarehouses = $this->availableWarehouses($request->user(), $selectedBranchId, $selectedWarehouseId);

        if ($isSearch) {
            $q = $request->input('q');
            $stockLocations = (clone $base)
                ->with(['branch', 'warehouse', 'parent'])
                ->where(fn($inner) => $inner
                    ->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%"))
                ->orderBy('name')
                ->paginate(20)
                ->withQueryString();

            return view('admin.stock-locations.index', [
                'treeMode'       => false,
                'stockLocations' => $stockLocations,
                'branches'       => $branches,
                'warehouses'     => $filterWarehouses,
            ]);
        }

        // Tree mode: load all at once (no N+1), build tree PHP-side
        $allLocations = (clone $base)
            ->with(['warehouse.branch'])
            ->orderBy('parent_id')
            ->orderBy('name')
            ->get()
            ->keyBy('id');

        // Group root nodes (no parent) by warehouse
        $rootsByWarehouse = $allLocations->whereNull('parent_id')->groupBy('warehouse_id');

        $warehouseModels = Warehouse::with('branch')
            ->whereIn('id', $rootsByWarehouse->keys()->filter()->all())
            ->orderBy('name')
            ->get()
            ->keyBy('id');

        return view('admin.stock-locations.index', [
            'treeMode'         => true,
            'allLocations'     => $allLocations,
            'rootsByWarehouse' => $rootsByWarehouse,
            'warehouseModels'  => $warehouseModels,
            'branches'         => $branches,
            'warehouses'       => $filterWarehouses,
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', StockLocation::class);

        $selectedBranchId = $request->integer('branch_id') ?: $request->user()->branch_id;
        $selectedWarehouseId = $request->integer('warehouse_id');

        return view('admin.stock-locations.create', [
            'branches' => $this->availableBranches($request->user(), $selectedBranchId),
            'warehouses' => $this->availableWarehouses($request->user(), $selectedBranchId, $selectedWarehouseId),
            'parents' => $this->availableParents($request->user(), $selectedWarehouseId),
            'selectedBranchId' => $selectedBranchId,
            'selectedWarehouseId' => $selectedWarehouseId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', StockLocation::class);

        $branch = $this->resolveBranch($request, $request->integer('branch_id'));
        $warehouse = $this->resolveWarehouse($branch, $request->integer('warehouse_id'));
        $data = $this->validated($request, $branch->tenant_id);
        $parent = $this->resolveParent($branch->tenant_id, $warehouse->id, $request->integer('parent_id'));

        $data['tenant_id'] = $branch->tenant_id;
        $data['branch_id'] = $branch->id;
        $data['warehouse_id'] = $warehouse->id;
        $data['parent_id'] = $parent?->id;
        $data['code'] = strtoupper($data['code']);

        StockLocation::create($data);

        return redirect()
            ->route('admin.stock-locations.index', ['branch_id' => $branch->id, 'warehouse_id' => $warehouse->id])
            ->with('success', 'Lokasi stok berhasil ditambahkan.');
    }

    public function edit(Request $request, StockLocation $stockLocation): View
    {
        Gate::authorize('update', $stockLocation);

        return view('admin.stock-locations.edit', [
            'stockLocation' => $stockLocation,
            'branches' => $this->availableBranches($request->user(), $stockLocation->branch_id),
            'warehouses' => $this->availableWarehouses($request->user(), $stockLocation->branch_id, $stockLocation->warehouse_id),
            'parents' => $this->availableParents($request->user(), $stockLocation->warehouse_id, $stockLocation->id),
            'selectedBranchId' => $stockLocation->branch_id,
            'selectedWarehouseId' => $stockLocation->warehouse_id,
        ]);
    }

    public function update(Request $request, StockLocation $stockLocation): RedirectResponse
    {
        Gate::authorize('update', $stockLocation);

        $branch = $this->resolveBranch($request, $stockLocation->branch_id);
        $warehouse = $this->resolveWarehouse($branch, $stockLocation->warehouse_id);

        // Lokasi sistem hanya boleh diubah name dan label-nya
        if ($stockLocation->isSystemLocation()) {
            $data = $request->validate([
                'name'  => ['required', 'string', 'max:100'],
                'label' => ['nullable', 'string', 'max:50'],
            ]);
            $stockLocation->update($data);

            return redirect()
                ->route('admin.stock-locations.index', ['branch_id' => $branch->id, 'warehouse_id' => $warehouse->id])
                ->with('success', 'Nama lokasi sistem berhasil diperbarui.');
        }

        $data = $this->validated($request, $stockLocation->tenant_id, $stockLocation);
        $parent = $this->resolveParent($stockLocation->tenant_id, $warehouse->id, $request->integer('parent_id'), $stockLocation->id);

        // Guard against circular reference
        if ($parent) {
            $all = StockLocation::query()->forTenant($stockLocation->tenant_id)->where('warehouse_id', $warehouse->id)->get()->keyBy('id');
            $descendantIds = $this->collectDescendantIds($all, $stockLocation->id);
            if ($descendantIds->contains($parent->id) || $parent->id === $stockLocation->id) {
                return back()->withErrors(['parent_id' => 'Parent tidak boleh merupakan turunan dari lokasi ini.'])->withInput();
            }
        }

        $data['tenant_id'] = $stockLocation->tenant_id;
        $data['branch_id'] = $branch->id;
        $data['warehouse_id'] = $warehouse->id;
        $data['parent_id'] = $parent?->id;
        $data['code'] = strtoupper($data['code']);

        $stockLocation->update($data);

        return redirect()
            ->route('admin.stock-locations.index', ['branch_id' => $branch->id, 'warehouse_id' => $warehouse->id])
            ->with('success', 'Lokasi stok berhasil diperbarui.');
    }

    public function deactivate(StockLocation $stockLocation): RedirectResponse
    {
        Gate::authorize('deactivate', $stockLocation);

        if ($stockLocation->isSystemLocation()) {
            return redirect()
                ->route('admin.stock-locations.index')
                ->with('error', 'Lokasi sistem tidak dapat dinonaktifkan.');
        }

        $stockLocation->update(['is_active' => false]);

        return redirect()
            ->route('admin.stock-locations.index')
            ->with('success', 'Lokasi stok berhasil dinonaktifkan.');
    }

    public function reactivate(StockLocation $stockLocation): RedirectResponse
    {
        Gate::authorize('deactivate', $stockLocation);

        if ($stockLocation->isSystemLocation()) {
            return redirect()
                ->route('admin.stock-locations.index')
                ->with('error', 'Lokasi sistem selalu aktif.');
        }

        $stockLocation->update(['is_active' => true]);

        return redirect()
            ->route('admin.stock-locations.index')
            ->with('success', 'Lokasi stok berhasil diaktifkan kembali.');
    }

    public function destroy(StockLocation $stockLocation): RedirectResponse
    {
        Gate::authorize('delete', $stockLocation);

        if ($stockLocation->isSystemLocation()) {
            return redirect()
                ->route('admin.stock-locations.index')
                ->with('error', 'Lokasi sistem tidak dapat dihapus.');
        }

        try {
            $stockLocation->delete();
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('admin.stock-locations.index')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.stock-locations.index')
            ->with('success', 'Lokasi stok berhasil dihapus.');
    }

    private function validated(Request $request, int $tenantId, ?StockLocation $stockLocation = null): array
    {
        $uniqueCode = Rule::unique('stock_locations', 'code')
            ->where(fn ($query) => $query->where('tenant_id', $tenantId))
            ->withoutTrashed();

        if ($stockLocation) {
            $uniqueCode->ignore($stockLocation->id);
        }

        return $request->validate([
            'code' => ['required', 'string', 'max:40', 'regex:/^[A-Z0-9_-]+$/i', $uniqueCode],
            'name' => ['required', 'string', 'max:100'],
            'label' => ['nullable', 'string', 'max:50'],
            'parent_id' => ['nullable', 'integer'],
        ]);
    }

    private function resolveBranch(Request $request, ?int $branchId): Branch
    {
        $branch = Branch::query()->findOrFail($branchId);

        if (! $request->user()->canAccessTenant($branch->tenant_id)) {
            abort(403, 'Anda tidak dapat mengakses cabang ini.');
        }

        if (! $request->user()->isGlobal() && ! $request->user()->canAccessBranch($branch->id)) {
            abort(403, 'Anda tidak dapat mengakses cabang ini.');
        }

        if (! $branch->is_active) {
            abort(422, 'Cabang yang dipilih tidak aktif.');
        }

        return $branch;
    }

    private function resolveWarehouse(Branch $branch, ?int $warehouseId): Warehouse
    {
        $warehouse = Warehouse::query()
            ->forTenant($branch->tenant_id)
            ->where('branch_id', $branch->id)
            ->findOrFail($warehouseId);

        if (! $warehouse->is_active) {
            abort(422, 'Gudang yang dipilih tidak aktif.');
        }

        return $warehouse;
    }

    private function resolveParent(int $tenantId, int $warehouseId, ?int $parentId, ?int $ignoreId = null): ?StockLocation
    {
        if (! $parentId) {
            return null;
        }

        $query = StockLocation::query()
            ->forTenant($tenantId)
            ->where('warehouse_id', $warehouseId);

        if ($ignoreId) {
            $query->whereKeyNot($ignoreId);
        }

        return $query->findOrFail($parentId);
    }

    private function availableBranches($user, ?int $selectedBranchId = null)
    {
        $query = Branch::query()
            ->forTenant($user->getAccessibleTenantId())
            ->orderBy('name');

        if (! $user->isGlobal()) {
            return $query->whereKey($user->branch_id)->get();
        }

        $query->where(function ($inner) use ($selectedBranchId) {
            $inner->where('is_active', true);

            if ($selectedBranchId) {
                $inner->orWhere($inner->getModel()->getQualifiedKeyName(), $selectedBranchId);
            }
        });

        return $query->get();
    }

    private function availableWarehouses($user, ?int $selectedBranchId = null, ?int $selectedWarehouseId = null)
    {
        $query = Warehouse::query()
            ->forTenant($user->getAccessibleTenantId())
            ->forBranch($user->getAccessibleBranchId())
            ->orderBy('name');

        if ($selectedBranchId) {
            $query->where('branch_id', $selectedBranchId);
        }

        if ($user->isGlobal()) {
            $query->where(function ($inner) use ($selectedWarehouseId) {
                $inner->where('is_active', true);

                if ($selectedWarehouseId) {
                    $inner->orWhere($inner->getModel()->getQualifiedKeyName(), $selectedWarehouseId);
                }
            });
        }

        return $query->get();
    }

    private function availableParents($user, ?int $warehouseId, ?int $ignoreId = null): \Illuminate\Support\Collection
    {
        if (! $warehouseId) {
            return collect();
        }

        $all = StockLocation::query()
            ->forTenant($user->getAccessibleTenantId())
            ->forBranch($user->getAccessibleBranchId())
            ->where('warehouse_id', $warehouseId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->keyBy('id');

        $excludedIds = collect();
        if ($ignoreId) {
            $excludedIds = $this->collectDescendantIds($all, $ignoreId);
            $excludedIds->push($ignoreId);
        }

        $result = collect();
        $this->flattenForSelect($all, null, 0, $excludedIds, $result);
        return $result;
    }

    private function collectDescendantIds(\Illuminate\Support\Collection $all, int $parentId): \Illuminate\Support\Collection
    {
        $ids = collect();
        foreach ($all->where('parent_id', $parentId) as $child) {
            $ids->push($child->id);
            $ids = $ids->merge($this->collectDescendantIds($all, $child->id));
        }
        return $ids;
    }

    private function flattenForSelect(\Illuminate\Support\Collection $all, ?int $parentId, int $depth, \Illuminate\Support\Collection $excludedIds, \Illuminate\Support\Collection &$result): void
    {
        foreach ($all->where('parent_id', $parentId)->sortBy('name') as $loc) {
            if ($excludedIds->contains($loc->id)) {
                continue;
            }
            $result->push((object) ['id' => $loc->id, 'code' => $loc->code, 'name' => $loc->name, 'depth' => $depth]);
            $this->flattenForSelect($all, $loc->id, $depth + 1, $excludedIds, $result);
        }
    }
}
