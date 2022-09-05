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
 * @OA\Property(property="public_api_key", type="string", description="Merchant Id", example="sdf_sdf4_sfd2"),
 * @OA\Property(property="secret_api_key", type="string", description="Stripe Key", example=""),
 * @OA\Property(property="webhook_secret", type="string", example=""),
 * @OA\Property(property="public_api_key_sandbox", type="string", description="Merchant Id", example="sdf_sdf4_sfd2"),
 * @OA\Property(property="secret_api_key_sandbox", type="string", description="Stripe Key", example=""),
 * @OA\Property(property="webhook_secret_sandbox", type="string", example=""),
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

class Stripe extends Model
{
    use HasFactory;

    protected $hidden = ['created_at', 'updated_at'];
    protected $fillable= ['public_api_key', 'secret_api_key', 'webhook_secret', 'public_api_key_sandbox', 'secret_api_key_sandbox', 'webhook_secret_sandbox', 'status', 'manual_capture', 'refund_in_platform', 'no_capture'];
    protected $nullable= ['public_api_key', 'secret_api_key', 'webhook_secret', 'public_api_key_sandbox', 'secret_api_key_sandbox', 'webhook_secret_sandbox', 'status', 'manual_capture', 'refund_in_platform', 'no_capture'];

    public function storeview() {
        return $this->belongsTo(StoreView::class);
    }
}
