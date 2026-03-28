<?php

namespace App\Services\Inventory;

use App\Models\InventoryLedger;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\StockBalance;
use App\Models\StockLocation;
use Illuminate\Support\Facades\DB;

class InventoryLedgerService
{
    /**
     * Record a stock movement and keep stock_balances in sync atomically.
     *
     * @param  float  $qtyIn   Quantity received (in transaction UOM).
     * @param  float  $qtyOut  Quantity issued  (in transaction UOM).
     */
    public function record(
        Item $item,
        string $movementType,
        StockLocation $stockLocation,
        float $qtyIn = 0,
        float $qtyOut = 0,
        ?LedgerInput $input = null,
    ): InventoryLedger {
        $input ??= new LedgerInput();

        $this->assertValidInput($item, $stockLocation, $qtyIn, $qtyOut, $input);

        $transactionUnit  = $this->resolveTransactionUnit($item, $input->transactionUomId);
        $normalizedQtyIn  = $qtyIn  > 0 ? $qtyIn  * $transactionUnit->conversion_qty : 0;
        $normalizedQtyOut = $qtyOut > 0 ? $qtyOut * $transactionUnit->conversion_qty : 0;

        $ctx = [
            'movementType'     => $movementType,
            'qtyIn'            => $qtyIn,
            'qtyOut'           => $qtyOut,
            'normalizedQtyIn'  => $normalizedQtyIn,
            'normalizedQtyOut' => $normalizedQtyOut,
            'transactionUnit'  => $transactionUnit,
            'input'            => $input,
        ];

        return DB::transaction(fn () => $this->writeWithinTransaction($item, $stockLocation, $ctx));
    }

    private function assertValidInput(
        Item $item,
        StockLocation $stockLocation,
        float $qtyIn,
        float $qtyOut,
        LedgerInput $input
    ): void {
        if ($qtyIn <= 0 && $qtyOut <= 0) {
            throw new \InvalidArgumentException('Either qty_in or qty_out must be greater than zero.');
        }

        if ($qtyIn > 0 && $qtyOut > 0) {
            throw new \InvalidArgumentException('qty_in and qty_out cannot be populated together.');
        }

        if ($stockLocation->tenant_id !== $item->tenant_id) {
            throw new \InvalidArgumentException('Stock location must belong to the same tenant as the item.');
        }

        if ($input->performedBy && $input->performedBy->tenant_id !== null
            && $input->performedBy->tenant_id !== $item->tenant_id) {
            throw new \InvalidArgumentException('Performed by user must belong to the same tenant as the item.');
        }
    }

    private function writeWithinTransaction(Item $item, StockLocation $stockLocation, array $ctx): InventoryLedger
    {
        Item::query()->whereKey($item->id)->lockForUpdate()->first();
        StockLocation::query()->whereKey($stockLocation->id)->lockForUpdate()->first();

        $lastLedger = InventoryLedger::query()
            ->where('tenant_id', $item->tenant_id)
            ->where('item_id', $item->id)
            ->where('stock_location_id', $stockLocation->id)
            ->lockForUpdate()
            ->latest('id')
            ->first();

        $balanceBefore = (float) ($lastLedger?->balance_after ?? 0);
        $balanceAfter  = $balanceBefore + $ctx['normalizedQtyIn'] - $ctx['normalizedQtyOut'];

        if ($balanceAfter < 0) {
            throw new \InvalidArgumentException('Inventory balance cannot become negative.');
        }

        /** @var ItemUnit $unit */
        $unit = $ctx['transactionUnit'];

        /** @var LedgerInput $input */
        $input = $ctx['input'];

        $ledger = InventoryLedger::create([
            'tenant_id'           => $item->tenant_id,
            'branch_id'           => $stockLocation->branch_id,
            'warehouse_id'        => $stockLocation->warehouse_id,
            'stock_location_id'   => $stockLocation->id,
            'item_id'             => $item->id,
            'uom_id'              => $item->base_uom_id,
            'transaction_uom_id'  => $unit->uom_id,
            'movement_type'       => $ctx['movementType'],
            'qty_in'              => $ctx['normalizedQtyIn'],
            'qty_out'             => $ctx['normalizedQtyOut'],
            'transaction_qty_in'  => $ctx['qtyIn']  > 0 ? $ctx['qtyIn']  : null,
            'transaction_qty_out' => $ctx['qtyOut'] > 0 ? $ctx['qtyOut'] : null,
            'balance_after'       => $balanceAfter,
            'source_type'         => $input->sourceType,
            'source_id'           => $input->sourceId,
            'reference_number'    => $input->referenceNumber,
            'notes_json'          => $input->notes ?: null,
            'user_id'             => $input->performedBy?->id,
            'created_at'          => now(),
        ]);

        $this->syncStockBalance($item, $stockLocation, $balanceAfter);

        return $ledger;
    }

    private function syncStockBalance(Item $item, StockLocation $stockLocation, float $newQty): void
    {
        StockBalance::query()
            ->lockForUpdate()
            ->firstOrCreate(
                ['item_id' => $item->id, 'stock_location_id' => $stockLocation->id],
                ['tenant_id' => $item->tenant_id, 'qty' => 0]
            );

        StockBalance::where('item_id', $item->id)
            ->where('stock_location_id', $stockLocation->id)
            ->update(['qty' => $newQty]);
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
