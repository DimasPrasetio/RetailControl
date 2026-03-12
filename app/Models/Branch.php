<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use Auditable, TenantScoped;

    protected $fillable = [
        'tenant_id',
        'branch_code',
        'name',
        'address',
        'phone',
        'timezone',
        'notes',
        'metadata_json',
        'is_active',
    ];

    protected $casts = [
        'metadata_json' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }

    public function stockLocations(): HasMany
    {
        return $this->hasMany(StockLocation::class);
    }
}
