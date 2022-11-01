<?php

namespace App\Models;

use App\Models\Company;
use App\Models\AttributeGroup;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Provider;
use App\Models\ProviderFeilds;
use App\Models\Paypal;
use App\Models\Stripe;
use App\Models\Cybersource;
use App\Models\CheckoutCom;
use App\Models\CheckoutCom2;
use App\Models\BarclaysEnterprise;

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
    protected $hidden = ['created_at', 'updated_at', 'company_id', 'api_key_1', 'api_key_2', 'payment_additional_1',
        'payment_additional_2', 'payment_additional_3', 'es_password', 'webhook_token'];

    protected $with = ['company', 'paypal', 'stripe', 'cybersource', 'checkoutcom', 'checkoutcom2', 'barclaysenterprise'];

    protected $fillable = [
        'code', 'store_id', 'company_id', 'payment_provider', 'api_key_1', 'api_key_2',
        'payment_additional_1', 'payment_additional_2', 'payment_additional_3', 'es_url',
        'es_index', 'es_username', 'es_password', 'vsf_url', 'vsf_preview', 'email_domain',
        'email_password', 'email_sender', 'website_id', 'webhook_token', 'language', 'currency', 'currency_code',
        'shipment_with_label', 'shipment_without_label', 'shipment_without_tracking', 'default_provider'
    ];

    public function company()
    {
        return $this->hasOne(Company::class, 'id', 'company_id');
    }

    public function paypal() {
        return $this->hasOne(Paypal::class);
    }

    public function stripe() {
        return $this->hasOne(Stripe::class);
    }

    public function cybersource() {
        return $this->hasOne(Cybersource::class);
    }

    public function checkoutcom() {
        return $this->hasOne(CheckoutCom::class);
    }

    public function checkoutcom2() {
        return $this->hasOne(CheckoutCom2::class);
    }

    public function barclaysenterprise() {
        return $this->hasOne(BarclaysEnterprise::class);
    }
}
