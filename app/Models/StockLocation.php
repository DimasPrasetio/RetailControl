<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BranchScoped;
use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockLocation extends Model
{
    use Auditable, BranchScoped, TenantScoped;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'warehouse_id',
        'parent_id',
        'code',
        'name',
        'type',
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

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function isSystemLocation(): bool
    {
        return in_array($this->type, ['BRANCH', 'WAREHOUSE'], true);
    }
}
