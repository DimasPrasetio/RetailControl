<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\InteractsWithTenantContext;
use App\Models\Uom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UomController extends Controller
{
    use InteractsWithTenantContext;

    public function index(): View
    {
        Gate::authorize('viewAny', Uom::class);

        $uoms = Uom::query()
            ->forTenant(request()->user()->getAccessibleTenantId())
            ->orderBy('code')
            ->paginate(10);

        return view('admin.uoms.index', compact('uoms'));
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Uom::class);

        $selectedTenantId = $this->selectedTenantId($request);

        return view('admin.uoms.create', [
            'tenants' => $this->availableTenants($request->user(), $selectedTenantId),
            'selectedTenantId' => $selectedTenantId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Uom::class);

        $tenantId = $this->resolveTenantId($request);
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('uoms', 'code')->where(fn ($query) => $query->where('tenant_id', $tenantId))->withoutTrashed(),
            ],
            'name' => ['required', 'string', 'max:50'],
        ]);

        $data['code'] = strtoupper($data['code']);
        $data['tenant_id'] = $tenantId;
        Uom::create($data);

        return redirect()->route('admin.uoms.index')
            ->with('success', 'Satuan berhasil ditambahkan.');
    }

    public function edit(Uom $uom): View
    {
        Gate::authorize('update', $uom);

        return view('admin.uoms.edit', compact('uom'));
    }

    public function update(Request $request, Uom $uom): RedirectResponse
    {
        Gate::authorize('update', $uom);

        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('uoms', 'code')
                    ->ignore($uom->id)
                    ->where(fn ($query) => $query->where('tenant_id', $uom->tenant_id))
                    ->withoutTrashed(),
            ],
            'name' => ['required', 'string', 'max:50'],
        ]);

        $data['code'] = strtoupper($data['code']);
        $uom->update($data);

        return redirect()->route('admin.uoms.index')
            ->with('success', 'Satuan berhasil diperbarui.');
    }

    public function deactivate(Uom $uom): RedirectResponse
    {
        Gate::authorize('deactivate', $uom);

        $uom->update(['is_active' => false]);

        return redirect()->route('admin.uoms.index')
            ->with('success', 'Satuan berhasil dinonaktifkan.');
    }

    public function destroy(Uom $uom): RedirectResponse
    {
        Gate::authorize('delete', $uom);

        $uom->delete();

        return redirect()->route('admin.uoms.index')
            ->with('success', 'Satuan berhasil dihapus.');
    }
}
