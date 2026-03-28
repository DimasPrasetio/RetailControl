<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BranchScoped;
use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use Auditable, BranchScoped, SoftDeletes, TenantScoped;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'warehouse_code',
        'name',
        'type',
        'notes',
        'metadata_json',
        'is_active',
    ];

    protected $casts = [
        'metadata_json' => 'array',
        'is_active' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function stockLocations()
    {
        return $this->hasMany(StockLocation::class);
    }
}
