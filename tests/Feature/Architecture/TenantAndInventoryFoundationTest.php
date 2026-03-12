<?php

namespace Tests\Feature\Architecture;

use App\Models\Branch;
use App\Models\InventoryLedger;
use App\Models\Item;
use App\Models\StockLocation;
use App\Models\Tenant;
use App\Models\Uom;
use App\Services\Inventory\InventoryLedgerService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantAndInventoryFoundationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function inventory_ledger_records_balances_per_tenant_and_location(): void
    {
        $this->seed(DatabaseSeeder::class);

        $tenant = Tenant::query()->firstOrFail();
        $branch = Branch::where('tenant_id', $tenant->id)->firstOrFail();
        $location = StockLocation::where('tenant_id', $tenant->id)
            ->where('branch_id', $branch->id)
            ->whereNotNull('warehouse_id')
            ->firstOrFail();
        $uom = Uom::where('tenant_id', $tenant->id)->where('code', 'PCS')->firstOrFail();

        $item = Item::create([
            'tenant_id' => $tenant->id,
            'sku_code' => 'LEDGER-001',
            'name' => 'Produk Ledger',
            'base_uom_id' => $uom->id,
            'pack_qty' => 1,
            'is_stockable' => true,
            'is_active' => true,
        ]);

        $service = app(InventoryLedgerService::class);

        $first = $service->record(
            item: $item,
            movementType: 'INITIAL_STOCK',
            qtyIn: 10,
            stockLocation: $location,
            referenceNumber: 'INIT-001',
        );

        $second = $service->record(
            item: $item,
            movementType: 'SALE',
            qtyOut: 4,
            stockLocation: $location,
            referenceNumber: 'SAL-001',
        );

        $this->assertSame($tenant->id, $first->tenant_id);
        $this->assertSame($location->id, $first->stock_location_id);
        $this->assertEquals('10.0000', $first->balance_after);
        $this->assertEquals('6.0000', $second->balance_after);
    }

    /** @test */
    public function inventory_ledger_rows_are_immutable(): void
    {
        $this->seed(DatabaseSeeder::class);

        $tenant = Tenant::query()->firstOrFail();
        $branch = Branch::where('tenant_id', $tenant->id)->firstOrFail();
        $location = StockLocation::where('tenant_id', $tenant->id)
            ->where('branch_id', $branch->id)
            ->whereNotNull('warehouse_id')
            ->firstOrFail();
        $uom = Uom::where('tenant_id', $tenant->id)->where('code', 'PCS')->firstOrFail();

        $item = Item::create([
            'tenant_id' => $tenant->id,
            'sku_code' => 'LEDGER-IMMUTABLE',
            'name' => 'Produk Ledger Immutable',
            'base_uom_id' => $uom->id,
            'pack_qty' => 1,
            'is_stockable' => true,
            'is_active' => true,
        ]);

        $ledger = app(InventoryLedgerService::class)->record(
            item: $item,
            movementType: 'INITIAL_STOCK',
            qtyIn: 5,
            stockLocation: $location,
        );

        $this->expectException(\LogicException::class);
        $ledger->update(['reference_number' => 'CHANGED']);
    }

    /** @test */
    public function inventory_ledger_tracks_balance_per_location_stream_including_null_location(): void
    {
        $this->seed(DatabaseSeeder::class);

        $tenant = Tenant::query()->firstOrFail();
        $branch = Branch::where('tenant_id', $tenant->id)->firstOrFail();
        $location = StockLocation::where('tenant_id', $tenant->id)
            ->where('branch_id', $branch->id)
            ->whereNotNull('warehouse_id')
            ->firstOrFail();
        $uom = Uom::where('tenant_id', $tenant->id)->where('code', 'PCS')->firstOrFail();

        $item = Item::create([
            'tenant_id' => $tenant->id,
            'sku_code' => 'LEDGER-LOC-001',
            'name' => 'Produk Ledger Stream',
            'base_uom_id' => $uom->id,
            'pack_qty' => 1,
            'is_stockable' => true,
            'is_active' => true,
        ]);

        $service = app(InventoryLedgerService::class);

        $global = $service->record(
            item: $item,
            movementType: 'INITIAL_STOCK',
            qtyIn: 10,
            referenceNumber: 'GLOBAL-001',
        );

        $located = $service->record(
            item: $item,
            movementType: 'INITIAL_STOCK',
            qtyIn: 4,
            stockLocation: $location,
            referenceNumber: 'LOC-001',
        );

        $globalSale = $service->record(
            item: $item,
            movementType: 'SALE',
            qtyOut: 3,
            referenceNumber: 'GLOBAL-002',
        );

        $this->assertNull($global->stock_location_id);
        $this->assertSame($location->id, $located->stock_location_id);
        $this->assertEquals('10.0000', $global->balance_after);
        $this->assertEquals('4.0000', $located->balance_after);
        $this->assertEquals('7.0000', $globalSale->balance_after);
    }

    /** @test */
    public function inventory_ledger_rejects_negative_balance(): void
    {
        $this->seed(DatabaseSeeder::class);

        $tenant = Tenant::query()->firstOrFail();
        $uom = Uom::where('tenant_id', $tenant->id)->where('code', 'PCS')->firstOrFail();
        $item = Item::create([
            'tenant_id' => $tenant->id,
            'sku_code' => 'LEDGER-NEG-001',
            'name' => 'Produk Ledger Negatif',
            'base_uom_id' => $uom->id,
            'pack_qty' => 1,
            'is_stockable' => true,
            'is_active' => true,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Inventory balance cannot become negative.');

        app(InventoryLedgerService::class)->record(
            item: $item,
            movementType: 'SALE',
            qtyOut: 1,
            referenceNumber: 'NEG-001',
        );
    }

    /** @test */
    public function inventory_ledger_keeps_transaction_unit_and_normalized_base_qty(): void
    {
        $this->seed(DatabaseSeeder::class);

        $tenant = Tenant::query()->firstOrFail();
        $uomPcs = Uom::where('tenant_id', $tenant->id)->where('code', 'PCS')->firstOrFail();
        $uomBox = Uom::where('tenant_id', $tenant->id)->where('code', 'BOX')->firstOrFail();

        $item = Item::create([
            'tenant_id' => $tenant->id,
            'sku_code' => 'LEDGER-UOM-001',
            'name' => 'Produk Ledger Multi UOM',
            'base_uom_id' => $uomPcs->id,
            'selling_uom_id' => $uomBox->id,
            'purchase_uom_id' => $uomBox->id,
            'pack_qty' => 12,
            'is_stockable' => true,
            'is_active' => true,
        ]);

        $item->itemUnits()->updateOrCreate(
            ['uom_id' => $uomBox->id],
            [
                'tenant_id' => $tenant->id,
                'conversion_qty' => 12,
                'is_base' => false,
                'allow_sale' => true,
                'allow_purchase' => true,
                'is_default_sale' => true,
                'is_default_purchase' => true,
                'is_active' => true,
            ]
        );

        $ledger = app(InventoryLedgerService::class)->record(
            item: $item,
            movementType: 'PURCHASE_RECEIPT',
            qtyIn: 2,
            referenceNumber: 'BOX-001',
            transactionUomId: $uomBox->id,
        );

        $this->assertSame($uomPcs->id, $ledger->uom_id);
        $this->assertSame($uomBox->id, $ledger->transaction_uom_id);
        $this->assertEquals('24.0000', $ledger->qty_in);
        $this->assertEquals('2.0000', $ledger->transaction_qty_in);
        $this->assertEquals('24.0000', $ledger->balance_after);
    }
}
