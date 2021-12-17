<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable
        = [
            'name',
            'email',
            'password',
        ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden
        = [
            'password',
            'remember_token',
        ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts
        = [
            'email_verified_at' => 'datetime',
        ];

    public static function getRoles(User $user)
    {
        return $user->roles->pluck('id')->toArray();

    }

    public function checkRole(string $role)
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

    public static function isAdministrative(User $user)
    {
        $roles = self::getRoles($user);

        return in_array(Role::types['admin'], $roles)
            || in_array(Role::types['super_admin'], $roles);
    }

    public static function isSuperAdmin(User $user)
    {
        $roles = self::getRoles();

        return in_array(Role::types['super_admin'], $roles);
    }

    public static function isAdmin(User $user)
    {
        $roles = self::getRoles();

        return in_array(Role::types['admin'], $roles);
    }


    //relationships
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role')->withTimestamps();
    }

    public function vouchers()
    {
        return $this->belongsToMany(Voucher::class, 'purchases')
            ->as('purchases');
    }

    public function cashVouchers()
    {
        return $this->belongsToMany(CashVoucher::class, 'purchases')
            ->as('purchases');
    }
}
