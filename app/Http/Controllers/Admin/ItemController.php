<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use App\Models\Uom;
use App\Services\Product\ExcelImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Item::class);

        $query = Item::with(['brand', 'category', 'baseUom']);

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

        $items      = $query->orderBy('name')->paginate(30)->withQueryString();
        $brands     = Brand::where('is_active', true)->orderBy('name')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('admin.items.index', compact('items', 'brands', 'categories'));
    }

    public function create(): View
    {
        Gate::authorize('create', Item::class);

        return view('admin.items.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Item::class);

        $data = $this->validated($request);
        $data['attributes_json']    = $this->parseJson($request->input('attributes_json'));
        $data['custom_fields_json'] = $this->parseJson($request->input('custom_fields_json'));

        Item::create($data);

        return redirect()->route('admin.items.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Item $item): View
    {
        Gate::authorize('view', $item);

        $auditLogs = AuditLog::where('auditable_type', Item::class)
            ->where('auditable_id', $item->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        $item->load(['brand', 'category', 'baseUom', 'purchaseUom', 'barcodes', 'priceListItems.priceList']);

        return view('admin.items.show', compact('item', 'auditLogs'));
    }

    public function edit(Item $item): View
    {
        Gate::authorize('update', $item);

        return view('admin.items.edit', array_merge(['item' => $item], $this->formData()));
    }

    public function update(Request $request, Item $item): RedirectResponse
    {
        Gate::authorize('update', $item);

        $data = $this->validated($request, $item);
        $data['attributes_json']    = $this->parseJson($request->input('attributes_json'));
        $data['custom_fields_json'] = $this->parseJson($request->input('custom_fields_json'));

        $item->update($data);

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

    // ─── Import ───────────────────────────────────────────────────────────────

    public function importForm(): View
    {
        Gate::authorize('import', Item::class);

        return view('admin.items.import');
    }

    public function import(Request $request, ExcelImportService $service): RedirectResponse
    {
        Gate::authorize('import', Item::class);

        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls', 'max:20480'],
        ]);

        $path   = $request->file('excel_file')->getRealPath();
        $result = $service->import($path);

        return redirect()->route('admin.items.import-form')
            ->with('import_result', $result);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function formData(): array
    {
        return [
            'brands'     => Brand::where('is_active', true)->orderBy('name')->get(),
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'uoms'       => Uom::orderBy('code')->get(),
        ];
    }

    private function validated(Request $request, ?Item $item = null): array
    {
        $skuRule = ['required', 'string', 'max:100'];
        if ($item) {
            $skuRule[] = Rule::unique('items', 'sku_code')->ignore($item->id);
        } else {
            $skuRule[] = 'unique:items,sku_code';
        }

        return $request->validate([
            'sku_code'        => $skuRule,
            'name'            => ['required', 'string', 'max:255'],
            'brand_id'        => ['nullable', 'exists:brands,id'],
            'category_id'     => ['nullable', 'exists:categories,id'],
            'base_uom_id'     => ['required', 'exists:uoms,id'],
            'purchase_uom_id' => ['nullable', 'exists:uoms,id'],
            'pack_qty'        => ['numeric', 'min:0.0001'],
            'tax_included'    => ['boolean'],
            'is_active'       => ['boolean'],
        ]);
    }

    private function parseJson(?string $raw): ?array
    {
        if (blank($raw)) {
            return null;
        }
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : null;
    }
}
