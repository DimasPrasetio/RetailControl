<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Uom extends Model
{
    protected $fillable = ['code', 'name'];

    public function itemsAsBase(): HasMany
    {
        return $this->hasMany(Item::class, 'base_uom_id');
    }

    public function itemsAsPurchase(): HasMany
    {
        return $this->hasMany(Item::class, 'purchase_uom_id');
    }
}
