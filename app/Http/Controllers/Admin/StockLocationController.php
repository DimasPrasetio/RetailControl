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

        $query = StockLocation::query()
            ->with(['branch', 'warehouse', 'parent'])
            ->forTenant($request->user()->getAccessibleTenantId())
            ->forBranch($request->user()->getAccessibleBranchId())
            ->orderBy('name');

        if ($request->filled('branch_id') && $request->user()->isGlobal()) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->integer('warehouse_id'));
        }

        if ($request->filled('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($inner) use ($q) {
                $inner->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");
            });
        }

        $stockLocations = $query->paginate(10)->withQueryString();
        $selectedBranchId = $request->integer('branch_id');

        return view('admin.stock-locations.index', [
            'stockLocations' => $stockLocations,
            'branches' => $this->availableBranches($request->user(), $selectedBranchId),
            'warehouses' => $this->availableWarehouses($request->user(), $selectedBranchId, $request->integer('warehouse_id')),
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
        $warehouse = $this->resolveWarehouse($request, $branch, $request->integer('warehouse_id'));
        $data = $this->validated($request, $branch->tenant_id);
        $parent = $this->resolveParent($request, $branch->tenant_id, $warehouse->id, $request->integer('parent_id'));

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

        abort_if($stockLocation->isSystemLocation(), 403, 'Lokasi sistem tidak dapat diubah manual.');

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

        abort_if($stockLocation->isSystemLocation(), 403, 'Lokasi sistem tidak dapat diubah manual.');

        $branch = $this->resolveBranch($request, $stockLocation->branch_id);
        $warehouse = $this->resolveWarehouse($request, $branch, $stockLocation->warehouse_id);
        $data = $this->validated($request, $stockLocation->tenant_id, $stockLocation);
        $parent = $this->resolveParent($request, $stockLocation->tenant_id, $warehouse->id, $request->integer('parent_id'), $stockLocation->id);

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

        if ($stockLocation->children()->where('is_active', true)->exists()) {
            return redirect()
                ->route('admin.stock-locations.index')
                ->with('error', 'Nonaktifkan lokasi turunan terlebih dahulu.');
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

    private function validated(Request $request, int $tenantId, ?StockLocation $stockLocation = null): array
    {
        $uniqueCode = Rule::unique('stock_locations', 'code')
            ->where(fn ($query) => $query->where('tenant_id', $tenantId));

        if ($stockLocation) {
            $uniqueCode->ignore($stockLocation->id);
        }

        return $request->validate([
            'code' => ['required', 'string', 'max:40', 'regex:/^[A-Z0-9_-]+$/i', $uniqueCode],
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', Rule::in(['AREA', 'SUB_AREA'])],
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

    private function resolveWarehouse(Request $request, Branch $branch, ?int $warehouseId): Warehouse
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

    private function resolveParent(Request $request, int $tenantId, int $warehouseId, ?int $parentId, ?int $ignoreId = null): ?StockLocation
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

    private function availableParents($user, ?int $warehouseId, ?int $ignoreId = null)
    {
        if (! $warehouseId) {
            return collect();
        }

        $query = StockLocation::query()
            ->forTenant($user->getAccessibleTenantId())
            ->forBranch($user->getAccessibleBranchId())
            ->where('warehouse_id', $warehouseId)
            ->where('is_active', true)
            ->orderBy('name');

        if ($ignoreId) {
            $query->whereKeyNot($ignoreId);
        }

        return $query->get();
    }
}
