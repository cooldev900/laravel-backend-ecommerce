<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarclaysEnterprise extends Model
{
    use HasFactory;
    protected $table = 'barclays_enterprise';
    
    protected $hidden = ['created_at', 'updated_at'];
    protected $fillable= ['client_id', 'secret_key', 'enterprise', 'client_id_sandbox', 'secret_key_sandbox', 'enterprise_sandbox', 'status', 'manual_capture', 'refund_in_platform', 'no_capture'];
    protected $nullable= ['client_id', 'secret_key', 'enterprise', 'client_id_sandbox', 'secret_key_sandbox', 'enterprise_sandbox', 'status', 'manual_capture', 'refund_in_platform', 'no_capture'];

    public function storeview() {
        return $this->belongsTo(StoreView::class);
    }
}
