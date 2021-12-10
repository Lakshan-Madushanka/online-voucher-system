<?php


namespace App\Repository\Auth;


use App\Models\User;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

trait EmailVerifier
{
    use ApiResponser;

    public function verifyEmail(int $id, Request $request)
    {
        $user = User::findOrFail($id);

        if ($user->hasVerifiedEmail()) {
            return $this->isEmailVerified($request, $user);
        }

        if (!hash_equals((string) $request->route('id'),
            (string) $user->getKey())
        ) {
            throw new AccessDeniedException('Invalid User');
        }

        if (!$request->hasValidSignature()) {
            return $this->showError(['error' => Response::$statusTexts[Response::HTTP_BAD_REQUEST]],
                Response::HTTP_BAD_REQUEST,
                'error',
                'Invalid or Expired URL provided.', null
            );
        }

        if (hash_equals((string) $request->route('hash'), sha1($user
            ->getEmailForVerification()))
        ) {
            $user->markEmailAsVerified();

            return $this->showOne([], 200, Response::HTTP_OK,
                'Email verified',);
        }
    }

    public function sendEmailVerification($id, Request $request)
    {

        $user = User::findOrFail($id);

        if($user->hasVerifiedEmail()){
            return $this->isEmailVerified($request, $user);
        }

        $user->sendEmailVerificationNotification();

        return $this->showOne([], 200, Response::HTTP_OK,
            'Email verification link sent to your email',
        );
    }

    protected function isEmailVerified(Request $request, $user)
    {
        if ($user
            ->hasVerifiedEmail()
        ) {
            return $this->showError([],
                Response::HTTP_BAD_REQUEST,
                'error',
                'Email has been already verified.',
            );
        }
    }
}