<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $hidden = ['created_at', 'updated_at'];

    public $timestamps = false;

    public function __construct($attributes = [], $table = null) {
        parent::__construct();
        $this->fillable = $attributes;
        $this->nullable = $attributes;
        $this->table = $table;
    }
}
