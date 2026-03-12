<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBranchRequest;
use App\Http\Requests\Admin\UpdateBranchRequest;
use App\Models\Branch;
use App\Models\StockLocation;
use App\Models\Tenant;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Branch::class);

        $query = Branch::query()
            ->withCount(['users', 'warehouses'])
            ->forTenant($request->user()->getAccessibleTenantId())
            ->orderBy('name');

        if (! $request->user()->isGlobal()) {
            $query->whereKey($request->user()->branch_id);
        }

        if ($request->filled('tenant_id') && $request->user()->isPlatformAdmin()) {
            $query->where('tenant_id', $request->integer('tenant_id'));
        }

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(fn ($inner) => $inner
                ->where('name', 'like', "%{$q}%")
                ->orWhere('branch_code', 'like', "%{$q}%"));
        }

        if ($request->filled('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $branches = $query->paginate(10)->withQueryString();

        $tenants = $this->availableTenants($request->user(), $request->integer('tenant_id'));

        return view('admin.branches.index', compact('branches', 'tenants'));
    }

    public function create(): View
    {
        Gate::authorize('create', Branch::class);

        $tenants = $this->availableTenants(request()->user());

        return view('admin.branches.create', compact('tenants'));
    }

    public function store(StoreBranchRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['timezone'] = $data['timezone'] ?: 'Asia/Jakarta';

        DB::transaction(function () use ($data) {
            $branch = Branch::create($data);

            Warehouse::create([
                'tenant_id' => $branch->tenant_id,
                'branch_id' => $branch->id,
                'warehouse_code' => "{$branch->branch_code}-MAIN",
                'name' => 'Gudang Utama',
                'type' => 'MAIN',
                'is_active' => true,
            ]);

            $mainWarehouse = Warehouse::where('tenant_id', $branch->tenant_id)
                ->where('warehouse_code', "{$branch->branch_code}-MAIN")
                ->first();

            if ($mainWarehouse) {
                StockLocation::create([
                    'tenant_id' => $branch->tenant_id,
                    'branch_id' => $branch->id,
                    'warehouse_id' => $mainWarehouse->id,
                    'code' => "{$branch->branch_code}-MAIN-ROOT",
                    'name' => "Lokasi Utama {$branch->name}",
                    'type' => 'WAREHOUSE',
                    'is_active' => true,
                ]);
            }
        });

        return redirect()
            ->route('admin.branches.index')
            ->with('success', 'Cabang berhasil ditambahkan. Gudang utama otomatis dibuat.');
    }

    public function show(Branch $branch): View
    {
        Gate::authorize('view', $branch);

        $branch->load(['warehouses' => fn ($query) => $query->orderBy('name'), 'users' => fn ($query) => $query->with('role')->orderBy('name')]);

        return view('admin.branches.show', compact('branch'));
    }

    public function edit(Branch $branch): View
    {
        Gate::authorize('update', $branch);

        $tenants = $this->availableTenants(request()->user(), $branch->tenant_id);

        return view('admin.branches.edit', compact('branch', 'tenants'));
    }

    public function update(UpdateBranchRequest $request, Branch $branch): RedirectResponse
    {
        Gate::authorize('update', $branch);

        $data = $request->validated();
        $data['timezone'] = $data['timezone'] ?: 'Asia/Jakarta';

        $branch->update($data);

        return redirect()
            ->route('admin.branches.index')
            ->with('success', 'Cabang berhasil diperbarui.');
    }

    public function deactivate(Branch $branch): RedirectResponse
    {
        Gate::authorize('deactivate', $branch);

        $branch->update(['is_active' => false]);

        return redirect()
            ->route('admin.branches.index')
            ->with('success', 'Cabang berhasil dinonaktifkan.');
    }

    public function reactivate(Branch $branch): RedirectResponse
    {
        Gate::authorize('deactivate', $branch);

        $branch->update(['is_active' => true]);

        return redirect()
            ->route('admin.branches.index')
            ->with('success', 'Cabang berhasil diaktifkan kembali.');
    }

    private function availableTenants($user, ?int $selectedTenantId = null)
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
