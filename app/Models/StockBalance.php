<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockBalance extends Model
{
    use TenantScoped;

    protected $fillable = ['tenant_id', 'item_id', 'stock_location_id', 'qty'];

    protected $casts = ['qty' => 'decimal:4'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function stockLocation(): BelongsTo
    {
        return $this->belongsTo(StockLocation::class);
    }
}
