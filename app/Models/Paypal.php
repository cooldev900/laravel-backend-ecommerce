<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StoreView;

class Paypal extends Model
{
    use HasFactory;

    protected $hidden = ['created_at', 'updated_at'];
    protected $fillable= ['client_id', 'client_secret', 'public_key', 'client_id_sandbox', 'client_secret_sandbox', 'public_key_sandbox', 'status', 'manual_capture', 'refund_in_platform'];
    protected $nullable= ['client_id', 'client_secret', 'public_key', 'client_id_sandbox', 'client_secret_sandbox', 'public_key_sandbox', 'status', 'manual_capture', 'refund_in_platform','created_at', 'updated_at'];

    public function storeview() {
        return $this->belongsTo(StoreView::class);
    }
}
