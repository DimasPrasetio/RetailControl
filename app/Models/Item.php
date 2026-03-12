<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Item extends Model
{
    use Auditable, TenantScoped;

    protected $fillable = [
        'tenant_id',
        'sku_code',
        'name',
        'brand_id',
        'category_id',
        'base_uom_id',
        'selling_uom_id',
        'purchase_uom_id',
        'pack_qty',
        'tax_included',
        'is_stockable',
        'cost_price',
        'selling_price',
        'minimum_selling_price',
        'is_active',
        'attributes_json',
        'custom_fields_json',
        'raw_source_json',
    ];

    protected $casts = [
        'pack_qty'          => 'decimal:4',
        'tax_included'      => 'boolean',
        'is_stockable'      => 'boolean',
        'cost_price'        => 'decimal:2',
        'selling_price'     => 'decimal:2',
        'minimum_selling_price' => 'decimal:2',
        'is_active'         => 'boolean',
        'attributes_json'   => 'array',
        'custom_fields_json'=> 'array',
        'raw_source_json'   => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function baseUom(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'base_uom_id');
    }

    public function sellingUom(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'selling_uom_id');
    }

    public function purchaseUom(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'purchase_uom_id');
    }

    public function barcodes(): HasMany
    {
        return $this->hasMany(ItemBarcode::class);
    }

    public function itemUnits(): HasMany
    {
        return $this->hasMany(ItemUnit::class)->orderByDesc('is_base')->orderBy('conversion_qty');
    }

    public function priceListItems(): HasMany
    {
        return $this->hasMany(PriceListItem::class);
    }

    public function inventoryLedgers(): HasMany
    {
        return $this->hasMany(InventoryLedger::class);
    }

    protected static function booted(): void
    {
        static::creating(function (self $item): void {
            if (! $item->selling_uom_id) {
                $item->selling_uom_id = $item->base_uom_id;
            }

            if (! $item->pack_qty) {
                $item->pack_qty = 1;
            }
        });

        static::saved(function (self $item): void {
            $item->syncLegacyUnitRows();
        });
    }

    public function syncLegacyUnitRows(): void
    {
        if (! $this->tenant_id || ! $this->base_uom_id) {
            return;
        }

        DB::transaction(function (): void {
            $baseUnit = $this->itemUnits()->updateOrCreate(
                ['uom_id' => $this->base_uom_id],
                [
                    'tenant_id' => $this->tenant_id,
                    'conversion_qty' => 1,
                    'is_base' => true,
                    'allow_sale' => true,
                    'allow_purchase' => (int) $this->purchase_uom_id === (int) $this->base_uom_id,
                    'is_active' => true,
                ]
            );

            if ($this->purchase_uom_id && (int) $this->purchase_uom_id !== (int) $this->base_uom_id) {
                $this->itemUnits()->updateOrCreate(
                    ['uom_id' => $this->purchase_uom_id],
                    [
                        'tenant_id' => $this->tenant_id,
                        'conversion_qty' => $this->pack_qty ?: 1,
                        'is_base' => false,
                        'allow_purchase' => true,
                        'is_active' => true,
                    ]
                );
            }

            $this->itemUnits()->update([
                'is_default_sale' => false,
                'is_default_purchase' => false,
            ]);

            $sellingUomId = $this->selling_uom_id ?: $this->base_uom_id;
            $this->itemUnits()
                ->where('uom_id', $sellingUomId)
                ->update([
                    'allow_sale' => true,
                    'is_default_sale' => true,
                ]);

            if ($this->purchase_uom_id) {
                $this->itemUnits()
                    ->where('uom_id', $this->purchase_uom_id)
                    ->update([
                        'allow_purchase' => true,
                        'is_default_purchase' => true,
                    ]);
            } elseif ($baseUnit) {
                $baseUnit->update(['is_default_purchase' => false]);
            }
        });
    }
}
