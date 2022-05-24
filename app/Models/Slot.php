<?php

namespace App\Models;
use App\Models\Appointment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slot extends Model
{
    use HasFactory;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    public $timestamps = false;

    protected $fillable = ['start_time', 'end_time', 'duration'];

    public function appointment()
    {
        return $this->hasOne(Appointment::class);
    }
}
