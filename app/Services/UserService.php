<?php


namespace App\Services;


use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserService
{
    public static function getRoles(User $user)
    {
        return  (clone $user)->roles->pluck('id')->toArray();
    }

    public static function checkRole(string $role)
    {
        if(!Auth::check()) {
            return false;
        }

        $authUser = Auth::user();
        $roles = self::getRoles($authUser);

        switch ($role) {
            case 'isAdministrative' :
                return in_array(Role::types['admin'], $roles)
                    || in_array(Role::types['super_admin'], $roles);
                break;
            case 'isSuperAdmin' :
                return in_array(Role::types['super_admin'], $roles);
                break;
            case 'isAdmin' :
                return in_array(Role::types['admin'], $roles);
                break;
        }
    }
}