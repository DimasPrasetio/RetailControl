<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemBarcode;
use App\Models\ItemUnit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ItemBarcodeController extends Controller
{
    public function index(Item $item): View
    {
        Gate::authorize('manageBarcodes', $item);

        $item->load(['barcodes.itemUnit.uom', 'itemUnits.uom']);

        return view('admin.items.barcodes.index', compact('item'));
    }

    public function store(Request $request, Item $item): RedirectResponse
    {
        Gate::authorize('manageBarcodes', $item);

        $request->validate([
            'barcode' => [
                'required',
                'string',
                'max:100',
                Rule::unique('item_barcodes', 'barcode')
                    ->where(fn($query) => $query->where('tenant_id', $item->tenant_id)),
            ],
            'item_unit_id' => [
                'nullable',
                Rule::exists('item_units', 'id')
                    ->where(fn($query) => $query->where('item_id', $item->id)),
            ],
        ], [
            'barcode.required' => 'Barcode wajib diisi.',
            'barcode.unique' => 'Barcode ini sudah dipakai oleh produk lain di tenant yang sama.',
            'item_unit_id.exists' => 'Satuan tidak valid untuk produk ini.',
        ]);

        $isPrimary = $request->boolean('is_primary');

        DB::transaction(function () use ($request, $item, $isPrimary): void {
            // Jika set primary, lepas primary lama terlebih dahulu
            if ($isPrimary) {
                $item->barcodes()->update(['is_primary' => false]);
            }

            // Jika ini barcode pertama, otomatis jadikan primary
            if ($item->barcodes()->count() === 0) {
                $isPrimary = true;
            }

            ItemBarcode::create([
                'tenant_id' => $item->tenant_id,
                'item_id' => $item->id,
                'item_unit_id' => $request->input('item_unit_id') ?: null,
                'barcode' => $request->input('barcode'),
                'is_primary' => $isPrimary,
            ]);
        });

        return redirect()->route('admin.items.barcodes.index', $item)
            ->with('success', 'Barcode berhasil ditambahkan.');
    }

    public function setPrimary(Item $item, ItemBarcode $barcode): RedirectResponse
    {
        Gate::authorize('manageBarcodes', $item);

        abort_unless($barcode->item_id === $item->id, 404);

        DB::transaction(function () use ($item, $barcode): void {
            $item->barcodes()->update(['is_primary' => false]);
            $barcode->update(['is_primary' => true]);
        });

        return redirect()->route('admin.items.barcodes.index', $item)
            ->with('success', 'Barcode utama berhasil diperbarui.');
    }

    public function destroy(Item $item, ItemBarcode $barcode): RedirectResponse
    {
        Gate::authorize('manageBarcodes', $item);

        abort_unless($barcode->item_id === $item->id, 404);

        $wasPrimary = $barcode->is_primary;
        $barcode->delete();

        // Jika barcode yang dihapus adalah primary, otomatis set barcode pertama sebagai primary baru
        if ($wasPrimary && $item->barcodes()->count() > 0) {
            $item->barcodes()->oldest()->first()->update(['is_primary' => true]);
        }

        return redirect()->route('admin.items.barcodes.index', $item)
            ->with('success', 'Barcode berhasil dihapus.');
    }
}
