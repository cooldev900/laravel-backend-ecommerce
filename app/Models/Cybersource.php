<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StoreView;

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
