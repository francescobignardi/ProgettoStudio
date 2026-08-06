<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseOrder extends Model
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    protected $fillable = ['supplier_id', 'order_date', 'notes', 'status'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
