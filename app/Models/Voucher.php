<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    const storagePath = 'vouchers';
    const STATUS
        = [
            'APPROVED' => 'approved',
            'REJECTED' => 'rejected',
            'PENDING'  => 'pending',
        ];

    protected $fillable
        = [
            'image',
            'price',
            'terms',
            'validity',
            'status',
        ];

    public function owner()
    {
        return $this->belongsToMany(User::class, 'purchases')
            ->as('purchases')->withTimestamps();
    }

}
