<?php


namespace App\Repository\Auth;


use App\Repository\SanctumAuthRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SanctumAuthRepository extends BaseRepository implements SanctumAuthRepositoryInterface
{
    public function __construct()
    {
    }

    public function spaAuthenticate(array $credentials, bool $shouldRemember = false)
    {
        abort_if(Auth::check(), 401,'Authenticated');

        if (Auth::attempt($credentials, $shouldRemember)) {
            \request()->session()->regenerate();

            return true;
        }
        return false;
    }

    public function logout(Request $request)
    {
        abort_if(!Auth::check(), 'Unauthrnticated', 401);

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return true;
    }
}