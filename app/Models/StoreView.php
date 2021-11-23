<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     @OA\Xml(name="StoreView"),
 *     @OA\Property(
 *          property="id",
 *          type="integer",
 *          description="Unique StoreView ID",
 *          example="1",
 *      ),
 *     @OA\Property(property="code", type="string", example="omni"),
 *     @OA\Property(property="magento_id", type="integer", example="1"),
 * )
 *
 * Class StoreView
 *
 * @package App\Models
 */

class StoreView extends Model
{
    use HasFactory;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at'];

    protected $fillable = [
        'code',
        'magento_id',
    ];
}