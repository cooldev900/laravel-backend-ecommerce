<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPassCode extends Model
{
    use HasFactory;

    protected $hidden = ['created_at', 'updated_at'];

    protected $fillable = ['user_id', 'passcode', 'fail_num', 'token'];
    protected $nullable = ['user_id', 'passcode', 'fail_num', 'token'];
}
