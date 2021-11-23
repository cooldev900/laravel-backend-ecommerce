<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     @OA\Xml(name="Scope"),
 *     @OA\Property(
 *          property="id",
 *          type="integer",
 *          description="Unique Scope ID",
 *          example="1",
 *      ),
 *     @OA\Property(property="name", type="string", example="products"),
 * )
 *
 * Class Scope
 *
 * @package App\Models
 */

class Scope extends Model
{
    use HasFactory;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at'];

    protected $fillable = [
        'name',
    ];
}