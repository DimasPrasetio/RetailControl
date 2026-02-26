<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceListItem extends Model
{
    protected $fillable = [
        'price_list_id',
        'item_id',
        'sell_price',
        'price_uom_id',
        'notes_json',
    ];

    protected $casts = [
        'sell_price'  => 'decimal:2',
        'notes_json'  => 'array',
    ];

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function priceUom(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'price_uom_id');
    }
}
