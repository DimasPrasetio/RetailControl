<?php

namespace App\Observers;

use App\Models\Item;
use App\Models\ItemBarcode;

class ItemObserver
{
    /**
     * Cascade soft-delete to barcodes when an item is deleted.
     */
    public function deleting(Item $item): void
    {
        $item->barcodes()->each(fn ($barcode) => $barcode->delete());
    }

    /**
     * Cascade restore to barcodes when an item is restored.
     * Skips any barcode that has since been claimed by another active item
     * to avoid creating duplicates.
     */
    public function restoring(Item $item): void
    {
        $claimedBarcodes = ItemBarcode::query()
            ->where('tenant_id', $item->tenant_id)
            ->whereNot('item_id', $item->id)
            ->pluck('barcode')
            ->flip();

        $item->barcodes()->onlyTrashed()->each(function ($barcode) use ($claimedBarcodes) {
            if (! $claimedBarcodes->has($barcode->barcode)) {
                $barcode->restore();
            }
        });
    }
}
