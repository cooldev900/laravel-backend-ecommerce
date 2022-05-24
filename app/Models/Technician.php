<?php

namespace App\Models;
use App\Models\Appointment;
use App\Models\Company;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Technician extends Model
{
    use HasFactory;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */

    public $timestamps = false;
    
    protected $fillable = ['name', 'store_views', 'timezone', 'working_days'];

    public function company() {
        return $this->belongsTo(Company::class);
    }

    public function appointment()
    {
        return $this->hasOne(Appointment::class);
    }
}
