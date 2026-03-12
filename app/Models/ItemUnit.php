<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemUnit extends Model
{
    use TenantScoped;

    protected $fillable = [
        'tenant_id',
        'item_id',
        'uom_id',
        'conversion_qty',
        'is_base',
        'allow_sale',
        'allow_purchase',
        'is_default_sale',
        'is_default_purchase',
        'is_active',
    ];

    protected $casts = [
        'conversion_qty' => 'decimal:4',
        'is_base' => 'boolean',
        'allow_sale' => 'boolean',
        'allow_purchase' => 'boolean',
        'is_default_sale' => 'boolean',
        'is_default_purchase' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(Uom::class);
    }

    public function barcodes(): HasMany
    {
        return $this->hasMany(ItemBarcode::class);
    }
}
