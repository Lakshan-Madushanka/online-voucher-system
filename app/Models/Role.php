<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    const SUPER_ADMIN = 'super_admin';
    const ADMIN = 'admin';
    const CUSTOMER = 'customer';

    const types
        = [
            self::SUPER_ADMIN => 1,
            self::ADMIN       => 2,
            self::CUSTOMER    => 3,
        ];

    public static function getRoleName()
    {
        $names = array_keys(self::types);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_role')->withTimestamps();
    }
}
