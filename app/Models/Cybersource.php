<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StoreView;

/**
 *
 * @OA\Schema(
 * @OA\Xml(name="Cybersource"),
 * @OA\Property(property="id", type="integer", readOnly="true", example="1"),
 * @OA\Property(property="merchant_id", type="string", description="Merchant Id", example="sdf_sdf4_sfd2"),
 * @OA\Property(property="key", type="string", description="Cybersource Key", example=""),
 * @OA\Property(property="shared_secret_key", type="string", example=""),
 * @OA\Property(property="merchant_id_sandbox", type="string", description="Merchant Id", example="sdf_sdf4_sfd2"),
 * @OA\Property(property="key_sandbox", type="string", description="Cybersource Key", example=""),
 * @OA\Property(property="shared_secret_key_sandbox", type="string", example=""),
 * @OA\Property(property="status", type="integer", example="1"),
 * @OA\Property(property="manual_capture", type="integer", example="1"),
 * @OA\Property(property="refund_in_platform", type="integer", example="1"),
 * @OA\Property(property="created_at", ref="#/components/schemas/BaseModel/properties/created_at"),
 * @OA\Property(property="updated_at", ref="#/components/schemas/BaseModel/properties/updated_at"),
 * )
 *
 * Class Cybersource
 *
 */

class Cybersource extends Model
{
    use HasFactory;

    protected $hidden = ['created_at', 'updated_at'];
    protected $fillable= ['merchant_id', 'key', 'shared_secret_key', 'merchant_id_sandbox', 'key_sandbox', 'shared_secret_key_sandbox', 'status', 'manual_capture', 'refund_in_platform'];
    protected $nullable= ['merchant_id', 'key', 'shared_secret_key', 'merchant_id_sandbox', 'key_sandbox', 'shared_secret_key_sandbox', 'status', 'manual_capture', 'refund_in_platform'];

    public function storeview() {
        return $this->belongsTo(StoreView::class);
    }
}
