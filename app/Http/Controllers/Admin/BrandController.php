<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Brand::class);

        $brands = Brand::orderBy('name')->paginate(10);

        return view('admin.brands.index', compact('brands'));
    }

    public function create(): View
    {
        Gate::authorize('create', Brand::class);

        return view('admin.brands.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Brand::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:brands,name'],
            'is_active' => ['boolean'],
        ]);

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
                \Illuminate\Validation\Rule::unique('brands', 'name')->ignore($brand->id)
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
