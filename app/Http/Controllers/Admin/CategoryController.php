<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\InteractsWithTenantContext;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    use InteractsWithTenantContext;

    public function index(): View
    {
        Gate::authorize('viewAny', Category::class);

        $categories = Category::with('parent')
            ->forTenant(request()->user()->getAccessibleTenantId())
            ->orderBy('name')
            ->paginate(10);

        return view('admin.categories.index', compact('categories'));
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Category::class);

        $selectedTenantId = $this->selectedTenantId($request);
        $parents = $selectedTenantId
            ? Category::query()
                ->forTenant($selectedTenantId)
                ->where('is_active', true)
                ->whereNull('parent_id')
                ->orderBy('name')
                ->get()
            : collect();

        return view('admin.categories.create', [
            'parents' => $parents,
            'tenants' => $this->availableTenants($request->user(), $selectedTenantId),
            'selectedTenantId' => $selectedTenantId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Category::class);

        $tenantId = $this->resolveTenantId($request);
        $data = $request->validate([
            'parent_id' => ['nullable', Rule::exists('categories', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'name' => ['required', 'string', 'max:100'],
            'code' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('categories', 'code')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
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

        $parents = Category::where('is_active', true)
            ->where('tenant_id', $category->tenant_id)
            ->whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();

        return view('admin.categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        Gate::authorize('update', $category);

        $data = $request->validate([
            'parent_id' => ['nullable', Rule::exists('categories', 'id')->where(fn ($query) => $query->where('tenant_id', $category->tenant_id))],
            'name' => ['required', 'string', 'max:100'],
            'code' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('categories', 'code')
                    ->ignore($category->id)
                    ->where(fn ($query) => $query->where('tenant_id', $category->tenant_id))
            ],
            'is_active' => ['boolean'],
        ]);

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
}
