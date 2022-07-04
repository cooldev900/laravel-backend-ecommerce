<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StoreView;

class CheckoutCom extends Model
{
    use HasFactory;

    protected $hidden = ['created_at', 'updated_at'];
    protected $fillable= ['public_api_key', 'secret_api_key', 'webhook_secret', 'public_api_key_sandbox', 'secret_api_key_sandbox', 'webhook_secret_sandbox', 'status', 'manual_capture', 'refund_in_platform'];
    protected $nullable= ['public_api_key', 'secret_api_key', 'webhook_secret', 'public_api_key_sandbox', 'secret_api_key_sandbox', 'webhook_secret_sandbox', 'status', 'manual_capture', 'refund_in_platform'];

    public function storeview() {
        return $this->belongsTo(StoreView::class);
    }
}
