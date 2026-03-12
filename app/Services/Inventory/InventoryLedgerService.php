<?php

namespace App\Services\Inventory;

use App\Models\InventoryLedger;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\StockLocation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InventoryLedgerService
{
    public function record(
        Item $item,
        string $movementType,
        float $qtyIn = 0,
        float $qtyOut = 0,
        ?StockLocation $stockLocation = null,
        ?string $sourceType = null,
        ?int $sourceId = null,
        ?string $referenceNumber = null,
        array $notes = [],
        ?User $performedBy = null,
        ?int $transactionUomId = null,
    ): InventoryLedger {
        if ($qtyIn <= 0 && $qtyOut <= 0) {
            throw new \InvalidArgumentException('Either qty_in or qty_out must be greater than zero.');
        }

        if ($qtyIn > 0 && $qtyOut > 0) {
            throw new \InvalidArgumentException('qty_in and qty_out cannot be populated together.');
        }

        if ($stockLocation && $stockLocation->tenant_id !== $item->tenant_id) {
            throw new \InvalidArgumentException('Stock location must belong to the same tenant as the item.');
        }

        if ($performedBy && $performedBy->tenant_id !== null && $performedBy->tenant_id !== $item->tenant_id) {
            throw new \InvalidArgumentException('Performed by user must belong to the same tenant as the item.');
        }

        $transactionUnit = $this->resolveTransactionUnit($item, $transactionUomId);
        $normalizedQtyIn = $qtyIn > 0 ? $qtyIn * $transactionUnit->conversion_qty : 0;
        $normalizedQtyOut = $qtyOut > 0 ? $qtyOut * $transactionUnit->conversion_qty : 0;

        return DB::transaction(function () use (
            $item,
            $movementType,
            $qtyIn,
            $qtyOut,
            $normalizedQtyIn,
            $normalizedQtyOut,
            $stockLocation,
            $sourceType,
            $sourceId,
            $referenceNumber,
            $notes,
            $performedBy,
            $transactionUnit
        ) {
            Item::query()
                ->whereKey($item->id)
                ->lockForUpdate()
                ->first();

            if ($stockLocation) {
                StockLocation::query()
                    ->whereKey($stockLocation->id)
                    ->lockForUpdate()
                    ->first();
            }

            $lastLedger = InventoryLedger::query()
                ->where('tenant_id', $item->tenant_id)
                ->where('item_id', $item->id)
                ->when(
                    $stockLocation,
                    fn ($query) => $query->where('stock_location_id', $stockLocation->id),
                    fn ($query) => $query->whereNull('stock_location_id')
                )
                ->lockForUpdate()
                ->latest('id')
                ->first();

            $balanceBefore = (float) ($lastLedger?->balance_after ?? 0);

            $balanceAfter = $balanceBefore + $normalizedQtyIn - $normalizedQtyOut;
            if ($balanceAfter < 0) {
                throw new \InvalidArgumentException('Inventory balance cannot become negative.');
            }

            return InventoryLedger::create([
                'tenant_id' => $item->tenant_id,
                'branch_id' => $stockLocation?->branch_id,
                'warehouse_id' => $stockLocation?->warehouse_id,
                'stock_location_id' => $stockLocation?->id,
                'item_id' => $item->id,
                'uom_id' => $item->base_uom_id,
                'transaction_uom_id' => $transactionUnit->uom_id,
                'movement_type' => $movementType,
                'qty_in' => $normalizedQtyIn,
                'qty_out' => $normalizedQtyOut,
                'transaction_qty_in' => $qtyIn > 0 ? $qtyIn : null,
                'transaction_qty_out' => $qtyOut > 0 ? $qtyOut : null,
                'balance_after' => $balanceAfter,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'reference_number' => $referenceNumber,
                'notes_json' => $notes ?: null,
                'user_id' => $performedBy?->id,
                'created_at' => now(),
            ]);
        });
    }

    private function resolveTransactionUnit(Item $item, ?int $transactionUomId): ItemUnit
    {
        $targetUomId = $transactionUomId ?: $item->base_uom_id;

        $itemUnit = $item->itemUnits()
            ->where('uom_id', $targetUomId)
            ->where('is_active', true)
            ->first();

        if (! $itemUnit) {
            throw new \InvalidArgumentException('Transaction unit must be registered on the item.');
        }

        return $itemUnit;
    }
}
