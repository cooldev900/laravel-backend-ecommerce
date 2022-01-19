<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'enquiries';
    protected $fillable = ['first_name', 'last_name', 'email', 'vin', 'item_required', 'message', 'phone', 'client_id', 'store_id'];
}