<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemBarcode extends Model
{
    protected $fillable = ['item_id', 'barcode', 'is_primary'];

    protected $casts = ['is_primary' => 'boolean'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
