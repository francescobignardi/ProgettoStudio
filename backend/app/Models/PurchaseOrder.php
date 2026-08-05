<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = ['supplier_id', 'order_date', 'notes', 'status'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
