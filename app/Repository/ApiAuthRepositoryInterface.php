<?php


namespace App\Repository;


use App\Models\User;

interface ApiAuthRepositoryInterface extends AuthRepositoryInterface
{
    public function generateToken(
        string $email,
        string $password,
        string $deviceName
    ): User;

    public function revokeToken(User $user, int $tokenId);

    public function revokeTokens(User $user);

    public function getTokenDetails(int $userId);

    public function checkTokenExpired(int $expireTime, string $createdDate);

    public function prepareResponse(
        User $user,
        string $token,
        ?int $expires_in = null
    );
}