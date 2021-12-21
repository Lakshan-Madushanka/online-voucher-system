<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseDetail extends Model
{
    use HasFactory;

    const type = [
        'direct',
        'presented'
    ];

    protected $fillable = [
        'quantity',
        'type',
        'receiver_id',
        'purchase_id'
    ];
    public function voucher()
    {
        return $this->belongsTo(Purchase::class);
    }
}

