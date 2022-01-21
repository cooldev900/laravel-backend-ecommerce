<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_name',
        'location_order_id',
        'vsf_store_id',
        'country',
        'region',
        'city',
        'street',
        'postcode',
        'phone',
        'is_hub',
        'collection',
        'fitment',
        'delivery',
        'brand',
        'longitude',
        'latitude',
    ];

    public $timestamps = false;
}