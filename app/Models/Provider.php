<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProviderFields;

class Provider extends Model
{
    use HasFactory;

    protected $hidden = ['created_at', 'updated_at'];

    public $timestamps = false;

    protected $fillable = ['name'];
    protected $with = ['fields'];

    public function fields()
    {
        return $this->hasMany(ProviderFields::class);
    }
}
