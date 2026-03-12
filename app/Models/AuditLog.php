<?php

namespace App\Models;

use App\Enums\AuditActionEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    // Tabel ini append-only — tidak boleh ada update atau delete
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'tenant_id',
        'action',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'action'     => AuditActionEnum::class,
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /** Guard: tidak pernah boleh update atau delete baris audit_log */
    public static function boot(): void
    {
        parent::boot();

        static::updating(fn () => throw new \LogicException('AuditLog records are immutable.'));
        static::deleting(fn () => throw new \LogicException('AuditLog records cannot be deleted.'));
    }
}
