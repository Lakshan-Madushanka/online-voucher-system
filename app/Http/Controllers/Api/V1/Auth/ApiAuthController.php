<?php


namespace App\Http\Controllers\Api\V1\Auth;


use App\Http\Requests\ApiLoginValidateRequest;
use App\Repository\ApiAuthRepositoryInterface;
use App\Services\UserService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class ApiAuthController
{
    use ApiResponser;

    private $apiAuth;
    private $userService;

    public function __construct(
        ApiAuthRepositoryInterface $apiAuth,
        UserService $userService
    ) {
        $this->apiAuth = $apiAuth;
        $this->userService = $userService;
    }

    public function login(ApiLoginValidateRequest $request)
    {
        $inputs = $request->validated();

        $authUser = $this->apiAuth->generateToken($inputs['email'],
            $inputs['password'], $inputs['app_id']);

        $user = clone $authUser;

        $roles = UserService::getRoles($user);

        $authUser['roles'] = $roles;

        return $this->showOne($authUser);

    }

    public function logout(Request $request)
    {
        $this->apiAuth->logout($request->user());

        return $this->showOne(null);
    }

    public function logoutAll(Request $request)
    {
        $this->apiAuth->revokeTokens($request->user());

        return $this->showOne(null);
    }
}