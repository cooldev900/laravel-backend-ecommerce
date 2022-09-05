<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StoreView;

/**
 *
 * @OA\Schema(
 * @OA\Xml(name="Stripe"),
 * @OA\Property(property="id", type="integer", readOnly="true", example="1"),
 * @OA\Property(property="client_id", type="string", description="Merchant Id", example="sdf_sdf4_sfd2"),
 * @OA\Property(property="client_secret", type="string", description="Stripe Key", example=""),
 * @OA\Property(property="public_key", type="string", example=""),
 * @OA\Property(property="client_id_sandbox", type="string", description="Merchant Id", example="sdf_sdf4_sfd2"),
 * @OA\Property(property="client_secret_sandbox", type="string", description="Stripe Key", example=""),
 * @OA\Property(property="public_key_sandbox", type="string", example=""),
 * @OA\Property(property="status", type="integer", example="1"),
 * @OA\Property(property="manual_capture", type="integer", example="1"),
 * @OA\Property(property="refund_in_platform", type="integer", example="1"),
 * @OA\Property(property="created_at", ref="#/components/schemas/BaseModel/properties/created_at"),
 * @OA\Property(property="updated_at", ref="#/components/schemas/BaseModel/properties/updated_at"),
 * )
 *
 * Class Stripe
 *
 */

class Paypal extends Model
{
    use HasFactory;

    protected $hidden = ['created_at', 'updated_at'];
    protected $fillable= ['client_id', 'client_secret', 'public_key', 'client_id_sandbox', 'client_secret_sandbox', 'public_key_sandbox', 'status', 'manual_capture', 'refund_in_platform', 'no_capture'];
    protected $nullable= ['client_id', 'client_secret', 'public_key', 'client_id_sandbox', 'client_secret_sandbox', 'public_key_sandbox', 'status', 'manual_capture', 'refund_in_platform','created_at', 'updated_at', 'no_capture'];

    public function storeview() {
        return $this->belongsTo(StoreView::class);
    }
}
