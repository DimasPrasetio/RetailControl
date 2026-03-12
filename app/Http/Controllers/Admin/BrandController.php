<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\InteractsWithTenantContext;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BrandController extends Controller
{
    use InteractsWithTenantContext;

    public function index(): View
    {
        Gate::authorize('viewAny', Brand::class);

        $brands = Brand::query()
            ->forTenant(request()->user()->getAccessibleTenantId())
            ->orderBy('name')
            ->paginate(10);

        return view('admin.brands.index', compact('brands'));
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Brand::class);

        $selectedTenantId = $this->selectedTenantId($request);

        return view('admin.brands.create', [
            'tenants' => $this->availableTenants($request->user(), $selectedTenantId),
            'selectedTenantId' => $selectedTenantId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Brand::class);

        $tenantId = $this->resolveTenantId($request);
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('brands', 'name')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'is_active' => ['boolean'],
        ]);

        $data['tenant_id'] = $tenantId;
        Brand::create($data);

        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand berhasil ditambahkan.');
    }

    public function edit(Brand $brand): View
    {
        Gate::authorize('update', $brand);

        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand): RedirectResponse
    {
        Gate::authorize('update', $brand);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('brands', 'name')
                    ->ignore($brand->id)
                    ->where(fn ($query) => $query->where('tenant_id', $brand->tenant_id))
            ],
            'is_active' => ['boolean'],
        ]);

        $brand->update($data);

        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand berhasil diperbarui.');
    }

    public function deactivate(Brand $brand): RedirectResponse
    {
        Gate::authorize('deactivate', $brand);

        $brand->update(['is_active' => false]);

        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand berhasil dinonaktifkan.');
    }
}
