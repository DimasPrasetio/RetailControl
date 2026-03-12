<?php

namespace App\Services\Audit;

use App\Enums\AuditActionEnum;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Service untuk mencatat semua perubahan sensitif ke audit_logs.
 * Tabel audit_logs adalah append-only — tidak boleh ada update/delete.
 *
 * Penggunaan manual:
 *   AuditLogger::log(AuditActionEnum::Login, $user, newValues: ['ip' => '...']);
 *
 * Penggunaan otomatis (via Auditable trait di Model):
 *   Otomatis terpanggil saat create/update/delete.
 */
class AuditLogger
{
    public static function log(
        AuditActionEnum $action,
        Model $auditable,
        array $oldValues = [],
        array $newValues = [],
    ): AuditLog {
        return AuditLog::create([
            'user_id'        => Auth::id(),
            'tenant_id'      => $auditable->tenant_id ?? Auth::user()?->tenant_id,
            'action'         => $action,
            'auditable_type' => get_class($auditable),
            'auditable_id'   => $auditable->getKey(),
            'old_values'     => empty($oldValues) ? null : $oldValues,
            'new_values'     => empty($newValues) ? null : $newValues,
            'ip_address'     => Request::ip(),
            'user_agent'     => Request::userAgent(),
            'created_at'     => now(),
        ]);
    }

    /**
     * Shorthand untuk mencatat event login/logout
     * yang tidak memiliki model target selain User itu sendiri.
     */
    public static function logAuth(AuditActionEnum $action, Model $user): AuditLog
    {
        return self::log(
            action: $action,
            auditable: $user,
            newValues: ['ip_address' => Request::ip()],
        );
    }
}
