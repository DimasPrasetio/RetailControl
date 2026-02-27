<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Stub model — tabel branches akan dibuat di modul berikutnya (Modul 03+).
 * Model ini ada agar User::branch() tidak throw "Class not found".
 */
class Branch extends Model
{
    protected $fillable = ['name', 'code', 'address', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
