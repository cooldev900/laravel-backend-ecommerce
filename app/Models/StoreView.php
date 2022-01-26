<?php

namespace App\Models;

use App\Models\Company;
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
    protected $hidden = ['created_at', 'updated_at', 'company_id'];

    protected $with = ['company'];

    protected $fillable = [
        'code', 'store_id', 'company_id', 'payment_provider', 'api_key_1', 'api_key_2', 'payment_additional_1', 'payment_additional_2', 'payment_additional_3',
    ];

    public function company()
    {
        return $this->hasOne(Company::class, 'id', 'company_id');
    }
}