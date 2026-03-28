<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\InteractsWithTenantContext;
use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use App\Models\Uom;
use App\Services\Product\ExcelImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ItemController extends Controller
{
    use InteractsWithTenantContext;

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Item::class);

        $tenantId = $request->user()->getAccessibleTenantId();
        $query = Item::with(['brand', 'category', 'baseUom'])
            ->forTenant($tenantId);

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('active')) {
            $query->where('is_active', (bool) $request->active);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('sku_code', 'like', "%{$q}%");
            });
        }

        $items = $query->orderBy('name')->paginate(10)->withQueryString();
        $brands = Brand::query()->forTenant($tenantId)->where('is_active', true)->orderBy('name')->get();
        $categories = Category::query()->forTenant($tenantId)->where('is_active', true)->orderBy('name')->get();

        // Build brand↔category relationship map from existing items for cascading filter
        $pairs = Item::select('brand_id', 'category_id')
            ->forTenant($tenantId)
            ->whereNotNull('brand_id')
            ->whereNotNull('category_id')
            ->distinct()
            ->get();

        $brandToCategories = [];
        $categoryToBrands = [];
        foreach ($pairs as $p) {
            $brandToCategories[$p->brand_id][] = $p->category_id;
            $categoryToBrands[$p->category_id][] = $p->brand_id;
        }
        // Deduplicate
        $brandToCategories = array_map('array_unique', $brandToCategories);
        $categoryToBrands = array_map('array_unique', $categoryToBrands);

        $filterMap = compact('brandToCategories', 'categoryToBrands');

        return view('admin.items.index', compact('items', 'brands', 'categories', 'filterMap'));
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Item::class);

        return view('admin.items.create', $this->formData($request));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Item::class);

        $data = $this->validated($request);
        $data['tenant_id'] = $this->resolveTenantId($request);
        $data['attributes_json'] = $this->parseCategoryAttributes($request, $data['tenant_id'], $data['category_id'] ?? null);
        $data['custom_fields_json'] = $this->parseKeyValue($request->input('custom_keys'), $request->input('custom_values'));

        $item = Item::create($data);
        $this->syncItemUnits($item, $request);

        return redirect()->route('admin.items.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Item $item): View
    {
        Gate::authorize('view', $item);

        $auditLogs = AuditLog::where('auditable_type', Item::class)
            ->where('auditable_id', $item->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        $item->load(['brand', 'category', 'baseUom', 'sellingUom', 'purchaseUom', 'itemUnits.uom', 'barcodes.itemUnit.uom', 'priceListItems.priceList', 'priceListItems.priceUom']);

        return view('admin.items.show', compact('item', 'auditLogs'));
    }

    public function edit(Request $request, Item $item): View
    {
        Gate::authorize('update', $item);

        return view('admin.items.edit', array_merge(['item' => $item], $this->formData($request, $item)));
    }

    public function update(Request $request, Item $item): RedirectResponse
    {
        Gate::authorize('update', $item);

        $data = $this->validated($request, $item);
        $data['tenant_id'] = $item->tenant_id;
        $data['attributes_json'] = $this->parseCategoryAttributes($request, $item->tenant_id, $data['category_id'] ?? null);
        $data['custom_fields_json'] = $this->parseKeyValue($request->input('custom_keys'), $request->input('custom_values'));

        $item->update($data);
        $this->syncItemUnits($item->fresh(), $request);

        return redirect()->route('admin.items.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function deactivate(Item $item): RedirectResponse
    {
        Gate::authorize('deactivate', $item);

        $item->update(['is_active' => false]);

        return redirect()->route('admin.items.index')
            ->with('success', 'Produk berhasil dinonaktifkan.');
    }

    public function destroy(Item $item): RedirectResponse
    {
        Gate::authorize('delete', $item);

        $item->delete();

        return redirect()->route('admin.items.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    // ─── Import ───────────────────────────────────────────────────────────────

    public function importForm(Request $request): View
    {
        Gate::authorize('import', Item::class);

        $selectedTenantId = $this->selectedTenantId($request);

        return view('admin.items.import', [
            'tenants' => $this->availableTenants($request->user(), $selectedTenantId),
            'selectedTenantId' => $selectedTenantId,
        ]);
    }

    public function import(Request $request, ExcelImportService $service): RedirectResponse
    {
        Gate::authorize('import', Item::class);

        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls', 'max:20480'],
        ]);

        $tenantId = $this->resolveTenantId($request);
        $path = $request->file('excel_file')->getRealPath();
        $result = $service->import($path, $tenantId);

        return redirect()->route('admin.items.import-form', ['tenant_id' => $tenantId])
            ->with('import_result', $result);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function formData(Request $request, ?Item $item = null): array
    {
        $tenantId = $item?->tenant_id ?? $this->selectedTenantId($request);
        if ($tenantId === null) {
            return [
                'tenants' => $this->availableTenants($request->user()),
                'selectedTenantId' => null,
                'brands' => collect(),
                'categories' => collect(),
                'uoms' => collect(),
                'categoryAttributes' => [],
                'itemUnitRows' => [],
            ];
        }

        $categories = Category::query()
            ->forTenant($tenantId)
            ->where('is_active', true)
            ->with(['attributeDefinitions' => fn ($query) => $query->orderBy('label')])
            ->orderBy('name')
            ->get();

        return [
            'tenants' => $this->availableTenants($request->user(), $tenantId),
            'selectedTenantId' => $tenantId,
            'brands' => Brand::query()->forTenant($tenantId)->where('is_active', true)->orderBy('name')->get(),
            'categories' => $categories,
            'uoms' => Uom::query()->forTenant($tenantId)->orderBy('code')->get(),
            'itemUnitRows' => $this->initialItemUnitRows($item),
            'categoryAttributes' => $categories
                ->mapWithKeys(fn ($category) => [
                    $category->id => $category->attributeDefinitions->map(fn ($attribute) => [
                        'key' => $attribute->key,
                        'label' => $attribute->label,
                        'data_type' => $attribute->data_type,
                        'unit' => $attribute->unit,
                        'options' => $attribute->options_json ?? [],
                        'is_required' => $attribute->is_required,
                    ])->values(),
                ])
                ->toArray(),
        ];
    }

    private function validated(Request $request, ?Item $item = null): array
    {
        $tenantId = $item?->tenant_id ?? $this->resolveTenantId($request);
        $skuRule = ['required', 'string', 'max:100'];
        if ($item) {
            $skuRule[] = Rule::unique('items', 'sku_code')
                ->ignore($item->id)
                ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                ->withoutTrashed();
        } else {
            $skuRule[] = Rule::unique('items', 'sku_code')
                ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                ->withoutTrashed();
        }

        $validator = Validator::make($request->all(), [
            'sku_code' => $skuRule,
            'name' => ['required', 'string', 'max:255'],
            'brand_id' => ['nullable', Rule::exists('brands', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'base_uom_id' => ['required', Rule::exists('uoms', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'selling_uom_id' => ['nullable', Rule::exists('uoms', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'purchase_uom_id' => ['nullable', Rule::exists('uoms', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'item_units' => ['nullable', 'array'],
            'item_units.*.uom_id' => ['nullable', Rule::exists('uoms', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'item_units.*.conversion_qty' => ['nullable', 'numeric', 'min:0.0001'],
            'item_units.*.allow_sale' => ['nullable', 'boolean'],
            'item_units.*.allow_purchase' => ['nullable', 'boolean'],
            'tax_included' => ['boolean'],
            'is_stockable' => ['boolean'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'minimum_selling_price' => ['nullable', 'numeric', 'min:0', 'lte:selling_price'],
            'is_active' => ['boolean'],
        ]);

        $validator->after(function ($validator) use ($request): void {
            $unitRows = $this->normalizedItemUnits($request);
            $baseUomId = (int) $request->input('base_uom_id');
            $sellingUomId = (int) ($request->input('selling_uom_id') ?: $baseUomId);
            $purchaseUomId = $request->filled('purchase_uom_id') ? (int) $request->input('purchase_uom_id') : null;

            if ($baseUomId === 0) {
                return;
            }

            $additionalIds = $unitRows->pluck('uom_id')->all();
            if (count($additionalIds) !== count(array_unique($additionalIds))) {
                $validator->errors()->add('item_units', 'Satuan tambahan tidak boleh duplikat.');
            }

            if (in_array($baseUomId, $additionalIds, true)) {
                $validator->errors()->add('item_units', 'Satuan dasar stok tidak boleh dimasukkan ulang sebagai satuan tambahan.');
            }

            $availableIds = collect([$baseUomId])->merge($additionalIds);

            if (! $availableIds->contains($sellingUomId)) {
                $validator->errors()->add('selling_uom_id', 'Default satuan jual harus berasal dari daftar satuan produk.');
            }

            if ($purchaseUomId !== null && ! $availableIds->contains($purchaseUomId)) {
                $validator->errors()->add('purchase_uom_id', 'Default satuan beli harus berasal dari daftar satuan produk.');
            }

            $sellingRow = $unitRows->firstWhere('uom_id', $sellingUomId);
            if ($sellingUomId !== $baseUomId && ! ($sellingRow['allow_sale'] ?? false)) {
                $validator->errors()->add('selling_uom_id', 'Default satuan jual harus ditandai boleh dijual.');
            }

            $purchaseRow = $unitRows->firstWhere('uom_id', $purchaseUomId);
            if ($purchaseUomId !== null && $purchaseUomId !== $baseUomId && ! ($purchaseRow['allow_purchase'] ?? false)) {
                $validator->errors()->add('purchase_uom_id', 'Default satuan beli harus ditandai boleh dibeli.');
            }

            foreach ($unitRows as $index => $row) {
                if ($row['uom_id'] && $row['conversion_qty'] <= 0) {
                    $validator->errors()->add("item_units.$index.conversion_qty", 'Konversi satuan harus lebih besar dari nol.');
                }
            }
        });

        $data = $validator->validate();
        $data['selling_uom_id'] = (int) ($data['selling_uom_id'] ?? $data['base_uom_id']);
        $data['purchase_uom_id'] = $data['purchase_uom_id'] ?? null;
        $data['pack_qty'] = $this->resolvePackQty(
            (int) $data['base_uom_id'],
            $data['purchase_uom_id'] ? (int) $data['purchase_uom_id'] : null,
            $this->normalizedItemUnits($request)
        );

        // Convert cost_price from purchase unit to base unit before storing
        if (isset($data['cost_price']) && $data['cost_price'] !== null && $data['pack_qty'] > 1) {
            $data['cost_price'] = round($data['cost_price'] / $data['pack_qty'], 4);
        }

        return $data;
    }

    private function parseCategoryAttributes(Request $request, int $tenantId, ?int $categoryId): ?array
    {
        if (! $categoryId) {
            return $this->parseKeyValue($request->input('attr_keys'), $request->input('attr_values'));
        }

        $category = Category::query()
            ->forTenant($tenantId)
            ->with('attributeDefinitions')
            ->find($categoryId);

        if (! $category) {
            return null;
        }

        $input = $request->input('attributes', []);
        $result = [];

        foreach ($category->attributeDefinitions as $attributeDefinition) {
            $value = data_get($input, $attributeDefinition->key);
            if ($value === null || $value === '') {
                continue;
            }

            $result[$attributeDefinition->key] = is_array($value)
                ? array_values(array_filter($value, fn ($item) => $item !== null && $item !== ''))
                : $value;
        }

        return ! empty($result) ? $result : null;
    }
    private function parseKeyValue(?array $keys, ?array $values): ?array
    {
        if (empty($keys) || empty($values)) {
            return null;
        }

        $result = [];
        foreach ($keys as $i => $key) {
            $key = trim((string) $key);
            $val = trim((string) ($values[$i] ?? ''));
            if ($key !== '') {
                $result[$key] = $val;
            }
        }

        return !empty($result) ? $result : null;
    }

    private function normalizedItemUnits(Request $request): Collection
    {
        return collect($request->input('item_units', []))
            ->map(function ($row) {
                $uomId = (int) ($row['uom_id'] ?? 0);

                return [
                    'uom_id' => $uomId,
                    'conversion_qty' => (float) ($row['conversion_qty'] ?? 0),
                    'allow_sale' => filter_var($row['allow_sale'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'allow_purchase' => filter_var($row['allow_purchase'] ?? false, FILTER_VALIDATE_BOOLEAN),
                ];
            })
            ->filter(fn ($row) => $row['uom_id'] > 0)
            ->values();
    }

    private function resolvePackQty(int $baseUomId, ?int $purchaseUomId, Collection $itemUnits): float
    {
        if ($purchaseUomId === null || $purchaseUomId === $baseUomId) {
            return 1;
        }

        return (float) ($itemUnits->firstWhere('uom_id', $purchaseUomId)['conversion_qty'] ?? 1);
    }

    private function syncItemUnits(Item $item, Request $request): void
    {
        $unitRows = $this->normalizedItemUnits($request);
        $sellingUomId = (int) ($item->selling_uom_id ?: $item->base_uom_id);
        $purchaseUomId = $item->purchase_uom_id ? (int) $item->purchase_uom_id : null;

        $rows = [
            $item->base_uom_id => [
                'tenant_id' => $item->tenant_id,
                'conversion_qty' => 1,
                'is_base' => true,
                'allow_sale' => true,
                'allow_purchase' => $purchaseUomId === (int) $item->base_uom_id,
                'is_default_sale' => $sellingUomId === (int) $item->base_uom_id,
                'is_default_purchase' => $purchaseUomId === (int) $item->base_uom_id,
                'is_active' => true,
            ],
        ];

        foreach ($unitRows as $row) {
            $rows[$row['uom_id']] = [
                'tenant_id' => $item->tenant_id,
                'conversion_qty' => $row['conversion_qty'],
                'is_base' => false,
                'allow_sale' => $row['allow_sale'] || $sellingUomId === $row['uom_id'],
                'allow_purchase' => $row['allow_purchase'] || $purchaseUomId === $row['uom_id'],
                'is_default_sale' => $sellingUomId === $row['uom_id'],
                'is_default_purchase' => $purchaseUomId === $row['uom_id'],
                'is_active' => true,
            ];
        }

        DB::transaction(function () use ($item, $rows): void {
            $item->itemUnits()->whereNotIn('uom_id', array_keys($rows))->delete();

            foreach ($rows as $uomId => $attributes) {
                $item->itemUnits()->updateOrCreate(
                    ['uom_id' => $uomId],
                    $attributes
                );
            }
        });
    }

    private function initialItemUnitRows(?Item $item): array
    {
        if (! $item) {
            return [];
        }

        return $item->itemUnits()
            ->with('uom')
            ->where('is_base', false)
            ->get()
            ->map(fn ($unit) => [
                'uom_id' => $unit->uom_id,
                'conversion_qty' => $unit->conversion_qty,
                'allow_sale' => $unit->allow_sale,
                'allow_purchase' => $unit->allow_purchase,
            ])
            ->values()
            ->all();
    }
}
