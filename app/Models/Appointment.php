<?php

namespace App\Models;
use App\Models\Technician;
use App\Models\Slot;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at'];
    protected $with = ['technician', 'slot'];

    public $timestamps = false;

    protected $fillable = ['client_id', 'customer', 'consumer_key', 'start_time', 'end_time', 'order_id', 'booked_online', 'duration', 'note', 'internal_booking'];
    protected $nullable = ['client_id', 'customer', 'consumer_key', 'start_time', 'end_time', 'order_id', 'booked_online', 'duration', 'note', 'internal_booking'];

    public function technician()
    {
        return $this->belongsTo(Technician::class);
    }

    public function slot() {
        return $this->belongsTo(Slot::class);
    }
}
