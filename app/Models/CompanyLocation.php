<?php

namespace App\Models;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyLocation extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'company_location';

    protected $with = ['locations'];

    public function locations()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
}
