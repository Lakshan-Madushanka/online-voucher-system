<?php


namespace App\Repository;


use Illuminate\Http\Request;

interface AuthRepositoryInterface
{
    public function register(array $user);
    public function verifyEmail(int $id, Request $request);
    public function sendEmailVerification($id, Request $request);
    public function sendResetPasswordLink(Request $request);
    public function redirectPasswordResetPage(Request $request);
    public function resetPassword(Request $request);

}