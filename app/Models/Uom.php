<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Uom extends Model
{
    use Auditable;

    protected $fillable = ['code', 'name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function itemsAsBase(): HasMany
    {
        return $this->hasMany(Item::class, 'base_uom_id');
    }

    public function itemsAsPurchase(): HasMany
    {
        return $this->hasMany(Item::class, 'purchase_uom_id');
    }
}
