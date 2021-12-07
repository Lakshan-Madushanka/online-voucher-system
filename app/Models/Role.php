<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    const types
        = [
            'super_admin' => 1,
            'admin'       => 2,
            'customer'    => 3,
        ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_role')->withTimestamps();
    }
}
