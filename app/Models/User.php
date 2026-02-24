<?php

namespace App\Models;

use App\Enums\RoleEnum;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, Auditable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role_id',
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

    public function canAccessBranch(int $branchId): bool
    {
        return $this->isGlobal() || $this->branch_id === $branchId;
    }
}
