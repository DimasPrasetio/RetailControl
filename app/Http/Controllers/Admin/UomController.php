<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Uom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UomController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Uom::class);

        $uoms = Uom::orderBy('code')->paginate(10);

        return view('admin.uoms.index', compact('uoms'));
    }

    public function create(): View
    {
        Gate::authorize('create', Uom::class);

        return view('admin.uoms.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Uom::class);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:uoms,code'],
            'name' => ['required', 'string', 'max:50'],
        ]);

        $data['code'] = strtoupper($data['code']);
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
            'code' => ['required', 'string', 'max:20', Rule::unique('uoms', 'code')->ignore($uom->id)],
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
}
