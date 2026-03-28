<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Uom extends Model
{
    use Auditable, SoftDeletes, TenantScoped;

    protected $fillable = ['tenant_id', 'code', 'name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function itemsAsBase(): HasMany
    {
        return $this->hasMany(Item::class, 'base_uom_id');
    }

    public function itemsAsSelling(): HasMany
    {
        return $this->hasMany(Item::class, 'selling_uom_id');
    }

    public function itemsAsPurchase(): HasMany
    {
        return $this->hasMany(Item::class, 'purchase_uom_id');
    }

    public function itemUnits(): HasMany
    {
        return $this->hasMany(ItemUnit::class);
    }
}
