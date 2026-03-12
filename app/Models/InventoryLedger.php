<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLedger extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'warehouse_id',
        'stock_location_id',
        'item_id',
        'uom_id',
        'transaction_uom_id',
        'movement_type',
        'qty_in',
        'qty_out',
        'transaction_qty_in',
        'transaction_qty_out',
        'balance_after',
        'source_type',
        'source_id',
        'reference_number',
        'notes_json',
        'user_id',
        'created_at',
    ];

    protected $casts = [
        'qty_in' => 'decimal:4',
        'qty_out' => 'decimal:4',
        'transaction_qty_in' => 'decimal:4',
        'transaction_qty_out' => 'decimal:4',
        'balance_after' => 'decimal:4',
        'notes_json' => 'array',
        'created_at' => 'datetime',
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

    public function stockLocation(): BelongsTo
    {
        return $this->belongsTo(StockLocation::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(Uom::class);
    }

    public function transactionUom(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'transaction_uom_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public static function boot(): void
    {
        parent::boot();

        static::updating(fn () => throw new \LogicException('Inventory ledger rows are immutable.'));
        static::deleting(fn () => throw new \LogicException('Inventory ledger rows cannot be deleted.'));
    }
}
