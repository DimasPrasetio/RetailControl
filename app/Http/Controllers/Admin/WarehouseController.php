<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWarehouseRequest;
use App\Http\Requests\Admin\UpdateWarehouseRequest;
use App\Models\Branch;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Warehouse::class);

        $accessibleBranchId = $request->user()->getAccessibleBranchId();
        $accessibleTenantId = $request->user()->getAccessibleTenantId();
        $query = Warehouse::with('branch')
            ->forTenant($accessibleTenantId)
            ->forBranch($accessibleBranchId)
            ->orderBy('name');

        if ($request->filled('tenant_id') && $request->user()->isPlatformAdmin()) {
            $query->where('tenant_id', $request->integer('tenant_id'));
        }

        if ($request->filled('branch_id') && $request->user()->isGlobal()) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(fn ($inner) => $inner
                ->where('name', 'like', "%{$q}%")
                ->orWhere('warehouse_code', 'like', "%{$q}%"));
        }

        if ($request->filled('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $warehouses = $query->paginate(10)->withQueryString();
        $branches = $this->availableBranches($request->user());

        return view('admin.warehouses.index', compact('warehouses', 'branches'));
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Warehouse::class);

        $branches = $this->availableBranches($request->user());

        return view('admin.warehouses.create', compact('branches'));
    }

    public function store(StoreWarehouseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['type'] = $data['type'] ?: 'SECONDARY';

        Warehouse::create($data);

        return redirect()
            ->route('admin.warehouses.index')
            ->with('success', 'Gudang berhasil ditambahkan.');
    }

    public function show(Warehouse $warehouse): View
    {
        Gate::authorize('view', $warehouse);

        $warehouse->load('branch');

        return view('admin.warehouses.show', compact('warehouse'));
    }

    public function edit(Request $request, Warehouse $warehouse): View
    {
        Gate::authorize('update', $warehouse);

        $branches = $this->availableBranches($request->user(), $warehouse->branch_id);

        return view('admin.warehouses.edit', compact('warehouse', 'branches'));
    }

    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        Gate::authorize('update', $warehouse);

        $data = $request->validated();
        $data['type'] = $warehouse->type === 'MAIN'
            ? 'MAIN'
            : ($data['type'] ?: 'SECONDARY');

        $warehouse->update($data);

        return redirect()
            ->route('admin.warehouses.index')
            ->with('success', 'Gudang berhasil diperbarui.');
    }

    public function deactivate(Warehouse $warehouse): RedirectResponse
    {
        Gate::authorize('deactivate', $warehouse);

        if ($warehouse->type === 'MAIN') {
            return redirect()
                ->route('admin.warehouses.index')
                ->with('error', 'Gudang utama tidak dapat dinonaktifkan.');
        }

        $warehouse->update(['is_active' => false]);

        return redirect()
            ->route('admin.warehouses.index')
            ->with('success', 'Gudang berhasil dinonaktifkan.');
    }

    public function reactivate(Warehouse $warehouse): RedirectResponse
    {
        Gate::authorize('deactivate', $warehouse);

        $warehouse->update(['is_active' => true]);

        return redirect()
            ->route('admin.warehouses.index')
            ->with('success', 'Gudang berhasil diaktifkan kembali.');
    }

    private function availableBranches($user, ?int $selectedBranchId = null)
    {
        $query = Branch::query()
            ->forTenant($user->getAccessibleTenantId())
            ->orderBy('name');

        if (! $user->isGlobal()) {
            $query->whereKey($user->branch_id);
        } else {
            $query->where(function ($inner) use ($selectedBranchId) {
                $inner->where('is_active', true);

                if ($selectedBranchId) {
                    $inner->orWhere($inner->getModel()->getQualifiedKeyName(), $selectedBranchId);
                }
            });
        }

        return $query->get();
    }
}
