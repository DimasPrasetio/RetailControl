<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeDefinition extends Model
{
    protected $fillable = [
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
