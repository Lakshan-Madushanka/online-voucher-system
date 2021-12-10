<?php


namespace App\Repository\Auth;


use App\Repository\SanctumAuthRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class SanctumAuthRepository extends BaseRepository implements SanctumAuthRepositoryInterface
{
    public function __construct()
    {
    }

    public function spaAuthenticate(array $credentials, bool $shouldRemember = false)
    {
        if (Auth::attempt($credentials, $shouldRemember)) {
            \request()->session()->regenerate();

            return true;
        }
        return false;
    }
}