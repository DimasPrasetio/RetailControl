<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttributeDefinition extends Model
{
    use Auditable, SoftDeletes, TenantScoped;

    protected $fillable = [
        'tenant_id',
        'key',
        'label',
        'data_type',
        'unit',
        'options_json',
        'is_required',
    ];

    protected $casts = [
        'options_json' => 'array',
        'is_required'  => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function categories()
    {
        return $this->belongsToMany(
            Category::class,
            'category_attribute_sets',
            'attribute_definition_id',
            'category_id'
        );
    }
}
