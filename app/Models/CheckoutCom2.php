<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckoutCom2 extends Model
{
    use HasFactory;

    protected $hidden = ['created_at', 'updated_at'];
    protected $fillable= ['public_api_key', 'secret_api_key', 'webhook_secret', 'public_api_key_sandbox', 'secret_api_key_sandbox', 'webhook_secret_sandbox', 'status', 'manual_capture', 'refund_in_platform', 'no_capture'];
    protected $nullable= ['public_api_key', 'secret_api_key', 'webhook_secret', 'public_api_key_sandbox', 'secret_api_key_sandbox', 'webhook_secret_sandbox', 'status', 'manual_capture', 'refund_in_platform', 'no_capture'];

    public function storeview() {
        return $this->belongsTo(StoreView::class);
    }
}
