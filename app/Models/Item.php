<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use Auditable;

    protected $fillable = [
        'sku_code',
        'name',
        'brand_id',
        'category_id',
        'base_uom_id',
        'purchase_uom_id',
        'pack_qty',
        'tax_included',
        'is_active',
        'attributes_json',
        'custom_fields_json',
        'raw_source_json',
    ];

    protected $casts = [
        'pack_qty'          => 'decimal:4',
        'tax_included'      => 'boolean',
        'is_active'         => 'boolean',
        'attributes_json'   => 'array',
        'custom_fields_json'=> 'array',
        'raw_source_json'   => 'array',
    ];

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

    public function purchaseUom(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'purchase_uom_id');
    }

    public function barcodes(): HasMany
    {
        return $this->hasMany(ItemBarcode::class);
    }

    public function priceListItems(): HasMany
    {
        return $this->hasMany(PriceListItem::class);
    }
}
