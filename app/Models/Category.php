<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use Auditable, SoftDeletes, TenantScoped;

    protected $fillable = ['tenant_id', 'parent_id', 'name', 'code', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function attributeDefinitions()
    {
        return $this->belongsToMany(
            AttributeDefinition::class,
            'category_attribute_sets',
            'category_id',
            'attribute_definition_id'
        );
    }
}
