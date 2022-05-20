<?php

namespace App\Models;
use App\Models\Company;
use App\Models\AttributeGroup;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributeGroupStoreView extends Model
{
    use HasFactory;

    protected $table = 'attribute_groups_storeview';
    protected $fillable = [
        'store_view'
    ];

    public $timestamps = false;

    public function attribute_group() {
        $this->belongsTo(AttributeGroup::class, 'attribute_group_id');
    }
}