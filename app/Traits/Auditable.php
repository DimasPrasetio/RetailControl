<?php

namespace App\Traits;

use App\Enums\AuditActionEnum;
use App\Services\Audit\AuditLogger;

/**
 * Trait untuk model yang membutuhkan audit trail otomatis.
 * Cukup tambahkan `use Auditable;` di model.
 *
 * Kolom yang TIDAK dicatat di audit (sensitif / tidak perlu):
 * Definisikan $auditExclude = ['password', 'remember_token'] di model.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (self $model) {
            AuditLogger::log(
                action: AuditActionEnum::Create,
                auditable: $model,
                newValues: $model->getAuditableAttributes(),
            );
        });

        static::updated(function (self $model) {
            $old = collect($model->getOriginal())
                ->only(array_keys($model->getDirty()))
                ->except($model->getAuditExclude())
                ->toArray();

            $new = collect($model->getDirty())
                ->except($model->getAuditExclude())
                ->toArray();

            if (empty($new)) {
                return; // tidak ada perubahan yang perlu dicatat
            }

            AuditLogger::log(
                action: AuditActionEnum::Update,
                auditable: $model,
                oldValues: $old,
                newValues: $new,
            );
        });

        static::deleted(function (self $model) {
            AuditLogger::log(
                action: AuditActionEnum::Delete,
                auditable: $model,
                oldValues: $model->getAuditableAttributes(),
            );
        });
    }

    protected function getAuditableAttributes(): array
    {
        return collect($this->getAttributes())
            ->except($this->getAuditExclude())
            ->toArray();
    }

    protected function getAuditExclude(): array
    {
        return $this->auditExclude ?? ['password', 'remember_token'];
    }
}
