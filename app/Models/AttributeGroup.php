<?php

namespace App\Models;
use App\Models\Company;
use App\Models\AttributeGroupStoreView;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributeGroup extends Model
{
    use HasFactory;

    protected $with = ['storeviews'];

    protected $fillable = [
        'name',
        'attribute_id',
        'product_tool_1',
        'product_tool_2',
        'product_tool_3',
        'product_tool_4',
    ];

    public $timestamps = false;

    public function company() {
        return $this->belongsTo(Company::class);
    }

    public function storeviews()
    {
        return $this->hasMany(AttributeGroupStoreView::class);
    }
}