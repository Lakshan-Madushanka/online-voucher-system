<?php


namespace App\Repository\Auth;


use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

trait PasswordResetter
{
    use ApiResponser;

    public function sendResetPasswordLink(Request $request)
    {
        $credentials = $request->validate(['email' => ['required', 'email']]);

        $response = Password::sendResetLink($credentials);

        return $this->showOne([], Response::HTTP_OK,
            Response::$statusTexts[Response::HTTP_OK],
            'Reset link sent to your email');
    }

    public function redirectPasswordResetPage(Request $request)
    {
        $token = $request->query('token');

        abort_if(empty($token), 400, 'Token not found');

        return $this->showMessage(['token' => $token], 200, Response::HTTP_OK,
            'Send new credentals with token');
    }

    public function resetPassword(Request $request)
    {
        $errorStatus = [
            Password::INVALID_USER,
            Password::INVALID_TOKEN,
        ];

        $credentials = request()->validate([
            'email'    => ['required', 'email'],
            'token'    => ['required', 'string'],
            'password' => [
                'required', \Illuminate\Validation\Rules\Password::defaults(),
            ],
        ]);

        $reset_password_status = Password::reset($credentials,
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            });

        throw_if(in_array($reset_password_status, $errorStatus),
            ValidationException::withMessages(['token' => 'Invalid token or user email']));

        return $this->showMessage([]);
    }
}