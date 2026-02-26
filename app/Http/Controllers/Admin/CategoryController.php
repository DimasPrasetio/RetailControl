<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Category::class);

        $categories = Category::with('parent')->orderBy('name')->paginate(30);

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        Gate::authorize('create', Category::class);

        $parents = Category::where('is_active', true)->whereNull('parent_id')->orderBy('name')->get();

        return view('admin.categories.create', compact('parents'));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Category::class);

        $data = $request->validate([
            'parent_id' => ['nullable', 'exists:categories,id'],
            'name'      => ['required', 'string', 'max:100'],
            'code'      => ['nullable', 'string', 'max:20', 'unique:categories,code'],
            'is_active' => ['boolean'],
        ]);

        Category::create($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category): View
    {
        Gate::authorize('update', $category);

        $parents = Category::where('is_active', true)
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
            'parent_id' => ['nullable', 'exists:categories,id'],
            'name'      => ['required', 'string', 'max:100'],
            'code'      => ['nullable', 'string', 'max:20',
                            \Illuminate\Validation\Rule::unique('categories', 'code')->ignore($category->id)],
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
