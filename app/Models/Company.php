<?php

namespace App\Models;
use App\Models\Attribute;
use App\Models\AttributeGroup;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at'];

    protected $fillable = ['name', 'url', 'consumer_key', 'consumer_secret', 'token', 'token_secret', 'user_id', 'image_base_url'];

    public function attributes()
    {
        return $this->hasMany(Attribute::class);
    }

    public function attribute_groups() {
        return $this->hasMany(AttributeGroup::class);
    }
}
