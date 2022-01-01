<?php


namespace App\Http\Controllers\Api\V1\Auth;


use App\Services\UserService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class AuthController
{
    use ApiResponser;

    private $userService;

    public function show(Request $request)
    {
        $authUser = $request->user();

        $roles = UserService::getRoles( $authUser);

        $authUser['roles'] = $roles;

        return $this->showOne($authUser);
    }
}