<?php

namespace App\Models;

use App\Models\Role;
use App\Models\Scope;
use App\Models\StoreView;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    use HasFactory;

    protected $with = ['scopes', 'store_views', 'roles'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = ['user_id', 'store_view_id', 'role_id', 'scope_id'];

    public function users()
    {
        return $this->belongsTo(User::class);
    }

    public function scopes()
    {
        return $this->belongsTo(Scope::class, 'scope_id');
    }

    public function store_views()
    {
        return $this->belongsTo(StoreView::class, 'store_view_id');
    }

    public function roles()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}