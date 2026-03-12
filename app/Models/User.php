<?php

namespace App\Models;

use App\Enums\RoleEnum;
use App\Traits\Auditable;
use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, Auditable, TenantScoped;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role_id',
        'tenant_id',
        'branch_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password'   => 'hashed',
        'is_active'  => 'boolean',
        'deleted_at' => 'datetime',
    ];

    // Kolom yang tidak dicatat di audit log
    protected array $auditExclude = ['password', 'remember_token'];

    // ─── Relations ───────────────────────────────────────────────────────────

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Branch akan tersedia setelah Module 02.
     * Didefinisikan di sini agar tidak perlu modifikasi User model nanti.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    // ─── Role Helpers ────────────────────────────────────────────────────────

    public function isSuperAdmin(): bool
    {
        return $this->role->name === RoleEnum::SuperAdmin;
    }

    public function isPlatformAdmin(): bool
    {
        return $this->isSuperAdmin();
    }

    public function isOwner(): bool
    {
        return $this->role->name === RoleEnum::Owner;
    }

    public function isGlobal(): bool
    {
        return $this->role->name->isGlobal();
    }

    // ─── Permission Helpers ──────────────────────────────────────────────────

    /**
     * Cek apakah user memiliki permission tertentu.
     * Super Admin otomatis lolos semua permission.
     */
    public function hasPermission(string $slug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->role->hasPermission($slug);
    }

    // ─── Branch Access ───────────────────────────────────────────────────────

    /**
     * Kembalikan branch_id untuk filter query.
     * NULL = tidak difilter (super_admin / owner).
     */
    public function getAccessibleBranchId(): ?int
    {
        return $this->isGlobal() ? null : $this->branch_id;
    }

    public function getAccessibleTenantId(): ?int
    {
        return $this->isPlatformAdmin() ? null : $this->tenant_id;
    }

    public function canAccessTenant(?int $tenantId): bool
    {
        if ($tenantId === null) {
            return $this->isPlatformAdmin();
        }

        return $this->isPlatformAdmin() || $this->tenant_id === $tenantId;
    }

    public function canAccessBranch(int $branchId): bool
    {
        if ($this->isPlatformAdmin()) {
            return true;
        }

        $branch = Branch::query()->select(['id', 'tenant_id'])->find($branchId);
        if (! $branch || ! $this->canAccessTenant($branch->tenant_id)) {
            return false;
        }

        return $this->isGlobal() || $this->branch_id === $branchId;
    }

    public function allowsMissingTenantOnCreate(): bool
    {
        if (empty($this->role_id) || ! empty($this->tenant_id)) {
            return false;
        }

        $role = Role::query()->find($this->role_id);
        if (! $role) {
            return false;
        }

        return $role->name === RoleEnum::SuperAdmin;
    }
}
