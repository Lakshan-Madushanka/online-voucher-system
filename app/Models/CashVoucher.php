<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashVoucher extends Model
{
    use HasFactory;

    public function owner()
    {
        return $this->belongsToMany(User::class, 'purchases')
            ->as('purchases')
            ->using(Purchase::class);
    }

    public function purchaseDetail()
    {
        return $this->hasOne(PurchaseDetail::class);
    }
}
