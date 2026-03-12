<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use Auditable;

    protected $fillable = [
        'code',
        'name',
        'is_active',
        'metadata_json',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata_json' => 'array',
    ];

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
