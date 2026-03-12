<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\InteractsWithTenantContext;
use App\Http\Controllers\Controller;
use App\Models\AttributeDefinition;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttributeDefinitionController extends Controller
{
    use InteractsWithTenantContext;

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', AttributeDefinition::class);

        $tenantId = $request->user()->isPlatformAdmin()
            ? ($request->integer('tenant_id') ?: null)
            : $request->user()->tenant_id;

        $query = AttributeDefinition::query()
            ->with('categories')
            ->forTenant($tenantId)
            ->orderBy('label');

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($inner) use ($q) {
                $inner->where('label', 'like', "%{$q}%")
                    ->orWhere('key', 'like', "%{$q}%")
                    ->orWhere('data_type', 'like', "%{$q}%");
            });
        }

        $attributeDefinitions = $query->paginate(10)->withQueryString();

        return view('admin.attribute-definitions.index', [
            'attributeDefinitions' => $attributeDefinitions,
            'tenants' => $this->availableTenants($request->user(), $tenantId),
            'selectedTenantId' => $tenantId,
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', AttributeDefinition::class);

        $selectedTenantId = $this->selectedTenantId($request);

        return view('admin.attribute-definitions.create', [
            'tenants' => $this->availableTenants($request->user(), $selectedTenantId),
            'selectedTenantId' => $selectedTenantId,
            'categories' => $this->availableCategories($selectedTenantId),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', AttributeDefinition::class);

        $tenantId = $this->resolveTenantId($request);
        $data = $this->validated($request, $tenantId);
        $data['tenant_id'] = $tenantId;
        $data['key'] = strtolower($data['key']);
        $data['options_json'] = $this->parseOptions($request->input('options_text'));

        $attributeDefinition = AttributeDefinition::create($data);
        $attributeDefinition->categories()->sync($data['category_ids'] ?? []);

        return redirect()
            ->route('admin.attribute-definitions.index', ['tenant_id' => $tenantId])
            ->with('success', 'Definisi atribut berhasil ditambahkan.');
    }

    public function edit(AttributeDefinition $attributeDefinition): View
    {
        Gate::authorize('update', $attributeDefinition);

        return view('admin.attribute-definitions.edit', [
            'attributeDefinition' => $attributeDefinition,
            'tenants' => $this->availableTenants(request()->user(), $attributeDefinition->tenant_id),
            'selectedTenantId' => $attributeDefinition->tenant_id,
            'categories' => $this->availableCategories($attributeDefinition->tenant_id),
        ]);
    }

    public function update(Request $request, AttributeDefinition $attributeDefinition): RedirectResponse
    {
        Gate::authorize('update', $attributeDefinition);

        $data = $this->validated($request, $attributeDefinition->tenant_id, $attributeDefinition);
        $data['tenant_id'] = $attributeDefinition->tenant_id;
        $data['key'] = strtolower($data['key']);
        $data['options_json'] = $this->parseOptions($request->input('options_text'));

        $attributeDefinition->update($data);
        $attributeDefinition->categories()->sync($data['category_ids'] ?? []);

        return redirect()
            ->route('admin.attribute-definitions.index', ['tenant_id' => $attributeDefinition->tenant_id])
            ->with('success', 'Definisi atribut berhasil diperbarui.');
    }

    private function validated(Request $request, int $tenantId, ?AttributeDefinition $attributeDefinition = null): array
    {
        $uniqueKey = Rule::unique('attribute_definitions', 'key')
            ->where(fn ($query) => $query->where('tenant_id', $tenantId));

        if ($attributeDefinition) {
            $uniqueKey->ignore($attributeDefinition->id);
        }

        return $request->validate([
            'key' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/', $uniqueKey],
            'label' => ['required', 'string', 'max:100'],
            'data_type' => ['required', Rule::in(['number', 'text', 'select', 'boolean'])],
            'unit' => ['nullable', 'string', 'max:20'],
            'options_text' => ['nullable', 'string'],
            'is_required' => ['boolean'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => [
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
        ]);
    }

    private function availableCategories(?int $tenantId)
    {
        if (! $tenantId) {
            return collect();
        }

        return Category::query()
            ->forTenant($tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    private function parseOptions(?string $optionsText): ?array
    {
        if ($optionsText === null || trim($optionsText) === '') {
            return null;
        }

        $options = collect(explode(',', $optionsText))
            ->map(fn ($option) => trim($option))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return empty($options) ? null : $options;
    }
}
