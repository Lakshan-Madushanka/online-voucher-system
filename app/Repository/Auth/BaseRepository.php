<?php


namespace App\Repository\Auth;


use App\Models\User;
use App\Repository\AuthRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class BaseRepository implements AuthRepositoryInterface
{
    use EmailVerifier, PasswordResetter;

    public function register(array $user)
    {
        abort_if(Auth::check(), 401,'Authenticated' );

        $user['password'] = Hash::make($user['password']);

        return User::create($user);
    }

}