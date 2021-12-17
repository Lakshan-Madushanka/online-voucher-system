<?php

namespace App\Http\Controllers\Api\V1\Auth;


use App\Http\Controllers\Controller;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserStoreRequest;
use App\Models\Role;
use App\Repository\SanctumAuthRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SanctumSPAAuthController extends Controller
{
    private $authRepo;

    public function __construct(SanctumAuthRepositoryInterface $authRepo)
    {
        $this->authRepo = $authRepo;
    }

    public function SPAAuth(UserLoginRequest $request)
    {
        $userInputs     = $request->validated();
        $shouldRemember = $this->shouldRememberUser($userInputs);
        unset($userInputs['rememberMe']);

        $isAuthenticated
            = $this->authRepo->spaAuthenticate($userInputs, $shouldRemember);

        if ($isAuthenticated) {
            $this->modifyAuthUser($shouldRemember);

            return $this->showOne(Auth::user(), Response::HTTP_CREATED);
        } else {
            return $this->showError([Response::$statusTexts[422] => __('auth.failed')]);
        }
    }

    public function register(UserStoreRequest $request)
    {
        $userInputs     = $request->validated();
        $credentals     = $userInputs;
        $shouldRemember = $this->shouldRememberUser($userInputs);
        unset($credentals['name']);
        unset($credentals['rememberMe']);

        $registeredUser = $this->authRepo
            ->register($userInputs);

        if ($registeredUser) {
            $registeredUser->roles()->attach([Role::types['customer']]);

            return $this->showOne($registeredUser, Response::HTTP_CREATED);
        } else {
            return $this->showError([Response::$statusTexts[500] => Response::HTTP_INTERNAL_SERVER_ERROR]);
        }
    }

    public function sendEmail($id, Request $request)
    {
        return $this->authRepo->sendEmailVerification($id, $request);
    }

    public function verifyEmail($id, Request $request)
    {
        return $this->authRepo->verifyEmail($id, $request);
    }

    public function sendResetPasswordLink(Request $request)
    {
        return $this->authRepo->sendResetPasswordLink($request);
    }

    public function redirectPasswordResetPage(Request $request)
    {
        return $this->authRepo->redirectPasswordResetPage($request);
    }

    public function resetPassword(Request $request)
    {
        return $this->authRepo->resetPassword($request);
    }

    public function logout(Request $request)
    {
        $isLogout = $this->authRepo->logout($request);

        if ($isLogout) {
            return $this->showMessage([], Response::HTTP_OK,
                Response::$statusTexts[200],
                Response::$statusTexts[401]);
        }
    }

    public function shouldRememberUser(array $credentials)
    {
        if (array_key_exists('rememberMe', $credentials)) {
            $shouldRemember = $credentials['rememberMe'];

            return $shouldRemember;
        }

        return false;
    }

    public function modifyAuthUser(bool $shouldRemember)
    {
        if ($shouldRemember) {
            Auth::user()['rememberMe'] = true;
        }
    }
}
