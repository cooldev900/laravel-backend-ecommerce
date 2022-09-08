<?php

namespace App\Models;
use App\Models\Company;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'group',
        'used_as_product_option',
        'details',
        'variant_product_field',
        'used_for_filter'
    ];

    public $timestamps = false;

    public function company() {
        return $this->belongsTo(Company::class);
    }
}
