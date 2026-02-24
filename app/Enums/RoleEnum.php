<?php

namespace App\Enums;

enum RoleEnum: string
{
    case SuperAdmin   = 'super_admin';
    case AdminCabang  = 'admin_cabang';
    case Kasir        = 'kasir';
    case Accounting   = 'accounting';
    case Owner        = 'owner';

    public function label(): string
    {
        return match($this) {
            self::SuperAdmin  => 'Super Admin',
            self::AdminCabang => 'Admin Cabang',
            self::Kasir       => 'Kasir',
            self::Accounting  => 'Accounting',
            self::Owner       => 'Owner',
        };
    }

    /** Role yang memiliki akses ke semua cabang tanpa filter */
    public function isGlobal(): bool
    {
        return match($this) {
            self::SuperAdmin, self::Owner => true,
            default                       => false,
        };
    }

    /** Role yang membutuhkan branch_id */
    public function requiresBranch(): bool
    {
        return ! $this->isGlobal();
    }
}
